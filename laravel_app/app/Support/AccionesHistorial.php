<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Catálogo único de las acciones que registra el historial (spec 015), agrupadas por tipo de registro afectado
 * (el alias de `historial_cambios.sujeto_tipo`). Es la única fuente de etiquetas: escribir una acción que no esté
 * aquí lanza una excepción. Las claves de miembros, sedes, roles y niveles son las que ya usaban sus historiales.
 *
 * Cada acción de un miembro lleva su sección del histórico (`categoria`) y, si la provoca otro módulo, ese módulo.
 */
class AccionesHistorial
{
    public const DATOS = 'datos';

    public const ENCUENTROS_RETIROS = 'encuentros_retiros';

    public const CELULAS_MINISTERIOS = 'celulas_ministerios';

    public const LIDERAZGO = 'liderazgo';

    public const NIVELES = 'niveles';

    public const SEDE = 'sede';

    /** Secciones del histórico del miembro, en el orden en que se muestran. */
    public const CATEGORIAS = [
        self::DATOS => 'Cambios de datos',
        self::ENCUENTROS_RETIROS => 'Encuentros y retiros',
        self::CELULAS_MINISTERIOS => 'Células y ministerios',
        self::LIDERAZGO => 'Liderazgo',
        self::NIVELES => 'Niveles',
        self::SEDE => 'Sede',
    ];

    /** Modelo de cada tipo de registro con historial. */
    public const CLASES = [
        'persona' => \App\Models\Persona::class,
        'celula' => \App\Models\Celula::class,
        'ministerio' => \App\Models\Ministerio::class,
        'evento' => \App\Models\Evento::class,
        'encuentro' => \App\Models\Encuentro::class,
        'nivel' => \App\Models\NivelFormacion::class,
        'sede' => \App\Models\Sede::class,
        'usuario' => \App\Models\User::class,
        'rol' => \App\Models\Rol::class,
        'solicitud_clave' => \App\Models\SolicitudClave::class,
    ];

    /** Acciones que guardan una foto legible del registro (`detalle.datos`). */
    public const CON_FOTO = ['creada', 'creado', 'eliminada', 'eliminado', 'creada_qr', 'creada_qr_actividad', 'llego_por_traslado'];

