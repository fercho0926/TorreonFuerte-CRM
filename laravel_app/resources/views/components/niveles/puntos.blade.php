{{--
    Progreso resumido de un miembro: un punto por nivel activo (completados rellenos) y el nombre del nivel actual.
    Variable: $miembro (el miembro; conviene con `avances` precargados para no consultar por fila).
--}}
@props(['miembro', 'niveles' => null])
@php
    $camino = app(\App\Services\CaminoService::class)->camino($miembro, $niveles, false);
    $actual = $camino['actual']?->nombre;
    $etiqueta = $camino['completados'].' de '.$camino['total'].' niveles; '.($actual ? 'actual: '.$actual : 'sin iniciar');
@endphp
<span class="puntos-progreso">
    <span class="puntos" role="img" aria-label="{{ $etiqueta }}">
        @foreach($camino['estaciones'] as $e)
            <span class="punto {{ $e['estado'] === 'completado' ? 'punto--completado' : 'punto--pendiente' }}"></span>
        @endforeach
    </span>
    <span class="puntos-texto">{{ $actual ?? 'Sin iniciar' }}</span>
</span>
