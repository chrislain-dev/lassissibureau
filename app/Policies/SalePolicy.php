<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Determine whether the user can view any models.
     * ✅ Vendeurs peuvent voir toutes les ventes (pour gérer les retours)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('sales.view');
    }

    /**
     * Determine whether the user can view the model.
     * ✅ Vendeurs peuvent voir n'importe quelle vente (pour les retours clients)
     */
    public function view(User $user, Sale $sale): bool
    {
        // Tous les vendeurs et admins peuvent voir toutes les ventes
        // Nécessaire pour gérer les retours clients
        return $user->can('sales.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('sales.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sale $sale): bool
    {
        // Ne peut pas modifier une vente confirmée
        if ($sale->is_confirmed) {
            return false;
        }

        // Le vendeur peut modifier toute vente non confirmée
        if ($user->isVendeur()) {
            return true;
        }

        return $user->can('sales.edit');
    }

    /**
     * Determine whether the user can delete the model.
     * ✅ Admin et Vendeur peuvent tout supprimer
     */
    public function delete(User $user, Sale $sale): bool
    {
        // Admin et Vendeur ont tous les droits de suppression sur les ventes
        if ($user->isAdmin() || $user->isVendeur()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sale $sale): bool
    {
        return $user->isAdmin() || $user->isVendeur();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sale $sale): bool
    {
        return ($user->isAdmin() || $user->isVendeur()) && ! $sale->is_confirmed;
    }

    /**
     * Determine whether the user can confirm a sale (for reseller sales).
     */
    public function confirm(User $user, Sale $sale): bool
    {
        // L'admin et le vendeur peuvent confirmer les ventes revendeurs
        if (! $user->isAdmin() && ! $user->isVendeur()) {
            return false;
        }

        // La vente doit être via un revendeur et non confirmée
        return $sale->reseller_id !== null && ! $sale->is_confirmed;
    }

    /**
     * Determine whether the user can view sale profit.
     * ✅ NOUVEAU: Seul l'admin peut voir les bénéfices
     */
    public function viewProfit(User $user, Sale $sale): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can export sales.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin() || $user->isVendeur();
    }

    /**
     * Determine whether the user can process returns.
     */
    public function processReturn(User $user, Sale $sale): bool
    {
        // La vente doit être confirmée
        if (! $sale->is_confirmed) {
            return false;
        }

        return $user->can('returns.manage') || $user->isVendeur();
    }

    /**
     * Determine whether the user can return from reseller.
     */
    public function returnFromReseller(User $user, Sale $sale): bool
    {
        return $user->isAdmin() || $user->isVendeur();
    }

    /**
     * Determine whether the user can view pending sales.
     */
    public function viewPending(User $user): bool
    {
        return $user->isAdmin() || $user->isVendeur();
    }
}