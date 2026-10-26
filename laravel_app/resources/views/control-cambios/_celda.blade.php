{{-- Celda de la grilla «Control de cambios» (spec 015). Variables: $fila (CambioHistorial), $columna. Sin acciones: el historial no se edita. --}}
@php($cambio = $fila)
@switch($columna)
    @case('fecha')
        <time datetime="{{ $cambio->created_at->toIso8601String() }}">{{ $cambio->created_at->locale('es')->translatedFormat('j M Y, g:i a') }}</time>
        @break
    @case('usuario')
        {{ $cambio->actor_nombre }}
        @break
    @case('sede')
        {{ $cambio->sede_id ? app(\App\Services\LecturaHistorial::class)->nombreSede((int) $cambio->sede_id) : '—' }}
        @break
    @case('modulo')
        {{ \App\Support\AccionesHistorial::etiquetaModulo($cambio->modulo) }}
        @break
    @case('accion')
        <i class="fas {{ $cambio->icono }} me-1" aria-hidden="true"></i>{{ $cambio->etiqueta }}
        @break
    @case('registro')
        @php($lectura = app(\App\Services\LecturaHistorial::class))
        <span>
            <small class="text-muted d-block">{{ \App\Support\AccionesHistorial::etiquetaTipo($cambio->sujeto_tipo) }}</small>
            @if($lectura->puedeEnlazar(\App\Http\Controllers\Controller::usuarioDeLaPeticion(), $cambio->sujeto_tipo, $cambio->sujeto_id))
                <a href="{{ $lectura->ruta($cambio->sujeto_tipo, $cambio->sujeto_id, true) }}">{{ $cambio->sujeto_nombre }}</a>
            @else
                {{ $cambio->sujeto_nombre }}
            @endif
        </span>
        @break
    @case('detalle')
        <details class="historial-largo"><summary>Ver detalle</summary>
            <x-historial.entrada :cambio="$cambio" />
        </details>
        @break
@endswitch
