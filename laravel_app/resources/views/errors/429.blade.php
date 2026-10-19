@extends(\Illuminate\Support\Facades\Session::get('is_logged_in') ? 'layouts.admin' : 'layouts.auth')

@section('title', 'Demasiadas solicitudes')

@section('page-title', 'Demasiadas solicitudes')

@section('content')
@include('errors._contenido', ['codigo' => 429, 'icono' => 'fa-hourglass-half', 'tipo' => 'warning', 'mensaje' => $exception->getMessage() ?: 'Espera unos minutos antes de volver a intentarlo.'])
@endsection
