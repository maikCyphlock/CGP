<?php

namespace Tests\Feature;

use App\Models\CaseFile;
use App\Models\StaffUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Corre contra Postgres (cgp_test): el esquema usa triggers y JSONB.
class PanelAdminTest extends TestCase
{
    use RefreshDatabase;

    /** Crea un usuario; por defecto con acceso total, o con los permisos indicados ['CASES' => 'read', ...]. */
    private function usuario(array $extra = [], ?array $permisos = null): StaffUser
    {
        $u = StaffUser::create($extra + [
            'job_position_id' => DB::table('job_position')->value('id'),
            'id_doc_type_id' => DB::table('id_document_type')->where('code', 'V')->value('id'),
            'id_doc_number' => (string) random_int(1000000, 99999999),
            'first_name' => 'María Laura',
            'last_name' => 'Pérez',
            'email' => 'oac'.random_int(1, 99999).'@cgp.test',
            'password_hash' => 'clave-segura',
        ]);

        foreach (DB::table('app_module')->get() as $m) {
            $nivel = $permisos === null ? 'delete' : ($permisos[$m->code] ?? null);
            if ($nivel === null) {
                continue;
            }
            DB::table('staff_privilege')->insert([
                'user_id' => $u->id, 'module_id' => $m->id, 'can_read' => true,
                'can_write' => in_array($nivel, ['write', 'delete']), 'can_delete' => $nivel === 'delete',
            ]);
        }

        return $u;
    }

    private function expediente(): CaseFile
    {
        $r = $this->postJson('/denuncias', ['datos' => json_encode([
            'tipo_tramite' => 'queja',
            'ciudadano' => [
                'tipo_doc' => 'V', 'nro_doc' => '12345678', 'primer_nombre' => 'Ana', 'primer_apellido' => 'Díaz',
                'sexo' => 'F', 'correo' => 'ana@example.com', 'telf_cel' => '04141234567',
                'direccion' => 'Calle 1, Acarigua, Portuguesa',
            ],
            'narracion' => str_repeat('Hechos narrados por la ciudadana. ', 3),
            'acepta_declaracion' => true,
        ])])->assertCreated();

        return CaseFile::where('case_number', $r->json('case_number'))->firstOrFail();
    }

    private function actuar(CaseFile $e, array $datos)
    {
        return $this->from(route('admin.expedientes.show', $e))
            ->post(route('admin.expedientes.actuar', $e), $datos + ['nota' => 'Revisado por la OAC.']);
    }

