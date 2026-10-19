@extends(\Illuminate\Support\Facades\Session::get('is_logged_in') ? 'layouts.admin' : 'layouts.auth')

@section('title', 'Sesión expirada')

@section('page-title', 'Sesión expirada')

@section('content')
@include('errors._contenido', ['codigo' => 419, 'icono' => 'fa-hourglass-end', 'tipo' => 'warning', 'mensaje' => 'Tu sesión expiró por inactividad. Vuelve a iniciar sesión e inténtalo de nuevo.'])
@endsection
