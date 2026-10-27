<?php

namespace App\Providers;

use App\Models\Chamado;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale(config('app.locale'));

        Gate::define('admin', fn (User $user) => $user->isAdmin());

        Gate::define('atender-chamados', fn (User $user) => $user->podeAtender());

        Gate::define('ver-chamado', fn (User $user, Chamado $chamado) =>
            $user->podeAtender() || $chamado->solicitante_id === $user->id);

        // O solicitante só pode editar enquanto o chamado ainda está aberto.
        Gate::define('editar-chamado', fn (User $user, Chamado $chamado) =>
            $user->podeAtender() || ($chamado->solicitante_id === $user->id && $chamado->status === 'aberto'));
    }
}
