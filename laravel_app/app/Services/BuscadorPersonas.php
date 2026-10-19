<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\User;

/**
 * Búsqueda de personas con autocompletado: respeta el alcance del usuario (sede y rol), pagina de
 * 20 en 20 y devuelve lo mínimo que necesitan los selectores (texto, apoyo y estado).
 */
class BuscadorPersonas
{
    public const POR_PAGINA = 20;

    public const MINIMO_LETRAS = 2;

    public function __construct(private AlcanceService $alcance)
    {
    }

    /**
     * @param  array{q?: ?string, activas?: bool, sin_lideres?: bool, excluir?: array<int, int|string>, pagina?: int}  $filtros
     * @return array{resultados: array<int, array<string, mixed>>, mas: bool}
     */
    public function buscar(?User $user, array $filtros): array
    {
        $texto = trim((string) ($filtros['q'] ?? ''));

        if (mb_strlen($texto) < self::MINIMO_LETRAS) {
            return ['resultados' => [], 'mas' => false];
        }

        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));
        $excluir = array_values(array_filter(array_map('intval', (array) ($filtros['excluir'] ?? []))));

        $consulta = $this->alcance->personasVisibles($user)
            ->buscar($texto)
            ->when($filtros['activas'] ?? true, fn ($q) => $q->activas())
            ->when(!empty($filtros['sin_lideres']), fn ($q) => $q->where('personas.es_lider', false))
            ->when($excluir, fn ($q) => $q->whereNotIn('personas.id', $excluir))
            ->orderBy('personas.apellidos')
            ->orderBy('personas.nombres')
            ->orderBy('personas.id');

        // Se pide una fila de más para saber si hay otra página.
        $filas = $consulta->offset(($pagina - 1) * self::POR_PAGINA)->limit(self::POR_PAGINA + 1)->get();

        return [
            'resultados' => $filas->take(self::POR_PAGINA)->map(fn (Persona $p) => self::formato($p))->values()->all(),
            'mas' => $filas->count() > self::POR_PAGINA,
        ];
    }

    /** @return array{id: int, texto: string, apoyo: string, estado: string, sinDocumento: bool} */
    public static function formato(Persona $persona): array
    {
        $documento = $persona->numero_documento
            ? trim($persona->tipo_documento.' '.$persona->numero_documento)
            : 'Sin documento';

        return [
            'id' => $persona->id,
            'texto' => $persona->nombre_completo,
            'apoyo' => trim($documento.($persona->celular ? ' · '.$persona->celular : '')),
            'estado' => $persona->estado,
            'sinDocumento' => !$persona->numero_documento,
        ];
    }
}
