@extends('layouts.admin')

@section('title', 'Crear usuario')

@section('page-title', 'Crear nuevo usuario')

@section('content')
<x-form.layout :action="route('users.store')" guardar="Crear y generar clave" icono="fa-key" :cancelar="route('users.index')">
    @include('users._form')
</x-form.layout>
@endsection
