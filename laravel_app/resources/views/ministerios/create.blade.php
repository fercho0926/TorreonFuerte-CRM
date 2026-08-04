@extends('layouts.admin')

@section('title', 'Crear ministerio')

@section('page-title', 'Crear nuevo ministerio')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo ministerio</h3>
    </div>
    <form method="POST" action="{{ route('ministerios.store') }}">
        @csrf
        <div class="card-body">
            @include('ministerios._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar ministerio</button>
            <a href="{{ route('ministerios.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
