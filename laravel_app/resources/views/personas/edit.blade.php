@extends('layouts.admin')

@section('title', 'Editar persona')

@section('page-title', 'Editar persona')

@section('content')
<x-form.layout :action="route('personas.update', $persona)" method="PUT" guardar="Actualizar persona" icono="fa-save" boton="warning" :cancelar="route('personas.show', $persona)">
    @include('personas._form')
</x-form.layout>
@endsection
