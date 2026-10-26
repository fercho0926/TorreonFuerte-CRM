<?php

namespace App\Providers;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\SolicitudClave;
use App\Models\User;
use App\Services\AlcanceService;
use App\Services\Historial;
use App\Services\SedeContext;
use App\Support\Menu;
use App\Support\Modulos;
use App\Support\Niveles;
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
        $this->app->singleton(Historial::class);
        $this->app->singleton(\App\Services\LecturaHistorial::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale('es');
        Carbon::setLocale('es');
        Paginator::useBootstrapFive();

        // Acciones reservadas al administrador (Sedes, Roles, traslados, selector de sede...).
        Gate::define('administrador', fn (User $user) => $user->esAdmin());

        // QR de un evento, encuentro o célula (spec 013): escritura en el módulo del destino y, además,
        // alcance de toda la sede o ser líder o co-líder de esa célula.
        Gate::define('gestionarRegistroQr', function (User $user, $destino) {
            $modulo = match (true) {
                $destino instanceof \App\Models\Evento => Modulos::EVENTOS,
                $destino instanceof \App\Models\Encuentro => Modulos::ENCUENTROS,
                $destino instanceof \App\Models\Celula => Modulos::CELULAS,
                default => null,
            };

            if ($modulo === null || $user->nivelEn($modulo) < Niveles::ESCRITURA) {
                return false;
            }

            if ($user->tieneAccesoTotal()) {
                return true;
            }

            return $user->isLiderCelula()
                && $destino instanceof \App\Models\Celula
                && $user->persona_id !== null
                && in_array($user->persona_id, [$destino->lider_id, $destino->co_lider_id], true);
        });

        // QR de registro de miembros de una sede: el administrador gestiona cualquier sede; los demás, solo la suya
        // y con escritura en «Registro de miembros por QR».
        Gate::define('gestionarRegistroMiembros', function (User $user, ?\App\Models\Sede $sede = null) {
            if ($user->esAdmin()) {
                return true;
            }

            return $user->nivelEn(Modulos::REGISTRO_MIEMBROS) >= Niveles::ESCRITURA
                && $sede !== null && $user->sede_id !== null && (int) $user->sede_id === (int) $sede->id;
        });

        // Opción «Registro de miembros» del menú: quien tenga lectura (el administrador entra por el detalle de sede).
        Gate::define('verRegistroMiembros', fn (User $user) => !$user->esAdmin() && $user->nivelEn(Modulos::REGISTRO_MIEMBROS) >= Niveles::LECTURA);

        // @puedeModulo('miembros', 'escritura') ... @endpuedeModulo — según el nivel del rol del usuario.
        Blade::if('puedeModulo', function (string $modulo, string $nivel = 'lectura') {
            $user = Controller::usuarioDeLaPeticion();

            return $user && $user->nivelEn($modulo) >= Niveles::aNumero($nivel);
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
                'rolEtiqueta' => $user ? (User::etiquetaDeRol($user->role)) : null,
                'menu' => Menu::secciones($user, ['sinLider' => $sinLider, 'solicitudes' => $solicitudesPendientes]),
            ]);
        });
    }
}