    /**
     * @return array<string, array{etiqueta: string, modulo: string, acciones: array<string, array{0: string, 1: string, 2?: ?string, 3?: string}>}>
     */
    public static function catalogo(): array
    {
        $d = self::DATOS;
        $e = self::ENCUENTROS_RETIROS;
        $c = self::CELULAS_MINISTERIOS;
        $l = self::LIDERAZGO;
        $n = self::NIVELES;
        $s = self::SEDE;

        // [etiqueta, icono, categoría (solo miembros), módulo si no es el del registro]
        return [
            'persona' => ['etiqueta' => 'Miembro', 'modulo' => Modulos::MIEMBROS, 'acciones' => [
                'creada' => ['Creado', 'fa-user-plus', $d],
                'editada' => ['Datos editados', 'fa-pen', $d],
                'estado' => ['Cambio de estado', 'fa-exchange-alt', $d],
                'inactivada' => ['Inactivado', 'fa-user-slash', $d],
                'reactivada' => ['Reactivado', 'fa-user-check', $d],
                'eliminada' => ['Eliminado', 'fa-trash', $d],
                'creada_qr' => ['Creado por QR de miembros', 'fa-qrcode', $d, Modulos::REGISTRO_MIEMBROS],
                'creada_qr_actividad' => ['Creado al registrarse por QR', 'fa-qrcode', $d, Modulos::REGISTRO_MIEMBROS],
                'actualizada_qr' => ['Actualizado por QR de miembros', 'fa-qrcode', $d, Modulos::REGISTRO_MIEMBROS],
                'reactivada_qr' => ['Reactivado por QR de miembros', 'fa-qrcode', $d, Modulos::REGISTRO_MIEMBROS],
                'lider' => ['Cambio de líder', 'fa-user-friends', $l],
                'rol_lider' => ['Rol de líder', 'fa-user-tie', $l, Modulos::LIDERES],
                'inscrito_encuentro' => ['Inscrito al encuentro', 'fa-calendar-plus', $e, Modulos::ENCUENTROS],
                'quitado_encuentro' => ['Retirado del encuentro', 'fa-calendar-minus', $e, Modulos::ENCUENTROS],
                'asistio_encuentro' => ['Asistió al encuentro', 'fa-calendar-check', $e, Modulos::ENCUENTROS],
                'no_asistio_encuentro' => ['Asistencia al encuentro retirada', 'fa-calendar-times', $e, Modulos::ENCUENTROS],
                'pago_encuentro' => ['Valor pagado del encuentro', 'fa-money-bill', $e, Modulos::ENCUENTROS],
                'inscrito_evento' => ['Inscrito al evento', 'fa-ticket-alt', $e, Modulos::EVENTOS],
                'quitado_evento' => ['Retirado del evento', 'fa-ban', $e, Modulos::EVENTOS],
                'costo_evento' => ['Costo del evento cambiado', 'fa-tag', $e, Modulos::EVENTOS],
                'abono_registrado' => ['Abono registrado', 'fa-money-bill-wave', $e, Modulos::EVENTOS],
                'abono_eliminado' => ['Abono eliminado', 'fa-money-bill-wave', $e, Modulos::EVENTOS],
                'ingreso_celula' => ['Ingresó a la célula', 'fa-sign-in-alt', $c, Modulos::CELULAS],
                'salida_celula' => ['Salió de la célula', 'fa-sign-out-alt', $c, Modulos::CELULAS],
                'asistio_reunion' => ['Asistió a reunión de célula', 'fa-users', $c, Modulos::CELULAS],
                'no_asistio_reunion' => ['Asistencia a reunión retirada', 'fa-user-times', $c, Modulos::CELULAS],
                'ingreso_ministerio' => ['Ingresó al ministerio', 'fa-sign-in-alt', $c, Modulos::MINISTERIOS],
                'salida_ministerio' => ['Salió del ministerio', 'fa-sign-out-alt', $c, Modulos::MINISTERIOS],
                'cambio_ministerio' => ['Rol o fecha en el ministerio', 'fa-id-badge', $c, Modulos::MINISTERIOS],
                'nivel_registrado' => ['Nivel completado', 'fa-star', $n, Modulos::NIVELES],
                'nivel_quitado' => ['Nivel retirado', 'fa-star-half-alt', $n, Modulos::NIVELES],
                'nivel_automatico' => ['Nivel completado por encuentro', 'fa-star', $n, Modulos::NIVELES],
                'trasladada' => ['Trasladado a otra sede', 'fa-truck-moving', $s, 'sedes'],
                'llego_por_traslado' => ['Llegó por traslado', 'fa-truck-moving', $s, 'sedes'],
            ]],
            'celula' => ['etiqueta' => 'Célula', 'modulo' => Modulos::CELULAS, 'acciones' => [
                'creado' => ['Célula creada', 'fa-plus'],
                'editado' => ['Datos editados', 'fa-pen'],
                'eliminado' => ['Célula eliminada', 'fa-trash'],
                'miembros_actualizados' => ['Miembros actualizados', 'fa-users'],
                'reunion_registrada' => ['Reunión registrada', 'fa-calendar-check'],
                'reunion_eliminada' => ['Reunión eliminada', 'fa-calendar-times'],
                'qr_cambiado' => ['QR de registro', 'fa-qrcode', null, Modulos::REGISTRO_MIEMBROS],
            ]],
            'ministerio' => ['etiqueta' => 'Ministerio', 'modulo' => Modulos::MINISTERIOS, 'acciones' => [
                'creado' => ['Ministerio creado', 'fa-plus'],
                'editado' => ['Datos editados', 'fa-pen'],
                'eliminado' => ['Ministerio eliminado', 'fa-trash'],
                'integrantes_actualizados' => ['Integrantes actualizados', 'fa-users'],
            ]],
            'evento' => ['etiqueta' => 'Evento', 'modulo' => Modulos::EVENTOS, 'acciones' => [
                'creado' => ['Evento creado', 'fa-plus'],
                'editado' => ['Datos editados', 'fa-pen'],
                'eliminado' => ['Evento eliminado', 'fa-trash'],
                'inscrito_evento' => ['Inscripción', 'fa-ticket-alt'],
                'quitado_evento' => ['Inscripción retirada', 'fa-ban'],
                'costo_evento' => ['Costo individual cambiado', 'fa-tag'],
                'abono_registrado' => ['Abono registrado', 'fa-money-bill-wave'],
                'abono_eliminado' => ['Abono eliminado', 'fa-money-bill-wave'],
                'qr_cambiado' => ['QR de registro', 'fa-qrcode', null, Modulos::REGISTRO_MIEMBROS],
            ]],
            'encuentro' => ['etiqueta' => 'Encuentro', 'modulo' => Modulos::ENCUENTROS, 'acciones' => [
                'creado' => ['Encuentro creado', 'fa-plus'],
                'editado' => ['Datos editados', 'fa-pen'],
                'eliminado' => ['Encuentro eliminado', 'fa-trash'],
                'inscritos_actualizados' => ['Inscritos actualizados', 'fa-users'],
                'asistencia_actualizada' => ['Asistencia y pagos actualizados', 'fa-clipboard-check'],
                'qr_cambiado' => ['QR de registro', 'fa-qrcode', null, Modulos::REGISTRO_MIEMBROS],
            ]],
            'nivel' => ['etiqueta' => 'Nivel', 'modulo' => Modulos::NIVELES, 'acciones' => [
                'creado' => ['Nivel creado', 'fa-plus'],
                'editado' => ['Nivel editado', 'fa-pen'],
                'reordenado' => ['Orden cambiado', 'fa-sort'],
                'desactivado' => ['Nivel desactivado', 'fa-toggle-off'],
                'reactivado' => ['Nivel reactivado', 'fa-toggle-on'],
                'eliminado' => ['Nivel eliminado', 'fa-trash'],
            ]],
            'sede' => ['etiqueta' => 'Sede', 'modulo' => 'sedes', 'acciones' => [
                'creada' => ['Sede creada', 'fa-plus'],
                'editada' => ['Datos editados', 'fa-pen'],
                'desactivada' => ['Sede desactivada', 'fa-toggle-off'],
                'reactivada' => ['Sede reactivada', 'fa-toggle-on'],
                'eliminada' => ['Sede eliminada', 'fa-trash'],
                'pastor_cambiado' => ['Cambio de pastor', 'fa-user-tie'],
                'pastora_cambiada' => ['Cambio de pastora', 'fa-user-tie'],
                'registro_abierto' => ['Registro de miembros abierto', 'fa-qrcode', null, Modulos::REGISTRO_MIEMBROS],
                'registro_cerrado' => ['Registro de miembros cerrado', 'fa-qrcode', null, Modulos::REGISTRO_MIEMBROS],
                'registro_regenerado' => ['QR de miembros regenerado', 'fa-qrcode', null, Modulos::REGISTRO_MIEMBROS],
            ]],
            'usuario' => ['etiqueta' => 'Usuario', 'modulo' => Modulos::USUARIOS, 'acciones' => [
                'creado' => ['Usuario creado', 'fa-user-plus'],
                'editado' => ['Datos editados', 'fa-pen'],
                'eliminado' => ['Usuario eliminado', 'fa-trash'],
                'clave_generada' => ['Clave generada', 'fa-key'],
                'clave_enviada' => ['Clave enviada', 'fa-paper-plane'],
                'clave_cambiada' => ['Clave cambiada', 'fa-key'],
            ]],
            'rol' => ['etiqueta' => 'Rol', 'modulo' => 'roles', 'acciones' => [
                'creado' => ['Creado', 'fa-plus'],
                'editado' => ['Editado', 'fa-pen'],
                'duplicado' => ['Duplicado', 'fa-copy'],
                'eliminado' => ['Eliminado', 'fa-trash'],
            ]],
            'solicitud_clave' => ['etiqueta' => 'Solicitud de clave', 'modulo' => Modulos::SOLICITUDES_CLAVE, 'acciones' => [
                'creado' => ['Solicitud recibida', 'fa-inbox'],
                'editado' => ['Solicitud actualizada', 'fa-pen'],
                'atendida' => ['Solicitud atendida', 'fa-check'],
                'descartada' => ['Solicitud descartada', 'fa-times'],
            ]],
        ];
    }

