@extends('layouts.admin')

@section('title', 'Editar célula')

@section('page-title', 'Editar célula')

@section('content')
<x-form.layout :action="route('celulas.update', $celula)" method="PUT" guardar="Actualizar célula" icono="fa-save" boton="warning" :cancelar="route('celulas.show', $celula)">
    @include('celulas._form')
</x-form.layout>
@endsection
