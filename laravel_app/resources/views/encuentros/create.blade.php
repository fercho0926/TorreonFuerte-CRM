@extends('layouts.admin')

@section('title', 'Crear encuentro')

@section('page-title', 'Crear nuevo encuentro')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo encuentro</h3>
    </div>
    <form method="POST" action="{{ route('encuentros.store') }}">
        @csrf
        <div class="card-body">
            @include('encuentros._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar encuentro</button>
            <a href="{{ route('encuentros.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
