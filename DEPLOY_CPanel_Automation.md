# Automatización de despliegue a cPanel

Esta guía explica cómo automatizar el despliegue de `laravel_app` a un servidor cPanel usando SSH/SCP y un script de PowerShell.

## Requisitos previos
- Acceso SSH al servidor cPanel.
- `scp` y `ssh` disponibles en tu máquina local.
- `composer`, `php`, y `unzip` en el servidor.
- Si quieres compilar assets en el servidor, también `node` y `npm`.
- Si no tienes SSH, sigue usando File Manager, pero esta guía explica la mejor práctica con SSH.

## Archivos incluidos
- `deploy-cpanel.ps1`: script local de PowerShell que crea el ZIP, sube el paquete por SCP y ejecuta comandos remotos.

## Uso básico
1. Abre PowerShell en la carpeta del workspace (`C:\WorkSpace\crm 2`).
2. Ejecuta:

```powershell
.\deploy-cpanel.ps1 -RemoteHost "mi-servidor.com" -RemoteUser "mi_usuario" -RemotePath "/home/mi_usuario/mi_dominio/" 
```

3. El script hará:
- crear `laravel_app_deploy.zip` excluyendo `node_modules`, `vendor`, `.git` y `.github`
- subirlo al servidor con `scp`
- descomprimirlo
- ejecutar `composer install --no-dev --optimize-autoloader`
- ejecutar migraciones y seeders
- limpiar caches de Laravel

## Opciones útiles
- `-IncludeVendor`: incluye la carpeta `vendor` en el ZIP si no quieres ejecutar `composer install` en el servidor.
- `-SkipBuild`: evita compilar assets localmente.

Ejemplo con opciones:

```powershell
.\deploy-cpanel.ps1 -RemoteHost "mi-servidor.com" -RemoteUser "mi_usuario" -RemotePath "/home/mi_usuario/mi_dominio/" -IncludeVendor
```

## Configuración de `.env`
Asegúrate de tener `laravel_app/.env` en el servidor con tu configuración de MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=<DB_NAME>
DB_USERNAME=<DB_USER>
DB_PASSWORD=<DB_PASSWORD>
APP_URL=https://<tu-dominio>
APP_ENV=production
APP_DEBUG=false
```

## Ruta de despliegue recomendada
Si el servidor usa la carpeta `public` de Laravel correctamente, la ruta de despliegue debería ser algo como:

`/home/mi_usuario/mi_dominio/laravel_app`

Si el hosting no te permite usar `public` como root, el proyecto puede requerir un `index.php` de nivel superior apuntando a `laravel_app`.

## Workflow recomendado
1. Actualiza tu código local en `laravel_app`.
2. Ejecuta el script desde tu máquina:

```powershell
.\deploy-cpanel.ps1 -RemoteHost "mi-servidor.com" -RemoteUser "mi_usuario" -RemotePath "/home/mi_usuario/mi_dominio/"
```

3. Confirma que el despliegue funcionó abriendo tu dominio.
4. Si hay errores, revisa `laravel_app/storage/logs/laravel.log` en el servidor.

## Alternativa con Git
Si tu servidor tiene Git y el repositorio en el servidor, es más estable usar:

```bash
cd /home/mi_usuario/mi_dominio/laravel_app
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Este método evita subir ZIP y simplifica cambios continuos.
