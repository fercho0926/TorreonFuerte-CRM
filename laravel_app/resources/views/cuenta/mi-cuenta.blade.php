@extends('layouts.admin')

@section('title', 'Mi cuenta')

@section('page-title', 'Mi cuenta')

@section('content')
<div class="row">
    <div class="col-lg-5">
        <div class="card card-primary card-outline mb-4">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-circle me-1" aria-hidden="true"></i>Mis datos</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8">{{ $usuario->name }}</dd>

                    <dt class="col-sm-4">Correo</dt>
                    <dd class="col-sm-8">{{ $usuario->email }}</dd>

                    <dt class="col-sm-4">Rol</dt>
                    <dd class="col-sm-8">{{ \App\Models\User::etiquetaDeRol($usuario->role) }}</dd>

                    <dt class="col-sm-4">Sede</dt>
                    <dd class="col-sm-8">{{ $usuario->sede?->nombre ?? 'Todas las sedes' }}</dd>

                    <dt class="col-sm-4">WhatsApp</dt>
                    <dd class="col-sm-8">{{ $usuario->celular ? \App\Support\Celular::formatoLegible($usuario->celular) : '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <x-form.layout :action="route('cuenta.clave.update')" method="PUT" guardar="Cambiar contraseña" icono="fa-key" :leyenda="false">
            <x-form.section titulo="Cambiar mi contraseña" descripcion="Necesitas escribir la actual para confirmar que eres tú." icono="fa-lock" id="seccion-cambiar-clave">
                <x-form.input name="password_actual" label="Contraseña actual" type="password" icono="fa-lock" :required="true" col="col-12" data-revelable autocomplete="current-password" />
                <x-form.input name="password" label="Contraseña nueva" type="password" icono="fa-key" :required="true" col="col-md-6" data-revelable minlength="{{ config('claves.contrasena_minimo') }}"
                    :ayuda="'Mínimo '.config('claves.contrasena_minimo').' caracteres.'" autocomplete="new-password" />
                <x-form.input name="password_confirmation" label="Repite la contraseña nueva" type="password" icono="fa-key" :required="true" col="col-md-6" data-revelable autocomplete="new-password" />
            </x-form.section>
        </x-form.layout>
    </div>
</div>
@endsection
