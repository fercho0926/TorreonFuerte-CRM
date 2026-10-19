<?php

namespace App\Grillas;

use App\Models\Persona;
use App\Models\User;
use App\Services\AlcanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Listado de personas: nombre, estado, celular, líder, sede (solo en «Todas las sedes») y acciones. */
class PersonasGrilla extends Grilla
{
    public function __construct(private AlcanceService $alcance)
    {
    }

    public function id(): string
    {
        return 'personas';
    }

    public function extras(): array
    {
        return ['inactivos', 'lider_id'];
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function puedeVer(?User $usuario): bool
    {
        return (bool) $usuario;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No se encontraron personas.', 'icono' => 'fa-address-card'];
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Nombre')
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy('personas.apellidos', $dir)->orderBy('personas.nombres', $dir))
                ->filtraTexto(fn (Builder $q, string $valor) => $q->buscar($valor))
                ->valor(fn (Persona $p) => $p->nombre_completo)
                ->prioridad(1),
            Columna::make('estado', 'Estado')
                ->ordena('personas.estado')
                ->filtraLista(Persona::ESTADOS, 'personas.estado')
                ->valor(fn (Persona $p) => $p->estado_etiqueta)
                ->prioridad(2),
            Columna::make('celular', 'Celular')
                ->ordena('personas.celular')
                ->filtraTexto(fn (Builder $q, string $valor) => $q->where('personas.celular', 'like', '%'.preg_replace('/\D+/', '', $valor).'%'))
                ->valor(fn (Persona $p) => $p->celular)
                ->prioridad(3),
            Columna::make('correo', 'Correo')->valor(fn (Persona $p) => $p->email)->soloExportar(),
            Columna::make('lider', 'Líder')
                ->ordenaPersona('personas', 'lider_id')
                ->filtraPersona('lider')
                ->valor(fn (Persona $p) => $p->lider?->nombre_completo)
                ->prioridad(4),
            Columna::sede('personas'),
            Columna::acciones(),
        ];
    }

    public function ordenInicial(): array
    {
        return ['nombre', 'asc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        $incluirInactivos = !empty($estado['inactivos']) || ($estado['estado'] ?? '') === 'inactivo';

        return $this->alcance->personasVisibles($usuario)
            ->with(['lider', 'sede'])
            ->when(!$incluirInactivos, fn ($q) => $q->activas())
            ->when(filled($estado['lider_id'] ?? null), fn ($q) => $q->where('personas.lider_id', $estado['lider_id']));
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        return $consulta->buscar($q);
    }
}
