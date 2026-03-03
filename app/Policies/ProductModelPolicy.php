<?php

namespace App\Policies;

use App\Models\ProductModel;
use App\Models\User;

class ProductModelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isVendeur()) return false;
        return $user->can('products.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductModel $productModel): bool
    {
        if ($user->isVendeur()) return false;
        return $user->can('products.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isVendeur()) return false;
        return $user->can('products.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductModel $productModel): bool
    {
        if ($user->isVendeur()) return false;
        return $user->can('products.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductModel $productModel): bool
    {
        if ($user->isVendeur()) return false;
        // Ne peut supprimer que si aucun produit n'utilise ce modèle
        if ($productModel->products()->count() > 0) {
            return false;
        }

        return $user->can('products.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductModel $productModel): bool
    {
        if ($user->isVendeur()) return false;
        return $user->can('products.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductModel $productModel): bool
    {
        // Seul l'admin peut supprimer définitivement
        return $user->isAdmin() && $productModel->products()->count() === 0;
    }
}
