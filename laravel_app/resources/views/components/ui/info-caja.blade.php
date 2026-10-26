{{-- Info-box de AdminLTE («Dashboard v2»): icono de color, texto y cifra; opcionalmente con barra de progreso y enlace.
     relleno=true pinta toda la caja del color (text-bg-*) en lugar de solo el icono. --}}
@props(['valor', 'texto', 'icono', 'color' => 'info', 'enlace' => null, 'relleno' => false, 'progreso' => null, 'detalle' => null])
<div {{ $attributes->class(['info-box mb-3 position-relative', "text-bg-{$color}" => $relleno]) }}>
    <span class="info-box-icon {{ $relleno ? '' : "text-bg-{$color}" }} shadow-sm"><i class="fas {{ $icono }}" aria-hidden="true"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">
            @if($enlace)
                <a href="{{ $enlace }}" class="stretched-link link-underline-opacity-0 {{ $relleno ? 'link-light' : 'link-body-emphasis' }}">{{ $texto }}</a>
            @else
                {{ $texto }}
            @endif
        </span>
        <span class="info-box-number">{{ $valor }}</span>
        @if($progreso !== null)
            <div class="progress" role="progressbar" aria-label="{{ $texto }}" aria-valuenow="{{ $progreso }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: {{ $progreso }}%"></div></div>
        @endif
        @if($detalle)
            <span class="progress-description">{{ $detalle }}</span>
        @endif
    </div>
</div>
