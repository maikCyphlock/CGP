<#
.SYNOPSIS
  Enciende cgp-laravel con Docker. No instala nada: solo ejecuta los comandos de Docker.

.DESCRIPTION
  Requiere Docker Desktop ya instalado y encendido. Desde esta carpeta (cgp-laravel):
  crea el .env, instala las dependencias PHP, levanta los contenedores, migra la base
  de datos y crea el usuario administrador. Se puede repetir sin problema.

.PARAMETER Puerto  Puerto de la pagina en tu equipo.      (por defecto: 8001)
.PARAMETER Correo  Correo del administrador inicial.      (por defecto: admin@admin.com)
.PARAMETER Clave   Contrasena del administrador inicial.  (por defecto: admin)

.EXAMPLE
  .\levantar-docker.ps1
  .\levantar-docker.ps1 -Puerto 8080 -Correo jefe@cgp.gob -Clave "OtraClave123"
#>
param(
    [int]$Puerto = 8001,
    [string]$Correo = 'admin@admin.com',
    [string]$Clave = 'admin'
)

$ErrorActionPreference = 'Stop'
$dir = $PSScriptRoot
Set-Location $dir

function Paso($texto) { Write-Host "`n==> $texto" -ForegroundColor Cyan }

# Ejecuta un programa y falla si termina con error (PowerShell no lo hace solo).
function Ejecutar {
    $previo = $ErrorActionPreference; $ErrorActionPreference = 'Continue'
    $programa = $args[0]; $resto = @($args | Select-Object -Skip 1)
    try { & $programa @resto; $codigo = $LASTEXITCODE } catch { $codigo = -1 }
    $ErrorActionPreference = $previo
    if ($codigo -ne 0) { throw "Fallo el comando: $($args -join ' ')" }
}

# Igual, pero sin mostrar nada; devuelve el codigo de salida (-1 si el programa no existe).
function Silencioso {
    $previo = $ErrorActionPreference; $ErrorActionPreference = 'Continue'
    $programa = $args[0]; $resto = @($args | Select-Object -Skip 1)
    try { & $programa @resto *> $null; $codigo = $LASTEXITCODE } catch { $codigo = -1 }
    $ErrorActionPreference = $previo
    return $codigo
}

Paso '1/4  Revisando Docker'
if ((Silencioso docker info) -ne 0) {
    throw 'Docker no esta funcionando. Abre Docker Desktop, espera a que diga "Engine running" y ejecuta este script otra vez.'
}

Paso '2/4  Configuracion'
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
$conf = [IO.File]::ReadAllText("$dir\.env")
$conf = $conf -replace '(?m)^APP_URL=.*$', "APP_URL=http://localhost:$Puerto"
if ($conf -match '(?m)^APP_PORT=') { $conf = $conf -replace '(?m)^APP_PORT=.*$', "APP_PORT=$Puerto" }
else { $conf = $conf.TrimEnd() + "`nAPP_PORT=$Puerto`n" }
[IO.File]::WriteAllText("$dir\.env", $conf, (New-Object Text.UTF8Encoding $false))   # UTF-8 sin BOM

if (-not (Test-Path vendor\autoload.php)) {
    Write-Host 'Instalando dependencias PHP (tarda unos minutos)...'
    Ejecutar docker run --rm -v "${dir}:/app" -w /app composer:2 install --no-interaction --prefer-dist --ignore-platform-reqs
}

Paso '3/4  Encendiendo el sistema (la primera vez tarda varios minutos)'
Ejecutar docker compose up -d --build
$limite = (Get-Date).AddMinutes(3)
Write-Host -NoNewline 'Esperando la base de datos'
while ((Silencioso docker compose exec -T db pg_isready -U cgp -d cgp) -ne 0) {
    if ((Get-Date) -gt $limite) { throw 'La base de datos no arranco. Mira el motivo con: docker compose logs db' }
    Write-Host -NoNewline '.'; Start-Sleep 3
}
Write-Host ' lista'

Paso '4/4  Base de datos y usuario administrador'
if ([IO.File]::ReadAllText("$dir\.env") -notmatch '(?m)^APP_KEY=\S') { Ejecutar docker compose exec -T app php artisan key:generate --force }
Ejecutar docker compose exec -T app php artisan migrate --force
Ejecutar docker compose exec -T app php artisan cgp:admin --defecto --correo $Correo --clave $Clave

$url = "http://localhost:$Puerto"
Write-Host @"

  El sistema esta funcionando.

  Administracion : $url/admin/login
  Oficina (OAC)  : $url/oac/login
  Pagina publica : $url/

  Usuario : $Correo
  Clave   : $Clave

  Para apagarlo : docker compose stop
  Para prenderlo: docker compose start     (con Docker Desktop abierto)

"@ -ForegroundColor Green
if ($Clave -eq 'admin') { Write-Host '  Aviso: esta clave es solo para pruebas. Si el sistema es real, cambia el usuario (ver docs\CREAR_USUARIO.md).' -ForegroundColor Yellow }
