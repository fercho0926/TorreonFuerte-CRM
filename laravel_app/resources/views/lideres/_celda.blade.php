{{-- Celda de la grilla de líderes. Variables: $fila (Miembro), $columna. --}}
@php($lider = $fila)
@switch($columna)
    @case('nombre')
        <a href="{{ route('lideres.show', $lider) }}" class="fw-semibold">{{ $lider->nombre_completo }}</a>
        @break
    @case('personas')
        <x-ui.insignia color="info">{{ $lider->miembros_count }}</x-ui.insignia>
        @break
    @case('celulas')
        <x-ui.insignia color="secondary">{{ $lider->celulas_lideradas_count }}</x-ui.insignia>
        @break
    @case('celular')
        {{ $lider->celular ?? '—' }}
        @break
    @case('sede')
        {{ $lider->sede?->nombre ?? '—' }}
        @break
    @case('acciones')
        <x-ui.acciones-fila :ver="route('lideres.show', $lider)" :editar="route('personas.edit', $lider)" :puede-editar="\App\Support\Permisos::puede('designarLider')" :nombre="$lider->nombre_completo">
            @puede('designarLider')
                <a href="{{ route('lideres.quitar', $lider) }}" class="btn btn-sm btn-outline-danger" title="Quitar rol" aria-label="Quitar rol de {{ $lider->nombre_completo }}"><i class="fas fa-user-minus" aria-hidden="true"></i></a>
            @endpuede
        </x-ui.acciones-fila>
        @break
@endswitch
