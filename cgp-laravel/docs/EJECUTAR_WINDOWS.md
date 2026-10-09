# Cómo ejecutar cgp-laravel en Windows (paso a paso)

Guía para Windows 10 (22H2) y Windows 11, **con WSL y sin WSL**.

- La base de datos es **PostgreSQL**. No sirve SQLite ni MySQL.
- Los comandos van en **PowerShell** (no CMD). Para abrirlo: tecla Windows → escribe `PowerShell` → Enter. Mejor aún: **Windows Terminal**.
- Para crear usuarios y cambiar permisos usa [`CREAR_USUARIO.md`](CREAR_USUARIO.md).

---

## 0. Elige tu camino

Primero mira tu edición de Windows: **Inicio → Configuración → Sistema → Acerca de → Especificaciones de Windows → Edición** (o ejecuta `winver`).

| Camino | Usa | Edición de Windows | ¿WSL? | Instala |
|---|---|---|:-:|---|
| **1. Docker con WSL 2** | Docker Desktop | Home, Pro, Enterprise, Education | Sí | Git, Docker Desktop |
| **2. Docker con Hyper-V** | Docker Desktop | **Solo Pro, Enterprise, Education** | **No** | Git, Docker Desktop |
| **3. Sin Docker** | PHP y PostgreSQL directos en Windows | **Cualquiera** (incluida Home) | **No** | Git, PHP, Composer, PostgreSQL |

- ¿Tienes **Home** y no quieres WSL? Ve al **Camino 3**.
- ¿Tienes **Pro/Enterprise/Education** y no quieres WSL? **Camino 2** (o el 3).
- ¿No te importa WSL? **Camino 1** es el más simple y el más usado.

Todos los caminos empiezan con los pasos **A** (Git) y **B** (descargar el proyecto). Luego sigue la sección de tu camino.

---

## A. Instala Git (todos los caminos)

Descarga de <https://git-scm.com/download/win> e instala con las opciones por defecto. Comprueba en PowerShell:

```powershell
git --version
```

## B. Descarga el proyecto (todos los caminos)

Elige una carpeta **corta, sin espacios ni tildes**, por ejemplo `C:\proyectos`:

```powershell
mkdir C:\proyectos
cd C:\proyectos
git clone https://github.com/maikCyphlock/CGP.git
cd CGP\cgp-laravel
```

Todos los comandos siguientes se ejecutan **desde `CGP\cgp-laravel`**.

> El repositorio fuerza finales de línea LF (`.gitattributes`); no cambies `core.autocrlf` a mano.

---

# CAMINOS 1 y 2 — Docker Desktop

## C. Instala Docker Desktop

### Camino 1 (con WSL 2)

1. Descarga de <https://www.docker.com/products/docker-desktop/> e instala.
2. En la pantalla de configuración, deja marcada **Use WSL 2 instead of Hyper-V**. Si pide instalar o actualizar WSL, acepta y **reinicia el equipo**.

### Camino 2 (con Hyper-V, sin WSL)

Solo Windows **Pro, Enterprise o Education**. La edición *Home* no tiene Hyper-V.

1. Abre **PowerShell como administrador** (clic derecho → *Ejecutar como administrador*) y activa Hyper-V:

   ```powershell
   Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Hyper-V -All
   ```

   Reinicia cuando lo pida.
2. Descarga Docker Desktop de <https://www.docker.com/products/docker-desktop/>.
3. Ejecuta el instalador y elige instalación **para todos los usuarios** (*All users*). Hyper-V **no está disponible** en la instalación "solo para mi usuario".
4. En la pantalla de configuración, **desmarca** *Use WSL 2 instead of Hyper-V*.
5. Si tu cuenta de Windows no es la de administrador que instaló, agrégala al grupo `docker-users` (PowerShell como administrador) y **cierra sesión y vuelve a entrar**:

   ```powershell
   net localgroup docker-users TU_USUARIO /add
   ```
