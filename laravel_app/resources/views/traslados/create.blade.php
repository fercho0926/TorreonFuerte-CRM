@extends('layouts.admin')

@section('title', 'Trasladar persona')

@section('page-title', 'Trasladar persona a otra sede')

@section('content')
<div class="callout callout-info">
    <strong>{{ $persona->nombre_completo }}</strong> — sede actual: {{ $persona->sede?->nombre }}<br>
    <small>
        Se crea un registro nuevo en la sede destino, sin líder, célula ni ministerios (aparecerá en "Sin líder asignado").
        El registro de aquí queda inactivo y conserva su historial (asistencias, pagos, células).
    </small>
</div>

@if(array_sum($pendientes) > 0)
    <div class="alert alert-warning">
        <strong>Antes de trasladar hay que reasignar lo que esta persona dirige:</strong>
        <ul class="mb-0">
            @if($pendientes['personas'])<li>{{ $pendientes['personas'] }} persona(s) a su cargo — reasígnalas con "Quitar rol" del líder.</li>@endif
            @if($pendientes['celulas'])<li>{{ $pendientes['celulas'] }} célula(s) — cambia su líder o co-líder.</li>@endif
            @if($pendientes['ministerios'])<li>{{ $pendientes['ministerios'] }} ministerio(s) — cambia su encargado.</li>@endif
        </ul>
    </div>
@endif

<x-form.layout :action="route('traslados.store', $persona)" guardar="Trasladar" icono="fa-exchange-alt" :cancelar="route('personas.show', $persona)" :deshabilitar="array_sum($pendientes) > 0" :leyenda="false">
    <x-form.section titulo="Sede destino" descripcion="A qué iglesia se traslada la persona." icono="fa-church" id="seccion-destino">
        <x-form.select name="sede_destino_id" label="Sede destino" icono="fa-church" :required="true" col="col-md-8"
            :opciones="$sedes->map(fn ($s) => ['id' => $s->id, 'texto' => $s->nombre, 'apoyo' => $s->ciudad])->all()" />
    </x-form.section>
</x-form.layout>
@endsection
