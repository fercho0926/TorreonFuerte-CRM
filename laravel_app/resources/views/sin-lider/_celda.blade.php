{{-- Celda de la grilla «sin líder». Variables: $fila (Miembro), $columna, $grilla. --}}
@php($persona = $fila)
@switch($columna)
    @case('nombre')
        <a href="{{ route('personas.show', $persona) }}" class="fw-semibold">{{ $persona->nombre_completo }}</a>
        @break
    @case('estado')
        <x-ui.estado :valor="$persona->estado" :etiqueta="$persona->estado_etiqueta" />
        @break
    @case('celular')
        <div class="small text-muted mb-1">{{ $persona->celular ?? 'sin celular' }}</div>
        @include('partials.acciones-contacto', ['persona' => $persona])
        @break
    @case('asignar')
    @puedeModulo('sin_lider', 'escritura')
        <x-form.layout :action="route('sin-lider.asignar', $persona)" modo="linea" guardar="Asignar" boton="success" icono="fa-user-check" :leyenda="false" :id="'asignar-'.$persona->id">
            <x-form.select name="lider_id" :id="'fm-lider-'.$persona->id" label="Líder" :ocultarEtiqueta="true" icono="fa-user-tie" :required="true" vacio="Elegir líder..." col="col-12" buscar :opciones="$grilla->opcionesLider()" />
        </x-form.layout>
    @endpuedeModulo
        @break
@endswitch
