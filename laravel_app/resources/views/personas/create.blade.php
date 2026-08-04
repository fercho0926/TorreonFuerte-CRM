@extends('layouts.admin')

@section('title', 'Crear persona')

@section('page-title', 'Crear nueva persona')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva persona</h3>
    </div>
    <form method="POST" action="{{ route('personas.store') }}">
        @csrf
        <div class="card-body">
            @include('personas._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar persona</button>
            <a href="{{ route('personas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
