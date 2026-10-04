<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccesoModulo
{
    /** Uso: ->middleware('modulo:oac') o 'modulo:admin'. Entra quien tenga algún privilegio del módulo. */
    public function handle(Request $request, Closure $next, string $modulo)
    {
        view()->share('mod', $modulo);

        if (! $request->user()->accede($modulo)) {
            DB::table('unauthorized_access_log')->insert([
                'user_id' => $request->user()->id,
                'endpoint' => mb_substr($request->method().' /'.$request->path(), 0, 200),
                'client_meta' => json_encode(['ip' => $request->ip(), 'ua' => $request->userAgent()]),
            ]);
            abort(403);
        }

        return $next($request);
    }
}
