<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $authenticatedUser, User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser->getKey() === $user->getKey() || $authenticatedUser->isAdmin();
    }

    public function delete(User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser->isAdmin() && $authenticatedUser->getKey() !== $user->getKey();
    }

    public function restore(User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser->isAdmin();
    }

    public function forceDelete(User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser->isAdmin() && $authenticatedUser->getKey() !== $user->getKey();
    }

    public function approve(User $authenticatedUser): bool
    {
        return $authenticatedUser->isAdmin();
    }

    public function ban(User $authenticatedUser): bool
    {
        return $authenticatedUser->isAdmin();
    }
}
