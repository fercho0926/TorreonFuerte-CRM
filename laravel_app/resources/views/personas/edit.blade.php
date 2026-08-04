@extends('layouts.admin')

@section('title', 'Editar persona')

@section('page-title', 'Editar persona')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Editar persona</h3>
    </div>
    <form method="POST" action="{{ route('personas.update', $persona) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('personas._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Actualizar persona</button>
            <a href="{{ route('personas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
