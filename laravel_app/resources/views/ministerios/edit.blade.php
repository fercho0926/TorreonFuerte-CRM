@extends('layouts.admin')

@section('title', 'Editar ministerio')

@section('page-title', 'Editar ministerio')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Editar ministerio</h3>
    </div>
    <form method="POST" action="{{ route('ministerios.update', $ministerio) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('ministerios._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Actualizar ministerio</button>
            <a href="{{ route('ministerios.show', $ministerio) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
