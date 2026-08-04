param(
    [string]$RemoteHost = "example.com",
    [string]$RemoteUser = "cpaneluser",
    [string]$RemotePath = "/home/cpaneluser/yourdomain/laravel_app",
    [switch]$IncludeVendor,
    [switch]$SkipBuild
)

$workspace = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $workspace

$packageName = "laravel_app_deploy.zip"
$deployDir = Join-Path $workspace "deploy-temp"

if (Test-Path $deployDir) {
    Remove-Item -Recurse -Force $deployDir
}
New-Item -ItemType Directory -Path $deployDir | Out-Null

$exclude = @('node_modules','vendor','.git','.github')
$copyParams = @{ Path = "laravel_app\*"; Destination = $deployDir; Recurse = $true; Force = $true }
if ($exclude.Count -gt 0) { $copyParams['Exclude'] = $exclude }
Copy-Item @copyParams

if (-not $IncludeVendor) {
    $vendorPath = Join-Path $deployDir 'vendor'
    if (Test-Path $vendorPath) { Remove-Item -Recurse -Force $vendorPath }
}

if (-not $SkipBuild) {
    Write-Host "Compilando assets locales..."
    Set-Location (Join-Path $workspace 'laravel_app')
    if (Test-Path 'package.json') {
        npm.cmd install | Out-Null
        npm.cmd run build | Out-Null
    }
    Set-Location $workspace
}

if (Test-Path $packageName) { Remove-Item -Force $packageName }
Compress-Archive -Path "$deployDir\*" -DestinationPath $packageName -Force
Remove-Item -Recurse -Force $deployDir

Write-Host "Archivo de despliegue generado: $packageName"

$remoteZip = Join-Path $RemotePath $packageName

Write-Host "Subiendo paquete a $RemoteUser@$RemoteHost:$RemotePath"
scp $packageName "$RemoteUser@$RemoteHost:$RemotePath"

$remoteCommands = @(
    "cd $RemotePath",
    "unzip -o $packageName -d .",
    "rm -f $packageName",
    "cd $RemotePath/laravel_app",
    "composer install --no-dev --optimize-autoloader",
    "php artisan migrate --force",
    "php artisan db:seed --force",
    "php artisan config:cache",
    "php artisan route:cache",
    "php artisan view:cache"
) -join "; "

Write-Host "Ejecutando comandos remotos en el servidor..."
ssh "$RemoteUser@$RemoteHost" "$remoteCommands"

Write-Host "Despliegue automatizado completado. Verifica el sitio en el servidor."