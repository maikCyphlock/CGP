<?php

namespace App\Http\Controllers\Oac;

use App\Http\Controllers\Controller;
use App\Models\CaseFile;
use App\Models\CaseRemision;
use App\Models\Funcionario;
use App\Support\DacEstados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExpedienteController extends Controller
{
    // Único flujo permitido entre estatus. Lo que no está aquí se rechaza en el servidor.
    const TRANSICIONES = DacEstados::TRANSICIONES;

    // Texto del botón/opción para cada estatus destino.
    const ACCIONES = DacEstados::ACCIONES;

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
        $expediente->load([
            'citizen',
            'claimType',
            'respondents.respondentType',
            'assignedFuncionario',
            'remisiones.usuario',
            'notificacionesCiudadanas.remision',
        ]);
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

        // Catálogos y directorio de funcionarios para Valoración y Asignación
        $oficinas = DB::table('referral_unit')->where('active', true)->orderBy('name')->get();
        $cargos = DB::table('job_position')->where('active', true)->orderBy('title')->get();
        $funcionarios = Funcionario::activos()
            ->orderBy('area_direccion')
            ->orderBy('cargo')
            ->orderBy('figura')
            ->orderBy('nombre_completo')
            ->get();

        // Valores resueltos de la asignación actual (prioriza la tabla de funcionarios)
        $funcActual = $expediente->assignedFuncionario;
        $asignacionOficina = $expediente->assigned_area ?: $funcActual?->area_direccion;
        if (! $asignacionOficina && ! empty($expediente->assigned_unit_id)) {
            $asignacionOficina = DB::table('referral_unit')->where('id', $expediente->assigned_unit_id)->value('name');
        }
        if (! $asignacionOficina && ! empty($expediente->referral_unit_id)) {
            $asignacionOficina = DB::table('referral_unit')->where('id', $expediente->referral_unit_id)->value('name');
        }
        $asignacionOficina = $asignacionOficina ?: 'Oficina de Atención al Ciudadano (OAC)';

        $asignacionCargo = $expediente->assigned_cargo ?: $funcActual?->cargo;
        if (! $asignacionCargo && ! empty($expediente->assigned_position_id)) {
            $asignacionCargo = DB::table('job_position')->where('id', $expediente->assigned_position_id)->value('title');
        }
        $asignacionCargo = $asignacionCargo ?: 'Analista de OAC';

        $asignacionFigura = $expediente->assigned_figura ?: $funcActual?->figura ?: 'Sin figura asignada';

        $asignacionUsuario = $expediente->assigned_funcionario_nombre ?: $funcActual?->nombre_completo;
        if (! $asignacionUsuario && ! empty($expediente->assigned_user_id)) {
            $asignacionUsuario = DB::table('staff_user')->where('id', $expediente->assigned_user_id)
                ->selectRaw("first_name || ' ' || last_name as nombre")->value('nombre');
        }
        $asignacionUsuario = $asignacionUsuario ?: 'Sin funcionario asignado';

        $asignacionFecha = $expediente->assigned_at ?? $expediente->protocolized_at ?? $expediente->created_at;

        $remisiones = $expediente->remisiones;
        $respuestasCiudadano = $expediente->notificacionesCiudadanas;

        $deadlineConfirmacion = $expediente->data_confirmation_deadline ? \Illuminate\Support\Carbon::parse($expediente->data_confirmation_deadline)->endOfDay() : null;
        $diasRestantesConfirmacion = $deadlineConfirmacion ? (int) ceil(now()->diffInDays($deadlineConfirmacion, false)) : 0;
        $confirmacionVigente = ($deadlineConfirmacion && now()->lte($deadlineConfirmacion) && empty($expediente->data_confirmed_at));
        $confirmacionVencida = ($deadlineConfirmacion && now()->gt($deadlineConfirmacion) && empty($expediente->data_confirmed_at));

        $deadlineSubsanacion = $expediente->subsanation_deadline ? \Illuminate\Support\Carbon::parse($expediente->subsanation_deadline)->endOfDay() : null;
        $diasHabilesSubsanacion = $deadlineSubsanacion ? DacEstados::diasHabilesRestantes($deadlineSubsanacion) : 0;
        $subsanacionVigente = ($actual->code === 'SUBSANATION' && $deadlineSubsanacion && now()->lte($deadlineSubsanacion));
        $subsanacionVencida = ($actual->code === 'SUBSANATION' && $deadlineSubsanacion && now()->gt($deadlineSubsanacion));

        return view('oac.expediente', [
            'e' => $expediente,
            'c' => $expediente->citizen,
            'actual' => $actual,
            'opciones' => collect(self::TRANSICIONES[$actual->code] ?? [])
                ->reject(fn ($code) => $code === 'REFERRED' && ! $request->user()->puede('CLASSIFY', 'write'))
                ->mapWithKeys(fn ($code) => [$code => self::ACCIONES[$code]]),
            'puedeEscribir' => $request->user()->puede('CASES', 'write'),
            'puedeClasificar' => $request->user()->puede('CLASSIFY', 'write') && ! $actual->is_terminal,
            'puedeDerivar' => $request->user()->puede('CLASSIFY', 'write'),
            'puedeCatalogos' => $request->user()->puede('CATALOGS'),
            'causalesImprocedencia' => DacEstados::CAUSALES_IMPROCEDENCIA,
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
            'funcionarios' => $funcionarios,
            'asignacionOficina' => $asignacionOficina,
            'asignacionCargo' => $asignacionCargo,
            'asignacionFigura' => $asignacionFigura,
            'asignacionUsuario' => $asignacionUsuario,
            'asignacionFecha' => $asignacionFecha,
            'remisiones' => $remisiones,
            'respuestasCiudadano' => $respuestasCiudadano,
            'deadlineConfirmacion' => $deadlineConfirmacion,
            'diasRestantesConfirmacion' => max(0, $diasRestantesConfirmacion),
            'confirmacionVigente' => $confirmacionVigente,
            'confirmacionVencida' => $confirmacionVencida,
            'deadlineSubsanacion' => $deadlineSubsanacion,
            'diasHabilesSubsanacion' => $diasHabilesSubsanacion,
            'subsanacionVigente' => $subsanacionVigente,
            'subsanacionVencida' => $subsanacionVencida,
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
            'subsanation_days' => 'nullable|integer|min:1|max:30',
            'subsanation_missing_docs' => 'nullable|string|max:1000',
            'closure_auto_number' => 'nullable|string|max:100',
            'closure_auto_date' => 'nullable|date',
            'closure_auto_url' => 'nullable|url:http,https|max:500',
            'closure_reason' => 'nullable|string|max:200',
            'dac_approved' => 'nullable|boolean',
            'dac_approved_by' => 'nullable|string|max:160',
            'comptroller_approved' => 'nullable|boolean',
            'comptroller_approved_by' => 'nullable|string|max:160',
        ], [
            'referral_letter_url.url' => 'El enlace del oficio no es válido (debe empezar por http:// o https://).',
            'closure_auto_url.url' => 'El enlace del auto de cierre no es válido (debe empezar por http:// o https://).',
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
            if ($actual->code === 'SUBSANATION' && $destino === 'PROCESSING') {
                $cambios['subsanation_completed_at'] = now();
            }
            if ($destino === 'SUBSANATION') {
                $diasHabiles = (int) ($d['subsanation_days'] ?? 5);
                $cambios += [
                    'subsanation_started_at' => now(),
                    'subsanation_deadline' => DacEstados::sumarDiasHabiles(now(), $diasHabiles)->toDateString(),
                    'subsanation_completed_at' => null,
                    'subsanation_missing_docs' => $d['subsanation_missing_docs'] ?? $e->subsanation_missing_docs,
                ];
            }

            $unidadNombre = null;
            if ($destino === 'REFERRED') {
                $unidadNombre = DB::table('referral_unit')->where('id', $d['referral_unit_id'])->value('name');
                $cambios += [
                    'referral_unit_id' => $d['referral_unit_id'],
                    'assigned_unit_id' => $d['referral_unit_id'],
                    'referred_at' => now(),
                    'referred_by' => $user->id,
                    'referral_letter_url' => $d['referral_letter_url'] ?? null,
                    'deriv_scope' => 'Competencia de la Contraloría del Municipio Páez',
                    'deriv_dest_type' => $unidadNombre === 'Unidad de Auditoría Interna (UAI)' ? 'Unidad de Auditoría Interna' : 'Contraloría del Municipio Páez',
                    'deriv_unit' => $unidadNombre,
                    'deriv_office_date' => now()->toDateString(),
                    'deriv_letter_url' => $d['referral_letter_url'] ?? null,
                ];
            }

            if (in_array($destino, ['ARCHIVED', 'INVALIDATED'], true)) {
                $dacOk = $request->boolean('dac_approved');
                $contralorOk = $request->boolean('comptroller_approved');
                $cambios += [
                    'closure_auto_number' => $d['closure_auto_number'] ?? $e->closure_auto_number,
                    'closure_auto_date' => $d['closure_auto_date'] ?? now()->toDateString(),
                    'closure_auto_url' => $d['closure_auto_url'] ?? $e->closure_auto_url,
                    'closure_reason' => $d['closure_reason'] ?? $e->closure_reason,
                    'dac_approved' => $dacOk,
                    'dac_approved_by' => $dacOk ? ($d['dac_approved_by'] ?? $user->nombre) : null,
                    'dac_approved_at' => $dacOk ? now() : null,
                    'comptroller_approved' => $contralorOk,
                    'comptroller_approved_by' => $contralorOk ? ($d['comptroller_approved_by'] ?? 'Contralor(a) Municipal') : null,
                    'comptroller_approved_at' => $contralorOk ? now() : null,
                ];
            }

            $e->update($cambios);

            if ($destino === 'REFERRED') {
                CaseRemision::create([
                    'case_file_id' => $e->id,
                    'user_id' => $user->id,
                    'ambito_competencia' => 'Competencia de la Contraloría del Municipio Páez',
                    'ente_destino' => $unidadNombre === 'Unidad de Auditoría Interna (UAI)' ? 'Unidad de Auditoría Interna' : 'Contraloría del Municipio Páez',
                    'unidad_interna' => $unidadNombre,
                    'referral_unit_id' => $d['referral_unit_id'],
                    'fecha_remision' => now()->toDateString(),
                    'oficio_url' => $d['referral_letter_url'] ?? null,
                    'observacion' => $d['nota'],
                ]);
            }

            // El trigger escribe el historial; aquí se completa quién hizo el cambio.
            $logId = DB::table('case_status_log')->where('case_file_id', $e->id)->where('new_status', $nuevo->id)->max('id');
            DB::table('case_status_log')->where('id', $logId)->update(['changed_by' => $user->id]);

            $this->registrar($e, $user, 'STATUS_CHANGE', [
                'de' => $actual->code,
                'a' => $destino,
                'nota' => $d['nota'],
                'unidad' => $destino === 'REFERRED' ? $unidadNombre : null,
                'oficio' => $destino === 'REFERRED' ? ($d['referral_letter_url'] ?? null) : null,
                'subsanacion_limite' => $destino === 'SUBSANATION' ? ($cambios['subsanation_deadline'] ?? null) : null,
                'auto_numero' => in_array($destino, ['ARCHIVED', 'INVALIDATED'], true) ? ($d['closure_auto_number'] ?? null) : null,
                'causal' => $destino === 'INVALIDATED' ? ($d['closure_reason'] ?? null) : null,
            ]);

            return ['ok' => "Expediente actualizado a «{$nuevo->name}»."];
        });

        return isset($mensaje['error'])
            ? back()->withErrors(['estatus' => $mensaje['error']])
            : redirect()->route('oac.expedientes.show', $expediente)->with('ok', $mensaje['ok']);
    }

    /** Registra la consignación de recaudos físicos y número de expediente físico. */
    public function recaudosFisicos(Request $request, CaseFile $expediente)
    {
        $d = $request->validate([
            'physical_docs_submitted' => 'nullable|boolean',
            'physical_docs_date' => 'nullable|date|before_or_equal:today',
            'physical_file_number' => 'nullable|string|max:50',
        ], [
            'physical_docs_date.before_or_equal' => 'La fecha de consignación no puede ser futura.',
        ]);

        $consignados = $request->boolean('physical_docs_submitted');
        $user = $request->user();

        DB::transaction(function () use ($d, $expediente, $consignados, $user) {
            $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();
            $e->update([
                'physical_docs_submitted' => $consignados,
                'physical_docs_date' => $consignados ? ($d['physical_docs_date'] ?? now()->toDateString()) : null,
                'physical_file_number' => filled($d['physical_file_number'] ?? null) ? trim($d['physical_file_number']) : $e->physical_file_number,
            ]);

            $this->registrar($e, $user, 'DOCUMENT_CHECK', [
                'consignados' => $consignados ? 'Sí' : 'No',
                'expediente_fisico' => $e->physical_file_number,
                'nota' => $consignados
                    ? 'Se registró la consignación física de los recaudos en sede.'
                    : 'Se marcó pendiente la consignación física de los recaudos.',
            ]);
        });

        return redirect()->route('oac.expedientes.show', $expediente)
            ->with('seccion_activa', 'seccion-expediente')
            ->with('ok', 'Control de recaudos físicos actualizado.');
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

    /** Asigna o reasigna el trámite desde la tabla de funcionarios (área/dirección, cargo, figura y funcionario). */
    public function asignar(Request $request, CaseFile $expediente)
    {
        if ($request->filled('funcionario_id') || ! $request->hasAny(['assigned_unit_id', 'assigned_position_id'])) {
            $d = $request->validate([
                'funcionario_id' => ['required', 'integer', Rule::exists('funcionarios', 'id')->where('activo', true)],
                'assignment_notes' => 'nullable|string|max:2000',
            ], [
                'funcionario_id.required' => 'Seleccione el funcionario responsable desde el directorio.',
                'funcionario_id.exists' => 'El funcionario seleccionado no existe o está inactivo.',
                'assignment_notes.max' => 'Las notas de asignación no pueden superar 2000 caracteres.',
            ]);

            $user = $request->user();
            $func = Funcionario::findOrFail($d['funcionario_id']);

            DB::transaction(function () use ($d, $expediente, $user, $func) {
                $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();

                $e->update([
                    'assigned_funcionario_id' => $func->id,
                    'assigned_area' => $func->area_direccion,
                    'assigned_cargo' => $func->cargo,
                    'assigned_figura' => $func->figura,
                    'assigned_funcionario_nombre' => $func->nombre_completo,
                    'assigned_unit_id' => $func->referral_unit_id ?? $e->assigned_unit_id,
                    'assigned_position_id' => $func->job_position_id ?? $e->assigned_position_id,
                    'assigned_user_id' => $func->staff_user_id,
                    'assigned_at' => now(),
                    'assigned_by' => $user->id,
                    'assignment_notes' => $d['assignment_notes'] ?? null,
                ]);

                $this->registrar($e, $user, 'ASSIGNMENT', [
                    'tipo_accion' => 'Asignación de trámite',
                    'caso' => $e->case_number,
                    'oficina' => $func->area_direccion,
                    'cargo' => $func->cargo,
                    'figura' => $func->figura,
                    'funcionario' => $func->nombre_completo,
                    'nota' => $d['assignment_notes'] ?? null,
                ]);
            });

            return redirect()->route('oac.expedientes.show', $expediente)
                ->with('seccion_activa', 'seccion-valoracion-asignacion')
                ->with('ok', 'Asignación del trámite guardada y registrada en el historial.');
        }

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

            $e->update([
                'assigned_unit_id' => $d['assigned_unit_id'],
                'assigned_position_id' => $d['assigned_position_id'],
                'assigned_user_id' => $d['assigned_user_id'] ?? null,
                'assigned_area' => $nombreOficina,
                'assigned_cargo' => $nombreCargo,
                'assigned_funcionario_nombre' => $nombreFuncionario,
                'assigned_at' => now(),
                'assigned_by' => $user->id,
                'assignment_notes' => $d['assignment_notes'] ?? null,
            ]);

            $this->registrar($e, $user, 'ASSIGNMENT', [
                'tipo_accion' => 'Asignación de trámite',
                'caso' => $e->case_number,
                'oficina' => $nombreOficina,
                'cargo' => $nombreCargo,
                'funcionario' => $nombreFuncionario,
                'nota' => $d['assignment_notes'] ?? null,
            ]);
        });

        return redirect()->route('oac.expedientes.show', $expediente)
            ->with('seccion_activa', 'seccion-valoracion-asignacion')
            ->with('ok', 'Asignación del trámite guardada y registrada en el historial.');
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
