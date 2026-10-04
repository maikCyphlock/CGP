<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    const MAX_INTENTOS = 5;

    const MINUTOS_BLOQUEO = 15;

    public function create()
    {
        return view('admin.login');
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
            $this->fallar('Demasiados intentos. Espere '.ceil(RateLimiter::availableIn($llave) / 60).' minuto(s).');
        }

        $user = StaffUser::where('email', $email)->first();

        if ($user?->locked_until?->isFuture()) {
            $this->fallar('Cuenta bloqueada por intentos fallidos hasta las '.$user->locked_until->timezone(config('app.timezone'))->format('h:i a').'.');
        }

        if (! $user || ! Hash::check($cred['password'], $user->password_hash)) {
            RateLimiter::hit($llave, 60 * self::MINUTOS_BLOQUEO);
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

        return redirect()->intended(route('admin.inicio'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function fallar(string $mensaje): never
    {
        throw ValidationException::withMessages(['email' => $mensaje]);
    }
}
