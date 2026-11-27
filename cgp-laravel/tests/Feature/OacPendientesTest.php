<?php

namespace Tests\Feature;

use App\Models\CaseFile;
use App\Models\Funcionario;
use App\Models\StaffUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Corre contra Postgres (cgp_test): el esquema usa triggers, UUID y JSONB.
class OacPendientesTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(?array $permisos = null): StaffUser
    {
        $u = StaffUser::create([
            'job_position_id' => DB::table('job_position')->value('id'),
            'id_doc_type_id' => DB::table('id_document_type')->where('code', 'V')->value('id'),
            'id_doc_number' => (string) random_int(1000000, 99999999),
            'first_name' => 'Juan',
            'last_name' => 'Romero',
            'email' => 'juan'.random_int(1, 99999).'@cgp.test',
            'password_hash' => 'clave-segura',
        ]);

        foreach (DB::table('app_module')->get() as $m) {
            $nivel = $permisos === null ? 'delete' : ($permisos[$m->code] ?? null);
            if ($nivel === null) {
                continue;
            }
            DB::table('staff_privilege')->insert([
                'user_id' => $u->id,
                'module_id' => $m->id,
                'can_read' => true,
                'can_write' => in_array($nivel, ['write', 'delete'], true),
                'can_delete' => $nivel === 'delete',
            ]);
        }

        return $u;
    }

    private function crearExpediente(array $overrides = []): CaseFile
    {
        Storage::fake();
        $pdf = function (string $nombre) {
            $ruta = tempnam(sys_get_temp_dir(), 'ev');
            file_put_contents($ruta, "%PDF-1.4\n%prueba\n");

            return new \Illuminate\Http\UploadedFile($ruta, $nombre, null, null, true);
        };

        $payload = array_replace_recursive([
            'tipo_tramite' => 'denuncia',
            'es_consulta' => false,
            'ciudadano' => [
                'tipo_doc' => 'V',
                'nro_doc' => '18444555',
                'primer_nombre' => 'María',
                'primer_apellido' => 'Gómez',
                'sexo' => 'F',
                'correo' => 'maria@gmail.com',
                'telf_cel' => '0414-5551122',
                'direccion' => 'Av. Libertador, Acarigua, Portuguesa',
            ],
            'cit_lat' => '9.5558000',
            'cit_lng' => '-69.1994000',
            'senalados' => [
                ['tipo' => 'persona_natural', 'campos' => ['pn-nombres' => 'Pedro Pérez']],
            ],
            'ubicacion_senalado' => 'Alcaldía del Municipio Páez, Acarigua',
            'ubicacion_lat' => '9.5580000',
            'ubicacion_lng' => '-69.2010000',
            'narracion' => str_repeat('Descripción detallada de los hechos denunciados. ', 3),
            'otra_instancia' => 'no',
            'acepta_declaracion' => true,
        ], $overrides);

        $resp = $this->post('/denuncias', [
            'datos' => json_encode($payload),
            'evidencias' => [
                'ci_denunciante' => [$pdf('cedula.pdf')],
                'carta_exposicion' => [$pdf('carta.pdf')],
            ],
        ], ['Accept' => 'application/json'])->assertCreated();

        return CaseFile::where('case_number', $resp->json('case_number'))->firstOrFail();
    }

    public function test_mensajes_estado_personalizables_y_consulta_publica(): void
    {
        $e = $this->crearExpediente();
        $soloLectura = $this->usuario(['CASES' => 'read']);
        $editor = $this->usuario(['CASES' => 'write', 'CATALOGS' => 'write']);

        $this->actingAs($soloLectura)
            ->get(route('oac.mensajes_estado.index'))
            ->assertForbidden();

        $this->actingAs($editor);
        $this->get(route('oac.mensajes_estado.index'))
            ->assertOk()
            ->assertSee('En tramitación / valoración')
            ->assertSee('En subsanación')
            ->assertSee('Remitido / derivado')
            ->assertSee('Improcedente / desestimado y archivado')
            ->assertSee('Cerrado / archivado');

        $this->post(route('oac.mensajes_estado.update', 'RECEIVED'), [
            'titulo' => 'Aviso personalizado DAC',
            'mensaje' => 'Su solicitud fue recibida correctamente en nuestra oficina.',
            'activo' => 1,
        ])->assertRedirect(route('oac.mensajes_estado.index'));

        $this->getJson('/denuncias/estado?codigo='.$e->case_number)
            ->assertOk()
            ->assertJsonPath('mensaje_estado.titulo', 'Aviso personalizado DAC')
            ->assertJsonPath('mensaje_estado.texto', 'Su solicitud fue recibida correctamente en nuestra oficina.');

        // Si se desactiva el mensaje para ese estado, no se muestra al ciudadano
        $this->post(route('oac.mensajes_estado.update', 'RECEIVED'), [
            'titulo' => 'Aviso oculto',
            'mensaje' => 'Mensaje desactivado temporalmente.',
            'activo' => 0,
        ])->assertRedirect();

        $this->getJson('/denuncias/estado?codigo='.$e->case_number)
            ->assertOk()
            ->assertJsonPath('mensaje_estado', null);
    }

    public function test_directorio_de_funcionarios_y_asignacion_de_expediente(): void
    {
        $e = $this->crearExpediente();
        $usuario = $this->usuario(['CASES' => 'write', 'CLASSIFY' => 'write', 'CATALOGS' => 'write']);
        $this->actingAs($usuario);

        $this->get(route('oac.funcionarios.index'))->assertOk();

        $this->post(route('oac.funcionarios.store'), [
            'area_direccion' => 'Dirección de Atención al Ciudadano (DAC)',
            'cargo' => 'Director de Tecnología / Enlace DAC',
            'figura' => 'Director(a) de la Dirección de Atención al Ciudadano (DAC)',
            'nombre_completo' => 'Ing. Juan Romero',
            'cedula' => 'V-14123456',
        ])->assertRedirect(route('oac.funcionarios.index'));

        $func = Funcionario::where('nombre_completo', 'Ing. Juan Romero')->firstOrFail();

        $this->post(route('oac.expedientes.asignar', $e), [
            'funcionario_id' => $func->id,
            'assignment_notes' => 'Evaluar admisibilidad conforme al Manual DAC.',
        ])->assertRedirect(route('oac.expedientes.show', $e));

        $e->refresh();
        $this->assertSame($func->id, $e->assigned_funcionario_id);
        $this->assertSame('Dirección de Atención al Ciudadano (DAC)', $e->assigned_area);
        $this->assertSame('Director de Tecnología / Enlace DAC', $e->assigned_cargo);
        $this->assertSame('Director(a) de la Dirección de Atención al Ciudadano (DAC)', $e->assigned_figura);
        $this->assertSame('Ing. Juan Romero', $e->assigned_funcionario_nombre);

        $this->get(route('oac.expedientes.show', $e))
            ->assertOk()
            ->assertSee('Ing. Juan Romero')
            ->assertSee('Director(a) de la Dirección de Atención al Ciudadano (DAC)');
    }

    public function test_remision_y_notificacion_formal_en_base_de_datos(): void
    {
        $e = $this->crearExpediente();
        $usuario = $this->usuario(['CASES' => 'write', 'CLASSIFY' => 'write']);
        $this->actingAs($usuario);

        // Órgano o ente externo exige el nombre del ente competente
        $this->post(route('oac.expedientes.derivar', $e), [
            'deriv_scope' => 'Órgano o ente con competencia',
            'deriv_dest_type' => 'Órgano o ente con competencia',
            'nota' => 'No corresponde a control fiscal municipal.',
        ])->assertSessionHasErrors('deriv_external_entity');

        // Registro completo de remisión en base de datos
        $this->post(route('oac.expedientes.derivar', $e), [
            'deriv_scope' => 'Órgano o ente con competencia',
            'deriv_dest_type' => 'Órgano o ente con competencia',
            'deriv_external_entity' => 'Hidrológica Socialista de Portuguesa (HIDROSPORTUGUESA)',
            'deriv_office_number' => 'CMP-DAC-OF-012-2026',
            'deriv_office_date' => now()->toDateString(),
            'deriv_letter_url' => 'https://oficios.cmp.gob.ve/cmp-dac-of-012-2026.pdf',
            'deriv_fundamento' => 'Incompetencia por razón de la materia; corresponde a ente prestador del servicio.',
            'deriv_recaudos' => ['acta_recepcion', 'informe_valoracion'],
            'deriv_citizen_notified' => '1',
            'deriv_citizen_notified_at' => now()->toDateString(),
            'deriv_citizen_notified_ref' => 'CMP-DAC-NOT-012-2026',
            'nota' => 'Se remite el expediente al ente competente mediante oficio formal.',
        ])->assertRedirect(route('oac.expedientes.show', $e));

        $e->load('remisiones');
        $this->assertCount(1, $e->remisiones);
        $remision = $e->remisiones->first();
        $this->assertSame('Órgano o ente con competencia', $remision->ente_destino);
        $this->assertSame('Hidrológica Socialista de Portuguesa (HIDROSPORTUGUESA)', $remision->ente_externo);
        $this->assertSame('CMP-DAC-OF-012-2026', $remision->numero_oficio);
        $this->assertSame('REFERRED', DB::table('case_status')->where('id', $e->status_id)->value('code'));
        $this->assertSame($usuario->id, DB::table('case_status_log')->where('case_file_id', $e->id)->orderByDesc('id')->value('changed_by'));

        // Notificación formal al ciudadano vinculada a la remisión
        $this->post(route('oac.expedientes.respuestaCiudadana', $e), [
            'remision_id' => $remision->id,
            'response_type' => 'Remisión a otro órgano o ente competente',
            'response_date' => now()->toDateString(),
            'is_competencia' => 'no',
            'referred_entity' => 'Hidrológica Socialista de Portuguesa (HIDROSPORTUGUESA)',
            'legal_basis' => 'Artículo 10 del Manual DAC',
            'response_text' => 'Se le informa que su expediente fue remitido formalmente al ente competente para su atención.',
            'issued_by_name' => 'Juan Romero',
            'issued_by_cargo' => 'Director de la DAC',
        ])->assertRedirect(route('oac.expedientes.show', $e));

        $e->load('notificacionesCiudadanas');
        $this->assertCount(1, $e->notificacionesCiudadanas);
        $this->assertSame($remision->id, $e->notificacionesCiudadanas->first()->remision_id);
        $this->assertSame('no', $e->notificacionesCiudadanas->first()->is_competencia);

        $this->getJson('/denuncias/estado?codigo='.$e->case_number)
            ->assertOk()
            ->assertJsonPath('remision_actual.destino_especifico', 'Hidrológica Socialista de Portuguesa (HIDROSPORTUGUESA)')
            ->assertJsonPath('respuestas_ciudadano.0.ente_remitido', 'Hidrológica Socialista de Portuguesa (HIDROSPORTUGUESA)');
    }

    public function test_google_maps_y_evidencias_digitalizadas_en_formulario(): void
    {
        // La vista pública contiene mapas, enlaces a Google Maps, modal e imágenes de ejemplo digitalizadas
        $this->get('/denuncias')
            ->assertOk()
            ->assertSee('mapa-ciudadano')
            ->assertSee('mapa-senalado')
            ->assertSee('modalEjemploDocumento')
            ->assertSee('ejemplo-ci-frente.jpg')
            ->assertSee('ejemplo-ci-reverso.png');

        // Rechaza coordenadas fuera de Venezuela tanto en ciudadano como en señalado
        Storage::fake();
        $pdf = function (string $nombre) {
            $ruta = tempnam(sys_get_temp_dir(), 'ev');
            file_put_contents($ruta, "%PDF-1.4\n%prueba\n");

            return new \Illuminate\Http\UploadedFile($ruta, $nombre, null, null, true);
        };

        $datosInvalidos = [
            'tipo_tramite' => 'denuncia',
            'es_consulta' => false,
            'ciudadano' => [
                'tipo_doc' => 'V',
                'nro_doc' => '19555666',
                'primer_nombre' => 'Carlos',
                'primer_apellido' => 'Rivas',
                'sexo' => 'M',
                'correo' => 'carlos@gmail.com',
                'direccion' => 'Calle 5, Acarigua, Portuguesa',
            ],
            'cit_lat' => '40.4168',
            'cit_lng' => '-3.7038',
            'senalados' => [['tipo' => 'persona_natural', 'campos' => ['pn-nombres' => 'Luis Torres']]],
            'ubicacion_senalado' => 'Acarigua Centro',
            'ubicacion_lat' => '40.4168',
            'ubicacion_lng' => '-3.7038',
            'narracion' => str_repeat('Hechos de prueba suficientemente largos para validar. ', 2),
            'otra_instancia' => 'no',
            'acepta_declaracion' => true,
        ];

        $this->post('/denuncias', [
            'datos' => json_encode($datosInvalidos),
            'evidencias' => [
                'ci_denunciante' => [$pdf('ci.pdf')],
                'carta_exposicion' => [$pdf('carta.pdf')],
            ],
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ciudadano.direccion', 'ubicacion_senalado']);

        // Coordenadas válidas en Acarigua generan google_maps_url en consulta pública
        $e = $this->crearExpediente();
        $this->getJson('/denuncias/estado?codigo='.$e->case_number)
            ->assertOk()
            ->assertJsonPath('google_maps_url', 'https://www.google.com/maps?q=9.5580000,-69.2010000');
    }

    public function test_flujo_subsanacion_recaudos_improcedencia_y_cierre_motivado(): void
    {
        $e = $this->crearExpediente();
        $soloCasos = $this->usuario(['CASES' => 'write']);
        $analista = $this->usuario(['CASES' => 'write', 'CLASSIFY' => 'write']);

        // Derivar exige permiso CLASSIFY:write también en la ruta oac.expedientes.derivar
        $this->actingAs($soloCasos)
            ->post(route('oac.expedientes.derivar', $e), [
                'deriv_scope' => 'Unidad de Auditoría Interna (UAI)',
                'deriv_dest_type' => 'Unidad de Auditoría Interna',
                'deriv_unit' => 'Unidad de Auditoría Interna (UAI)',
                'nota' => 'Intento sin permiso de clasificación.',
            ])->assertForbidden();

        $this->actingAs($analista);

        // Consignación de recaudos físicos
        $this->post(route('oac.expedientes.recaudosFisicos', $e), [
            'physical_docs_submitted' => 1,
            'physical_docs_date' => now()->toDateString(),
            'physical_file_number' => 'CMP-DAC-D-01-2026',
        ])->assertRedirect(route('oac.expedientes.show', $e));

        $e->refresh();
        $this->assertTrue((bool) $e->physical_docs_submitted);
        $this->assertSame('CMP-DAC-D-01-2026', $e->physical_file_number);

        // Pasar a PROCESSING y luego a SUBSANATION (calcula 5 días hábiles)
        $this->post(route('oac.expedientes.actuar', $e), [
            'desde' => 'RECEIVED',
            'estatus' => 'PROCESSING',
            'nota' => 'Protocolizado para valoración técnica.',
            'ignorar_lapso_confirmacion' => 1,
        ])->assertSessionHas('ok');

        $this->post(route('oac.expedientes.actuar', $e), [
            'desde' => 'PROCESSING',
            'estatus' => 'SUBSANATION',
            'subsanation_days' => 5,
            'subsanation_missing_docs' => 'Copia legible de C.I. y carta con huella',
            'nota' => 'Se otorgan 5 días hábiles conforme al Oficio Circular N° 01-00-499.',
        ])->assertSessionHas('ok');

        $e->refresh();
        $this->assertNotNull($e->subsanation_deadline);
        $this->assertSame('Copia legible de C.I. y carta con huella', $e->subsanation_missing_docs);

        $this->getJson('/denuncias/estado?codigo='.$e->case_number)
            ->assertOk()
            ->assertJsonPath('estatus_codigo', 'SUBSANATION')
            ->assertJsonPath('subsanacion_vigente', true);

        // Retornar a PROCESSING al subsanar
        $this->post(route('oac.expedientes.actuar', $e), [
            'desde' => 'SUBSANATION',
            'estatus' => 'PROCESSING',
            'nota' => 'Ciudadano consignó recaudos dentro del plazo.',
        ])->assertSessionHas('ok');
        $this->assertNotNull($e->fresh()->subsanation_completed_at);

        // Derivar desde actuar() también crea registro en case_remisiones
        $uaiId = DB::table('referral_unit')->where('code', 'UAI')->value('id');
        $this->assertNotNull($uaiId);
        $this->post(route('oac.expedientes.actuar', $e), [
            'desde' => 'PROCESSING',
            'estatus' => 'REFERRED',
            'referral_unit_id' => $uaiId,
            'nota' => 'Remitido a la Unidad de Auditoría Interna.',
        ])->assertSessionHas('ok');
        $this->assertSame(1, $e->remisiones()->count());

        // Cierre motivado con Auto de Cierre, V°B° DAC y Aprobación del Contralor
        $this->post(route('oac.expedientes.actuar', $e), [
            'desde' => 'REFERRED',
            'estatus' => 'ARCHIVED',
            'closure_auto_number' => 'CMP-DAC-AC-009-2026',
            'closure_auto_date' => now()->toDateString(),
            'dac_approved' => 1,
            'dac_approved_by' => 'Ing. Juan Romero',
            'comptroller_approved' => 1,
            'comptroller_approved_by' => 'Contralora Municipal',
            'nota' => 'Auto de cierre motivado con visto bueno DAC y aprobación de máxima autoridad.',
        ])->assertSessionHas('ok');

        $e->refresh();
        $this->assertSame('CMP-DAC-AC-009-2026', $e->closure_auto_number);
        $this->assertTrue((bool) $e->dac_approved);
        $this->assertTrue((bool) $e->comptroller_approved);

        // Segundo expediente para probar improcedencia (INVALIDATED)
        $e2 = $this->crearExpediente(['ciudadano' => ['nro_doc' => '20999888']]);
        $this->post(route('oac.expedientes.actuar', $e2), [
            'desde' => 'RECEIVED',
            'estatus' => 'INVALIDATED',
            'closure_auto_number' => 'CMP-DAC-IN-002-2026',
            'closure_reason' => 'Omisión de requisitos mínimos (Oficio Circular N° 01-00-499)',
            'nota' => 'Declarado improcedente por incumplimiento de requisitos mínimos.',
        ])->assertSessionHas('ok');

        $this->getJson('/denuncias/estado?codigo='.$e2->case_number)
            ->assertOk()
            ->assertJsonPath('estatus_codigo', 'INVALIDATED')
            ->assertJsonPath('cerrado', true)
            ->assertJsonPath('closure_auto_number', 'CMP-DAC-IN-002-2026');
    }
}
