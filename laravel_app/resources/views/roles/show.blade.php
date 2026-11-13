@extends('layouts.admin')

@section('title', $rol->nombre)

@section('page-title', 'Rol: '.$rol->nombre)

@section('page-actions')
    @unless($rol->esAdministrador())
        <a href="{{ route('roles.edit', $rol) }}" class="btn btn-primary">{{ __('interfaz.editar') }}</a>
    @endunless
    <a href="{{ route('roles.create', ['duplicar' => $rol->clave]) }}" class="btn btn-outline-primary">{{ __('interfaz.duplicar') }}</a>
    @if(!$rol->es_sistema)
        <x-form.confirm :action="route('roles.destroy', $rol)" method="DELETE" titulo="¿Eliminar este rol?" :mensaje="$usuarios->isEmpty() ? 'Se elimina el rol; su historial se conserva.' : 'Tiene '.$usuarios->count().' usuarios: reasígnalos antes de eliminarlo.'" boton="Eliminar" :peligro="true" variante="outline-danger" tamano="">{{ __('interfaz.eliminar') }}</x-form.confirm>
    @endif
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">{{ __('interfaz.volver') }}</a>
@endsection

@section('content')
@include('users._pestanas', ['activa' => 'roles'])

@if($rol->esAdministrador())
    <div class="alert alert-info" role="status"><i class="fas fa-lock" aria-hidden="true"></i> {{ __('interfaz.rol_protegido_siempre_tiene_escritura_en') }}</div>
@endif

@include('historial._pestanas', ['url' => route('roles.show', $rol), 'pestana' => $pestana])

@if($pestana === 'historico')
    @include('historial._lista', ['historico' => $historico])
@else

<div class="card card-primary card-outline mb-4">
    <div class="card-header"><h3 class="card-title">{{ __('interfaz.datos_del_rol') }}</h3></div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('interfaz.nombre') }}</dt><dd class="col-sm-9">{{ $rol->nombre }}</dd>
            <dt class="col-sm-3">{{ __('interfaz.descripcion') }}</dt><dd class="col-sm-9">{{ $rol->descripcion ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('interfaz.alcance_de_datos') }}</dt><dd class="col-sm-9">{{ $rol->etiquetaAlcance() }}</dd>
            <dt class="col-sm-3">{{ __('interfaz.tipo') }}</dt><dd class="col-sm-9">{{ $rol->es_sistema ? 'Sistema' : 'Personalizado' }}</dd>
            <dt class="col-sm-3">{{ __('interfaz.asignable_por_quien_gestiona_usuarios') }}</dt><dd class="col-sm-9">{{ $rol->asignable ? 'Sí' : 'No' }}</dd>
        </dl>
    </div>
</div>

<x-form.matriz-permisos :niveles="$niveles" :solo-lectura="true" titulo="Permisos por módulo" />

<x-ui.tarjeta titulo="Usuarios con este rol" icono="fa-users" :contador="$usuarios->count()" :tabla="true" id="usuarios-del-rol">
    <table class="tabla-apilable table table-sm table-hover mb-0">
        <thead><tr><th>{{ __('interfaz.nombre') }}</th><th>{{ __('interfaz.correo') }}</th><th>{{ __('interfaz.sede') }}</th></tr></thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td data-etiqueta="Nombre" data-primaria>{{ $usuario->name }}</td>
                    <td data-etiqueta="Correo">{{ $usuario->email }}</td>
                    <td data-etiqueta="Sede">{{ $usuario->sede?->nombre ?? 'Todas (administrador)' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">{{ __('interfaz.ningun_usuario_tiene_este_rol') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</x-ui.tarjeta>

@endif
@endsection
