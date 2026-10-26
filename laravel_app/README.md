<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Registro de miembros por QR

Cada sede tiene un QR permanente (`/registro-sede/{token}`) para que las personas se registren o actualicen sus datos sin iniciar sesión.

- **Quién lo gestiona**: el administrador, en la pestaña «Registro de miembros» del detalle de sede; el pastor, en «Registro de miembros» del menú (solo su sede). Se puede descargar, cerrar, abrir y regenerar (el enlace anterior deja de funcionar).
- **Qué hace el formulario**: busca por celular o cédula dentro de la sede del QR. Si no existe, crea al miembro (como visitante o miembro activo, a elección de la persona); si existe, precarga todos sus datos y actualiza al instante solo lo que cambió; si estaba inactivo o eliminado, lo reactiva.
- **Qué nunca toca**: no vacía datos guardados ni cambia celular, estado (salvo reactivar), líder, células, rol de liderazgo ni sede. Un miembro que solo existe en otra sede no se lee ni se modifica.
- **Límite**: 5 consultas o envíos por minuto por conexión (`RegistroMiembrosPublicoController::INTENTOS_POR_MINUTO`).
- **Historial**: cada alta, actualización y reactivación queda en el historial del miembro («… por QR de miembros») con valores anteriores y nuevos; para revertir un cambio indebido se corrige la ficha a mano.
- **Riesgo aceptado**: quien conozca un celular o una cédula ve y puede cambiar los datos de esa persona (decisión de la funcionalidad 012).

## Roles y permisos

El módulo **Usuarios** tiene una pestaña «Roles» (solo administrador) donde se crean y editan los roles de la aplicación.

- **Niveles por módulo**: Sin acceso, Lectura o Escritura (acumulativos). Módulos configurables: Tablero, Miembros, Líderes, Sin líder asignado, Células, Ministerios, Eventos, Encuentros, Organigrama, Registro de miembros por QR, Usuarios y Solicitudes de clave.
- **Alcance de datos** del rol: toda la sede, solo su rama (él, sus miembros y sus células) o solo su registro. El aislamiento por sede no depende del rol.
- **Reservado al administrador** (ningún nivel lo concede): Sedes, Roles, traslados entre sedes, eliminar miembros, el selector de sede activa y «Todas las sedes».
- **Cómo se aplica**: el middleware `modulo:{clave}` protege cada grupo de rutas (GET exige lectura; el resto, escritura; las pantallas create/edit también escritura) y las políticas y gates deciden por registro. Los permisos se leen en cada petición, así un cambio se aplica en la siguiente acción del usuario sin cerrar sesión. `tests/Feature/Roles/CoberturaRutasTest.php` falla si una ruta con sesión queda sin módulo.
- **Roles de sistema**: Administrador (protegido, siempre todo), Pastor, Líder y Usuario se sembraron con el acceso que ya tenían. `users.role` guarda la clave del rol.
- **Asignar roles**: quien tiene escritura en Usuarios crea cuentas de su sede solo con roles marcados «asignables» cuyos permisos y alcance no superen los suyos. Nunca queda el sistema sin un administrador.
- **Hueco heredado**: Eventos, Encuentros y Ministerios nunca tuvieron restricción por rol; la migración conserva esa escritura para Líder y Usuario y se puede cerrar desde la pestaña Roles.
- **Historial**: cada alta, cambio, duplicado y baja de un rol queda en su historial con autor y los niveles anterior y nuevo.
- **Prueba de caracterización**: `tests/Feature/Roles/CaracterizacionTest.php` compara el acceso de los 4 roles por ruta con `tests/Support/matriz-acceso-base.json`; regenerar solo con `GENERAR_MATRIZ_BASE=1`.

## Niveles de formación

Plan único para toda la iglesia (Libres para servir → Aliviando el fuego → Caminando en santidad → Viviendo en el espíritu), editable desde el módulo **Niveles** (administrador y pastor escriben; líder y usuario leen).

- El avance de cada miembro se registra a mano o automáticamente al asistir a un encuentro *realizado* vinculado a un nivel; lo manual prevalece.
- La ficha del miembro muestra el **camino de niveles** (estilo mapa de juego); las listas muestran el progreso en puntos.
- `niveles/miembros` lista quién está en cada nivel (actual, completaron, les toca) con la grilla estándar; el tablero tiene la tarjeta «Miembros por nivel».
- Los niveles no bloquean: se puede saltar uno. Al trasladar un miembro de sede sus avances se copian.
- Tras actualizar: `php artisan migrate` y `npm run build`.

## Historial de cambios

