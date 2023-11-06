<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Compra;
use App\Models\contasPagar;
use App\Models\ContasReceber;
use App\Models\FluxoCaixa;
use App\Models\FormaPgmto;
use App\Models\Fornecedor;
use App\Models\Funcionario;
use App\Models\User;
use App\Policies\ClientePolicy;
use App\Policies\CompraPolicy;
use App\Policies\ContasPagarPolicy;
use App\Policies\ContasReceberPolicy;
use App\Policies\FluxoCaixaPolicy;
use App\Policies\FornecedorPolicy;
use App\Policies\FuncionarioPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\PgmtoPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Policies\VendaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
//use App\Policies\ActivityPolicy;
//use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Produto::class => ProdutoPolicy::class,
        Cliente::class => ClientePolicy::class,
        FormaPgmto::class => PgmtoPolicy::class,
        Funcionario::class => FuncionarioPolicy::class,
        Fornecedor::class => FornecedorPolicy::class,
        Compra::class => CompraPolicy::class,
        ContasPagar::class => ContasPagarPolicy::class,
        ContasReceber::class => ContasReceberPolicy::class,
        FluxoCaixa::class => FluxoCaixaPolicy::class,
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
        Venda::class => VendaPolicy::class,
        //Activity::class => ActivityPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
