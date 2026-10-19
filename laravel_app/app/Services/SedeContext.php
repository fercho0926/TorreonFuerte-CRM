<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\Sede;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Session;

/**
 * Sede efectiva de la petición actual:
 *  - usuario que no es admin: su sede (si no tiene, no ve nada);
 *  - admin: la "sede activa" elegida en la barra superior, o "Todas las sedes" (solo consulta);
 *  - sin usuario: en la web no se ve nada (falla cerrado); en consola (seeders, migraciones,
 *    tinker) no se filtra.
 */
class SedeContext
{
    public const MODO_SEDE = 'sede';

    public const MODO_CERRADO = 'cerrado';

    public const MODO_TODAS = 'todas';

    private bool $modoWeb = false;

    /** @var array<int, int|null> pila de sustituciones: id de sede o null = sin filtro */
    private array $pila = [];

    private ?Sede $sedeEnCache = null;

    public function iniciarPeticionWeb(): void
    {
        $this->modoWeb = true;
    }

    public function terminarPeticionWeb(): void
    {
        $this->modoWeb = false;
    }

    public function estaEnPeticionWeb(): bool
    {
        return $this->modoWeb;
    }

    public function usuario(): ?User
    {
        return Controller::usuarioDeLaPeticion();
    }

    /**
     * @return array{tipo:string, id:?int}
     */
    public function modo(): array
    {
        if ($this->pila) {
            $tope = end($this->pila);

            return $tope === null
                ? ['tipo' => self::MODO_TODAS, 'id' => null]
                : ['tipo' => self::MODO_SEDE, 'id' => $tope];
        }

        $user = $this->usuario();

        if (!$user) {
            return $this->modoWeb
                ? ['tipo' => self::MODO_CERRADO, 'id' => null]
                : ['tipo' => self::MODO_TODAS, 'id' => null];
        }

        if ($user->esAdmin()) {
            $id = Session::get('sede_activa_id');

            return $id
                ? ['tipo' => self::MODO_SEDE, 'id' => (int) $id]
                : ['tipo' => self::MODO_TODAS, 'id' => null];
        }

        return $user->sede_id
            ? ['tipo' => self::MODO_SEDE, 'id' => (int) $user->sede_id]
            : ['tipo' => self::MODO_CERRADO, 'id' => null];
    }

    /**
     * Id de la sede en la que se está trabajando; null si no hay una sede única efectiva.
     */
    public function sedeId(): ?int
    {
        return $this->modo()['id'];
    }

    public function estaEnTodas(): bool
    {
        return $this->modo()['tipo'] === self::MODO_TODAS;
    }

    public function sede(): ?Sede
    {
        $id = $this->sedeId();
        if (!$id) {
            return null;
        }

        if ($this->sedeEnCache?->id !== $id) {
            $this->sedeEnCache = Sede::find($id);
        }

        return $this->sedeEnCache;
    }

    /**
     * Sede del usuario autenticado (o la activa del admin), para mostrarla en la interfaz.
     */
    public function sedeDelUsuario(): ?Sede
    {
        return $this->sede();
    }

    /**
     * Ejecuta un bloque trabajando dentro de una sede concreta (servicios, seeders, migraciones).
     */
    public function conSede(int $sedeId, Closure $callback): mixed
    {
        $this->pila[] = $sedeId;

        try {
            return $callback();
        } finally {
            array_pop($this->pila);
        }
    }

    /**
     * Ejecuta un bloque sin filtro de sede (solo para el módulo Sedes, servicios del admin y
     * resúmenes globales).
     */
    public function sinFiltro(Closure $callback): mixed
    {
        $this->pila[] = null;

        try {
            return $callback();
        } finally {
            array_pop($this->pila);
        }
    }

    /**
     * Indica si el usuario actual puede escribir datos de sede en este momento.
     */
    public function puedeEscribir(): bool
    {
        $modo = $this->modo();

        if ($modo['tipo'] !== self::MODO_SEDE) {
            return false;
        }

        $sede = $this->sede();

        return $sede !== null && $sede->activa;
    }

    /**
     * Sede que se asigna a un registro nuevo. Falla con 403 si en la web no hay una sede única.
     */
    public function sedeParaCrear(bool $obligatoria = true): ?int
    {
        $modo = $this->modo();

        if ($modo['tipo'] === self::MODO_SEDE) {
            return $modo['id'];
        }

        if ($this->modoWeb && $obligatoria) {
            abort(403, 'Elige una sede activa para crear o editar');
        }

        return null;
    }

    /**
     * Regla de validación `exists` limitada a la sede efectiva (la regla `exists` consulta la
     * base directamente y no usa el filtro global de los modelos).
     */
    public function existe(string $tabla, string $columna = 'id'): \Illuminate\Validation\Rules\Exists
    {
        $regla = \Illuminate\Validation\Rule::exists($tabla, $columna);

        return ($id = $this->sedeId()) ? $regla->where('sede_id', $id) : $regla;
    }

    /**
     * Regla de validación `unique` limitada a la sede efectiva.
     */
    public function unico(string $tabla, string $columna): \Illuminate\Validation\Rules\Unique
    {
        $regla = \Illuminate\Validation\Rule::unique($tabla, $columna);

        return ($id = $this->sedeId()) ? $regla->where('sede_id', $id) : $regla;
    }
}
