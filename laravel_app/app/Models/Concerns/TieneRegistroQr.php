<?php

namespace App\Models\Concerns;

use App\Models\Celula;
use App\Models\Encuentro;
use App\Models\Evento;
use App\Models\RegistroQr;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * Enlace de registro por QR de un evento, encuentro o célula: token no adivinable, interruptor
 * de apertura y cierre automático según el estado del destino.
 */
trait TieneRegistroQr
{
    public static function bootTieneRegistroQr(): void
    {
        static::creating(function ($modelo) {
            $modelo->registro_token ??= Str::random(32);
        });
    }

    public function registrosQr(): MorphMany
    {
        return $this->morphMany(RegistroQr::class, 'registrable')->latest();
    }

    /** Tipo corto para las rutas: evento, encuentro o celula. */
    public function tipoRegistroQr(): string
    {
        return [Evento::class => 'evento', Encuentro::class => 'encuentro', Celula::class => 'celula'][static::class];
    }

    public function estaAbierto(): bool
    {
        if (!$this->registro_abierto) {
            return false;
        }

        return $this->tipoRegistroQr() === 'celula'
            ? $this->estado === 'activa'
            : $this->estado === 'planificado';
    }

    public function regenerarToken(): void
    {
        $this->forceFill(['registro_token' => Str::random(32)])->save();
    }

    public function urlRegistro(): string
    {
        // Filas creadas sin pasar por el modelo (seeders, importaciones) aún no tienen enlace: se les da uno.
        if (blank($this->registro_token)) {
            $this->regenerarToken();
        }

        return route('registro.show', $this->registro_token);
    }
}
