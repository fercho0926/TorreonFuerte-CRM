@extends('layouts.admin')

@section('title', 'Editar rol')

@section('page-title', 'Editar rol: '.$rol->nombre)

@section('content')
@include('users._pestanas', ['activa' => 'roles'])
@if($afectados > 0)
    <div class="alert alert-warning" role="status">
        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
        Este rol lo tienen <strong>{{ $afectados }} {{ $afectados === 1 ? 'usuario' : 'usuarios' }}</strong>: los cambios de permisos se aplican en su siguiente acción, sin cerrar sesión.
    </div>
@endif
<x-form.layout :global="true" :action="route('roles.update', $rol)" method="PUT" guardar="Guardar cambios" icono="fa-save" :cancelar="route('roles.show', $rol)">
    @include('roles._form', ['rol' => $rol, 'niveles' => $niveles, 'duplicado' => false])
    @if($afectados > 0)
        <x-form.section titulo="Confirmación" icono="fa-check-double" id="seccion-confirmacion">
            <x-form.switch name="confirmo_afectados" label="Entiendo que {{ $afectados }} {{ $afectados === 1 ? 'usuario se verá afectado' : 'usuarios se verán afectados' }}" :required="true" />
        </x-form.section>
    @endif
</x-form.layout>
@endsection
