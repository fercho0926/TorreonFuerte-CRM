<?php

namespace App\Services;

use App\Models\CambioHistorial;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use App\Support\Celular;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Toda cuenta de acceso es de un miembro de la iglesia (spec 016): búsqueda del miembro por su celular,
 * alta de la cuenta (y del miembro, si no existe) en una sola operación, y cambio del miembro de una cuenta.
 */
class CuentasDeMiembros
{
    public const ENCONTRADO = 'encontrado';

    public const VARIOS = 'varios';

    public const CON_CUENTA = 'con_cuenta';

    public const INACTIVO = 'inactivo';

    public const OTRA_SEDE = 'otra_sede';

    public const NO_VISIBLE = 'no_visible';

    public const NUEVO = 'nuevo';

    public const INVALIDO = 'invalido';

    public function __construct(
        private SedeContext $contexto,
        private AlcanceService $alcance,
        private LiderazgoService $liderazgo,
    ) {
    }

    /**
     * Busca entre los miembros que ve el actor (en la sede indicada, si la elige el administrador).
     * De otra sede solo se comprueba que el celular exista, sin devolver ningún dato (research D8).
     *
     * @return array{estado: string, personas: Collection<int, Persona>}
     */
    public function buscarPorCelular(string $celular, User $actor, ?int $sedeId = null): array
    {
        if (!Celular::esValido($celular)) {
            return ['estado' => self::INVALIDO, 'personas' => collect()];
        }

        $clave = Celular::clave($celular);

        $buscar = fn () => [
            $this->alcance->personasVisibles($actor)->where('celular_clave', $clave)->with('cuenta')
                ->orderBy('apellidos')->orderBy('nombres')->get(),
            Persona::query()->where('celular_clave', $clave)->exists(),
        ];

        [$personas, $enLaSede] = $sedeId ? $this->contexto->conSede($sedeId, $buscar) : $buscar();

        if ($personas->isEmpty()) {
            if ($enLaSede) {
                return ['estado' => self::NO_VISIBLE, 'personas' => collect()];
            }

            $enOtra = Persona::withoutGlobalScopes()->whereNull('deleted_at')->where('celular_clave', $clave)->exists();

            return ['estado' => $enOtra ? self::OTRA_SEDE : self::NUEVO, 'personas' => collect()];
        }

        $disponibles = $personas->filter(fn (Persona $p) => !$p->cuenta && $p->estado !== 'inactivo')->values();

        if ($disponibles->count() > 1) {
            return ['estado' => self::VARIOS, 'personas' => $disponibles];
        }

        if ($disponibles->count() === 1) {
            return ['estado' => self::ENCONTRADO, 'personas' => $disponibles];
        }

        $primera = $personas->first();

        return ['estado' => $primera->cuenta ? self::CON_CUENTA : self::INACTIVO, 'personas' => collect([$primera])];
    }

    /**
     * Crea la cuenta del miembro elegido o, si no existe, crea el miembro y la cuenta juntos. Si algo
     * falla no queda ninguno de los dos.
     *
     * @param  array{celular: string, email: string, role: string}  $cuenta
     * @param  array<string, mixed>|null  $miembro  datos del miembro nuevo (nombres, apellidos, …)
     */
    public function crear(array $cuenta, ?Persona $persona, ?array $miembro, User $actor, ?int $sedeId = null): User
    {
        $busqueda = $this->buscarPorCelular($cuenta['celular'], $actor, $sedeId);

        if ($persona) {
            $elegida = in_array($busqueda['estado'], [self::ENCONTRADO, self::VARIOS], true)
                ? $busqueda['personas']->first(fn (Persona $p) => $p->id === $persona->id)
                : null;

            if (!$elegida) {
                throw ValidationException::withMessages(['persona_id' => 'El miembro elegido no corresponde a ese celular o ya no está disponible. Búscalo de nuevo.']);
            }

            $persona = $elegida;
        } elseif ($busqueda['estado'] !== self::NUEVO) {
            throw ValidationException::withMessages(['celular' => 'Ese celular ya es de un miembro registrado. Búscalo de nuevo para vincularlo.']);
        }

        return DB::transaction(function () use ($cuenta, $persona, $miembro, $actor, $sedeId) {
            $persona ??= $this->crearMiembro($cuenta, $miembro ?? [], $actor, $sedeId);

            if (!filled($persona->email)) {
                $persona->update(['email' => $cuenta['email']]);
            }

            $esAdmin = $cuenta['role'] === 'admin';
            $crear = fn () => User::create([
                'sede_id' => $esAdmin ? null : $persona->sede_id,
                'email' => $cuenta['email'],
                // Contraseña aleatoria que nadie conoce: enseguida se reemplaza por la clave temporal.
                'password' => Str::random(40),
                'role' => $cuenta['role'],
                'persona_id' => $persona->id,
            ]);

            $usuario = $esAdmin ? $this->contexto->sinFiltro($crear) : $this->contexto->conSede($persona->sede_id, $crear);

            return $usuario->setRelation('persona', $persona);
        });
    }

