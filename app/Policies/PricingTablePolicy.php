<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PricingTable;
use Illuminate\Auth\Access\HandlesAuthorization;

class PricingTablePolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PricingTable $pricingTable): bool
    {
        return $user->company_id === $pricingTable->company_id
            && $user->can('view_pricing::table');
    }
    /**
     * Determine whether the user can update the model.
     */

    public function update(User $user, PricingTable $pricingTable): bool
    {
        return $user->company_id === $pricingTable->company_id
            && $user->can('update_pricing::table');
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pricing::table');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_pricing::table');
    }

    /**
     * Determine whether the user can update the model.
     */

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PricingTable $pricing): bool
    {
        return $user->can('delete_pricing::table');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_pricing::table');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, PricingTable $pricing): bool
    {
        return $user->can('force_delete_pricing::table');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_pricing::table');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, PricingTable $pricing): bool
    {
        return $user->can('restore_pricing::table');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_pricing::table');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, PricingTable $pricing): bool
    {
        return $user->can('replicate_pricing::table');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_pricing::table');
    }
}
