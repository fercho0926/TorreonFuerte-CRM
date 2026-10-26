{{-- Celda de la grilla de encuentros. Variables: $fila (Encuentro), $columna. --}}
@php($encuentro = $fila)
@switch($columna)
    @case('nombre')
        <a href="{{ route('encuentros.show', $encuentro) }}" class="fw-semibold">{{ $encuentro->nombre }}</a>
        @break
    @case('fecha')
        {{ $encuentro->fecha_realizacion->format('Y-m-d') }}
        @break
    @case('nivel')
        @if($encuentro->nivel)<x-ui.insignia color="primary">{{ $encuentro->nivel->posicion }} · {{ $encuentro->nivel->nombre }}</x-ui.insignia>@else<span class="text-muted">—</span>@endif
        @break
    @case('lugar')
        {{ $encuentro->lugar ?? '—' }}
        @break
    @case('valor')
        ${{ number_format($encuentro->valor, 2) }}
        @break
    @case('estado')
        <x-ui.estado :valor="$encuentro->estado" />
        @break
    @case('participantes')
        <x-ui.insignia color="secondary">{{ $encuentro->personas_count }}</x-ui.insignia>
        @break
    @case('sede')
        {{ $encuentro->getRelationValue('sede')?->nombre ?? '—' }}
        @break
    @case('acciones')
        <x-ui.acciones-fila :puede-editar="\App\Support\Permisos::modulo('encuentros', 'escritura')" :puede-eliminar="\App\Support\Permisos::modulo('encuentros', 'escritura')" :ver="route('encuentros.show', $encuentro)" :editar="route('encuentros.edit', $encuentro)" :eliminar="route('encuentros.destroy', $encuentro)" eliminar-titulo="¿Eliminar este encuentro?" eliminar-mensaje="Esta acción no se puede deshacer." :nombre="$encuentro->nombre" />
        @break
@endswitch
