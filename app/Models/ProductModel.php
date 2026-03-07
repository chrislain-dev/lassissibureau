<?php

namespace App\Models;

use App\Enums\ProductCategory;
use App\Enums\ProductConditionType;
use App\Enums\ProductLocation;
use App\Enums\ProductState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductModel extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'brand',
        'description',
        'category',
        'condition_type',
        'image_url',
        'prix_revient_default',
        'prix_vente_default',
        'prix_vente_revendeur',
        'stock_minimum',
        'quantity',
        'quantity_sold',
        'is_active',
    ];

    protected $casts = [
        'category'             => ProductCategory::class,
        'condition_type'       => ProductConditionType::class,
        'prix_revient_default' => 'decimal:2',
        'prix_vente_default'   => 'decimal:2',
        'prix_vente_revendeur' => 'decimal:2',
        'stock_minimum'        => 'integer',
        'quantity'             => 'integer',
        'quantity_sold'        => 'integer',
        'is_active'            => 'boolean',
    ];

    /**
     * Configuration de l'audit log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'brand', 'prix_revient_default', 'prix_vente_default', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Produits individuels de ce modèle
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Produits actuellement en stock (en boutique ou en réparation)
     */
    public function productsInStock(): HasMany
    {
        return $this->products()
            ->whereIn('location', [
                ProductLocation::BOUTIQUE->value,
                ProductLocation::EN_REPARATION->value,
            ]);
    }

    /**
     * Produits disponibles à la vente
     */
    public function productsAvailableForSale(): HasMany
    {
        return $this->products()
            ->whereIn('state', [
                ProductState::DISPONIBLE->value,
                ProductState::REPARE->value,
            ])
            ->where('location', ProductLocation::BOUTIQUE->value);
    }

    /**
     * Produits vendus
     */
    public function productsSold(): HasMany
    {
        return $this->products()
            ->where('state', ProductState::VENDU->value);
    }

    /**
     * Produits chez les revendeurs
     */
    public function productsAtResellers(): HasMany
    {
        return $this->products()
            ->where('location', ProductLocation::CHEZ_REVENDEUR->value);
    }

    /**
     * Produits à réparer ou en réparation
     */
    public function productsInRepair(): HasMany
    {
        return $this->products()
            ->where(function ($query) {
                $query->where('state', ProductState::A_REPARER->value)
                    ->orWhere('location', ProductLocation::EN_REPARATION->value);
            });
    }

    /**
     * Produits chez les clients (vendus et livrés)
     */
    public function productsAtClients(): HasMany
    {
        return $this->products()
            ->where('location', ProductLocation::CHEZ_CLIENT->value);
    }

    /**
     * Quantité totale en stock (boutique + en réparation)
     */
    public function getStockQuantityAttribute(): int
    {
        if ($this->isAccessoire()) {
            return $this->quantity ?? 0;
        }
        return $this->productsInStock()->count();
    }

    /**
     * Quantité disponible à la vente
     */
    public function getAvailableQuantityAttribute(): int
    {
        if ($this->isAccessoire()) {
            return $this->quantity ?? 0;
        }
        return $this->productsAvailableForSale()->count();
    }

    /**
     * Quantité vendue
     */
    public function getSoldQuantityAttribute(): int
    {
        if ($this->isAccessoire()) {
            return $this->quantity_sold ?? 0;
        }
        return $this->productsSold()->count();
    }

    /**
     * Quantité chez les revendeurs
     */
    public function getResellerQuantityAttribute(): int
    {
        return $this->productsAtResellers()->count();
    }

    /**
     * Quantité en réparation
     */
    public function getRepairQuantityAttribute(): int
    {
        return $this->productsInRepair()->count();
    }

    /**
     * Indique si ce modèle est un accessoire (gestion par quantité, sans IMEI/SN)
     */
    public function isAccessoire(): bool
    {
        return $this->category->value === 'accessoire';
    }

    /**
     * Décrémente le stock d'accessoires et incrémente le compteur de ventes.
     * Lève une exception si le stock est insuffisant.
     */
    public function decrementStock(int $qty = 1): void
    {
        if (! $this->isAccessoire()) {
            throw new \LogicException('decrementStock() ne s\'applique qu\'aux accessoires.');
        }

        if ($this->quantity < $qty) {
            throw new \Exception("Stock insuffisant pour '{$this->name}'. Disponible : {$this->quantity}, demandé : {$qty}.");
        }

        $this->decrement('quantity', $qty);
        $this->increment('quantity_sold', $qty);
    }

    /**
     * Incrémente le stock d'accessoires (annulation de vente, retour).
     */
    public function incrementStock(int $qty = 1): void
    {
        if (! $this->isAccessoire()) {
            throw new \LogicException('incrementStock() ne s\'applique qu\'aux accessoires.');
        }

        $this->increment('quantity', $qty);
        $this->decrement('quantity_sold', $qty);
    }

    /**
     * Vérifie si le stock est en dessous du minimum.
     * Pour les accessoires, compare `quantity` directement.
     * Pour les téléphones/tablettes, compte les Product en stock.
     */
    public function isLowStock(): bool
    {
        if ($this->isAccessoire()) {
            return ($this->quantity ?? 0) <= $this->stock_minimum;
        }

        return $this->stock_quantity <= $this->stock_minimum;
    }

    /**
     * Vérifie si le modèle a des produits disponibles
     */
    public function hasAvailableProducts(): bool
    {
        return $this->available_quantity > 0;
    }

    /**
     * Valeur totale du stock (prix revient du modèle × quantité)
     */
    public function getStockValueAttribute(): float
    {
        return (float) ($this->prix_revient_default * $this->stock_quantity);
    }

    /**
     * Valeur potentielle de vente du stock (prix client)
     */
    public function getStockSaleValueAttribute(): float
    {
        return (float) ($this->prix_vente_default * $this->stock_quantity);
    }

    /**
     * Valeur potentielle de vente revendeur du stock
     */
    public function getStockResellerSaleValueAttribute(): float
    {
        return (float) (($this->prix_vente_revendeur ?? $this->prix_vente_default) * $this->stock_quantity);
    }

    /**
     * Bénéfice potentiel du stock
     */
    public function getStockPotentialProfitAttribute(): float
    {
        return $this->stock_sale_value - $this->stock_value;
    }

    /**
     * Indique si ce modèle est en condition "occasion"
     */
    public function isOccasion(): bool
    {
        return $this->condition_type === ProductConditionType::OCCASION;
    }

    /**
     * Scope pour les modèles actifs
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les téléphones uniquement
     */
    public function scopeTelephones(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('category', 'telephone');
    }

    /**
     * Scope pour les accessoires uniquement
     */
    public function scopeAccessoires(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('category', 'accessoire');
    }

    /**
     * Scope pour les modèles en stock bas
     * Utilise une sous-requête pour comparer le stock actuel avec le minimum
     */
    public function scopeLowStock(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->withCount(['productsInStock as current_stock'])
            ->having('current_stock', '<=', \Illuminate\Support\Facades\DB::raw('stock_minimum'));
    }

    /**
     * Scope avec statistiques de stock
     */
    public function scopeWithStockStats(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->withCount([
            'products',
            'productsInStock',
            'productsAvailableForSale',
            'productsSold',
            'productsAtResellers',
            'productsInRepair',
        ]);
    }

    /**
     * Obtenir un résumé complet du stock
     */
    public function getStockSummary(): array
    {
        return [
            'total_products' => $this->products()->count(),
            'in_stock' => $this->stock_quantity,
            'available_for_sale' => $this->available_quantity,
            'sold' => $this->sold_quantity,
            'at_resellers' => $this->reseller_quantity,
            'in_repair' => $this->repair_quantity,
            'stock_value' => $this->stock_value,
            'potential_sale_value' => $this->stock_sale_value,
            'potential_profit' => $this->stock_potential_profit,
            'is_low_stock' => $this->isLowStock(),
            'minimum_stock' => $this->stock_minimum,
        ];
    }
}
