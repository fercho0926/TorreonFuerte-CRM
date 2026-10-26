{{-- Celda de la grilla de sedes. Variables: $fila (arreglo del resumen: sede, miembros, lideres, celulas), $columna. --}}
@php($sede = $fila['sede'])
@switch($columna)
    @case('sede')
        <a href="{{ route('sedes.show', $sede) }}" class="fw-semibold">{{ $sede->nombre }}</a>
        @break
    @case('ciudad')
        {{ $sede->ciudad }}
        @break
    @case('pastor')
        {{ $sede->pastor?->nombre_completo ?? '—' }}
        @break
    @case('pastora')
        {{ $sede->pastora?->nombre_completo ?? '—' }}
        @break
    @case('personas')
        <x-ui.insignia color="info">{{ $fila['personas'] }}</x-ui.insignia>
        @break
    @case('lideres')
        <x-ui.insignia color="primary">{{ $fila['lideres'] }}</x-ui.insignia>
        @break
    @case('celulas')
        <x-ui.insignia color="secondary">{{ $fila['celulas'] }}</x-ui.insignia>
        @break
    @case('estado')
        <x-ui.estado :valor="$sede->activa ? 'activa' : 'inactiva'" />
        @break
    @case('acciones')
        <x-ui.acciones-fila :ver="route('sedes.show', $sede)" :editar="route('sedes.edit', $sede)" :nombre="$sede->nombre" />
        @break
@endswitch
