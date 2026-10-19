@extends('layouts.admin')

@section('title', 'Nueva sede')

@section('page-title', 'Nueva sede')

@section('content')
<x-form.layout :action="route('sedes.store')" guardar="Crear sede" icono="fa-save" :cancelar="route('sedes.index')">
    <x-form.section titulo="Datos de la iglesia" descripcion="Nombre y datos de contacto de la sede." icono="fa-church" id="seccion-iglesia">
        @include('sedes._datos')
    </x-form.section>

    <x-form.section titulo="Pastor" descripcion="Tendrá acceso para administrar esta sede." icono="fa-user-tie" id="seccion-pastor">
        @include('sedes._cuenta', ['prefijo' => 'pastor', 'obligatoria' => true])
    </x-form.section>

    <x-form.section titulo="Pastora (opcional)" descripcion="Si la iglesia solo tiene pastor, deja estos campos vacíos; podrás añadirla después. Tendrá los mismos permisos que el pastor." icono="fa-user-friends" id="seccion-pastora">
        @include('sedes._cuenta', ['prefijo' => 'pastora', 'obligatoria' => false])
    </x-form.section>
</x-form.layout>
@endsection
