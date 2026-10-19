@extends('layouts.admin')

@section('title', 'Editar encuentro')

@section('page-title', 'Editar encuentro')

@section('content')
<x-form.layout :action="route('encuentros.update', $encuentro)" method="PUT" guardar="Actualizar encuentro" icono="fa-save" boton="warning" :cancelar="route('encuentros.show', $encuentro)">
    @include('encuentros._form')
</x-form.layout>
@endsection