    public static function existe(string $tipo, string $accion): bool
    {
        return isset(self::catalogo()[$tipo]['acciones'][$accion]);
    }

    public static function tipoExiste(string $tipo): bool
    {
        return isset(self::catalogo()[$tipo]);
    }

    /** Tipo (alias) de un modelo con historial, o null si no lleva historial. */
    public static function tipoDe(object|string $modelo): ?string
    {
        $clase = is_object($modelo) ? $modelo::class : $modelo;

        return array_search($clase, self::CLASES, true) ?: null;
    }

    /** @return array{0: string, 1: string, 2?: ?string, 3?: string} */
    private static function definicion(string $tipo, string $accion): array
    {
        if (!self::existe($tipo, $accion)) {
            throw new InvalidArgumentException("La acción «{$accion}» no está en el catálogo del historial para «{$tipo}».");
        }

        return self::catalogo()[$tipo]['acciones'][$accion];
    }

    public static function etiqueta(string $tipo, string $accion): string
    {
        return self::definicion($tipo, $accion)[0];
    }

    public static function icono(string $tipo, string $accion): string
    {
        return self::definicion($tipo, $accion)[1];
    }

    public static function categoria(string $tipo, string $accion): ?string
    {
        return self::definicion($tipo, $accion)[2] ?? null;
    }

    public static function modulo(string $tipo, string $accion): string
    {
        return self::definicion($tipo, $accion)[3] ?? self::catalogo()[$tipo]['modulo'];
    }

    public static function etiquetaTipo(string $tipo): string
    {
        return self::catalogo()[$tipo]['etiqueta'] ?? $tipo;
    }

    /** Etiqueta de un módulo del historial (incluye Sedes y Roles, que no están en el catálogo de permisos). */
    public static function etiquetaModulo(string $modulo): string
    {
        return ['sedes' => 'Sedes', 'roles' => 'Roles'][$modulo] ?? Modulos::etiqueta($modulo);
    }

    /** @return array<string, string> acciones del tipo (clave → etiqueta), para filtros */
    public static function deTipo(string $tipo): array
    {
        return array_map(fn ($a) => $a[0], self::catalogo()[$tipo]['acciones'] ?? []);
    }

    /** @return array<string, string> todos los módulos que aparecen en el historial (clave → etiqueta) */
    public static function modulos(): array
    {
        $modulos = [];
        foreach (self::catalogo() as $tipo) {
            $modulos[$tipo['modulo']] = self::etiquetaModulo($tipo['modulo']);
            foreach ($tipo['acciones'] as $definicion) {
                if (isset($definicion[3])) {
                    $modulos[$definicion[3]] = self::etiquetaModulo($definicion[3]);
                }
            }
        }
        asort($modulos);

        return $modulos;
    }
}
