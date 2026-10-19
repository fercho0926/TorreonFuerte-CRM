@extends('layouts.admin')

@section('title', 'Designar líder')

@section('page-title', 'Designar líder')

@section('content')
@include('partials.ayuda-terminos')

<x-form.layout :action="route('lideres.designar.store')" guardar="Designar líder" icono="fa-user-check" boton="success" :cancelar="route('lideres.index')" :leyenda="false">
    <x-form.section titulo="¿Quién será líder?" descripcion="Escribe al menos 2 letras del nombre, apellido, documento o celular. Solo aparecen personas activas que aún no son líderes." icono="fa-user-tie" id="seccion-persona">
        <x-form.select name="persona_id" label="Persona" icono="fa-search" :required="true" :opciones="$opcionElegida" :value="old('persona_id')"
            vacio="Escribe para buscar…" col="col-12" :fuente="route('personas.buscar')" :filtros="['activas' => 1, 'sin_lideres' => 1]"
            data-aviso-sin-documento="#aviso-sin-documento" />

        <div class="col-12" id="aviso-sin-documento" hidden>
            <div class="alert alert-warning mb-0" role="alert">
                Esta persona no tiene documento de identidad: complétalo antes de designarla como líder.
                <a href="#" data-plantilla="{{ route('personas.edit', '__ID__') }}" class="alert-link">Editar a la persona</a>
            </div>
        </div>
    </x-form.section>
</x-form.layout>

<a href="{{ route('personas.create', ['es_lider' => 1]) }}" class="btn btn-outline-primary mb-4"><i class="fas fa-user-plus me-1" aria-hidden="true"></i>Nueva persona como líder</a>
@endsection
