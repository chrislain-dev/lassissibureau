<?php

namespace App\Policies;

use App\Models\Reseller;
use App\Models\User;

class ResellerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('resellers.manage') || $user->can('sales.create');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reseller $reseller): bool
    {
        return $user->can('resellers.manage') || $user->can('sales.create');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('resellers.manage') || $user->isVendeur();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reseller $reseller): bool
    {
        return $user->can('resellers.manage') || $user->isVendeur();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reseller $reseller): bool
    {
        // Ne peut pas supprimer un revendeur qui a des produits en cours
        if ($reseller->hasPendingProducts()) {
            return false;
        }

        return $user->can('resellers.manage') || $user->isVendeur();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reseller $reseller): bool
    {
        return $user->can('resellers.manage') || $user->isVendeur();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reseller $reseller): bool
    {
        // Admin ou Vendeur peuvent supprimer définitivement
        return ($user->isAdmin() || $user->isVendeur()) && ! $reseller->hasPendingProducts();
    }

    /**
     * Determine whether the user can view reseller statistics.
     */
    public function viewStatistics(User $user, Reseller $reseller): bool
    {
        return $user->can('reports.view') || $user->isVendeur();
    }

    /**
     * Determine whether the user can confirm reseller sales.
     */
    public function confirmSales(User $user, Reseller $reseller): bool
    {
        return $user->can('sales.edit') || $user->isVendeur();
    }
}
