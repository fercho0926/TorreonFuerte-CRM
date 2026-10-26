<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Persona;
use App\Services\LecturaHistorial;
use App\Support\AccionesHistorial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Arma la pestaña «Histórico» de una ficha (spec 015). El controlador ya autorizó la ficha; quien la ve, ve su
 * histórico (RF-021). La pestaña se elige con ?pestana=historico; sin el parámetro se abre la de datos.
 */
trait PestanaHistorico
{
    protected function pestanaActiva(Request $request, array $otras = []): string
    {
        $pestana = (string) $request->query('pestana', 'datos');

        return in_array($pestana, ['historico', ...$otras], true) ? $pestana : 'datos';
    }

    /** @return array<string, mixed> datos para `historial._lista` */
    protected function historicoDeRegistro(Request $request, Model $sujeto, string $url): array
    {
        $lectura = app(LecturaHistorial::class);
        $filtros = $lectura->filtros($request);
        $tipo = (string) AccionesHistorial::tipoDe($sujeto);

        return [
            'url' => $url,
            'filtros' => $filtros,
            'usuarios' => $lectura->actoresDe($tipo, [(int) $sujeto->getKey()]),
            'acciones' => AccionesHistorial::deTipo($tipo),
            'entradas' => $lectura->deRegistro($sujeto, $filtros, (int) $request->query('pagina', 1)),
        ];
    }

    /** @return array<string, mixed> datos para `historial._secciones` */
    protected function historicoDePersona(Request $request, Persona $persona, string $url): array
    {
        $lectura = app(LecturaHistorial::class);
        $filtros = $lectura->filtros($request);
        $paginas = [];
        foreach (array_keys(AccionesHistorial::CATEGORIAS) as $categoria) {
            $paginas[$categoria] = (int) $request->query('pagina_'.$categoria, 1);
        }

        return [
            'url' => $url,
            'filtros' => $filtros,
            'usuarios' => $lectura->actoresDe('persona', $lectura->cadenaDe($persona)),
            'secciones' => $lectura->seccionesDePersona($persona, $filtros, $paginas),
        ];
    }
}
