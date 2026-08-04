@extends('layouts.admin')

@section('title', 'Editar encuentro')

@section('page-title', 'Editar encuentro')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Editar encuentro</h3>
    </div>
    <form method="POST" action="{{ route('encuentros.update', $encuentro) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('encuentros._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Actualizar encuentro</button>
            <a href="{{ route('encuentros.show', $encuentro) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
