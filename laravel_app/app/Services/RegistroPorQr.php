<?php

namespace App\Services;

use App\Models\Celula;
use App\Models\Encuentro;
use App\Models\Evento;
use App\Models\EventoPersona;
use App\Models\Persona;
use App\Models\RegistroQr;
use App\Support\Celular;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Autoinscripción por QR. El formulario es público (sin usuario), así que el destino se resuelve por
 * su token sin filtro de sede y todo lo demás corre dentro de la sede del destino (`conSede`): solo
 * se ven y se crean personas de esa sede. Una persona que solo existe en otra sede nunca se lee ni
 * se modifica; queda un `RegistroQr` con lo que ella escribió.
 */
class RegistroPorQr
{
    public const NUEVA = 'nueva';

    public const EXISTENTE = 'existente';

    public const PENDIENTE = 'pendiente';

    public const CONFLICTO = 'conflicto';

    public const OTRA_SEDE = 'de_otra_sede';

    public function __construct(private SedeContext $contexto)
    {
    }

    public function resolverDestino(string $token): ?Model
    {
        if (strlen($token) !== 32) {
            return null;
        }

        return $this->contexto->sinFiltro(function () use ($token) {
            foreach ([Evento::class, Encuentro::class, Celula::class] as $clase) {
                if ($destino = $clase::where('registro_token', $token)->first()) {
                    return $destino;
                }
            }

            return null;
        });
    }

