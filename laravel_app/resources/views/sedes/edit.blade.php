@extends('layouts.admin')

@section('title', 'Editar sede')

@section('page-title', 'Editar sede')

@section('content')
<form method="POST" action="{{ route('sedes.update', $sede) }}">
    @csrf
    @method('PUT')
    <div class="card card-warning">
        <div class="card-header"><h3 class="card-title">{{ $sede->nombre }}</h3></div>
        <div class="card-body">
            @include('sedes._datos')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Guardar cambios</button>
            <a href="{{ route('sedes.show', $sede) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </div>
</form>
@endsection
