# Cómo crear usuarios (paso a paso)

Los usuarios del panel (personal de la Contraloría) viven en la tabla `staff_user`. **No hay registro público**: cada cuenta la crea un administrador.

Hay un problema de "el huevo y la gallina": para crear usuarios desde el panel hay que haber iniciado sesión, y la base de datos nace **sin ninguna cuenta**. Por eso:

1. El **primer administrador** se crea por consola (sección 1, una sola vez).
2. Todos los demás se crean **desde el panel** (sección 2).

> Requisito: el sistema ya debe estar corriendo con las migraciones aplicadas. Si no, sigue primero [`EJECUTAR.md`](EJECUTAR.md).

---

## 1. Crear el primer administrador (por consola)

> En Windows, el script de [`EJECUTAR_WINDOWS.md`](EJECUTAR_WINDOWS.md) ya crea el administrador `admin@admin.com` / `admin`. Para otro correo o clave: `php artisan cgp:admin --defecto --correo tu@correo --clave TuClave`, o sin opciones para que pregunte.

### 1.1 Ejecuta el comando

Desde `cgp-laravel/`. Con Docker (Opción A):

```bash
docker compose exec app php artisan cgp:admin
```

Con PHP local (Opción B):

```bash
php artisan cgp:admin
```

### 1.2 Responde las preguntas

| Pregunta | Reglas |
|---|---|
| Nombres / Apellidos | Hasta 80 caracteres cada uno |
| Cédula | Solo números, máx. 12 caracteres, única |
| Correo | Es el usuario de login. Se guarda en minúsculas; único |
| Contraseña / Repita la contraseña | Mínimo 8 caracteres; no se ve al escribir |

El comando:

1. Crea el cargo **Administrador del sistema** si no existe (todo usuario necesita un cargo).
2. Crea el usuario con cédula tipo `V` (venezolana).
3. Le da **lectura, escritura y borrado en los 8 módulos** (expedientes, clasificación, usuarios, accesos, estadísticas, página web, catálogos y reportes). Con eso entra a `/oac` y a `/admin`.

Termina con `Listo. Ya puede entrar con tu@correo`. Si hay un error, lo muestra y no crea nada: corrige y vuelve a ejecutarlo.

### 1.3 Errores típicos

| Mensaje | Qué hacer |
|---|---|
| `Ya existe un usuario con ese correo` / `con esa cédula` | Usa otro, o resetea la clave del existente (sección 4) |
| `Las contraseñas no coinciden` | Vuelve a ejecutar y escríbela igual las dos veces |
| `The password field must be at least 8 characters` | Usa 8 o más caracteres |
| `relation "staff_user" does not exist` | Sin esquema: aplica las migraciones ([`EJECUTAR.md`](EJECUTAR.md), A.6) |
| `Command "cgp:admin" is not defined` | Código desactualizado: `git pull` y `php artisan optimize:clear` |

### 1.4 Inicia sesión

Abre <http://localhost:8001/admin/login> (Opción B: <http://127.0.0.1:8000/admin/login>) e ingresa el correo y la clave que escribiste.

Entras al panel de Administración. Desde ahí puedes pasar a la OAC en <http://localhost:8001/oac/login> (la sesión es la misma cuenta).

---

## 2. Crear usuarios desde el panel (uso normal)

Requiere estar logueado como un usuario con permiso de **escritura** en el módulo *Gestión de Usuarios* (`USERS`). El administrador del punto 1 lo tiene.

### 2.1 (Si hace falta) Crear el cargo

Cada usuario pertenece a un cargo (Jefe de OAC, Analista, etc.). Si el que necesitas no existe:

1. En el menú lateral entra a **Cargos**, o ve a <http://localhost:8001/admin/catalogos/cargos>.
2. Agrega el título (y descripción). Queda activo.

Un cargo con personal activo **no se puede desactivar** (lo impide un trigger de la BD).

### 2.2 Crear el usuario

1. Ve a <http://localhost:8001/admin/usuarios> y pulsa **Nuevo usuario** (o entra directo a <http://localhost:8001/admin/usuarios/nuevo>).
2. Llena el formulario:

   | Campo | Notas |
   |---|---|
   | Nombres / Apellidos | Hasta 80 caracteres cada uno |
   | Tipo de cédula | `V` (venezolana) o `E` (extranjera) |
   | Cédula | Hasta 12 caracteres; la pareja tipo + número debe ser única |
   | Correo | Es el usuario de login. Se guarda en minúsculas; `Pedro@X.com` y `pedro@x.com` cuentan como el mismo |
   | Cargo | Lista de cargos activos |
   | Contraseña y confirmación | Mínimo 8 caracteres; deben coincidir |

3. Pulsa **Crear usuario**. Te lleva a la ficha del usuario con el mensaje *"Usuario creado. Ahora asígnele los permisos que necesite."*

### 2.3 Asignar permisos (obligatorio)

