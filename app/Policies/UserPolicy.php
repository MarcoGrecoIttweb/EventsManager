<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tutti gli utenti autenticati possono vedere la lista utenti
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authenticatedUser, User $user): bool
    {
        // Gli utenti possono vedere il proprio profilo e gli admin possono vedere tutti i profili
        return $authenticatedUser->id === $user->id || $authenticatedUser->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo gli admin possono creare nuovi utenti (tramite registrazione normale)
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authenticatedUser, User $user): bool
    {
        // Gli utenti possono modificare solo il proprio profilo
        // Gli admin possono modificare tutti i profili
        return $authenticatedUser->id === $user->id || $authenticatedUser->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authenticatedUser, User $user): bool
    {
        // Solo gli admin possono eliminare utenti, e non possono eliminare se stessi
        return $authenticatedUser->isAdmin() && $authenticatedUser->id !== $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser->isAdmin() && $authenticatedUser->id !== $user->id;
    }

    /**
     * Determine whether the user can approve other users.
     */
    public function approve(User $authenticatedUser): bool
    {
        return $authenticatedUser->isAdmin();
    }

    /**
     * Determine whether the user can ban other users.
     */
    public function ban(User $authenticatedUser): bool
    {
        return $authenticatedUser->isAdmin();
    }
}
