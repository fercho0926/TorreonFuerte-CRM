# Guía de Despliegue Manual — TorreonFuerte CRM (Laravel + cPanel)

> **Contexto:** Repo `fercho0926/TorreonFuerte-CRM` (Laravel, PÚBLICO), alojado en cPanel
> compartido **sin acceso SSH/shell**. Base de datos = **SQLite** (`laravel_app/database/database.sqlite`),
> NO MySQL. Docroot del dominio = raíz `/home/torreonf/crm.torreonfuerte.org.co/`.
> El automatismo (deploy on merge) está bloqueado porque el firewall del host rechaza las IPs
> de GitHub Actions y el shell está deshabilitado.

## Datos del entorno

| Dato | Valor |
|------|-------|
| Docroot dominio | `/home/torreonf/crm.torreonfuerte.org.co/` |
| App Laravel | `/home/torreonf/crm.torreonfuerte.org.co/laravel_app/` |
| Base de datos | `laravel_app/database/database.sqlite` (SQLite) |
| Repo clonado en cPanel | `/home/torreonf/repositories/torreonfuerte-crm/` |
| URL del sitio | `https://crm.torreonfuerte.org.co` |

## Reglas de seguridad (SIEMPRE)

- **NUNCA** sobrescribir ni borrar el `.env` de producción.
- **NUNCA** sobrescribir la carpeta `vendor/` de producción.
- **NUNCA** tocar `storage/` ni `database.sqlite` sin respaldo previo.
- Todo endpoint temporal que ejecute comandos debe: estar protegido con **token secreto**, usarse una sola vez y **borrarse inmediatamente** después.
- Hacer **backup de la base SQLite ANTES** de correr migraciones.
- No manejar credenciales sensibles (claves SSH, passwords) en texto plano.

---

## PROCEDIMIENTO DE DESPLIEGUE

### Paso 0 — Preparar el commit a desplegar
1. Hacer merge del PR a `main` en GitHub.
2. Anotar el hash del commit de merge (ej. `75a0fd5`).

### Paso 1 — Actualizar el repo clonado en cPanel
1. cPanel → **Git Version Control**.
2. En el repo `torreonfuerte-crm` → **"Actualizar desde remoto"** (Pull).
3. Verificar que el commit HEAD coincide con el merge.

### Paso 2 — Copiar el código nuevo a producción
En **Administrador de archivos**, desde `/home/torreonf/repositories/torreonfuerte-crm/laravel_app/`, copiar (merge/sobrescribir) SOLO las carpetas de código hacia `/home/torreonf/crm.torreonfuerte.org.co/laravel_app/`:

- `app/`
- `database/` (incluye las migraciones nuevas)
- `resources/`
- `routes/`

> NO copiar: `.env`, `vendor/`, `storage/`, `node_modules/`.

Verificar que las migraciones nuevas quedaron en `laravel_app/database/migrations/`.

### Paso 3 — Backup de la base SQLite
En File Manager, copiar `laravel_app/database/database.sqlite` → `database.sqlite.backup-AAAAMMDD`.

### Paso 4 — Ejecutar migraciones (endpoint temporal protegido)
Como no hay shell, se usa un script PHP temporal. **Borrar tras usar.**

Crear `__deploy_migrate.php` en el **docroot** (`/home/torreonf/crm.torreonfuerte.org.co/`):

```php
<?php
// TEMPORAL — borrar tras usar. Protegido por token.
$expected = 'PON_AQUI_UN_TOKEN_ALEATORIO';
if (!hash_equals($expected, $_GET['token'] ?? '')) { http_response_code(403); exit('Forbidden'); }
$appDir = __DIR__ . '/laravel_app';
require $appDir . '/vendor/autoload.php';
$app = require_once $appDir . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
header('Content-Type: text/plain');
$status = $kernel->call('migrate', ['--force' => true]);
echo $kernel->output();
$kernel->call('migrate:status');
echo $kernel->output();
echo "\nEXIT_STATUS: $status\n";
```

Ejecutar visitando: `https://crm.torreonfuerte.org.co/__deploy_migrate.php?token=TU_TOKEN`

Confirmar `EXIT_STATUS: 0` y que `migrate:status` muestra todas "Ran".

### Paso 5 — (Solo primera vez) Crear usuario admin
Crear `__deploy_seed.php` en el docroot con el mismo patrón de token, que use `firstOrCreate` para crear SOLO el usuario (sin factories de prueba):

```php
<?php
$expected = 'OTRO_TOKEN'; if (!hash_equals($expected, $_GET['token'] ?? '')) { http_response_code(403); exit('Forbidden'); }
$appDir = __DIR__ . '/laravel_app';
require $appDir . '/vendor/autoload.php';
$app = require_once $appDir . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
header('Content-Type: text/plain');
$u = App\Models\User::firstOrCreate(
  ['email' => 'admin@example.com'],
  ['name' => 'Admin', 'password' => Illuminate\Support\Facades\Hash::make('CAMBIAR_PASSWORD')]
);
echo "OK id={$u->id} total=" . App\Models\User::count() . "\n";
```

Ejecutar por URL con su token. Cambiar la contraseña luego desde el CRM.

### Paso 6 — LIMPIEZA (CRÍTICO)
1. **Borrar** `__deploy_migrate.php` y `__deploy_seed.php` del docroot.
2. Verificar visitando sus URLs → deben dar **404 / 403**.

### Paso 7 — Verificación final
- Abrir `https://crm.torreonfuerte.org.co` → login carga sin error 500.
- Iniciar sesión y revisar el Dashboard.

---

## Notas / problemas conocidos
- El docroot es la raíz del dominio, no `laravel_app/public` → los scripts van en la raíz con `$appDir = __DIR__ . '/laravel_app'`.
- La API de cPanel `Fileman/fileop` / `delete_files` NO existe en este host: borrar por la **UI** del File Manager.
- Para despliegue 100% automático se requiere que el hosting **habilite SSH/shell** (ticket de soporte).
