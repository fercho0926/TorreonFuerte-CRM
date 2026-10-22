<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-lte-color-mode="off">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Ingreso') · {{ config('app.sistema') }}</title>
    @include('partials.meta-aplicacion')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body.login-page {
            background: #1b2230 url('{{ asset('img/login-background.jpeg') }}') center / cover no-repeat fixed;
        }
        body.login-page .login-logo a { color: #fff; text-shadow: 0 1px 6px rgba(0, 0, 0, .55); }
        body.login-page .login-box .card { box-shadow: 0 10px 30px rgba(0, 0, 0, .35); }
    </style>
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url('/') }}"><i class="fas fa-church me-1"></i><b>CRM</b> Torreón Fuerte</a>
        </div>
        @yield('content')
    </div>
</body>
</html>
