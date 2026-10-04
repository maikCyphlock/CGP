<?php

namespace App\Http\Controllers\Oac;

use App\Http\Controllers\Controller;
use App\Models\CaseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExpedienteController extends Controller
{
    // Único flujo permitido entre estatus. Lo que no está aquí se rechaza en el servidor.
    const TRANSICIONES = [
        'RECEIVED' => ['IN_REVIEW', 'INVALIDATED'],
        'IN_REVIEW' => ['PROCESSED', 'REFERRED', 'ARCHIVED', 'INVALIDATED'],
        'PROCESSED' => ['REFERRED', 'ARCHIVED'],
        'REFERRED' => ['ARCHIVED'],
    ];

    // Texto del botón/opción para cada estatus destino.
    const ACCIONES = [
        'IN_REVIEW' => 'Protocolizar (pasa a En Revisión)',
        'PROCESSED' => 'Marcar como Procesado',
        'REFERRED' => 'Derivar a otra unidad',
        'ARCHIVED' => 'Archivar (cierra el expediente)',
        'INVALIDATED' => 'Invalidar (cierra el expediente)',
    ];

    public function inicio(Request $request)
    {
        if (! $request->user()->puede('CASES')) {
            return view('oac.inicio', ['kpi' => null, 'movimientos' => collect()]);
        }

        $id = $this->estatus()->pluck('id', 'code');

        return view('oac.inicio', [
            'kpi' => [
                'mes' => CaseFile::where('created_at', '>=', now()->startOfMonth())->count(),
                'recibidos' => CaseFile::where('status_id', $id['RECEIVED'])->count(),
                'revision' => CaseFile::whereIn('status_id', [$id['IN_REVIEW'], $id['PROCESSED'], $id['REFERRED']])->count(),
                'cerrados' => CaseFile::whereIn('status_id', [$id['ARCHIVED'], $id['INVALIDATED']])->count(),
            ],
            'movimientos' => DB::table('case_status_log as l')
                ->join('case_file as c', 'c.id', '=', 'l.case_file_id')
                ->join('case_status as s', 's.id', '=', 'l.new_status')
                ->leftJoin('staff_user as u', 'u.id', '=', 'l.changed_by')
                ->orderByDesc('l.changed_at')->orderByDesc('l.id')
                ->limit(10)
                ->get(['l.changed_at', 'l.previous_status', 's.name as estatus', 'c.id', 'c.case_number',
                    DB::raw("COALESCE(u.first_name || ' ' || u.last_name, 'Portal público') as usuario")]),
        ]);
    }

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'estado' => ['nullable', Rule::in($this->estatus()->pluck('code'))],
            'tipo' => ['nullable', Rule::in(DB::table('claim_type')->pluck('code'))],
            'q' => 'nullable|string|max:80',
        ]);
        $estatus = $this->estatus();

        $expedientes = CaseFile::with(['citizen', 'claimType'])
            ->when($filtros['estado'] ?? null, fn ($q, $e) => $q->where('status_id', $estatus->firstWhere('code', $e)->id))
            ->when($filtros['tipo'] ?? null, fn ($q, $t) => $q->whereHas('claimType', fn ($q) => $q->where('code', $t)))
            ->when($filtros['q'] ?? null, function ($q, $texto) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $texto).'%';
                $q->where(fn ($q) => $q->where('case_number', 'ilike', $like)
                    ->orWhereHas('citizen', fn ($q) => $q->where('id_doc_number', 'ilike', $like)
                        ->orWhereRaw("first_name || ' ' || last_name ilike ?", [$like])));
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('oac.expedientes', [
            'expedientes' => $expedientes,
            'estatus' => $estatus->keyBy('id'),
            'tipos' => DB::table('claim_type')->orderBy('id')->get(),
            'conteo' => CaseFile::select('status_id', DB::raw('count(*) as n'))->groupBy('status_id')->pluck('n', 'status_id'),
            'filtros' => $filtros,
        ]);
    }

    public function show(Request $request, CaseFile $expediente)
    {
        $expediente->load(['citizen', 'claimType', 'respondents.respondentType']);
        $estatus = $this->estatus()->keyBy('id');
        $actual = $estatus[$expediente->status_id];

        return view('oac.expediente', [
            'e' => $expediente,
            'c' => $expediente->citizen,
            'actual' => $actual,
            'opciones' => collect(self::TRANSICIONES[$actual->code] ?? [])
                ->reject(fn ($code) => $code === 'REFERRED' && ! $request->user()->puede('CLASSIFY', 'write'))
                ->mapWithKeys(fn ($code) => [$code => self::ACCIONES[$code]]),
            'puedeEscribir' => $request->user()->puede('CASES', 'write'),
            'puedeClasificar' => $request->user()->puede('CLASSIFY', 'write') && ! $actual->is_terminal,
            'irregularidades' => DB::table('irregularity_type')->where('active', true)->orderBy('name')->get(),
            'irregularidad' => DB::table('irregularity_type')->where('id', $expediente->irregularity_type_id)->value('name'),
            'unidades' => DB::table('referral_unit')->where('active', true)->orderBy('name')->get(),
            'unidad' => DB::table('referral_unit')->where('id', $expediente->referral_unit_id)->value('name'),
            'docTipo' => DB::table('id_document_type')->where('id', $expediente->citizen->id_doc_type_id)->value('code'),
            'consulta' => DB::table('popular_consultation')->where('case_file_id', $expediente->id)->first(),
            'archivos' => DB::table('evidence_file')->where('case_file_id', $expediente->id)->orderBy('created_at')->get(),
            'documentos' => DB::table('case_physical_doc as d')
                ->join('physical_doc_type as t', 't.id', '=', 'd.physical_doc_type_id')
                ->where('d.case_file_id', $expediente->id)->pluck('t.name'),
            'historial' => DB::table('case_action as a')
                ->join('staff_user as u', 'u.id', '=', 'a.user_id')
                ->where('a.case_file_id', $expediente->id)
                ->orderByDesc('a.performed_at')->orderByDesc('a.id')
                ->get(['a.action_type', 'a.payload', 'a.performed_at', DB::raw("u.first_name || ' ' || u.last_name as usuario")])
                ->map(fn ($a) => tap($a, fn ($a) => $a->payload = json_decode($a->payload, true))),
            'estatus' => $estatus,
        ]);
    }

    /** Registra una actuación: nota sola, o nota + cambio de estatus. */
    public function actuar(Request $request, CaseFile $expediente)
    {
        $d = $request->validate([
            'desde' => 'required|string',
            'estatus' => ['nullable', Rule::in(array_keys(self::ACCIONES))],
            'nota' => 'required|string|min:5|max:2000',
            'referral_unit_id' => 'exclude_unless:estatus,REFERRED|required|integer|exists:referral_unit,id',
            'referral_letter_url' => 'exclude_unless:estatus,REFERRED|nullable|url:http,https|max:300',
        ], [
            'referral_letter_url.url' => 'El enlace del oficio no es válido (debe empezar por http:// o https://).',
            'nota.required' => 'Escriba una observación.',
            'nota.min' => 'La observación debe tener al menos 5 caracteres.',
            'nota.max' => 'La observación no puede superar 2000 caracteres.',
            'referral_unit_id.required' => 'Seleccione la unidad a la que se deriva.',
            'estatus.in' => 'La acción seleccionada no es válida.',
        ]);
        $user = $request->user();

        if (($d['estatus'] ?? null) === 'REFERRED' && ! $user->puede('CLASSIFY', 'write')) {
            abort(403);
        }

        $mensaje = DB::transaction(function () use ($d, $expediente, $user) {
            // Bloquea la fila: dos personas no pueden mover el mismo expediente a la vez.
            $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();
            $estatus = $this->estatus();
            $actual = $estatus->firstWhere('id', $e->status_id);

            if ($actual->code !== $d['desde']) {
                return ['error' => "Otro usuario actualizó este expediente mientras usted lo revisaba (ahora está «{$actual->name}»). Revise los cambios e intente de nuevo."];
            }

            $destino = $d['estatus'] ?? null;
            if (! $destino) {
                $this->registrar($e, $user, 'NOTE', ['nota' => $d['nota']]);

                return ['ok' => 'Observación registrada.'];
            }

            if (! in_array($destino, self::TRANSICIONES[$actual->code] ?? [], true)) {
                return ['error' => "Un expediente «{$actual->name}» no puede pasar a ese estatus."];
            }

            $nuevo = $estatus->firstWhere('code', $destino);
            $cambios = ['status_id' => $nuevo->id];
            if ($destino === 'IN_REVIEW' && ! $e->protocolized_at) {
                $cambios += ['protocolized_at' => now(), 'received_by' => $user->id];
            }
            if ($destino === 'REFERRED') {
                $cambios += ['referral_unit_id' => $d['referral_unit_id'], 'referred_at' => now(), 'referred_by' => $user->id,
                    'referral_letter_url' => $d['referral_letter_url'] ?? null];
            }
            $e->update($cambios);

            // El trigger escribe el historial; aquí se completa quién hizo el cambio.
            $logId = DB::table('case_status_log')->where('case_file_id', $e->id)->where('new_status', $nuevo->id)->max('id');
            DB::table('case_status_log')->where('id', $logId)->update(['changed_by' => $user->id]);

            $this->registrar($e, $user, 'STATUS_CHANGE', [
                'de' => $actual->code, 'a' => $destino, 'nota' => $d['nota'],
                'unidad' => $destino === 'REFERRED' ? DB::table('referral_unit')->where('id', $d['referral_unit_id'])->value('name') : null,
                'oficio' => $destino === 'REFERRED' ? ($d['referral_letter_url'] ?? null) : null,
            ]);

            return ['ok' => "Expediente actualizado a «{$nuevo->name}»."];
        });

        return isset($mensaje['error'])
            ? back()->withErrors(['estatus' => $mensaje['error']])
            : redirect()->route('oac.expedientes.show', $expediente)->with('ok', $mensaje['ok']);
    }

    /** Tipifica la irregularidad y guarda las notas del analista (módulo CLASSIFY). */
    public function clasificar(Request $request, CaseFile $expediente)
    {
        $d = $request->validate([
            'irregularity_type_id' => ['nullable', Rule::exists('irregularity_type', 'id')->where('active', true)],
            'analyst_notes' => 'nullable|string|max:3000',
        ], ['analyst_notes.max' => 'Las notas no pueden superar 3000 caracteres.']);

        DB::transaction(function () use ($d, $expediente, $request) {
            $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();
            $cerrado = DB::table('case_status')->where('id', $e->status_id)->value('is_terminal');
            abort_if($cerrado, 422, 'El expediente está cerrado y no se puede clasificar.');

            $e->update(['irregularity_type_id' => $d['irregularity_type_id'] ?? null, 'analyst_notes' => $d['analyst_notes'] ?? null]);
            $this->registrar($e, $request->user(), 'CLASSIFICATION', [
                'irregularidad' => DB::table('irregularity_type')->where('id', $d['irregularity_type_id'] ?? null)->value('name'),
                'nota' => $d['analyst_notes'] ?? null,
            ]);
        });

        return redirect()->route('oac.expedientes.show', $expediente)->with('ok', 'Clasificación guardada.');
    }

    public function archivo(CaseFile $expediente, string $archivo)
    {
        $f = DB::table('evidence_file')->where('case_file_id', $expediente->id)->where('id', $archivo)->first();
        abort_unless($f && Storage::exists($f->storage_url), 404, 'El archivo no existe.');

        return Storage::download($f->storage_url, $f->original_name);
    }

    private function registrar(CaseFile $e, $user, string $tipo, array $payload): void
    {
        DB::table('case_action')->insert([
            'case_file_id' => $e->id,
            'user_id' => $user->id,
            'action_type' => $tipo,
            'payload' => json_encode(array_filter($payload, fn ($v) => $v !== null)),
        ]);
    }

    private function estatus()
    {
        return DB::table('case_status')->orderBy('sort_order')->get();
    }
}
