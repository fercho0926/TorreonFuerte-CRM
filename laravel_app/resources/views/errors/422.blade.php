@extends(\Illuminate\Support\Facades\Session::get('is_logged_in') ? 'layouts.admin' : 'layouts.auth')

@section('title', 'No se puede completar')

@section('page-title', 'No se puede completar')

@section('content')
@include('errors._contenido', ['codigo' => 422, 'icono' => 'fa-exclamation-circle', 'tipo' => 'warning', 'mensaje' => $exception->getMessage() ?: 'No se puede completar la acción.'])
@endsection
