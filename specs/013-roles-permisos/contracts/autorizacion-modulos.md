# Contrato: Autorización por módulo

Define cómo se aplican los niveles en toda la aplicación (RF-009, RF-010, RF-011, RF-012).

## Servicio

`App\Services\AutorizacionRoles`

| Método | Resultado |
|--------|-----------|
| `nivel(?User $u, string $modulo): int` | 0 sin acceso, 1 lectura, 2 escritura; 0 si no hay usuario o rol vigente; 2 siempre para `admin` |
| `puedeLeer(?User, string $modulo): bool` | nivel ≥ 1 |
| `puedeEscribir(?User, string $modulo): bool` | nivel = 2 |
| `alcance(?User): string` | `sede`, `rama` o `registro` (el de su rol; `sede` para `admin`) |
| `rolesAsignables(User $actor): Collection` | Roles que el actor puede dar (techo de asignación) |
| `puedeAsignar(User $actor, Rol $rol): bool` | Mismo cálculo para un rol |

Resuelve los permisos del rol una vez por petición (atributos de la petición) a partir de la tabla; no usa sesión ni caché entre peticiones.

## Middleware `modulo`

`->middleware('modulo:{clave}[,{nivel}]')` en `routes/web.php`.

- Sin `nivel`: `GET`/`HEAD` exigen lectura y los demás métodos exigen escritura.
- Con `nivel` (`lectura` o `escritura`): fuerza ese nivel para la ruta (por ejemplo `modulo:usuarios,escritura` en la exportación de datos de acceso, que hoy es solo del administrador).
- Sin sesión: redirige al inicio de sesión (igual que hoy). Sin el nivel: responde 403 con «No tienes permiso para esta acción», y JSON en las rutas que lo esperan.
- Rutas reservadas (Sedes, Roles, traslados, selector de sede activa, eliminar miembro): se protegen con el gate `administrador` (no con módulo).

## Mapa módulo → rutas

| Módulo | Rutas |
|--------|-------|
| `tablero` | `dashboard` |
| `miembros` | `personas.*` (listado, ficha, datos, búsqueda, exportar, rápida, alta, edición, inactivar/reactivar), `traslados.*` reservado |
| `lideres` | `lideres.*` |
| `sin_lider` | `sin-lider.*` |
| `celulas` | `celulas.*` |
| `ministerios` | `ministerios.*` |
| `eventos` | `eventos.*` |
| `encuentros` | `encuentros.*` |
| `organigrama` | `organigrama.*` (pantalla para roles no administradores) y la pestaña de Sedes (solo administrador) |
| `registro_miembros` | `registro-miembros.*` (gestión) y `registro-qr.*` de eventos, encuentros y células |
| `usuarios` | `users.*`, `claves.*` |
| `solicitudes_clave` | `solicitudes-clave.*` |
| (público) | `login*`, `olvide-clave`, `registro.*`, `registro-sede.*`, `mi-cuenta`, `cambiar-clave` |

El mapa se completa en `Modulos::rutas()` y una prueba recorre `Route::getRoutes()` y falla si una ruta con sesión no pertenece a un módulo, a «reservada» ni a «pública».

## Políticas y gates

Las políticas existentes dejan de comparar nombres de rol y consultan el servicio:

| Habilidad actual | Regla nueva |
|------------------|-------------|
| `PersonaPolicy::ver/editar/inactivar/crear` | `miembros` ≥ lectura/escritura **y** registro dentro del alcance |
| `PersonaPolicy::designarLider`, `verSinLider` | `lideres` / `sin_lider` escritura (lectura para ver) |
| `PersonaPolicy::eliminar` | Solo administrador (reservada) |
| `UserPolicy::verUsuarios/crear/gestionar/gestionarClaves` | `usuarios` lectura/escritura, misma sede, techo de asignación |
| `UserPolicy::verSolicitudesClave` | `solicitudes_clave` lectura |
| `gestionarRegistroQr` (eventos/encuentros/células) | Escritura del módulo del destino |
| `gestionarRegistroMiembros` | `registro_miembros` escritura y sede propia (administrador: cualquiera) |
| `verRegistroMiembros` (menú) | `registro_miembros` lectura y no administrador |
| `SedePolicy::*` | Solo administrador |

## Vistas

- Menú: cada opción de `config/menu.php` declara `modulo` y se muestra con lectura (se elimina `permiso` basado en roles).
- Botones y acciones: `@puedeModulo('miembros', 'escritura') … @endpuedeModulo`.
- Tablero: cada total, tarjeta y acceso rápido solo si hay lectura en su módulo, con totales dentro del alcance.

## Alcance

`AlcanceService::personasVisibles/lideresVisibles/celulasVisibles` usan `alcance(User)`:

| Alcance | Personas | Células |
|---------|----------|---------|
| `sede` | todas las de la sede | todas |
| `rama` | él, sus miembros directos, los de sus células y los líderes de esas células | las que lidera o co-lidera |
| `registro` | solo su propia ficha | ninguna |

Los módulos sin dueño (Eventos, Encuentros, Ministerios) no se filtran por alcance; sí lo hacen las personas que se listan dentro de ellos.
