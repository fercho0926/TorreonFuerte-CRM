@extends('layouts.admin')

@section('title', 'Editar miembro')

@section('page-title', 'Editar miembro')

@section('content')
<x-form.layout :action="route('personas.update', $persona)" method="PUT" guardar="Actualizar miembro" icono="fa-save" boton="warning" :cancelar="route('personas.show', $persona)">
    @include('personas._form')
</x-form.layout>
@endsection
