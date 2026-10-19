@extends('layouts.admin')

@section('title', 'Editar sede')

@section('page-title', 'Editar sede')

@section('content')
<x-form.layout :action="route('sedes.update', $sede)" method="PUT" guardar="Guardar cambios" icono="fa-save" boton="warning" :cancelar="route('sedes.show', $sede)">
    <x-form.section :titulo="$sede->nombre" descripcion="Datos de la iglesia." icono="fa-church" id="seccion-iglesia">
        @include('sedes._datos')
    </x-form.section>
</x-form.layout>
@endsection
