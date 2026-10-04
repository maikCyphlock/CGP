<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\Admin\ContenidoController;
use App\Http\Controllers\Admin\InicioController;
use App\Http\Controllers\Admin\MonitoreoController;
use App\Http\Controllers\Oac\ExpedienteController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\DenunciaController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::get('/', function () {
    // Contenido publicado desde el CMS; si no hay, la portada usa su texto fijo.
    $cms = DB::table('cms_content as c')
        ->join('cms_content_type as t', 't.id', '=', 'c.content_type_id')
        ->where('c.published', true)
        ->orderByDesc('c.published_at')
        ->get(['t.code', 'c.title', 'c.body', 'c.published_at', 'c.image_path'])
        ->groupBy('code');

    // El cuerpo se guarda como Markdown; se descarta el HTML crudo y los enlaces peligrosos.
    $md = ['html_input' => 'strip', 'allow_unsafe_links' => false, 'renderer' => ['soft_break' => "<br>\n"]];
    $html = fn ($c) => $c ? tap($c, fn ($c) => $c->html = Str::markdown($c->body, $md)) : null;

    return view('home', [
        'noticias' => $cms->get('NEWS', collect())->take(6)->map($html),
        'mision' => $html($cms->get('MISSION')?->first()),
        'vision' => $html($cms->get('VISION')?->first()),
    ]);
});

// Imágenes del CMS: solo las que están en uso por un contenido publicado.
Route::get('/media/cms/{archivo}', function (string $archivo) {
    abort_unless(DB::table('cms_content')->where('image_path', $archivo)->where('published', true)->exists(), 404);

    return Storage::response("cms/$archivo", null, ['Cache-Control' => 'public, max-age=86400']);
})->where('archivo', '[A-Za-z0-9]{20,60}\.(jpg|jpeg|png|webp)')->name('media.cms');

Route::get('/denuncias', [DenunciaController::class, 'create'])->name('denuncias.create');
Route::post('/denuncias', [DenunciaController::class, 'store'])->name('denuncias.store');
Route::get('/denuncias/{trackingCode}/planilla', [DenunciaController::class, 'planilla'])->name('denuncias.planilla');

Route::get('/contraloria-escolar', function () {
    return view('contraloria_escolar');
});

// Dos backoffice separados, cada uno con su URL, su login y su inicio:
//   /oac   → Oficina de Atención al Ciudadano (denuncias, quejas y reclamos)
//   /admin → Administración del sistema (accesos, usuarios, portal web y monitoreo)
// La misma cuenta puede entrar a ambos si tiene privilegios en los dos.
foreach (['oac', 'admin'] as $mod) {
    Route::middleware('guest')->prefix($mod)->group(function () use ($mod) {
        Route::get('/login', [AuthController::class, 'create'])->name("$mod.login");
        Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:20,1');
    });
    Route::post("/$mod/logout", [AuthController::class, 'destroy'])->middleware('auth')->name("$mod.logout");
}

// ───────────── OAC ─────────────
Route::middleware(['auth', 'modulo:oac'])->prefix('oac')->name('oac.')->group(function () {
    Route::get('/', [ExpedienteController::class, 'inicio'])->name('inicio');

    Route::middleware('permiso:CASES,read')->group(function () {
        Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show'])->whereUuid('expediente')->name('expedientes.show');
        Route::get('/expedientes/{expediente}/archivos/{archivo}', [ExpedienteController::class, 'archivo'])->whereUuid(['expediente', 'archivo'])->name('expedientes.archivo');
    });
    Route::post('/expedientes/{expediente}/actuar', [ExpedienteController::class, 'actuar'])->whereUuid('expediente')->middleware('permiso:CASES,write')->name('expedientes.actuar');
    Route::post('/expedientes/{expediente}/clasificar', [ExpedienteController::class, 'clasificar'])->whereUuid('expediente')->middleware('permiso:CLASSIFY,write')->name('expedientes.clasificar');

    // Catálogos de la OAC (tipos de trámite, irregularidades, unidades de derivación…)
    Route::middleware('permiso:CATALOGS,read')->group(function () {
        Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');
        Route::get('/catalogos/{slug}', [CatalogoController::class, 'show'])->name('catalogos.show');
    });
    Route::middleware('permiso:CATALOGS,write')->group(function () {
        Route::post('/catalogos/{slug}', [CatalogoController::class, 'store'])->name('catalogos.store');
        Route::post('/catalogos/{slug}/{id}', [CatalogoController::class, 'update'])->whereNumber('id')->name('catalogos.update');
    });
});

// ───────────── Administración ─────────────
Route::middleware(['auth', 'modulo:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [InicioController::class, 'index'])->name('inicio');

    // Monitoreo del sistema
    Route::middleware('permiso:STATS,read')->group(function () {
        Route::get('/monitoreo', [MonitoreoController::class, 'index'])->name('monitoreo');
    });
    Route::middleware('permiso:ACCESS,write')->group(function () {
        Route::post('/monitoreo/sesiones/{id}/cerrar', [MonitoreoController::class, 'cerrarSesion'])->name('monitoreo.cerrar');
        Route::post('/monitoreo/usuarios/{usuario}/desbloquear', [MonitoreoController::class, 'desbloquear'])->whereUuid('usuario')->name('monitoreo.desbloquear');
    });

    // Usuarios y accesos
    Route::middleware('permiso:USERS,read')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/nuevo', [UsuarioController::class, 'create'])->middleware('permiso:USERS,write')->name('usuarios.create');
        Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show'])->name('usuarios.show');
    });
    Route::middleware('permiso:USERS,write')->group(function () {
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::post('/usuarios/{usuario}/estado', [UsuarioController::class, 'estado'])->name('usuarios.estado');
    });
    Route::post('/usuarios/{usuario}/permisos', [UsuarioController::class, 'permisos'])->middleware('permiso:ACCESS,write')->name('usuarios.permisos');

    // Cargos del personal (los administra quien gestiona usuarios)
    Route::middleware('permiso:USERS,read')->group(function () {
        Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');
        Route::get('/catalogos/{slug}', [CatalogoController::class, 'show'])->name('catalogos.show');
    });
    Route::middleware('permiso:USERS,write')->group(function () {
        Route::post('/catalogos/{slug}', [CatalogoController::class, 'store'])->name('catalogos.store');
        Route::post('/catalogos/{slug}/{id}', [CatalogoController::class, 'update'])->whereNumber('id')->name('catalogos.update');
    });

    // Contenido de la página web
    Route::middleware('permiso:CMS,read')->group(function () {
        Route::get('/contenidos', [ContenidoController::class, 'index'])->name('contenidos.index');
        Route::get('/contenidos/nuevo', [ContenidoController::class, 'create'])->middleware('permiso:CMS,write')->name('contenidos.create');
        Route::get('/contenidos/imagen/{archivo}', [ContenidoController::class, 'imagen'])->where('archivo', '[A-Za-z0-9]{20,60}\.(jpg|jpeg|png|webp)')->name('contenidos.imagen');
        Route::get('/contenidos/{id}', [ContenidoController::class, 'edit'])->name('contenidos.edit');
    });
    Route::middleware('permiso:CMS,write')->group(function () {
        Route::post('/contenidos', [ContenidoController::class, 'store'])->name('contenidos.store');
        Route::post('/contenidos/{id}', [ContenidoController::class, 'update'])->name('contenidos.update');
    });
});
