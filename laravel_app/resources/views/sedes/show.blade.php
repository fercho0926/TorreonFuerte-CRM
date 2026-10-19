@extends('layouts.admin')

@section('title', $sede->nombre)

@section('page-title', $sede->nombre)

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        @if($sede->activa && $sedeActivaId !== $sede->id)
            <form action="{{ route('sede-activa.update') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="sede_id" value="{{ $sede->id }}">
                <button type="submit" class="btn btn-success">Trabajar en esta sede</button>
            </form>
        @endif
        <a href="{{ route('sedes.edit', $sede) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('sedes.pastores', $sede) }}" class="btn btn-info">Cambiar pastor o pastora</a>
        @if($sede->activa)
            <form action="{{ route('sedes.desactivar', $sede) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary" onclick="return confirm('¿Desactivar esta sede? Sus usuarios no podrán iniciar sesión; los datos se conservan.')">Desactivar</button>
            </form>
        @else
            <form action="{{ route('sedes.reactivar', $sede) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">Reactivar</button>
            </form>
        @endif
        <a href="{{ route('sedes.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(!$sede->activa)
    <div class="alert alert-warning">Esta sede está <strong>inactiva</strong>: sus usuarios no pueden iniciar sesión y sus datos están en solo lectura.</div>
@endif

@if($resumen)
    <div class="row">
        <div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Personas</span><span class="info-box-number">{{ $resumen['personas'] }}</span></div></div></div>
        <div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-primary"><i class="fas fa-user-tie"></i></span><div class="info-box-content"><span class="info-box-text">Líderes</span><span class="info-box-number">{{ $resumen['lideres'] }}</span></div></div></div>
        <div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-secondary"><i class="fas fa-people-arrows"></i></span><div class="info-box-content"><span class="info-box-text">Células</span><span class="info-box-number">{{ $resumen['celulas'] }}</span></div></div></div>
        <div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-glass-cheers"></i></span><div class="info-box-content"><span class="info-box-text">Eventos próximos</span><span class="info-box-number">{{ $resumen['eventos_proximos'] }}</span></div></div></div>
    </div>
@endif

<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Datos de la sede</h3></div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Ciudad</dt><dd class="col-sm-9">{{ $sede->ciudad }}</dd>
            <dt class="col-sm-3">Dirección</dt><dd class="col-sm-9">{{ $sede->direccion ?? '—' }}</dd>
            <dt class="col-sm-3">Teléfono</dt><dd class="col-sm-9">{{ $sede->telefono ?? '—' }}</dd>
            <dt class="col-sm-3">Correo</dt><dd class="col-sm-9">{{ $sede->email ?? '—' }}</dd>
            <dt class="col-sm-3">Pastor</dt><dd class="col-sm-9">{{ $sede->pastor?->nombre_completo ?? '—' }}</dd>
            <dt class="col-sm-3">Pastora</dt><dd class="col-sm-9">{{ $sede->pastora?->nombre_completo ?? '—' }}</dd>
        </dl>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Usuarios de la sede ({{ $usuarios->count() }})</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th></tr></thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr><td>{{ $usuario->name }}</td><td>{{ $usuario->email }}</td><td>{{ \App\Models\User::ROLE_LABELS[$usuario->role] ?? $usuario->role }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center">Sin usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Historial de la sede</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>Fecha</th><th>Acción</th><th>Usuario</th></tr></thead>
            <tbody>
                @forelse($sede->cambios as $cambio)
                    <tr>
                        <td class="text-nowrap">{{ $cambio->created_at->translatedFormat('j M Y H:i') }}</td>
                        <td>{{ $cambio->etiqueta }}</td>
                        <td>{{ $cambio->usuario?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Sin cambios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
