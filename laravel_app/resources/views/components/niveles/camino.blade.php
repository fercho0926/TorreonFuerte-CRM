{{--
    Camino de niveles de un miembro (spec 014): estaciones conectadas por un sendero, como un mapa de juego.
    Variable: $miembro (el miembro, modelo AppModelsPersona). El estado se comunica con texto, forma e icono (no solo con color); funciona sin JavaScript.
--}}
@props(['miembro'])
@php
    $servicio = app(\App\Services\CaminoService::class);
    $camino = $servicio->camino($miembro);
    $forma = $servicio->geometria($camino['total']);
    $escribe = \App\Support\Permisos::modulo('niveles', 'escritura');
    $pendientes = collect($camino['estaciones'])->filter(fn ($e) => $e['estado'] !== 'completado')->map(fn ($e) => ['id' => $e['nivel']->id, 'texto' => $e['numero'].' · '.$e['nivel']->nombre])->values()->all();
    $icono = ['completado' => 'fa-star', 'siguiente' => 'fa-location-dot', 'pendiente' => 'fa-lock'];
@endphp
<section class="camino" aria-labelledby="camino-titulo" data-estaciones="{{ $camino['total'] }}">
    <h3 id="camino-titulo" class="camino-titulo">Camino de niveles</h3>

    @if($camino['total'] === 0)
        <p class="camino-resumen text-muted">
            El plan de niveles aún no tiene niveles.
            @puedeModulo('niveles', 'escritura')<a href="{{ route('niveles.create') }}">Crear el primero</a>@endpuedeModulo
        </p>
    @else
        <p class="camino-resumen">
            <strong>{{ $camino['completados'] }} de {{ $camino['total'] }} niveles</strong>
            @if($camino['terminado'])
                · <span class="camino-fin"><i class="fas fa-trophy" aria-hidden="true"></i> ¡Camino terminado!</span>
            @elseif($camino['actual'])
                · Nivel actual: {{ $camino['actual']->nombre }}
            @else
                · Aún no inicia su camino
            @endif
        </p>

        <div class="camino-mapa" style="--alto: {{ $forma['alto'] }}px">
            <svg class="camino-sendero" viewBox="0 0 100 {{ $forma['alto'] }}" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path d="{{ $forma['sendero'] }}" class="camino-sendero-base" vector-effect="non-scaling-stroke" />
            </svg>
            <ol class="camino-estaciones" aria-label="Camino de niveles de {{ $miembro->nombre_completo }}">
                @foreach($camino['estaciones'] as $i => $e)
                    @php
                        $punto = $forma['puntos'][$i];
                        $lado = $punto['x'] < 40 ? 'izq' : ($punto['x'] > 60 ? 'der' : 'centro');
                    @endphp
                    <li class="estacion estacion--{{ $e['estado'] }} estacion--{{ $lado }}" style="--x: {{ $punto['x'] }}%; --y: {{ $punto['y'] }}px">
                        <span class="estacion-circulo" aria-hidden="true">
                            <span class="estacion-numero">{{ $e['numero'] }}</span>
                            <i class="fas {{ $icono[$e['estado']] }} estacion-sello"></i>
                        </span>
                        <div class="estacion-texto">
                            <strong class="estacion-nombre">{{ $e['nivel']->nombre }}</strong>
                            <span class="estacion-estado">
                                @if($e['estado'] === 'completado')
                                    Completado el {{ $e['fecha']->locale('es')->translatedFormat('j \d\e F \d\e Y') }}
                                @elseif($e['estado'] === 'siguiente')
                                    Siguiente{{ $e['saltado'] ? ' (saltado)' : '' }}
                                @else
                                    Pendiente{{ $e['saltado'] ? ' (saltado)' : '' }}
                                @endif
                            </span>
                            @if($e['estado'] === 'completado' || $e['nivel']->descripcion)
                                <details class="estacion-detalle">
                                    <summary>Detalle</summary>
                                    <div class="estacion-detalle-cuerpo">
                                        @if($e['nivel']->descripcion)<p class="mb-1">{{ $e['nivel']->descripcion }}</p>@endif
                                        @if($e['avance'])
                                            <p class="mb-1">
                                                @if($e['origen'] === 'encuentro')
                                                    Origen: encuentro{{ $e['encuentro'] ? ' «'.$e['encuentro']->nombre.'»' : '' }}
                                                @else
                                                    Origen: registro manual{{ $e['registrado_por'] ? ' de '.$e['registrado_por']->name : '' }}
                                                @endif
                                            </p>
                                            @if($e['avance']->nota)<p class="mb-1 text-muted">{{ $e['avance']->nota }}</p>@endif
                                            @if($escribe && $e['avance']->esManual())
                                                <x-form.confirm :action="route('niveles.avances.destroy', $e['avance'])" method="DELETE" titulo="¿Quitar este nivel?" :mensaje="'Se retira «'.$e['nivel']->nombre.'» del camino de '.$miembro->nombre_completo.'.'" boton="Quitar" :peligro="true" variante="outline-danger" tamano="sm">Quitar</x-form.confirm>
                                            @endif
                                        @endif
                                    </div>
                                </details>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        @if($escribe && count($pendientes))
            <details class="camino-registrar">
                <summary>Registrar avance</summary>
                <x-form.layout :action="route('niveles.avances.store')" modo="linea" guardar="Marcar completado" icono="fa-check" :leyenda="false" :id="'avance-'.$miembro->id">
                    <input type="hidden" name="persona_id" value="{{ $miembro->id }}">
                    <x-form.select name="nivel_id" label="Nivel" icono="fa-route" :opciones="$pendientes" :value="$camino['siguiente']?->id" :vacio="null" :required="true" col="col-md-5" />
                    <x-form.date name="fecha" label="Fecha" :value="now()->toDateString()" max="hoy" :required="true" col="col-md-3" />
                    <x-form.input name="nota" label="Nota (opcional)" icono="fa-pen" maxlength="255" col="col-md-4" />
                </x-form.layout>
            </details>
        @endif
    @endif
</section>
