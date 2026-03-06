<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\SaleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'product_id',
        'product_model_id',
        'quantity_vendue',
        'sale_type',
        'prix_vente',
        'prix_achat_produit',
        'client_name',
        'client_phone',
        'reseller_id',
        'date_depot_revendeur',
        'date_confirmation_vente',
        'is_confirmed',
        'payment_status',
        'amount_paid',
        'amount_remaining',
        'payment_due_date',
        'final_payment_date',
        'date_vente_effective',
        'sold_by',
        'notes',
    ];

    protected $casts = [
        'sale_type'              => SaleType::class,
        'payment_status'         => PaymentStatus::class,
        'prix_vente'             => 'decimal:2',
        'prix_achat_produit'     => 'decimal:2',
        'amount_paid'            => 'decimal:2',
        'amount_remaining'       => 'decimal:2',
        'date_depot_revendeur'   => 'date',
        'date_confirmation_vente'=> 'date',
        'payment_due_date'       => 'date',
        'final_payment_date'     => 'date',
        'date_vente_effective'   => 'date',
        'is_confirmed'           => 'boolean',
        'quantity_vendue'        => 'integer',
    ];

    /**
     * Configuration de l'audit log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Produit vendu (null pour les accessoires)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Modèle de produit (toujours renseigné, que ce soit un téléphone ou un accessoire)
     */
    public function productModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class);
    }

    /**
     * Indique si c'est une vente d'accessoire (pas d'unité individuelle)
     */
    public function isAccessoireSale(): bool
    {
        return $this->product_id === null && $this->product_model_id !== null;
    }

    /**
     * Revendeur (si vente via revendeur)
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    /**
     * Vendeur
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    /**
     * Trade-in associé (si vente avec troc)
     */
    public function tradeIn(): HasOne
    {
        return $this->hasOne(TradeIn::class);
    }

    /**
     * Retour client associé
     */
    public function customerReturn(): HasOne
    {
        return $this->hasOne(CustomerReturn::class, 'original_sale_id');
    }

    /**
     * Paiements associés
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'desc');
    }

    /**
     * Calculer le bénéfice réel
     */
    public function getBeneficeAttribute(): float
    {
        return (float) ($this->prix_vente - $this->prix_achat_produit);
    }

    /**
     * Calculer le taux de marge
     */
    public function getMargePercentageAttribute(): float
    {
        if ($this->prix_achat_produit == 0) {
            return 0;
        }

        return round(($this->benefice / $this->prix_achat_produit) * 100, 2);
    }

    /**
     * Obtenir le montant en espèces reçu
     */
    public function getMontantEspecesAttribute(): float
    {
        if ($this->sale_type === SaleType::ACHAT_DIRECT) {
            return (float) $this->prix_vente;
        }

        if ($this->tradeIn) {
            return (float) $this->tradeIn->complement_especes;
        }

        return 0;
    }

    /**
     * Vérifier si c'est une vente avec troc
     */
    public function hasTradeIn(): bool
    {
        return $this->sale_type === SaleType::TROC;
    }

    /**
     * Vérifier si c'est une vente via revendeur
     */
    public function isResellerSale(): bool
    {
        return $this->reseller_id !== null;
    }

    /**
     * Vérifier si la vente est confirmée
     */
    public function isConfirmed(): bool
    {
        return $this->is_confirmed === true;
    }

    /**
     * Vérifier si la vente est en attente (chez revendeur)
     */
    public function isPending(): bool
    {
        return $this->is_confirmed === false && $this->reseller_id !== null;
    }

    /**
     * Vérifier si le paiement est complet
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === PaymentStatus::PAID;
    }

    /**
     * Vérifier si c'est une vente à crédit
     */
    public function isCreditSale(): bool
    {
        return $this->reseller_id && in_array($this->payment_status, [
            PaymentStatus::UNPAID,
            PaymentStatus::PARTIAL,
        ]);
    }

    /**
     * Vérifier si le paiement est en retard
     */
    public function isPaymentOverdue(): bool
    {
        return $this->payment_due_date
            && $this->payment_due_date->isPast()
            && ! $this->isFullyPaid();
    }

    /**
     * Scope pour les ventes confirmées
     */
    public function scopeConfirmed(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_confirmed', true);
    }

    /**
     * Scope pour les ventes en attente (chez revendeur)
     */
    public function scopePending(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_confirmed', false)
            ->whereNotNull('reseller_id');
    }

    /**
     * Scope pour les ventes impayées
     */
    public function scopeUnpaid(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('payment_status', PaymentStatus::UNPAID);
    }

    /**
     * Scope pour les ventes partiellement payées
     */
    public function scopePartiallyPaid(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('payment_status', PaymentStatus::PARTIAL);
    }

    /**
     * Scope pour les ventes avec paiement en attente
     */
    public function scopeWithPendingPayment(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('payment_status', [
            PaymentStatus::UNPAID,
            PaymentStatus::PARTIAL,
        ]);
    }

    /**
     * Scope pour les paiements en retard
     */
    public function scopeOverdue(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('payment_status', [
            PaymentStatus::UNPAID,
            PaymentStatus::PARTIAL,
        ])
            ->where('payment_due_date', '<', today());
    }

    /**
     * Scope pour les ventes d'une date spécifique
     */
    public function scopeForDate(\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereDate('date_vente_effective', $date);
    }

    /**
     * Scope pour les ventes d'une période
     */
    public function scopeBetweenDates(\Illuminate\Database\Eloquent\Builder $query, $startDate, $endDate): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereBetween('date_vente_effective', [$startDate, $endDate]);
    }

    /**
     * Scope pour les ventes du jour
     */
    public function scopeToday(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereDate('date_vente_effective', today());
    }

    /**
     * Scope pour les ventes de la semaine
     */
    public function scopeThisWeek(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereBetween('date_vente_effective', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope pour les ventes du mois
     */
    public function scopeThisMonth(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereYear('date_vente_effective', now()->year)
            ->whereMonth('date_vente_effective', now()->month);
    }

    /**
     * Scope pour les ventes par type
     */
    public function scopeByType(\Illuminate\Database\Eloquent\Builder $query, SaleType $type): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('sale_type', $type->value);
    }

    /**
     * Scope pour les ventes par vendeur
     */
    public function scopeBySeller(\Illuminate\Database\Eloquent\Builder $query, int $sellerId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('sold_by', $sellerId);
    }

    /**
     * Scope pour les ventes par revendeur
     */
    public function scopeByReseller(\Illuminate\Database\Eloquent\Builder $query, int $resellerId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('reseller_id', $resellerId);
    }
}
