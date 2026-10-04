<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Monitoreo del sistema para contralores y RR.HH.: quién está dentro, qué se intentó y quién cambió accesos. */
class MonitoreoController extends Controller
{
    const VISTAS = ['sesiones', 'denegados', 'permisos', 'historial', 'bloqueos'];

    /** Resumen común al inicio de Administración y a esta pantalla. */
    public static function resumen(): array
    {
        $activas = now()->subMinutes((int) config('session.lifetime'))->timestamp;

        return [
            'sesiones' => DB::table('sessions')->whereNotNull('user_id')->where('last_activity', '>', $activas)->count(),
            'bloqueadas' => DB::table('staff_user')->where('active', true)->where('locked_until', '>', now())->count(),
            'denegados' => DB::table('unauthorized_access_log')->where('logged_at', '>', now()->subDay())->count(),
            'permisos' => DB::table('access_audit_log')->where('logged_at', '>', now()->subDays(7))->count(),
            'usuarios' => DB::table('staff_user')->where('active', true)->count(),
            'inactivos' => DB::table('staff_user')->where('active', false)->count(),
        ];
    }

    public function index(Request $request)
    {
        $ver = in_array($request->query('ver'), self::VISTAS) ? $request->query('ver') : 'sesiones';
        $libre = @disk_free_space(storage_path());
        $total = @disk_total_space(storage_path());

        return view('admin.monitoreo', [
            'ver' => $ver,
            'r' => self::resumen(),
            'disco' => $total ? ['libre' => $libre, 'usado' => round(100 * (1 - $libre / $total))] : null,
            'filas' => $this->{'consulta'.ucfirst($ver)}($request),
            'miSesion' => $request->session()->getId(),
        ]);
    }

    /** Cierra a la fuerza una sesión abierta (ej. equipo perdido o persona que ya no trabaja aquí). */
    public function cerrarSesion(Request $request, string $id)
    {
        $s = DB::table('sessions')->where('id', $id)->whereNotNull('user_id')->first() ?? abort(404);
        abort_if($s->id === $request->session()->getId(), 422, 'Para salir de su propia sesión use «Cerrar sesión».');

        DB::table('sessions')->where('id', $id)->delete();
        DB::table('session_log')->where('token_hash', hash('sha256', $id))->whereNull('closed_at')->update(['closed_at' => now()]);
        $this->auditar($request, $s->user_id, ['sesion_abierta' => true], ['sesion_abierta' => false]);

        return back()->with('ok', 'Sesión cerrada.');
    }

    public function desbloquear(Request $request, string $usuario)
    {
        $u = DB::table('staff_user')->where('id', $usuario)->first() ?? abort(404);
        DB::table('staff_user')->where('id', $u->id)->update(['locked_until' => null, 'failed_attempts' => 0]);
        $this->auditar($request, $u->id, ['bloqueada' => true], ['bloqueada' => false]);

        return back()->with('ok', 'Cuenta desbloqueada.');
    }

    private function auditar(Request $request, string $afectado, array $antes, array $despues): void
    {
        DB::table('access_audit_log')->insert([
            'affected_user_id' => $afectado,
            'module_id' => DB::table('app_module')->where('code', 'ACCESS')->value('id'),
            'admin_id' => $request->user()->id,
            'action' => 'MODIFY',
            'before_state' => json_encode($antes),
            'after_state' => json_encode($despues),
        ]);
    }

    private function consultaSesiones(Request $request)
    {
        return DB::table('sessions as s')
            ->join('staff_user as u', 'u.id', '=', 's.user_id')
            ->where('s.last_activity', '>', now()->subMinutes((int) config('session.lifetime'))->timestamp)
            ->orderByDesc('s.last_activity')
            ->get(['s.id', 's.ip_address', 's.user_agent', 's.last_activity', 'u.first_name', 'u.last_name', 'u.email']);
    }

    private function consultaDenegados(Request $request)
    {
        return DB::table('unauthorized_access_log as l')
            ->leftJoin('staff_user as u', 'u.id', '=', 'l.user_id')
            ->leftJoin('app_module as m', 'm.id', '=', 'l.module_id')
            ->orderByDesc('l.logged_at')->orderByDesc('l.id')->limit(100)
            ->get(['l.logged_at', 'l.endpoint', 'l.client_meta', 'm.name as modulo', DB::raw("u.first_name || ' ' || u.last_name as usuario")]);
    }

    private function consultaPermisos(Request $request)
    {
        return DB::table('access_audit_log as a')
            ->join('app_module as m', 'm.id', '=', 'a.module_id')
            ->join('staff_user as q', 'q.id', '=', 'a.admin_id')
            ->join('staff_user as t', 't.id', '=', 'a.affected_user_id')
            ->orderByDesc('a.logged_at')->orderByDesc('a.id')->limit(100)
            ->get(['a.logged_at', 'a.action', 'a.before_state', 'a.after_state', 'm.name as modulo',
                DB::raw("q.first_name || ' ' || q.last_name as admin"), DB::raw("t.first_name || ' ' || t.last_name as afectado"), 't.id as afectado_id']);
    }

    private function consultaHistorial(Request $request)
    {
        return DB::table('session_log as l')
            ->join('staff_user as u', 'u.id', '=', 'l.user_id')
            ->orderByDesc('l.created_at')->limit(100)
            ->get(['l.created_at', 'l.closed_at', 'l.expires_at', 'l.client_meta', 'u.first_name', 'u.last_name', 'u.email']);
    }

    private function consultaBloqueos(Request $request)
    {
        return DB::table('staff_user')->where('active', true)->where('locked_until', '>', now())
            ->orderByDesc('locked_until')->get(['id', 'first_name', 'last_name', 'email', 'locked_until']);
    }
}
