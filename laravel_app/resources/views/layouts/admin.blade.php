<!DOCTYPE html>
{{-- Tema claro fijo: data-lte-color-mode="off" desactiva el selector de tema de AdminLTE. --}}
<html lang="es" data-bs-theme="light" data-lte-color-mode="off">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Inicio') · {{ config('app.sistema') }}@if($sedeActual) · {{ $sedeActual->nombre }}@endif</title>
    @include('partials.meta-aplicacion')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
{{-- Aplica el plegado recordado antes del primer pintado para evitar parpadeo (ver layout/menu-recordado.js). --}}
<script>try{if(localStorage.getItem('crm.menu.plegado')==='1'&&window.innerWidth>=992)document.body.classList.add('sidebar-collapse')}catch(e){}</script>
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Mostrar u ocultar el menú"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-md-block">
                    <a href="{{ route('dashboard') }}" class="nav-link">Inicio</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                @if($esAdminSede)
                    <li class="nav-item me-2">
                        <form method="POST" action="{{ route('sede-activa.update') }}" class="d-flex align-items-center gap-1">
                            @csrf
                            <label class="small text-muted d-none d-lg-inline mb-0" for="sede-activa">Sede activa</label>
                            <select id="sede-activa" name="sede_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="" {{ $enTodasLasSedes ? 'selected' : '' }}>Todas las sedes</option>
                                @foreach($sedesSelector as $opcion)
                                    <option value="{{ $opcion->id }}" {{ $sedeActual?->id === $opcion->id ? 'selected' : '' }}>{{ $opcion->nombre }}{{ $opcion->activa ? '' : ' (inactiva)' }}</option>
                                @endforeach
                            </select>
                        </form>
                    </li>
                    @if($enTodasLasSedes)
                        <li class="nav-item me-2 d-none d-md-block"><span class="badge text-bg-warning">Todas las sedes: solo consulta</span></li>
                    @endif
                @elseif($sedeActual)
                    <li class="nav-item me-3 d-none d-sm-block"><span class="navbar-text fw-bold"><i class="fas fa-church"></i> <span class="nombre-sede">{{ $sedeActual->nombre }}</span></span></li>
                @endif
                @if($usuarioActual)
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fa-lg"></i>
                            <span class="d-none d-md-inline ms-1">{{ $usuarioActual->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header text-bg-primary">
                                <i class="fas fa-user-circle fa-3x mb-2"></i>
                                <p class="mb-0">{{ $usuarioActual->name }}</p>
                                <small>{{ $rolEtiqueta }}</small>
                                <small class="d-block">{{ $sedeActual?->nombre ?? 'Todas las sedes' }}</small>
                            </li>
                            <li class="user-footer d-flex justify-content-between align-items-center gap-2">
                                <a href="{{ route('cuenta.edit') }}" class="btn btn-outline-primary"><i class="fas fa-id-card"></i> Mi cuenta</a>
                                <form method="POST" action="{{ route('logout') }}" class="text-end">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary "><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <span class="brand-text fw-light">CRM Admin{!! $sedeActual ? ' - <strong class="nombre-sede">'.e($sedeActual->nombre).'</strong>' : '' !!}</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-2" aria-label="Menú principal">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                    @foreach($menu as $seccion)
                        @if($seccion['titulo'])
                            <li class="nav-header">{{ mb_strtoupper($seccion['titulo']) }}</li>
                        @endif
                        @foreach($seccion['opciones'] as $opcion)
                            <li class="nav-item">
                                <a href="{{ $opcion['url'] }}" class="nav-link{{ $opcion['activa'] ? ' active' : '' }}" @if($opcion['activa']) aria-current="page" @endif>
                                    <i class="nav-icon fas {{ $opcion['icono'] }}"></i>
                                    <p>{{ $opcion['etiqueta'] }}@if($opcion['insignia'] > 0)<span class="nav-badge badge text-bg-warning me-3">{{ $opcion['insignia'] }}</span>@endif</p>
                                </a>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
        @if($esAdminSede && $enTodasLasSedes)
            <p class="aviso-sede-movil" role="status">Todas las sedes: solo consulta</p>
        @endif
        @if($sedeActual)
            {{-- Marca de agua con el nombre de la sede en la que se está trabajando (todas las páginas). --}}
            <div class="marca-agua-sede" aria-hidden="true"><span>{{ $sedeActual->nombre }}</span></div>
        @endif
        @php($migas = \App\Support\Menu::migas($menu, trim($__env->yieldContent('page-title'))))
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3 class="mb-0">@yield('page-title', 'Dashboard')</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end mb-0 mt-2 mt-sm-0">
                            @hasSection('breadcrumbs')
                                @yield('breadcrumbs')
                            @else
                                @foreach($migas as $miga)
                                    @if($loop->last || !$miga['url'])
                                        <li class="breadcrumb-item @if($loop->last) active @endif" @if($loop->last) aria-current="page" @endif>{{ $miga['texto'] }}</li>
                                    @else
                                        <li class="breadcrumb-item"><a href="{{ $miga['url'] }}">{{ $miga['texto'] }}</a></li>
                                    @endif
                                @endforeach
                            @endif
                        </ol>
                    </div>
                </div>
                @hasSection('page-actions')
                    <div class="row mt-2">
                        <div class="col-12 d-flex flex-wrap gap-2">@yield('page-actions')</div>
                    </div>
                @endif
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                {{-- Mensajes de la acción anterior: el éxito se vuelve un aviso que se cierra solo (con JavaScript). --}}
                <div data-avisos aria-live="polite">
                    @if(session('success'))
                        <div class="alert alert-success" role="status" data-aviso="exito">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif
                </div>
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">{{ config('app.sistema') }}</div>
        <strong>Copyright &copy; {{ date('Y') }}.</strong>
    </footer>
</div>
</body>
</html>
