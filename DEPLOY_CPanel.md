# Despliegue Laravel en cPanel con MySQL

## 1. Qué ya quedó listo en el código
- El login ya usa la tabla `users` en la base de datos.
- El controlador `app/Http/Controllers/AuthController.php` consulta el usuario real con:
  - `User::where('email', $request->email)->first()`
  - `Hash::check($request->password, $user->password)`
- El `DatabaseSeeder` crea un usuario inicial:
  - email: `admin@example.com`
  - contraseña: `password`
- El modelo `app/Models/User.php` ya está configurado como `Authenticatable`.

## 2. Configurar la base de datos en cPanel
1. Entra a cPanel y busca `MySQL Databases`.
2. Crea una base de datos, por ejemplo `crm_torreonfuerte`.
3. Crea un usuario de MySQL, por ejemplo `crm_user`, y una contraseña segura.
4. Asigna el usuario a la base de datos con todos los privilegios.

## 3. Actualizar `.env` en el servidor
Edita el archivo `.env` que subiste al servidor y reemplaza los valores de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=crm_torreonfuerte
DB_USERNAME=crm_user
DB_PASSWORD=TU_CONTRASEÑA
```

> Usa `localhost` si cPanel lo recomienda; si te pide otro host, usa el que indique.

## 4. Migrar la base de datos en el servidor
### Con SSH (recomendado)
Si el hosting tiene SSH, ve a la carpeta donde está `laravel_app` y ejecuta:

```bash
cd /home/torreonf/crm.torreonfuerte.org.co/laravel_app
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Sin SSH
- Si no tienes SSH, puedes usar `phpMyAdmin` para importar la estructura de la tabla `users`.
- Revisa `database/migrations/0001_01_01_000000_create_users_table.php` y crea la tabla manualmente.
- Después, inserta un usuario con contraseña hasheada.

## 5. Nota sobre el archivo `index.php` raíz
Este proyecto usa `laravel_app` como carpeta activa. Si tu Document Root apunta a la carpeta raíz del hosting, el archivo `index.php` principal ya debe incluir:

```php
require __DIR__.'/laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/laravel_app/bootstrap/app.php';
```

Si tu sitio apunta directamente a `laravel_app/public`, no necesitas ese archivo extra.

## 5. Si necesitas importar el usuario inicial manualmente
El usuario inicial se puede recrear con un SQL similar a este (pero solo si no puedes correr seeders):

```sql
INSERT INTO users (name, email, password, created_at, updated_at)
VALUES ('Administrador', 'admin@example.com', '$2y$10$...hash...', NOW(), NOW());
```

> Mejor deja correr `php artisan db:seed --force` si tienes SSH.

## 6. Ajustes finales en el servidor
- Verifica que existan estas carpetas y tengan permisos `775`:
  - `laravel_app/storage`
  - `laravel_app/bootstrap/cache`
- Si tu Document Root es la carpeta `public` dentro de `laravel_app`, usa:
  - `/home/torreonf/crm.torreonfuerte.org.co/laravel_app/public`
- Si no puedes cambiar el root, mantén `index.php` en la raíz y con rutas a `laravel_app`.

## 7. Migraciones y roles
- Después de configurar `.env` para MySQL, asegúrate de ejecutar las migraciones para crear la columna `role` añadida por la última migración:

```bash
cd /home/torreonf/crm.torreonfuerte.org.co/laravel_app
php artisan migrate --force
php artisan db:seed --force
```

El seeder crea un usuario inicial con rol `admin` (email `admin@example.com`, contraseña `password`).

## 8. Crear y subir el paquete de despliegue (recomendado)

Puedes subir todo el directorio `laravel_app` comprimido desde el equipo local a cPanel usando File Manager o FTP. Para reducir tamaño, excluye `node_modules` y (opcionalmente) `vendor` si vas a ejecutar `composer install` en el servidor.

En Windows puedes crear un ZIP limpio desde la raíz del proyecto (donde está `laravel_app`) con PowerShell:

