<?php

namespace App\Policies;

use App\Models\ContasPagar;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContasPagarPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('View ContasPagar');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ContasPagar  $contasPagar
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ContasPagar $contasPagar)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('Create ContasPagar');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ContasPagar  $contasPagar
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ContasPagar $contasPagar)
    {
        return $user->hasPermissionTo('Edit ContasPagar');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ContasPagar  $contasPagar
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ContasPagar $contasPagar)
    {
        return $user->hasPermissionTo('Delete ContasPagar');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ContasPagar  $contasPagar
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ContasPagar $contasPagar)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ContasPagar  $contasPagar
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ContasPagar $contasPagar)
    {
        //
    }
}
