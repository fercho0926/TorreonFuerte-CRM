@extends('layouts.auth')

@section('title', 'Recuperar acceso')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body login-card-body">
        <p class="login-box-msg">{{ __('interfaz.escribe_el_correo_de_tu_cuenta_avisaremo') }}</p>

        <x-form.layout :action="route('recuperacion.store')" modo="login" guardar="Pedir nueva clave" icono="fa-paper-plane" :leyenda="false">
            <x-form.section titulo="Recuperar acceso" icono="fa-key" id="seccion-recuperar">
                <x-form.input name="email" label="Correo electrónico" type="correo" icono="fa-envelope" :required="true" ejemplo="nombre@correo.com" col="col-12" />
            </x-form.section>
        </x-form.layout>

        <p class="mt-3 mb-0 text-center"><a href="{{ route('login') }}">{{ __('interfaz.volver_al_inicio_de_sesion') }}</a></p>
    </div>
</div>
@endsection
