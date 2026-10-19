<?php

namespace App\Http\Controllers;

use App\Models\RegistroClave;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

/**
 * La propia persona elige su contraseña: obligatoria tras una clave temporal («Elige tu
 * contraseña») y voluntaria desde «Mi cuenta».
 */
class CuentaController extends Controller
{
    /** Pantalla «Elige tu contraseña» tras entrar con una clave temporal. */
    public function editarClave()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $user = $this->usuarioActual();

        if (!$user->debe_cambiar_clave) {
            return redirect()->route('dashboard');
        }

        return view('auth.cambiar-clave', ['usuario' => $user]);
    }

    public function actualizarClave(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $user = $this->usuarioActual();

        if (!$user->debe_cambiar_clave) {
            return redirect()->route('dashboard');
        }

        $request->validate(['password' => $this->reglasContrasena($user)], $this->mensajes());

        $this->guardarContrasena($user, (string) $request->input('password'));

        return redirect()->route('dashboard')->with('success', 'Listo: tu contraseña quedó guardada.');
    }

    /** «Mi cuenta»: datos básicos y cambio de contraseña. */
    public function mostrar()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        return view('cuenta.mi-cuenta', ['usuario' => $this->usuarioActual()]);
    }

    public function cambiarClave(Request $request)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $user = $this->usuarioActual();

        $request->validate([
            'password_actual' => ['required', 'string', function (string $atributo, mixed $valor, Closure $fallar) use ($user) {
                if (!Hash::check((string) $valor, $user->password)) {
                    $fallar('La contraseña actual no es correcta.');
                }
            }],
            'password' => $this->reglasContrasena($user),
        ], $this->mensajes());

        $this->guardarContrasena($user, (string) $request->input('password'));

        return redirect()->route('cuenta.edit')->with('success', 'Tu contraseña se cambió correctamente.');
    }

    /**
     * Reglas de la contraseña definitiva: longitud mínima, confirmación y distinta de la vigente
     * (la clave temporal o la contraseña actual).
     *
     * @return array<int, mixed>
     */
    private function reglasContrasena(User $user): array
    {
        return [
            'required', 'string', 'min:'.config('claves.contrasena_minimo'), 'confirmed',
            function (string $atributo, mixed $valor, Closure $fallar) use ($user) {
                if (Hash::check((string) $valor, $user->password)) {
                    $fallar($user->debe_cambiar_clave
                        ? 'La contraseña nueva no puede ser igual a la clave temporal.'
                        : 'La contraseña nueva debe ser distinta de la actual.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    private function mensajes(): array
    {
        return [
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación no coincide con la contraseña.',
            'password.required' => 'Escribe la contraseña nueva.',
        ];
    }

    /** Guarda la contraseña elegida, cierra el ciclo de la clave temporal y renueva la sesión. */
    private function guardarContrasena(User $user, string $contrasena): void
    {
        $eraTemporal = $user->debe_cambiar_clave;

        $user->forceFill([
            'password' => $contrasena,
            'debe_cambiar_clave' => false,
            'clave_vence_en' => null,
        ])->save();

        if ($eraTemporal) {
            RegistroClave::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->whereIn('estado', [RegistroClave::PENDIENTE_ENVIO, RegistroClave::ENVIADA])
                ->update(['estado' => RegistroClave::USADA, 'usada_en' => now(), 'updated_at' => now()]);
        }

        Session::regenerate();
        Session::put('is_logged_in', true);
        Session::put('user_id', $user->id);
        Session::put('user_name', $user->name);
    }
}
