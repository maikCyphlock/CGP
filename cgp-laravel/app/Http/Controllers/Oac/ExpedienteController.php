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
        'RECEIVED'    => ['PROCESSING', 'INVALIDATED'],
        'PROCESSING'  => ['SUBSANATION', 'REFERRED', 'INVALIDATED', 'ARCHIVED'],
        'SUBSANATION' => ['PROCESSING', 'REFERRED', 'INVALIDATED', 'ARCHIVED'],
        'REFERRED'    => ['ARCHIVED'],
        // Compatibilidad con expedientes creados antes de la migración
        'IN_REVIEW'   => ['PROCESSING', 'SUBSANATION', 'REFERRED', 'INVALIDATED', 'ARCHIVED'],
        'PROCESSED'   => ['PROCESSING', 'SUBSANATION', 'REFERRED', 'INVALIDATED', 'ARCHIVED'],
    ];

    // Texto del botón/opción para cada estatus destino.
    const ACCIONES = [
        'PROCESSING'  => 'En tramitación / valoración',
        'SUBSANATION' => 'En subsanación',
        'REFERRED'    => 'Remitido / derivado',
        'INVALIDATED' => 'Improcedente / desestimado y archivado',
        'ARCHIVED'    => 'Cerrado / archivado',
    ];

    public function inicio(Request $request)
    {
        if (! $request->user()->puede('CASES')) {
            return view('oac.inicio', ['kpi' => null, 'movimientos' => collect()]);
        }

        $id = $this->estatus()->pluck('id', 'code');
        $recibidosId = $id['RECEIVED'] ?? 0;
        $revisionIds = array_values(array_filter([
            $id['PROCESSING'] ?? null,
            $id['SUBSANATION'] ?? null,
            $id['REFERRED'] ?? null,
            $id['IN_REVIEW'] ?? null,
            $id['PROCESSED'] ?? null,
        ]));
        $cerradosIds = array_values(array_filter([
            $id['ARCHIVED'] ?? null,
            $id['INVALIDATED'] ?? null,
        ]));

        return view('oac.inicio', [
            'kpi' => [
                'mes' => CaseFile::where('created_at', '>=', now()->startOfMonth())->count(),
                'recibidos' => CaseFile::where('status_id', $recibidosId)->count(),
                'revision' => CaseFile::whereIn('status_id', $revisionIds)->count(),
                'cerrados' => CaseFile::whereIn('status_id', $cerradosIds)->count(),
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

        // Asegurar que OAC exista en las unidades/oficinas disponibles
        try {
            if (! DB::table('referral_unit')->where('code', 'OAC')->exists()) {
                DB::table('referral_unit')->insert(['code' => 'OAC', 'name' => 'Oficina de Atención al Ciudadano', 'active' => true]);
            }
        } catch (\Throwable $th) {
            // No crítico si ya existe o falla por permisos
        }

        // Catálogos para asignación explícita
        $oficinas = DB::table('referral_unit')->where('active', true)->orderBy('name')->get();
        $cargos = DB::table('job_position')->where('active', true)->orderBy('title')->get();
        $usuariosPersonal = DB::table('staff_user as u')
            ->leftJoin('job_position as j', 'j.id', '=', 'u.job_position_id')
            ->where('u.active', true)
            ->orderBy('u.first_name')
            ->get(['u.id', 'u.first_name', 'u.last_name', 'u.job_position_id', 'j.title as cargo']);

        // Valores resueltos de la asignación actual
        $asignacionOficina = null;
        if (! empty($expediente->assigned_unit_id)) {
            $asignacionOficina = DB::table('referral_unit')->where('id', $expediente->assigned_unit_id)->value('name');
        }
        if (! $asignacionOficina && ! empty($expediente->referral_unit_id)) {
            $asignacionOficina = DB::table('referral_unit')->where('id', $expediente->referral_unit_id)->value('name');
        }
        $asignacionOficina = $asignacionOficina ?: 'Oficina de Atención al Ciudadano (OAC)';

        $asignacionCargo = null;
        if (! empty($expediente->assigned_position_id)) {
            $asignacionCargo = DB::table('job_position')->where('id', $expediente->assigned_position_id)->value('title');
        }
        $asignacionCargo = $asignacionCargo ?: 'Analista de OAC';

        $asignacionUsuario = null;
        if (! empty($expediente->assigned_user_id)) {
            $asignacionUsuario = DB::table('staff_user')->where('id', $expediente->assigned_user_id)
                ->selectRaw("first_name || ' ' || last_name as nombre")->value('nombre');
        }
        $asignacionUsuario = $asignacionUsuario ?: 'Sin funcionario asignado';

        $asignacionFecha = $expediente->assigned_at ?? $expediente->protocolized_at ?? $expediente->created_at;

        $respuestasCiudadano = collect();
        try {
            $respuestasCiudadano = DB::table('case_citizen_response')
                ->where('case_file_id', $expediente->id)
                ->orderByDesc('response_date')
                ->orderByDesc('created_at')
                ->get();
        } catch (\Throwable $th) {
            $respuestasCiudadano = collect();
        }

        $deadlineConfirmacion = $expediente->data_confirmation_deadline ? \Illuminate\Support\Carbon::parse($expediente->data_confirmation_deadline)->endOfDay() : null;
        $diasRestantesConfirmacion = $deadlineConfirmacion ? (int) ceil(now()->diffInDays($deadlineConfirmacion, false)) : 0;
        $confirmacionVigente = ($deadlineConfirmacion && now()->lte($deadlineConfirmacion) && empty($expediente->data_confirmed_at));
        $confirmacionVencida = ($deadlineConfirmacion && now()->gt($deadlineConfirmacion) && empty($expediente->data_confirmed_at));

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
            'unidades' => $oficinas,
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
            'oficinas' => $oficinas,
            'cargos' => $cargos,
            'usuariosPersonal' => $usuariosPersonal,
            'asignacionOficina' => $asignacionOficina,
            'asignacionCargo' => $asignacionCargo,
            'asignacionUsuario' => $asignacionUsuario,
            'asignacionFecha' => $asignacionFecha,
            'respuestasCiudadano' => $respuestasCiudadano,
            'deadlineConfirmacion' => $deadlineConfirmacion,
            'diasRestantesConfirmacion' => max(0, $diasRestantesConfirmacion),
            'confirmacionVigente' => $confirmacionVigente,
            'confirmacionVencida' => $confirmacionVencida,
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

        $mensaje = DB::transaction(function () use ($d, $expediente, $user, $request) {
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

            // Regla de negocio: Lapso de anticipación para confirmar/rectificar datos
            $deadline = $e->data_confirmation_deadline ? \Illuminate\Support\Carbon::parse($e->data_confirmation_deadline)->endOfDay() : null;
            if (in_array($destino, ['PROCESSING', 'IN_REVIEW'], true) && $deadline && now()->lte($deadline) && empty($e->data_confirmed_at) && ! $request->boolean('ignorar_lapso_confirmacion')) {
                return ['error' => 'El denunciante tiene un lapso activo de anticipación para confirmar o rectificar sus datos que vence el ' . $deadline->format('d/m/Y') . '. Para formalizar excepcionalmente antes de dicho vencimiento, marque la confirmación correspondiente en el formulario.'];
            }

            $nuevo = $estatus->firstWhere('code', $destino);
            if (! $nuevo) {
                return ['error' => "El estatus destino «{$destino}» no se encuentra disponible en el catálogo del sistema."];
            }

            $cambios = ['status_id' => $nuevo->id];
            if (in_array($destino, ['PROCESSING', 'IN_REVIEW'], true) && ! $e->protocolized_at) {
                $cambios += ['protocolized_at' => now(), 'received_by' => $user->id];
            }
            if ($destino === 'REFERRED') {
                $cambios += [
                    'referral_unit_id' => $d['referral_unit_id'],
                    'assigned_unit_id' => $d['referral_unit_id'],
                    'referred_at' => now(),
                    'referred_by' => $user->id,
                    'referral_letter_url' => $d['referral_letter_url'] ?? null,
                ];
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

    /** Asigna explícitamente el trámite a una oficina/departamento, cargo y funcionario responsable. */
    public function asignar(Request $request, CaseFile $expediente)
    {
        $d = $request->validate([
            'assigned_unit_id' => 'required|integer|exists:referral_unit,id',
            'assigned_position_id' => 'required|integer|exists:job_position,id',
            'assigned_user_id' => 'nullable|uuid|exists:staff_user,id',
            'assignment_notes' => 'nullable|string|max:2000',
        ], [
            'assigned_unit_id.required' => 'Seleccione la oficina o departamento.',
            'assigned_unit_id.exists' => 'La oficina seleccionada no es válida.',
            'assigned_position_id.required' => 'Seleccione el cargo asignado.',
            'assigned_position_id.exists' => 'El cargo seleccionado no es válido.',
            'assigned_user_id.exists' => 'El funcionario seleccionado no es válido.',
            'assignment_notes.max' => 'Las notas de asignación no pueden superar 2000 caracteres.',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($d, $expediente, $user) {
            $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();

            $nombreOficina = DB::table('referral_unit')->where('id', $d['assigned_unit_id'])->value('name') ?? 'Oficina designada';
            $nombreCargo = DB::table('job_position')->where('id', $d['assigned_position_id'])->value('title') ?? 'Cargo designado';
            $nombreFuncionario = ! empty($d['assigned_user_id'])
                ? (DB::table('staff_user')->where('id', $d['assigned_user_id'])->selectRaw("first_name || ' ' || last_name as nombre")->value('nombre') ?? null)
                : null;

            $campos = [
                'assigned_unit_id' => $d['assigned_unit_id'],
                'assigned_position_id' => $d['assigned_position_id'],
                'assigned_user_id' => $d['assigned_user_id'] ?? null,
                'assigned_at' => now(),
                'assigned_by' => $user->id,
                'assignment_notes' => $d['assignment_notes'] ?? null,
            ];

            $e->update($campos);

            // Registro en historial
            $payload = [
                'tipo_accion' => 'Asignación de trámite',
                'caso' => $e->case_number,
                'oficina' => $nombreOficina,
                'cargo' => $nombreCargo,
                'funcionario' => $nombreFuncionario,
                'nota' => $d['assignment_notes'] ?? null,
            ];

            try {
                $this->registrar($e, $user, 'ASSIGNMENT', $payload);
            } catch (\Throwable $th) {
                // Fallback si la base de datos tuviera check constraint previa
                $this->registrar($e, $user, 'NOTE', array_merge(['accion' => 'ASSIGNMENT'], $payload));
            }
        });

        return redirect()->route('oac.expedientes.show', $expediente)
            ->with('ok', 'Asignación del trámite guardada y registrada en el historial.');
    }

    /** Guarda la derivación formal del trámite a una unidad interna o ente externo. */
    public function derivar(Request $request, CaseFile $expediente)
    {
        $d = $request->validate([
            'deriv_scope' => 'required|string|max:255',
            'deriv_dest_type' => 'required|string|max:255',
            'deriv_unit' => 'nullable|string|max:255',
            'deriv_external_entity' => 'nullable|string|max:500',
            'deriv_office_number' => 'nullable|string|max:100',
            'deriv_office_date' => 'nullable|date',
            'deriv_letter_url' => 'nullable|url|max:500',
            'deriv_fundamento' => 'nullable|string|max:3000',
            'deriv_recaudos' => 'nullable|array',
            'deriv_citizen_notified' => 'nullable|boolean',
            'deriv_citizen_notified_at' => 'nullable|date',
            'deriv_citizen_notified_ref' => 'nullable|string|max:255',
            'nota' => 'required|string|min:5|max:2000',
        ], [
            'deriv_scope.required' => 'Seleccione el ámbito de competencia.',
            'deriv_dest_type.required' => 'Seleccione el tipo de destino / instancia.',
            'deriv_letter_url.url' => 'El enlace del oficio debe ser una URL válida.',
            'nota.required' => 'La observación o motivo de la actuación es obligatorio.',
            'nota.min' => 'La observación debe tener al menos 5 caracteres.',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($d, $expediente, $user) {
            $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();

            $unidadDestino = ! empty($d['deriv_unit']) ? $d['deriv_unit'] : ($d['deriv_external_entity'] ?? 'Instancia correspondiente');

            $cambios = [
                'deriv_scope' => $d['deriv_scope'],
                'deriv_dest_type' => $d['deriv_dest_type'],
                'deriv_unit' => $d['deriv_unit'] ?? null,
                'deriv_external_entity' => $d['deriv_external_entity'] ?? null,
                'deriv_office_number' => $d['deriv_office_number'] ?? null,
                'deriv_office_date' => $d['deriv_office_date'] ?? null,
                'deriv_letter_url' => $d['deriv_letter_url'] ?? null,
                'deriv_fundamento' => $d['deriv_fundamento'] ?? null,
                'deriv_recaudos' => $d['deriv_recaudos'] ?? [],
                'deriv_citizen_notified' => ! empty($d['deriv_citizen_notified']),
                'deriv_citizen_notified_at' => ! empty($d['deriv_citizen_notified']) ? ($d['deriv_citizen_notified_at'] ?? now()) : null,
                'deriv_citizen_notified_ref' => $d['deriv_citizen_notified_ref'] ?? null,
            ];

            // Si se seleccionó una unidad interna existente en referral_unit, se vincula
            if (! empty($d['deriv_unit'])) {
                $refUnitId = DB::table('referral_unit')->where('name', $d['deriv_unit'])->value('id');
                if ($refUnitId) {
                    $cambios['referral_unit_id'] = $refUnitId;
                    $cambios['assigned_unit_id'] = $refUnitId;
                    $cambios['referred_at'] = now();
                    $cambios['referred_by'] = $user->id;
                    if (! empty($d['deriv_letter_url'])) {
                        $cambios['referral_letter_url'] = $d['deriv_letter_url'];
                    }
                }
            }

            // Si existe el estado REFERRED y el actual no es terminal, actualizar estado
            $statusReferred = DB::table('case_status')->where('code', 'REFERRED')->first();
            $currentStatus = DB::table('case_status')->where('id', $e->status_id)->first();
            if ($statusReferred && $currentStatus && ! $currentStatus->is_terminal) {
                $cambios['status_id'] = $statusReferred->id;
            }

            $e->update($cambios);

            // Registro en historial de actuaciones
            $payload = [
                'accion' => 'DERIVATION',
                'tipo_accion' => 'Derivación formal registrada',
                'caso' => $e->case_number,
                'ambito' => $d['deriv_scope'],
                'tipo_destino' => $d['deriv_dest_type'],
                'unidad' => $unidadDestino,
                'nro_oficio' => $d['deriv_office_number'] ?? null,
                'oficio' => $d['deriv_letter_url'] ?? null,
                'nota' => $d['nota'],
            ];

            try {
                $this->registrar($e, $user, 'DERIVATION', $payload);
            } catch (\Throwable $th) {
                $this->registrar($e, $user, 'NOTE', array_merge(['accion' => 'DERIVATION'], $payload));
            }
        });

        return redirect()->route('oac.expedientes.show', $expediente)
            ->with('ok', 'Derivación formal guardada y registrada en el historial.');
    }

    /** Emite una respuesta formal comunicable al ciudadano. */
    public function respuestaCiudadana(Request $request, CaseFile $expediente)
    {
        $d = $request->validate([
            'response_type' => 'required|string|max:100',
            'response_date' => 'required|date',
            'referred_entity' => 'nullable|string|max:500',
            'legal_basis' => 'nullable|string|max:1000',
            'office_circular' => 'nullable|string|max:200',
            'subsanation_days' => 'nullable|integer|min:1|max:60',
            'viabilidad_result' => 'nullable|string|max:1000',
            'informed_max_authority' => 'nullable|boolean',
            'response_text' => 'required|string|min:10|max:5000',
            'issued_by_name' => 'required|string|max:200',
            'issued_by_cargo' => 'required|string|max:200',
        ], [
            'response_type.required' => 'Seleccione el tipo de respuesta o estatus del trámite.',
            'response_date.required' => 'Indique la fecha de la comunicación.',
            'response_text.required' => 'El mensaje de respuesta al ciudadano es obligatorio.',
            'response_text.min' => 'El mensaje debe tener al menos 10 caracteres.',
            'issued_by_name.required' => 'Indique el nombre del firmante.',
            'issued_by_cargo.required' => 'Indique el cargo del firmante.',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($d, $expediente, $user) {
            $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();

            try {
                DB::table('case_citizen_response')->insert([
                    'case_file_id' => $e->id,
                    'user_id' => $user->id,
                    'response_type' => $d['response_type'],
                    'response_date' => $d['response_date'],
                    'referred_entity' => $d['referred_entity'] ?? null,
                    'legal_basis' => $d['legal_basis'] ?? null,
                    'office_circular' => $d['office_circular'] ?? null,
                    'subsanation_days' => $d['subsanation_days'] ?? null,
                    'viabilidad_result' => $d['viabilidad_result'] ?? null,
                    'informed_max_authority' => ! empty($d['informed_max_authority']),
                    'response_text' => $d['response_text'],
                    'issued_by_name' => $d['issued_by_name'],
                    'issued_by_cargo' => $d['issued_by_cargo'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $th) {
                // Si la tabla no estuviera creada aún, continúa sin error fatal
            }

            // Registro en historial de actuaciones
            $payload = [
                'accion' => 'CITIZEN_RESPONSE',
                'tipo_accion' => 'Respuesta ciudadana emitida',
                'caso' => $e->case_number,
                'tipo_respuesta' => $d['response_type'],
                'firmante' => $d['issued_by_name'].' — '.$d['issued_by_cargo'],
                'nota' => $d['response_text'],
            ];

            try {
                $this->registrar($e, $user, 'CITIZEN_RESPONSE', $payload);
            } catch (\Throwable $th) {
                $this->registrar($e, $user, 'NOTE', array_merge(['accion' => 'CITIZEN_RESPONSE'], $payload));
            }
        });

        return redirect()->route('oac.expedientes.show', $expediente)
            ->with('ok', 'Respuesta ciudadana emitida y registrada exitosamente.');
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
