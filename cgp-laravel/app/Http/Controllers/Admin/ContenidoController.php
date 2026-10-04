<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Contenido de la página web (noticias, misión, visión…) con historial de versiones. */
class ContenidoController extends Controller
{
    public function index()
    {
        return view('admin.contenidos', [
            'items' => DB::table('cms_content as c')
                ->join('cms_content_type as t', 't.id', '=', 'c.content_type_id')
                ->join('staff_user as s', 's.id', '=', 'c.author_id')
                ->orderByDesc('c.updated_at')
                ->get(['c.id', 'c.title', 'c.published', 'c.updated_at', 't.name as tipo', DB::raw("s.first_name || ' ' || s.last_name as autor")]),
        ]);
    }

    /** Imagen de un contenido, también borradores (solo para quien administra el CMS). */
    public function imagen(string $archivo)
    {
        abort_unless(Storage::exists("cms/$archivo"), 404);

        return Storage::response("cms/$archivo");
    }

    public function create()
    {
        return view('admin.contenido', ['item' => null, 'tipos' => $this->tipos(), 'versiones' => collect()]);
    }

    public function store(Request $request)
    {
        $d = $this->validar($request);
        $id = (string) Str::uuid();

        DB::table('cms_content')->insert([
            'id' => $id, 'author_id' => $request->user()->id, 'image_path' => $this->guardarImagen($request),
            'published_at' => $d['published'] ? now() : null,
        ] + $d);

        return redirect()->route('admin.contenidos.edit', $id)->with('ok', $d['published'] ? 'Contenido creado y publicado.' : 'Contenido guardado como borrador.');
    }

    public function edit(string $id)
    {
        return view('admin.contenido', [
            'item' => $this->buscar($id),
            'tipos' => $this->tipos(),
            'versiones' => DB::table('cms_content_version as v')
                ->join('staff_user as s', 's.id', '=', 'v.modified_by')
                ->where('v.content_id', $id)->orderByDesc('v.modified_at')->orderByDesc('v.id')
                ->get(['v.title', 'v.modified_at', DB::raw("s.first_name || ' ' || s.last_name as autor")]),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $d = $this->validar($request);
        $nueva = $this->guardarImagen($request);
        $anterior = null;

        DB::transaction(function () use ($id, $d, $request, $nueva, &$anterior) {
            $actual = DB::table('cms_content')->where('id', $id)->lockForUpdate()->first() ?? abort(404);

            // Imagen: la nueva reemplaza a la actual; "quitar" la elimina.
            $imagen = $nueva ?? ($request->boolean('quitar_imagen') ? null : $actual->image_path);
            if ($imagen !== $actual->image_path) {
                $anterior = $actual->image_path;
            }

            // Se guarda la versión anterior antes de sobrescribir.
            DB::table('cms_content_version')->insert([
                'content_id' => $id, 'title' => $actual->title, 'body' => $actual->body, 'modified_by' => $request->user()->id,
            ]);
            DB::table('cms_content')->where('id', $id)->update($d + [
                'image_path' => $imagen,
                'published_at' => ! $actual->published && $d['published'] ? now() : $actual->published_at,
                'unpublished_at' => $actual->published && ! $d['published'] ? now() : $actual->unpublished_at,
            ]);
        });

        if ($anterior) {
            Storage::delete("cms/$anterior");
        }

        return redirect()->route('admin.contenidos.edit', $id)->with('ok', 'Contenido actualizado.');
    }

    private function validar(Request $request): array
    {
        $d = $request->validate([
            'content_type_id' => 'required|exists:cms_content_type,id',
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:50000',
            'imagen' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096', // sin SVG: puede llevar scripts
        ], [
            'required' => 'Falta el campo :attribute.',
            'title.max' => 'El título es demasiado largo.',
            'body.max' => 'El contenido es demasiado largo.',
            'imagen.max' => 'La imagen pesa más de 4 MB.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG o WebP.',
            'imagen.uploaded' => 'La imagen no se pudo subir (máximo 4 MB).',
        ], ['content_type_id' => 'tipo de contenido', 'title' => 'título', 'body' => 'contenido']);
        unset($d['imagen']);

        return $d + ['published' => $request->boolean('published')];
    }

    /** Guarda la imagen en disco (no en la base de datos) y devuelve solo el nombre del archivo. */
    private function guardarImagen(Request $request): ?string
    {
        return $request->file('imagen') ? basename($request->file('imagen')->store('cms')) : null;
    }

    private function tipos()
    {
        return DB::table('cms_content_type')->orderBy('name')->get();
    }

    private function buscar(string $id): object
    {
        abort_unless(Str::isUuid($id), 404);

        return DB::table('cms_content')->where('id', $id)->first() ?? abort(404);
    }
}
