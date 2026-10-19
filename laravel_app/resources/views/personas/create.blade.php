@extends('layouts.admin')

@section('title', 'Crear persona')

@section('page-title', 'Crear nueva persona')

@section('content')
<x-form.layout :action="route('personas.store')" guardar="Guardar persona" icono="fa-save" :cancelar="route('personas.index')">
    @include('personas._form')
</x-form.layout>
@endsection
