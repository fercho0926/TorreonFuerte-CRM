@extends('layouts.admin')

@section('title', 'Nueva sede')

@section('page-title', 'Nueva sede')

@section('content')
<form method="POST" action="{{ route('sedes.store') }}">
    @csrf
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">Datos de la iglesia</h3></div>
        <div class="card-body">
            @include('sedes._datos')
        </div>
    </div>

    <div class="card card-info">
        <div class="card-header"><h3 class="card-title">Pastor</h3></div>
        <div class="card-body">
            @include('sedes._cuenta', ['prefijo' => 'pastor', 'obligatoria' => true])
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-header"><h3 class="card-title">Pastora <small class="text-muted">(opcional)</small></h3></div>
        <div class="card-body">
            <p class="text-muted small">Si la iglesia solo tiene pastor, deja estos campos vacíos; la pastora puede añadirse después. Tendrá los mismos permisos que el pastor sobre su sede.</p>
            @include('sedes._cuenta', ['prefijo' => 'pastora', 'obligatoria' => false])
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Crear sede</button>
    <a href="{{ route('sedes.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection
