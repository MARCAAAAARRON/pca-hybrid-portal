<?php

namespace App\Policies;

use App\Models\User;
use App\Models\NurseryOperation;
use Illuminate\Auth\Access\HandlesAuthorization;

class NurseryOperationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_terminal');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NurseryOperation $nurseryOperation): bool
    {
        return $user->can('view_terminal');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_terminal');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NurseryOperation $nurseryOperation): bool
    {
        return $user->can('update_terminal');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NurseryOperation $nurseryOperation): bool
    {
        return $user->can('delete_terminal');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_terminal');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, NurseryOperation $nurseryOperation): bool
    {
        return $user->can('{{ ForceDelete }}');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, NurseryOperation $nurseryOperation): bool
    {
        return $user->can('{{ Restore }}');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, NurseryOperation $nurseryOperation): bool
    {
        return $user->can('{{ Replicate }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('{{ Reorder }}');
    }
}
