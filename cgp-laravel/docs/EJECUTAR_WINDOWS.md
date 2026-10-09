# Instalar el sistema en Windows (guía fácil)

Solo tienes que **copiar cada bloque gris, pegarlo en la ventana azul de PowerShell y presionar Enter**. Espera a que termine antes de pasar al siguiente.

**Antes de empezar, necesitas:**
- Windows 10 u 11 versión **Pro**, **Enterprise** o **Education** (no *Home*).
- Conexión a internet y unos 30 minutos la primera vez.

> ¿No sabes tu versión? Haz el paso 1 y mira el resultado de la primera línea: si dice *Home*, esta guía no sirve para tu equipo.

---

## Paso 1. Abre PowerShell como administrador

1. Presiona la tecla **Windows** del teclado.
2. Escribe `PowerShell`.
3. Clic derecho sobre **Windows PowerShell** → **Ejecutar como administrador** → **Sí**.

Se abre una ventana azul. Pega esto:

```powershell
(Get-CimInstance Win32_OperatingSystem).Caption
```

Debe aparecer algo como *Microsoft Windows 11 Pro*. Si dice *Home*, no sigas.

*(Para pegar en PowerShell: clic derecho dentro de la ventana.)*

## Paso 2. Prepara Windows y reinicia

```powershell
Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Hyper-V -All -NoRestart
Restart-Computer
```

El equipo se reinicia solo. Cuando vuelva, repite el **Paso 1** (abrir PowerShell como administrador).

## Paso 3. Instala los programas

```powershell
winget install -e --id Git.Git --accept-package-agreements --accept-source-agreements
Invoke-WebRequest "https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe" -OutFile "$env:TEMP\DockerInstaller.exe"
Start-Process "$env:TEMP\DockerInstaller.exe" -Wait -ArgumentList "install","--quiet","--accept-license","--backend=hyper-v","--always-run-service"
net localgroup docker-users $env:USERNAME /add
```

Tarda varios minutos y no muestra mucho: es normal. Cuando vuelva a aparecer la línea para escribir, ejecuta:

```powershell
logoff
```

Tu sesión de Windows se cierra. **Vuelve a entrar** con tu usuario y contraseña.

## Paso 4. Enciende Docker

Abre PowerShell normal (esta vez **no** hace falta administrador) y pega:

```powershell
Start-Process "C:\Program Files\Docker\Docker\Docker Desktop.exe"
while (-not (docker info 2>$null)) { Start-Sleep 5 }
```

Espera hasta que vuelva a aparecer la línea para escribir (1 o 2 minutos). Es la señal de que Docker ya está listo.

## Paso 5. Descarga el sistema

```powershell
mkdir $HOME\proyectos
cd $HOME\proyectos
git clone https://github.com/maikCyphlock/CGP.git
cd CGP\cgp-laravel
copy .env.example .env
docker run --rm -v "${PWD}:/app" -w /app composer:2 install --no-interaction --prefer-dist --ignore-platform-reqs
```

> Si `git` no se reconoce, cierra PowerShell, ábrelo de nuevo y repite desde la línea `mkdir`.

## Paso 6. Enciende el sistema

```powershell
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

La primera vez tarda varios minutos. Al final de `migrate` verás una lista de líneas con la palabra **DONE**. Eso significa que todo salió bien.

## Paso 7. Crea tu usuario administrador

Pega esto para entrar a la consola del sistema:

```powershell
docker compose exec app php artisan tinker
```

Aparece un `>`. **Antes de pegar el siguiente bloque**, cámbiale tus datos en un Bloc de notas:
- `Nombre` y `Apellido`: los tuyos.
- `12345678`: tu cédula.
- `admin@cgp.test`: tu correo, **en minúsculas**. Con este correo vas a entrar.
- `CambiaEstaClave123`: tu contraseña (mínimo 8 caracteres).

Mantén las comillas `'` tal como están. Luego copia el bloque ya editado y pégalo:

```php
use App\Models\StaffUser; use Illuminate\Support\Facades\DB;
$cargo = DB::table('job_position')->where('title', 'Administrador del sistema')->value('id') ?? DB::table('job_position')->insertGetId(['title' => 'Administrador del sistema', 'description' => 'Acceso total al sistema']);
$u = StaffUser::create(['job_position_id' => $cargo, 'id_doc_type_id' => DB::table('id_document_type')->where('code', 'V')->value('id'), 'id_doc_number' => '12345678', 'first_name' => 'Nombre', 'last_name' => 'Apellido', 'email' => 'admin@cgp.test', 'password_hash' => 'CambiaEstaClave123']);
DB::statement('INSERT INTO staff_privilege (user_id, module_id, can_read, can_write, can_delete, granted_by) SELECT ?, id, TRUE, TRUE, TRUE, ? FROM app_module', [$u->id, $u->id]);
echo "Listo: {$u->email}\n";
```

Debe aparecer `Listo:` con tu correo. Si aparece un error, ve a [`CREAR_USUARIO.md`](CREAR_USUARIO.md), sección 1.5. Para salir de la consola escribe:

```
exit
```

## Paso 8. ¡Listo! Entra al sistema

Abre el navegador y entra a:

- **Administración:** <http://localhost:8001/admin/login>
- **Oficina de Atención al Ciudadano:** <http://localhost:8001/oac/login>
- **Página pública:** <http://localhost:8001/>

Entra con el correo y la contraseña del paso 7.

---

## Después: apagar y encender

Cada vez que reinicies la computadora:

1. Abre PowerShell y pega:

   ```powershell
   Start-Process "C:\Program Files\Docker\Docker\Docker Desktop.exe"
   while (-not (docker info 2>$null)) { Start-Sleep 5 }
   ```

2. Espera a que termine. El sistema ya funciona en <http://localhost:8001/>.

Para apagarlo del todo:

```powershell
cd $HOME\proyectos\CGP\cgp-laravel
docker compose stop
```

Para encenderlo de nuevo: `docker compose start`.

> ⚠ **Nunca** uses `docker compose down -v`: borra toda la información (usuarios y denuncias).

---

## Si algo sale mal

| Qué ves | Qué hacer |
|---|---|
| *No se reconoce el término `docker`* o `git` | Cierra la ventana, abre PowerShell nuevo. Si sigue, reinicia la computadora |
| *error during connect* / *cannot find the file specified* | Docker no está encendido: repite el **Paso 4** |
| *Access denied* al usar `docker` | Repite el paso 3 desde `net localgroup...` y luego `logoff` |
| El error menciona *virtualization* | Hay que activar la **virtualización** en la BIOS de tu equipo (pide ayuda a quien te da soporte técnico) |
| *port is already allocated* | Otro programa usa el mismo puerto. Reinicia la computadora y vuelve a intentar |
| La página no abre | En PowerShell, dentro de `cgp-laravel`, ejecuta `docker compose ps`: deben aparecer dos líneas con *running*. Si no, `docker compose up -d` |
| Cualquier otro error | Copia el mensaje completo y envíaselo a quien te dio esta guía |