6. Después de abrir Docker Desktop, entra a **Settings → Resources → File sharing** y verifica que la unidad donde clonaste el proyecto (p. ej. `C:\`) esté compartida. Con Hyper-V Docker puede pedir tu **contraseña de Windows** para compartirla: es normal.

   > Con Hyper-V el proyecto debe estar en una unidad compartida; si `docker compose up` falla con *"drive is not shared"* o *"mounts denied"*, el problema es este paso.

### Ambos caminos: arranca y comprueba

1. Abre **Docker Desktop** y espera a que abajo a la izquierda diga **Engine running** (ícono verde). Hasta entonces los comandos `docker` fallan.
2. Comprueba en PowerShell:

   ```powershell
   docker --version
   docker compose version
   ```

Si Windows o Docker dicen que la **virtualización está desactivada**, actívala en la BIOS (Intel VT-x / AMD-V / SVM Mode) y reinicia. Verifícalo en Administrador de tareas → Rendimiento → CPU → *Virtualización: Habilitada*.

## D. Prepara `.env` y `vendor/`

Desde `CGP\cgp-laravel`:

```powershell
copy .env.example .env
```

No hay que editar nada: los valores ya coinciden con `docker-compose.yml`.

> Si ya existe un `.env`, no lo sobrescribas. Revisa que tenga `DB_CONNECTION=pgsql` y `DB_HOST=db`.

La imagen de Docker **no trae Composer**, así que `vendor/` se instala antes, sin instalar nada más en Windows:

```powershell
docker run --rm -v "${PWD}:/app" -w /app composer:2 install --no-interaction --prefer-dist --ignore-platform-reqs
```

Tarda unos minutos y termina con `Generating optimized autoload files`. (En CMD cambia `${PWD}` por `%cd%`.)

## E. Levanta el sistema

```powershell
docker compose up -d --build
```

La primera vez tarda varios minutos. Crea dos contenedores:

| Contenedor | Qué es | Puerto |
|---|---|---|
| `cgp-laravel-app` | Laravel | **8001** |
| `cgp-postgres` | Base de datos | 5432 |

En el primer arranque Postgres crea solo todas las tablas con `database/sql/db.sql`. Comprueba:

```powershell
docker compose ps
```

Ambos deben decir `running` (también en Docker Desktop → *Containers*). Luego:

```powershell
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

`migrate` debe mostrar una lista de migraciones en `DONE`.

## F. Crea el primer administrador

La base de datos nace **sin usuarios**. Abre Tinker:

```powershell
docker compose exec app php artisan tinker
```

Pega el bloque PHP de [`CREAR_USUARIO.md`](CREAR_USUARIO.md), sección 1.2 (clic derecho pega; si se corta, pégalo línea por línea) y escribe `exit`.

## G. Abre el sistema

| URL | Qué es |
|---|---|
| <http://localhost:8001/> | Portada pública |
| <http://localhost:8001/denuncias> | Formulario de denuncias |
| <http://localhost:8001/oac/login> | Panel OAC |
| <http://localhost:8001/admin/login> | Panel de Administración |

## H. Uso diario (Docker)

```powershell
docker compose stop         # apagar (conserva los datos)
docker compose start        # encender de nuevo
docker compose logs -f app  # ver errores en vivo (Ctrl+C para salir)
docker compose down         # borrar contenedores, CONSERVA la base de datos
docker compose down -v      # ⚠ borra también la base de datos
```

Docker Desktop debe estar abierto. Tras reiniciar el PC, ábrelo y los contenedores (`restart: always`) se levantan solos.

---

# CAMINO 3 — Sin Docker y sin WSL (PHP + PostgreSQL en Windows)

Funciona en cualquier edición. Instalas PHP, Composer y PostgreSQL directamente. Hazlo **en este orden**. Ya hiciste los pasos A y B.

## 3.1 Instala PostgreSQL 17

1. Descarga el instalador de Windows en <https://www.postgresql.org/download/windows/> (botón *Download the installer*, versión 17).
2. Ejecútalo con las opciones por defecto, con estos detalles:
   - **Password** del superusuario `postgres`: elige una y **anótala**.
   - **Port**: `5432`.
   - Deja marcados *PostgreSQL Server*, *pgAdmin* y *Command Line Tools*. *Stack Builder* no hace falta.
3. Al terminar, PostgreSQL queda como servicio de Windows y arranca solo con el equipo.

Crea el usuario y la base de datos del proyecto. En PowerShell (te pide la contraseña de `postgres` del paso anterior):

```powershell
& "C:\Program Files\PostgreSQL\17\bin\psql.exe" -U postgres -c "CREATE USER cgp WITH PASSWORD 'cgp' SUPERUSER;" -c "CREATE DATABASE cgp OWNER cgp;"
```

Debe responder `CREATE ROLE` y `CREATE DATABASE`. (`cgp` queda como superusuario igual que en Docker; es solo para desarrollo local.)

## 3.2 Instala PHP 8.3 o superior

1. Instala el **Microsoft Visual C++ Redistributable x64**: <https://aka.ms/vs/17/release/vc_redist.x64.exe>.
2. Entra a <https://windows.php.net/download/>, elige **PHP 8.4** (o 8.3) y descarga el **ZIP x64 *Non Thread Safe***.
3. Crea la carpeta `C:\php` y extrae ahí todo el contenido del ZIP (debe quedar `C:\php\php.exe`).
4. Agrega `C:\php` al **PATH**: tecla Windows → escribe *variables de entorno* → *Editar las variables de entorno del sistema* → **Variables de entorno…** → en *Variables de usuario* selecciona **Path** → **Editar** → **Nuevo** → `C:\php` → Aceptar en todo.
5. **Cierra y vuelve a abrir PowerShell** y comprueba:

   ```powershell
   php -v
   ```

Configura PHP (copia el archivo de configuración y activa lo que necesita Laravel y el PDF):

```powershell
cd C:\php
copy php.ini-development php.ini
(Get-Content php.ini) -replace '^;(extension_dir = "ext")$','$1' -replace '^;(extension=(curl|fileinfo|gd|mbstring|openssl|pdo_pgsql|pgsql|zip))\s*$','$1' | Set-Content php.ini -Encoding ascii
Add-Content php.ini "upload_max_filesize=12M"
Add-Content php.ini "post_max_size=64M"
php -m
```

`php -m` debe listar, entre otras: `curl`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_pgsql`, `pgsql`, `zip`. Si falta alguna, abre `C:\php\php.ini` con el Bloc de notas, busca esa línea (`;extension=gd`) y quítale el `;` del principio.

Si `php` se queja de `libpq.dll` o de una extensión que "no se pudo cargar", confirma que `C:\php` está en el PATH y que instalaste el Visual C++ del paso 1.

## 3.3 Instala Composer

Descarga y ejecuta <https://getcomposer.org/Composer-Setup.exe>. Detecta solo `C:\php\php.exe`. Siguiente en todo. **Abre PowerShell nuevo** y comprueba:

```powershell
composer --version
```

## 3.4 Configura el proyecto

Desde `C:\proyectos\CGP\cgp-laravel`:

```powershell
copy .env.example .env
composer install
```

Abre `.env` con el **Bloc de notas** o VS Code y cambia **solo** estas dos líneas (usa tu propia clave si cambiaste la de `cgp`):

```dotenv
DB_HOST=127.0.0.1
APP_URL=http://127.0.0.1:8000
```

> No uses PowerShell para editar el `.env` (puede guardarlo con un carácter invisible al inicio y Laravel fallaría). Guarda con codificación **UTF-8** simple.

Luego:

```powershell
php artisan key:generate
php artisan migrate
```

Aquí `migrate` crea **todo** el esquema (carga `database/sql/db.sql`) y luego aplica el resto de migraciones. Debe terminar con todas en `DONE`.

## 3.5 Crea el primer administrador

```powershell
php artisan tinker
```

Pega el bloque PHP de [`CREAR_USUARIO.md`](CREAR_USUARIO.md), sección 1.2, y escribe `exit`.

## 3.6 Arranca el servidor

```powershell
php artisan serve
```

Deja esa ventana **abierta** (se apaga con `Ctrl+C`). Si el Firewall de Windows pregunta, permite el acceso en redes privadas. Abre:

| URL | Qué es |
|---|---|
| <http://127.0.0.1:8000/> | Portada pública |
| <http://127.0.0.1:8000/denuncias> | Formulario de denuncias |
| <http://127.0.0.1:8000/oac/login> | Panel OAC |
| <http://127.0.0.1:8000/admin/login> | Panel de Administración |

## 3.7 Uso diario (sin Docker)

1. Comprueba que el servicio de PostgreSQL esté corriendo (`services.msc` → *postgresql-x64-17* → *En ejecución*).
2. `cd C:\proyectos\CGP\cgp-laravel` y `php artisan serve`.
3. Consola SQL: `& "C:\Program Files\PostgreSQL\17\bin\psql.exe" -U cgp -d cgp` (`\dt` lista tablas, `\q` sale).
4. En [`CREAR_USUARIO.md`](CREAR_USUARIO.md) quita `docker compose exec app` de los comandos de Laravel; para las consultas SQL usa el comando de `psql` anterior.

---

# Problemas frecuentes

| Síntoma | Camino | Causa | Solución |
|---|:-:|---|---|
| `error during connect ... docker_engine ... cannot find the file specified` | 1, 2 | Docker Desktop apagado | Ábrelo y espera a *Engine running* |
| *WSL 2 installation is incomplete* | 1 | Falta o está viejo WSL | PowerShell **como administrador**: `wsl --update` (o `wsl --install`) y reinicia. ¿No quieres WSL? Camino 2 o 3 |
| *Hyper-V is not enabled / not available* | 2 | Edición Home, o función apagada | Home no tiene Hyper-V → usa el Camino 3. En Pro: paso C de Camino 2 |
| *Virtualization must be enabled* | 1, 2 | VT-x/SVM apagado | Actívalo en la BIOS |
| `docker : El término 'docker' no se reconoce` | 1, 2 | Terminal abierta antes de instalar | Cierra y abre PowerShell; si sigue, reinicia |
| *mounts denied* / *drive is not shared* | 2 | Unidad sin compartir | Docker Desktop → Settings → Resources → File sharing |
| `Failed opening required '...vendor/autoload.php'` | todos | Falta `vendor/` | Docker: paso D · Camino 3: `composer install` |
| Error 500 *No application encryption key* | todos | Falta `APP_KEY` | `php artisan key:generate` (con `docker compose exec app` en Docker) |
| `could not translate host name "db"` | 3 | `.env` quedó con `DB_HOST=db` | Cámbialo a `127.0.0.1` |
| `could not translate host name "db"` | 1, 2 | Base de datos apagada | `docker compose up -d` y `docker compose ps` |
| `could not find driver` | 3 | Falta `pdo_pgsql` en `php.ini` | Paso 3.2: activa `extension=pdo_pgsql` y `extension=pgsql`; abre PowerShell nuevo |
| `SQLSTATE[08006] ... connection refused` | 3 | PostgreSQL apagado o puerto distinto | `services.msc` → iniciar *postgresql-x64-17*; revisa `DB_PORT` |
| `password authentication failed for user "cgp"` | 3 | Usuario no creado o clave distinta | Repite el `CREATE USER` del paso 3.1 o iguala `DB_PASSWORD` en `.env` |
| `permission denied to create extension` | 3 | `cgp` no es superusuario | `ALTER USER cgp SUPERUSER;` como `postgres` |
| `Bind for 0.0.0.0:5432 failed: port is already allocated` | 1, 2 | Tienes PostgreSQL instalado en Windows | Detén ese servicio (`services.msc`) o cambia `"5432:5432"` por `"5433:5432"` en `docker-compose.yml` |
| `Ports are not available ... 8001` | 1, 2 | Otro programa usa 8001 | Cambia `"8001:8000"` por `"8002:8000"` y usa `http://localhost:8002` |
| `Address already in use` al hacer `serve` | 3 | Puerto 8000 ocupado | `php artisan serve --port=8002` y usa ese puerto en `APP_URL` |
| Páginas muy lentas | 1, 2 | Proyecto en `C:\` montado en Docker | Normal en Windows; el Camino 3 es el más rápido |
| Un antivirus frena la instalación | todos | Escaneo de `vendor/` | Reintenta o excluye la carpeta del proyecto |
| Subir archivos falla | 3 | Límites de PHP | Revisa `upload_max_filesize=12M` y `post_max_size=64M` al final de `php.ini` |

**No** necesitas `chmod`/`chown` ni `sudo`: eso es solo para Linux.
