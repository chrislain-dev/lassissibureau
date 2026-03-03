<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TradeIn extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'sale_id',
        'product_received_id',
        'valeur_reprise',
        'complement_especes',
        'imei_recu',
        'modele_recu',
        'etat_recu',
        'needs_repair',
        'repair_notes',
        'repair_status',
    ];

    protected $casts = [
        'valeur_reprise'    => 'decimal:2',
        'complement_especes' => 'decimal:2',
        'needs_repair'      => 'boolean',
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
     * Vente associée
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Produit reçu en troc (maintenant dans le stock)
     */
    public function productReceived(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_received_id');
    }

    /**
     * Calculer le montant total du troc
     */
    public function getMontantTotalAttribute(): float
    {
        return (float) ($this->valeur_reprise + $this->complement_especes);
    }

    /**
     * Vérifier si c'est un troc pur (sans complément)
     */
    public function isPureTradeIn(): bool
    {
        return $this->complement_especes == 0;
    }

    /**
     * Vérifier si c'est un troc avec complément
     */
    public function hasComplement(): bool
    {
        return $this->complement_especes > 0;
    }

    /**
     * Scope pour les trocs avec complément
     */
    public function scopeWithComplement($query)
    {
        return $query->where('complement_especes', '>', 0);
    }

    /**
     * Scope pour les trocs purs
     */
    public function scopePureTradeIns($query)
    {
        return $query->where('complement_especes', 0);
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
     * Vérifier si le produit reçu a été créé
     */
    public function hasProductReceived(): bool
    {
        return $this->product_received_id !== null;
    }

    /**
     * Vérifier si le troc est en attente de traitement
     */
    public function isPending(): bool
    {
        return $this->product_received_id === null;
    }

    /**
     * Vérifier si le téléphone reçu en troc nécessite une réparation
     */
    public function needsRepair(): bool
    {
        return (bool) $this->needs_repair;
    }

    /**
     * Vérifier si la réparation est terminée
     */
    public function isRepaired(): bool
    {
        return $this->repair_status === 'repare';
    }
    /**
     * Scope pour les trocs en attente (sans produit créé)
     */
    public function scopePending($query)
    {
        return $query->whereNull('product_received_id');
    }

    /**
     * Scope pour les trocs traités (avec produit créé)
     */
    public function scopeProcessed($query)
    {
        return $query->whereNotNull('product_received_id');
    }
}
