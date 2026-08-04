@extends('layouts.admin')

@section('title', 'Crear célula')

@section('page-title', 'Crear nueva célula')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nueva célula</h3>
    </div>
    <form method="POST" action="{{ route('celulas.store') }}">
        @csrf
        <div class="card-body">
            @include('celulas._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar célula</button>
            <a href="{{ route('celulas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
