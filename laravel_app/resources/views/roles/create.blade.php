@extends('layouts.admin')

@section('title', 'Crear rol')

@section('page-title', !empty($duplicado) ? 'Duplicar rol' : 'Crear rol')

@section('content')
@include('users._pestanas', ['activa' => 'roles'])
@if(!empty($duplicado))
    <div class="alert alert-info" role="status"><i class="fas fa-copy" aria-hidden="true"></i> Parte de los permisos de <strong>{{ $rol->nombre }}</strong>; ponle un nombre nuevo.</div>
@endif
<x-form.layout :global="true" :action="route('roles.store')" guardar="Crear rol" icono="fa-save" :cancelar="route('roles.index')">
    @if(!empty($duplicado))
        <input type="hidden" name="duplicado_de" value="{{ $rol->clave }}">
    @endif
    @include('roles._form', ['rol' => $rol ?? null, 'niveles' => $niveles ?? [], 'duplicado' => $duplicado ?? false])
</x-form.layout>
@endsection
