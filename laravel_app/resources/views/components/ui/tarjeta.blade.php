{{--
    Tarjeta de AdminLTE: encabezado con título (e icono y contador opcionales), cuerpo y pie.
    Con tabla="true" el cuerpo no lleva relleno y la tabla se desplaza dentro de la tarjeta (sin
    mover toda la página). Slots opcionales: "acciones" (derecha del encabezado) y "pie".
--}}
@props(['titulo' => null, 'icono' => null, 'contador' => null, 'variante' => null, 'tabla' => false])
<div {{ $attributes->class(['card', 'mb-4', 'card-outline card-'.$variante => $variante]) }}>
    @if($titulo || isset($acciones))
        <div class="card-header">
            <h3 class="card-title">
                @if($icono)<i class="fas {{ $icono }} me-1" aria-hidden="true"></i>@endif{{ $titulo }}
                @if($contador !== null)<small class="text-muted ms-2">{{ $contador }} {{ (int) $contador === 1 ? 'resultado' : 'resultados' }}</small>@endif
            </h3>
            @isset($acciones)
                <div class="card-tools">{{ $acciones }}</div>
            @endisset
        </div>
    @endif
    <div @class(['card-body', 'p-0' => $tabla])>
        @if($tabla)
            <div class="table-responsive">{{ $slot }}</div>
        @else
            {{ $slot }}
        @endif
    </div>
    @isset($pie)
        <div class="card-footer">{{ $pie }}</div>
    @endisset
</div>
