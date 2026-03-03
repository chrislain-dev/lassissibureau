<?php

namespace App\Models;

use App\Enums\CustomerReturnStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerReturn extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'original_sale_id',
        'returned_product_id',
        'exchange_product_id',
        'exchange_sale_id',
        'reason',
        'defect_description',
        'repair_notes',
        'is_exchange',
        'refund_amount',
        'status',
        'supplier_return_id',
        'processed_by',
    ];

    protected $casts = [
        'status'        => CustomerReturnStatus::class,
        'is_exchange'   => 'boolean',
        'refund_amount' => 'decimal:2',
    ];

    /**
     * Configuration de l'audit log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Vente originale
     */
    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }

    /**
     * Produit retourné
     */
    public function returnedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'returned_product_id');
    }

    /**
     * Produit donné en échange
     */
    public function exchangeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'exchange_product_id');
    }

    /**
     * Nouvelle vente créée pour l'échange
     */
    public function exchangeSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'exchange_sale_id');
    }

    /**
     * Utilisateur ayant traité le retour
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Retour fournisseur associé
     */
    public function supplierReturn(): HasOne
    {
        return $this->hasOne(SupplierReturn::class);
    }

    /**
     * Vérifier si un retour fournisseur a été créé
     */
    public function hasSupplierReturn(): bool
    {
        return $this->supplierReturn()->exists();
    }

    /**
     * Vérifier si le workflow est terminé
     */
    public function isClosed(): bool
    {
        return $this->status?->isFinal() ?? false;
    }
    /**
     * Vérifier si c'est un échange (pas un remboursement)
     */
    public function isExchange(): bool
    {
        return $this->is_exchange;
    }

    /**
     * Vérifier si c'est un remboursement
     */
    public function isRefund(): bool
    {
        return ! $this->is_exchange;
    }

    /**
     * Obtenir la différence de prix (échange)
     */
    public function getPriceDifferenceAttribute(): ?float
    {
        if (! $this->is_exchange || ! $this->exchangeProduct) {
            return null;
        }

        return (float) ($this->exchangeProduct->prix_vente - $this->returnedProduct->prix_vente);
    }

    /**
     * Scope pour les échanges
     */
    public function scopeExchanges($query)
    {
        return $query->where('is_exchange', true);
    }

    /**
     * Scope pour les remboursements
     */
    public function scopeRefunds($query)
    {
        return $query->where('is_exchange', false);
    }

    /**
     * Scope pour une période
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope pour aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope pour cette semaine
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope pour ce mois
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
    }

    /**
     * Scope par raison de retour
     */
    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', 'LIKE', "%{$reason}%");
    }
}
