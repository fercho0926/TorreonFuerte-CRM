<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition login-page" style="min-height: 100vh; background: linear-gradient(135deg, #3c8dbc, #00a65a);">
    <div class="login-box">
        @yield('content')
    </div>
</body>
</html>
