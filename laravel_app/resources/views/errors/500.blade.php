@extends(\Illuminate\Support\Facades\Session::get('is_logged_in') ? 'layouts.admin' : 'layouts.auth')

@section('title', 'Error del servidor')

@section('page-title', 'Error del servidor')

@section('content')
@include('errors._contenido', ['codigo' => 500, 'icono' => 'fa-bug', 'tipo' => 'danger', 'mensaje' => 'Algo salió mal de nuestro lado. Intenta de nuevo y, si continúa, avisa a un administrador.'])
@endsection
