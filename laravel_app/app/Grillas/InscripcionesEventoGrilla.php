<?php

namespace App\Grillas;

use App\Models\CambioHistorial;
use App\Models\Evento;
use App\Models\EventoPersona;
use App\Models\User;
use App\Services\AlcanceService;
use App\Support\Modulos;
use App\Support\Niveles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Miembros inscritos de un evento, con su control de pagos (spec 019): nombre, costo aplicable,
 * total pagado, saldo pendiente y estado. Siempre se filtra por un evento (`extras(): ['evento']`),
 * fijado por el controlador a partir de la ruta — nunca por un valor que el cliente pueda elegir.
 */
class InscripcionesEventoGrilla extends Grilla
{
    private ?Collection $registradosPorQr = null;

    private int $eventoIdActual = 0;

    /** @var Collection<int, Collection<int, CambioHistorial>>|null agrupado por persona_id */
    private ?Collection $logsPorPersona = null;

    public function __construct(private AlcanceService $alcance)
    {
    }

    public function id(): string
    {
        return 'eventos.inscripciones';
    }

    public function archivo(): string
    {
        return 'miembros-inscritos';
    }

    public function extras(): array
    {
        return ['evento'];
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'Este evento aún no tiene miembros inscritos.', 'icono' => 'fa-user-friends'];
    }

    public function puedeVer(?User $usuario): bool
    {
        return (bool) $usuario && $usuario->nivelEn(Modulos::EVENTOS) >= Niveles::LECTURA;
    }

    /** ¿Este miembro también se registró por el código QR del evento? (para la insignia «QR»). */
    public function seRegistroPorQr(EventoPersona $inscripcion): bool
    {
        return $this->registradosPorQr?->contains($inscripcion->persona_id) ?? false;
    }

    /**
     * Trazabilidad de este miembro dentro de este evento: inscripción, cambios de costo, abonos registrados y
     * eliminados, con quién y cuándo (spec 019). Son las mismas entradas que ya arma `EventoPersona`/`EventoPago`
     * hacia el historial único de la plataforma; aquí solo se filtran por este miembro, no por todo el evento.
     *
     * @return Collection<int, CambioHistorial>
     */
    public function logsDe(EventoPersona $inscripcion): Collection
    {
        return $this->logsPorPersona?->get($inscripcion->persona_id) ?? collect();
    }

    /** Precarga en un solo lote los logs de todos los miembros de la página actual (evita N+1). */
    public function filas(iterable $filas, ?User $usuario): array
    {
        $items = is_array($filas) ? $filas : iterator_to_array($filas);
        $personaIds = collect($items)->pluck('persona_id')->unique()->values()->all();

        $this->logsPorPersona = $personaIds === [] ? collect() : CambioHistorial::query()
            ->de('evento', $this->eventoIdActual)
            ->where('relacionado_tipo', 'persona')
            ->whereIn('relacionado_id', $personaIds)
            ->recientes()
            ->get()
            ->groupBy('relacionado_id');

        return parent::filas($items, $usuario);
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Miembro')
                ->ordenaPersona('evento_persona', 'persona_id')
                ->filtraPersona('persona')
                ->valor(fn (EventoPersona $i) => $i->persona->nombre_completo)
                ->prioridad(1),
            Columna::make('costo', 'Costo aplicable')
                ->valor(fn (EventoPersona $i) => number_format($i->costoAplicable(), 2, '.', ''))
                ->prioridad(4),
            Columna::make('pagado', 'Total pagado')
                ->valor(fn (EventoPersona $i) => number_format($i->totalPagado(), 2, '.', ''))
                ->prioridad(4),
            Columna::make('saldo', 'Saldo pendiente')
                ->valor(fn (EventoPersona $i) => number_format($i->saldoPendiente(), 2, '.', ''))
                ->prioridad(3),
            Columna::make('estado', 'Estado')
                ->valor(fn (EventoPersona $i) => $i->estaPagado() ? 'Pagado' : 'Pendiente')
                ->prioridad(2),
            Columna::acciones(),
        ];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        $eventoId = (int) ($estado['evento'] ?? 0);
        $this->eventoIdActual = $eventoId;
        $evento = Evento::query()->find($eventoId);

        $this->registradosPorQr = $evento
            ? $evento->registrosQr()->whereNotNull('persona_id')->pluck('persona_id')
            : collect();

        $visibles = $usuario && !$usuario->tieneAccesoTotal()
            ? $this->alcance->personasVisibles($usuario)->pluck('personas.id')->all()
            : null;

        return EventoPersona::query()
            ->where('evento_id', $eventoId)
            ->with(['persona', 'pagos' => fn ($q) => $q->orderByDesc('fecha_pago')])
            ->when($visibles !== null, fn ($q) => $q->whereIn('persona_id', $visibles));
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        return $consulta->whereHas('persona', fn ($p) => $p->buscar($q));
    }
}
