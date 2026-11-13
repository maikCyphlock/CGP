<?php

namespace App\Http\Controllers;

use App\Models\CaseFile;
use App\Models\Citizen;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DenunciaController extends Controller
{
    // Código SITUR (Ministerio de las Comunas): comuna, circuito comunal o formato legado. Igual que RE_SITUR en wizard.js.
    const RE_SITUR = '/^(?:C-(?:MIX|RUR|URB)-(?:19|20)\d{2}-(?:0[1-9]|1[0-2])-\d{4}|CEC-[A-Z]{2,3}-\d{3,4}|(?:0[1-9]|1\d|2[0-5])-(?:0[1-9]|[1-9]\d)-\d{4})$/';

    // Valores del formulario → códigos de catálogo en la base de datos.
    const TIPOS_TRAMITE = [
        'denuncia' => 'COMPLAINT',
        'queja' => 'GRIEVANCE',
        'reclamo' => 'CLAIM',
        'peticion' => 'PETITION',
        'sugerencia' => 'SUGGESTION',
    ];

    const TIPOS_DOC = ['V' => 'V', 'E' => 'E', 'P' => 'PASSPORT'];

    const TIPOS_SENALADO = [
        'persona_natural' => 'NATURAL_PERSON',
        'persona_juridica' => 'LEGAL_ENTITY',
        'organo_ente' => 'GOV_AGENCY',
        'comuna' => 'COMMUNE',
        'consejo_comunal' => 'COMMUNAL_COUNCIL',
        'juez_paz' => 'JUSTICE_OF_PEACE',
        'otro' => 'OTHER',
    ];

    const EVIDENCIAS = [
        'ci_testigo' => 'WITNESS_ID',
        'ci_denunciante' => 'CLAIMANT_ID',
        'carta_exposicion' => 'COVER_LETTER',
        'fotografias' => 'PHOTOS',
        'video' => 'VIDEO',
        'grabacion_voz' => 'AUDIO',
        'testimonio_escrito' => 'WRITTEN_TESTIMONY',
        'otros_docs' => 'OTHER',
    ];

    public function create()
    {
        return view('denuncias');
    }

    public function store(Request $request)
    {
        $datos = json_decode($request->input('datos', '{}'), true) ?: [];

        $v = Validator::make($datos, [
            'tipo_tramite' => ['required', Rule::in(array_keys(self::TIPOS_TRAMITE))],
            'es_consulta' => 'boolean',
            'ciudadano.tipo_doc' => ['required', Rule::in(array_keys(self::TIPOS_DOC))],
            'ciudadano.nro_doc' => ['required', 'string', 'regex:/^\d{5,10}$/'],
            'ciudadano.primer_nombre' => 'required|string|max:40',
            'ciudadano.segundo_nombre' => 'nullable|string|max:39',
            'ciudadano.primer_apellido' => 'required|string|max:40',
            'ciudadano.segundo_apellido' => 'nullable|string|max:39',
            'ciudadano.sexo' => 'required|in:M,F',
            'ciudadano.fecha_nac' => 'nullable|date|before:today',
            'ciudadano.lugar_nac' => 'nullable|string|max:120',
            'ciudadano.telf_trab' => 'nullable|string|max:15',
            'ciudadano.estado_civil' => 'nullable|string|max:40',
            'ciudadano.nivel_educativo' => 'nullable|string|max:40',
            'ciudadano.profesion' => ['nullable', 'string', 'max:80', Rule::exists('profesiones', 'nombre')->where('activo', true)],
            'ciudadano.ocupacion' => 'nullable|string|max:80',
            'ciudadano.correo' => 'required|email|max:150',
            'ciudadano.telf_cel' => 'nullable|string|max:15',
            'ciudadano.telf_hab' => 'nullable|string|max:15',
            'ciudadano.direccion' => 'required|string|min:10|max:250',
            'ciudadano.parroquia' => 'nullable|string|max:80',
            'ciudadano.municipio' => 'nullable|string|max:80',
            'ciudadano.ciudad' => 'nullable|string|max:80',
            'senalados' => 'required_if:tipo_tramite,denuncia,queja|array',
            'senalados.*.tipo' => ['required', Rule::in(array_keys(self::TIPOS_SENALADO))],
            'senalados.*.campos' => 'array',
            'senalados.*.campos.*' => 'nullable|string|max:250',
            'senalados.*.campos.cm-situr' => ['nullable', 'regex:'.self::RE_SITUR],
            'senalados.*.campos.cc-situr' => ['nullable', 'regex:'.self::RE_SITUR],
            'ubicacion_senalado' => 'required_with:senalados.0|nullable|string|min:5|max:250',
            'proyecto.nombre' => 'exclude_unless:es_consulta,true|required|string|min:4|max:200',
            'proyecto.fecha' => [
                'exclude_unless:es_consulta,true',
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:'.now()->subYears(5)->format('Y-m-d'),
            ],
            'proyecto.monto' => 'exclude_unless:es_consulta,true|required|numeric|gt:0',
            'proyecto.financiador' => 'exclude_unless:es_consulta,true|required|string|min:3|max:200',
            'proyecto.situr' => ['nullable', 'string', 'regex:'.self::RE_SITUR],
            'narracion' => 'required|string|min:50|max:3500',
            'otra_instancia' => 'boolean',
            'cual_instancia' => 'required_if:otra_instancia,true|nullable|string|max:200',
            'acepta_declaracion' => 'accepted',
        ], [
            'required' => 'Falta el campo :attribute.',
            'required_if' => 'Falta el campo :attribute.',
            'required_with' => 'Falta el campo :attribute.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'max' => 'El campo :attribute no puede superar :max caracteres.',
            'email' => 'El :attribute no es válido.',
            'in' => 'El valor de :attribute no es válido.',
            'date' => 'La fecha de :attribute no es válida.',
            'before' => 'La fecha de :attribute debe ser anterior a hoy.',
            'before_or_equal' => 'La fecha de :attribute no puede ser futura.',
            'after_or_equal' => 'La fecha de :attribute no puede exceder los 5 años de antigüedad.',
            'numeric' => 'El :attribute debe ser un número.',
            'gt' => 'El :attribute debe ser mayor que cero.',
            'regex' => 'El código SITUR no es válido. Ej.: C-URB-2014-11-0001 o CEC-ZU-205.',
            'accepted' => 'Debe aceptar la declaración jurada.',
            'ciudadano.nro_doc.regex' => 'El número de documento debe tener entre 5 y 10 dígitos.',
            'senalados.required_if' => 'Debe agregar al menos un señalado.',
        ], [
            'tipo_tramite' => 'tipo de trámite',
            'ciudadano.tipo_doc' => 'tipo de documento',
            'ciudadano.fecha_nac' => 'nacimiento',
            'proyecto.nombre' => 'nombre del proyecto',
            'proyecto.fecha' => 'aprobación del proyecto',
            'proyecto.monto' => 'monto del proyecto',
            'proyecto.financiador' => 'ente financiador',
            'ciudadano.nro_doc' => 'número de documento',
            'ciudadano.primer_nombre' => 'primer nombre',
            'ciudadano.primer_apellido' => 'primer apellido',
            'ciudadano.sexo' => 'sexo',
            'ciudadano.correo' => 'correo electrónico',
            'ciudadano.telf_cel' => 'teléfono celular',
            'ciudadano.direccion' => 'dirección de habitación',
            'ubicacion_senalado' => 'ubicación del señalado',
            'narracion' => 'narración de los hechos',
            'cual_instancia' => 'instancia',
        ]);

        $v->after(function ($validator) use ($datos) {
            $citNroDoc = preg_replace('/\D/', '', $datos['ciudadano']['nro_doc'] ?? '');
            $citTipoDoc = $datos['ciudadano']['tipo_doc'] ?? '';
            if ($citNroDoc !== '') {
                foreach ($datos['senalados'] ?? [] as $index => $s) {
                    $c = $s['campos'] ?? [];
                    $pnNro = preg_replace('/\D/', '', $c['pn-nro-doc'] ?? '');
                    $pnTipo = $c['pn-tipo-doc'] ?? '';
                    if ($pnNro !== '' && $pnNro === $citNroDoc && (empty($pnTipo) || $pnTipo === $citTipoDoc)) {
                        $validator->errors()->add("senalados.{$index}.campos.pn-nro-doc", 'La persona señalada no puede tener la misma cédula que el denunciante.');
                    }
                    $jpNro = preg_replace('/\D/', '', $c['jp-nro-doc'] ?? '');
                    $jpTipo = $c['jp-tipo-doc'] ?? '';
                    if ($jpNro !== '' && $jpNro === $citNroDoc && (empty($jpTipo) || $jpTipo === $citTipoDoc)) {
                        $validator->errors()->add("senalados.{$index}.campos.jp-nro-doc", 'El juez de paz señalado no puede tener la misma cédula que el denunciante.');
                    }
                    $otDoc = trim($c['ot-documento'] ?? '');
                    $otNro = preg_replace('/\D/', '', $otDoc);
                    if ($otNro !== '' && $otNro === $citNroDoc && !preg_match('/^[JjGgCc]/', $otDoc)) {
                        $validator->errors()->add("senalados.{$index}.campos.ot-documento", 'El documento del señalado no puede coincidir con la cédula del denunciante.');
                    }
                }
            }

            // Validación entre señalados: no puede existir el mismo RIF, código SITUR, número de documento
            $senalados = $datos['senalados'] ?? [];
            $count = count($senalados);
            for ($i = 0; $i < $count; $i++) {
                $cA = $senalados[$i]['campos'] ?? [];

                // 1. RIF A
                $rifFieldA = null;
                $rifA = '';
                if (!empty($cA['pj-rif'])) {
                    $rifFieldA = 'pj-rif';
                    $rifA = $cA['pj-rif'];
                } elseif (!empty($cA['oe-rif'])) {
                    $rifFieldA = 'oe-rif';
                    $rifA = $cA['oe-rif'];
                } elseif (!empty($cA['cc-rif'])) {
                    $rifFieldA = 'cc-rif';
                    $rifA = $cA['cc-rif'];
                } elseif (!empty($cA['ot-documento']) && preg_match('/^[JGVECjgvec]-?\d{8,9}-?\d?$/', trim($cA['ot-documento']))) {
                    $rifFieldA = 'ot-documento';
                    $rifA = trim($cA['ot-documento']);
                }
                $normRifA = $rifA !== '' ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rifA)) : '';

                // 2. SITUR A
                $siturFieldA = null;
                $siturA = '';
                if (!empty($cA['cm-situr'])) {
                    $siturFieldA = 'cm-situr';
                    $siturA = trim($cA['cm-situr']);
                } elseif (!empty($cA['cc-situr'])) {
                    $siturFieldA = 'cc-situr';
                    $siturA = trim($cA['cc-situr']);
                }
                $normSiturA = $siturA !== '' ? strtoupper(trim($siturA)) : '';

                // 3. Documento A
                $docFieldA = null;
                $docA = '';
                $tipoDocA = '';
                if (!empty($cA['pn-nro-doc'])) {
                    $docFieldA = 'pn-nro-doc';
                    $docA = $cA['pn-nro-doc'];
                    $tipoDocA = trim($cA['pn-tipo-doc'] ?? '');
                } elseif (!empty($cA['jp-nro-doc'])) {
                    $docFieldA = 'jp-nro-doc';
                    $docA = $cA['jp-nro-doc'];
                    $tipoDocA = trim($cA['jp-tipo-doc'] ?? '');
                } elseif (!empty($cA['ot-documento']) && !preg_match('/^[JjGgCc]/', trim($cA['ot-documento']))) {
                    $docFieldA = 'ot-documento';
                    $docA = trim($cA['ot-documento']);
                    $tipoDocA = '';
                }
                $normDocA = preg_replace('/\D/', '', $docA);

                for ($j = $i + 1; $j < $count; $j++) {
                    $cB = $senalados[$j]['campos'] ?? [];

                    // RIF B
                    $rifFieldB = null;
                    $rifB = '';
                    if (!empty($cB['pj-rif'])) {
                        $rifFieldB = 'pj-rif';
                        $rifB = $cB['pj-rif'];
                    } elseif (!empty($cB['oe-rif'])) {
                        $rifFieldB = 'oe-rif';
                        $rifB = $cB['oe-rif'];
                    } elseif (!empty($cB['cc-rif'])) {
                        $rifFieldB = 'cc-rif';
                        $rifB = $cB['cc-rif'];
                    } elseif (!empty($cB['ot-documento']) && preg_match('/^[JGVECjgvec]-?\d{8,9}-?\d?$/', trim($cB['ot-documento']))) {
                        $rifFieldB = 'ot-documento';
                        $rifB = trim($cB['ot-documento']);
                    }
                    $normRifB = $rifB !== '' ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rifB)) : '';

                    if ($normRifA !== '' && $normRifB !== '' && $normRifA === $normRifB) {
                        $validator->errors()->add("senalados.{$j}.campos.{$rifFieldB}", "No puede existir el mismo R.I.F. ({$rifB}) entre señalados.");
                    }

                    // SITUR B
                    $siturFieldB = null;
                    $siturB = '';
                    if (!empty($cB['cm-situr'])) {
                        $siturFieldB = 'cm-situr';
                        $siturB = trim($cB['cm-situr']);
                    } elseif (!empty($cB['cc-situr'])) {
                        $siturFieldB = 'cc-situr';
                        $siturB = trim($cB['cc-situr']);
                    }
                    $normSiturB = $siturB !== '' ? strtoupper(trim($siturB)) : '';

                    if ($normSiturA !== '' && $normSiturB !== '' && $normSiturA === $normSiturB) {
                        $validator->errors()->add("senalados.{$j}.campos.{$siturFieldB}", "No puede existir el mismo código SITUR ({$siturB}) entre señalados.");
                    }

                    // Documento B
                    $docFieldB = null;
                    $docB = '';
                    $tipoDocB = '';
                    if (!empty($cB['pn-nro-doc'])) {
                        $docFieldB = 'pn-nro-doc';
                        $docB = $cB['pn-nro-doc'];
                        $tipoDocB = trim($cB['pn-tipo-doc'] ?? '');
                    } elseif (!empty($cB['jp-nro-doc'])) {
                        $docFieldB = 'jp-nro-doc';
                        $docB = $cB['jp-nro-doc'];
                        $tipoDocB = trim($cB['jp-tipo-doc'] ?? '');
                    } elseif (!empty($cB['ot-documento']) && !preg_match('/^[JjGgCc]/', trim($cB['ot-documento']))) {
                        $docFieldB = 'ot-documento';
                        $docB = trim($cB['ot-documento']);
                        $tipoDocB = '';
                    }
                    $normDocB = preg_replace('/\D/', '', $docB);

                    if ($normDocA !== '' && $normDocB !== '' && strlen($normDocA) >= 5 && $normDocA === $normDocB && (empty($tipoDocA) || empty($tipoDocB) || $tipoDocA === $tipoDocB)) {
                        $validator->errors()->add("senalados.{$j}.campos.{$docFieldB}", "No puede existir el mismo número de documento ({$docB}) entre señalados.");
                    }
                }
            }
        });

        // Igual que en wizard.js: C.I. y carta son obligatorias salvo en la sugerencia.
        // Va en $v para que salga junto con los demás errores de datos.
        $v->after(function ($validator) use ($datos, $request) {
            if (($datos['tipo_tramite'] ?? '') === 'sugerencia') {
                return;
            }
            foreach (['ci_denunciante' => 'Falta la copia de la cédula de identidad.', 'carta_exposicion' => 'Falta la carta de exposición de motivos.'] as $doc => $msg) {
                if (! $request->file("evidencias.$doc")) {
                    $validator->errors()->add("evidencias.$doc", $msg);
                }
            }
        });

        $request->validate([
            'evidencias' => 'array',
            'evidencias.*' => 'array',
            // Formatos que ofrece el formulario; `mimes` revisa el contenido, no solo la extensión.
            'evidencias.*.*' => 'file|max:10240|mimes:pdf,png,jpg,jpeg,doc,docx,mp4,avi,mov,qt,mkv,webm,mp3,mpga,wav,ogg,oga,m4a,aac',
        ], [
            'evidencias.*.*.uploaded' => 'El archivo no se pudo cargar: el máximo es de 10 MB.',
            'evidencias.*.*.max' => 'El archivo supera el máximo de 10 MB.',
            'evidencias.*.*.mimes' => 'Formato no permitido. Se admiten PDF, Word, imagen, video o audio.',
        ]);

        $d = $v->validate();
        $c = $d['ciudadano'];
        $esConsulta = $d['tipo_tramite'] === 'denuncia' && ($d['es_consulta'] ?? false);

        $case = DB::transaction(function () use ($d, $c, $esConsulta, $request) {
            $docTypeId = $this->catalogId('id_document_type', self::TIPOS_DOC[$c['tipo_doc']]);
            $claimTypeId = $this->catalogId('claim_type', self::TIPOS_TRAMITE[$d['tipo_tramite']]);

            // Verificación de duplicado reciente (Anti-doble clic / Spam / Idempotencia)
            $existingCitizen = Citizen::where('id_doc_type_id', $docTypeId)
                ->where('id_doc_number', $c['nro_doc'])
                ->lockForUpdate()
                ->first();

            if ($existingCitizen) {
                $duplicado = CaseFile::where('citizen_id', $existingCitizen->id)
                    ->where('claim_type_id', $claimTypeId)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->whereRaw('TRIM(narrative) = ?', [trim($d['narracion'])])
                    ->first();

                if ($duplicado) {
                    abort(response()->json([
                        'message' => 'Ya existe un trámite idéntico registrado recientemente con el expediente N° ' . $duplicado->case_number . '. Si se trató de un error de envío involuntario, no se preocupe: su solicitud original ya fue recibida exitosamente.',
                    ], 422));
                }
            }

            // Si el ciudadano ya existe (misma cédula) se actualizan sus datos.
            $citizen = ($existingCitizen ?? new Citizen([
                'id_doc_type_id' => $docTypeId,
                'id_doc_number' => $c['nro_doc'],
            ]))->fill([
                'first_name' => trim($c['primer_nombre'].' '.($c['segundo_nombre'] ?? '')),
                'last_name' => trim($c['primer_apellido'].' '.($c['segundo_apellido'] ?? '')),
                'sex' => $c['sexo'],
                'birth_date' => $c['fecha_nac'] ?? null,
                'email' => $c['correo'],
                'mobile_phone' => ($c['telf_cel'] ?? null) ?: null,
                'address' => $c['direccion'],
                'parish' => $c['parroquia'] ?? null,
                'municipality' => ($c['municipio'] ?? null) ?: 'Páez',
                'city' => $c['ciudad'] ?? null,
                'contact_data' => array_filter([
                    'home_phone' => $c['telf_hab'] ?? null,
                    'work_phone' => $c['telf_trab'] ?? null,
                    'birth_place' => $c['lugar_nac'] ?? null,
                    'marital_status' => $c['estado_civil'] ?? null,
                    'education_level' => $c['nivel_educativo'] ?? null,
                    'profession' => $c['profesion'] ?? null,
                    'occupation' => $c['ocupacion'] ?? null,
                ]),
            ]);
            $citizen->id ??= (string) Str::uuid();
            $citizen->save();

            $case = CaseFile::create([
                'id' => (string) Str::uuid(),
                'claim_type_id' => $this->catalogId('claim_type', self::TIPOS_TRAMITE[$d['tipo_tramite']]),
                'status_id' => $this->catalogId('case_status', 'RECEIVED'),
                'citizen_id' => $citizen->id,
                'narrative' => $d['narracion'],
                'incident_location' => $d['ubicacion_senalado'] ?? null,
                'other_instance' => $d['otra_instancia'] ?? false,
                'other_instance_name' => ($d['otra_instancia'] ?? false) ? $d['cual_instancia'] : null,
                'is_popular_consultation' => $esConsulta,
                'sworn_declaration' => true,
                'declaration_date' => now(),
            ]);

            foreach ($d['senalados'] ?? [] as $s) {
                $case->respondents()->create([
                    'respondent_type_id' => $this->catalogId('respondent_type', self::TIPOS_SENALADO[$s['tipo']]),
                    'location' => $d['ubicacion_senalado'],
                    'attributes' => $s['campos'] ?? [],
                ]);
            }

            if ($esConsulta) {
                DB::table('popular_consultation')->insert([
                    'case_file_id' => $case->id,
                    'project_name' => $d['proyecto']['nombre'],
                    'approval_date' => $d['proyecto']['fecha'],
                    'project_amount' => $d['proyecto']['monto'],
                    'funding_entity' => $d['proyecto']['financiador'],
                    'situr_code' => $d['proyecto']['situr'] ?? null,
                ]);
            }

            foreach ($request->file('evidencias', []) as $tipo => $archivos) {
                if (! isset(self::EVIDENCIAS[$tipo])) {
                    continue;
                }
                DB::table('case_physical_doc')->insert([
                    'case_file_id' => $case->id,
                    'physical_doc_type_id' => $this->catalogId('physical_doc_type', self::EVIDENCIAS[$tipo]),
                ]);
                foreach ($archivos as $archivo) {
                    DB::table('evidence_file')->insert([
                        'case_file_id' => $case->id,
                        'original_name' => $archivo->getClientOriginalName(),
                        'mime_type' => $archivo->getMimeType(),
                        'size_bytes' => $archivo->getSize(),
                        'storage_url' => $archivo->store("evidencias/{$case->id}"),
                    ]);
                }
            }

            return $case->refresh(); // trae case_number y tracking_code generados por la BD
        });

        return response()->json([
            'case_number' => $case->case_number,
            'tracking_code' => $case->tracking_code,
            'planilla_url' => route('denuncias.planilla', $case->tracking_code),
        ], 201);
    }

    /** Consulta pública del estatus y observaciones del trámite: tipo, fecha, estatus y observaciones (sin datos sensibles del ciudadano). */
    public function estado(Request $request)
    {
        $codigo = strtoupper(trim((string) $request->query('codigo')));

        $case = DB::table('case_file as c')
            ->join('claim_type as t', 't.id', '=', 'c.claim_type_id')
            ->join('case_status as s', 's.id', '=', 'c.status_id')
            ->where(fn ($q) => $q->where('c.case_number', $codigo)->orWhere('c.tracking_code', strtolower($codigo)))
            ->first([
                'c.id',
                'c.case_number',
                't.name as tipo',
                't.code as tipo_codigo',
                'c.created_at',
                's.name as estatus',
                's.code as estatus_codigo',
                's.is_terminal',
                'c.analyst_notes',
            ]);

        abort_unless($case, 404, 'No existe un trámite con ese código.');

        $acciones = DB::table('case_action')
            ->where('case_file_id', $case->id)
            ->orderByDesc('performed_at')
            ->orderByDesc('id')
            ->get(['action_type', 'payload', 'performed_at']);

        $observaciones = [];
        foreach ($acciones as $a) {
            $payload = is_array($a->payload) ? $a->payload : json_decode((string) $a->payload, true);
            $texto = trim((string) ($payload['nota'] ?? ''));
            if ($texto !== '') {
                $observaciones[] = [
                    'fecha' => date('d/m/Y', strtotime($a->performed_at)),
                    'texto' => $texto,
                ];
            }
        }

        if (empty($observaciones) && ! empty($case->analyst_notes)) {
            $observaciones[] = [
                'fecha' => date('d/m/Y', strtotime($case->created_at)),
                'texto' => trim($case->analyst_notes),
            ];
        }

        $ultimaObservacion = $observaciones[0]['texto'] ?? null;

        return response()->json([
            'case_number' => $case->case_number,
            'tipo' => $case->tipo,
            'tipo_codigo' => $case->tipo_codigo,
            'fecha' => date('d/m/Y', strtotime($case->created_at)),
            'estatus' => $case->estatus,
            'estatus_codigo' => $case->estatus_codigo,
            'cerrado' => (bool) $case->is_terminal,
            'observacion' => $ultimaObservacion,
            'observaciones' => $observaciones,
        ]);
    }

    public function planilla(string $trackingCode)
    {
        $case = CaseFile::with(['citizen', 'claimType', 'respondents.respondentType'])
            ->where('tracking_code', $trackingCode)
            ->firstOrFail();

        $pdf = Pdf::loadView('pdf.planilla', [
            'case' => $case,
            'c' => $case->citizen,
            'docTipo' => DB::table('id_document_type')->where('id', $case->citizen->id_doc_type_id)->value('code'),
            'consulta' => DB::table('popular_consultation')->where('case_file_id', $case->id)->first(),
            'evidencias' => DB::table('case_physical_doc')
                ->join('physical_doc_type', 'physical_doc_type.id', '=', 'case_physical_doc.physical_doc_type_id')
                ->where('case_file_id', $case->id)
                ->pluck('physical_doc_type.code')
                ->all(),
            'senalados' => $case->respondents->map(fn ($r) => $this->filaSenalado($r->attributes ?? []))->all(),
            'tiposSenalado' => $case->respondents->pluck('respondentType.code')->all(),
        ])->setPaper('letter')->setOption('dpi', 120); // escala 96→120: el diseño (≈32cm) cabe en una hoja carta

        return $pdf->stream("planilla-{$case->case_number}.pdf");
    }

    /** Reparte los campos libres del señalado en las columnas 23–27 de la planilla. */
    private function filaSenalado(array $a): array
    {
        $doc = fn ($tipo, $nro) => isset($a[$nro]) ? trim(($a[$tipo] ?? '').'-'.$a[$nro], '-') : null;

        return [
            'cedula' => $doc('pn-tipo-doc', 'pn-nro-doc') ?? $doc('jp-tipo-doc', 'jp-nro-doc') ?? $a['ot-documento'] ?? '',
            'nombre' => $a['pn-nombres'] ?? $a['jp-nombres'] ?? $a['pj-razon'] ?? $a['oe-nombre'] ?? $a['ot-nombre'] ?? '',
            'comuna' => $a['cm-nombre'] ?? $a['cc-nombre'] ?? '',
            'situr' => $a['cm-situr'] ?? $a['cc-situr'] ?? '',
            'rif' => $a['pj-rif'] ?? $a['oe-rif'] ?? $a['cc-rif'] ?? '',
            'otro' => $a['ot-tipo'] ?? null,
        ];
    }

    private function catalogId(string $table, string $code): int
    {
        return DB::table($table)->where('code', $code)->value('id')
            ?? abort(500, "Falta el código {$code} en el catálogo {$table}");
    }
}
