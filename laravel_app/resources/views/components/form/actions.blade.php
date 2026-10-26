@props(['guardar' => 'Guardar', 'cancelar' => null, 'modo' => 'completo', 'boton' => 'primary', 'metodo' => 'POST', 'icono' => null, 'deshabilitar' => false, 'global' => false])
@php
    // En "Todas las sedes" el administrador solo consulta: los formularios que escriben no se pueden enviar,
    // salvo los de datos globales (que no pertenecen a una sede, como los roles).
    $esEscritura = $metodo !== 'GET' && !$global;
@endphp
<div class="fm-acciones" data-acciones>
    @if($esEscritura)
        @todasLasSedes
            <span class="fm-acciones__nota text-warning" role="status"><i class="fas fa-eye"></i> Todas las sedes: solo consulta. Elige una sede activa para guardar.</span>
        @endtodasLasSedes
    @endif
    @if($cancelar)
        <a href="{{ $cancelar }}" class="btn btn-outline-secondary">Cancelar</a>
    @endif
    @if($guardar)
        @if($esEscritura)
            @todasLasSedes
                <button type="submit" class="btn btn-{{ $boton }}" disabled data-boton-principal>{{ $guardar }}</button>
            @else
                <button type="submit" class="btn btn-{{ $boton }}" data-boton-principal @disabled($deshabilitar)>@if($icono)<i class="fas {{ $icono }} me-1"></i>@endif{{ $guardar }}</button>
            @endtodasLasSedes
        @else
            <button type="submit" class="btn btn-{{ $boton }}" data-boton-principal @disabled($deshabilitar)>@if($icono)<i class="fas {{ $icono }} me-1"></i>@endif{{ $guardar }}</button>
        @endif
    @endif
</div>
