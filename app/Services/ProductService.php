<?php

namespace App\Services;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\ProductModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Créer un nouveau produit avec mouvement de stock initial.
     */
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // Créer le produit
            $product = Product::create($data);

            // Créer le mouvement de stock initial (réception)
            $product->stockMovements()->create([
                'type' => StockMovementType::RECEPTION_FOURNISSEUR->value,
                'quantity' => 1,
                'state_before' => null,
                'location_before' => null,
                'state_after' => $data['state'],
                'location_after' => $data['location'],
                'user_id' => $data['created_by'],
                'notes' => 'Création du produit - Réception initiale',
            ]);

            return $product->fresh(['productModel', 'stockMovements']);
        });
    }

    /**
     * Mettre à jour un produit.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh(['productModel', 'stockMovements']);
    }

    /**
     * Changer l'état et/ou la localisation d'un produit avec mouvement de stock.
     */
    public function changeStateAndLocation(
        Product $product,
        StockMovementType $movementType,
        int $userId,
        ?ProductState $newState = null,
        ?ProductLocation $newLocation = null,
        array $additionalData = []
    ): Product {
        // Au moins un des deux doit être fourni
        if (! $newState && ! $newLocation) {
            throw new \InvalidArgumentException('Au moins un des paramètres $newState ou $newLocation doit être fourni.');
        }

        $product->changeStateAndLocation(
            $movementType->value,
            $newState,
            $newLocation,
            $userId,
            $additionalData
        );

        return $product->fresh(['productModel', 'stockMovements']);
    }

    /**
     * Déplacer un produit vers une nouvelle localisation (sans changer l'état).
     */
    public function moveProduct(
        Product $product,
        ProductLocation $newLocation,
        int $userId,
        ?string $notes = null
    ): Product {
        $movementType = match ($newLocation) {
            ProductLocation::BOUTIQUE => StockMovementType::RETOUR_REVENDEUR, // ou RETOUR_CLIENT selon contexte
            ProductLocation::CHEZ_REVENDEUR => StockMovementType::DEPOT_REVENDEUR,
            ProductLocation::EN_REPARATION => StockMovementType::ENVOI_REPARATION,
            ProductLocation::FOURNISSEUR => StockMovementType::RETOUR_FOURNISSEUR,
            ProductLocation::CHEZ_CLIENT => StockMovementType::VENTE_DIRECTE,
            default => throw new \InvalidArgumentException('Type de mouvement non défini pour cette localisation'),
        };

        return $this->changeStateAndLocation(
            $product,
            $movementType,
            $userId,
            null, // Ne change pas l'état
            $newLocation,
            ['notes' => $notes]
        );
    }

    /**
     * Marquer un produit comme réparé.
     */
    public function markAsRepaired(Product $product, int $userId, ?string $notes = null): Product
    {
        return $this->changeStateAndLocation(
            $product,
            StockMovementType::RETOUR_REPARATION,
            $userId,
            ProductState::REPARE,
            ProductLocation::BOUTIQUE, // Le ramener en boutique
            ['notes' => $notes ?? 'Produit réparé']
        );
    }

    /**
     * Envoyer un produit en réparation.
     */
    public function sendToRepair(Product $product, int $userId, ?string $notes = null): Product
    {
        return $this->changeStateAndLocation(
            $product,
            StockMovementType::ENVOI_REPARATION,
            $userId,
            ProductState::A_REPARER,
            ProductLocation::EN_REPARATION,
            ['notes' => $notes ?? 'Envoi en réparation']
        );
    }

    /**
     * Rechercher un produit par IMEI.
     */
    public function findByImei(string $imei): ?Product
    {
        // Nettoyer l'IMEI
        $cleanImei = Str::replaceMatches('/[^0-9]/', '', $imei);

        return Product::with(['productModel', 'sale', 'stockMovements'])
            ->where('imei', $cleanImei)
            ->first();
    }

    /**
     * Obtenir les produits en stock bas.
     */
    public function getLowStockProducts()
    {
        return ProductModel::with(['productsInStock'])
            ->get()
            ->filter(fn ($model) => $model->isLowStock())
            ->map(function ($model) {
                return [
                    'model' => $model,
                    'current_stock' => $model->stock_quantity,
                    'minimum_stock' => $model->stock_minimum,
                    'deficit' => $model->stock_minimum - $model->stock_quantity,
                ];
            });
    }

    /**
     * Obtenir les statistiques d'un produit.
     */
    public function getProductStats(Product $product): array
    {
        return [
            'total_movements' => $product->stockMovements()->count(),
            'benefice_potentiel' => $product->benefice_potentiel,
            'marge_percentage' => $product->marge_percentage,
            'days_in_stock_human' => $product->created_at
                ? $product->created_at->diffForHumans()
                : null,
            'is_available' => $product->isAvailable(),
            'is_in_store' => $product->isInStore(),
            'current_state' => $product->state->label(),
            'current_location' => $product->location->label(),
        ];
    }

    /**
     * Supprimer un produit (soft delete).
     */
    public function deleteProduct(Product $product): bool
    {
        // Vérifier que le produit n'est pas vendu ou chez un revendeur
        if ($product->state === ProductState::VENDU || $product->location === ProductLocation::CHEZ_REVENDEUR) {
            throw new \Exception('Impossible de supprimer un produit vendu ou chez un revendeur.');
        }

        return $product->delete();
    }

    /**
     * Obtenir les produits disponibles à la vente.
     */
    public function getAvailableProducts()
    {
        return Product::availableForSale()
            ->with(['productModel'])
            ->orderBy('date_achat', 'desc')
            ->get();
    }

    /**
     * Obtenir les produits nécessitant une attention.
     */
    public function getProductsNeedingAttention()
    {
        return Product::whereIn('state', [
            ProductState::A_REPARER->value,
            ProductState::RETOUR->value,
            ProductState::PERDU->value,
        ])->with(['productModel', 'lastMovement'])
            ->orderBy('updated_at', 'asc')
            ->get();
    }
}
