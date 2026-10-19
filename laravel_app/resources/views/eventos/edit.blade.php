@extends('layouts.admin')

@section('title', 'Editar evento')

@section('page-title', 'Editar evento')

@section('content')
<x-form.layout :action="route('eventos.update', $evento)" method="PUT" guardar="Actualizar evento" icono="fa-save" boton="warning" :cancelar="route('eventos.show', $evento)">
    @include('eventos._form')
</x-form.layout>
@endsection
