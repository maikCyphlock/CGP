<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CatalogoController;
use App\Http\Controllers\Admin\ContenidoController;
use App\Http\Controllers\Admin\ExpedienteController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\DenunciaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/denuncias', [DenunciaController::class, 'create'])->name('denuncias.create');
Route::post('/denuncias', [DenunciaController::class, 'store'])->name('denuncias.store');
Route::get('/denuncias/{trackingCode}/planilla', [DenunciaController::class, 'planilla'])->name('denuncias.planilla');

Route::get('/contraloria-escolar', function () {
    return view('contraloria_escolar');
});

// Panel administrativo (OAC)
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->middleware('throttle:20,1');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', [ExpedienteController::class, 'inicio'])->name('inicio');

    Route::middleware('permiso:CASES,read')->group(function () {
        Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show'])->whereUuid('expediente')->name('expedientes.show');
        Route::get('/expedientes/{expediente}/archivos/{archivo}', [ExpedienteController::class, 'archivo'])->whereUuid(['expediente', 'archivo'])->name('expedientes.archivo');
    });
    Route::post('/expedientes/{expediente}/actuar', [ExpedienteController::class, 'actuar'])->whereUuid('expediente')->middleware('permiso:CASES,write')->name('expedientes.actuar');
    Route::post('/expedientes/{expediente}/clasificar', [ExpedienteController::class, 'clasificar'])->whereUuid('expediente')->middleware('permiso:CLASSIFY,write')->name('expedientes.clasificar');

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

    // Catálogos
    Route::middleware('permiso:CATALOGS,read')->group(function () {
        Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');
        Route::get('/catalogos/{slug}', [CatalogoController::class, 'show'])->name('catalogos.show');
    });
    Route::middleware('permiso:CATALOGS,write')->group(function () {
        Route::post('/catalogos/{slug}', [CatalogoController::class, 'store'])->name('catalogos.store');
        Route::post('/catalogos/{slug}/{id}', [CatalogoController::class, 'update'])->whereNumber('id')->name('catalogos.update');
    });

    // Contenido de la página web
    Route::middleware('permiso:CMS,read')->group(function () {
        Route::get('/contenidos', [ContenidoController::class, 'index'])->name('contenidos.index');
        Route::get('/contenidos/nuevo', [ContenidoController::class, 'create'])->middleware('permiso:CMS,write')->name('contenidos.create');
        Route::get('/contenidos/{id}', [ContenidoController::class, 'edit'])->name('contenidos.edit');
    });
    Route::middleware('permiso:CMS,write')->group(function () {
        Route::post('/contenidos', [ContenidoController::class, 'store'])->name('contenidos.store');
        Route::post('/contenidos/{id}', [ContenidoController::class, 'update'])->name('contenidos.update');
    });
});
