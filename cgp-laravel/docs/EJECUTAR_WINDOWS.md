# Instalar el sistema en Windows (un solo script)

Un único script de PowerShell hace **todo**: activa Hyper-V, instala Git y Docker Desktop (sin WSL), descarga el sistema, lo configura, lo enciende y crea un usuario administrador.

**Necesitas:**
- Windows 10 u 11 **Pro, Enterprise o Education** (no *Home*).
- Internet y unos 30 minutos la primera vez.

---

## Paso 1. Abre PowerShell

Tecla **Windows** → escribe `PowerShell` → **Enter**. (No hace falta que sea administrador: el script lo pide solo.)

## Paso 2. Pega esto y presiona Enter

Pega con **clic derecho** dentro de la ventana:

```powershell
Set-ExecutionPolicy -Scope Process Bypass -Force
irm https://raw.githubusercontent.com/maikCyphlock/CGP/main/cgp-laravel/instalar-windows.ps1 -OutFile $HOME\instalar-cgp.ps1
& $HOME\instalar-cgp.ps1
```

Aparece una ventana pidiendo permisos de administrador: pulsa **Sí**. Se abre una ventana azul que va mostrando los pasos.

## Paso 3. Si pide reiniciar, reinicia y repite

La primera vez Windows necesita reiniciarse para activar Hyper-V. El script avisa: responde `S`. Cuando la computadora vuelva, **repite el Paso 1 y el Paso 2**. El script salta lo que ya está hecho y sigue donde se quedó.

## Paso 4. Espera el mensaje verde

Al terminar, el script abre el navegador y muestra:

```
Administracion : http://localhost:8001/admin/login
Oficina (OAC)  : http://localhost:8001/oac/login

Usuario : admin@admin.com
Clave   : admin
```

Entra con ese usuario y esa clave.

> **El usuario se llama `admin@admin.com`** (no solo `admin`): el sistema pide que el usuario sea un correo.
>
> ⚠ La clave `admin` es solo para pruebas. Si el sistema es real, crea tu propio administrador (ver [`CREAR_USUARIO.md`](CREAR_USUARIO.md)) y desactiva el de prueba.

---

## Cambiar valores (opcional)

Todo se puede personalizar con parámetros. Ejemplo (en el Paso 2, cambia la última línea):

```powershell
& $HOME\instalar-cgp.ps1 -Puerto 8080 -Correo jefe@cgp.gob -Clave "OtraClave123"
```

| Parámetro | Qué es | Por defecto |
|---|---|---|
| `-Carpeta` | Dónde se descarga el sistema | `C:\Users\TU_USUARIO\proyectos` |
| `-Puerto` | Puerto de la página en tu equipo | `8001` |
| `-Correo` | Usuario administrador inicial | `admin@admin.com` |
| `-Clave` | Contraseña del administrador inicial | `admin` |

---

## Uso diario

Cada vez que reinicies la computadora, abre **Docker Desktop** (menú Inicio) y espera a que diga *Engine running*. El sistema se enciende solo en <http://localhost:8001/>.

Para apagarlo o encenderlo a mano, en PowerShell:

```powershell
cd $HOME\proyectos\CGP\cgp-laravel
docker compose stop     # apagar
docker compose start    # encender
```

> ⚠ **Nunca** uses `docker compose down -v`: borra toda la información (usuarios y denuncias).

Para actualizar el sistema a la última versión, repite los Pasos 1 y 2: el script descarga los cambios y vuelve a configurar sin borrar datos.

---

## Si algo sale mal

El script se detiene y muestra un mensaje en **rojo** que dice qué pasó. Los más comunes:

| Mensaje | Qué hacer |
|---|---|
| *edición Home* | Esta guía no sirve para Windows Home |
| *Docker no arrancó* | Activa la **virtualización** en la BIOS (pide ayuda a soporte técnico) y ejecuta el script otra vez |
| *Falta instalar Git* | Instálalo de <https://git-scm.com/download/win> y ejecuta el script otra vez |
| *port is already allocated* / *Ports are not available* | Otro programa usa el puerto: ejecuta con otro, p. ej. `-Puerto 8080` |
| *Access denied* con `docker` | Cierra sesión en Windows, vuelve a entrar y ejecuta el script |
| Cualquier otro | Copia el mensaje completo y envíaselo a quien te dio el script |

Para ver qué pasa por dentro: `docker compose logs -f app` (dentro de la carpeta `cgp-laravel`).
