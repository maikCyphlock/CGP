<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    const MAX_INTENTOS = 5;

    const MINUTOS_BLOQUEO = 15;

    /** El módulo (oac|admin) lo da la URL: /oac/login o /admin/login. */
    private function modulo(Request $request): string
    {
        return $request->is('oac', 'oac/*') ? 'oac' : 'admin';
    }

    public function create(Request $request)
    {
        return view('auth.login', ['mod' => $this->modulo($request)]);
    }

    public function store(Request $request)
    {
        $cred = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Ingrese su correo.',
            'email.email' => 'El correo no es válido.',
            'password.required' => 'Ingrese su contraseña.',
        ]);
        $email = Str::lower(trim($cred['email']));

        // Freno por IP + correo, además del bloqueo de la cuenta.
        $llave = 'login|'.$email.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($llave, self::MAX_INTENTOS)) {
            $this->registrar($request, "LOGIN BLOQUEADO $email", StaffUser::where('email', $email)->first());
            $this->fallar('Demasiados intentos. Espere '.ceil(RateLimiter::availableIn($llave) / 60).' minuto(s).');
        }

        $user = StaffUser::where('email', $email)->first();

        if ($user?->locked_until?->isFuture()) {
            $this->registrar($request, "LOGIN BLOQUEADO $email", $user);
            $this->fallar('Cuenta bloqueada por intentos fallidos hasta las '.$user->locked_until->timezone(config('app.timezone'))->format('h:i a').'.');
        }

        if (! $user || ! Hash::check($cred['password'], $user->password_hash)) {
            RateLimiter::hit($llave, 60 * self::MINUTOS_BLOQUEO);
            $this->registrar($request, "LOGIN FALLIDO $email", $user);
            if ($user) {
                $intentos = $user->failed_attempts + 1;
                $user->forceFill($intentos >= self::MAX_INTENTOS
                    ? ['failed_attempts' => 0, 'locked_until' => now()->addMinutes(self::MINUTOS_BLOQUEO)]
                    : ['failed_attempts' => $intentos])->save();
            }
            $this->fallar('Correo o contraseña incorrectos.');
        }

        RateLimiter::clear($llave);
        $user->forceFill(['failed_attempts' => 0, 'locked_until' => null, 'last_login' => now()])->save();

        Auth::login($user);
        $request->session()->regenerate();

        $mod = $this->modulo($request);
        DB::table('session_log')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $request->session()->getId()),
            'expires_at' => now()->addMinutes((int) config('session.lifetime')),
            'client_meta' => json_encode(['ip' => $request->ip(), 'ua' => $request->userAgent(), 'modulo' => $mod]),
        ]);

        // Entró bien pero no a este módulo: se le lleva al que sí tiene, o se le dice por qué no.
        if (! $user->accede($mod)) {
            $otro = $mod === 'oac' ? 'admin' : 'oac';
            if ($user->accede($otro)) {
                return redirect()->route("$otro.inicio");
            }
            $this->cerrar($request);
            $this->fallar('Su cuenta no tiene acceso a ningún módulo. Solicítelo al administrador.');
        }

        return redirect()->intended(route("$mod.inicio"));
    }

    public function destroy(Request $request)
    {
        $mod = $this->modulo($request);
        $this->cerrar($request);

        return redirect()->route("$mod.login");
    }

    /** Cierra la sesión y deja constancia de la hora de salida. */
    private function cerrar(Request $request): void
    {
        DB::table('session_log')->where('token_hash', hash('sha256', $request->session()->getId()))->whereNull('closed_at')->update(['closed_at' => now()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /** Intentos fallidos o bloqueados: quedan en el registro de accesos no autorizados. */
    private function registrar(Request $request, string $evento, ?StaffUser $user): void
    {
        DB::table('unauthorized_access_log')->insert([
            'user_id' => $user?->id,
            'endpoint' => mb_substr($evento, 0, 200),
            'client_meta' => json_encode(['ip' => $request->ip(), 'ua' => $request->userAgent()]),
        ]);
    }

    private function fallar(string $mensaje): never
    {
        throw ValidationException::withMessages(['email' => $mensaje]);
    }
}
