@extends('layouts.admin')

@section('title', 'Crear líder')

@section('page-title', 'Crear nuevo líder')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo líder</h3>
    </div>
    <form method="POST" action="{{ route('lideres.store') }}">
        @csrf
        <div class="card-body">
            @include('lideres._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar líder</button>
            <a href="{{ route('lideres.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
