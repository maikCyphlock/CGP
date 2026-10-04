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
            'ciudadano.nro_doc' => 'required|string|max:12',
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
            'ciudadano.profesion' => 'nullable|string|max:80',
            'ciudadano.ocupacion' => 'nullable|string|max:80',
            'ciudadano.correo' => 'required|email|max:150',
            'ciudadano.telf_cel' => 'required|string|max:15',
            'ciudadano.telf_hab' => 'nullable|string|max:15',
            'ciudadano.direccion' => 'required|string|min:10|max:250',
            'ciudadano.parroquia' => 'nullable|string|max:80',
            'ciudadano.municipio' => 'nullable|string|max:80',
            'ciudadano.ciudad' => 'nullable|string|max:80',
            'senalados' => 'array',
            'senalados.*.tipo' => ['required', Rule::in(array_keys(self::TIPOS_SENALADO))],
            'senalados.*.campos' => 'array',
            'senalados.*.campos.*' => 'nullable|string|max:250',
            'ubicacion_senalado' => 'required_with:senalados.0|nullable|string|min:5|max:250',
            'proyecto.nombre' => 'exclude_unless:es_consulta,true|required|string|min:4|max:200',
            'proyecto.fecha' => 'exclude_unless:es_consulta,true|required|date|before_or_equal:today',
            'proyecto.monto' => 'exclude_unless:es_consulta,true|required|numeric|gt:0',
            'proyecto.financiador' => 'exclude_unless:es_consulta,true|required|string|min:3|max:200',
            'proyecto.situr' => 'nullable|string|max:40',
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
            'numeric' => 'El :attribute debe ser un número.',
            'gt' => 'El :attribute debe ser mayor que cero.',
            'accepted' => 'Debe aceptar la declaración jurada.',
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

        $request->validate([
            'evidencias' => 'array',
            'evidencias.*' => 'array',
            'evidencias.*.*' => 'file|max:10240',
        ]);

        $d = $v->validate();
        $c = $d['ciudadano'];
        $esConsulta = $d['tipo_tramite'] === 'denuncia' && ($d['es_consulta'] ?? false);

        $case = DB::transaction(function () use ($d, $c, $esConsulta, $request) {
            // Si el ciudadano ya existe (misma cédula) se actualizan sus datos.
            $citizen = Citizen::firstOrNew([
                'id_doc_type_id' => $this->catalogId('id_document_type', self::TIPOS_DOC[$c['tipo_doc']]),
                'id_doc_number' => $c['nro_doc'],
            ])->fill([
                'first_name' => trim($c['primer_nombre'].' '.($c['segundo_nombre'] ?? '')),
                'last_name' => trim($c['primer_apellido'].' '.($c['segundo_apellido'] ?? '')),
                'sex' => $c['sexo'],
                'birth_date' => $c['fecha_nac'] ?? null,
                'email' => $c['correo'],
                'mobile_phone' => $c['telf_cel'],
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
        ])->setPaper('letter');

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
