@extends('layouts.admin')

@section('title', 'Editar usuario')

@section('page-title', 'Editar usuario')

@if(\App\Support\Permisos::puede('gestionarClaves', $user))
    @section('page-actions')
        <x-form.confirm :action="route('users.clave.store', $user)" titulo="¿Enviar una clave nueva?" :mensaje="'La contraseña actual de '.$user->name.' dejará de servir. Se abrirá la clave para enviarla por WhatsApp.'" boton="Generar clave" variante="success" tamano=""><i class="fab fa-whatsapp me-1" aria-hidden="true"></i>Enviar nueva clave</x-form.confirm>
    @endsection
@endif

@section('content')
<x-form.layout :action="route('users.update', $user)" method="PUT" guardar="Actualizar usuario" icono="fa-save" boton="warning" :cancelar="route('users.index')">
    @include('users._form')
</x-form.layout>
@endsection
