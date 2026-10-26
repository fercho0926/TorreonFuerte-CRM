@extends('layouts.admin')

@section('title', $rol->nombre)

@section('page-title', 'Rol: '.$rol->nombre)

@section('page-actions')
    @unless($rol->esAdministrador())
        <a href="{{ route('roles.edit', $rol) }}" class="btn btn-primary">Editar</a>
    @endunless
    <a href="{{ route('roles.create', ['duplicar' => $rol->clave]) }}" class="btn btn-outline-primary">Duplicar</a>
    @if(!$rol->es_sistema)
        <x-form.confirm :action="route('roles.destroy', $rol)" method="DELETE" titulo="¿Eliminar este rol?" :mensaje="$usuarios->isEmpty() ? 'Se elimina el rol; su historial se conserva.' : 'Tiene '.$usuarios->count().' usuarios: reasígnalos antes de eliminarlo.'" boton="Eliminar" :peligro="true" variante="outline-danger" tamano="">Eliminar</x-form.confirm>
    @endif
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')
@include('users._pestanas', ['activa' => 'roles'])

@if($rol->esAdministrador())
    <div class="alert alert-info" role="status"><i class="fas fa-lock" aria-hidden="true"></i> Rol protegido: siempre tiene escritura en todos los módulos y no se puede modificar ni eliminar.</div>
@endif

@include('historial._pestanas', ['url' => route('roles.show', $rol), 'pestana' => $pestana])

@if($pestana === 'historico')
    @include('historial._lista', ['historico' => $historico])
@else

<div class="card card-primary card-outline mb-4">
    <div class="card-header"><h3 class="card-title">Datos del rol</h3></div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Nombre</dt><dd class="col-sm-9">{{ $rol->nombre }}</dd>
            <dt class="col-sm-3">Descripción</dt><dd class="col-sm-9">{{ $rol->descripcion ?? '—' }}</dd>
            <dt class="col-sm-3">Alcance de datos</dt><dd class="col-sm-9">{{ $rol->etiquetaAlcance() }}</dd>
            <dt class="col-sm-3">Tipo</dt><dd class="col-sm-9">{{ $rol->es_sistema ? 'Sistema' : 'Personalizado' }}</dd>
            <dt class="col-sm-3">Asignable por quien gestiona usuarios</dt><dd class="col-sm-9">{{ $rol->asignable ? 'Sí' : 'No' }}</dd>
        </dl>
    </div>
</div>

<x-form.matriz-permisos :niveles="$niveles" :solo-lectura="true" titulo="Permisos por módulo" />

<x-ui.tarjeta titulo="Usuarios con este rol" icono="fa-users" :contador="$usuarios->count()" :tabla="true" id="usuarios-del-rol">
    <table class="tabla-apilable table table-sm table-hover mb-0">
        <thead><tr><th>Nombre</th><th>Correo</th><th>Sede</th></tr></thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td data-etiqueta="Nombre" data-primaria>{{ $usuario->name }}</td>
                    <td data-etiqueta="Correo">{{ $usuario->email }}</td>
                    <td data-etiqueta="Sede">{{ $usuario->sede?->nombre ?? 'Todas (administrador)' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">Ningún usuario tiene este rol.</td></tr>
            @endforelse
        </tbody>
    </table>
</x-ui.tarjeta>

@endif
@endsection
