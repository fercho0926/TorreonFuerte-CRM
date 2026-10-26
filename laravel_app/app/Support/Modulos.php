<?php

namespace App\Support;

/**
 * Catálogo fijo de módulos a los que un rol puede dar acceso (spec 013). Sedes y Roles no están aquí: son
 * exclusivos del administrador. `solo_lectura` indica módulos que solo se consultan (nivel máximo: lectura).
 */
class Modulos
{
    public const TABLERO = 'tablero';

    public const MIEMBROS = 'miembros';

    public const LIDERES = 'lideres';

    public const SIN_LIDER = 'sin_lider';

    public const CELULAS = 'celulas';

    public const MINISTERIOS = 'ministerios';

    public const EVENTOS = 'eventos';

    public const ENCUENTROS = 'encuentros';

    public const NIVELES = 'niveles';

    public const ORGANIGRAMA = 'organigrama';

    public const REGISTRO_MIEMBROS = 'registro_miembros';

    public const USUARIOS = 'usuarios';

    public const SOLICITUDES_CLAVE = 'solicitudes_clave';

    public const CONTROL_CAMBIOS = 'control_cambios';

    /** @return array<string, array{etiqueta: string, icono: string, solo_lectura: bool, descripcion: string}> */
    public static function catalogo(): array
    {
        return [
            self::TABLERO => ['etiqueta' => 'Tablero', 'icono' => 'fa-home', 'solo_lectura' => true, 'descripcion' => 'Totales y accesos rápidos del inicio'],
            self::MIEMBROS => ['etiqueta' => 'Miembros', 'icono' => 'fa-address-card', 'solo_lectura' => false, 'descripcion' => 'Fichas, altas, ediciones e inactivación'],
            self::LIDERES => ['etiqueta' => 'Líderes', 'icono' => 'fa-user-tie', 'solo_lectura' => false, 'descripcion' => 'Designar y quitar líderes'],
            self::SIN_LIDER => ['etiqueta' => 'Sin líder asignado', 'icono' => 'fa-user-clock', 'solo_lectura' => false, 'descripcion' => 'Asignar un líder a quien no lo tiene'],
            self::CELULAS => ['etiqueta' => 'Células', 'icono' => 'fa-people-arrows', 'solo_lectura' => false, 'descripcion' => 'Células, sus miembros y reuniones'],
            self::MINISTERIOS => ['etiqueta' => 'Ministerios', 'icono' => 'fa-hands-helping', 'solo_lectura' => false, 'descripcion' => 'Ministerios e integrantes'],
            self::EVENTOS => ['etiqueta' => 'Eventos', 'icono' => 'fa-glass-cheers', 'solo_lectura' => false, 'descripcion' => 'Eventos, inscripciones y abonos'],
            self::ENCUENTROS => ['etiqueta' => 'Encuentros', 'icono' => 'fa-calendar-check', 'solo_lectura' => false, 'descripcion' => 'Encuentros y asistencia'],
            self::NIVELES => ['etiqueta' => 'Niveles', 'icono' => 'fa-route', 'solo_lectura' => false, 'descripcion' => 'Plan de niveles, avance de los miembros y quién está en cada nivel'],
            self::ORGANIGRAMA => ['etiqueta' => 'Organigrama', 'icono' => 'fa-sitemap', 'solo_lectura' => true, 'descripcion' => 'Gráfica de pastores, líderes y miembros'],
            self::REGISTRO_MIEMBROS => ['etiqueta' => 'Registro de miembros por QR', 'icono' => 'fa-qrcode', 'solo_lectura' => false, 'descripcion' => 'QR de la sede y de eventos, encuentros y células'],
            self::USUARIOS => ['etiqueta' => 'Usuarios', 'icono' => 'fa-users', 'solo_lectura' => false, 'descripcion' => 'Cuentas de acceso y sus claves'],
            self::SOLICITUDES_CLAVE => ['etiqueta' => 'Solicitudes de clave', 'icono' => 'fa-key', 'solo_lectura' => false, 'descripcion' => 'Atender solicitudes de clave'],
            self::CONTROL_CAMBIOS => ['etiqueta' => 'Control de cambios', 'icono' => 'fa-history', 'solo_lectura' => true, 'descripcion' => 'Todo lo que ha cambiado en la plataforma: quién, cuándo y qué'],
        ];
    }

    /** @return array<int, string> */
    public static function claves(): array
    {
        return array_keys(self::catalogo());
    }

    public static function existe(string $modulo): bool
    {
        return array_key_exists($modulo, self::catalogo());
    }

    public static function etiqueta(string $modulo): string
    {
        return self::catalogo()[$modulo]['etiqueta'] ?? $modulo;
    }

    public static function esSoloLectura(string $modulo): bool
    {
        return self::catalogo()[$modulo]['solo_lectura'] ?? false;
    }

    /** Nivel más alto que admite el módulo. */
    public static function nivelMaximo(string $modulo): int
    {
        return self::esSoloLectura($modulo) ? Niveles::LECTURA : Niveles::ESCRITURA;
    }

    /**
     * Patrones de nombre de ruta (fnmatch) que cubre cada módulo.
     *
     * @return array<string, array<int, string>>
     */
    public static function rutas(): array
    {
        return [
            self::TABLERO => ['dashboard'],
            self::MIEMBROS => ['personas.*'],
            self::LIDERES => ['lideres.*'],
            self::SIN_LIDER => ['sin-lider.*'],
            self::CELULAS => ['celulas.*'],
            self::MINISTERIOS => ['ministerios.*'],
            self::EVENTOS => ['eventos.*'],
            self::ENCUENTROS => ['encuentros.*'],
            self::NIVELES => ['niveles.*'],
            self::ORGANIGRAMA => ['organigrama.*'],
            self::REGISTRO_MIEMBROS => ['registro-miembros.*'],
            self::USUARIOS => ['users.*', 'claves.*'],
            self::SOLICITUDES_CLAVE => ['solicitudes-clave.*'],
            self::CONTROL_CAMBIOS => ['control-cambios.*'],
        ];
    }

    /**
     * Rutas con sesión reservadas al administrador (gate `administrador`).
     *
     * @return array<int, string>
     */
    public static function rutasReservadas(): array
    {
        return ['sedes.*', 'traslados.*', 'sede-activa.*', 'roles.*'];
    }

    /**
     * Rutas cuyo módulo depende del destino y lo decide un gate (QR de eventos, encuentros y células).
     *
     * @return array<int, string>
     */
    public static function rutasPorPolitica(): array
    {
        return ['registro-qr.*'];
    }

    /**
     * Rutas disponibles para cualquier usuario con sesión, o sin sesión.
     *
     * @return array<int, string>
     */
    public static function rutasPublicas(): array
    {
        return ['login', 'login.post', 'logout', 'recuperacion.*', 'registro.*', 'registro-sede.*', 'cuenta.*', 'cambiar-clave.*', 'up', 'storage.*'];
    }

    /** Módulo de una ruta por su nombre, o nulo si es reservada, pública o desconocida. */
    public static function deRuta(?string $nombre): ?string
    {
        if ($nombre === null) {
            return null;
        }

        foreach (self::rutas() as $modulo => $patrones) {
            foreach ($patrones as $patron) {
                if (fnmatch($patron, $nombre)) {
                    return $modulo;
                }
            }
        }

        return null;
    }
}
