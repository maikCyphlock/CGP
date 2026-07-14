<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/denuncias', function () {
    return view('denuncias');
});

Route::get('/contraloria-escolar', function () {
    return view('contraloria_escolar');
});
