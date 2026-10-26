@extends('layouts.admin')

@section('title', 'Crear miembro')

@section('page-title', 'Crear nuevo miembro')

@section('content')
<x-form.layout :action="route('personas.store')" guardar="Guardar miembro" icono="fa-save" :cancelar="route('personas.index')">
    @include('personas._form')
</x-form.layout>
@endsection
