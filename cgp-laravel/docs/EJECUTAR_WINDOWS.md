# Cómo ejecutar cgp-laravel en Windows con Docker, sin WSL (solo terminal)

Todo se hace desde **PowerShell**, sin instaladores gráficos. Docker Desktop usa el motor **Hyper-V** en lugar de WSL.

**Requisitos**

- Windows 10 (22H2) o Windows 11, edición **Pro, Enterprise o Education**. La edición *Home* no tiene Hyper-V y no sirve para esta guía.
- Virtualización activada en la BIOS (Intel VT-x / AMD-V).
- 8 GB de RAM o más y conexión a internet.

La base de datos es PostgreSQL (va dentro de Docker; no instalas nada más).

---

## 1. Abre PowerShell como administrador

Tecla Windows → escribe `PowerShell` → clic derecho → **Ejecutar como administrador**. Usa esta ventana para los pasos 2 a 5.

## 2. Comprueba que tu equipo sirve

```powershell
(Get-CimInstance Win32_OperatingSystem).Caption
(Get-CimInstance Win32_Processor).VirtualizationFirmwareEnabled
```

- La primera línea debe decir *Pro*, *Enterprise* o *Education* (no *Home*).
- La segunda debe responder `True`. Si dice `False`, entra a la BIOS y activa la virtualización. Si responde vacío, ya hay un hipervisor activo (normal si Hyper-V ya estaba habilitado).

## 3. Activa Hyper-V y reinicia

```powershell
Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Hyper-V -All -NoRestart
Restart-Computer
```

Al volver, abre otra vez PowerShell **como administrador**.

## 4. Instala Git y Docker Desktop

Git:

```powershell
winget install -e --id Git.Git --accept-package-agreements --accept-source-agreements
```

Docker Desktop (descarga el instalador y lo instala con el motor Hyper-V, aceptando la licencia):

```powershell
Invoke-WebRequest "https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe" -OutFile "$env:TEMP\DockerInstaller.exe"
Start-Process "$env:TEMP\DockerInstaller.exe" -Wait -ArgumentList "install","--quiet","--accept-license","--backend=hyper-v","--always-run-service"
```

Tarda unos minutos. Después agrega tu usuario al grupo de Docker y **cierra sesión** para que surta efecto:

```powershell
net localgroup docker-users $env:USERNAME /add
logoff
```

> Si dice que ya es miembro, sigue; no es un error.

> Docker Desktop es gratis para uso personal, educación y empresas pequeñas (menos de 250 empleados y menos de 10 millones de USD de ingresos). Fuera de eso requiere suscripción de pago.

## 5. Inicia Docker

Vuelve a entrar a Windows y abre PowerShell (ya no hace falta que sea administrador):

```powershell
Start-Process "C:\Program Files\Docker\Docker\Docker Desktop.exe"
while (-not (docker info 2>$null)) { Start-Sleep 5 }
docker compose version
```

El `while` espera hasta que el motor responde (puede tardar 1 o 2 minutos la primera vez). Al terminar, `docker compose version` debe imprimir una versión (v2 o superior).

## 6. Descarga el proyecto

Clona dentro de tu carpeta de usuario (Docker Desktop con Hyper-V suele compartirla por defecto):

```powershell
mkdir $HOME\proyectos
cd $HOME\proyectos
git clone https://github.com/maikCyphlock/CGP.git
cd CGP\cgp-laravel
```

Si `git` no se reconoce, cierra y abre PowerShell. Todos los comandos siguientes se ejecutan **desde `CGP\cgp-laravel`**.

## 7. Crea el `.env` e instala `vendor/`

```powershell
copy .env.example .env
docker run --rm -v "${PWD}:/app" -w /app composer:2 install --no-interaction --prefer-dist --ignore-platform-reqs
```

No hay que editar el `.env`: ya coincide con `docker-compose.yml`. El segundo comando termina con `Generating optimized autoload files` (la imagen de la app no trae Composer, por eso `vendor/` se instala aparte).

