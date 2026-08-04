@extends('layouts.admin')

@section('title', 'Crear evento')

@section('page-title', 'Crear nuevo evento')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo evento</h3>
    </div>
    <form method="POST" action="{{ route('eventos.store') }}">
        @csrf
        <div class="card-body">
            @include('eventos._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar evento</button>
            <a href="{{ route('eventos.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