    /** Pasa la cuenta a otro miembro (revisión de vínculos y traslados) y limpia la marca de revisión. */
    public function vincular(User $usuario, Persona $persona, ?User $por = null): User
    {
        return DB::transaction(function () use ($usuario, $persona) {
            $usuario->forceFill([
                'persona_id' => $persona->id,
                'sede_id' => $usuario->esAdmin() ? null : $persona->sede_id,
                'revision_vinculo' => null,
            ])->save();

            return $usuario->setRelation('persona', $persona);
        });
    }

    /**
     * Unifica las cuentas existentes con sus miembros (research D7). Idempotente:
     * - una coincidencia por celular con alguna palabra del nombre en común: se vinculan;
     * - sin coincidencia: se crea el miembro con los datos de la cuenta;
     * - varias coincidencias o un nombre distinto: se crea el miembro y la cuenta queda por revisar.
     * En las cuentas ya vinculadas prevalecen los datos del miembro (la diferencia queda en el historial).
     *
     * @return array{vinculadas: int, creadas: int, por_revisar: int, sedes_sin_pastor: array<int, string>}
     */
    public function unificar(): array
    {
        $resumen = ['vinculadas' => 0, 'creadas' => 0, 'por_revisar' => 0];
        $legado = Schema::hasColumn('users', 'name');
        $sedePorDefecto = Sede::query()->orderBy('id')->value('id');

        app(Historial::class)->comoActor('sistema', 'Sistema', function () use (&$resumen, $legado, $sedePorDefecto) {
            DB::table('users')->orderBy('id')->chunkById(200, function ($filas) use (&$resumen, $legado, $sedePorDefecto) {
                foreach ($filas as $fila) {
                    $nombre = $legado ? trim((string) $fila->name) : '';
                    $celular = $legado ? $fila->celular : null;
                    $usuario = User::withoutGlobalScopes()->find($fila->id);

                    if ($fila->persona_id) {
                        $this->conservarDatosDelMiembro($usuario, $nombre, $celular);

                        continue;
                    }

                    $candidatos = $this->candidatos($celular, $fila->role === 'admin' ? null : $fila->sede_id);

                    if ($candidatos->count() === 1 && $this->compartenNombre($nombre, $candidatos->first()->nombre_completo)) {
                        $this->vincular($usuario, $candidatos->first());
                        $resumen['vinculadas']++;

                        continue;
                    }

                    $sedeId = $fila->role === 'admin' ? $sedePorDefecto : ($fila->sede_id ?? $sedePorDefecto);
                    if (!$sedeId) {
                        continue;
                    }

                    $nuevo = $this->miembroDesdeCuenta($sedeId, $nombre, $celular, $fila->email);
                    $this->vincular($usuario, $nuevo);
                    $resumen['creadas']++;

                    if ($candidatos->isNotEmpty()) {
                        $usuario->forceFill(['revision_vinculo' => [
                            'candidatos' => $candidatos->pluck('id')->all(),
                            'motivo' => $candidatos->count() > 1 ? 'varios' : 'nombre_distinto',
                            'creado_automatico' => $nuevo->id,
                        ]])->save();
                        $resumen['por_revisar']++;
                    }
                }
            });
        });

        return $resumen + ['sedes_sin_pastor' => Sede::query()->whereNull('pastor_persona_id')->orderBy('nombre')->pluck('nombre')->all()];
    }

