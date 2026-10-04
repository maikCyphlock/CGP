<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InicioController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.inicio', [
            'r' => $request->user()->puede('STATS') ? MonitoreoController::resumen() : null,
        ]);
    }
}
