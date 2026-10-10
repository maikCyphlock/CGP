<#
.SYNOPSIS
  Instala y enciende cgp-laravel en Windows con Docker Desktop (motor Hyper-V, sin WSL).

.DESCRIPTION
  Un solo script: activa Hyper-V, instala Git y Docker Desktop, descarga el proyecto y
  luego llama a levantar-docker.ps1 (configura, enciende y crea el administrador).
  Se puede ejecutar varias veces: salta lo que ya esta hecho.

.PARAMETER Carpeta  Donde se descarga el proyecto.        (por defecto: $HOME\proyectos)
.PARAMETER Puerto   Puerto de la pagina en tu equipo.     (por defecto: 8001)
.PARAMETER Correo   Correo del administrador inicial.     (por defecto: admin@admin.com)
.PARAMETER Clave    Contrasena del administrador inicial. (por defecto: admin)

.EXAMPLE
  .\instalar-windows.ps1
  .\instalar-windows.ps1 -Puerto 8080 -Correo jefe@cgp.gob -Clave "OtraClave123"
#>
param(
    [string]$Carpeta = "$HOME\proyectos",
    [int]$Puerto = 8001,
    [string]$Correo = 'admin@admin.com',
    [string]$Clave = 'admin'
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'   # sin barra: acelera mucho las descargas en PowerShell 5
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$Repo = 'https://github.com/maikCyphlock/CGP.git'
$DockerExe = "$env:ProgramFiles\Docker\Docker\Docker Desktop.exe"

# --- Pide permisos de administrador y se vuelve a lanzar ---
$esAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $esAdmin) {
    Write-Host 'Se necesitan permisos de administrador. Acepta la ventana que aparece...'
    $argumentos = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', "`"$PSCommandPath`"",
        '-Carpeta', "`"$Carpeta`"", '-Puerto', $Puerto, '-Correo', "`"$Correo`"", '-Clave', "`"$Clave`"")
    Start-Process powershell -Verb RunAs -ArgumentList $argumentos
    exit
}

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

# Tras instalar algo, esta ventana no conoce el programa nuevo hasta releer el PATH.
function ActualizarPath {
    $env:Path = [Environment]::GetEnvironmentVariable('Path', 'Machine') + ';' + [Environment]::GetEnvironmentVariable('Path', 'User')
}

function Esperar($texto, $segundos, [scriptblock]$listo) {
    $limite = (Get-Date).AddSeconds($segundos)
    Write-Host -NoNewline $texto
    while (-not (& $listo)) {
        if ((Get-Date) -gt $limite) { Write-Host ''; return $false }
        Write-Host -NoNewline '.'; Start-Sleep 5
    }
    Write-Host ' listo'
    return $true
}

try {
    # --- 1. Docker ---
    Paso '1/3  Docker'
    ActualizarPath
    if ((Silencioso docker info) -ne 0) {
        if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
            if ((Get-CimInstance Win32_OperatingSystem).Caption -match 'Home') {
                throw 'Tu Windows es la edicion Home, que no tiene Hyper-V. Hace falta Windows Pro, Enterprise o Education.'
            }
            $hyperv = (Get-WindowsOptionalFeature -Online -FeatureName Microsoft-Hyper-V).State
            if ($hyperv -ne 'Enabled') {
                Write-Host 'Activando Hyper-V...'
                Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Hyper-V -All -NoRestart | Out-Null
                Write-Host "`nHay que reiniciar la computadora. Cuando vuelva, ejecuta este mismo script otra vez." -ForegroundColor Yellow
                if ((Read-Host 'Reiniciar ahora? (S/N)') -match '^[sS]') { Restart-Computer }
                exit
            }
            Write-Host 'Descargando e instalando Docker Desktop (tarda varios minutos)...'
            $instalador = "$env:TEMP\DockerInstaller.exe"
            Invoke-WebRequest 'https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe' -OutFile $instalador
            Start-Process $instalador -Wait -ArgumentList 'install', '--quiet', '--accept-license', '--backend=hyper-v', '--always-run-service'
            Silencioso net localgroup docker-users $env:USERNAME /add | Out-Null
            ActualizarPath
        }
        Write-Host 'Encendiendo Docker (1 o 2 minutos)...'
        Start-Process $DockerExe
        if (-not (Esperar 'Esperando a Docker' 360 { (Silencioso docker info) -eq 0 })) {
            throw 'Docker no arranco. Revisa que la virtualizacion este activada en la BIOS. Si Docker si abrio, cierra sesion en Windows, vuelve a entrar y ejecuta este script otra vez.'
        }
    }
    else { Write-Host 'Docker ya esta funcionando.' }

    # --- 2. Proyecto ---
    Paso '2/3  Proyecto'
    if (Test-Path "$PSScriptRoot\docker-compose.yml") { $dir = $PSScriptRoot }
    else {
        if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
            if (-not (Get-Command winget -ErrorAction SilentlyContinue)) { throw 'Falta instalar Git. Descargalo de https://git-scm.com/download/win y ejecuta este script otra vez.' }
            Write-Host 'Instalando Git...'
            Ejecutar winget install -e --id Git.Git --accept-package-agreements --accept-source-agreements
            ActualizarPath
        }
        $raiz = Join-Path $Carpeta 'CGP'
        if (Test-Path "$raiz\.git") {
            try { Ejecutar git -C $raiz pull --ff-only } catch { Write-Warning 'No se pudo actualizar; se usa la version que ya tienes.' }
        }
        else {
            New-Item -ItemType Directory -Path $Carpeta -Force | Out-Null
            Ejecutar git clone $Repo $raiz
        }
        $dir = "$raiz\cgp-laravel"
    }
    Set-Location $dir
    Write-Host "Carpeta: $dir"

    # --- 3. Encender (comandos de Docker) ---
    Paso '3/3  Encendiendo el sistema'
    & "$dir\levantar-docker.ps1" -Puerto $Puerto -Correo $Correo -Clave $Clave
    Start-Process "http://localhost:$Puerto/admin/login"
}
catch {
    Write-Host "`nERROR: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host 'Copia este mensaje completo y envialo a quien te dio el script.'
}
Read-Host "`nPresiona Enter para cerrar"