    /**
     * Cambia el miembro de una cuenta marcada para revisión por uno de sus candidatos. El miembro creado
     * automáticamente se elimina si no tiene datos propios.
     *
     * @return bool verdadero si el miembro creado automáticamente se conservó por tener datos
     */
    public function resolverRevision(User $usuario, Persona $elegido, ?User $por = null): bool
    {
        $revision = $usuario->revision_vinculo ?? [];

        if (!in_array($elegido->id, $revision['candidatos'] ?? [], true) || $elegido->cuenta()->exists()) {
            throw ValidationException::withMessages(['vincular_a' => 'Elige uno de los miembros propuestos que aún no tenga cuenta.']);
        }

        return DB::transaction(function () use ($usuario, $elegido, $revision, $por) {
            $this->vincular($usuario, $elegido, $por);

            $automatico = Persona::withoutGlobalScopes()->find($revision['creado_automatico'] ?? 0);
            if (!$automatico || $automatico->id === $elegido->id) {
                return false;
            }

            if ($automatico->tieneHistorial() || $automatico->cuenta()->exists() || $automatico->es_lider) {
                return true;
            }

            $this->liderazgo->registrarCambio($automatico, 'eliminada', ['estado' => $automatico->estado], null, $por);
            $automatico->delete();

            return false;
        });
    }

    /** @return Collection<int, Persona> miembros sin cuenta con ese celular (en la sede, o en todas si es nula) */
    private function candidatos(?string $celular, ?int $sedeId): Collection
    {
        $clave = Celular::clave($celular);

        if ($clave === null) {
            return collect();
        }

        return Persona::withoutGlobalScopes()->whereNull('deleted_at')->where('celular_clave', $clave)
            ->when($sedeId, fn ($q) => $q->where('sede_id', $sedeId))
            ->whereDoesntHave('cuenta')
            ->orderBy('id')->get();
    }

    /** Verdadero si los dos nombres comparten al menos una palabra (sin tildes ni mayúsculas). */
    private function compartenNombre(string $a, string $b): bool
    {
        $palabras = fn (string $texto) => array_filter(preg_split('/\s+/', mb_strtolower(Str::ascii($texto))), fn ($p) => mb_strlen($p) > 1);

        return array_intersect($palabras($a), $palabras($b)) !== [];
    }

    private function miembroDesdeCuenta(int $sedeId, string $nombre, ?string $celular, string $email): Persona
    {
        $partes = preg_split('/\s+/', $nombre !== '' ? $nombre : 'Sin nombre', 2);

        return $this->contexto->conSede($sedeId, function () use ($sedeId, $partes, $celular, $email) {
            $persona = Persona::create([
                'sede_id' => $sedeId,
                'nombres' => $partes[0],
                'apellidos' => $partes[1] ?? '-',
                'celular' => $celular,
                'email' => $email,
                'estado' => 'miembro_activo',
            ]);
            $this->liderazgo->registrarCambio($persona, 'creada', null, ['estado' => $persona->estado]);

            return $persona;
        });
    }

    /** En una cuenta ya vinculada prevalecen el nombre y el celular del miembro; la diferencia se registra. */
    private function conservarDatosDelMiembro(?User $usuario, string $nombre, ?string $celular): void
    {
        $persona = $usuario?->persona;
        if (!$persona) {
            return;
        }

        $antes = array_filter([
            'name' => $nombre !== '' && $nombre !== $persona->nombre_completo ? $nombre : null,
            'celular' => $celular && Celular::clave($celular) !== $persona->celular_clave ? $celular : null,
        ]);

        if ($antes === [] || CambioHistorial::query()->de('usuario', $usuario->id)->where('accion', 'editado')->where('detalle->unificacion', true)->exists()) {
            return;
        }

        $despues = array_intersect_key(['name' => $persona->nombre_completo, 'celular' => $persona->celular], $antes);
        app(Historial::class)->registrar($usuario, 'editado', $antes, $despues, null, ['unificacion' => true]);
    }

    /**
     * @param  array{celular: string, email: string}  $cuenta
     * @param  array<string, mixed>  $miembro
     */
    private function crearMiembro(array $cuenta, array $miembro, User $actor, ?int $sedeId): Persona
    {
        $datos = array_intersect_key($miembro, array_flip(['nombres', 'apellidos', 'fecha_nacimiento', 'direccion', 'barrio', 'ciudad'])) + [
            'celular' => $cuenta['celular'],
            'email' => $cuenta['email'],
            'estado' => 'miembro_activo',
        ];

        $crear = function () use ($datos, $actor) {
            $persona = Persona::create($datos);
            $this->liderazgo->registrarCambio($persona, 'creada', null, ['estado' => $persona->estado], $actor);

            return $persona;
        };

        return $sedeId ? $this->contexto->conSede($sedeId, $crear) : $crear();
    }
}
