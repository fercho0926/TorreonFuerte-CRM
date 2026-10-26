@extends('layouts.admin')

@section('title', 'Crear nivel')

@section('page-title', 'Crear nuevo nivel')

@section('content')
<x-form.layout :action="route('niveles.store')" guardar="Guardar nivel" icono="fa-save" :cancelar="route('niveles.index')">
    @include('niveles._form')
</x-form.layout>
@endsection