```powershell
Set-Location -LiteralPath 'C:\Path\To\workspace'
# crea laravel_app_deploy.zip excluyendo node_modules/vendor
$src='laravel_app'; $tmp='laravel_app_deploy_temp'; $zip='laravel_app_deploy.zip'
if (Test-Path $tmp) { Remove-Item -Recurse -Force $tmp }
New-Item -ItemType Directory -Path $tmp | Out-Null
Copy-Item -Path "$src\*" -Destination $tmp -Recurse -Force -Exclude 'node_modules','vendor','.git','.github'
Compress-Archive -Path "$tmp\*" -DestinationPath $zip -Force
Remove-Item -Recurse -Force $tmp
```

Sube `laravel_app_deploy.zip` con File Manager y extrae en la ruta deseada.

## 9. Comandos recomendados en servidor con SSH

Si tienes SSH, conéctate y ejecuta (ajusta rutas y usuario según tu hosting):

```bash
cd /home/tu_usuario/tu_dominio/laravel_app
# (opcional) si no subiste vendor
composer install --no-dev --optimize-autoloader

# permisos
chown -R youruser:youruser storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# migrar y seed (crear admin)
php artisan migrate --force
php artisan db:seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Reemplaza `youruser` por el usuario del sistema según tu hosting (a veces es tu nombre de cuenta cPanel).

## 10. Sin SSH (File Manager / phpMyAdmin)

- Sube y extrae `laravel_app_deploy.zip` con File Manager.
- En `laravel_app/.env` configura `DB_CONNECTION=mysql` y credenciales.
- Crea la base de datos y usuario en cPanel → MySQL Databases.
- En `phpMyAdmin`, importa la estructura de `database/migrations` (o crea la tabla `users` manualmente usando el SQL de la migración).
- Inserta el usuario admin con la contraseña hasheada (usa `php artisan tinker` local para generar el hash si no tienes SSH):

```php
// en tu máquina local
php artisan tinker
echo \Illuminate\Support\Facades\Hash::make('password');
```

Usa el resultado para insertar en `phpMyAdmin`:

```sql
INSERT INTO users (name, email, password, created_at, updated_at, role)
VALUES ('Administrador', 'admin@example.com', 'HASH_GENERADO', NOW(), NOW(), 'admin');
```

## 11. Pruebas finales

- Abre `https://tu-dominio` y prueba login con `admin@example.com` / `password`.
- Si obtienes errores, revisa `laravel_app/storage/logs/laravel.log`.


## 7. Prueba de login
Visita:

```text
https://crm.torreonfuerte.org.co
```

Ingresa con:

- Email: `admin@example.com`
- Contraseña: `password`

## 8. Notas importantes
- Si el sitio arroja `Error 500`, revisa `laravel_app/storage/logs/laravel.log`.
- Si hay problemas de permiso, asegúrate de que `storage` y `bootstrap/cache` sean escribibles.
- Si estás en producción, mantén `APP_DEBUG=false`.
- La interfaz (AdminLTE 4 + Bootstrap 5) se compila con `npm install && npm run build` dentro de `laravel_app/`; sube la carpeta `laravel_app/public/build` junto con el resto. Define `APP_SISTEMA` en `.env` si quieres otro nombre en pestañas y pie.
- Después de subir cambios en vistas, ejecuta `php artisan view:clear && php artisan view:cache` para que las pantallas respondan más rápido.
- Esta versión añade migraciones (columnas `celular`, `debe_cambiar_clave` y `clave_vence_en` en `users`, y las tablas `solicitudes_clave` y `registros_clave`): ejecuta `php artisan migrate`. Los usuarios existentes conservan su contraseña; para enviarles una clave nueva hay que completar su celular de WhatsApp.
- Esta versión añade la migración `2026_10_25_000001_drop_redes` (retira la tabla `redes` y la columna `personas.red_id`; no se pierde ninguna persona): haz una copia de la base antes de ejecutar `php artisan migrate`.
