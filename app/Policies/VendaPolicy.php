<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venda;
use Illuminate\Auth\Access\HandlesAuthorization;

class VendaPolicy
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
        return $user->hasPermissionTo('View Vendas');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Venda  $venda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Venda $venda)
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
        return $user->hasPermissionTo('Create Vendas');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Venda  $venda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Venda $venda)
    {
        return $user->hasPermissionTo('Edit Vendas');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Venda  $venda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Venda $venda)
    {
        return $user->hasPermissionTo('Delete Vendas');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Venda  $venda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Venda $venda)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Venda  $venda
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Venda $venda)
    {
        //
    }
}