Todo cambio de información queda en un historial único e inalterable (`historial_cambios`, modelo `CambioHistorial`, spec 015) con fecha y hora, quién lo hizo (usuario, «Registro público por QR», «Automático · por …» o «Sistema»), la acción y el detalle «campo: antes → después».

- **Pestaña «Histórico»** en cada ficha (`?pestana=historico`): miembro y líder en secciones (Cambios de datos, Encuentros y retiros, Células y ministerios, Liderazgo, Niveles, Sede); célula, ministerio, evento, encuentro, nivel, sede, usuario y rol en una lista con filtro por acción. La ve quien puede ver la ficha.
- **Control de cambios** (`/control-cambios`): todo el historial con la grilla estándar. Por defecto solo el administrador; se concede a otros roles desde Roles. **No se exporta** (excepción declarada al principio III).
- **Un miembro trasladado** lleva su histórico completo a la sede nueva (`personas.trasladada_desde_persona_id`); las fichas de la otra sede se muestran sin enlace.
- **Cómo registrar en código nuevo** (única puerta: `App\Services\Historial`):
  - Modelo con ficha y sin registro propio: usa el rasgo `RegistraHistorial` (altas, ediciones y bajas solas) y define `camposHistorial()` (etiqueta y tipo legible de cada campo), `camposIgnorados()` y, si hace falta, `camposSecretos()`. Agrega su tipo a `AccionesHistorial::CLASES` y sus acciones al catálogo.
  - Relaciones muchos a muchos (`sync`, `attach`, `detach`): `Historial::sincronizacion($principal, $resultado, $resumen, $agregado, $quitado)`; deja un resumen y una entrada por miembro.
  - Nombre de negocio para lo que genera el rasgo: `Historial::accion('clave_cambiada', fn () => …)`. Actor especial: `Historial::comoActor('qr' | 'automatico' | 'sistema', …)`.
  - Toda acción nueva debe existir en `App\Support\AccionesHistorial`; `CoberturaHistorialTest` falla si una ruta de escritura nueva no deja su entrada.
- **Secretos**: claves, tokens y códigos nunca se guardan (solo «Clave cambiada»).
- Las entradas no se editan ni se borran, y borrar un registro o un usuario no borra su historial (la tabla no tiene claves foráneas).

## Usuarios y miembros: una sola persona

Toda cuenta de usuario es de un miembro de la iglesia (spec 016). Un miembro tiene como máximo una cuenta.

- **El nombre y el celular son del miembro**: `users` ya no tiene columnas `name` ni `celular`. `User::name` y `User::celular` leen del miembro vinculado (`User::persona()`, sin filtro de sede). Se corrigen en la ficha del miembro. El correo sigue siendo de la cuenta: es la credencial de ingreso.
- **«Nuevo usuario» en dos pasos**:
  1. Se escribe el celular (y la sede, si es el administrador).
  2. El sistema busca al miembro por `personas.celular_clave` (`Celular::clave()`: dígitos sin el indicativo 57). Si lo encuentra, vincula la cuenta; si hay varios, se elige uno; si no existe, se registra el miembro junto con la cuenta en una sola operación (`App\Services\CuentasDeMiembros`).
  - Un celular de otra sede solo muestra un aviso para pedir el traslado, sin datos del miembro.
- **Reglas**:
  - No se elimina un miembro con cuenta.
  - Dos miembros con cuenta no comparten celular.
  - Al trasladar a un miembro, su cuenta se va con él.
- **Cuentas antiguas**: `php artisan miembros:unificar-usuarios` (también corre como migración) vincula cada cuenta con su miembro, o lo crea. Los casos dudosos (varios miembros con el celular, o un nombre distinto) quedan en Usuarios, filtro «Vínculo: Por revisar», donde el administrador elige el miembro correcto. El comando se puede repetir.

## Liderazgo: el pastor es la cabeza

- Al crear o designar un líder, su líder pasa a ser el pastor de la sede, aunque antes tuviera otro. Después se le puede mover bajo otro líder (sin ciclos).
- Todo líder tiene líder, salvo el pastor y la pastora, que son líderes sin líder. En una sede sin pastor no se pueden crear líderes.
- Al quitar el rol a un líder, los líderes a su cargo suben a su líder (o al pastor).
- Al cambiar el pastor de una sede, quienes dependían del anterior pasan al nuevo (sin salir de sus células), y el anterior queda como líder bajo el nuevo.
- La lógica vive en `LiderazgoService` (`liderPorDefecto`, `reasignarDependientes`, `corregirCabezas`). La migración `pastor_cabeza_del_liderazgo` corrigió los datos existentes.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
