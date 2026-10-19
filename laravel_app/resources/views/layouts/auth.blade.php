<!DOCTYPE html>
<html lang="es" data-bs-theme="light" data-lte-color-mode="off">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ingreso') · {{ config('app.sistema') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url('/') }}"><i class="fas fa-church me-1"></i><b>CRM</b> Torreón Fuerte</a>
        </div>
        @yield('content')
    </div>
</body>
</html>
