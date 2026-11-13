@extends('layouts.auth')

@section('title', 'Ingreso al panel')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body login-card-body">
        <p class="login-box-msg">{{ __('interfaz.ingresa_tus_credenciales_para_iniciar_se') }}</p>

        @if(session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        <x-form.layout :action="route('login.post')" modo="login" guardar="Ingresar" icono="fa-sign-in-alt" :leyenda="false">
            <x-form.section titulo="Iniciar sesión" icono="fa-user-lock" id="seccion-ingreso">
                <x-form.input name="email" label="Correo electrónico" type="correo" icono="fa-envelope" :required="true" ejemplo="nombre@correo.com" col="col-12" />
                <x-form.input name="password" label="Contraseña" type="password" icono="fa-lock" :required="true" col="col-12" data-revelable autocomplete="current-password" />
            </x-form.section>
        </x-form.layout>

        <p class="mt-3 mb-1 text-center"><a href="{{ route('recuperacion.create') }}">{{ __('interfaz.olvidaste_tu_contrasena') }}</a></p>
    </div>
</div>
@endsection
