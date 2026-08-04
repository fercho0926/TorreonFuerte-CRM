@extends('layouts.admin')

@section('title', 'Editar líder')

@section('page-title', 'Editar líder')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Editar líder</h3>
    </div>
    <form method="POST" action="{{ route('lideres.update', $lider) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('lideres._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Actualizar líder</button>
            <a href="{{ route('lideres.show', $lider) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
