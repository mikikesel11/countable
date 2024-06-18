<?php

namespace App\Policies;

use App\Models\Count;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return auth()->user($user)->login;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Count $count): bool
    {
        return $count->user()->is($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return auth()->user($user)->login;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Count $count): bool
    {
        return $count->user()->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Count $count): bool
    {
        return $count->user()->is($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Count $count): bool
    {
        return $count->user()->is($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Count $count): bool
    {
        return $count->user()->is($user);
    }
}
