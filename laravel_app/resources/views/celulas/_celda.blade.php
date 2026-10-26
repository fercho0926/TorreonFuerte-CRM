{{-- Celda de la grilla de células. Variables: $fila (Celula), $columna. --}}
@php($celula = $fila)
@switch($columna)
    @case('nombre')
        <a href="{{ route('celulas.show', $celula) }}" class="fw-semibold">{{ $celula->nombre }}</a>
        @break
    @case('lider')
        {{ $celula->lider?->nombre_completo ?? '—' }}
        @break
    @case('colider')
        {{ $celula->coLider?->nombre_completo ?? '—' }}
        @break
    @case('reunion')
        {{ $celula->dia_reunion ?? '—' }} {{ $celula->hora_reunion ? substr($celula->hora_reunion, 0, 5) : '' }}
        @break
    @case('estado')
        <x-ui.estado :valor="$celula->estado" />
        @break
    @case('miembros')
        <x-ui.insignia color="info">{{ $celula->miembros_count }}</x-ui.insignia>
        @break
    @case('sede')
        {{ $celula->getRelationValue('sede')?->nombre ?? '—' }}
        @break
    @case('acciones')
        @php($gestiona = \App\Http\Controllers\Controller::usuarioDeLaPeticion()?->tieneAccesoTotal() && \App\Support\Permisos::modulo('celulas', 'escritura'))
        <x-ui.acciones-fila :ver="route('celulas.show', $celula)" :editar="route('celulas.edit', $celula)" :eliminar="route('celulas.destroy', $celula)" :puede-editar="$gestiona" :puede-eliminar="$gestiona" eliminar-titulo="¿Eliminar esta célula?" :nombre="$celula->nombre" />
        @break
@endswitch
