<?php

namespace App\Models;

use App\Enums\CustomerReturnStatus;
use App\Enums\SupplierReturnStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplierReturn extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'customer_return_id',
        'motif',
        'date_envoi',
        'date_retour_prevue',
        'date_retour_effective',
        'statut',
        'replacement_product_id',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'statut'                => SupplierReturnStatus::class,
        'date_envoi'            => 'date',
        'date_retour_prevue'    => 'date',
        'date_retour_effective' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Produit envoyé au fournisseur
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Retour client à l'origine de ce retour fournisseur
     */
    public function customerReturn(): BelongsTo
    {
        return $this->belongsTo(CustomerReturn::class);
    }

    /**
     * Produit de remplacement reçu du fournisseur
     */
    public function replacementProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'replacement_product_id');
    }

    /**
     * Utilisateur ayant traité ce retour fournisseur
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Vérifier si le retour est en attente de réponse du fournisseur
     */
    public function isPending(): bool
    {
        return $this->statut === SupplierReturnStatus::EN_ATTENTE;
    }

    /**
     * Vérifier si le produit a été remplacé
     */
    public function isReplaced(): bool
    {
        return $this->statut === SupplierReturnStatus::REMPLACE;
    }

    /**
     * Vérifier si le retour est en retard
     */
    public function isOverdue(): bool
    {
        return $this->date_retour_prevue
            && $this->date_retour_prevue->isPast()
            && $this->statut !== SupplierReturnStatus::REMPLACE;
    }

    /**
     * Scope pour les retours en attente
     */
    public function scopePending($query)
    {
        return $query->where('statut', SupplierReturnStatus::EN_ATTENTE);
    }

    /**
     * Scope pour une période
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_envoi', [$startDate, $endDate]);
    }
}
