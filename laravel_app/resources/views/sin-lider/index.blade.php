@extends('layouts.admin')

@section('title', 'Sin líder asignado')

@section('page-title', 'Sin líder asignado')

@section('content')
<x-form.layout :action="route('sin-lider.index')" method="GET" modo="filtro" guardar="Buscar" icono="fa-search" :leyenda="false">
    <x-form.input name="q" label="Buscar" icono="fa-search" :value="$q" ejemplo="Nombre, documento o celular" col="col-md-6" />
    <div class="col-auto fm-campo"><a href="{{ route('sin-lider.index') }}" class="btn btn-outline-secondary">Limpiar</a></div>
</x-form.layout>

@php($opcionesLider = $lideres->map(fn ($l) => ['id' => $l->id, 'texto' => $l->nombre_completo, 'apoyo' => $l->numero_documento ? 'Doc. '.$l->numero_documento : null])->all())

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Personas esperando un líder ({{ $personas->total() }})</h3>
    </div>
    <div class="card-body p-0">
        @forelse($personas as $persona)
            <x-form.layout :action="route('sin-lider.asignar', $persona)" modo="linea" guardar="Asignar" boton="success" icono="fa-user-check" :leyenda="false" :id="'asignar-'.$persona->id" class="border-bottom p-2">
                <div class="col-md-4 mb-2">
                    <a href="{{ route('personas.show', $persona) }}"><strong>{{ $persona->nombre_completo }}</strong></a>
                    <x-ui.estado :valor="$persona->estado" :etiqueta="$persona->estado_etiqueta" />
                    <div class="small text-muted">{{ $persona->celular ?? 'sin celular' }}</div>
                </div>
                <div class="col-md-3 mb-2">@include('partials.acciones-contacto', ['persona' => $persona])</div>
                <x-form.select name="lider_id" :id="'fm-lider-'.$persona->id" label="Líder" :ocultarEtiqueta="true" icono="fa-user-tie" :required="true" vacio="Elegir líder..." col="col-md-3" buscar :opciones="$opcionesLider" />
            </x-form.layout>
        @empty
            <p class="text-center text-muted my-4">No hay personas sin líder.</p>
        @endforelse
    </div>
    @if($personas->hasPages())
        <div class="card-footer">{{ $personas->links() }}</div>
    @endif
</div>
@endsection
