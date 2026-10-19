@extends('layouts.admin')

@section('title', 'Crear usuario')

@section('page-title', 'Crear nuevo usuario')

@section('content')
<x-form.layout :action="route('users.store')" guardar="Guardar usuario" icono="fa-save" :cancelar="route('users.index')">
    @include('users._form')
</x-form.layout>
@endsection
