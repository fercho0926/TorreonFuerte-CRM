<?php

namespace App\Models\Concerns;

use App\Services\Historial;

/**
 * Registra solas las altas, ediciones, bajas y restauraciones del modelo (spec 015, research D2) con las acciones
 * `creado`, `editado`, `eliminado` y `restaurado` de su catálogo, o la que imponga `Historial::accion()`.
 * Una edición sin campos cambiados (fuera de los ignorados) no escribe nada.
 */
trait RegistraHistorial
{
    use TieneHistorial;

    public static function bootRegistraHistorial(): void
    {
        $historial = fn () => app(Historial::class);

        static::created(fn ($modelo) => $historial()->registrar($modelo, 'creado', null, $modelo->valoresHistorial($modelo->getAttributes())));

        static::updated(function ($modelo) use ($historial) {
            $cambiados = $modelo->valoresHistorial($modelo->getChanges());
            $secretos = array_filter(array_keys($modelo->getChanges()), fn ($c) => $historial()->esSecreto($modelo, $c));

            if (!$cambiados && !$secretos) {
                return;
            }

            $historial()->registrar($modelo, 'editado', array_intersect_key($modelo->getRawOriginal(), $cambiados), $cambiados);
        });

        static::deleted(fn ($modelo) => $historial()->registrar($modelo, 'eliminado', null, null, null, ['datos' => $historial()->foto($modelo)]));
    }

    /**
     * Valores que cuentan para el historial: sin campos técnicos ni ignorados (los secretos los quita `Historial`).
     *
     * @param  array<string, mixed>  $valores
     * @return array<string, mixed>
     */
    public function valoresHistorial(array $valores): array
    {
        // La sede solo cuenta si el modelo la declara (por ejemplo, la sede de un usuario).
        $fuera = ['id', 'created_at', 'updated_at', 'deleted_at', ...$this->camposIgnorados()];
        if (!array_key_exists('sede_id', $this->camposHistorial())) {
            $fuera[] = 'sede_id';
        }

        return array_diff_key($valores, array_flip($fuera));
    }
}
