<?php

namespace App\Console\Commands;

use App\Models\StaffUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CrearAdmin extends Command
{
    protected $signature = 'cgp:admin';

    protected $description = 'Crea un usuario administrador con acceso total (OAC y Administración)';

    public function handle(): int
    {
        $d = [
            'first_name' => trim($this->ask('Nombres')),
            'last_name' => trim($this->ask('Apellidos')),
            'id_doc_number' => trim($this->ask('Cédula (solo números)')),
            'email' => mb_strtolower(trim($this->ask('Correo'))),
            'password' => (string) $this->secret('Contraseña (mínimo 8 caracteres)'),
        ];

        $v = Validator::make($d + ['repetida' => (string) $this->secret('Repita la contraseña')], [
            'first_name' => 'required|max:80',
            'last_name' => 'required|max:80',
            'id_doc_number' => 'required|max:12|unique:staff_user,id_doc_number',
            'email' => 'required|email|max:150|unique:staff_user,email',
            'password' => ['required', 'same:repetida', Password::min(8)],
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
