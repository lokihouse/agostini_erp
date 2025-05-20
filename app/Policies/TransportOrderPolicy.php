<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TransportOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransportOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_transport::order');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TransportOrder $transportOrder): bool
    {
        return $user->can('view_transport::order');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_transport::order');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TransportOrder $transportOrder): bool
    {
        return $user->can('update_transport::order');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransportOrder $transportOrder): bool
    {
        return $user->can('delete_transport::order');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_transport::order');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, TransportOrder $transportOrder): bool
    {
        return $user->can('force_delete_transport::order');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_transport::order');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, TransportOrder $transportOrder): bool
    {
        return $user->can('restore_transport::order');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_transport::order');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, TransportOrder $transportOrder): bool
    {
        return $user->can('replicate_transport::order');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_transport::order');
    }
}
