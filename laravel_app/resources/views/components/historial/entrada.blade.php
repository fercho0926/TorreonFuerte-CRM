{{--
    Una entrada del historial (spec 015): fecha y hora, acción con icono y texto, quién la hizo, registro relacionado y
    el detalle «Campo: antes → después». No tiene acciones de editar ni borrar: el historial es inalterable.
    Props: cambio (CambioHistorial), resumen (true en la grilla «Control de cambios»: muestra también el registro).
--}}
@props(['cambio', 'resumen' => false])
@php
    $lectura = app(\App\Services\LecturaHistorial::class);
    $usuario = \App\Http\Controllers\Controller::usuarioDeLaPeticion();
    $detalle = $cambio->detalle ?? [];
    $sedeActiva = app(\App\Services\SedeContext::class)->sedeId();
    $enlace = fn (?string $tipo, ?int $id) => $lectura->puedeEnlazar($usuario, $tipo, $id) ? $lectura->ruta($tipo, $id) : null;
    $nombreSede = $cambio->sede_id && $sedeActiva && (int) $cambio->sede_id !== $sedeActiva
        ? $lectura->nombreSede((int) $cambio->sede_id) : null;
    $fechaEvento = isset($detalle['fecha_evento'])
        ? \Illuminate\Support\Carbon::parse($detalle['fecha_evento'])->locale('es')->translatedFormat('j M Y') : null;
@endphp
<article class="historial-entrada" data-accion="{{ $cambio->accion }}">
    <div class="historial-entrada-cabeza">
        <span class="historial-accion"><i class="fas {{ $cambio->icono }} me-1" aria-hidden="true"></i>{{ $cambio->etiqueta }}</span>
        <time class="historial-fecha" datetime="{{ $cambio->created_at->toIso8601String() }}">{{ $cambio->created_at->locale('es')->translatedFormat('j M Y, g:i a') }}</time>
    </div>

    @if($resumen)
        <p class="historial-registro mb-1">
            {{ \App\Support\AccionesHistorial::etiquetaTipo($cambio->sujeto_tipo) }}:
            @if($url = $enlace($cambio->sujeto_tipo, $cambio->sujeto_id))
                <a href="{{ $lectura->ruta($cambio->sujeto_tipo, $cambio->sujeto_id, true) }}">{{ $cambio->sujeto_nombre }}</a>
            @else
                <strong>{{ $cambio->sujeto_nombre }}</strong>
            @endif
        </p>
    @endif

    <p class="historial-actor mb-1"><i class="fas fa-user-pen me-1" aria-hidden="true"></i>{{ $cambio->actor_nombre }}</p>

    @if($cambio->relacionado_nombre)
        <p class="historial-relacionado mb-1">
            {{ \App\Support\AccionesHistorial::etiquetaTipo((string) $cambio->relacionado_tipo) }}:
            @if($url = $enlace($cambio->relacionado_tipo, $cambio->relacionado_id))
                <a href="{{ $url }}">{{ $cambio->relacionado_nombre }}</a>
            @else
                <strong>{{ $cambio->relacionado_nombre }}</strong>
            @endif
            @if($fechaEvento) <span class="text-muted">· {{ $fechaEvento }}</span>@endif
        </p>
    @elseif($fechaEvento)
        <p class="historial-relacionado mb-1 text-muted">Fecha: {{ $fechaEvento }}</p>
    @endif

    @if($cambio->cambios)
        <ul class="historial-cambios">
            @foreach($cambio->cambios as $campo)
                <li>
                    <span class="historial-campo">{{ $campo['etiqueta'] }}:</span>
                    @if($campo['secreto'] ?? false)
                        <span>Clave cambiada</span>
                    @elseif(!$lectura->puedeVerSensible($usuario, $campo['modulo_sensible'] ?? null))
                        <span>—</span>
                    @elseif($campo['solo'] ?? false)
                        <span class="historial-despues">{{ $campo['despues'] }}</span>
                    @elseif($campo['largo'] ?? false)
                        <details class="historial-largo"><summary>Ver texto anterior y nuevo</summary>
                            <p class="mb-1"><strong>Antes:</strong> {{ $campo['antes'] }}</p>
                            <p class="mb-0"><strong>Después:</strong> {{ $campo['despues'] }}</p>
                        </details>
                    @else
                        <span class="historial-antes">{{ $campo['antes'] }}</span>
                        <span aria-label="cambió a"> → </span>
                        <span class="historial-despues">{{ $campo['despues'] }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if(!empty($detalle['lineas']))
        <ul class="historial-cambios">
            @foreach($detalle['lineas'] as $linea)<li>{{ $linea }}</li>@endforeach
        </ul>
    @endif

    @if(!empty($detalle['agregados']) || !empty($detalle['quitados']))
        @php([$tituloAgregados, $tituloQuitados] = $detalle['etiquetas'] ?? ['Agregados', 'Quitados'])
        <ul class="historial-cambios">
            @if(!empty($detalle['agregados']))
                <li><span class="historial-campo">{{ $tituloAgregados }} ({{ count($detalle['agregados']) }}):</span> {{ collect($detalle['agregados'])->pluck('nombre')->join(', ') }}</li>
            @endif
            @if(!empty($detalle['quitados']))
                <li><span class="historial-campo">{{ $tituloQuitados }} ({{ count($detalle['quitados']) }}):</span> {{ collect($detalle['quitados'])->pluck('nombre')->join(', ') }}</li>
            @endif
        </ul>
    @endif

    @if(!empty($detalle['datos']) && in_array($cambio->accion, ['eliminado', 'eliminada'], true))
        <details class="historial-largo"><summary>Datos que tenía</summary>
            <ul class="historial-cambios mb-0">
                @foreach($detalle['datos'] as $etiqueta => $valor)<li><span class="historial-campo">{{ $etiqueta }}:</span> {{ $valor }}</li>@endforeach
            </ul>
        </details>
    @endif

    @if($cambio->es_legado && !$cambio->cambios && empty($detalle['lineas']) && ($cambio->despues || $cambio->antes))
        <p class="historial-legado small text-muted mb-0">
            @foreach(($cambio->despues ?? $cambio->antes ?? []) as $clave => $valor)
                @continue(is_array($valor))
                <span class="me-2">{{ str_replace('_', ' ', $clave) }}: {{ is_bool($valor) ? ($valor ? 'sí' : 'no') : ($valor ?? '(vacío)') }}</span>
            @endforeach
        </p>
    @endif

    @if($nombreSede)
        <p class="historial-sede small text-muted mb-0"><i class="fas fa-church me-1" aria-hidden="true"></i>Ocurrió en la sede {{ $nombreSede }}</p>
    @endif
</article>
