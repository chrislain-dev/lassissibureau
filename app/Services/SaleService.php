<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\SaleType;
use App\Enums\StockMovementType;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\TradeIn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private StockService $stockService
    ) {}

    /**
     * Créer une vente (avec ou sans troc).
     * Supporte deux types de produits :
     *  - Téléphones / tablettes / PC : sélection d'une unité Product individuelle
     *  - Accessoires                   : décrément du stock sur ProductModel (pas de Product individuel)
     */
    public function createSale(array $data): Sale
    {
        // Brancher vers la méthode dédiée aux accessoires
        if (! empty($data['is_accessoire'])) {
            return $this->createAccessoireSale($data);
        }

        return $this->createProductSale($data);
    }

    /**
     * Vente d'un accessoire (stock géré par quantité sur ProductModel).
     */
    private function createAccessoireSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $productModel = ProductModel::lockForUpdate()->findOrFail($data['product_model_id']);

            if (! $productModel->isAccessoire()) {
                throw new \Exception("Ce modèle n'est pas un accessoire.");
            }

            $qtyVendue = (int) ($data['quantity_vendue'] ?? 1);

            // Vérifier le stock
            $productModel->decrementStock($qtyVendue); // lève une exception si insuffisant

            $prixVente   = (float) ($data['prix_vente'] ?? $productModel->prix_vente_default);
            $prixAchat   = (float) ($data['prix_achat_produit'] ?? $productModel->prix_revient_default);
            $amountPaid  = (float) ($data['amount_paid'] ?? $prixVente);

            $sale = Sale::create([
                'product_id'           => null,
                'product_model_id'     => $productModel->id,
                'quantity_vendue'      => $qtyVendue,
                'sale_type'            => $data['sale_type'] ?? SaleType::ACHAT_DIRECT->value,
                'prix_vente'           => $prixVente,
                'prix_achat_produit'   => $prixAchat,
                'client_name'          => $data['client_name'] ?? null,
                'client_phone'         => $data['client_phone'] ?? null,
                'reseller_id'          => null, // accessoires : ventes directes uniquement
                'date_vente_effective' => $data['date_vente_effective'],
                'is_confirmed'         => true, // toujours confirmé (vente directe)
                'payment_status'       => PaymentStatus::PAID,
                'amount_paid'          => $prixVente,
                'amount_remaining'     => 0,
                'payment_due_date'     => null,
                'sold_by'              => $data['sold_by'],
                'notes'                => $data['notes'] ?? null,
            ]);

            // Enregistrer le paiement
            if ($amountPaid > 0) {
                $this->recordPayment($sale, $amountPaid, [
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'reference'      => $data['payment_reference'] ?? null,
                    'notes'          => 'Paiement initial (accessoire)',
                ]);
            }

            // Mouvement de stock au niveau du modèle
            StockMovement::create([
                'product_id'       => null,
                'product_model_id' => $productModel->id,
                'type'             => StockMovementType::VENTE_DIRECTE->value,
                'quantity'         => -$qtyVendue,
                'state_before'     => null,
                'location_before'  => null,
                'state_after'      => null,
                'location_after'   => null,
                'sale_id'          => $sale->id,
                'user_id'          => $data['sold_by'],
                'notes'            => "Vente accessoire x{$qtyVendue} — {$productModel->name}",
            ]);

            return $sale->fresh(['productModel', 'seller', 'payments']);
        });
    }

    /**
     * Vente d'un produit individuel (téléphone, tablette, PC). Ancien createSale().
     * Le prix de vente est déterminé automatiquement depuis le ProductModel :
     *  - Vente client directe  : prix_vente_default
     *  - Vente revendeur        : prix_vente_revendeur
     */
    private function createProductSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Verrouiller le produit pour éviter les conditions de course (double vente)
            $product = Product::lockForUpdate()->with('productModel')->findOrFail($data['product_id']);

            if (! $product->isAvailable()) {
                throw new \Exception('Ce produit n\'est pas disponible à la vente.');
            }

            $isResellerSale = isset($data['reseller_id']) && $data['reseller_id'];

            // Déterminer le prix de vente depuis le ProductModel
            $prixVente = $isResellerSale
                ? $product->prix_vente_revendeur   // prix partenaire
                : $product->prix_vente;             // prix client

            // Prix de revient pour calcul marge
            $prixAchat = $product->prix_achat;

            // Déterminer le statut de paiement
            $paymentStatus = PaymentStatus::PAID;
            $amountPaid    = $prixVente;
            $amountRemaining = 0;
            $paymentDueDate  = null;

            // Si c'est un revendeur avec paiement différé
            if ($isResellerSale) {
                $paymentStatus   = PaymentStatus::from($data['payment_status'] ?? 'unpaid');
                $amountPaid      = $data['amount_paid'] ?? 0;
                $amountRemaining = $prixVente - $amountPaid;
                $paymentDueDate  = $data['payment_due_date'] ?? now()->addDays(30);
            }

            // Créer la vente
            $sale = Sale::create([
                'product_id'          => $data['product_id'],
                'sale_type'           => $data['sale_type'],
                'prix_vente'          => $prixVente,
                'prix_achat_produit'  => $prixAchat,
                'client_name'         => $data['client_name'] ?? null,
                'client_phone'        => $data['client_phone'] ?? null,
                'reseller_id'         => $data['reseller_id'] ?? null,
                'date_depot_revendeur' => $data['date_depot_revendeur'] ?? null,
                'date_vente_effective' => $data['date_vente_effective'],
                'is_confirmed'        => $data['is_confirmed'],
                'payment_status'      => $paymentStatus,
                'amount_paid'         => 0,
                'amount_remaining'    => $prixVente,
                'payment_due_date'    => $paymentDueDate,
                'sold_by'             => $data['sold_by'],
                'notes'               => $data['notes'] ?? null,
            ]);

            // Enregistrer le paiement initial si montant > 0
            if ($amountPaid > 0) {
                $this->recordPayment($sale, $amountPaid, [
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'reference'      => $data['payment_reference'] ?? null,
                    'notes'          => 'Paiement initial',
                ]);
            }

            // Gérer le troc si présent
            if (isset($data['has_trade_in']) && $data['has_trade_in'] && $data['sale_type'] === SaleType::TROC->value) {
                $this->handleTradeIn($sale, $data['trade_in']);
            }

            // Déterminer état et localisation
            if ($data['is_confirmed']) {
                $newState    = ProductState::VENDU;
                $newLocation = ProductLocation::CHEZ_CLIENT;
                $movementType = $data['sale_type'] === SaleType::ACHAT_DIRECT->value
                    ? StockMovementType::VENTE_DIRECTE
                    : StockMovementType::VENTE_TROC;
            } else {
                // Si non confirmé, c'est forcément un dépôt revendeur
                $newState     = ProductState::DISPONIBLE;
                $newLocation  = ProductLocation::CHEZ_REVENDEUR;
                $movementType = StockMovementType::DEPOT_REVENDEUR;
            }

            $product->changeStateAndLocation(
                $movementType->value,
                $newState,
                $newLocation,
                $data['sold_by'],
                [
                    'sale_id'     => $sale->id,
                    'reseller_id' => $data['reseller_id'] ?? null,
                    'notes'       => 'Vente créée - '.$sale->sale_type->label(),
                ]
            );

            return $sale->fresh(['product.productModel', 'tradeIn', 'reseller', 'seller', 'payments']);
        });
    }

    /**
     * Gérer le troc (produit repris).
     * Note: Le produit reçu en troc n'est PAS créé automatiquement
     * car il nécessite la sélection manuelle du product_model_id par l'admin.
     */
    private function handleTradeIn(Sale $sale, array $tradeInData): void
    {
        TradeIn::create([
            'sale_id'              => $sale->id,
            'product_received_id'  => null,
            'valeur_reprise'       => $tradeInData['valeur_reprise'],
            'complement_especes'   => $tradeInData['complement_especes'],
            'imei_recu'            => $tradeInData['imei_recu'],
            'modele_recu'          => $tradeInData['modele_recu'],
            'etat_recu'            => $tradeInData['etat_recu'] ?? null,
            'needs_repair'         => $tradeInData['needs_repair'] ?? false,
            'repair_notes'         => $tradeInData['repair_notes'] ?? null,
            'repair_status'        => ($tradeInData['needs_repair'] ?? false)
                                        ? 'en_attente_reparation'
                                        : null,
        ]);
    }

    /**
     * Enregistrer un paiement
     */
    public function recordPayment(Sale $sale, float $amount, array $data = []): Payment
    {
        return DB::transaction(function () use ($sale, $amount, $data) {
            $payment = Payment::create([
                'sale_id'        => $sale->id,
                'amount'         => $amount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_date'   => $data['payment_date'] ?? now(),
                'reference'      => $data['reference'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'recorded_by'    => Auth::id(),
            ]);

            // Mettre à jour le statut de paiement de la vente
            $newAmountPaid     = $sale->amount_paid + $amount;
            $newAmountRemaining = $sale->prix_vente - $newAmountPaid;

            $newStatus = $newAmountRemaining <= 0
                ? PaymentStatus::PAID
                : ($newAmountPaid > 0 ? PaymentStatus::PARTIAL : PaymentStatus::UNPAID);

            $sale->update([
                'amount_paid'         => $newAmountPaid,
                'amount_remaining'    => max(0, $newAmountRemaining),
                'payment_status'      => $newStatus,
                'final_payment_date'  => $newStatus === PaymentStatus::PAID ? now() : null,
            ]);

            return $payment->fresh(['sale', 'recorder']);
        });
    }

    /**
     * Supprimer une vente et remettre le produit en stock.
     * Supporte les ventes de téléphones ET les ventes d'accessoires.
     */
    public function deleteSale(Sale $sale, string $reason = ''): ?Product
    {
        return DB::transaction(function () use ($sale, $reason) {
            // --- Vente d'accessoire ---
            if ($sale->isAccessoireSale()) {
                $productModel = $sale->productModel;
                $qtyVendue    = $sale->quantity_vendue ?? 1;

                // Remettre le stock
                $productModel->incrementStock($qtyVendue);

                $notes = sprintf(
                    'Annulation vente accessoire #%d — %s x%d — Montant: %s FCFA%s',
                    $sale->id,
                    $productModel->name,
                    $qtyVendue,
                    number_format($sale->prix_vente, 0, ',', ' '),
                    $reason ? ' — Motif: '.$reason : ''
                );

                StockMovement::create([
                    'product_id'       => null,
                    'product_model_id' => $productModel->id,
                    'type'             => StockMovementType::ANNULATION_VENTE->value,
                    'quantity'         => $qtyVendue,
                    'state_before'     => null,
                    'location_before'  => null,
                    'state_after'      => null,
                    'location_after'   => null,
                    'sale_id'          => $sale->id,
                    'user_id'          => Auth::id(),
                    'notes'            => $notes,
                ]);

                $sale->payments()->delete();
                $sale->delete();

                return null;
            }

            // --- Vente de produit individuel (comportement original) ---
            $product = $sale->product;

            $typeLabel = match ($sale->sale_type->value ?? $sale->sale_type) {
                'achat_direct' => 'vente directe client',
                'troc'         => 'vente avec troc',
                default        => 'vente',
            };

            $notes = sprintf(
                'Annulation de %s — Vente #%d — Montant: %s FCFA%s%s',
                $typeLabel,
                $sale->id,
                number_format($sale->prix_vente, 0, ',', ' '),
                $sale->client_name ? ' — Client: '.$sale->client_name : '',
                $reason ? ' — Motif: '.$reason : ''
            );

            // Remettre le produit en stock
            $this->stockService->createMovement([
                'product_id'      => $product->id,
                'type'            => StockMovementType::ANNULATION_VENTE->value,
                'quantity'        => 1,
                'state_before'    => $product->state->value,
                'location_before' => $product->location->value,
                'state_after'     => ProductState::DISPONIBLE->value,
                'location_after'  => ProductLocation::BOUTIQUE->value,
                'user_id'         => Auth::id(),
                'sale_id'         => $sale->id,
                'notes'           => $notes,
            ]);

            $sale->payments()->delete();
            $sale->delete();

            return $product->fresh();
        });
    }

    /**
     * Créer le produit repris dans un troc.
     * Le troc reçu est lié à un ProductModel existant — les prix viennent du modèle.
     * Si needs_repair = true, le produit est créé à l'état A_REPARER/EN_REPARATION.
     */
    public function createTradeInProduct(
        TradeIn $tradeIn,
        int $productModelId,
        ?string $notes = null
    ): Product {
        return DB::transaction(function () use ($tradeIn, $productModelId, $notes) {
            $initialState    = $tradeIn->needs_repair ? ProductState::A_REPARER    : ProductState::DISPONIBLE;
            $initialLocation = $tradeIn->needs_repair ? ProductLocation::EN_REPARATION : ProductLocation::BOUTIQUE;
            $movementType    = $tradeIn->needs_repair
                ? StockMovementType::ENVOI_REPARATION
                : StockMovementType::TROC_RECU;

            // Créer le produit — les prix sont portés par le ProductModel
            $product = Product::create([
                'product_model_id' => $productModelId,
                'imei'             => $tradeIn->imei_recu,
                'state'            => $initialState->value,
                'location'         => $initialLocation->value,
                'date_achat'       => now(),
                'notes'            => $notes ?? 'Reçu en troc - Vente #'.$tradeIn->sale_id,
                'condition'        => 'troc',
                'defauts'          => $tradeIn->etat_recu,
                'created_by'       => Auth::id(),
            ]);

            // Lier le produit au troc
            $tradeIn->update([
                'product_received_id' => $product->id,
                'repair_status'       => $tradeIn->needs_repair ? 'en_reparation' : null,
            ]);

            // Créer le mouvement de stock
            $this->stockService->createMovement([
                'product_id'     => $product->id,
                'type'           => $movementType->value,
                'quantity'       => 1,
                'state_before'   => null,
                'location_before' => null,
                'state_after'    => $initialState->value,
                'location_after' => $initialLocation->value,
                'user_id'        => Auth::id(),
                'notes'          => $tradeIn->needs_repair
                    ? 'Troc reçu - envoi en réparation - Vente #'.$tradeIn->sale_id
                    : 'Produit reçu en troc - Vente #'.$tradeIn->sale_id,
            ]);

            event(new \App\Events\TradeInProcessed($tradeIn));

            return $product->fresh('productModel');
        });
    }

    /**
     * Confirmer une vente revendeur.
     */
    public function confirmResellerSale(Sale $sale, array $data = []): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            // Verrouiller la vente et le produit
            $sale = Sale::lockForUpdate()->findOrFail($sale->id);
            $product = Product::lockForUpdate()->findOrFail($sale->product_id);

            if ($sale->is_confirmed) {
                throw new \Exception('Cette vente est déjà confirmée.');
            }

            if (! $sale->reseller_id) {
                throw new \Exception('Cette vente n\'est pas une vente revendeur.');
            }

            // Si un paiement est fourni, l'enregistrer
            if (isset($data['payment_amount']) && $data['payment_amount'] > 0) {
                $this->recordPayment($sale, $data['payment_amount'], [
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'notes' => 'Paiement lors de la confirmation',
                ]);
            }

            // Confirmer la vente
            $sale->update([
                'is_confirmed' => true,
                'date_confirmation_vente' => now(),
                'notes' => $data['notes'] ?? $sale->notes,
            ]);

            // Changer l'état du produit à VENDU
            $sale->product->changeStateAndLocation(
                StockMovementType::VENTE_DIRECTE->value,
                ProductState::VENDU,
                ProductLocation::CHEZ_CLIENT,
                Auth::id(),
                [
                    'sale_id' => $sale->id,
                    'reseller_id' => $sale->reseller_id,
                    'notes' => 'Confirmation vente revendeur',
                ]
            );

            return $sale->fresh(['product.productModel', 'reseller', 'seller', 'payments']);
        });
    }

    /**
     * Retourner un produit du revendeur au stock.
     */
    public function returnFromReseller(Sale $sale, string $reason): Sale
    {
        return DB::transaction(function () use ($sale, $reason) {
            if ($sale->is_confirmed) {
                throw new \Exception('Impossible de retourner une vente déjà confirmée.');
            }

            if (! $sale->reseller_id) {
                throw new \Exception('Cette vente n\'est pas une vente revendeur.');
            }

            // Si des paiements ont été faits, ils doivent être remboursés
            if ($sale->amount_paid > 0) {
                $sale->update([
                    'notes' => ($sale->notes ? $sale->notes."\n" : '')
                        ."RETOUR REVENDEUR: {$reason}\n"
                        .'Montant à rembourser: '.number_format($sale->amount_paid, 0, ',', ' ').' FCFA',
                ]);
            } else {
                $sale->update([
                    'notes' => ($sale->notes ? $sale->notes."\n" : '').'RETOUR REVENDEUR: '.$reason,
                ]);
            }

            // Ramener le produit en stock
            $sale->product->changeStateAndLocation(
                StockMovementType::RETOUR_REVENDEUR->value,
                ProductState::DISPONIBLE,
                ProductLocation::BOUTIQUE,
                Auth::id(),
                [
                    'sale_id' => $sale->id,
                    'reseller_id' => $sale->reseller_id,
                    'notes' => 'Retour du revendeur: '.$reason,
                ]
            );

            // Supprimer la vente (soft delete)
            $sale->delete();

            return $sale->fresh(['product.productModel', 'reseller', 'seller', 'payments']);
        });
    }

    /**
     * Obtenir les ventes impayées ou partiellement payées
     */
    public function getUnpaidSales(?int $resellerId = null)
    {
        $query = Sale::with(['product.productModel', 'reseller', 'seller', 'payments'])
            ->whereIn('payment_status', [PaymentStatus::UNPAID, PaymentStatus::PARTIAL])
            ->orderBy('payment_due_date', 'asc');

        if ($resellerId) {
            $query->where('reseller_id', $resellerId);
        }

        return $query->get();
    }

    /**
     * Obtenir les statistiques de paiement
     */
    public function getPaymentStats(?int $resellerId = null): array
    {
        $query = Sale::confirmed();

        if ($resellerId) {
            $query->where('reseller_id', $resellerId);
        }

        $sales = $query->get();

        return [
            'total_sales_amount' => $sales->sum('prix_vente'),
            'total_paid' => $sales->sum('amount_paid'),
            'total_remaining' => $sales->sum('amount_remaining'),
            'unpaid_count' => $sales->where('payment_status', PaymentStatus::UNPAID)->count(),
            'partial_count' => $sales->where('payment_status', PaymentStatus::PARTIAL)->count(),
            'paid_count' => $sales->where('payment_status', PaymentStatus::PAID)->count(),
        ];
    }

    /**
     * Obtenir les statistiques de vente pour une période.
     */
    public function getSalesStats($startDate, $endDate, ?int $userId = null): array
    {
        $query = Sale::confirmed()->betweenDates($startDate, $endDate);

        if ($userId) {
            $query->where('sold_by', $userId);
        }

        $sales = $query->get();

        return [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('prix_vente'),
            'total_profit' => $sales->sum(fn ($sale) => $sale->benefice),
            'average_sale_price' => $sales->avg('prix_vente') ?? 0,
            'average_profit_per_sale' => $sales->count() > 0
                ? $sales->sum(fn ($sale) => $sale->benefice) / $sales->count()
                : 0,
            'sales_by_type' => $sales->groupBy('sale_type')->map->count()->toArray(),
        ];
    }

    /**
     * Obtenir les ventes en attente (revendeurs).
     */
    public function getPendingSales()
    {
        return Sale::with(['product.productModel', 'reseller', 'seller'])
            ->pending()
            ->orderBy('date_depot_revendeur', 'desc')
            ->get();
    }

    /**
     * Obtenir les produits actuellement chez les revendeurs.
     */
    public function getProductsAtResellers(?int $resellerId = null)
    {
        $query = Product::where('location', ProductLocation::CHEZ_REVENDEUR->value)
            ->where('state', ProductState::DISPONIBLE->value)
            ->with(['productModel', 'currentSale.reseller']);

        if ($resellerId) {
            $query->whereHas('currentSale', function ($q) use ($resellerId) {
                $q->where('reseller_id', $resellerId)
                    ->where('is_confirmed', false);
            });
        }

        return $query->get();
    }

    /**
     * Obtenir les trocs sans produit créé.
     */
    public function getTradeInsWithoutProduct()
    {
        return TradeIn::with(['sale.product.productModel', 'sale.seller'])
            ->whereNull('product_received_id')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtenir les statistiques d'un vendeur.
     */
    public function getSellerStats(int $userId, $startDate = null, $endDate = null): array
    {
        $query = Sale::confirmed()->where('sold_by', $userId);

        if ($startDate) {
            $query->where('date_vente_effective', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date_vente_effective', '<=', $endDate);
        }

        $sales = $query->get();

        return [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('prix_vente'),
            'today_sales' => Sale::confirmed()->where('sold_by', $userId)->today()->count(),
            'week_sales' => Sale::confirmed()->where('sold_by', $userId)->thisWeek()->count(),
            'month_sales' => Sale::confirmed()->where('sold_by', $userId)->thisMonth()->count(),
        ];
    }
}
