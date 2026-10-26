@extends('layouts.auth')

@section('title', 'Registro recibido')

@section('content')
@php
    $tipo = $resultado['resultado'] ?? null;
    $campos = $resultado['campos'] ?? [];
@endphp
<div class="card card-outline card-success">
    <div class="card-body login-card-body text-center">
        <p class="display-5 text-success mb-2" aria-hidden="true"><i class="fas fa-check-circle"></i></p>
        @if($tipo === 'nueva')
            <h1 class="h4">¡Listo, quedaste registrado!</h1>
            <p class="mb-0">Te esperamos en <strong>{{ $sede->nombre }}</strong>.</p>
        @elseif($tipo === 'actualizada')
            <h1 class="h4">¡Datos actualizados!</h1>
            <p class="mb-1">Actualizamos: {{ implode(', ', $campos) }}.</p>
            <p class="mb-0">Gracias por mantener tus datos al día en <strong>{{ $sede->nombre }}</strong>.</p>
        @elseif($tipo === 'reactivada')
            <h1 class="h4">¡Bienvenido de nuevo!</h1>
            <p class="mb-1">Volviste a quedar registrado en <strong>{{ $sede->nombre }}</strong>.</p>
            @if($campos)<p class="mb-0">Actualizamos: {{ implode(', ', $campos) }}.</p>@endif
        @elseif($tipo === 'sin_cambios')
            <h1 class="h4">Tus datos ya estaban al día</h1>
            <p class="mb-0">No hizo falta cambiar nada en <strong>{{ $sede->nombre }}</strong>.</p>
        @else
            <h1 class="h4">Registro</h1>
            <p class="mb-0">Si acabas de enviar tus datos a <strong>{{ $sede->nombre }}</strong>, ya quedaron listos.</p>
        @endif
    </div>
</div>
@endsection
