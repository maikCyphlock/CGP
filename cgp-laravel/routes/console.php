<?php

use App\Models\StaffUser;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Crea un usuario del panel, o le cambia la clave y lo desbloquea si ya existe.
Artisan::command('cgp:usuario {email}', function (string $email) {
    $email = Str::lower(trim($email));
    $user = StaffUser::withoutGlobalScopes()->where('email', $email)->first();

    if (! $user) {
        $user = new StaffUser(['email' => $email]);
        $user->first_name = $this->ask('Nombres');
        $user->last_name = $this->ask('Apellidos');
        $user->id_doc_type_id = DB::table('id_document_type')->where('code', $this->choice('Tipo de cédula', ['V', 'E'], 'V'))->value('id');
        $user->id_doc_number = $this->ask('Número de cédula');
        $user->job_position_id = DB::table('job_position')->where('title', $this->ask('Cargo', 'Jefe de OAC'))->value('id')
            ?? DB::table('job_position')->insertGetId(['title' => 'Jefe de OAC']);
    }

    do {
        $clave = $this->secret('Contraseña (mínimo 8 caracteres)');
        $ok = strlen((string) $clave) >= 8 && $clave === $this->secret('Repita la contraseña');
        if (! $ok) {
            $this->error('Las contraseñas no coinciden o son muy cortas.');
        }
    } while (! $ok);

    $user->forceFill(['password_hash' => $clave, 'active' => true, 'failed_attempts' => 0, 'locked_until' => null])->save();

    // Quien se crea (o restablece) por consola es administrador: acceso total a todos los módulos.
    foreach (DB::table('app_module')->pluck('id') as $modulo) {
        DB::table('staff_privilege')->upsert(
            ['user_id' => $user->id, 'module_id' => $modulo, 'can_read' => true, 'can_write' => true, 'can_delete' => true],
            ['user_id', 'module_id'], ['can_read', 'can_write', 'can_delete'],
        );
    }
    $this->info(($user->wasRecentlyCreated ? 'Usuario creado: ' : 'Clave actualizada: ').$email);
})->purpose('Crear usuario del panel OAC o restablecer su contraseña');
