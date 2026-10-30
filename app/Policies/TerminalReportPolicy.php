<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TerminalReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class TerminalReportPolicy
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
        return $user->can('view_any_terminal');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TerminalReport $terminalReport): bool
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
    public function update(User $user, TerminalReport $terminalReport): bool
    {
        if (in_array($user->role, ['manager', 'admin'])) {
            return true;
        }
        
        return $user->can('update_terminal');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TerminalReport $terminalReport): bool
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
    public function forceDelete(User $user, TerminalReport $terminalReport): bool
    {
        return $user->can('force_delete_terminal');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_terminal');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, TerminalReport $terminalReport): bool
    {
        return $user->can('restore_terminal');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_terminal');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, TerminalReport $terminalReport): bool
    {
        return $user->can('replicate_terminal');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_terminal');
    }
}

