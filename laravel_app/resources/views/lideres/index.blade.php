@extends('layouts.admin')

@section('title', 'Líderes')

@section('page-title', 'Líderes de red')

@section('content')
@if($puedeGestionarEstructura)
    <div class="row mb-3">
        <div class="col-12 text-right">
            <a href="{{ route('lideres.create') }}" class="btn btn-primary">Crear líder</a>
        </div>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Listado de líderes</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th>Red</th>
                    <th>Documento</th>
                    <th>Celular</th>
                    <th>Personas lideradas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lideres as $lider)
                    <tr>
                        <td>{{ $lider->id }}</td>
                        <td>{{ $lider->nombre_completo }}</td>
                        <td>{{ $lider->red }}</td>
                        <td>{{ $lider->tipo_documento }} {{ $lider->numero_documento }}</td>
                        <td>{{ $lider->celular }}</td>
                        <td><span class="badge badge-info">{{ $lider->miembros_count }}</span></td>
                        <td>
                            <a href="{{ route('lideres.show', $lider) }}" class="btn btn-sm btn-info">Ver</a>
                            @if($puedeGestionarEstructura)
                                <a href="{{ route('lideres.edit', $lider) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('lideres.destroy', $lider) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Quitar el rol de líder a esta persona? Sus miembros quedarán sin líder asignado.')">Quitar rol</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay líderes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
