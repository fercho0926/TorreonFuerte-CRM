@extends('layouts.admin')

@section('title', 'Crear célula')

@section('page-title', 'Crear nueva célula')

@section('content')
<x-form.layout :action="route('celulas.store')" guardar="Guardar célula" icono="fa-save" :cancelar="route('celulas.index')">
    @include('celulas._form')
</x-form.layout>
@endsection
