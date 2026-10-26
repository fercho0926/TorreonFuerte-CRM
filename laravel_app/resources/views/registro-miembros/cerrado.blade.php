@extends('layouts.auth')

@section('title', 'Registro cerrado')

@section('content')
<div class="card card-outline card-secondary">
    <div class="card-body login-card-body text-center">
        <p class="display-5 text-secondary mb-2" aria-hidden="true"><i class="fas fa-lock"></i></p>
        <h1 class="h4">Registro cerrado</h1>
        <p class="mb-0">El registro de miembros de <strong>{{ $sede->nombre }}</strong> no está disponible en este momento.</p>
    </div>
</div>
@endsection
