@extends('layouts.auth')

@section('title', 'Registro recibido')

@section('content')
<div class="card card-outline card-success">
    <div class="card-body login-card-body text-center">
        <p class="display-5 text-success mb-2" aria-hidden="true"><i class="fas fa-check-circle"></i></p>
        @if(($resultado ?? null) === 'pendiente')
            <h1 class="h4">Recibimos tu registro</h1>
            <p class="mb-0">Para <strong>{{ $destino->nombre }}</strong>. El administrador te contactará para confirmarlo.</p>
        @elseif(($resultado ?? null) === null)
            <h1 class="h4">Registro</h1>
            <p class="mb-0">Si acabas de registrarte en <strong>{{ $destino->nombre }}</strong>, ya quedó listo.</p>
        @else
            <h1 class="h4">¡Listo, quedaste registrado!</h1>
            <p class="mb-0">Te esperamos en <strong>{{ $destino->nombre }}</strong>.</p>
        @endif
    </div>
</div>
@endsection
