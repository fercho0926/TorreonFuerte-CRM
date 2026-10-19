@extends('layouts.admin')

@section('title', 'Editar ministerio')

@section('page-title', 'Editar ministerio')

@section('content')
<x-form.layout :action="route('ministerios.update', $ministerio)" method="PUT" guardar="Actualizar ministerio" icono="fa-save" boton="warning" :cancelar="route('ministerios.show', $ministerio)">
    @include('ministerios._form')
</x-form.layout>
@endsection