    public function test_invitado_va_al_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
    }

    public function test_login_y_bloqueo_por_intentos_fallidos(): void
    {
        $u = $this->usuario();

        foreach (range(1, 5) as $_) {
            $this->post('/admin/login', ['email' => $u->email, 'password' => 'mala'])->assertSessionHasErrors('email');
        }
        $this->assertTrue($u->fresh()->locked_until->isFuture());

        // Bloqueada: ni con la clave correcta entra.
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'clave-segura']);
        $this->assertGuest();

        $u->forceFill(['locked_until' => null])->save();
        \RateLimiter::clear('login|'.$u->email.'|127.0.0.1');
        $this->post('/admin/login', ['email' => strtoupper($u->email), 'password' => 'clave-segura'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($u);
        $this->get('/admin')->assertOk()->assertSee('María Laura Pérez');
    }

    public function test_usuario_desactivado_pierde_acceso(): void
    {
        $u = $this->usuario();
        $this->actingAs($u)->get('/admin')->assertOk();

        $u->forceFill(['active' => false])->save();
        $this->app['auth']->forgetGuards();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_flujo_de_estatus(): void
    {
        $u = $this->usuario();
        $e = $this->expediente();
        $this->actingAs($u);

        $this->get('/admin/expedientes?estado=RECEIVED')->assertOk()->assertSee($e->case_number);
        $this->get('/admin/expedientes?q=Díaz')->assertOk()->assertSee($e->case_number);
        $this->get('/admin/expedientes?estado=HACK')->assertSessionHasErrors('estado');
        $this->get(route('admin.expedientes.show', $e))->assertOk()->assertSee('Ana Díaz');

        // Salto no permitido: de Recibido no se puede archivar.
        $this->actuar($e, ['desde' => 'RECEIVED', 'estatus' => 'ARCHIVED'])->assertSessionHasErrors('estatus');

        // Protocolizar.
        $this->actuar($e, ['desde' => 'RECEIVED', 'estatus' => 'IN_REVIEW'])->assertSessionHas('ok');
        $e->refresh();
        $this->assertSame('IN_REVIEW', DB::table('case_status')->where('id', $e->status_id)->value('code'));
        $this->assertNotNull($e->protocolized_at);
        $this->assertSame($u->id, $e->received_by);
        $this->assertSame($u->id, DB::table('case_status_log')->where('case_file_id', $e->id)->orderByDesc('id')->value('changed_by'));

        // Formulario viejo (otra pestaña todavía decía "Recibido"): se rechaza.
        $this->actuar($e, ['desde' => 'RECEIVED', 'estatus' => 'IN_REVIEW'])->assertSessionHasErrors('estatus');

        // Derivar exige la unidad.
        $this->actuar($e, ['desde' => 'IN_REVIEW', 'estatus' => 'REFERRED'])->assertSessionHasErrors('referral_unit_id');
        $this->actuar($e, ['desde' => 'IN_REVIEW', 'estatus' => 'REFERRED', 'referral_unit_id' => DB::table('referral_unit')->value('id')])->assertSessionHas('ok');

        // Nota sin cambio de estatus, y cierre.
        $this->actuar($e, ['desde' => 'REFERRED'])->assertSessionHas('ok');
        $this->actuar($e, ['desde' => 'REFERRED', 'estatus' => 'ARCHIVED'])->assertSessionHas('ok');

        // Archivado es final.
        $this->actuar($e, ['desde' => 'ARCHIVED', 'estatus' => 'IN_REVIEW'])->assertSessionHasErrors('estatus');

        $this->assertSame(
            ['STATUS_CHANGE', 'STATUS_CHANGE', 'NOTE', 'STATUS_CHANGE'],
            DB::table('case_action')->where('case_file_id', $e->id)->orderBy('id')->pluck('action_type')->all(),
        );
        $this->get('/admin')->assertOk()->assertSee($e->case_number);
    }

    public function test_sin_permiso_se_bloquea_y_se_registra(): void
    {
        $solo = $this->usuario([], ['CASES' => 'read']);
        $e = $this->expediente();
        $this->actingAs($solo);

        $this->get('/admin/expedientes')->assertOk();
        $this->get('/admin/usuarios')->assertForbidden();
        $this->get('/admin/catalogos')->assertForbidden();
        $this->get('/admin/contenidos')->assertForbidden();
        // Solo lectura: no puede registrar actuaciones ni clasificar.
        $this->actuar($e, ['desde' => 'RECEIVED', 'estatus' => 'IN_REVIEW'])->assertForbidden();
        $this->post(route('admin.expedientes.clasificar', $e), [])->assertForbidden();
        $this->assertSame(5, DB::table('unauthorized_access_log')->where('user_id', $solo->id)->count());
        $this->get('/admin')->assertOk()->assertDontSee('Usuarios y Accesos');
    }

    public function test_derivar_exige_permiso_de_clasificacion(): void
    {
        $e = $this->expediente();
        $this->actingAs($this->usuario([], ['CASES' => 'write']));
        $this->actuar($e, ['desde' => 'RECEIVED', 'estatus' => 'IN_REVIEW'])->assertSessionHas('ok');
        $this->actuar($e, ['desde' => 'IN_REVIEW', 'estatus' => 'REFERRED', 'referral_unit_id' => DB::table('referral_unit')->value('id')])->assertForbidden();
        $this->get(route('admin.expedientes.show', $e))->assertOk()->assertDontSee('Derivar a otra unidad');
    }

    public function test_clasificacion_y_oficio(): void
    {
        $e = $this->expediente();
        $this->actingAs($this->usuario());
        DB::table('irregularity_type')->insert(['code' => 'T1', 'name' => 'Irregularidad de prueba']);
        $tipo = DB::table('irregularity_type')->where('code', 'T1')->value('id');

        $this->post(route('admin.expedientes.clasificar', $e), ['irregularity_type_id' => 9999])->assertSessionHasErrors('irregularity_type_id');
        $this->post(route('admin.expedientes.clasificar', $e), ['irregularity_type_id' => $tipo, 'analyst_notes' => 'Revisar obra'])->assertSessionHas('ok');
        $this->assertSame($tipo, $e->fresh()->irregularity_type_id);

        $this->actuar($e, ['desde' => 'RECEIVED', 'estatus' => 'IN_REVIEW']);
        $unidad = DB::table('referral_unit')->value('id');
        $this->actuar($e, ['desde' => 'IN_REVIEW', 'estatus' => 'REFERRED', 'referral_unit_id' => $unidad, 'referral_letter_url' => 'javascript:alert(1)'])->assertSessionHasErrors('referral_letter_url');
        $this->actuar($e, ['desde' => 'IN_REVIEW', 'estatus' => 'REFERRED', 'referral_unit_id' => $unidad, 'referral_letter_url' => 'https://oficios.test/1.pdf'])->assertSessionHas('ok');
        $this->assertSame('https://oficios.test/1.pdf', $e->fresh()->referral_letter_url);

        // Cerrado: ya no se clasifica.
        $this->actuar($e, ['desde' => 'REFERRED', 'estatus' => 'ARCHIVED']);
        $this->post(route('admin.expedientes.clasificar', $e), ['analyst_notes' => 'tarde'])->assertStatus(422);
        $this->get(route('admin.expedientes.show', $e))->assertOk()->assertSee('Irregularidad de prueba');
    }

    public function test_usuarios_y_permisos_con_auditoria(): void
    {
        $admin = $this->usuario();
        $this->actingAs($admin);
        $datos = [
            'first_name' => 'Pedro', 'last_name' => 'Gil', 'id_doc_type_id' => DB::table('id_document_type')->where('code', 'V')->value('id'),
            'id_doc_number' => '9876543', 'email' => 'Pedro@Cgp.test', 'job_position_id' => DB::table('job_position')->value('id'),
            'password' => 'clave-larga-1', 'password_confirmation' => 'clave-larga-1',
        ];

        $this->post('/admin/usuarios', ['password_confirmation' => 'otra'] + $datos)->assertSessionHasErrors('password');
        $this->post('/admin/usuarios', ['password' => '123', 'password_confirmation' => '123'] + $datos)->assertSessionHasErrors('password');
        $this->post('/admin/usuarios', $datos)->assertRedirect();
        $pedro = StaffUser::where('email', 'pedro@cgp.test')->firstOrFail();
        $this->post('/admin/usuarios', ['id_doc_number' => '1'] + $datos)->assertSessionHasErrors('email');

        // Nace sin permisos.
        $this->assertSame(0, DB::table('staff_privilege')->where('user_id', $pedro->id)->count());

        $cases = DB::table('app_module')->where('code', 'CASES')->value('id');
        // "Eliminar" implica editar y ver.
        $this->post("/admin/usuarios/{$pedro->id}/permisos", ['p' => [$cases => ['delete' => 1]]])->assertSessionHas('ok');
        $this->assertTrue($pedro->fresh()->puede('CASES', 'read') && $pedro->fresh()->puede('CASES', 'write') && $pedro->fresh()->puede('CASES', 'delete'));
        $this->post("/admin/usuarios/{$pedro->id}/permisos", ['p' => [$cases => ['read' => 1]]]);
        $this->assertFalse($pedro->fresh()->puede('CASES', 'write'));
        $this->post("/admin/usuarios/{$pedro->id}/permisos", []);
        $this->assertFalse($pedro->fresh()->puede('CASES', 'read'));
        $this->assertSame(['GRANT', 'MODIFY', 'REVOKE'], DB::table('access_audit_log')->where('affected_user_id', $pedro->id)->orderBy('id')->pluck('action')->all());
        $this->get("/admin/usuarios/{$pedro->id}")->assertOk()->assertSee('Retiró');

        // Desactivar: pierde el acceso. Uno mismo, no.
        $this->post("/admin/usuarios/{$pedro->id}/estado", ['active' => 0])->assertSessionHas('ok');
        $this->assertFalse((bool) DB::table('staff_user')->where('id', $pedro->id)->value('active'));
        $this->post("/admin/usuarios/{$admin->id}/estado", ['active' => 0])->assertSessionHasErrors('active');
        $this->post("/admin/usuarios/{$admin->id}/permisos", [])->assertSessionHasErrors('permisos');
        $this->get("/admin/usuarios/{$pedro->id}")->assertOk()->assertSee('Desactivado');
    }

    public function test_no_se_puede_dejar_el_sistema_sin_administrador(): void
    {
        $uno = $this->usuario();
        $dos = $this->usuario([], ['USERS' => 'write']);
        $this->actingAs($dos);
        // "dos" gestiona usuarios pero no accesos, y "uno" es el único con ACCESS: no se puede desactivar.
        $this->post("/admin/usuarios/{$uno->id}/estado", ['active' => 0])->assertSessionHasErrors('active');
        $this->assertTrue((bool) DB::table('staff_user')->where('id', $uno->id)->value('active'));
    }

    public function test_catalogos(): void
    {
        $this->actingAs($this->usuario());
        $this->get('/admin/catalogos')->assertOk()->assertSee('Tipos de trámite');
        $this->get('/admin/catalogos/no-existe')->assertNotFound();
        $this->get('/admin/catalogos/case-status')->assertNotFound();

        $this->post('/admin/catalogos/irregularidades', ['code' => 'minuscula', 'name' => 'X'])->assertSessionHasErrors('code');
        $this->post('/admin/catalogos/irregularidades', ['code' => 'MALVERSACION', 'name' => 'Malversación', 'legal_basis' => 'Art. 91'])->assertSessionHas('ok');
        $this->post('/admin/catalogos/irregularidades', ['code' => 'MALVERSACION', 'name' => 'Otra'])->assertSessionHasErrors('code');

        $id = DB::table('irregularity_type')->where('code', 'MALVERSACION')->value('id');
        // El código no cambia; el resto sí; se puede desactivar.
        $this->post("/admin/catalogos/irregularidades/$id", ['code' => 'OTRO', 'name' => 'Malversación de fondos', 'active' => 0])->assertSessionHasErrors('code');
        $this->post("/admin/catalogos/irregularidades/$id", ['name' => 'Malversación de fondos', 'active' => 0])->assertSessionHas('ok');
        $fila = DB::table('irregularity_type')->find($id);
        $this->assertSame(['MALVERSACION', 'Malversación de fondos', false], [$fila->code, $fila->name, (bool) $fila->active]);

        // JSON inválido en tipos de señalado.
        $this->post('/admin/catalogos/tipos-senalado', ['code' => 'NUEVO', 'name' => 'Nuevo', 'field_schema' => '{no'])->assertSessionHasErrors('field_schema');
        $this->post('/admin/catalogos/tipos-senalado', ['code' => 'NUEVO', 'name' => 'Nuevo', 'field_schema' => '"texto"'])->assertStatus(422);

        // No se puede desactivar un cargo con personal activo.
        $cargo = DB::table('job_position')->value('id');
        $this->post("/admin/catalogos/cargos/$cargo", ['title' => 'Jefe de OAC', 'active' => 0])->assertSessionHasErrors('active');
    }

    public function test_contenido_web_con_versiones(): void
    {
        $this->actingAs($this->usuario());
        $tipo = DB::table('cms_content_type')->value('id');

        $this->post('/admin/contenidos', ['content_type_id' => $tipo, 'title' => '', 'body' => 'x'])->assertSessionHasErrors('title');
        $this->post('/admin/contenidos', ['content_type_id' => $tipo, 'title' => 'Primera noticia', 'body' => 'Texto 1'])->assertRedirect();
        $id = DB::table('cms_content')->value('id');
        $this->assertFalse((bool) DB::table('cms_content')->value('published'));

        $this->post("/admin/contenidos/$id", ['content_type_id' => $tipo, 'title' => 'Noticia editada', 'body' => 'Texto 2', 'published' => 1])->assertSessionHas('ok');
        $c = DB::table('cms_content')->find($id);
        $this->assertTrue($c->published && $c->published_at !== null && $c->title === 'Noticia editada');
        $this->assertSame(['Primera noticia'], DB::table('cms_content_version')->where('content_id', $id)->pluck('title')->all());

        $this->post("/admin/contenidos/$id", ['content_type_id' => $tipo, 'title' => 'Noticia editada', 'body' => 'Texto 2'])->assertSessionHas('ok');
        $this->assertNotNull(DB::table('cms_content')->find($id)->unpublished_at);
        $this->get("/admin/contenidos/$id")->assertOk()->assertSee('Primera noticia');
        $this->get('/admin/contenidos/no-es-uuid')->assertNotFound();
    }

    public function test_portada_muestra_solo_contenido_publicado(): void
    {
        $u = $this->usuario();
        $fila = fn (string $tipo, string $titulo, string $cuerpo, bool $pub) => DB::table('cms_content')->insert([
            'content_type_id' => DB::table('cms_content_type')->where('code', $tipo)->value('id'),
            'title' => $titulo, 'body' => $cuerpo, 'published' => $pub, 'author_id' => $u->id, 'published_at' => $pub ? now() : null,
        ]);

        $this->get('/')->assertOk()->assertSee('Juramentación del nuevo Contralor'); // sin CMS: texto fijo

        $fila('NEWS', 'Noticia del CMS', 'Cuerpo **nuevo** <script>alert(1)</script> [web](javascript:alert(1))', true);
        $fila('NEWS', 'Borrador secreto', 'x', false);
        $fila('MISSION', 'Misión', 'Misión desde el CMS', true);

        $this->get('/')->assertOk()
            ->assertSee('Noticia del CMS')->assertSee('<strong>nuevo</strong>', false)->assertDontSee('<script>alert', false)->assertDontSee('href="javascript:', false)
            ->assertSee('Misión desde el CMS')
            ->assertDontSee('Borrador secreto')->assertDontSee('Juramentación del nuevo Contralor')
            ->assertSee('A cinco años ejerceremos'); // visión sin publicar: texto fijo
    }

    public function test_imagenes_del_cms(): void
    {
        \Illuminate\Support\Facades\Storage::fake();
        $this->actingAs($this->usuario());
        $tipo = DB::table('cms_content_type')->where('code', 'NEWS')->value('id');
        $base = ['content_type_id' => $tipo, 'title' => 'Con foto', 'body' => 'Texto', 'published' => 1];
        // PNG real de 1x1 relleno hasta el peso pedido (el servidor valida el contenido, no la extensión).
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $foto = fn ($n, $kb = 100) => \Illuminate\Http\UploadedFile::fake()->createWithContent($n, str_pad($png, $kb * 1024, "\0"));

        // Rechazos: más de 4 MB y tipos que no son JPG/PNG/WebP (SVG puede llevar scripts).
        $this->post('/admin/contenidos', $base + ['imagen' => $foto('grande.jpg', 4097)])->assertSessionHasErrors('imagen');
        $this->post('/admin/contenidos', $base + ['imagen' => \Illuminate\Http\UploadedFile::fake()->create('x.svg', 1, 'image/svg+xml')])->assertSessionHasErrors('imagen');
        $this->post('/admin/contenidos', $base + ['imagen' => \Illuminate\Http\UploadedFile::fake()->create('x.pdf', 1, 'application/pdf')])->assertSessionHasErrors('imagen');
        $this->assertSame(0, DB::table('cms_content')->count());

        // Exactamente 4 MB entra; la base guarda solo el nombre y el archivo queda en disco.
        $this->post('/admin/contenidos', $base + ['imagen' => $foto('ok.jpg', 4096)])->assertSessionHasNoErrors();
        $c = DB::table('cms_content')->first();
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{20,60}\.(jpe?g|png|webp)$/', $c->image_path);
        \Illuminate\Support\Facades\Storage::assertExists("cms/{$c->image_path}");

        // Publicada: la ve el público y la portada la usa. Se sirve solo si está publicada.
        $this->get("/media/cms/{$c->image_path}")->assertOk();
        $this->get('/')->assertSee("/media/cms/{$c->image_path}", false);

        // Reemplazar borra la anterior; quitar también.
        $this->post("/admin/contenidos/{$c->id}", $base + ['imagen' => $foto('otra.png')])->assertSessionHasNoErrors();
        $c2 = DB::table('cms_content')->first();
        $this->assertNotSame($c->image_path, $c2->image_path);
        \Illuminate\Support\Facades\Storage::assertMissing("cms/{$c->image_path}");
        $this->post("/admin/contenidos/{$c->id}", $base + ['quitar_imagen' => 1])->assertSessionHasNoErrors();
        $this->assertNull(DB::table('cms_content')->value('image_path'));
        \Illuminate\Support\Facades\Storage::assertMissing("cms/{$c2->image_path}");

        // Sin cambios de imagen, se conserva; en borrador el público no la ve.
        $this->post("/admin/contenidos/{$c->id}", $base + ['imagen' => $foto('tres.webp')]);
        $nombre = DB::table('cms_content')->value('image_path');
        $this->post("/admin/contenidos/{$c->id}", ['published' => 0] + $base);
        $this->assertSame($nombre, DB::table('cms_content')->value('image_path'));
        $this->get("/media/cms/$nombre")->assertNotFound();
        $this->get("/admin/contenidos/imagen/$nombre")->assertOk(); // el admin sí la ve
    }
}
