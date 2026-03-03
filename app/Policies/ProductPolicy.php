<?php

namespace App\Policies;

use App\Enums\ProductLocation;
use App\Enums\ProductState;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->can('products.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->can('products.edit') || $user->isVendeur();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Ne peut pas supprimer un produit vendu ou chez un revendeur
        if (
            $product->state === ProductState::VENDU ||
            $product->location === ProductLocation::CHEZ_REVENDEUR
        ) {
            return false;
        }

        return $user->can('products.delete') || $user->isVendeur();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->can('products.edit') || $user->isVendeur();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Admin ou Vendeur peuvent supprimer définitivement
        // Et seulement si le produit n'est pas vendu
        return ($user->isAdmin() || $user->isVendeur()) && $product->state !== ProductState::VENDU;
    }

    /**
     * Determine whether the user can sell the product.
     */
    public function sell(User $user, Product $product): bool
    {
        // Doit avoir la permission de créer des ventes
        if (! $user->can('sales.create') && ! $user->isVendeur()) {
            return false;
        }

        // Le produit doit être disponible à la vente
        return $product->isAvailable();
    }

    /**
     * Determine whether the user can change product state or location.
     */
    public function changeStateOrLocation(User $user, Product $product): bool
    {
        return $user->can('stock.adjustment') || $user->isVendeur();
    }

    /**
     * Determine whether the user can send product to repair.
     */
    public function sendToRepair(User $user, Product $product): bool
    {
        // Vérifier la permission
        if (! $user->can('repairs.create') && ! $user->isVendeur()) {
            return false;
        }

        // Le produit ne doit pas être vendu ou chez un client
        if (
            $product->state === ProductState::VENDU ||
            $product->location === ProductLocation::CHEZ_CLIENT
        ) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can mark product as repaired.
     */
    public function markAsRepaired(User $user, Product $product): bool
    {
        // Vérifier la permission
        if (! $user->can('repairs.edit') && ! $user->isVendeur()) {
            return false;
        }

        // Le produit doit être à réparer ou en réparation
        if (
            $product->state !== ProductState::A_REPARER &&
            $product->location !== ProductLocation::EN_REPARATION
        ) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can move product to reseller.
     */
    public function moveToReseller(User $user, Product $product): bool
    {
        // Vérifier la permission
        if (! $user->can('sales.create') && ! $user->isVendeur()) {
            return false;
        }

        // Le produit doit être disponible
        if (! $product->isAvailable()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can return product from reseller.
     */
    public function returnFromReseller(User $user, Product $product): bool
    {
        // Vérifier la permission
        if (! $user->can('sales.edit') && ! $user->isVendeur()) {
            return false;
        }

        // Le produit doit être chez un revendeur
        if ($product->location !== ProductLocation::CHEZ_REVENDEUR) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can mark product as lost.
     */
    public function markAsLost(User $user, Product $product): bool
    {
        // Vérifier la permission
        if (! $user->can('stock.adjustment') && ! $user->isVendeur()) {
            return false;
        }

        // Le produit ne doit pas être déjà vendu
        if ($product->state === ProductState::VENDU) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can process a return.
     */
    public function processReturn(User $user, Product $product): bool
    {
        // Vérifier la permission
        if (! $user->can('returns.create') && ! $user->isVendeur()) {
            return false;
        }

        // Le produit doit être vendu et chez le client
        if (
            $product->state !== ProductState::VENDU ||
            $product->location !== ProductLocation::CHEZ_CLIENT
        ) {
            return false;
        }

        return true;
    }
}
