@extends('layouts.admin')

@section('title', 'Editar usuario')

@section('page-title', 'Editar usuario')

@section('content')
<x-form.layout :action="route('users.update', $user)" method="PUT" guardar="Actualizar usuario" icono="fa-save" boton="warning" :cancelar="route('users.index')">
    @include('users._form')
</x-form.layout>
@endsection
