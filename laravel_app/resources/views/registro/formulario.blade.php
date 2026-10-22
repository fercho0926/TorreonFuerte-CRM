@extends('layouts.auth')

@section('title', 'Registro')

@section('content')
@php
    $tipo = ['evento' => 'Evento', 'encuentro' => 'Encuentro', 'celula' => 'Célula'][$destino->tipoRegistroQr()];
    $fecha = $destino->fecha ?? $destino->fecha_realizacion ?? null;
    $lugar = $destino->lugar ?? $destino->lugar_reunion ?? null;
    $token = $destino->registro_token;
@endphp
<div class="card card-outline card-primary">
    <div class="card-body login-card-body">
        <p class="text-uppercase text-muted small mb-1">{{ $tipo }}</p>
        <h1 class="h4 mb-1">{{ $destino->nombre }}</h1>
        <p class="text-muted mb-3">
            @if($fecha){{ $fecha->format('Y-m-d') }}@endif
            @if($destino->dia_reunion ?? null){{ $destino->dia_reunion }} {{ $destino->hora_reunion }}@endif
            @if($lugar) · {{ $lugar }}@endif
        </p>

        @if($aviso)
            <div class="alert alert-warning" role="alert">{{ $aviso }}</div>
        @endif

        @if($paso === 1)
            <p class="login-box-msg">Escribe tu celular. Si ya estás en nuestra base, tus datos aparecerán solos.</p>

            <x-form.layout :action="route('registro.buscar', $token)" modo="login" guardar="Continuar" icono="fa-arrow-right" :leyenda="false">
                <x-form.section titulo="¿Quién eres?" icono="fa-id-card" id="seccion-identificacion">
                    <x-form.input name="celular" label="Celular" type="celular" icono="fa-mobile-alt" :required="true" ejemplo="310 555 9999" col="col-12" :value="$datos['celular'] ?? null" />
                    <x-form.input name="numero_documento" label="Cédula (opcional)" type="numero" icono="fa-id-card" ejemplo="Solo números" col="col-12" :value="$datos['numero_documento'] ?? null"
                        ayuda="Si la escribes, buscamos tus datos con ella." />
                </x-form.section>
            </x-form.layout>
        @else
            <p class="login-box-msg">
                @if($persona)
                    Encontramos tus datos. Revísalos y confirma tu registro.
                @else
                    Completa tus datos para registrarte.
                @endif
            </p>

            <x-form.layout :action="route('registro.store', $token)" modo="login" guardar="Registrarme" icono="fa-check" :leyenda="false"
                data-registro-buscar="{{ route('registro.buscar', $token) }}">
                <x-form.section titulo="Tus datos" icono="fa-user" id="seccion-datos">
                    <x-form.input name="celular" label="Celular" type="celular" icono="fa-mobile-alt" :required="true" col="col-12"
                        :value="$persona ? \App\Support\Celular::limpio($persona->celular) ?? ($datos['celular'] ?? null) : ($datos['celular'] ?? null)" />
                    <x-form.input name="nombres" label="Nombres" icono="fa-user" :required="true" col="col-12" :value="$persona?->nombres" />
                    <x-form.input name="apellidos" label="Apellidos" icono="fa-user" :required="true" col="col-12" :value="$persona?->apellidos" />
                    <x-form.date name="fecha_nacimiento" label="Fecha de nacimiento" :required="true" max="hoy" min="1900-01-01" atajos="" col="col-12"
                        :value="$persona?->fecha_nacimiento" />
                    <x-form.select name="tipo_documento" label="Tipo de documento" icono="fa-id-card" col="col-12" vacio="Sin cédula"
                        :opciones="\App\Models\Persona::TIPOS_DOCUMENTO" :value="$persona?->tipo_documento ?? old('tipo_documento')" />
                    <x-form.input name="numero_documento" label="Cédula (opcional)" type="numero" icono="fa-id-card" col="col-12"
                        :value="$datos['numero_documento'] ?? null" />
                </x-form.section>
            </x-form.layout>

            <p class="mt-3 mb-0 text-center"><a href="{{ route('registro.show', $token) }}">Empezar de nuevo</a></p>
        @endif
    </div>
</div>
@endsection
