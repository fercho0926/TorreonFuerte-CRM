<?php

namespace App\Providers;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\SolicitudClave;
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

        // QR de registro: administración y pastor de la sede; el líder solo el de su propia célula.
        Gate::define('gestionarRegistroQr', function (User $user, $destino) {
            if ($user->tieneAccesoTotal()) {
                return true;
            }

            return $user->role === 'lider_celula'
                && $destino instanceof \App\Models\Celula
                && $user->persona_id !== null
                && in_array($user->persona_id, [$destino->lider_id, $destino->co_lider_id], true);
        });

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

            // Solicitudes de clave pendientes dentro del alcance de quien administra (insignia del menú).
            $solicitudesPendientes = $user && Gate::forUser($user)->allows('verSolicitudesClave', User::class)
                ? SolicitudClave::pendientes()->where('ultima_solicitud_en', '>=', now()->subHours((int) config('claves.solicitud_vence_horas')))->count()
                : 0;

            $view->with([
                'usuarioActual' => $user,
                'rolEtiqueta' => $user ? (User::ROLE_LABELS[$user->role] ?? $user->role) : null,
                'menu' => Menu::secciones($user, ['sinLider' => $sinLider, 'solicitudes' => $solicitudesPendientes]),
            ]);
        });
    }
}
