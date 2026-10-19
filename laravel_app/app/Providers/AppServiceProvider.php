<?php

namespace App\Providers;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\User;
use App\Services\AlcanceService;
use App\Services\SedeContext;
use App\Support\Menu;
use App\Support\Permisos;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AlcanceService::class);
        $this->app->singleton(SedeContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale('es');
        Carbon::setLocale('es');
        Paginator::useBootstrapFive();

        // @puede('editar', $persona) ... @endpuede — la app usa sesión propia, no Auth de Laravel.
        Blade::if('puede', fn (string $habilidad, mixed $argumento = Persona::class) => Permisos::puede($habilidad, $argumento));

        // @todasLasSedes ... @endtodasLasSedes — solo para el admin en "Todas las sedes" (columna Sede).
        Blade::if('todasLasSedes', function () {
            $user = Controller::usuarioDeLaPeticion();

            return $user && $user->esAdmin() && app(SedeContext::class)->estaEnTodas();
        });

        View::composer('layouts.admin', function ($view) {
            $user = Controller::usuarioDeLaPeticion();
            $alcance = app(AlcanceService::class);

            $contexto = app(SedeContext::class);
            $esAdmin = (bool) $user?->esAdmin();

            $view->with([
                'sedeActual' => $contexto->sede(),
                'esAdminSede' => $esAdmin,
                'enTodasLasSedes' => $esAdmin && $contexto->estaEnTodas(),
                'sedesSelector' => $esAdmin ? \App\Models\Sede::orderBy('nombre')->get(['id', 'nombre', 'activa']) : collect(),
            ]);

            $sinLider = $user && Gate::forUser($user)->allows('verSinLider', Persona::class)
                ? $alcance->personasVisibles($user)->sinLider()->count()
                : 0;

            $view->with([
                'usuarioActual' => $user,
                'rolEtiqueta' => $user ? (User::ROLE_LABELS[$user->role] ?? $user->role) : null,
                'menu' => Menu::secciones($user, ['sinLider' => $sinLider]),
            ]);
        });
    }
}
