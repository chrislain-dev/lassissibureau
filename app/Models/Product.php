<?php

namespace App\Models;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'product_model_id',
        'imei',
        'serial_number',
        'state',
        'location',
        'date_achat',
        'fournisseur',
        'notes',
        'condition',
        'defauts',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'state'     => ProductState::class,
        'location'  => ProductLocation::class,
        'date_achat' => 'date',
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
     * Modèle de produit
     */
    public function productModel(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class);
    }

    /**
     * Mouvements de stock liés
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->orderByDesc('created_at');
    }

    /**
     * Dernier mouvement de stock
     */
    public function lastMovement(): HasOne
    {
        return $this->hasOne(StockMovement::class)->latestOfMany();
    }

    /**
     * Vente associée (si vendu)
     */
    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * Vente en cours (pour produits chez revendeur)
     */
    public function currentSale(): HasOne
    {
        return $this->hasOne(Sale::class)
            ->where('is_confirmed', false)
            ->latestOfMany();
    }

    /**
     * Trade-in associé (si reçu en troc)
     */
    public function tradeIn(): HasOne
    {
        return $this->hasOne(TradeIn::class, 'product_received_id');
    }

    /**
     * Retour client associé
     */
    public function customerReturn(): HasOne
    {
        return $this->hasOne(CustomerReturn::class, 'returned_product_id');
    }

    /**
     * Utilisateur ayant créé le produit
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié le produit
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // -------------------------------------------------------------------------
    // Accesseurs de prix (délégation vers le ProductModel)
    // -------------------------------------------------------------------------

    /**
     * Prix de revient (coût d'achat) hérité du modèle
     */
    public function getPrixAchatAttribute(): float
    {
        return (float) ($this->productModel?->prix_revient_default ?? 0);
    }

    /**
     * Prix de vente client hérité du modèle
     */
    public function getPrixVenteAttribute(): float
    {
        return (float) ($this->productModel?->prix_vente_default ?? 0);
    }

    /**
     * Prix de vente revendeur hérité du modèle
     */
    public function getPrixVenteRevendeurAttribute(): float
    {
        return (float) ($this->productModel?->prix_vente_revendeur ?? $this->getPrixVenteAttribute());
    }

    /**
     * Calculer le bénéfice potentiel (vente client)
     */
    public function getBeneficePotentielAttribute(): float
    {
        return $this->prix_vente - $this->prix_achat;
    }

    /**
     * Calculer le taux de marge
     */
    public function getMargePercentageAttribute(): float
    {
        if ($this->prix_achat == 0) {
            return 0;
        }

        return round(($this->benefice_potentiel / $this->prix_achat) * 100, 2);
    }

    /**
     * Vérifier si le produit est disponible à la vente
     */
    public function isAvailable(): bool
    {
        return $this->state->isAvailable() && $this->location === ProductLocation::BOUTIQUE;
    }

    /**
     * Vérifier si le produit est en stock physique de la boutique
     */
    public function isInStore(): bool
    {
        return $this->location->isInStock();
    }

    /**
     * Scope pour les produits en stock
     */
    public function scopeInStock(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('location', [
            ProductLocation::BOUTIQUE->value,
            ProductLocation::EN_REPARATION->value,
        ]);
    }

    /**
     * Scope pour les produits disponibles à la vente
     */
    public function scopeAvailableForSale(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('state', [
            ProductState::DISPONIBLE->value,
            ProductState::REPARE->value,
        ])->where('location', ProductLocation::BOUTIQUE->value);
    }

    /**
     * Scope pour les produits vendus
     */
    public function scopeSold(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('state', ProductState::VENDU->value);
    }

    /**
     * Scope pour les produits chez revendeurs
     */
    public function scopeChezRevendeur(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('location', ProductLocation::CHEZ_REVENDEUR->value);
    }

    /**
     * Scope pour les produits à réparer
     */
    public function scopeAReparer(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('state', ProductState::A_REPARER->value);
    }

    /**
     * Scope pour les produits en réparation
     */
    public function scopeEnReparation(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('location', ProductLocation::EN_REPARATION->value);
    }

    /**
     * Scope pour recherche par IMEI
     */
    public function scopeByImei(\Illuminate\Database\Eloquent\Builder $query, string $imei): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('imei', $imei);
    }

    /**
     * Scope pour filtrer par état
     */
    public function scopeByState(\Illuminate\Database\Eloquent\Builder $query, ProductState|array $states): \Illuminate\Database\Eloquent\Builder
    {
        if (is_array($states)) {
            return $query->whereIn('state', array_map(fn ($s) => $s->value, $states));
        }

        return $query->where('state', $states->value);
    }

    /**
     * Scope pour filtrer par localisation
     */
    public function scopeByLocation(\Illuminate\Database\Eloquent\Builder $query, ProductLocation|array $locations): \Illuminate\Database\Eloquent\Builder
    {
        if (is_array($locations)) {
            return $query->whereIn('location', array_map(fn ($l) => $l->value, $locations));
        }

        return $query->where('location', $locations->value);
    }

    /**
     * Changer l'état et/ou la localisation et créer un mouvement de stock
     */
    public function changeStateAndLocation(
        string $movementType,
        ?ProductState $newState = null,
        ?ProductLocation $newLocation = null,
        ?int $userId = null,
        ?array $additionalData = []
    ): void {
        $oldState = $this->state;
        $oldLocation = $this->location;

        $updates = ['updated_by' => $userId ?? Auth::id()];

        if ($newState) {
            $updates['state'] = $newState;
        }

        if ($newLocation) {
            $updates['location'] = $newLocation;
        }

        $this->update($updates);

        // Créer le mouvement de stock
        $this->stockMovements()->create(array_merge([
            'type' => $movementType,
            'quantity' => 1,
            'state_before' => $oldState->value,
            'location_before' => $oldLocation->value,
            'state_after' => ($newState ?? $oldState)->value,
            'location_after' => ($newLocation ?? $oldLocation)->value,
            'user_id' => $userId ?? Auth::id(),
        ], $additionalData));
    }
}
