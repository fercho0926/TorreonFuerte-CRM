@extends(\Illuminate\Support\Facades\Session::get('is_logged_in') ? 'layouts.admin' : 'layouts.auth')

@section('title', 'Método no permitido')

@section('page-title', 'Método no permitido')

@section('content')
@include('errors._contenido', ['codigo' => 405, 'icono' => 'fa-ban', 'tipo' => 'warning', 'mensaje' => 'Esa acción no se puede hacer de esta forma. Vuelve atrás e inténtalo desde el botón o enlace correcto.'])
@endsection
