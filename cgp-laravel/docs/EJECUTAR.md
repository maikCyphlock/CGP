# Cómo ejecutar cgp-laravel (paso a paso)

> Esta guía es para Linux/macOS. ¿Usas **Windows**? Ve a [`EJECUTAR_WINDOWS.md`](EJECUTAR_WINDOWS.md) (Docker sin WSL, solo terminal).

Sistema web de la Contraloría del Municipio Páez: portal público, formulario de denuncias y dos paneles internos (`/oac` y `/admin`).

- **Stack:** Laravel 13, PHP 8.3+ (el contenedor usa 8.4), PostgreSQL 17.
- **Importante:** la base de datos **tiene que ser PostgreSQL**. El esquema usa funciones, triggers, UUID y `unaccent`; SQLite o MySQL no funcionan.
- **Node/npm no hace falta.** Las vistas usan Bootstrap y CSS propios (`public/assets`); el único `@vite` está en `welcome.blade.php`, que no se usa.

Hay dos formas de correrlo. **La Opción A (todo en Docker) es la recomendada**: no hay que instalar PHP ni Postgres.

---

## 0. Requisitos previos

| Herramienta | Para qué | Opción A | Opción B |
|---|---|:-:|:-:|
| Docker + Docker Compose v2 | App y base de datos | ✅ | ✅ (solo la BD) |
| Composer 2 | Instalar las dependencias PHP (`vendor/`) | ✅ | ✅ |
| PHP 8.3+ con extensiones `pdo_pgsql`, `pgsql`, `gd`, `mbstring`, `xml`, `curl`, `zip` | Correr `artisan` en tu máquina | ❌ | ✅ |
| Git | Clonar el repo | ✅ | ✅ |

Verifica que los tienes:

```bash
docker --version
docker compose version
composer --version
git --version
```

Si Docker está instalado pero da `failed to connect to the docker API`, el servicio está apagado:

```bash
sudo systemctl start docker          # Linux
sudo usermod -aG docker "$USER"      # una sola vez; luego cierra sesión y vuelve a entrar
```

En Windows/macOS abre **Docker Desktop** y espera a que diga "Running".

---

## Opción A — Todo en Docker (recomendada)

### A.1 Entra a la carpeta del proyecto

```bash
cd cgp-laravel
```

Todos los comandos siguientes se ejecutan **desde `cgp-laravel/`** (ahí está `docker-compose.yml`).

### A.2 Crea el archivo `.env`

```bash
cp .env.example .env
```

Los valores por defecto ya coinciden con `docker-compose.yml` (BD `cgp`, usuario `cgp`, clave `cgp`, host `db`). No hay que tocar nada. `APP_KEY` queda vacío a propósito: se genera en A.5.

> Si ya existe un `.env`, no lo pises. Revisa que tenga `DB_CONNECTION=pgsql` y `DB_HOST=db`.

### A.3 Instala las dependencias PHP (`vendor/`)

La imagen Docker **no trae Composer**, y la carpeta del proyecto se monta dentro del contenedor, así que `vendor/` debe existir en tu máquina antes de levantar.

Con Composer instalado:

```bash
composer install --no-interaction --prefer-dist
```

Si no tienes PHP/Composer, usa el contenedor oficial de Composer (no instala nada en tu sistema):

```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD":/app -w /app composer:2 \
  install --no-interaction --prefer-dist --ignore-platform-reqs
```

Termina cuando aparece `Generating optimized autoload files`.

### A.4 Levanta los contenedores

```bash
docker compose up -d --build
```

La primera vez tarda varios minutos (descarga `php:8.4-cli` y `postgres:17` y compila las extensiones). Se crean dos contenedores:

| Contenedor | Qué es | Puerto en tu máquina |
|---|---|---|
| `cgp-laravel-app` | Laravel (`php artisan serve`) | **8001** → 8000 |
| `cgp-postgres` | PostgreSQL 17 | 5432 |

**Qué pasa con la BD:** en el *primer arranque* Postgres ejecuta solo `database/sql/db.sql` (crea tablas, catálogos, módulos, funciones y triggers). Si ese volumen ya existe, no lo repite.

Confirma que ambos están arriba:

```bash
docker compose ps
```

Deben salir `cgp-laravel-app` y `cgp-postgres` en estado `running`. Si el de Postgres se reinicia en bucle, mira `docker compose logs db`.

### A.5 Genera la clave de la aplicación

```bash
docker compose exec app php artisan key:generate
```

Escribe `APP_KEY=base64:...` en tu `.env`. Sin ella, cualquier página da error 500 ("No application encryption key").

### A.6 Aplica las migraciones

```bash
docker compose exec app php artisan migrate
```

Verás una lista de migraciones en `DONE`. La primera (`create_cgp_schema`) no hace nada si la BD ya se creó con `db.sql`; las demás ajustan el esquema (formulario público, panel, permisos, límite de 3500 caracteres, imagen del CMS) y crean el cargo `Jefe de OAC`.

> Si dice `could not translate host name "db"`, el contenedor de Postgres no está arriba o `.env` tiene otro `DB_HOST`.