    /**
     * Busca a la persona por cédula y por celular dentro de la sede del destino.
     *
     * @return array{estado: string, persona: ?Persona, mensaje: ?string}
     */
    public function buscar(Model $destino, ?string $celular, ?string $documento): array
    {
        $celular = $this->normalizarCelular($celular);
        $documento = $documento !== null && trim($documento) !== '' ? trim($documento) : null;

        return $this->contexto->conSede($destino->sede_id, function () use ($destino, $celular, $documento) {
            $porDocumento = $documento
                ? Persona::withTrashed()->where('numero_documento', $documento)->get()
                : collect();
            $porCelular = $celular
                ? Persona::withTrashed()->whereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(celular, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '') = ?",
                    [$celular]
                )->get()
                : collect();

            $candidatas = $porDocumento->merge($porCelular)->unique('id');

            if ($candidatas->count() > 1) {
                return $this->resultado(self::CONFLICTO, null, $porCelular->count() > 1 && $porDocumento->isEmpty()
                    ? 'Ese celular está en más de una ficha; contacta al administrador.'
                    : 'La cédula y el celular pertenecen a personas distintas; contacta al administrador.');
            }

            if ($candidatas->count() === 1) {
                /** @var Persona $persona */
                $persona = $candidatas->first();

                // La cédula escrita no coincide con la que ya tiene la persona del celular.
                if ($documento && $persona->numero_documento && $persona->numero_documento !== $documento) {
                    return $this->resultado(self::CONFLICTO, null, 'La cédula y el celular pertenecen a personas distintas; contacta al administrador.');
                }

                if ($persona->trashed() || $persona->estado === 'inactivo') {
                    return $this->resultado(self::PENDIENTE, $persona, 'Recibimos tu registro; el administrador te contactará.');
                }

                return $this->resultado(self::EXISTENTE, $persona);
            }

            return $this->resultado($this->existeEnOtraSede($destino->sede_id, $celular, $documento) ? self::OTRA_SEDE : self::NUEVA);
        });
    }

    /**
     * Registra a la persona. Devuelve el resultado guardado (nueva, existente, pendiente o de_otra_sede).
     *
     * @param  array{celular: string, nombres: string, apellidos: string, fecha_nacimiento: string, tipo_documento?: ?string, numero_documento?: ?string}  $datos
     *
     * @throws ValidationException
     */
    public function registrar(Model $destino, array $datos): string
    {
        $datos['celular'] = $this->normalizarCelular($datos['celular']);
        $datos['numero_documento'] = ($datos['numero_documento'] ?? null) !== null && trim($datos['numero_documento']) !== '' ? trim($datos['numero_documento']) : null;
        $datos['tipo_documento'] = $datos['numero_documento'] ? ($datos['tipo_documento'] ?? null) : null;

        $busqueda = $this->buscar($destino, $datos['celular'], $datos['numero_documento']);

        if ($busqueda['estado'] === self::CONFLICTO) {
            throw ValidationException::withMessages(['celular' => $busqueda['mensaje']]);
        }

        return $this->contexto->conSede($destino->sede_id, function () use ($destino, $datos, $busqueda) {
            return DB::transaction(function () use ($destino, $datos, $busqueda) {
                if ($this->yaRegistrado($destino, $datos['celular'], $busqueda['persona'])) {
                    throw ValidationException::withMessages(['celular' => 'Ya estás registrado en esta actividad.']);
                }

                $persona = $busqueda['persona'];
                $resultado = $busqueda['estado'];
                $nota = null;

                if ($resultado === self::NUEVA) {
                    $persona = Persona::create([
                        'sede_id' => $destino->sede_id,
                        'nombres' => $datos['nombres'],
                        'apellidos' => $datos['apellidos'],
                        'celular' => $datos['celular'],
                        'fecha_nacimiento' => $datos['fecha_nacimiento'],
                        'tipo_documento' => $datos['tipo_documento'],
                        'numero_documento' => $datos['numero_documento'],
                        'estado' => $destino instanceof Celula ? 'celula' : 'visitante',
                    ]);
                    $this->inscribir($destino, $persona);
                } elseif ($resultado === self::EXISTENTE) {
                    $nota = $this->notaDeCorreccion($persona, $datos);
                    $this->inscribir($destino, $persona);
                } elseif ($resultado === self::PENDIENTE) {
                    $persona = null; // sin ficha activa que enlazar: la gestiona el administrador
                }

                RegistroQr::create([
                    'sede_id' => $destino->sede_id,
                    'registrable_type' => $destino::class,
                    'registrable_id' => $destino->getKey(),
                    'persona_id' => in_array($resultado, [self::NUEVA, self::EXISTENTE], true) ? $persona->id : null,
                    'nombres' => $datos['nombres'],
                    'apellidos' => $datos['apellidos'],
                    'celular' => $datos['celular'],
                    'fecha_nacimiento' => $datos['fecha_nacimiento'],
                    'tipo_documento' => $datos['tipo_documento'],
                    'numero_documento' => $datos['numero_documento'],
                    'resultado' => $resultado,
                    'nota_correccion' => $nota,
                ]);

                return $resultado;
            });
        });
    }

    /** Solo dígitos y sin el indicativo de Colombia (+57) cuando viene con él. */
    public function normalizarCelular(?string $celular): ?string
    {
        $digitos = Celular::limpio($celular);

        return $digitos !== null && strlen($digitos) === 12 && str_starts_with($digitos, '57') ? substr($digitos, 2) : $digitos;
    }

    /** @return array{estado: string, persona: ?Persona, mensaje: ?string} */
    private function resultado(string $estado, ?Persona $persona = null, ?string $mensaje = null): array
    {
        return ['estado' => $estado, 'persona' => $persona, 'mensaje' => $mensaje];
    }

    /** Solo existencia: no lee ningún dato de la persona de otra sede. */
    private function existeEnOtraSede(int $sedeId, ?string $celular, ?string $documento): bool
    {
        return $this->contexto->sinFiltro(function () use ($sedeId, $celular, $documento) {
            return Persona::withTrashed()->where('sede_id', '!=', $sedeId)->where(function ($q) use ($celular, $documento) {
                if ($documento) {
                    $q->orWhere('numero_documento', $documento);
                }
                if ($celular) {
                    $q->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(celular, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '') = ?",
                        [$celular]
                    );
                }
            })->exists();
        });
    }

    private function yaRegistrado(Model $destino, string $celular, ?Persona $persona): bool
    {
        if (RegistroQr::where('registrable_type', $destino::class)->where('registrable_id', $destino->getKey())->where('celular', $celular)->exists()) {
            return true;
        }

        if (!$persona) {
            return false;
        }

        return match (true) {
            $destino instanceof Evento => EventoPersona::where('evento_id', $destino->id)->where('persona_id', $persona->id)->exists(),
            $destino instanceof Encuentro => $destino->personas()->whereKey($persona->id)->exists(),
            $destino instanceof Celula => $destino->miembros()->whereKey($persona->id)->exists(),
            default => false,
        };
    }

    private function inscribir(Model $destino, Persona $persona): void
    {
        if ($destino instanceof Evento) {
            EventoPersona::create(['sede_id' => $destino->sede_id, 'evento_id' => $destino->id, 'persona_id' => $persona->id]);
        } elseif ($destino instanceof Encuentro) {
            $destino->personas()->syncWithoutDetaching([$persona->id]);
        } elseif ($destino instanceof Celula) {
            $destino->miembros()->syncWithoutDetaching([$persona->id => ['fecha_ingreso' => now()->toDateString()]]);

            if ($persona->estado === 'visitante') {
                $persona->update(['estado' => 'celula']);
            }
        }
    }

    /** Diferencias entre lo escrito y la ficha: se guardan como nota, la persona no se modifica. */
    private function notaDeCorreccion(Persona $persona, array $datos): ?string
    {
        $diferencias = [];
        $igual = fn (?string $a, ?string $b) => mb_strtolower(trim((string) $a)) === mb_strtolower(trim((string) $b));

        if (!$igual($persona->nombres, $datos['nombres'])) {
            $diferencias[] = "Nombres: escribió «{$datos['nombres']}» (ficha: «{$persona->nombres}»)";
        }
        if (!$igual($persona->apellidos, $datos['apellidos'])) {
            $diferencias[] = "Apellidos: escribió «{$datos['apellidos']}» (ficha: «{$persona->apellidos}»)";
        }
        if ($persona->fecha_nacimiento && $persona->fecha_nacimiento->toDateString() !== $datos['fecha_nacimiento']) {
            $diferencias[] = "Fecha de nacimiento: escribió {$datos['fecha_nacimiento']} (ficha: {$persona->fecha_nacimiento->toDateString()})";
        }
        if ($datos['numero_documento'] && !$persona->numero_documento) {
            $diferencias[] = "Cédula: escribió {$datos['numero_documento']} (la ficha no tiene)";
        }
        if (Celular::limpio($persona->celular) !== $datos['celular']) {
            $diferencias[] = "Celular: escribió {$datos['celular']} (ficha: {$persona->celular})";
        }

        return $diferencias ? implode('. ', $diferencias).'.' : null;
    }
}
