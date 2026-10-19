@extends('layouts.admin')

@section('title', 'Usuarios')

@section('page-title', 'Usuarios registrados')

@section('page-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary">Crear usuario</a>
@endsection

@section('content')


@if($sedes->isNotEmpty())
    <x-form.layout :action="route('users.index')" method="GET" modo="filtro" guardar="Filtrar" icono="fa-filter" :leyenda="false">
        <x-form.select name="sede_id" label="Sede" icono="fa-church" :opciones="$sedes->map(fn ($x) => ['id' => $x->id, 'texto' => $x->nombre])->all()" :value="$filtroSede" vacio="Todas" col="col-md-4" />
    </x-form.layout>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Lista de usuarios</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    @if($sedes->isNotEmpty())<th>Sede</th>@endif
                    <th>Persona vinculada</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><x-ui.insignia color="secondary">{{ \App\Models\User::ROLE_LABELS[$user->role] ?? $user->role }}</x-ui.insignia></td>
                        @if($sedes->isNotEmpty())<td>{{ $user->sede?->nombre ?? 'Todas (administrador)' }}</td>@endif
                        <td>{{ $user->persona?->nombre_completo ?? '—' }}</td>
                        <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end text-nowrap">
                            <x-ui.acciones-fila :editar="route('users.edit', $user)" :eliminar="route('users.destroy', $user)" :puede-editar="\App\Support\Permisos::puede('gestionar', $user)" :puede-eliminar="\App\Support\Permisos::puede('gestionar', $user)" eliminar-titulo="¿Eliminar usuario?" eliminar-mensaje="Esta acción no se puede deshacer." :nombre="$user->name" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.vacio mensaje="No hay usuarios registrados." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
