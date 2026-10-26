{{-- Celda de la grilla de miembros por nivel. Variables: $fila (Miembro), $columna, $grilla. --}}
@php($persona = $fila)
@switch($columna)
    @case('nombre')
        <a href="{{ route('personas.show', $persona) }}" class="fw-semibold">{{ $persona->nombre_completo }}</a>
        @if($persona->es_lider)<x-ui.estado valor="lider" />@endif
        @break
    @case('nivel')
        <x-niveles.puntos :miembro="$persona" :niveles="$grilla->niveles()" />
        @break
    @case('celular')
        <div class="small text-muted mb-1">{{ $persona->celular ?? 'sin celular' }}</div>
        @include('partials.acciones-contacto', ['persona' => $persona])
        @break
    @case('completo')
        @php($fecha = $grilla->fechaDelNivel($persona))
        {{ $fecha ? $fecha->locale('es')->translatedFormat('j \d\e F \d\e Y') : '—' }}
        @break
    @case('sede')
        {{ $persona->sede?->nombre ?? '—' }}
        @break
    @case('acciones')
        <x-ui.acciones-fila :ver="route('personas.show', $persona)" :nombre="$persona->nombre_completo" />
        @break
@endswitch