## 8. Levanta el sistema

```powershell
docker compose up -d --build
docker compose ps
```

La primera vez tarda varios minutos. `docker compose ps` debe mostrar `cgp-laravel-app` (puerto **8001**) y `cgp-postgres` (puerto 5432) en `running`. En el primer arranque Postgres crea solo las tablas con `database/sql/db.sql`.

Luego genera la clave y aplica las migraciones:

```powershell
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

`migrate` debe mostrar una lista de migraciones en `DONE`.

## 9. Crea el primer administrador

La base de datos nace **sin usuarios**. Abre Tinker:

```powershell
docker compose exec app php artisan tinker
```

Pega el bloque PHP de [`CREAR_USUARIO.md`](CREAR_USUARIO.md), sección 1.2 (clic derecho pega; si se corta, pégalo línea por línea), y escribe `exit`.

## 10. Abre el sistema

Desde el navegador:

| URL | Qué es |
|---|---|
| <http://localhost:8001/> | Portada pública |
| <http://localhost:8001/denuncias> | Formulario de denuncias |
| <http://localhost:8001/oac/login> | Panel OAC |
| <http://localhost:8001/admin/login> | Panel de Administración |

O desde la terminal: `Start-Process http://localhost:8001/admin/login`.

---

## Uso diario

```powershell
docker compose stop         # apagar (conserva los datos)
docker compose start        # encender de nuevo
docker compose logs -f app  # ver errores en vivo (Ctrl+C para salir)
docker compose down         # borrar contenedores, CONSERVA la base de datos
docker compose down -v      # ⚠ borra también la base de datos
```

Docker Desktop debe estar corriendo (paso 5). Los contenedores tienen `restart: always`, así que se levantan solos cuando Docker arranca.

Consola SQL:

```powershell
docker compose exec db psql -U cgp -d cgp -c "SELECT email, active FROM staff_user;"
```

---

## Problemas frecuentes

| Síntoma | Causa | Solución |
|---|---|---|
| `Enable-WindowsOptionalFeature` no existe o dice que la función no está disponible | Windows *Home* | Esta guía no aplica a Home; necesitas Pro/Enterprise/Education |
| `error during connect ... docker_engine ... cannot find the file specified` | Docker Desktop apagado | Paso 5 |
| *Virtualization must be enabled* / `VirtualizationFirmwareEnabled` en `False` | VT-x/AMD-V apagado | Actívalo en la BIOS |
| `docker : El término 'docker' no se reconoce` | Terminal abierta antes de instalar, o falta cerrar sesión | Cierra sesión y abre PowerShell nuevo; si sigue, reinicia |
| *Access denied* al usar `docker` | Tu usuario no está en `docker-users` | `net localgroup docker-users $env:USERNAME /add` como administrador y `logoff` |
| `mounts denied` / *drive is not shared* | La carpeta del proyecto no está compartida con Docker | Clona dentro de `$HOME` (paso 6). Si usas otra unidad, compártela en Docker Desktop → Settings → Resources → File sharing |
| `Failed opening required '...vendor/autoload.php'` | Falta `vendor/` | El segundo comando del paso 7 |
| Error 500 *No application encryption key* | Falta `APP_KEY` | `docker compose exec app php artisan key:generate` |
| `could not translate host name "db"` | La base de datos no está arriba | `docker compose up -d` y `docker compose ps` |
| `Bind for 0.0.0.0:5432 failed: port is already allocated` | Hay un PostgreSQL en Windows usando el puerto | Detén ese servicio, o cambia `"5432:5432"` por `"5433:5432"` en `docker-compose.yml` |
| `Ports are not available ... 8001` | Otro programa usa 8001 | Cambia `"8001:8000"` por `"8002:8000"` en `docker-compose.yml` y usa `http://localhost:8002` |
| Un antivirus frena la instalación | Escaneo de `vendor/` | Reintenta o excluye la carpeta del proyecto |

**No** necesitas `chmod`/`chown` ni `sudo`: eso es solo para Linux.
