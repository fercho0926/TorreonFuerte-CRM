@extends('layouts.admin')

@section('title', 'Usuarios')

@section('page-title', 'Usuarios registrados')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('users.create') }}" class="btn btn-primary">Crear usuario</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($sedes->isNotEmpty())
    <form method="GET" action="{{ route('users.index') }}" class="form-inline mb-3">
        <label class="mr-2">Sede</label>
        <select name="sede_id" class="form-control mr-2" onchange="this.form.submit()">
            <option value="">Todas</option>
            @foreach($sedes as $sede)
                <option value="{{ $sede->id }}" {{ (string) $filtroSede === (string) $sede->id ? 'selected' : '' }}>{{ $sede->nombre }}</option>
            @endforeach
        </select>
    </form>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de usuarios</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
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
                        <td><span class="badge badge-secondary">{{ \App\Models\User::ROLE_LABELS[$user->role] ?? $user->role }}</span></td>
                        @if($sedes->isNotEmpty())<td>{{ $user->sede?->nombre ?? 'Todas (administrador)' }}</td>@endif
                        <td>{{ $user->persona?->nombre_completo ?? '—' }}</td>
                        <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            @puede('gestionar', $user)
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar usuario?')">Eliminar</button>
                                </form>
                            @endpuede
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay usuarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
