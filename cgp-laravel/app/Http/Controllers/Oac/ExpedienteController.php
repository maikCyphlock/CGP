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
    // Código SITUR para validación de proyectos y organizaciones comunales.
    const RE_SITUR = '/^(?:C-(?:MIX|RUR|URB)-(?:19|20)\d{2}-(?:0[1-9]|1[0-2])-\d{4}|CEC-[A-Z]{2,3}-\d{3,4}|(?:0[1-9]|1\d|2[0-5])-(?:0[1-9]|[1-9]\d)-\d{4})$/';

    // Único flujo permitido entre estatus. Lo que no está aquí se rechaza en el servidor.
    const TRANSICIONES = [
        'RECEIVED' => ['IN_REVIEW', 'INVALIDATED'],
        'IN_REVIEW' => ['PROCESSED', 'REFERRED', 'INVALIDATED', 'ARCHIVED'],
        'PROCESSED' => ['REFERRED', 'ARCHIVED'],
        'REFERRED' => ['ARCHIVED'],
    ];

    // Texto del botón/opción para cada estatus destino.
    const ACCIONES = [
        'IN_REVIEW' => 'Protocolizar (pasa a En Revisión)',
        'PROCESSED' => 'Marcar como Procesando',
        'REFERRED' => 'Derivar a otra unidad',
        'INVALIDATED' => 'Invalidar (cierra el expediente)',
        'ARCHIVED' => 'Archivar (cierra el expediente)',
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
                ->whereNull('c.deleted_at')
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
            'papelera' => 'nullable|in:1,true',
        ]);
        $estatus = $this->estatus();
        $esPapelera = ! empty($filtros['papelera']);

        $query = $esPapelera ? CaseFile::onlyTrashed() : CaseFile::query();

        $expedientes = $query->with(['citizen', 'claimType'])
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
            'conteoPapelera' => CaseFile::onlyTrashed()->count(),
            'filtros' => $filtros,
            'esPapelera' => $esPapelera,
            'puedeEscribir' => $request->user()->puede('CASES', 'write'),
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
            'esEliminado' => $expediente->trashed(),
            'opciones' => collect(self::TRANSICIONES[$actual->code] ?? [])
                ->reject(fn ($code) => $code === 'REFERRED' && ! $request->user()->puede('CLASSIFY', 'write'))
                ->mapWithKeys(fn ($code) => [$code => self::ACCIONES[$code]]),
            'puedeEscribir' => $request->user()->puede('CASES', 'write'),
            'puedeClasificar' => $request->user()->puede('CLASSIFY', 'write') && ! $actual->is_terminal && ! $expediente->trashed(),
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

    /** Muestra el formulario para editar los datos de la denuncia (sin modificar expediente ni fecha de ingreso). */
    public function edit(Request $request, CaseFile $expediente)
    {
        abort_unless($request->user()->puede('CASES', 'write'), 403);

        $expediente->load(['citizen', 'claimType', 'respondents.respondentType']);
        $estatus = $this->estatus()->keyBy('id');
        $actual = $estatus[$expediente->status_id];

        $consulta = DB::table('popular_consultation')->where('case_file_id', $expediente->id)->first();
        $tiposDoc = DB::table('id_document_type')->orderBy('id')->get();
        $tiposTramite = DB::table('claim_type')->orderBy('id')->get();
        $tiposSenalado = DB::table('respondent_type')->where('active', true)->orderBy('name')->get();
        $profesiones = DB::table('profesiones')->where('activo', true)->orderBy('nombre')->get();
        $parroquias = DB::table('parroquias')->orderBy('nombre')->get();

        return view('oac.expediente_edit', [
            'e' => $expediente,
            'c' => $expediente->citizen,
            'actual' => $actual,
            'consulta' => $consulta,
            'tiposDoc' => $tiposDoc,
            'tiposTramite' => $tiposTramite,
            'tiposSenalado' => $tiposSenalado,
            'profesiones' => $profesiones,
            'parroquias' => $parroquias,
            'docTipo' => DB::table('id_document_type')->where('id', $expediente->citizen->id_doc_type_id)->value('code'),
        ]);
    }

    /** Actualiza los datos de la denuncia. NUNCA modifica el número de expediente ni la fecha de ingreso. */
    public function update(Request $request, CaseFile $expediente)
    {
        abort_unless($request->user()->puede('CASES', 'write'), 403);

        $d = $request->validate([
            // Datos del ciudadano
            'ciudadano.id_doc_type_id' => 'required|integer|exists:id_document_type,id',
            'ciudadano.id_doc_number' => 'required|string|regex:/^\d{5,10}$/',
            'ciudadano.first_name' => 'required|string|max:80',
            'ciudadano.last_name' => 'required|string|max:80',
            'ciudadano.sex' => 'required|in:M,F',
            'ciudadano.birth_date' => 'nullable|date|before:today',
            'ciudadano.birth_place' => 'nullable|string|max:120',
            'ciudadano.email' => 'required|email|max:150',
            'ciudadano.mobile_phone' => 'nullable|string|max:20',
            'ciudadano.home_phone' => 'nullable|string|max:20',
            'ciudadano.work_phone' => 'nullable|string|max:20',
            'ciudadano.marital_status' => 'nullable|string|max:40',
            'ciudadano.education_level' => 'nullable|string|max:40',
            'ciudadano.profession' => 'nullable|string|max:80',
            'ciudadano.occupation' => 'nullable|string|max:80',
            'ciudadano.address' => 'required|string|min:5|max:250',
            'ciudadano.parish' => 'nullable|string|max:80',
            'ciudadano.municipality' => 'nullable|string|max:80',
            'ciudadano.city' => 'nullable|string|max:80',

            // Datos de la denuncia / trámite
            'tramite.claim_type_id' => 'required|integer|exists:claim_type,id',
            'tramite.narrative' => 'required|string|min:50|max:3500',
            'tramite.incident_location' => 'nullable|string|max:250',
            'tramite.other_instance' => 'required|in:si,no,no_se',
            'tramite.other_instance_name' => 'required_if:tramite.other_instance,si|nullable|string|max:200',

            // Proyecto Consulta Popular
            'consulta.es_consulta' => 'nullable|boolean',
            'consulta.project_name' => 'required_if:consulta.es_consulta,1,true|nullable|string|min:4|max:200',
            'consulta.approval_date' => 'required_if:consulta.es_consulta,1,true|nullable|date|before_or_equal:today',
            'consulta.project_amount' => 'required_if:consulta.es_consulta,1,true|nullable|numeric|gt:0',
            'consulta.funding_entity' => 'required_if:consulta.es_consulta,1,true|nullable|string|min:3|max:200',
            'consulta.situr_code' => ['nullable', 'string', 'regex:'.self::RE_SITUR],

            // Señalados
            'senalados' => 'nullable|array',
            'senalados.*.id' => 'nullable|integer',
            'senalados.*.respondent_type_id' => 'required|integer|exists:respondent_type,id',
            'senalados.*.location' => 'nullable|string|max:250',
            'senalados.*.attributes' => 'nullable|array',
            'senalados.*.attributes.*' => 'nullable|string|max:250',

            // Observación obligatoria para el historial
            'nota_edicion' => 'required|string|min:5|max:250',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'required_if' => 'El campo :attribute es requerido cuando aplica la opción.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'max' => 'El campo :attribute no puede superar :max caracteres.',
            'email' => 'El correo electrónico no es válido.',
            'date' => 'La fecha ingresada no es válida.',
            'before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'before_or_equal' => 'La fecha no puede ser futura.',
            'numeric' => 'El monto debe ser un valor numérico.',
            'gt' => 'El monto debe ser mayor que cero.',
            'regex' => 'El formato del campo :attribute no es válido.',
            'ciudadano.id_doc_number.regex' => 'El número de documento debe tener entre 5 y 10 dígitos numéricos.',
            'consulta.situr_code.regex' => 'El código SITUR no es válido (Ej: C-URB-2014-11-0001 o CEC-ZU-205).',
            'nota_edicion.required' => 'Debe indicar el motivo o resumen de la corrección para el historial.',
            'nota_edicion.min' => 'El motivo de la corrección debe tener al menos 5 caracteres.',
            'nota_edicion.max' => 'El motivo de la corrección no puede superar los 250 caracteres.',
        ]);

        DB::transaction(function () use ($d, $expediente, $request) {
            $e = CaseFile::whereKey($expediente->id)->lockForUpdate()->firstOrFail();
            $c = $d['ciudadano'];
            $t = $d['tramite'];

            // 1. Manejo del ciudadano denunciante
            $citTipoDoc = $c['id_doc_type_id'];
            $citNroDoc = $c['id_doc_number'];

            // Verifica si ya existe otro ciudadano con esa cédula para reutilizarlo o actualizar el actual
            $otroCiudadano = \App\Models\Citizen::where('id_doc_type_id', $citTipoDoc)
                ->where('id_doc_number', $citNroDoc)
                ->where('id', '!=', $e->citizen_id)
                ->first();

            $ciudadanoActual = $otroCiudadano ?: $e->citizen;

            $ciudadanoActual->update([
                'id_doc_type_id' => $citTipoDoc,
                'id_doc_number' => $citNroDoc,
                'first_name' => $c['first_name'],
                'last_name' => $c['last_name'],
                'sex' => $c['sex'],
                'birth_date' => $c['birth_date'] ?? null,
                'email' => $c['email'],
                'mobile_phone' => ($c['mobile_phone'] ?? null) ?: null,
                'address' => $c['address'],
                'parish' => $c['parish'] ?? null,
                'municipality' => ($c['municipality'] ?? null) ?: 'Páez',
                'city' => $c['city'] ?? null,
                'contact_data' => array_filter([
                    'home_phone' => $c['home_phone'] ?? null,
                    'work_phone' => $c['work_phone'] ?? null,
                    'birth_place' => $c['birth_place'] ?? null,
                    'marital_status' => $c['marital_status'] ?? null,
                    'education_level' => $c['education_level'] ?? null,
                    'profession' => $c['profession'] ?? null,
                    'occupation' => $c['occupation'] ?? null,
                ]),
            ]);

            if ($otroCiudadano && $e->citizen_id !== $otroCiudadano->id) {
                $e->citizen_id = $otroCiudadano->id;
            }

            // 2. Actualizar datos de la denuncia (case_file)
            // IMPORTANTE: case_number y created_at NO se modifican jamás.
            $otraInstVal = $t['other_instance'];
            $isOtraInstanciaSi = ($otraInstVal === 'si');
            $isOtraInstanciaNoSe = ($otraInstVal === 'no_se');
            $esConsulta = ! empty($d['consulta']['es_consulta']);

            $e->update([
                'claim_type_id' => $t['claim_type_id'],
                'narrative' => $t['narrative'],
                'incident_location' => $t['incident_location'] ?? null,
                'other_instance' => $isOtraInstanciaSi,
                'other_instance_name' => $isOtraInstanciaSi ? ($t['other_instance_name'] ?? null) : ($isOtraInstanciaNoSe ? 'No sé' : null),
                'is_popular_consultation' => $esConsulta,
            ]);

            // 3. Proyecto Consulta Popular
            if ($esConsulta && ! empty($d['consulta']['project_name'])) {
                DB::table('popular_consultation')->updateOrInsert(
                    ['case_file_id' => $e->id],
                    [
                        'project_name' => $d['consulta']['project_name'],
                        'approval_date' => $d['consulta']['approval_date'],
                        'project_amount' => $d['consulta']['project_amount'],
                        'funding_entity' => $d['consulta']['funding_entity'],
                        'situr_code' => $d['consulta']['situr_code'] ?? null,
                    ]
                );
            } else {
                DB::table('popular_consultation')->where('case_file_id', $e->id)->delete();
            }

            // 4. Señalados
            $senaladosEnviados = $d['senalados'] ?? [];
            $idsConservados = [];
            foreach ($senaladosEnviados as $s) {
                $attrs = array_filter($s['attributes'] ?? [], fn ($v) => $v !== null && $v !== '');
                if (! empty($s['id'])) {
                    $respondent = $e->respondents()->find($s['id']);
                    if ($respondent) {
                        $respondent->update([
                            'respondent_type_id' => $s['respondent_type_id'],
                            'location' => $s['location'] ?? $t['incident_location'] ?? 'Municipio Páez',
                            'attributes' => $attrs,
                        ]);
                        $idsConservados[] = $respondent->id;
                        continue;
                    }
                }
                $nuevo = $e->respondents()->create([
                    'respondent_type_id' => $s['respondent_type_id'],
                    'location' => $s['location'] ?? $t['incident_location'] ?? 'Municipio Páez',
                    'attributes' => $attrs,
                ]);
                $idsConservados[] = $nuevo->id;
            }
            $e->respondents()->whereNotIn('id', $idsConservados)->delete();

            // 5. Registrar actuación en el historial
            $this->registrar($e, $request->user(), 'NOTE', [
                'tipo_accion' => 'EDITION',
                'nota' => $d['nota_edicion'],
                'usuario' => $request->user()->nombre,
                'fecha' => now()->toIso8601String(),
            ]);
        });

        return redirect()->route('oac.expedientes.show', $expediente)->with('ok', "Los datos del expediente «{$expediente->case_number}» fueron actualizados exitosamente.");
    }

    /** Realiza el eliminado lógico de un expediente. */
    public function destroy(Request $request, CaseFile $expediente)
    {
        abort_unless($request->user()->puede('CASES', 'write'), 403);

        $motivo = $request->input('motivo') ?: 'Eliminado lógico realizado desde el panel OAC';

        DB::transaction(function () use ($expediente, $request, $motivo) {
            $this->registrar($expediente, $request->user(), 'NOTE', [
                'tipo_accion' => 'SOFT_DELETED',
                'motivo' => $motivo,
                'nota' => "Expediente eliminado lógicamente. Motivo: {$motivo}",
                'fecha' => now()->toIso8601String(),
            ]);
            $expediente->delete(); // Soft delete: asigna deleted_at
        });

        return redirect()->route('oac.expedientes.index')->with('ok', "El expediente «{$expediente->case_number}» ha sido eliminado (eliminado lógico).");
    }

    /** Restaura un expediente previamente eliminado lógicamente. */
    public function restore(Request $request, string $id)
    {
        abort_unless($request->user()->puede('CASES', 'write'), 403);

        $expediente = CaseFile::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($expediente, $request) {
            $expediente->restore();
            $this->registrar($expediente, $request->user(), 'NOTE', [
                'tipo_accion' => 'RESTORED',
                'nota' => 'Expediente restaurado a las listas activas.',
                'fecha' => now()->toIso8601String(),
            ]);
        });

        return redirect()->route('oac.expedientes.show', $expediente)->with('ok', "El expediente «{$expediente->case_number}» ha sido restaurado exitosamente.");
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
