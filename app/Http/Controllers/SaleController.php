<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordPaymentRequest;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        private SaleService $saleService
    ) {}

    /**
     * Display a listing of the resource.
     * ✅ Vendeurs peuvent voir TOUTES les ventes (pour les retours clients)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Sale::class);

        $query = Sale::with(['product.productModel', 'productModel', 'seller', 'reseller']);

        // ✅ SUPPRIMÉ le filtre par vendeur - tous voient toutes les ventes
        // Les vendeurs ont besoin de voir toutes les ventes pour gérer les retours clients
        
        // Filtres
        if ($request->filled('date_from')) {
            $query->where('date_vente_effective', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_vente_effective', '<=', $request->date_to);
        }

        if ($request->filled('sale_type')) {
            $query->where('sale_type', $request->sale_type);
        }

        if ($request->has('is_confirmed')) {
            $query->where('is_confirmed', $request->boolean('is_confirmed'));
        }

        $sales = $query->latest('date_vente_effective')->paginate(20);

        // Statistiques
        $statsQuery = Sale::confirmed();
        
        // ✅ Pour les stats, on filtre par vendeur
        // Mais pour la liste des ventes, on montre tout
        if ($request->user()->isVendeur()) {
            $statsQuery->where('sold_by', $request->user()->id);
        }

        $stats = [
            'today' => (clone $statsQuery)->today()->count(),
            'month' => (clone $statsQuery)->thisMonth()->count(),
        ];

        // Stats admin uniquement (bénéfices masqués pour vendeurs)
        if ($request->user()->isAdmin()) {
            $monthSales = (clone $statsQuery)->thisMonth()->get();
            $stats['revenue'] = $monthSales->sum('prix_vente');
            $stats['profit'] = $monthSales->sum(fn ($sale) => $sale->benefice);
        }

        return view('sales.index', compact('sales', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);

        $sale->load([
            'product.productModel',
            'productModel',
            'seller',
            'reseller',
            'tradeIn.productReceived.productModel',
            'customerReturn',
            'payments.recorder',
        ]);

        return view('sales.show', compact('sale'));
    }

    /**
     * Confirm a reseller sale.
     */
    public function confirm(Request $request, Sale $sale)
    {
        $this->authorize('confirm', $sale);

        if ($sale->is_confirmed) {
            return redirect()
                ->back()
                ->with('error', 'Cette vente est déjà confirmée.');
        }

        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_amount' => ['nullable', 'numeric', 'min:0', 'max:'.$sale->amount_remaining],
            'payment_method' => ['nullable', 'string', 'in:cash,mobile_money,bank_transfer,check'],
        ]);

        try {
            $data = [
                'notes' => $request->notes,
            ];

            if ($request->filled('payment_amount')) {
                $data['payment_amount'] = $request->payment_amount;
                $data['payment_method'] = $request->payment_method ?? 'cash';
            }

            $sale = $this->saleService->confirmResellerSale($sale, $data);

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Vente confirmée avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la confirmation : '.$e->getMessage());
        }
    }

    /**
     * Return a product from reseller to stock.
     */
    public function returnFromReseller(Request $request, Sale $sale)
    {
        $this->authorize('returnFromReseller', $sale);

        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'Le motif du retour est requis.',
            'reason.min' => 'Le motif doit contenir au moins 10 caractères.',
        ]);

        try {
            $sale = $this->saleService->returnFromReseller($sale, $request->reason);

            return redirect()
                ->route('sales.index')
                ->with('success', 'Produit retourné en stock avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors du retour : '.$e->getMessage());
        }
    }

    /**
     * Record a payment for a sale.
     */
    public function recordPayment(RecordPaymentRequest $request, Sale $sale)
    {
        $this->authorize('confirm', $sale);

        try {
            $payment = $this->saleService->recordPayment(
                $sale,
                $request->validated('amount'),
                $request->validated()
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Paiement enregistré avec succès.',
                    'payment' => $payment,
                    'sale' => $sale->fresh(),
                ]);
            }

            return redirect()
                ->route('sales.show', $sale)
                ->with('success', 'Paiement de '.number_format($request->amount, 0, ',', ' ').' FCFA enregistré avec succès.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur : '.$e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Erreur : '.$e->getMessage());
        }
    }

    /**
     * Display pending sales (reseller).
     */
    public function pending()
    {
        $this->authorize('viewPending', Sale::class);

        $sales = $this->saleService->getPendingSales();

        return view('sales.pending', compact('sales'));
    }

    /**
     * Remove the specified resource from storage.
     * Crée un mouvement de stock ANNULATION_VENTE pour la traçabilité.
     */
    public function destroy(Request $request, Sale $sale)
    {
        $this->authorize('delete', $sale);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $reason = $request->input('reason') ?? '';
            $this->saleService->deleteSale($sale, $reason);

            return redirect()
                ->route('sales.index')
                ->with('success', 'Vente supprimée. Le produit est remis en stock et la suppression est tracée dans l\'historique.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la suppression : '.$e->getMessage());
        }
    }
}