<?php

namespace App\Grillas;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\User;
use App\Services\AlcanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/** Personas esperando un líder: nombre, contacto y el formulario por fila para asignarles uno. */
class SinLiderGrilla extends Grilla
{
    private ?array $opcionesLider = null;

    public function __construct(private AlcanceService $alcance)
    {
    }

    public function id(): string
    {
        return 'sin-lider';
    }

    public function vistaCelda(): string
    {
        return 'sin-lider._celda';
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay personas sin líder.', 'icono' => 'fa-user-check'];
    }

    public function puedeVer(?User $usuario): bool
    {
        return $usuario && Gate::forUser($usuario)->allows('verSinLider', Persona::class);
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Persona')
                ->ordena(fn (Builder $q, string $dir) => $q->orderBy('personas.apellidos', $dir)->orderBy('personas.nombres', $dir))
                ->filtraTexto(fn (Builder $q, string $valor) => $q->buscar($valor))
                ->valor(fn (Persona $p) => $p->nombre_completo)
                ->prioridad(1),
            Columna::make('estado', 'Estado')
                ->ordena('personas.estado')
                ->filtraLista(collect(Persona::ESTADOS)->except(['inactivo', 'celula'])->all(), 'personas.estado')
                ->valor(fn (Persona $p) => $p->estado_etiqueta)
                ->prioridad(4),
            Columna::make('celular', 'Contacto')
                ->ordena('personas.celular')
                ->filtraTexto(fn (Builder $q, string $valor) => $q->where('personas.celular', 'like', '%'.preg_replace('/\D+/', '', $valor).'%'))
                ->valor(fn (Persona $p) => $p->celular)
                ->prioridad(3),
            Columna::make('asignar', 'Asignar líder')->prioridad(2)->sinExportar(),
        ];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return $this->alcance->personasVisibles($usuario)->sinLider();
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        return $consulta->buscar($q);
    }

    /** Líderes entre los que se puede elegir (los visibles para quien consulta); se calcula una vez. */
    public function opcionesLider(): array
    {
        return $this->opcionesLider ??= $this->alcance->lideresVisibles(Controller::usuarioDeLaPeticion())
            ->orderBy('apellidos')->orderBy('nombres')->get()
            ->map(fn ($l) => ['id' => $l->id, 'texto' => $l->nombre_completo, 'apoyo' => $l->numero_documento ? 'Doc. '.$l->numero_documento : null])
            ->all();
    }
}