### A.7 Crea el primer usuario administrador

La base de datos **nace sin ningún usuario**, y para crear usuarios desde el panel hay que haber iniciado sesión. Por eso el primero se crea por consola. Sigue [`CREAR_USUARIO.md`](CREAR_USUARIO.md), sección 1.

### A.8 Abre el sistema

| URL | Qué es |
|---|---|
| <http://localhost:8001/> | Portada pública |
| <http://localhost:8001/denuncias> | Formulario de denuncias, quejas y reclamos |
| <http://localhost:8001/contraloria-escolar> | Contraloría escolar |
| <http://localhost:8001/oac/login> | Panel de la Oficina de Atención al Ciudadano |
| <http://localhost:8001/admin/login> | Panel de Administración (usuarios, accesos, portal web, monitoreo) |

### A.9 Apagar y volver a encender

```bash
docker compose stop        # apaga, conserva los datos
docker compose start       # enciende de nuevo (no hace falta --build)
docker compose down        # borra los contenedores, CONSERVA la base de datos (volumen)
docker compose down -v     # ⚠ borra también la BD: pierdes usuarios, denuncias y contenido
```

---

## Opción B — PHP en tu máquina, solo la BD en Docker

Útil si quieres editar y depurar con tu PHP local.

```bash
cd cgp-laravel
cp .env.example .env
composer install
```

1. Edita `.env` y cambia **solo** el host de la BD (Postgres publica el 5432 en tu máquina):

   ```dotenv
   DB_HOST=127.0.0.1
   APP_URL=http://127.0.0.1:8000
   ```

2. Levanta **solo** la base de datos:

   ```bash
   docker compose up -d db
   ```

3. Espera unos segundos a que acepte conexiones y comprueba:

   ```bash
   docker compose exec db pg_isready -U cgp -d cgp
   ```

   Debe responder `accepting connections`.

4. Clave, migraciones y servidor:

   ```bash
   php artisan key:generate
   php artisan migrate
   php artisan serve
   ```

5. Abre <http://127.0.0.1:8000/>. Las rutas son las mismas de la tabla A.8, con ese host y puerto.

6. Crea el administrador con [`CREAR_USUARIO.md`](CREAR_USUARIO.md), usando `php artisan tinker` (sin `docker compose exec app`).

**Permisos de `storage/`:** si en algún momento corriste comandos con `docker compose exec app`, los archivos que Laravel crea (`storage/app/private/cms`, `evidencias`, logs) pertenecen a `root` y tu PHP local no podrá escribir. Corrígelo con:

```bash
sudo chown -R "$USER":"$USER" storage bootstrap/cache
```

---

## Comandos de uso diario

Con Opción A antepón `docker compose exec app` a cada `php artisan ...`.

```bash
docker compose logs -f app                       # ver errores en vivo
docker compose exec app php artisan route:list   # todas las rutas
docker compose exec app php artisan migrate:status
docker compose exec app php artisan optimize:clear   # limpia caché de config/rutas/vistas
docker compose exec db psql -U cgp -d cgp        # consola SQL (\dt lista tablas, \q sale)
```

Las vistas, rutas y controladores se recargan solos al guardar (el código está montado como volumen). Tras cambiar el `.env` ejecuta `optimize:clear`.

### Pruebas automáticas

Usan una BD aparte llamada `cgp_test` (nunca tocan los datos reales). Créala una vez:

```bash
docker compose exec db createdb -U cgp cgp_test
docker compose exec app php artisan test
```

---

## Problemas frecuentes

| Síntoma | Causa | Solución |
|---|---|---|
| `failed to connect to the docker API` | Docker apagado | `sudo systemctl start docker` |
| `Failed opening required '.../vendor/autoload.php'` | Falta `vendor/` | Paso A.3 |
| Error 500 "No application encryption key has been specified" | Falta `APP_KEY` | Paso A.5 |
| `SQLSTATE[08006] ... could not translate host name "db"` | BD apagada, o corres PHP local con `DB_HOST=db` | `docker compose up -d db`; en Opción B pon `DB_HOST=127.0.0.1` |
| `SQLSTATE[08006] ... password authentication failed` | El volumen de Postgres se creó con otra clave | Iguala `.env` con `docker-compose.yml`, o `docker compose down -v` (⚠ borra la BD) |
| `relation "staff_user" does not exist` | Postgres arrancó con el volumen vacío pero sin `db.sql`, o no migraste | `docker compose exec app php artisan migrate` |
| `Port is already allocated` (5432 u 8001) | Otro Postgres/servicio usa el puerto | Cámbialo en `docker-compose.yml` (`"5433:5432"`, `"8002:8000"`) y ajusta la URL |
| `Permission denied` al subir imagen/evidencia | `storage/` es de `root` | `sudo chown -R "$USER":"$USER" storage bootstrap/cache` |
| Cambié `.env` y no se nota | Configuración en caché | `php artisan optimize:clear` |
| Subida de archivos falla (>10 MB) | Límites de PHP | El `Dockerfile` ya fija 12 MB por archivo y 64 MB por envío; las evidencias aceptan hasta 10 MB c/u |
