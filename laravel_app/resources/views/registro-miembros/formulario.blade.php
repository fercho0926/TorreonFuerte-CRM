@extends('layouts.auth')

@section('title', 'Registro de miembros')

@section('content')
@php
    $token = $sede->registro_token;
    $documentos = array_combine(\App\Models\Persona::TIPOS_DOCUMENTO, \App\Models\Persona::TIPOS_DOCUMENTO);
@endphp
<div class="card card-outline card-primary">
    <div class="card-body login-card-body">
        <p class="text-uppercase text-muted small mb-1">Registro de miembros</p>
        <h1 class="h4 mb-3">{{ $sede->nombre }}</h1>

        @if($aviso)
            <div class="alert alert-warning" role="alert">{{ $aviso }}</div>
        @endif

        @if($paso === 1)
            <p class="login-box-msg">Escribe tu celular. Si ya estás en nuestra base, tus datos aparecerán solos para que los revises.</p>

            <x-form.layout :action="route('registro-sede.buscar', $token)" modo="login" guardar="Continuar" icono="fa-arrow-right" :leyenda="false">
                <x-form.section titulo="¿Quién eres?" icono="fa-id-card" id="seccion-identificacion">
                    <x-form.input name="celular" label="Celular" type="celular" icono="fa-mobile-alt" :required="true" ejemplo="310 555 9999" col="col-12" :value="$datos['celular'] ?? null" />
                    <x-form.input name="numero_documento" label="Cédula (opcional)" type="numero" icono="fa-id-card" ejemplo="Solo números" col="col-12" :value="$datos['numero_documento'] ?? null"
                        ayuda="Si la escribes, buscamos tus datos con ella." />
                </x-form.section>
            </x-form.layout>
        @else
            <p class="login-box-msg">
                @if($persona)
                    Encontramos tus datos. Revísalos, corrige lo que haya cambiado y confirma.
                @else
                    Completa tus datos para registrarte.
                @endif
            </p>

            <x-form.layout :action="route('registro-sede.store', $token)" modo="login" guardar="{{ $persona ? 'Guardar mis datos' : 'Registrarme' }}" icono="fa-check" :leyenda="false"
                data-registro-buscar="{{ route('registro-sede.buscar', $token) }}"
                data-registro-campos="nombres,apellidos,fecha_nacimiento,tipo_documento,numero_documento,email,direccion,barrio,ciudad">
                <x-form.section titulo="Tus datos" icono="fa-user" id="seccion-datos">
                    <x-form.input name="celular" label="Celular" type="celular" icono="fa-mobile-alt" :required="true" col="col-12"
                        :value="$persona ? \App\Support\Celular::limpio($persona->celular) ?? ($datos['celular'] ?? null) : ($datos['celular'] ?? null)" />
                    <x-form.input name="nombres" label="Nombres" icono="fa-user" :required="true" col="col-12" :value="$persona?->nombres" />
                    <x-form.input name="apellidos" label="Apellidos" icono="fa-user" :required="true" col="col-12" :value="$persona?->apellidos" />
                    <x-form.date name="fecha_nacimiento" label="Fecha de nacimiento" :required="true" max="hoy" min="1900-01-01" atajos="" col="col-12"
                        :value="$persona?->fecha_nacimiento" />
                    <x-form.select name="tipo_documento" label="Tipo de documento" icono="fa-id-card" col="col-12" vacio="Sin cédula"
                        :opciones="$documentos" :value="$persona?->tipo_documento" />
                    <x-form.input name="numero_documento" label="Cédula (opcional)" type="numero" icono="fa-id-card" col="col-12"
                        :value="$persona?->numero_documento ?? ($datos['numero_documento'] ?? null)" />
                </x-form.section>
                <x-form.section titulo="Contacto y ubicación (opcional)" icono="fa-map-marker-alt" id="seccion-contacto">
                    <x-form.input name="email" label="Correo" type="correo" icono="fa-envelope" col="col-12" ejemplo="ana@correo.com" :value="$persona?->email" />
                    <x-form.input name="direccion" label="Dirección" icono="fa-home" col="col-12" :value="$persona?->direccion" />
                    <x-form.input name="barrio" label="Barrio" icono="fa-map-signs" col="col-12" :value="$persona?->barrio" />
                    <x-form.input name="ciudad" label="Ciudad" icono="fa-city" col="col-12" :value="$persona?->ciudad" />
                </x-form.section>
                @unless($persona)
                    <x-form.section titulo="¿Cómo te registras?" icono="fa-church" id="seccion-estado">
                        <x-form.select name="estado_inicial" label="Me registro como" icono="fa-church" col="col-12" vacio="Elige una opción" ayuda="Obligatorio si es la primera vez que te registras."
                            :opciones="['visitante' => 'Visitante', 'miembro_activo' => 'Miembro activo']" />
                    </x-form.section>
                @endunless
            </x-form.layout>

            <p class="mt-3 mb-0 text-center"><a href="{{ route('registro-sede.show', $token) }}">Empezar de nuevo</a></p>
        @endif
    </div>
</div>
@endsection
