@extends('layouts.admin')

@section('title', 'No se puede completar')

@section('page-title', 'No se puede completar la acción')

@section('content')
<div class="alert alert-warning">
    {{ $exception->getMessage() }}
</div>
<a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
@endsection
