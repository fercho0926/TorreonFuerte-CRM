<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroQrRequest;
use App\Services\RegistroPorQr;
use App\Support\Celular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Formulario público del QR de un evento, encuentro o célula. No hay sesión: el token de la ruta
 * identifica el destino y toda consulta de personas queda limitada a la sede de ese destino.
 */
class RegistroPublicoController extends Controller
{
    private const BUSQUEDAS_POR_MINUTO = 30;

    private const ENVIOS_POR_VENTANA = 10;

    private const VENTANA_ENVIOS = 600; // segundos

    public function __construct(private RegistroPorQr $registro)
    {
    }

    public function show(Request $request, string $token)
    {
        $destino = $this->registro->resolverDestino($token);
        if (!$destino) {
            return $this->noValido($request);
        }
        if (!$destino->estaAbierto()) {
            return $this->sinCache(response()->view('registro.cerrado', ['destino' => $destino]));
        }

        return $this->sinCache(response()->view('registro.formulario', [
            'destino' => $destino, 'paso' => 1, 'datos' => [], 'persona' => null, 'aviso' => null,
        ]));
    }

    /** Paso 1 (sin JavaScript) y consulta de precarga (con JavaScript). */
    public function buscar(Request $request, string $token)
    {
        $destino = $this->registro->resolverDestino($token);
        if (!$destino) {
            return $this->noValido($request);
        }
        if (!$destino->estaAbierto()) {
            return $request->expectsJson()
                ? response()->json(['estado' => 'cerrado', 'mensaje' => 'El registro está cerrado.'], 409)
                : $this->sinCache(response()->view('registro.cerrado', ['destino' => $destino]));
        }

        $datos = $request->validate([
            'celular' => ['required_without:numero_documento', 'nullable', 'string', 'max:20'],
            'numero_documento' => ['nullable', 'string', 'max:30'],
        ], [
            'celular.required_without' => 'Escribe tu celular o tu cédula.',
        ]);

        $limite = $this->limitar('registro-buscar:'.$request->ip(), self::BUSQUEDAS_POR_MINUTO, 60);
        if ($limite) {
            return $this->demasiados($request, $limite);
        }

        $celular = $datos['celular'] ?? null;
        if ($celular !== null && $celular !== '' && !Celular::esValido($celular)) {
            return $this->errorDeCampo($request, 'celular', 'El celular debe tener al menos 10 dígitos (por ejemplo 310 555 9999).');
        }

        $busqueda = $this->registro->buscar($destino, $celular, $datos['numero_documento'] ?? null);

        // Una persona que solo existe en otra sede se trata como nueva: no se revela que existe.
        $estado = $busqueda['estado'] === RegistroPorQr::OTRA_SEDE ? RegistroPorQr::NUEVA : $busqueda['estado'];
        $persona = $estado === RegistroPorQr::EXISTENTE ? $busqueda['persona'] : null;

        if ($request->expectsJson()) {
            return $this->sinCache(response()->json(array_filter([
                'estado' => $estado,
                'mensaje' => $busqueda['mensaje'],
                'nombres' => $persona?->nombres,
                'apellidos' => $persona?->apellidos,
                'fecha_nacimiento' => $persona?->fecha_nacimiento?->toDateString(),
                'celular' => $persona ? Celular::limpio($persona->celular) : null,
            ], fn ($valor) => $valor !== null)));
        }

        if (in_array($estado, [RegistroPorQr::CONFLICTO, RegistroPorQr::PENDIENTE], true)) {
            return $this->sinCache(response()->view('registro.formulario', [
                'destino' => $destino, 'paso' => 1, 'datos' => $datos, 'persona' => null,
                'aviso' => $busqueda['mensaje'],
            ]));
        }

        return $this->sinCache(response()->view('registro.formulario', [
            'destino' => $destino, 'paso' => 2, 'datos' => $datos, 'persona' => $persona, 'aviso' => null,
        ]));
    }

    public function store(RegistroQrRequest $request, string $token)
    {
        $destino = $this->registro->resolverDestino($token);
        if (!$destino) {
            return $this->noValido($request);
        }
        if (!$destino->estaAbierto()) {
            return $this->sinCache(response()->view('registro.cerrado', ['destino' => $destino]));
        }

        $celular = Celular::limpio($request->input('celular'));
        $limite = $this->limitar('registro-envio:'.$request->ip().':'.$celular, self::ENVIOS_POR_VENTANA, self::VENTANA_ENVIOS);
        if ($limite) {
            return $this->demasiados($request, $limite);
        }

        $resultado = $this->registro->registrar($destino, $request->validated());

        return redirect()->route('registro.listo', $token)->with('registro_resultado', $resultado);
    }

    public function listo(Request $request, string $token)
    {
        $destino = $this->registro->resolverDestino($token);
        if (!$destino) {
            return $this->noValido($request);
        }

        return $this->sinCache(response()->view('registro.confirmacion', [
            'destino' => $destino,
            'resultado' => $request->session()->get('registro_resultado'),
        ]));
    }

    /** Cuenta un intento; devuelve los segundos de espera si ya se pasó del límite. */
    private function limitar(string $clave, int $maximo, int $segundos): ?int
    {
        if (RateLimiter::tooManyAttempts($clave, $maximo)) {
            return RateLimiter::availableIn($clave);
        }
        RateLimiter::hit($clave, $segundos);

        return null;
    }

    private function demasiados(Request $request, int $segundos)
    {
        $mensaje = 'Demasiados intentos; espera un momento y vuelve a intentarlo.';

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

    /** Las respuestas del registro llevan datos de personas: nunca se guardan en caché. */
    private function sinCache($respuesta)
    {
        $respuesta->headers->set('Cache-Control', 'no-store, private');

        return $respuesta;
    }
}
