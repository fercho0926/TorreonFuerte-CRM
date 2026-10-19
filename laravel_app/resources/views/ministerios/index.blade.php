@extends('layouts.admin')

@section('title', 'Ministerios')

@section('page-title', 'Ministerios')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('ministerios.create') }}" class="btn btn-primary">Crear ministerio</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Listado de ministerios</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Encargado</th>
                    <th>Estado</th>
                    <th>Integrantes</th>
                    @todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ministerios as $ministerio)
                    <tr>
                        <td>{{ $ministerio->id }}</td>
                        <td>{{ $ministerio->nombre }}</td>
                        <td>{{ $ministerio->encargado?->nombre_completo ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $ministerio->estado === 'activo' ? 'success' : 'secondary' }}">{{ ucfirst($ministerio->estado) }}</span>
                        </td>
                        <td><span class="badge badge-info">{{ $ministerio->integrantes_count }}</span></td>
                        @todasLasSedes<td>{{ $ministerio->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td>
                            <a href="{{ route('ministerios.show', $ministerio) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('ministerios.edit', $ministerio) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('ministerios.destroy', $ministerio) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este ministerio?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay ministerios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
