<?php

namespace App\Console\Commands;

use App\Models\StaffUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CrearAdmin extends Command
{
    protected $signature = 'cgp:admin
        {--defecto : No pregunta nada: usa admin@admin.com / admin salvo lo que den --correo y --clave}
        {--correo= : Correo del administrador}
        {--clave= : Contraseña del administrador}';

    protected $description = 'Crea un usuario administrador con acceso total (OAC y Administración)';

    public function handle(): int
    {
        $def = $this->option('defecto');
        $correo = mb_strtolower(trim($this->option('correo') ?: ($def ? 'admin@admin.com' : $this->ask('Correo'))));

        // Con --defecto el comando se puede repetir sin error (lo usa el instalador).
        if ($def && StaffUser::withoutGlobalScopes()->where('email', $correo)->exists()) {
            $this->info("Ya existe el usuario $correo; no se cambió nada.");

            return self::SUCCESS;
        }

        $d = [
            'first_name' => $def ? 'Administrador' : trim($this->ask('Nombres')),
            'last_name' => $def ? 'Sistema' : trim($this->ask('Apellidos')),
            'id_doc_number' => $def ? '00000001' : trim($this->ask('Cédula (solo números)')),
            'email' => $correo,
            'password' => (string) ($this->option('clave') ?: ($def ? 'admin' : $this->secret('Contraseña (mínimo 8 caracteres)'))),
        ];
        $repetida = $def || $this->option('clave') ? $d['password'] : (string) $this->secret('Repita la contraseña');

        // La clave por defecto (admin) es corta a propósito: solo se exige el mínimo cuando la escribe una persona.
        $v = Validator::make($d + ['repetida' => $repetida], [
            'first_name' => 'required|max:80',
            'last_name' => 'required|max:80',
            'id_doc_number' => 'required|max:12|unique:staff_user,id_doc_number',
            'email' => 'required|email|max:150|unique:staff_user,email',
            'password' => ['required', 'same:repetida', $def ? 'min:1' : Password::min(8)],
        ], [
            'required' => 'Falta el campo :attribute.',
            'unique' => 'Ya existe un usuario con ese :attribute.',
            'same' => 'Las contraseñas no coinciden.',
        ], ['first_name' => 'nombres', 'last_name' => 'apellidos', 'id_doc_number' => 'cédula', 'email' => 'correo', 'password' => 'contraseña']);

        if ($v->fails()) {
            $this->error(implode("\n", $v->errors()->all()));

            return self::FAILURE;
        }

        $u = DB::transaction(function () use ($d) {
            $cargo = DB::table('job_position')->where('title', 'Administrador del sistema')->value('id')
                ?? DB::table('job_position')->insertGetId(['title' => 'Administrador del sistema', 'description' => 'Acceso total al sistema']);

            $u = StaffUser::create([
                'job_position_id' => $cargo,
                'id_doc_type_id' => DB::table('id_document_type')->where('code', 'V')->value('id'),
                'id_doc_number' => $d['id_doc_number'],
                'first_name' => $d['first_name'],
                'last_name' => $d['last_name'],
                'email' => $d['email'],
                'password_hash' => $d['password'],
            ]);
            DB::statement('INSERT INTO staff_privilege (user_id, module_id, can_read, can_write, can_delete, granted_by) SELECT ?, id, TRUE, TRUE, TRUE, ? FROM app_module', [$u->id, $u->id]);

            return $u;
        });

        $this->info("Listo. Ya puede entrar con {$u->email}");

        return self::SUCCESS;
    }
}
