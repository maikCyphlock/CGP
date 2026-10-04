<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('admin.usuarios', [
            'usuarios' => StaffUser::withoutGlobalScopes()
                ->join('job_position as j', 'j.id', '=', 'staff_user.job_position_id')
                ->orderByDesc('staff_user.active')->orderBy('staff_user.first_name')
                ->get(['staff_user.*', 'j.title as cargo']),
        ]);
    }

    public function create()
    {
        return view('admin.usuario_nuevo', [
            'cargos' => DB::table('job_position')->where('active', true)->orderBy('title')->get(),
            'tiposDoc' => DB::table('id_document_type')->whereIn('code', ['V', 'E'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        // Antes de validar, para que Pedro@X y pedro@x cuenten como el mismo correo.
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $d = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'id_doc_type_id' => 'required|exists:id_document_type,id',
            'id_doc_number' => ['required', 'string', 'max:12', Rule::unique('staff_user')->where('id_doc_type_id', $request->input('id_doc_type_id'))],
            'email' => 'required|email|max:150|unique:staff_user,email',
            'job_position_id' => ['required', Rule::exists('job_position', 'id')->where('active', true)],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'required' => 'Falta el campo :attribute.',
            'email.unique' => 'Ya existe un usuario con ese correo.',
            'id_doc_number.unique' => 'Ya existe un usuario con esa cédula.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ], [
            'first_name' => 'nombres', 'last_name' => 'apellidos', 'id_doc_type_id' => 'tipo de cédula',
            'id_doc_number' => 'cédula', 'email' => 'correo', 'job_position_id' => 'cargo', 'password' => 'contraseña',
        ]);

        // Nace sin permisos: se le asignan en la ficha del usuario.
        $u = new StaffUser($d);
                $u->password_hash = $d['password'];
        $u->save();

        return redirect()->route('admin.usuarios.show', $u->id)->with('ok', 'Usuario creado. Ahora asígnele los permisos que necesite.');
    }

    public function show(Request $request, string $usuario)
    {
        $u = $this->buscar($usuario);

        return view('admin.usuario', [
            'u' => $u,
            'cargo' => DB::table('job_position')->where('id', $u->job_position_id)->value('title'),
            'esYo' => $u->id === $request->user()->id,
            'modulos' => DB::table('app_module')->orderBy('id')->get(),
            'permisos' => DB::table('staff_privilege')->where('user_id', $u->id)->get()->keyBy('module_id'),
            'cambios' => DB::table('access_audit_log as a')
                ->join('app_module as m', 'm.id', '=', 'a.module_id')
                ->join('staff_user as s', 's.id', '=', 'a.admin_id')
                ->where('a.affected_user_id', $u->id)
                ->orderByDesc('a.logged_at')->orderByDesc('a.id')->limit(15)
                ->get(['a.action', 'a.logged_at', 'a.after_state', 'm.name as modulo', DB::raw("s.first_name || ' ' || s.last_name as admin")]),
        ]);
    }

    public function estado(Request $request, string $usuario)
    {
        $u = $this->buscar($usuario);
        $activar = $request->boolean('active');

        if (! $activar && $u->id === $request->user()->id) {
            return back()->withErrors(['active' => 'No puede desactivar su propia cuenta.']);
        }
        if (! $activar && $this->esUltimoAdministrador($u)) {
            return back()->withErrors(['active' => 'No se puede desactivar: es el único usuario que administra los accesos.']);
        }

        $u->forceFill(['active' => $activar] + ($activar ? ['failed_attempts' => 0, 'locked_until' => null] : []))->save();

        return back()->with('ok', $activar ? 'Usuario activado.' : 'Usuario desactivado. Ya no puede entrar al panel.');
    }

    public function permisos(Request $request, string $usuario)
    {
        $u = $this->buscar($usuario);
        if ($u->id === $request->user()->id) {
            return back()->withErrors(['permisos' => 'No puede cambiar sus propios permisos; pídaselo a otro administrador.']);
        }

        $marcados = $request->input('p', []);
        DB::transaction(function () use ($u, $marcados, $request) {
            foreach (DB::table('app_module')->get() as $m) {
                $m_ = $marcados[$m->id] ?? [];
                $escribir = ! empty($m_['write']) || ! empty($m_['delete']);
                // Un permiso mayor implica los menores: borrar ⊂ escribir ⊂ leer.
                $nuevo = [
                    'can_read' => $escribir || ! empty($m_['read']),
                    'can_write' => $escribir,
                    'can_delete' => ! empty($m_['delete']),
                ];

                $antes = DB::table('staff_privilege')->where('user_id', $u->id)->where('module_id', $m->id)->first();
                $antesFlags = $antes ? ['can_read' => $antes->can_read, 'can_write' => $antes->can_write, 'can_delete' => $antes->can_delete] : null;
                if (($antesFlags ?? ['can_read' => false, 'can_write' => false, 'can_delete' => false]) == $nuevo) {
                    continue;
                }

                DB::table('staff_privilege')->updateOrInsert(
                    ['user_id' => $u->id, 'module_id' => $m->id],
                    $nuevo + ['granted_by' => $request->user()->id, 'granted_at' => now()],
                );
                DB::table('access_audit_log')->insert([
                    'affected_user_id' => $u->id,
                    'module_id' => $m->id,
                    'admin_id' => $request->user()->id,
                    'action' => ! $antes ? 'GRANT' : (in_array(true, $nuevo, true) ? 'MODIFY' : 'REVOKE'),
                    'before_state' => $antesFlags ? json_encode($antesFlags) : null,
                    'after_state' => json_encode($nuevo),
                ]);
            }
        });

        return back()->with('ok', 'Permisos actualizados. Rigen desde la próxima página que abra el usuario.');
    }

    private function buscar(string $id): StaffUser
    {
        abort_unless(\Illuminate\Support\Str::isUuid($id), 404);

        return StaffUser::withoutGlobalScopes()->findOrFail($id);
    }

    /** ¿Es el único usuario activo que puede administrar accesos? Evita dejar el sistema sin administrador. */
    private function esUltimoAdministrador(StaffUser $u): bool
    {
        $admins = DB::table('staff_privilege as p')
            ->join('app_module as m', 'm.id', '=', 'p.module_id')
            ->join('staff_user as s', 's.id', '=', 'p.user_id')
            ->where('m.code', 'ACCESS')->where('p.can_write', true)->where('s.active', true)
            ->pluck('s.id');

        return $admins->contains($u->id) && $admins->count() === 1;
    }
}
