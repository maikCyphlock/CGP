<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * CRUD genérico de las listas maestras. Para añadir un catálogo basta una entrada en CATALOGOS.
 * Nunca se borra: se desactiva (los expedientes históricos siguen apuntando al registro).
 * El `code` no se puede cambiar después de creado: el código de la aplicación depende de él.
 * No se exponen case_status ni id_document_type: la lógica de estatus y el formulario usan sus códigos fijos.
 */
class CatalogoController extends Controller
{
    const CATALOGOS = [
        'tipos-tramite' => ['tabla' => 'claim_type', 'titulo' => 'Tipos de trámite', 'campos' => [
            ['code', 'Código', 'text', 30], ['name', 'Nombre', 'text', 80],
            ['validation_level', 'Nivel de validación', 'select', ['STRICT' => 'Estricto', 'BASIC' => 'Básico', 'AUTOMATED' => 'Automático']],
        ]],
        'irregularidades' => ['tabla' => 'irregularity_type', 'titulo' => 'Tipos de irregularidad', 'campos' => [
            ['code', 'Código', 'text', 40], ['name', 'Nombre', 'text', 120], ['legal_basis', 'Base legal', 'textarea', 1000],
        ]],
        'unidades-derivacion' => ['tabla' => 'referral_unit', 'titulo' => 'Unidades de derivación', 'campos' => [
            ['code', 'Código', 'text', 30], ['name', 'Nombre', 'text', 100],
        ]],
        'tipos-senalado' => ['tabla' => 'respondent_type', 'titulo' => 'Tipos de señalado', 'campos' => [
            ['code', 'Código', 'text', 30], ['name', 'Nombre', 'text', 80], ['field_schema', 'Campos de identificación (JSON)', 'json', null],
        ]],
        'documentos-fisicos' => ['tabla' => 'physical_doc_type', 'titulo' => 'Documentos físicos consignados', 'campos' => [
            ['code', 'Código', 'text', 40], ['name', 'Nombre', 'text', 80],
        ]],
        'cargos' => ['tabla' => 'job_position', 'titulo' => 'Cargos del personal', 'campos' => [
            ['title', 'Cargo', 'text', 100], ['description', 'Descripción', 'textarea', 500],
        ]],
    ];

    /** Qué listas administra cada módulo: la OAC las suyas; Administración, los cargos del personal. */
    const POR_MODULO = [
        'oac' => ['tipos-tramite', 'irregularidades', 'unidades-derivacion', 'tipos-senalado', 'documentos-fisicos'],
        'admin' => ['cargos'],
    ];

    public function index(Request $request)
    {
        return view('panel.catalogos', [
            'catalogos' => collect(self::CATALOGOS)->only(self::POR_MODULO[$request->segment(1)])->map(fn ($c) => $c + ['total' => DB::table($c['tabla'])->count()]),
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $c = $this->config($request, $slug);
        $editar = $request->query('editar');

        return view('panel.catalogo', [
            'slug' => $slug,
            'c' => $c,
            'filas' => DB::table($c['tabla'])->orderByDesc('active')->orderBy('id')->get(),
            'fila' => $editar ? DB::table($c['tabla'])->where('id', (int) $editar)->first() : null,
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $c = $this->config($request, $slug);
        DB::table($c['tabla'])->insert($this->datos($request, $c, null) + ['active' => true]);

        return redirect()->route($request->segment(1).'.catalogos.show', $slug)->with('ok', 'Registro creado.');
    }

    public function update(Request $request, string $slug, int $id)
    {
        $c = $this->config($request, $slug);
        abort_unless(DB::table($c['tabla'])->where('id', $id)->exists(), 404);

        $datos = $this->datos($request, $c, $id) + ['active' => $request->boolean('active')];

        if ($c['tabla'] === 'job_position' && ! $datos['active']
            && DB::table('staff_user')->where('job_position_id', $id)->where('active', true)->exists()) {
            return back()->withInput()->withErrors(['active' => 'No se puede desactivar: hay personal activo con este cargo.']);
        }
        try {
            DB::table($c['tabla'])->where('id', $id)->update($datos);
        } catch (\Illuminate\Database\QueryException $e) {
            // Ej. el trigger de job_position no deja desactivar un cargo con personal activo.
            return back()->withInput()->withErrors(['active' => 'No se puede guardar: '.($e->errorInfo[2] ?? 'el registro está en uso.')]);
        }

        return redirect()->route($request->segment(1).'.catalogos.show', $slug)->with('ok', 'Registro actualizado.');
    }

    private function config(Request $request, string $slug): array
    {
        abort_unless(in_array($slug, self::POR_MODULO[$request->segment(1)] ?? []), 404);

        return self::CATALOGOS[$slug];
    }

    /** Valida con las reglas de cada campo y devuelve la fila lista para guardar. */
    private function datos(Request $request, array $c, ?int $id): array
    {
        $reglas = [];
        $nombres = [];
        foreach ($c['campos'] as [$campo, $etiqueta, $tipo, $extra]) {
            $nombres[$campo] = mb_strtolower($etiqueta);
            $reglas[$campo] = match (true) {
                $campo === 'code' => $id ? 'prohibited' : ['required', 'regex:/^[A-Z0-9_]+$/', "max:$extra", Rule::unique($c['tabla'], 'code')],
                $tipo === 'select' => ['required', Rule::in(array_keys($extra))],
                $tipo === 'json' => ['required', 'json'],
                $tipo === 'textarea' => ['nullable', 'string', "max:$extra"],
                default => ['required', 'string', "max:$extra"],
            };
        }

        $d = $request->validate($reglas, [
            'required' => 'Falta el campo :attribute.',
            'max' => 'El campo :attribute no puede superar :max caracteres.',
            'code.regex' => 'El código solo admite MAYÚSCULAS, números y guion bajo (ej. TIPO_NUEVO).',
            'code.unique' => 'Ya existe un registro con ese código.',
            'code.prohibited' => 'El código no se puede cambiar.',
            'json' => 'El campo :attribute no es un JSON válido.',
            'in' => 'El valor de :attribute no es válido.',
        ], $nombres);

        if (isset($d['field_schema'])) {
            abort_unless(is_array(json_decode($d['field_schema'], true)), 422, 'Los campos deben ser una lista JSON.');
        }

        return $d;
    }
}
