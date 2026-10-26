{{-- Lista paginada de entradas del historial (tarjetas apiladas). Props: entradas (paginador), vacio (mensaje). --}}
@props(['entradas', 'vacio' => 'Sin movimientos.'])
@if($entradas->isEmpty())
    <p class="text-muted mb-0 py-2">{{ $vacio }}</p>
@else
    <div class="historial-lista">
        @foreach($entradas as $cambio)
            <x-historial.entrada :cambio="$cambio" />
        @endforeach
    </div>
    @if($entradas->hasPages())
        <div class="mt-3">{{ $entradas->links() }}</div>
    @endif
@endif
