@extends('layouts.auth')

@section('title', 'Elige tu contraseña')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body login-card-body">
        <p class="login-box-msg">Hola, {{ $usuario->name }}. Entraste con una clave temporal: elige tu propia contraseña para continuar.</p>

        <x-form.layout :action="route('cambiar-clave.update')" method="PUT" modo="login" guardar="Guardar mi contraseña" icono="fa-save" :leyenda="false">
            <x-form.section titulo="Tu nueva contraseña" icono="fa-lock" id="seccion-clave">
                <x-form.input name="password" label="Contraseña nueva" type="password" icono="fa-lock" :required="true" col="col-12" data-revelable minlength="{{ config('claves.contrasena_minimo') }}"
                    :ayuda="'Mínimo '.config('claves.contrasena_minimo').' caracteres y distinta de la clave temporal.'" autocomplete="new-password" />
                <x-form.input name="password_confirmation" label="Repite la contraseña" type="password" icono="fa-lock" :required="true" col="col-12" data-revelable autocomplete="new-password" />
            </x-form.section>
        </x-form.layout>

        <div class="mt-3 text-center"><x-form.accion :action="route('logout')" boton="Cerrar sesión" variante="link" /></div>
    </div>
</div>
@endsection
