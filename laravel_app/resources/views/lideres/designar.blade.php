@extends('layouts.admin')

@section('title', 'Designar líder')

@section('page-title', 'Designar líder')

@section('content')
@include('partials.ayuda-terminos')

@if($errors->any() && $q !== null && $q !== '')
    <div class="alert alert-danger" role="alert">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<x-form.layout :action="route('lideres.designar')" method="GET" modo="filtro" guardar="Buscar" icono="fa-search" :leyenda="false">
    <x-form.section titulo="Buscar a la persona que será líder" descripcion="Escribe parte del nombre, documento o celular." icono="fa-search" id="seccion-buscar">
        <x-form.input name="q" label="Persona" icono="fa-search" :value="$q" ejemplo="Nombre, documento o celular" col="col-12" autofocus />
    </x-form.section>
</x-form.layout>

@if($q !== null && $q !== '')
    @php($opcionesRed = $redes->map(fn ($r) => ['id' => $r->id, 'texto' => $r->nombre])->all())
    @forelse($candidatas as $persona)
        <x-form.layout :action="route('lideres.designar.store')" modo="incrustado" guardar="Designar líder" icono="fa-user-check" boton="success" :leyenda="false" :id="'designar-'.$persona->id">
            <input type="hidden" name="persona_id" value="{{ $persona->id }}">
            <x-form.section :titulo="$persona->nombre_completo" :descripcion="($persona->numero_documento ? $persona->tipo_documento.' '.$persona->numero_documento : 'Sin documento').' · '.($persona->celular ?? 'sin celular')" icono="fa-user" :id="'persona-'.$persona->id">
                <x-form.select :name="'red_id'" :id="'fm-red-'.$persona->id" label="Red" icono="fa-sitemap" :required="true" :opciones="$opcionesRed" col="col-md-6" />
                @if(!$persona->numero_documento)
                    <div class="col-12"><p class="small text-danger mb-2">Esta persona no tiene documento; edítala para completarlo antes de designarla.</p></div>
                @endif
            </x-form.section>
        </x-form.layout>
    @empty
        <p class="text-muted">No se encontraron personas (o ya son líderes).</p>
    @endforelse
@endif

<a href="{{ route('personas.create', ['es_lider' => 1]) }}" class="btn btn-outline-primary">Nueva persona como líder</a>
<a href="{{ route('lideres.index') }}" class="btn btn-secondary">Cancelar</a>
@endsection
