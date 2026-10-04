<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequierePrivilegio
{
    /** Uso: ->middleware('permiso:CASES,write') */
    public function handle(Request $request, Closure $next, string $modulo, string $accion = 'read')
    {
        $user = $request->user();

        if (! $user->puede($modulo, $accion)) {
            DB::table('unauthorized_access_log')->insert([
                'user_id' => $user->id,
                'module_id' => DB::table('app_module')->where('code', $modulo)->value('id'),
                'endpoint' => mb_substr($request->method().' /'.$request->path(), 0, 200),
                'client_meta' => json_encode(['ip' => $request->ip(), 'ua' => $request->userAgent()]),
            ]);
            abort(403);
        }

        return $next($request);
    }
}
