<?php

namespace App\Http\Controllers;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductModel;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with(['productModel', 'lastMovement']);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('imei', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('productModel', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    });
            });
        }

        // Filtre par état
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // Filtre par localisation
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Filtre par modèle
        if ($request->filled('product_model_id')) {
            $query->where('product_model_id', $request->product_model_id);
        }

        // Filtre par condition
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate(20)->withQueryString();

        // Pour les filtres dans la vue
        $productModels = ProductModel::active()->orderBy('name')->get();
        $states = ProductState::options();
        $locations = ProductLocation::options();
        $conditions = ['Neuf', 'Excellent', 'Très bon', 'Bon', 'Correct', 'Passable'];

        return view('products.index', compact(
            'products',
            'productModels',
            'states',
            'locations',
            'conditions'
        ));
    }

    // NOTE: Les méthodes create() et store() ont été supprimées car remplacées par le composant Livewire CreateProduct

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load([
            'productModel',
            'stockMovements.user',
            'sale.reseller',
            'sale.seller',
            'sale.payments.recorder',
            'sale.customerReturn.supplierReturn.replacementProduct.productModel',
            'tradeIn.sale.seller',
            'customerReturn.originalSale',
            'customerReturn.supplierReturn.replacementProduct.productModel',
            'customerReturn.processor',
            'creator',
            'updater',
        ]);

        // Statistiques du produit
        $stats = $this->productService->getProductStats($product);

        return view('products.show', compact('product', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $productModels = ProductModel::active()->orderBy('name')->get();
        $states = ProductState::options();
        $locations = ProductLocation::options();
        $conditions = ['Neuf', 'Excellent', 'Très bon', 'Bon', 'Correct', 'Passable'];

        return view('products.edit', compact(
            'product',
            'productModels',
            'states',
            'locations',
            'conditions'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product = $this->productService->updateProduct($product, $request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Produit mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        try {
            $this->productService->deleteProduct($product);

            return redirect()
                ->route('products.index')
                ->with('success', 'Produit supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->route('products.show', $product)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Search product by IMEI.
     */
    public function searchByImei(Request $request)
    {
        $request->validate([
            'imei' => ['required', 'string', 'size:15', 'regex:/^[0-9]{15}$/'],
        ]);

        $product = $this->productService->findByImei($request->imei);

        if (! $product) {
            return redirect()
                ->route('products.index')
                ->with('error', 'Aucun produit trouvé avec cet IMEI.');
        }

        return redirect()->route('products.show', $product);
    }

    /**
     * Update product prices.
     */
    public function updatePrices(Request $request, Product $product)
    {
        $this->authorize('updatePrices', $product);

        $validated = $request->validate([
            'prix_achat' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'prix_vente' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'gte:prix_achat'],
        ], [
            'prix_achat.required' => 'Le prix d\'achat est obligatoire.',
            'prix_vente.required' => 'Le prix de vente est obligatoire.',
            'prix_vente.gte' => 'Le prix de vente doit être supérieur ou égal au prix d\'achat.',
        ]);

        $product = $this->productService->updatePrices(
            $product,
            $validated['prix_achat'],
            $validated['prix_vente'],
            $request->user()->id
        );

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Prix mis à jour avec succès.');
    }

    /**
     * Display products needing attention (to repair, returns, lost).
     */
    public function needsAttention()
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->productService->getProductsNeedingAttention();

        return view('products.needs-attention', compact('products'));
    }

    /**
     * Display available products for sale.
     */
    public function available()
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->productService->getAvailableProducts();

        return view('products.available', compact('products'));
    }

    /**
     * Quick sell form (for fast checkout).
     */
    public function quickSell(Product $product)
    {
        $this->authorize('sell', $product);

        if (! $product->isAvailable()) {
            return redirect()
                ->route('products.show', $product)
                ->with('error', 'Ce produit n\'est pas disponible à la vente.');
        }

        return view('products.quick-sell', compact('product'));
    }

    /**
     * API endpoint for product search (AJAX).
     */
    public function apiSearch(Request $request)
    {
        $search = $request->get('q');

        $products = Product::with('productModel')
            ->where(function ($query) use ($search) {
                $query->where('imei', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('productModel', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'text' => $product->productModel->name.' - '.
                        ($product->imei ? 'IMEI: '.$product->imei : 'S/N: '.$product->serial_number),
                    'state' => $product->state->label(),
                    'location' => $product->location->label(),
                    'available' => $product->isAvailable(),
                    'prix_vente' => $product->prix_vente,
                ];
            });

        return response()->json($products);
    }

    /**
     * Export products to CSV.
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with(['productModel']);

        // Appliquer les mêmes filtres que l'index
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('product_model_id')) {
            $query->where('product_model_id', $request->product_model_id);
        }

        $products = $query->get();

        $filename = 'produits_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Modèle',
                'Marque',
                'IMEI',
                'Numéro de série',
                'État',
                'Localisation',
                'Prix d\'achat',
                'Prix de vente',
                'Bénéfice potentiel',
                'Condition',
                'Date d\'achat',
                'Fournisseur',
            ]);

            // Données
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->productModel->name,
                    $product->productModel->brand,
                    $product->imei,
                    $product->serial_number,
                    $product->state->label(),
                    $product->location->label(),
                    $product->prix_achat,
                    $product->prix_vente,
                    $product->benefice_potentiel,
                    $product->condition,
                    $product->date_achat?->format('Y-m-d'),
                    $product->fournisseur,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Liste des produits à renvoyer au fournisseur ou déjà chez le fournisseur.
     */
    public function supplierReturns()
    {
        $this->authorize('viewAny', Product::class);

        // Produits à renvoyer : en boutique mais marqués comme retour/perdu/à réparer
        $toReturn = Product::with(['productModel', 'lastMovement'])
            ->where('location', ProductLocation::BOUTIQUE->value)
            ->whereIn('state', [
                ProductState::RETOUR->value,
                ProductState::PERDU->value,
                ProductState::A_REPARER->value,
            ])
            ->get();

        // Produits déjà chez le fournisseur
        $atSupplier = Product::with(['productModel', 'lastMovement'])
            ->where('location', ProductLocation::FOURNISSEUR->value)
            ->latest('updated_at')
            ->limit(50)
            ->get();

        return view('products.supplier-returns', compact('toReturn', 'atSupplier'));
    }
}
