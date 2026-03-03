<?php

use App\Http\Controllers\CustomerReturnController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductModelController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\TradeInController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Pas besoin d'importer les composants Livewire

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'throttle:60,1'])->group(function () {

    // Dashboard
    Route::get('/dashboard', App\Livewire\Dashboard::class)->name('dashboard');

    // Profile (Breeze default)
    Route::view('profile', 'profile')->name('profile');

    /*
    |--------------------------------------------------------------------------
    | Product Models Management (Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('product-models')->name('product-models.')->group(function () {
        Route::get('/', [ProductModelController::class, 'index'])->name('index');
        Route::get('/create', [ProductModelController::class, 'create'])->name('create');
        Route::post('/', [ProductModelController::class, 'store'])->name('store');
        Route::get('/{productModel}', [ProductModelController::class, 'show'])->name('show');
        Route::get('/{productModel}/edit', [ProductModelController::class, 'edit'])->name('edit');
        Route::put('/{productModel}', [ProductModelController::class, 'update'])->name('update');
        Route::delete('/{productModel}', [ProductModelController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Products Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->name('products.')->group(function () {
        // Lecture (tous)
        Route::get('/', function () {
            return view('products.index-livewire');
        })->name('index');
        Route::get('/available', [ProductController::class, 'available'])->name('available');
        Route::get('/needs-attention', [ProductController::class, 'needsAttention'])->name('needs-attention');
        Route::get('/supplier-returns', [ProductController::class, 'supplierReturns'])->name('supplier-returns');
        Route::get('/search/imei', [ProductController::class, 'searchByImei'])->name('search.imei');
        Route::get('/export', [ProductController::class, 'export'])->name('export');
        Route::get('/create', App\Livewire\Products\CreateProduct::class)->name('create');

        // Création et modification (Admin only)
        Route::middleware('admin')->group(function () {
            // CHANGEMENT ICI : Utiliser Livewire pour la création

            // La route store n'est plus nécessaire avec Livewire
            // Route::post('/', [ProductController::class, 'store'])->name('store');

            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            // Routes personnalisées pour les produits
            Route::post('/{product}/state', [ProductController::class, 'changeState'])->name('change-state');
        });

        // Vente rapide (Vendeurs)
        Route::get('/{product}/quick-sell', [ProductController::class, 'quickSell'])
            ->name('quick-sell')
            ->middleware('vendeur');

        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    });

    // API Search (en dehors du groupe products)
    Route::get('/api/products/search', [ProductController::class, 'apiSearch'])
        ->name('api.products.search')
        ->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | Sales Management (Livewire + Traditional)
    |--------------------------------------------------------------------------
    */
    Route::prefix('sales')->name('sales.')->group(function () {

        // Liste
        Route::get('/', [SaleController::class, 'index'])->name('index');

        // Création avec Livewire (Vendeurs et Admin)
        Route::middleware('vendeur')->group(function () {
            Route::get('/create', \App\Livewire\Sales\CreateSale::class)->name('create');
        });

        // Ventes revendeurs (Admin only) - Livewire
        Route::middleware('admin')->group(function () {
            Route::get('/resellers', \App\Livewire\Sales\ResellerSales::class)->name('resellers');
            Route::get('/pending', [SaleController::class, 'pending'])->name('pending');

            // Paiements en attente - Livewire
            Route::get('/payments/pending', \App\Livewire\Sales\PendingPayments::class)->name('payments.pending');

            // Actions sur les ventes
            Route::post('/{sale}/confirm', [SaleController::class, 'confirm'])->name('confirm');
            Route::post('/{sale}/return', [SaleController::class, 'returnFromReseller'])->name('return');

            // Enregistrement de paiement
            Route::post('/{sale}/payments', [SaleController::class, 'recordPayment'])->name('payments.record');
        });

        // Suppression : Admin ET Vendeur (leurs propres ventes) — policy SalePolicy::delete
        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('destroy');

        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Stock Movements
    |--------------------------------------------------------------------------
    */
    Route::prefix('stock-movements')->name('stock-movements.')->group(function () {
        // Lecture (tous)
        Route::get('/', [StockMovementController::class, 'index'])->name('index');
        Route::get('/{stockMovement}', [StockMovementController::class, 'show'])->name('show');

        // Création (selon type de mouvement)
        Route::post('/', [StockMovementController::class, 'store'])->name('store');

        // Mouvements spécifiques admin
        Route::middleware('admin')->group(function () {
            Route::get('/create/reception', [StockMovementController::class, 'createReception'])->name('create.reception');
            Route::get('/create/adjustment', [StockMovementController::class, 'createAdjustment'])->name('create.adjustment');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Resellers Management (Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('resellers')->name('resellers.')->group(function () {
        Route::get('/', [ResellerController::class, 'index'])->name('index');
        Route::get('/create', [ResellerController::class, 'create'])->name('create');
        Route::post('/', [ResellerController::class, 'store'])->name('store');
        Route::get('/{reseller}', [ResellerController::class, 'show'])->name('show');
        Route::get('/{reseller}/edit', [ResellerController::class, 'edit'])->name('edit');
        Route::put('/{reseller}', [ResellerController::class, 'update'])->name('update');
        Route::delete('/{reseller}', [ResellerController::class, 'destroy'])->name('destroy');

        // Statistiques revendeur
        Route::get('/{reseller}/statistics', [ResellerController::class, 'statistics'])->name('statistics');
    });

    /*
    |--------------------------------------------------------------------------
    | Customer Returns (Admin only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [CustomerReturnController::class, 'index'])->name('index');
        Route::get('/create', [CustomerReturnController::class, 'create'])->name('create');
        Route::post('/', [CustomerReturnController::class, 'store'])->name('store');
        Route::get('/{customerReturn}', [CustomerReturnController::class, 'show'])->name('show');
        Route::post('/{customerReturn}/process', [CustomerReturnController::class, 'processReturnedProduct'])->name('process');
    });

    /*
    |--------------------------------------------------------------------------
    | Supplier Returns (Retours Fournisseurs — Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('supplier-returns')->name('supplier-returns.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SupplierReturnController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\SupplierReturnController::class, 'store'])->name('store');
        Route::get('/{supplierReturn}', [\App\Http\Controllers\SupplierReturnController::class, 'show'])->name('show');
        Route::post('/{supplierReturn}/received', [\App\Http\Controllers\SupplierReturnController::class, 'markAsReceived'])->name('received');
        Route::post('/{supplierReturn}/confirm-replacement', [\App\Http\Controllers\SupplierReturnController::class, 'confirmReplacement'])->name('confirm-replacement');
    });

    /*
    |--------------------------------------------------------------------------
    | Reports & Statistics
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        // Rapports de base (Vendeurs peuvent voir leurs propres stats)
        Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
        Route::get('/weekly', [ReportController::class, 'weekly'])->name('weekly');
        Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');

        // Téléchargement PDF
        Route::get('/download-pdf', [ReportController::class, 'downloadPdf'])->name('download-pdf');

        // Rapports avancés (Admin only)
        Route::middleware('admin')->group(function () {
            Route::get('/overview', [ReportController::class, 'overview'])->name('overview');
            Route::get('/products', [ReportController::class, 'products'])->name('products');
            Route::get('/resellers', [ReportController::class, 'resellers'])->name('resellers');
            Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');

            // Exports
            Route::post('/export/sales', [ReportController::class, 'exportSales'])->name('export.sales');
            Route::post('/export/inventory', [ReportController::class, 'exportInventory'])->name('export.inventory');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Users Management (Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

        // Gestion des rôles
        Route::patch('/{user}/role', [UserController::class, 'updateRole'])->name('role');
    });

    /*
    |--------------------------------------------------------------------------
    | Trade-ins Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('trade-ins')->name('trade-ins.')->group(function () {
        Route::get('/', [TradeInController::class, 'index'])->name('index'); // Accessible Vendeurs + Admins

        // Actions Admin uniquement
        Route::middleware('admin')->group(function () {
            Route::get('/pending', [TradeInController::class, 'pending'])->name('pending');
            Route::get('/{tradeIn}/create-product', [TradeInController::class, 'create'])->name('create-product');
            Route::post('/{tradeIn}/store-product', [TradeInController::class, 'storeProduct'])->name('store-product');
        });
    });
    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');

    /*
    |--------------------------------------------------------------------------
    | Data Imports (Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('imports')->name('imports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ImportController::class, 'index'])->name('index');
        Route::post('/models', [\App\Http\Controllers\ImportController::class, 'storeModels'])->name('models');
        Route::post('/products', [\App\Http\Controllers\ImportController::class, 'storeProducts'])->name('products');
        Route::post('/resellers', [\App\Http\Controllers\ImportController::class, 'storeResellers'])->name('resellers');
        Route::get('/template/{type}', [\App\Http\Controllers\ImportController::class, 'downloadTemplate'])->name('template');
    });

});

/*
|--------------------------------------------------------------------------
| Auth Routes (from Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
