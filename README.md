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
```

## Personas, líderes y alcance por rol

Vocabulario único en toda la aplicación:

- **Líder**: persona que cuida de otras y dirige células (un solo nivel; antes "líder de red" y "líder de célula").
- **Red**: agrupación temática del líder (Jóvenes, Matrimonios…). Se administra en el menú *Redes*.
- **Zona**: sector geográfico opcional que agrupa líderes (sirve para filtrar). Se administra en *Zonas*.

El **líder directo** de cada persona es la fuente de verdad; la célula es opcional y solo puede ser una célula de ese líder. Las personas sin líder aparecen en *Sin líder asignado* (admin y pastor).

Cada usuario solo ve las personas, líderes, redes, zonas y células de su ámbito: admin y pastor ven todo y el líder (`lider_celula`) ve solo a sus personas y células.

### Migración desde la versión anterior

Al ejecutar `php artisan migrate` sobre datos existentes: las redes escritas a mano se normalizan en el catálogo de redes (sin duplicados por mayúsculas, tildes o espacios), los usuarios con el rol retirado `lider_zona` pasan a `lider_celula`, todas las personas existentes quedan como *miembro activo* y los miembros de célula sin líder toman el líder de la célula (los conflictos se registran en el log). La columna de texto `red` se elimina en la última migración (`2026_10_21_…`): haz una copia de la base de datos antes de migrar en producción.

### Usuarios de ejemplo (`php artisan migrate:fresh --seed`)

`admin@example.com` (ve todas las sedes) y, por cada sede sembrada (`centro`, `norte`, `sur`), `pastor.<sede>@example.com`, `pastora.<sede>@example.com` y `lider.<sede>@example.com`; contraseña `password`.

## Multi-sede (varias iglesias)

El CRM sirve a varias iglesias (**sedes**) en una sola base de datos. Cada sede tiene sus propias personas, líderes, redes, zonas, células, encuentros, eventos (con pagos) y ministerios, y **nunca ve los de otra**.

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
