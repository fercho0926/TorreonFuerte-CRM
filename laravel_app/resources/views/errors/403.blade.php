@extends('layouts.admin')

@section('title', 'Sin permiso')

@section('page-title', 'Sin permiso')

@section('content')
<div class="alert alert-danger">
    {{ $exception->getMessage() ?: 'No tienes permiso para esta acción' }}
</div>
<a href="{{ route('dashboard') }}" class="btn btn-secondary">Volver al inicio</a>
@endsection
