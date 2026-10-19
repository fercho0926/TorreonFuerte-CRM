{{-- Celda de la grilla de ministerios. Variables: $fila (Ministerio), $columna. --}}
@php($ministerio = $fila)
@switch($columna)
    @case('nombre')
        <a href="{{ route('ministerios.show', $ministerio) }}" class="fw-semibold">{{ $ministerio->nombre }}</a>
        @break
    @case('encargado')
        {{ $ministerio->encargado?->nombre_completo ?? '—' }}
        @break
    @case('estado')
        <x-ui.estado :valor="$ministerio->estado" />
        @break
    @case('integrantes')
        <x-ui.insignia color="info">{{ $ministerio->integrantes_count }}</x-ui.insignia>
        @break
    @case('sede')
        {{ $ministerio->getRelationValue('sede')?->nombre ?? '—' }}
        @break
    @case('acciones')
        <x-ui.acciones-fila :ver="route('ministerios.show', $ministerio)" :editar="route('ministerios.edit', $ministerio)" :eliminar="route('ministerios.destroy', $ministerio)" eliminar-titulo="¿Eliminar este ministerio?" eliminar-mensaje="Esta acción no se puede deshacer." :nombre="$ministerio->nombre" />
        @break
@endswitch
