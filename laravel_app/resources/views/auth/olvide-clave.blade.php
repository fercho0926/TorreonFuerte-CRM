@extends('layouts.auth')

@section('title', 'Recuperar acceso')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body login-card-body">
        <p class="login-box-msg">Escribe el correo de tu cuenta. Avisaremos a quien administra tu sede para que te envíe una nueva clave por WhatsApp.</p>

        <x-form.layout :action="route('recuperacion.store')" modo="login" guardar="Pedir nueva clave" icono="fa-paper-plane" :leyenda="false">
            <x-form.section titulo="Recuperar acceso" icono="fa-key" id="seccion-recuperar">
                <x-form.input name="email" label="Correo electrónico" type="correo" icono="fa-envelope" :required="true" ejemplo="nombre@correo.com" col="col-12" />
            </x-form.section>
        </x-form.layout>

        <p class="mt-3 mb-0 text-center"><a href="{{ route('login') }}">Volver al inicio de sesión</a></p>
    </div>
</div>
@endsection
