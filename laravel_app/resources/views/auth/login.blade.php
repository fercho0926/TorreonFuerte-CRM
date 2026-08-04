@extends('layouts.auth')

@section('title', 'Ingreso al panel')

@section('content')
<div class="login-page" style="min-height: 100vh; background: linear-gradient(135deg, #3c8dbc, #00a65a); display: flex; align-items: center; justify-content: center;">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="#" class="h1"><b>CRM</b> Admin</a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Ingresa tus credenciales para iniciar sesión</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Correo electrónico" name="email" value="{{ old('email') }}" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Contraseña" name="password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                        </div>
                    </div>
                </form>

                <p class="mt-3 mb-1 text-center">Demo: admin@example.com / password</p>
            </div>
        </div>
    </div>
</div>
@endsection
