<?php

namespace App\Http\Controllers\Oac;

use App\Http\Controllers\Controller;
use App\Models\MensajeEstado;
use App\Support\DacEstados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Permite personalizar los mensajes informativos sin costo que ve el ciudadano
 * al consultar el estado de su trámite en el portal público.
 */
class MensajesEstadoController extends Controller
{
    public function index()
    {
        $estados = DB::table('case_status')
            ->orderBy('sort_order')
            ->get();

        $mensajes = MensajeEstado::with('actualizadoPor')
            ->get()
            ->keyBy('estado_codigo');

        return view('oac.mensajes_estado', [
            'estados' => $estados,
            'mensajes' => $mensajes,
            'descripciones' => DacEstados::DESCRIPCIONES,
            'porDefecto' => DacEstados::MENSAJES_POR_DEFECTO,
        ]);
    }

    public function update(Request $request, string $codigo)
    {
        $estado = DB::table('case_status')->where('code', $codigo)->first();
        abort_unless($estado, 404);

        $d = $request->validate([
            'titulo' => 'nullable|string|max:120',
            'mensaje' => 'required|string|min:5|max:2000',
            'activo' => 'nullable|boolean',
        ], [
            'mensaje.required' => 'El mensaje para el ciudadano es obligatorio.',
            'mensaje.min' => 'El mensaje debe tener al menos 5 caracteres.',
            'mensaje.max' => 'El mensaje no puede superar 2000 caracteres.',
            'titulo.max' => 'El título no puede superar 120 caracteres.',
        ]);

        MensajeEstado::updateOrCreate(
            ['estado_codigo' => $codigo],
            [
                'titulo' => filled($d['titulo'] ?? null) ? trim($d['titulo']) : null,
                'mensaje' => trim($d['mensaje']),
                'activo' => $request->boolean('activo'),
                'actualizado_por' => $request->user()->id,
            ]
        );

        return redirect()
            ->route('oac.mensajes_estado.index')
            ->with('ok', "Mensaje para el estado «{$estado->name}» actualizado.");
    }
}
