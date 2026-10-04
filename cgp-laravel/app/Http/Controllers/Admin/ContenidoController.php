<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function create()
    {
        return view('admin.contenido', ['item' => null, 'tipos' => $this->tipos(), 'versiones' => collect()]);
    }

    public function store(Request $request)
    {
        $d = $this->validar($request);
        $id = (string) Str::uuid();

        DB::table('cms_content')->insert([
            'id' => $id, 'author_id' => $request->user()->id,
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

        DB::transaction(function () use ($id, $d, $request) {
            $actual = DB::table('cms_content')->where('id', $id)->lockForUpdate()->first() ?? abort(404);

            // Se guarda la versión anterior antes de sobrescribir.
            DB::table('cms_content_version')->insert([
                'content_id' => $id, 'title' => $actual->title, 'body' => $actual->body, 'modified_by' => $request->user()->id,
            ]);
            DB::table('cms_content')->where('id', $id)->update($d + [
                'published_at' => ! $actual->published && $d['published'] ? now() : $actual->published_at,
                'unpublished_at' => $actual->published && ! $d['published'] ? now() : $actual->unpublished_at,
            ]);
        });

        return redirect()->route('admin.contenidos.edit', $id)->with('ok', 'Contenido actualizado.');
    }

    private function validar(Request $request): array
    {
        $d = $request->validate([
            'content_type_id' => 'required|exists:cms_content_type,id',
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:50000',
        ], [
            'required' => 'Falta el campo :attribute.',
            'max' => 'El campo :attribute es demasiado largo.',
        ], ['content_type_id' => 'tipo de contenido', 'title' => 'título', 'body' => 'contenido']);

        return $d + ['published' => $request->boolean('published')];
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
