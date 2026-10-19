# Torreón Fuerte CRM

Aplicación Laravel. Todo el código vive en [laravel_app/](laravel_app/); la raíz solo contiene los archivos de despliegue a cPanel ([DEPLOY_CPanel.md](DEPLOY_CPanel.md), [DEPLOY_CPanel_Automation.md](DEPLOY_CPanel_Automation.md), [deploy-cpanel.ps1](deploy-cpanel.ps1)).

## Requisitos

- PHP 8.2+ con Composer
- Node.js y npm

## Primera vez (instalación)

```powershell
cd laravel_app
composer setup
```

`composer setup` ejecuta `composer install`, crea el `.env` desde `.env.example` si no existe, genera la `APP_KEY`, corre las migraciones y hace `npm install` + `npm run build`.

Por defecto usa SQLite (`database/database.sqlite`). Si usas otra base de datos, ajusta las variables `DB_*` en `laravel_app/.env`.

## Iniciar en local

Desde `laravel_app/`:

```powershell
composer dev
```

Levanta en paralelo el servidor (http://127.0.0.1:8000), el worker de colas, los logs (`pail`) y Vite.

> `pail` requiere la extensión `pcntl`, que no existe en Windows. Si `composer dev` falla por eso, usa dos terminales:

```powershell
php artisan serve   # terminal 1 → http://127.0.0.1:8000
npm run dev         # terminal 2 → Vite
```

## Comandos útiles

```powershell
php artisan migrate          # aplicar migraciones
php artisan migrate:fresh --seed   # reiniciar la base de datos con seeders
php artisan test             # correr las pruebas
npm run build                # compilar assets para producción
npm test                     # pruebas de JavaScript (Vitest)
```

## Interfaz (AdminLTE 4)

El panel usa **AdminLTE 4 sobre Bootstrap 5** (Font Awesome para los iconos; Select2, Flatpickr y SweetAlert2 en los formularios). Desde `laravel_app/`: `npm install`, luego `npm run dev` (desarrollo) o `npm run build` (producción: se genera `public/build`, que es lo que se sube al hosting).

- **Menú lateral y migas de pan**: se definen en un solo archivo, `laravel_app/config/menu.php` (secciones y opciones con etiqueta, icono, ruta, patrón de ruta activa y permiso). Para añadir un módulo al menú basta con agregar su opción ahí.
- **Plantilla de pantalla**: las vistas extienden `layouts.admin` y definen `title`, `page-title`, `content` y, opcionalmente, `page-actions` (botones del encabezado) y `breadcrumbs`.
- **Componentes de presentación** (`resources/views/components/ui/`): `<x-ui.tarjeta>`, `<x-ui.estado>` (insignia de estado con el esquema único de colores de `App\Support\Estados`), `<x-ui.insignia>`, `<x-ui.vacio>`, `<x-ui.acciones-fila>` y `<x-ui.caja>` (caja de resumen). Los formularios usan `<x-form.*>`.
- El nombre del sistema que se ve en pestañas y pie se configura con `APP_SISTEMA` en `.env` (por defecto «CRM Torreón Fuerte»).
- Una prueba (`ClasesBootstrapTest`) falla si alguien vuelve a usar clases de Bootstrap 4 / AdminLTE 3.
- En producción, `php artisan view:cache` reduce el tiempo de respuesta de las pantallas.

## Grillas (listados estándar)

Todo listado principal usa la **grilla estándar** (estilo tabla de PrimeNG, con DataTables en modo servidor): ordenar por columna sobre todo el conjunto, filtro por columna, búsqueda general, 10/25/50/100 filas por página, paginador, columnas que se pliegan en teléfono y **Exportar CSV** (con los filtros y el orden actuales). Sin JavaScript se ve la primera página con el paginador del servidor. Una prueba (`GuardaGrillaEstandarTest`) falla si un `resources/views/<modulo>/index.blade.php` usa una `<table>` en lugar de `<x-ui.grilla>`.

Para crear una grilla nueva (ejemplo: módulo `talleres`):

1. **Definición** `app/Grillas/TalleresGrilla.php`, que hereda de `App\Grillas\Grilla`: `id()` (prefijo de rutas), `definirColumnas()` (con `Columna::make('nombre', 'Nombre')->ordena('talleres.nombre')->filtraTexto('talleres.nombre')->valor(fn ($t) => $t->nombre)`; `Columna::sede('talleres')` y `Columna::acciones()` ya vienen hechas) y `base($usuario, $estado)` (consulta **ya limitada por el alcance** de rol y sede; puede ser un `Builder` o una `Collection`). Opcionales: `conBusqueda()` + `buscar()`, `extras()` (filtros propios como `inactivos`), `ordenInicial()`, `vacio()`, `puedeVer()` y `puedeExportar()`.
2. **Celdas** en `resources/views/talleres/_celda.blade.php`: un `@switch($columna)` que pinta cada celda a partir de `$fila` (las acciones siguen usando `<x-ui.acciones-fila>` y `<x-form.confirm>`).
3. **Controlador**: `use ServicioGrilla;`, `protected function grilla(): Grilla { return app(TalleresGrilla::class); }` y en `index()` devolver `view('talleres.index', $this->datosDeLaGrilla($request))`. El trait aporta `datos()` (JSON de DataTables) y `exportar()` (CSV).
4. **Rutas** `talleres.index`, `talleres.datos` y `talleres.exportar` en `routes/web.php`, declaradas **antes** de `/talleres/{taller}`.
5. **Vista** `talleres/index.blade.php`: `<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Talleres" />`. Los filtros propios van en el slot `filtros` (campos cuyo `name` coincide con `extras()`).

Los datos sensibles (como usuarios o solicitudes de clave) restringen la exportación sobrescribiendo `puedeExportar()`; el botón y la ruta respetan esa regla. Diseño completo en `specs/007-grillas-datatables/`.

## Usuarios y claves por WhatsApp

Nadie escribe ni conoce la contraseña de otra persona. El flujo es:

1. **Crear usuario** (administrador o pastor): se piden nombre, correo, rol y **celular de WhatsApp** (si se deja vacío se usa el de la persona vinculada). Al guardar, el sistema genera una **clave temporal** y la muestra **una sola vez** con los botones «Enviar por WhatsApp» (abre WhatsApp con el mensaje listo) y «Copiar mensaje». Después se pulsa «Ya la envié».
2. **Primer ingreso**: con la clave temporal la persona solo puede **elegir su propia contraseña** (mínimo 8 caracteres). La temporal vale 24 horas y deja de servir al cambiarla, al generar otra o al vencer.
3. **«¿Olvidaste tu contraseña?»** en el login: la persona escribe su correo y se crea una **solicitud** (la respuesta es la misma exista o no la cuenta). Un administrador o pastor de su sede la atiende desde **Administración → Solicitudes de clave** («Generar clave» y, en la pantalla siguiente, el botón verde «Enviar por WhatsApp»).
4. **Reenviar**: desde Usuarios, «Enviar nueva clave» (con confirmación). El pastor solo puede hacerlo con líderes y usuarios de su sede.
5. **Mi cuenta** (menú de usuario): cambiar la propia contraseña indicando la actual.

El envío es **asistido** (una persona pulsa el botón de WhatsApp); no hace falta ninguna cuenta ni servicio de pago. La clave nunca se guarda en claro: solo su hash vive en la contraseña de la cuenta y el registro de claves (`registros_clave`) guarda quién la preparó, cuándo y su estado, sin la clave. Hay límite de intentos de inicio de sesión (5 fallos por minuto) y de solicitudes de recuperación (3 por hora por cuenta).

Se ajusta con variables opcionales de `.env` (valores por defecto entre paréntesis): `CLAVES_VIGENCIA_HORAS` (24), `CLAVES_LOGIN_FALLOS_POR_MINUTO` (5), `CLAVES_SOLICITUDES_POR_CUENTA_HORA` (3), `CLAVES_SOLICITUDES_POR_ORIGEN_HORA` (10), `CLAVES_SOLICITUD_VENCE_HORAS` (48), `CLAVES_CONTRASENA_MINIMO` (8), `CLAVES_LONGITUD` (10).

Las cuentas existentes conservan su contraseña. Para enviarles una clave nueva necesitan un celular: edita el usuario y complétalo.

## Personas, líderes y alcance por rol

Vocabulario único en toda la aplicación:

- **Líder**: persona que cuida de otras y dirige células (un solo nivel; antes "líder de red" y "líder de célula").
- **En célula**: estado de quien asiste a una célula y aún no es parte de la iglesia; no exige documento y no aparece en «Sin líder asignado».

El **líder directo** de cada persona es la fuente de verdad; la célula es opcional y solo puede ser una célula de ese líder. Las personas sin líder aparecen en *Sin líder asignado* (admin y pastor).

Cada usuario solo ve las personas, líderes y células de su ámbito: admin y pastor ven todo y el líder (`lider_celula`) ve solo a sus personas y células.

### Migración desde la versión anterior

Al ejecutar `php artisan migrate` sobre datos existentes: los usuarios con el rol retirado `lider_zona` pasan a `lider_celula`, todas las personas existentes quedan como *miembro activo* y los miembros de célula sin líder toman el líder de la célula (los conflictos se registran en el log). La columna de texto `red` se elimina en la última migración (`2026_10_21_…`): haz una copia de la base de datos antes de migrar en producción.

### Usuarios de ejemplo (`php artisan migrate:fresh --seed`)

`admin@example.com` (ve todas las sedes) y, por cada sede sembrada (`centro`, `norte`, `sur`), `pastor.<sede>@example.com`, `pastora.<sede>@example.com` y `lider.<sede>@example.com`; contraseña `password`.

### Búsqueda de personas, células y ministerios

- **Designar líder**, los **integrantes de células** y los **de ministerios** usan un buscador con autocompletado (desde 2 letras, por nombre, apellido, documento o celular) que solo ofrece personas activas de tu sede; el servidor pagina de 20 en 20, así que funciona igual con cientos de personas.
- **Células**: se puede ser integrante de una célula sin importar quién es tu líder directo (la célula no cambia el líder directo). Desde la célula se puede **crear una persona nueva** («Crear persona nueva»): queda en estado **En célula**.
- La ficha del líder muestra la grilla **Personas a su cargo** (quienes lo tienen como líder directo).
- **Ministerios** está en el grupo Congregación del menú.

## Multi-sede (varias iglesias)

El CRM sirve a varias iglesias (**sedes**) en una sola base de datos. Cada sede tiene sus propias personas, líderes, células, encuentros, eventos (con pagos) y ministerios, y **nunca ve los de otra**.

- **Administrador del sistema (`admin`)**: único con visión total. Crea y administra sedes (menú *Sedes*), elige en la barra superior la **sede activa** o *Todas las sedes* (solo consulta: para crear o editar hay que elegir una sede) y ve el resumen global por sede en el tablero.
- **Pastor y pastora (`pastor`)**: mismos permisos, limitados a su sede. Gestionan catálogos y usuarios de su sede, pero solo crean cuentas de **líder** y **usuario**; las cuentas de pastor y admin las crea o cambia el admin.
- **Líder y usuario**: como antes, dentro de su sede. Cada usuario no admin pertenece a **una sola sede**.
- **Alta de sede**: el admin crea la sede, el pastor y la pastora en una sola operación atómica (si algo falla no queda nada). Desactivar una sede impide el inicio de sesión de sus usuarios y deja sus datos en solo lectura; las sedes con datos no se eliminan.
- **Traslado de personas** (solo admin): se crea un registro nuevo en la sede destino (sin líder, célula ni ministerios) y el de origen queda inactivo con su historial. Antes hay que reasignar lo que la persona dirige.
- **Aislamiento**: toda tabla de datos de la iglesia lleva `sede_id` y su modelo usa el trait `PerteneceASede` (filtro global que falla cerrado). Una prueba automática exige el trait en cada tabla con `sede_id` y otra recorre todas las rutas con IDs de otra sede.

### Migrar una instalación de una sola iglesia

1. **Haz una copia de la base de datos.**
2. `php artisan migrate`. Las migraciones `2026_10_22_*` crean las sedes, añaden `sede_id`, **asignan todos los datos existentes a una primera sede** (su nombre sale de `SEDE_INICIAL` en `.env`, por defecto "Sede principal"), dejan a los pastores actuales como pastores de esa sede (solo el admin conserva la visión total) y vuelven `sede_id` obligatoria.
3. Entra como admin, renombra la primera sede si hace falta y crea las demás desde *Sedes*.

> ⚠️ `php artisan migrate:fresh` borra la base indicada en `.env`. Para probar sin riesgo usa una base temporal: `DB_DATABASE=<ruta>\prueba.sqlite php artisan migrate:fresh --seed`.

## Despliegue

Ver [DEPLOY_CPanel.md](DEPLOY_CPanel.md) y [DEPLOY_CPanel_Automation.md](DEPLOY_CPanel_Automation.md).
