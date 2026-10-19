@extends(\Illuminate\Support\Facades\Session::get('is_logged_in') ? 'layouts.admin' : 'layouts.auth')

@section('title', 'Sin permiso')

@section('page-title', 'Sin permiso')

@section('content')
@include('errors._contenido', ['codigo' => 403, 'icono' => 'fa-lock', 'tipo' => 'danger', 'mensaje' => $exception->getMessage() ?: 'No tienes permiso para esta acción.'])
@endsection
