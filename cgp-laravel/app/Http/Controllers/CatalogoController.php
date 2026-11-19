<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * CRUD de listas maestras con borrado lógico y paginación.
 */
class CatalogoController extends Controller
{
    const CATALOGOS = [
        'tipos-tramite' => [
            'tabla' => 'claim_type',
            'titulo' => 'Tipos de trámite',
            'campos' => [
                ['code', 'Código', 'text', 30],
                ['name', 'Nombre', 'text', 80],
                ['validation_level', 'Nivel de validación', 'select', ['STRICT' => 'Estricto', 'BASIC' => 'Básico', 'AUTOMATED' => 'Automático']],
            ],
        ],
        'irregularidades' => [
            'tabla' => 'irregularity_type',
            'titulo' => 'Tipos de irregularidad',
            'campos' => [
                ['code', 'Código', 'text', 40],
                ['name', 'Nombre', 'text', 120],
                ['legal_basis', 'Base legal', 'textarea', 1000],
            ],
        ],
        'unidades-derivacion' => [
            'tabla' => 'referral_unit',
            'titulo' => 'Unidades de derivación',
            'campos' => [
                ['code', 'Código', 'text', 30],
                ['name', 'Nombre', 'text', 100],
            ],
        ],
        'tipos-senalado' => [
            'tabla' => 'respondent_type',
            'titulo' => 'Tipos de señalado',
            'campos' => [
                ['code', 'Código', 'text', 30],
                ['name', 'Nombre', 'text', 80],
                ['field_schema', 'Campos de identificación (JSON)', 'json', null],
            ],
        ],
        'documentos-fisicos' => [
            'tabla' => 'physical_doc_type',
            'titulo' => 'Documentos físicos consignados',
            'campos' => [
                ['code', 'Código', 'text', 40],
                ['name', 'Nombre', 'text', 80],
            ],
        ],
        'cargos' => [
            'tabla' => 'job_position',
            'titulo' => 'Cargos del personal',
            'campos' => [
                ['title', 'Cargo', 'text', 100],
                ['description', 'Descripción', 'textarea', 500],
            ],
        ],
    ];

    const POR_MODULO = [
        'oac' => ['tipos-tramite', 'irregularidades', 'unidades-derivacion', 'tipos-senalado', 'documentos-fisicos'],
        'admin' => ['cargos'],
    ];

    public function index(Request $request)
    {
        $mod = $request->segment(1);
        $permitidos = self::POR_MODULO[$mod] ?? [];

        $catalogos = collect(self::CATALOGOS)->only($permitidos)->map(function ($c) {
            return $c + [
                'total' => DB::table($c['tabla'])->count(),
                'activos' => DB::table($c['tabla'])->where('active', true)->count(),
                'inactivos' => DB::table($c['tabla'])->where('active', false)->count(),
            ];
        });

        return view('panel.catalogos', compact('catalogos'));
    }

    public function show(Request $request, string $slug)
    {
        $c = $this->config($request, $slug);
        $q = trim((string) $request->query('q', ''));
        $estado = $request->query('estado', 'todos');
        $editarId = $request->query('editar');

        $query = DB::table($c['tabla']);

        if ($estado === 'activos') {
            $query->where('active', true);
        } elseif ($estado === 'inactivos') {
            $query->where('active', false);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($c, $q) {
                foreach ($c['campos'] as [$campo, , $tipo]) {
                    if (in_array($tipo, ['text', 'textarea', 'select'])) {
                        $sub->orWhere($campo, 'ILIKE', "%{$q}%");
                    }
                }
            });
        }

        $filas = $query->orderByDesc('active')->orderBy('id')->paginate(10)->withQueryString();

        $conteo = [
            'total' => DB::table($c['tabla'])->count(),
            'activos' => DB::table($c['tabla'])->where('active', true)->count(),
            'inactivos' => DB::table($c['tabla'])->where('active', false)->count(),
        ];

        $fila = $editarId ? DB::table($c['tabla'])->where('id', (int) $editarId)->first() : null;

        return view('panel.catalogo', [
            'slug' => $slug,
            'c' => $c,
            'filas' => $filas,
            'fila' => $fila,
            'conteo' => $conteo,
            'filtros' => [
                'q' => $q,
                'estado' => $estado,
            ],
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $c = $this->config($request, $slug);
        $datos = $this->datos($request, $c, null);
        $activo = $request->has('active') ? $request->boolean('active') : true;

        DB::table($c['tabla'])->insert($datos + ['active' => $activo]);

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
            return back()->withInput()->withErrors(['active' => 'No se puede guardar: '.($e->errorInfo[2] ?? 'el registro está en uso.')]);
        }

        return redirect()->route($request->segment(1).'.catalogos.show', $slug)->with('ok', 'Registro actualizado.');
    }

    public function toggle(Request $request, string $slug, int $id)
    {
        $c = $this->config($request, $slug);
        $actual = DB::table($c['tabla'])->where('id', $id)->first();
        abort_unless($actual, 404);

        $nuevoEstado = $request->has('active') ? $request->boolean('active') : ! (bool) $actual->active;

        if ($c['tabla'] === 'job_position' && ! $nuevoEstado
            && DB::table('staff_user')->where('job_position_id', $id)->where('active', true)->exists()) {
            $msg = 'No se puede desactivar: hay personal activo con este cargo.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => $msg], 422);
            }
            return back()->withErrors(['active' => $msg]);
        }

        try {
            DB::table($c['tabla'])->where('id', $id)->update(['active' => $nuevoEstado]);
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'Error al cambiar estado: '.($e->errorInfo[2] ?? 'el registro está en uso.');
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => $msg], 422);
            }
            return back()->withErrors(['active' => $msg]);
        }

        $mensaje = $nuevoEstado ? 'Registro activado.' : 'Registro desactivado.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'active' => $nuevoEstado, 'mensaje' => $mensaje]);
        }

        return back()->with('ok', $mensaje);
    }

    public function destroy(Request $request, string $slug, int $id)
    {
        $c = $this->config($request, $slug);
        $actual = DB::table($c['tabla'])->where('id', $id)->first();
        abort_unless($actual, 404);

        if ($c['tabla'] === 'job_position'
            && DB::table('staff_user')->where('job_position_id', $id)->where('active', true)->exists()) {
            $msg = 'No se puede desactivar: hay personal activo con este cargo.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => $msg], 422);
            }
            return back()->withErrors(['active' => $msg]);
        }

        try {
            DB::table($c['tabla'])->where('id', $id)->update(['active' => false]);
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'Error al desactivar: '.($e->errorInfo[2] ?? 'el registro está en uso.');
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => $msg], 422);
            }
            return back()->withErrors(['active' => $msg]);
        }

        $mensaje = 'Registro desactivado (borrado lógico).';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'active' => false, 'mensaje' => $mensaje]);
        }

        return back()->with('ok', $mensaje);
    }

    private function config(Request $request, string $slug): array
    {
        abort_unless(in_array($slug, self::POR_MODULO[$request->segment(1)] ?? []), 404);

        return self::CATALOGOS[$slug];
    }

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
