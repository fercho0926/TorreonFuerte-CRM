@extends('layouts.admin')

@section('title', 'Editar célula')

@section('page-title', 'Editar célula')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Editar célula</h3>
    </div>
    <form method="POST" action="{{ route('celulas.update', $celula) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('celulas._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Actualizar célula</button>
            <a href="{{ route('celulas.show', $celula) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
