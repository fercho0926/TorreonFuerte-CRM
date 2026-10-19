{{-- Caja de resumen de AdminLTE: cifra, texto, icono y, opcionalmente, un enlace «Ver más». --}}
@props(['valor', 'texto', 'icono', 'color' => 'info', 'enlace' => null, 'etiquetaEnlace' => 'Ver más'])
<div class="small-box text-bg-{{ $color }}">
    <div class="inner">
        <h3>{{ $valor }}</h3>
        <p>{{ $texto }}</p>
    </div>
    <i class="fas {{ $icono }} small-box-icon" aria-hidden="true"></i>
    @if($enlace)
        <a href="{{ $enlace }}" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-75-hover">{{ $etiquetaEnlace }} <i class="fas fa-arrow-circle-right" aria-hidden="true"></i></a>
    @endif
</div>
