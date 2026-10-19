<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AdminLTE Panel')@if($sedeActual) · {{ $sedeActual->nombre }}@endif</title>
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
        <ul class="navbar-nav ml-auto align-items-center">
            @if($esAdminSede)
                <li class="nav-item mr-2">
                    <form method="POST" action="{{ route('sede-activa.update') }}" class="form-inline">
                        @csrf
                        <label class="mr-1 mb-0 small text-muted" for="sede-activa">Sede activa</label>
                        <select id="sede-activa" name="sede_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="" {{ $enTodasLasSedes ? 'selected' : '' }}>Todas las sedes</option>
                            @foreach($sedesSelector as $opcion)
                                <option value="{{ $opcion->id }}" {{ $sedeActual?->id === $opcion->id ? 'selected' : '' }}>{{ $opcion->nombre }}{{ $opcion->activa ? '' : ' (inactiva)' }}</option>
                            @endforeach
                        </select>
                    </form>
                </li>
                @if($enTodasLasSedes)
                    <li class="nav-item mr-2"><span class="badge badge-warning">Todas las sedes: solo consulta</span></li>
                @endif
            @elseif($sedeActual)
                <li class="nav-item mr-3"><span class="navbar-text font-weight-bold"><i class="fas fa-church"></i> {{ $sedeActual->nombre }}</span></li>
            @endif
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
            <span class="brand-text font-weight-light">CRM Admin{!! $sedeActual ? ' - <strong>'.e($sedeActual->nombre).'</strong>' : '' !!}</span>
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
                    @puede('ver', \App\Models\Sede::class)
                    <li class="nav-item">
                        <a href="{{ route('sedes.index') }}" class="nav-link @if(request()->routeIs('sedes.*')) active @endif">
                            <i class="nav-icon fas fa-church"></i>
                            <p>Sedes</p>
                        </a>
                    </li>
                    @endpuede
                    @puede('verSinLider')
                    <li class="nav-item">
                        <a href="{{ route('sin-lider.index') }}" class="nav-link @if(request()->routeIs('sin-lider.*')) active @endif">
                            <i class="nav-icon fas fa-user-clock"></i>
                            <p>Sin líder asignado @if($sinLiderPendientes > 0)<span class="badge badge-warning right">{{ $sinLiderPendientes }}</span>@endif</p>
                        </a>
                    </li>
                    @endpuede
                    @puede('gestionarCatalogos')
                    <li class="nav-item">
                        <a href="{{ route('redes.index') }}" class="nav-link @if(request()->routeIs('redes.*')) active @endif">
                            <i class="nav-icon fas fa-sitemap"></i>
                            <p>Redes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('zonas.index') }}" class="nav-link @if(request()->routeIs('zonas.*')) active @endif">
                            <i class="nav-icon fas fa-map-marked-alt"></i>
                            <p>Zonas</p>
                        </a>
                    </li>
                    @endpuede
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
                    @puede('verUsuarios', \App\Models\User::class)
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link @if(request()->routeIs('users.*')) active @endif">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Usuarios</p>
                        </a>
                    </li>
                    @endpuede
                </ul>
            </nav>
        </div>
    </aside>

    @if($sedeActual)
        {{-- Marca de agua con el nombre de la sede en la que se está trabajando (todas las páginas). --}}
        <style>
            .marca-agua-sede {
                position: fixed; inset: 0; z-index: 0; pointer-events: none;
                display: flex; align-items: flex-end; justify-content: center; overflow: hidden; padding-bottom: 7vh;
            }
            .marca-agua-sede span {
                text-transform: uppercase; white-space: nowrap;
                font-weight: 800; font-size: clamp(2rem, 8vw, 7rem); letter-spacing: .05em;
                color: rgba(0, 0, 0, .05); user-select: none;
            }
            .content-wrapper .content-header, .content-wrapper .content { position: relative; z-index: 1; }
        </style>
        <div class="marca-agua-sede" aria-hidden="true"><span>{{ $sedeActual->nombre }}</span></div>
    @endif

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
