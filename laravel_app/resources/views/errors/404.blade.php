@extends(\Illuminate\Support\Facades\Session::get('is_logged_in') ? 'layouts.admin' : 'layouts.auth')

@section('title', 'Página no encontrada')

@section('page-title', 'Página no encontrada')

@section('content')
@include('errors._contenido', ['codigo' => 404, 'icono' => 'fa-search', 'tipo' => 'warning', 'mensaje' => 'No encontramos lo que buscas. Revisa la dirección o vuelve al inicio.'])
@endsection
