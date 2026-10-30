<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PollenProduction;
use Illuminate\Auth\Access\HandlesAuthorization;

class PollenProductionPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pollen::production');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PollenProduction $pollenProduction): bool
    {
        return $user->can('view_pollen::production');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_pollen::production');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PollenProduction $pollenProduction): bool
    {
        if (in_array($user->role, ['manager', 'admin'])) {
            return true;
        }
        
        return $user->can('update_pollen::production');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PollenProduction $pollenProduction): bool
    {
        return $user->can('delete_pollen::production');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_pollen::production');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, PollenProduction $pollenProduction): bool
    {
        return $user->can('force_delete_pollen::production');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_pollen::production');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, PollenProduction $pollenProduction): bool
    {
        return $user->can('restore_pollen::production');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_pollen::production');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, PollenProduction $pollenProduction): bool
    {
        return $user->can('replicate_pollen::production');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_pollen::production');
    }
}
