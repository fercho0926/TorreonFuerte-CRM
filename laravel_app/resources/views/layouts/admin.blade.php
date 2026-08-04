<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AdminLTE Panel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link">Inicio</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger">Cerrar sesión</button>
                </form>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">CRM Admin</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link @if(request()->routeIs('dashboard')) active @endif">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('personas.index') }}" class="nav-link @if(request()->routeIs('personas.*')) active @endif">
                            <i class="nav-icon fas fa-address-card"></i>
                            <p>Personas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('lideres.index') }}" class="nav-link @if(request()->routeIs('lideres.*')) active @endif">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>Líderes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('celulas.index') }}" class="nav-link @if(request()->routeIs('celulas.*')) active @endif">
                            <i class="nav-icon fas fa-people-arrows"></i>
                            <p>Células</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('encuentros.index') }}" class="nav-link @if(request()->routeIs('encuentros.*')) active @endif">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Encuentros</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('eventos.index') }}" class="nav-link @if(request()->routeIs('eventos.*')) active @endif">
                            <i class="nav-icon fas fa-glass-cheers"></i>
                            <p>Eventos</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('ministerios.index') }}" class="nav-link @if(request()->routeIs('ministerios.*')) active @endif">
                            <i class="nav-icon fas fa-hands-helping"></i>
                            <p>Ministerios</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link @if(request()->routeIs('users.index')) active @endif">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Usuarios</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('page-title', 'Dashboard')</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">CRM Laravel</div>
        <strong>Copyright &copy; 2026.</strong>
    </footer>
</div>
</body>
</html>
