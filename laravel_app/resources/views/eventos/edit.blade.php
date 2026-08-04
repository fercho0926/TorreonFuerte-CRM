@extends('layouts.admin')

@section('title', 'Editar evento')

@section('page-title', 'Editar evento')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Editar evento</h3>
    </div>
    <form method="POST" action="{{ route('eventos.update', $evento) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('eventos._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Actualizar evento</button>
            <a href="{{ route('eventos.show', $evento) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
