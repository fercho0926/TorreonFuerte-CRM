@extends('layouts.admin')

@section('title', 'Designar líder')

@section('page-title', 'Designar líder')

@section('content')
@include('partials.ayuda-terminos')

<x-form.layout :action="route('lideres.designar.store')" guardar="Designar líder" icono="fa-user-check" boton="success" :cancelar="route('lideres.index')" :leyenda="false">
    <x-form.section titulo="¿Quién será líder?" descripcion="Escribe al menos 2 letras del nombre, apellido, documento o celular. Solo aparecen miembros activos que aún no son líderes." icono="fa-user-tie" id="seccion-persona">
        <x-form.select name="persona_id" label="Miembro" icono="fa-search" :required="true" :opciones="$opcionElegida" :value="old('persona_id')"
            vacio="Escribe para buscar…" col="col-12" :fuente="route('personas.buscar')" :filtros="['activas' => 1, 'sin_lideres' => 1]" />
    </x-form.section>
</x-form.layout>

<a href="{{ route('personas.create', ['es_lider' => 1]) }}" class="btn btn-outline-primary mb-4"><i class="fas fa-user-plus me-1" aria-hidden="true"></i>Nuevo miembro como líder</a>
@endsection
