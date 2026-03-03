<?php

namespace App\Http\Controllers;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Http\Requests\StoreCustomerReturnRequest;
use App\Models\CustomerReturn;
use App\Models\Product;
use App\Models\Sale;
use App\Services\ProductService;
use App\Services\SaleService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReturnController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private SaleService $saleService,
        private StockService $stockService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CustomerReturn::class);

        $query = CustomerReturn::with(['originalSale.product.productModel', 'returnedProduct', 'exchangeProduct', 'processor']);

        // Filtres
        if ($request->filled('is_exchange')) {
            $query->where('is_exchange', $request->boolean('is_exchange'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $returns = $query->latest()->paginate(20);

        return view('returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', CustomerReturn::class);

        // Vente présélectionnée
        $sale = null;
        if ($request->filled('sale_id')) {
            $sale = Sale::with('product.productModel')->find($request->sale_id);
        }

        // Ventes récentes confirmées (produits vendus et chez le client)
        $recentSales = Sale::with('product.productModel')
            ->confirmed()
            ->whereHas('product', function ($q) {
                $q->where('state', ProductState::VENDU->value)
                    ->where('location', ProductLocation::CHEZ_CLIENT->value);
            })
            ->latest()
            ->take(50)
            ->get();

        // Produits disponibles pour échange
        $availableProducts = Product::availableForSale()->with('productModel')->get();

        return view('returns.create', compact('sale', 'recentSales', 'availableProducts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerReturnRequest $request)
    {
        $customerReturn = DB::transaction(function () use ($request) {
            $validated = $request->validated();

            // Créer le retour client
            $customerReturn = CustomerReturn::create($validated);

            $returnedProduct = Product::findOrFail($validated['returned_product_id']);

            if ($validated['is_exchange']) {
                // C'est un échange
                $exchangeProduct = Product::findOrFail($validated['exchange_product_id']);

                // Créer une nouvelle vente pour le produit d'échange
                $newSale = $this->saleService->createSale([
                    'product_id'            => $exchangeProduct->id,
                    'sale_type'             => $customerReturn->originalSale->sale_type,
                    'client_name'           => $customerReturn->originalSale->client_name,
                    'client_phone'          => $customerReturn->originalSale->client_phone,
                    'date_vente_effective'  => now()->format('Y-m-d'),
                    'is_confirmed'          => true,
                    'payment_status'        => 'paid',
                    'payment_method'        => 'cash',
                    'amount_paid'           => $exchangeProduct->prix_vente,
                    'sold_by'               => $validated['processed_by'],
                    'notes'                 => 'Échange suite retour - Retour original: #'.$customerReturn->original_sale_id,
                ]);

                $customerReturn->update(['exchange_sale_id' => $newSale->id]);

                // Mouvement pour le produit d'échange (vendu au client)
                $this->stockService->createMovement([
                    'product_id' => $exchangeProduct->id,
                    'type' => StockMovementType::ECHANGE_RETOUR->value,
                    'quantity' => 1,
                    'state_after' => ProductState::VENDU->value,
                    'location_after' => ProductLocation::CHEZ_CLIENT->value,
                    'related_product_id' => $returnedProduct->id,
                    'user_id' => $validated['processed_by'],
                    'notes' => 'Échange suite retour client',
                ]);
            }

            // Mouvement pour le produit retourné (retour en boutique)
            $this->stockService->createMovement([
                'product_id' => $returnedProduct->id,
                'type' => StockMovementType::RETOUR_CLIENT->value,
                'quantity' => 1,
                'state_after' => ProductState::RETOUR->value,
                'location_after' => ProductLocation::BOUTIQUE->value,
                'user_id' => $validated['processed_by'],
                'notes' => 'Retour client - '.$validated['reason'],
            ]);

            return $customerReturn->fresh(['originalSale', 'returnedProduct', 'exchangeProduct', 'exchangeSale']);
        });

        return redirect()
            ->route('returns.show', $customerReturn)
            ->with('success', 'Retour client enregistré avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerReturn $customerReturn)
    {
        $this->authorize('view', $customerReturn);

        $customerReturn->load([
            'originalSale.product.productModel',
            'returnedProduct.productModel',
            'exchangeProduct.productModel',
            'exchangeSale',
            'processor',
        ]);

        return view('returns.show', compact('customerReturn'));
    }

    /**
     * Process a product after return (repair or mark as defective).
     */
    public function processReturnedProduct(Request $request, CustomerReturn $customerReturn)
    {
        $this->authorize('update', $customerReturn);

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:repair,available,defective'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $product = $customerReturn->returnedProduct;

        DB::transaction(function () use ($product, $validated, $request) {
            switch ($validated['action']) {
                case 'repair':
                    // Envoyer en réparation
                    $this->productService->sendToRepair(
                        $product,
                        $request->user()->id,
                        $validated['notes'] ?? 'Suite retour client'
                    );
                    break;

                case 'available':
                    // Remettre disponible (produit OK)
                    $this->productService->changeStateAndLocation(
                        $product,
                        StockMovementType::RETOUR_CLIENT,
                        $request->user()->id,
                        ProductState::DISPONIBLE,
                        ProductLocation::BOUTIQUE,
                        ['notes' => $validated['notes'] ?? 'Produit vérifié - disponible']
                    );
                    break;

                case 'defective':
                    // Marquer comme défectueux/perdu
                    $this->productService->changeStateAndLocation(
                        $product,
                        StockMovementType::PERTE,
                        $request->user()->id,
                        ProductState::PERDU,
                        ProductLocation::BOUTIQUE,
                        [
                            'notes' => $validated['notes'] ?? 'Produit défectueux - hors service',
                            'justification' => 'Retour client - produit irrécupérable',
                        ]
                    );
                    break;
            }
        });

        return redirect()
            ->route('returns.show', $customerReturn)
            ->with('success', 'Produit traité avec succès.');
    }
}
