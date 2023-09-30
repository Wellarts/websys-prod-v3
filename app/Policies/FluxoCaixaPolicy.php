<?php

namespace App\Policies;

use App\Models\FluxoCaixa;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FluxoCaixaPolicy
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
        return $user->hasPermissionTo('View FluxoCaixa');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FluxoCaixa  $fluxoCaixa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, FluxoCaixa $fluxoCaixa)
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
        return $user->hasPermissionTo('Create FluxoCaixa');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FluxoCaixa  $fluxoCaixa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, FluxoCaixa $fluxoCaixa)
    {
        return $user->hasPermissionTo('Edit FluxoCaixa');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FluxoCaixa  $fluxoCaixa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, FluxoCaixa $fluxoCaixa)
    {
        return $user->hasPermissionTo('Delete FluxoCaixa');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FluxoCaixa  $fluxoCaixa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, FluxoCaixa $fluxoCaixa)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FluxoCaixa  $fluxoCaixa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, FluxoCaixa $fluxoCaixa)
    {
        //
    }
}