**Un usuario recién creado no tiene ningún permiso** y no puede entrar a ningún panel (verá *"Su cuenta no tiene acceso a ningún módulo"*).

En su ficha (`/admin/usuarios/{id}`) marca, por módulo, qué puede hacer y pulsa **Guardar permisos**:

| Módulo | Código | Panel donde da acceso |
|---|---|---|
| Gestión de Expedientes | `CASES` | OAC (`/oac`) |
| Clasificación y Derivación | `CLASSIFY` | OAC |
| Gestión de Catálogos | `CATALOGS` | OAC |
| Gestión de Usuarios | `USERS` | Administración (`/admin`) |
| Gestión de Accesos | `ACCESS` | Administración |
| Criterios Estadísticos / Monitoreo | `STATS` | Administración |
| Gestión de Página Web | `CMS` | Administración |
| Reportes e Informes | `REPORTS` | Administración |

Reglas:

- Los niveles se incluyen entre sí: **borrar ⊃ escribir ⊃ leer**. Marcar "escribir" marca "leer" solo.
- Con **leer** en cualquier módulo de un panel, el usuario puede entrar a ese panel; los demás permisos limitan qué botones y secciones ve.
- Para cambiar permisos se necesita escritura en `ACCESS`.
- **No puedes cambiar tus propios permisos**: pídeselo a otro administrador. (Por eso conviene tener al menos dos administradores.)
- Cada cambio queda registrado en la auditoría (se ve en la ficha del usuario).
- Los permisos rigen desde la siguiente página que el usuario abra.

### 2.4 Activar/desactivar y cuenta bloqueada

- En la ficha puedes **desactivar** a alguien: ya no puede entrar y pierde la sesión abierta. Nunca se borra el registro.
- No puedes desactivar tu propia cuenta ni al **único** usuario que administra accesos.
- Tras **5 intentos fallidos** de login, la cuenta se bloquea **15 minutos**. Un administrador puede liberarla en <http://localhost:8001/admin/monitoreo> con **Desbloquear** (requiere escritura en `ACCESS`), o reactivar al usuario desde su ficha.

---

## 3. Arreglar un usuario sin permisos (por consola)

Si creaste un usuario y no hay nadie con acceso a `ACCESS` para asignarle permisos (o te quedaste sin administradores), dale acceso total desde Tinker:

```php
use App\Models\StaffUser; use Illuminate\Support\Facades\DB;
$u = StaffUser::withoutGlobalScopes()->where('email', 'admin@cgp.test')->firstOrFail();
$u->forceFill(['active' => true, 'failed_attempts' => 0, 'locked_until' => null])->save();
DB::statement('INSERT INTO staff_privilege (user_id, module_id, can_read, can_write, can_delete, granted_by) SELECT ?, id, TRUE, TRUE, TRUE, ? FROM app_module ON CONFLICT (user_id, module_id) DO UPDATE SET can_read = TRUE, can_write = TRUE, can_delete = TRUE', [$u->id, $u->id]);
echo "Acceso total para {$u->email}\n";
```

(`withoutGlobalScopes()` es necesario porque, por defecto, el modelo oculta a los usuarios desactivados.)

---

## 4. Olvidé la contraseña / resetearla

El login no tiene "recuperar contraseña" por correo: lo hace un administrador.

**Desde el panel:** hoy el formulario no permite editar la clave de un usuario existente, así que se hace por consola.

**Por consola (Tinker):**

```php
use App\Models\StaffUser;
StaffUser::withoutGlobalScopes()->where('email', 'admin@cgp.test')->firstOrFail()->forceFill(['password_hash' => 'NuevaClave123', 'failed_attempts' => 0, 'locked_until' => null, 'active' => true])->save();
```

Escribe la clave en claro; Laravel la cifra. Esto además desbloquea y reactiva la cuenta. Comunícale la clave temporal al usuario por un canal seguro.

---

## 5. Comprobar qué usuarios existen

```bash
docker compose exec db psql -U cgp -d cgp -c "SELECT email, first_name, last_name, active, locked_until FROM staff_user ORDER BY created_at;"
```

Y sus permisos:

```bash
docker compose exec db psql -U cgp -d cgp -c "SELECT u.email, m.code, p.can_read r, p.can_write w, p.can_delete d FROM staff_privilege p JOIN staff_user u ON u.id=p.user_id JOIN app_module m ON m.id=p.module_id ORDER BY u.email, m.code;"
```

---

## 6. Buenas prácticas

- Cambia la clave del administrador inicial apenas entres a producción; no dejes `CambiaEstaClave123` ni correos `@cgp.test`.
- Crea al menos **dos** administradores con `ACCESS`: uno no puede editarse a sí mismo ni quedarse como único administrador.
- Da a cada persona **solo** los módulos que necesita.
- Una cuenta por persona; no compartas contraseñas. Los accesos denegados y fallidos quedan registrados y se ven en **Monitoreo**.
