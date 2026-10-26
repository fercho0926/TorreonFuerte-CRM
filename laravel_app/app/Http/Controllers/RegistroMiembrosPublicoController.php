<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroMiembroQrRequest;
use App\Models\Sede;
use App\Services\RegistroMiembrosQr;
use App\Services\RegistroPorQr;
use App\Support\Celular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Formulario público del QR de miembros de una sede. No hay sesión: el token identifica la sede y toda
 * consulta de miembros queda limitada a ella. Las respuestas llevan datos personales: nunca se cachean.
 */
class RegistroMiembrosPublicoController extends Controller
{
    /** Peticiones (consultas y envíos) por minuto desde una misma conexión. */
    public const INTENTOS_POR_MINUTO = 5;

    public function __construct(private RegistroPorQr $busqueda, private RegistroMiembrosQr $registro)
    {
    }

    public function show(Request $request, string $token)
    {
        $sede = $this->sede($token);
        if (!$sede) {
            return $this->noValido($request);
        }
        if (!$sede->registroMiembrosAbierto()) {
            return $this->sinCache(response()->view('registro-miembros.cerrado', ['sede' => $sede]));
        }

        return $this->sinCache(response()->view('registro-miembros.formulario', [
            'sede' => $sede, 'paso' => 1, 'datos' => [], 'persona' => null, 'aviso' => null,
        ]));
    }

    /** Paso 1 (sin JavaScript) y consulta de precarga (con JavaScript). */
    public function buscar(Request $request, string $token)
    {
        $sede = $this->sede($token);
        if (!$sede) {
            return $this->noValido($request);
        }
        if (!$sede->registroMiembrosAbierto()) {
            return $request->expectsJson()
                ? response()->json(['estado' => 'cerrado', 'mensaje' => 'El registro está cerrado.'], 409)
                : $this->sinCache(response()->view('registro-miembros.cerrado', ['sede' => $sede]));
        }

        $datos = $request->validate([
            'celular' => ['required_without:numero_documento', 'nullable', 'string', 'max:20'],
            'numero_documento' => ['nullable', 'string', 'max:30'],
        ], ['celular.required_without' => 'Escribe tu celular o tu cédula.']);

        if ($espera = $this->limitar($request)) {
            return $this->demasiados($request, $espera);
        }

        $celular = $datos['celular'] ?? null;
        if ($celular !== null && $celular !== '' && !Celular::esValido($celular)) {
            return $this->errorDeCampo($request, 'celular', 'El celular debe tener al menos 10 dígitos (por ejemplo 310 555 9999).');
        }

        $busqueda = $this->busqueda->buscarEnSede($sede->id, $celular, $datos['numero_documento'] ?? null);

        // Un miembro que solo existe en otra sede se trata como nuevo: no se revela que existe.
        $estado = $busqueda['estado'] === RegistroPorQr::OTRA_SEDE ? RegistroPorQr::NUEVA : $busqueda['estado'];
        $persona = in_array($estado, [RegistroPorQr::EXISTENTE, RegistroPorQr::PENDIENTE], true) ? $busqueda['persona'] : null;

        if ($request->expectsJson()) {
            return $this->sinCache(response()->json(array_filter([
                'estado' => $estado,
                'mensaje' => $estado === RegistroPorQr::CONFLICTO ? $busqueda['mensaje'] : null,
            ] + ($persona ? $this->registro->datosParaFormulario($persona) : []), fn ($valor) => $valor !== null)));
        }

        if ($estado === RegistroPorQr::CONFLICTO) {
            return $this->sinCache(response()->view('registro-miembros.formulario', [
                'sede' => $sede, 'paso' => 1, 'datos' => $datos, 'persona' => null, 'aviso' => $busqueda['mensaje'],
            ]));
        }

        return $this->sinCache(response()->view('registro-miembros.formulario', [
            'sede' => $sede, 'paso' => 2, 'datos' => $datos, 'persona' => $persona, 'aviso' => null,
        ]));
    }

    public function store(RegistroMiembroQrRequest $request, string $token)
    {
        $sede = $this->sede($token);
        if (!$sede) {
            return $this->noValido($request);
        }
        if (!$sede->registroMiembrosAbierto()) {
            return $this->sinCache(response()->view('registro-miembros.cerrado', ['sede' => $sede]));
        }

        if ($espera = $this->limitar($request)) {
            return $this->demasiados($request, $espera);
        }

        $resultado = $this->registro->registrar($sede, $request->validated());

        return redirect()->route('registro-sede.listo', $token)->with('registro_resultado', [
            'resultado' => $resultado['resultado'],
            'campos' => array_values(array_map(
                fn ($campo) => RegistroMiembrosQr::ETIQUETAS[$campo] ?? $campo,
                array_keys($resultado['cambios']),
            )),
            'estado' => $resultado['persona']?->estado_etiqueta,
        ]);
    }

    public function listo(Request $request, string $token)
    {
        $sede = $this->sede($token);
        if (!$sede) {
            return $this->noValido($request);
        }

        return $this->sinCache(response()->view('registro-miembros.confirmacion', [
            'sede' => $sede,
            'resultado' => $request->session()->get('registro_resultado'),
        ]));
    }

    /** La sede del token (sin filtro de sede: `Sede` no lo lleva); nula si el token no existe. */
    private function sede(string $token): ?Sede
    {
        return strlen($token) === 32 ? Sede::where('registro_token', $token)->first() : null;
    }

    /** Cuenta un intento; devuelve los segundos de espera si ya se pasó del límite. */
    private function limitar(Request $request): ?int
    {
        $clave = 'registro-miembros:'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, self::INTENTOS_POR_MINUTO)) {
            return RateLimiter::availableIn($clave);
        }
        RateLimiter::hit($clave, 60);

        return null;
    }

    private function demasiados(Request $request, int $segundos)
    {
        $mensaje = 'Demasiados intentos; espera un minuto y vuelve a intentarlo.';

        return $request->expectsJson()
            ? response()->json(['estado' => 'limite', 'mensaje' => $mensaje], 429, ['Retry-After' => $segundos])
            : response($mensaje, 429, ['Retry-After' => $segundos]);
    }

    private function noValido(Request $request)
    {
        return $request->expectsJson()
            ? response()->json(['estado' => 'no_valido', 'mensaje' => 'Enlace no válido.'], 404)
            : abort(404, 'Enlace no válido.');
    }

    private function errorDeCampo(Request $request, string $campo, string $mensaje)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $mensaje, 'errors' => [$campo => [$mensaje]]], 422)
            : back()->withInput()->withErrors([$campo => $mensaje]);
    }

    private function sinCache($respuesta)
    {
        $respuesta->headers->set('Cache-Control', 'no-store, private');

        return $respuesta;
    }
}
