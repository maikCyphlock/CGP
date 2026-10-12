<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Informes (PDF + imagen de vista previa) de Contraloría Escolar y Gestión Comunitaria. */
class InformeController extends Controller
{
    public const SECCIONES = ['escolar' => 'Contraloría Escolar', 'gestion' => 'Gestión Comunitaria'];
    public const PERIODOS = ['mensual' => 'Mensual', 'trimestral' => 'Trimestral', 'semestral' => 'Semestral', 'anual' => 'Anual'];

    public function index()
    {
        return view('admin.informes', [
            'items' => DB::table('web_report')->orderBy('section')->orderByDesc('year')->orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'section' => 'required|in:'.implode(',', array_keys(self::SECCIONES)),
            'period' => 'required|in:'.implode(',', array_keys(self::PERIODOS)),
            'year' => 'required|integer|between:2000,2100',
            'label' => 'required|string|max:60',
            'pdf' => 'required|file|mimes:pdf|max:20480',
            'imagen' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'required' => 'Falta el campo :attribute.',
            'pdf.mimes' => 'El informe debe ser un PDF.',
            'pdf.max' => 'El PDF pesa más de 20 MB.',
            'imagen.max' => 'La imagen pesa más de 4 MB.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
        ], ['section' => 'sección', 'period' => 'período', 'year' => 'año', 'label' => 'descripción del período']);

        DB::table('web_report')->insert([
            'section' => $d['section'], 'period' => $d['period'], 'year' => $d['year'], 'label' => $d['label'],
            'pdf_path' => basename($request->file('pdf')->store('informes')),
            'image_path' => $request->file('imagen') ? basename($request->file('imagen')->store('informes')) : null,
            'author_id' => $request->user()->id,
        ]);

        return back()->with('ok', 'Informe publicado.');
    }

    public function destroy(int $id)
    {
        $r = DB::table('web_report')->where('id', $id)->first() ?? abort(404);
        DB::table('web_report')->where('id', $id)->delete();
        Storage::delete(array_filter(["informes/$r->pdf_path", $r->image_path ? "informes/$r->image_path" : null]));

        return back()->with('ok', 'Informe eliminado.');
    }

    /** Filas para la tabla pública de una sección, agrupadas por período. */
    public static function publicos(string $seccion): array
    {
        $url = fn ($f) => $f ? route('media.informe', $f) : null;

        return DB::table('web_report')->where('section', $seccion)->orderByDesc('year')->orderBy('created_at')->get()
            ->groupBy('period')
            ->map(fn ($rows) => $rows->map(fn ($r) => ['year' => $r->year, 'month' => $r->label, 'pdf' => $url($r->pdf_path), 'img' => $url($r->image_path)])->values())
            ->toArray() + array_fill_keys(array_keys(self::PERIODOS), []);
    }
}
