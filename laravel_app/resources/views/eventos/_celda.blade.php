{{-- Celda de la grilla de eventos. Variables: $fila (Evento), $columna. --}}
@php($evento = $fila)
@switch($columna)
    @case('nombre')
        <a href="{{ route('eventos.show', $evento) }}" class="fw-semibold">{{ $evento->nombre }}</a>
        @break
    @case('tipo')
        {{ $evento->tipo }}
        @break
    @case('fecha')
        {{ $evento->fecha->format('Y-m-d') }}
        @break
    @case('lugar')
        {{ $evento->lugar ?? '—' }}
        @break
    @case('costo')
        ${{ number_format($evento->costo, 2) }}
        @break
    @case('estado')
        <x-ui.estado :valor="$evento->estado" />
        @break
    @case('inscritos')
        <x-ui.insignia color="secondary">{{ $evento->inscripciones_count }}</x-ui.insignia>
        @break
    @case('sede')
        {{ $evento->getRelationValue('sede')?->nombre ?? '—' }}
        @break
    @case('acciones')
        <x-ui.acciones-fila :puede-editar="\App\Support\Permisos::modulo('eventos', 'escritura')" :puede-eliminar="\App\Support\Permisos::modulo('eventos', 'escritura')" :ver="route('eventos.show', $evento)" :editar="route('eventos.edit', $evento)" :eliminar="route('eventos.destroy', $evento)" eliminar-titulo="¿Eliminar este evento?" eliminar-mensaje="Se perderá el historial de inscritos y abonos." :nombre="$evento->nombre" />
        @break
@endswitch
