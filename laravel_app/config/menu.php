<?php

/*
 * Menú lateral de la aplicación: una sola fuente para el menú, la opción activa y las migas de pan.
 *
 * Cada opción: etiqueta, icono (Font Awesome), ruta (nombre), activa_en (patrones de ruta que la
 * marcan como activa), permiso (habilidad de Gate y, opcionalmente, la clase) e insignia (clave de
 * un contador que calcula el layout). Las secciones sin opciones visibles no se dibujan.
 */

use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;

return [
    'secciones' => [
        [
            'clave' => 'inicio',
            'titulo' => null,
            'opciones' => [
                ['etiqueta' => 'Dashboard', 'icono' => 'fa-home', 'ruta' => 'dashboard', 'activa_en' => ['dashboard']],
            ],
        ],
        [
            'clave' => 'congregacion',
            'titulo' => 'Congregación',
            'opciones' => [
                ['etiqueta' => 'Personas', 'icono' => 'fa-address-card', 'ruta' => 'personas.index', 'activa_en' => ['personas.*', 'traslados.*']],
                ['etiqueta' => 'Líderes', 'icono' => 'fa-user-tie', 'ruta' => 'lideres.index', 'activa_en' => ['lideres.*']],
                ['etiqueta' => 'Sin líder asignado', 'icono' => 'fa-user-clock', 'ruta' => 'sin-lider.index', 'activa_en' => ['sin-lider.*'],
                    'permiso' => ['verSinLider', Persona::class], 'insignia' => 'sinLider'],
                ['etiqueta' => 'Células', 'icono' => 'fa-people-arrows', 'ruta' => 'celulas.index', 'activa_en' => ['celulas.*']],
                ['etiqueta' => 'Redes', 'icono' => 'fa-sitemap', 'ruta' => 'redes.index', 'activa_en' => ['redes.*'],
                    'permiso' => ['gestionarCatalogos', Persona::class]],
            ],
        ],
        [
            'clave' => 'actividades',
            'titulo' => 'Actividades',
            'opciones' => [
                ['etiqueta' => 'Eventos', 'icono' => 'fa-glass-cheers', 'ruta' => 'eventos.index', 'activa_en' => ['eventos.*']],
                ['etiqueta' => 'Encuentros', 'icono' => 'fa-calendar-check', 'ruta' => 'encuentros.index', 'activa_en' => ['encuentros.*']],
                ['etiqueta' => 'Ministerios', 'icono' => 'fa-hands-helping', 'ruta' => 'ministerios.index', 'activa_en' => ['ministerios.*']],
            ],
        ],
        [
            'clave' => 'administracion',
            'titulo' => 'Administración',
            'opciones' => [
                ['etiqueta' => 'Sedes', 'icono' => 'fa-church', 'ruta' => 'sedes.index', 'activa_en' => ['sedes.*'],
                    'permiso' => ['ver', Sede::class]],
                ['etiqueta' => 'Usuarios', 'icono' => 'fa-users', 'ruta' => 'users.index', 'activa_en' => ['users.*'],
                    'permiso' => ['verUsuarios', User::class]],
            ],
        ],
    ],
];
