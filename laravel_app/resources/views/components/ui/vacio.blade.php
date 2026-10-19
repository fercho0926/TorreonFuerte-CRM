{{-- Estado vacío de un listado: mensaje con icono y, opcionalmente, la acción sugerida (contenido). --}}
@props(['mensaje', 'icono' => 'fa-inbox'])
<div class="text-center text-muted py-5">
    <i class="fas {{ $icono }} fa-2x mb-2 d-block" aria-hidden="true"></i>
    <p class="mb-2">{{ $mensaje }}</p>
    @if(trim((string) $slot) !== '')
        <div>{{ $slot }}</div>
    @endif
</div>
