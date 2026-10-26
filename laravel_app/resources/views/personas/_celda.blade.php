{{-- Celda de la grilla de miembros. Variables: $fila (Miembro), $columna (nombre, estado, celular, lider, sede, acciones). --}}
@php($persona = $fila)
@switch($columna)
    @case('nombre')
        <a href="{{ route('personas.show', $persona) }}" class="fw-semibold">{{ $persona->nombre_completo }}</a>
        @if($persona->es_lider)<x-ui.estado valor="lider" />@endif
        @if($persona->email)<small class="text-muted d-block">{{ $persona->email }}</small>@endif
        @break
    @case('estado')
        <x-ui.estado :valor="$persona->estado" :etiqueta="$persona->estado_etiqueta" />
        @break
    @case('celular')
        {{ $persona->celular ?? '—' }}
        @break
    @case('lider')
        @if($persona->lider)
            <a href="{{ route('lideres.show', $persona->lider) }}">{{ $persona->lider->nombre_completo }}</a>
        @else
            <span class="text-muted">—</span>
        @endif
        @break
    @case('nivel')
        <x-niveles.puntos :miembro="$persona" :niveles="$grilla->niveles()" />
        @break
    @case('sede')
        {{ $persona->sede?->nombre ?? '—' }}
        @break
    @case('acciones')
        <x-ui.acciones-fila :ver="route('personas.show', $persona)" :editar="route('personas.edit', $persona)" :puede-editar="\App\Support\Permisos::puede('editar', $persona)" :nombre="$persona->nombre_completo">
            @puede('inactivar', $persona)
                @if($persona->estado !== 'inactivo')
                    <x-form.confirm :action="route('personas.inactivar', $persona)" method="POST" titulo="¿Inactivar a este miembro?" mensaje="Su historial se conserva." boton="Inactivar" :peligro="false" variante="outline-secondary" tamano="sm">Inactivar</x-form.confirm>
                @endif
            @endpuede
        </x-ui.acciones-fila>
        @break
@endswitch
