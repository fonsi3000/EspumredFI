<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CuentaIngreso;
use Illuminate\Auth\Access\HandlesAuthorization;

class CuentaIngresoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_cuenta::ingreso');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CuentaIngreso $cuentaIngreso): bool
    {
        return $user->can('view_cuenta::ingreso');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_cuenta::ingreso');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CuentaIngreso $cuentaIngreso): bool
    {
        return $user->can('update_cuenta::ingreso');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CuentaIngreso $cuentaIngreso): bool
    {
        return $user->can('delete_cuenta::ingreso');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_cuenta::ingreso');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, CuentaIngreso $cuentaIngreso): bool
    {
        return $user->can('force_delete_cuenta::ingreso');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_cuenta::ingreso');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, CuentaIngreso $cuentaIngreso): bool
    {
        return $user->can('restore_cuenta::ingreso');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_cuenta::ingreso');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, CuentaIngreso $cuentaIngreso): bool
    {
        return $user->can('replicate_cuenta::ingreso');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_cuenta::ingreso');
    }
}
