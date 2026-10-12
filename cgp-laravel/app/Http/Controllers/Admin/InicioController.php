<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InicioController extends Controller
{
    public function index(Request $request)
    {
        $cms = $request->user()->puede('CMS');

        return view('admin.inicio', [
            'r' => $request->user()->puede('STATS') ? MonitoreoController::resumen() : null,
            'publicados' => $cms ? DB::table('cms_content')->where('published', true)->count() : null,
            'borradores' => $cms ? DB::table('cms_content')->where('published', false)->count() : null,
            // Bitácora: últimas ediciones del contenido web.
            'actividad' => $cms ? DB::table('cms_content as c')
                ->join('staff_user as s', 's.id', '=', 'c.author_id')
                ->orderByDesc('c.updated_at')->limit(4)
                ->get(['c.id', 'c.title', 'c.published', 'c.updated_at', DB::raw("s.first_name || ' ' || s.last_name as autor")]) : collect(),
        ]);
    }
}
