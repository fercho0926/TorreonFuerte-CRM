@extends('layouts.admin')

@section('title', $sede->nombre)

@section('page-title', $sede->nombre)

@section('page-actions')
    @if($sede->activa && $sedeActivaId !== $sede->id)
        <x-form.accion :action="route('sede-activa.update')" boton="Trabajar en esta sede" variante="success" icono="fa-briefcase">
            <input type="hidden" name="sede_id" value="{{ $sede->id }}">
        </x-form.accion>
    @endif
    <a href="{{ route('sedes.edit', $sede) }}" class="btn btn-primary">Editar</a>
    <a href="{{ route('sedes.pastores', $sede) }}" class="btn btn-outline-primary">Cambiar pastor o pastora</a>
    @if($sede->activa)
        <x-form.confirm :action="route('sedes.desactivar', $sede)" method="POST" titulo="¿Desactivar esta sede?" mensaje="Sus usuarios no podrán iniciar sesión; los datos se conservan." boton="Desactivar" :peligro="false" variante="outline-secondary" tamano="">Desactivar</x-form.confirm>
    @else
        <x-form.accion :action="route('sedes.reactivar', $sede)" boton="Reactivar" variante="success" icono="fa-undo" />
    @endif
    <a href="{{ route('sedes.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')

@if(!$sede->activa)
    <div class="alert alert-warning">Esta sede está <strong>inactiva</strong>: sus usuarios no pueden iniciar sesión y sus datos están en solo lectura.</div>
@endif

@include('historial._pestanas', ['url' => route('sedes.show', $sede), 'pestana' => $pestana, 'otras' => [
    'organigrama' => ['Organigrama', 'fa-sitemap'],
    'registro' => ['Registro de miembros', 'fa-qrcode'],
]])

@if($pestana === 'historico')
    @include('historial._lista', ['historico' => $historico])
@elseif($pestana === 'organigrama')
    @include('organigrama._arbol', ['estado' => $organigrama['estado'], 'raiz' => $organigrama['raiz']])
@elseif($pestana === 'registro')
    @include('partials.panel-registro-miembros', ['sede' => $sede])
@else
@if($resumen)
    <div class="row">
        <div class="col-sm-3"><div class="info-box"><span class="info-box-icon text-bg-info"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Miembros</span><span class="info-box-number">{{ $resumen['personas'] }}</span></div></div></div>
        <div class="col-sm-3"><div class="info-box"><span class="info-box-icon text-bg-primary"><i class="fas fa-user-tie"></i></span><div class="info-box-content"><span class="info-box-text">Líderes</span><span class="info-box-number">{{ $resumen['lideres'] }}</span></div></div></div>
        <div class="col-sm-3"><div class="info-box"><span class="info-box-icon text-bg-secondary"><i class="fas fa-people-arrows"></i></span><div class="info-box-content"><span class="info-box-text">Células</span><span class="info-box-number">{{ $resumen['celulas'] }}</span></div></div></div>
        <div class="col-sm-3"><div class="info-box"><span class="info-box-icon text-bg-success"><i class="fas fa-glass-cheers"></i></span><div class="info-box-content"><span class="info-box-text">Eventos próximos</span><span class="info-box-number">{{ $resumen['eventos_proximos'] }}</span></div></div></div>
    </div>
@endif

<div class="card card-primary card-outline mb-4">
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

<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Usuarios de la sede ({{ $usuarios->count() }})</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="tabla-apilable table table-sm table-hover mb-0">
            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th></tr></thead>
            <tbody>
                @forelse($usuarios as $usuario)
                    <tr><td data-etiqueta="Nombre" data-primaria>{{ $usuario->name }}</td><td data-etiqueta="Correo">{{ $usuario->email }}</td><td data-etiqueta="Rol">{{ \App\Models\User::etiquetaDeRol($usuario->role) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center">Sin usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif
@endsection
