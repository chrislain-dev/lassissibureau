<?php

namespace App\Http\Controllers;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Http\Requests\StoreProductModelRequest;
use App\Http\Requests\UpdateProductModelRequest;
use App\Models\ProductModel;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ProductModel::class);

        $query = ProductModel::query();

        // Ajouter les compteurs de base
        $query->withCount([
            'products',
            'productsInStock',
            'productsAvailableForSale',
            'productsSold',
        ]);

        // Filtres
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('brand', 'ILIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filtre pour stock bas - CORRIGÉ
        if ($request->boolean('low_stock')) {
            $query->select('product_models.*')
                ->selectSub(
                    'SELECT COUNT(*) FROM products 
                     WHERE products.product_model_id = product_models.id 
                     AND products.location IN (\'boutique\', \'en_reparation\')
                     AND products.deleted_at IS NULL',
                    'current_stock_count'
                )
                ->havingRaw('current_stock_count <= product_models.stock_minimum');
        }

        $productModels = $query->latest()->paginate(20);

        // Ajouter le stock actuel calculé pour chaque modèle
        $productModels->getCollection()->transform(function ($model) {
            $model->current_stock = $model->productsInStock()->count();
            $model->is_low_stock = $model->current_stock <= $model->stock_minimum;

            return $model;
        });

        return view('product-models.index', compact('productModels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', ProductModel::class);

        return view('product-models.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductModelRequest $request, ProductService $productService)
    {
        $validated = $request->validated();
        
        return DB::transaction(function () use ($validated, $productService) {
            $productModel = ProductModel::create($validated);
            
            // Si c'est un accessoire et qu'une quantité initiale est fournie
            if ($productModel->category->value === 'accessoire' && !empty($validated['quantity']) && (int)$validated['quantity'] > 0) {
                $quantity = (int) $validated['quantity'];
                
                for ($i = 0; $i < $quantity; $i++) {
                    $productService->createProduct([
                        'product_model_id' => $productModel->id,
                        'imei' => null,
                        'serial_number' => null,
                        'state' => ProductState::DISPONIBLE->value,
                        'location' => ProductLocation::BOUTIQUE->value,
                        'condition' => $productModel->condition_type?->value ?? 'neuf',
                        'date_achat' => now()->format('Y-m-d'),
                        'created_by' => auth()->id(),
                    ]);
                }
                
                return redirect()
                    ->route('product-models.show', $productModel)
                    ->with('success', "Modèle de produit créé avec succès et {$quantity} accessoires initialisés en boutique.");
            }

            return redirect()
                ->route('product-models.show', $productModel)
                ->with('success', 'Modèle de produit créé avec succès.');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductModel $productModel)
    {
        $this->authorize('view', $productModel);

        // Charger les produits avec leurs mouvements
        $productModel->load(['products' => function ($query) {
            $query->latest()->with('stockMovements')->take(20);
        }]);

        // Statistiques détaillées
        $stats = [
            // Quantités
            'total_stock' => $productModel->stock_quantity,
            'available_for_sale' => $productModel->available_quantity,
            'total_sold' => $productModel->sold_quantity,
            'at_resellers' => $productModel->reseller_quantity,
            'in_repair' => $productModel->repair_quantity,

            // Prix - Délégués au ProductModel
            'average_purchase_price' => $productModel->prix_revient_default ?? 0,
            'average_sale_price'     => $productModel->prix_vente_default ?? 0,

            // Valeurs
            'stock_value' => $productModel->stock_value,
            'potential_sale_value' => $productModel->stock_sale_value,
            'potential_profit' => $productModel->stock_potential_profit,

            // Alertes
            'is_low_stock' => $productModel->isLowStock(),
            'stock_minimum' => $productModel->stock_minimum,

            // Bénéfices réalisés
            'total_profit' => $productModel->productsSold()
                ->join('sales', 'products.id', '=', 'sales.product_id')
                ->where('sales.is_confirmed', true)
                ->sum('sales.benefice') ?? 0,
        ];

        return view('product-models.show', compact('productModel', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductModel $productModel)
    {
        $this->authorize('update', $productModel);

        return view('product-models.edit', compact('productModel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductModelRequest $request, ProductModel $productModel)
    {
        $productModel->update($request->validated());

        return redirect()
            ->route('product-models.show', $productModel)
            ->with('success', 'Modèle de produit mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductModel $productModel)
    {
        $this->authorize('delete', $productModel);

        // Vérifier s'il y a des produits associés
        $productsCount = $productModel->products()->count();

        if ($productsCount > 0) {
            return back()->with('error', "Impossible de supprimer ce modèle car {$productsCount} produit(s) l'utilisent.");
        }

        $productModel->delete();

        return redirect()
            ->route('product-models.index')
            ->with('success', 'Modèle de produit supprimé avec succès.');
    }

    /**
     * Obtenir les statistiques globales des modèles (API)
     */
    public function stats()
    {
        $this->authorize('viewAny', ProductModel::class);

        $stats = [
            'total_models' => ProductModel::count(),
            'active_models' => ProductModel::where('is_active', true)->count(),
            'low_stock_models' => $this->getLowStockModelsCount(),
            'total_products' => DB::table('products')->whereNull('deleted_at')->count(),
            'total_stock_value' => $this->getTotalStockValue(),
        ];

        return response()->json($stats);
    }

    /**
     * Obtenir le nombre de modèles en stock bas
     */
    private function getLowStockModelsCount(): int
    {
        $lowStockCount = 0;

        $productModels = ProductModel::where('is_active', true)->get();

        foreach ($productModels as $model) {
            $stockQuantity = $model->productsInStock()->count();
            if ($stockQuantity <= $model->stock_minimum) {
                $lowStockCount++;
            }
        }

        return $lowStockCount;
    }

    /**
     * Obtenir la valeur totale du stock
     */
    private function getTotalStockValue(): float
    {
        // Les prix sont sur ProductModel, pas sur Product
        // On calcule : SUM(prix_revient_default) pour chaque produit en stock
        return (float) DB::table('products')
            ->join('product_models', 'products.product_model_id', '=', 'product_models.id')
            ->whereNull('products.deleted_at')
            ->whereIn('products.location', ['boutique', 'en_reparation'])
            ->sum('product_models.prix_revient_default');
    }
}
