<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users.view') || $user->isVendeur();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Un utilisateur peut toujours voir son propre profil
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('users.view') || $user->isVendeur();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('users.create') || $user->isVendeur();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Un utilisateur peut toujours modifier son propre profil
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('users.edit') || $user->isVendeur();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Ne peut pas se supprimer soi-même
        if ($user->id === $model->id) {
            return false;
        }

        // Seul l'admin ou le vendeur peut supprimer des utilisateurs
        return $user->can('users.delete') || $user->isVendeur();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('users.edit') || $user->isVendeur();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Ne peut jamais se forceDelete soi-même
        if ($user->id === $model->id) {
            return false;
        }

        return $user->isAdmin() || $user->isVendeur();
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRole(User $user, User $model): bool
    {
        // Ne peut pas changer son propre rôle
        if ($user->id === $model->id) {
            return false;
        }

        // L'admin et le vendeur peuvent assigner des rôles (ex: recruter)
        return $user->isAdmin() || $user->isVendeur();
    }

    /**
     * Determine whether the user can view user statistics.
     */
    public function viewStatistics(User $user, User $model): bool
    {
        // Un utilisateur peut voir ses propres stats
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('reports.view') || $user->isVendeur();
    }

    /**
     * Determine whether the user can change user password.
     */
    public function changePassword(User $user, User $model): bool
    {
        // Un utilisateur peut changer son propre mot de passe
        if ($user->id === $model->id) {
            return true;
        }

        // Seul l'admin ou vendeur peut changer le mot de passe d'autres utilisateurs
        return $user->isAdmin() || $user->isVendeur();
    }
}
