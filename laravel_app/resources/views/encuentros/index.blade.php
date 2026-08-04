@extends('layouts.admin')

@section('title', 'Encuentros')

@section('page-title', 'Encuentros')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('encuentros.create') }}" class="btn btn-primary">Crear encuentro</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Listado de encuentros</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Lugar</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Participantes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($encuentros as $encuentro)
                    <tr>
                        <td>{{ $encuentro->id }}</td>
                        <td>{{ $encuentro->nombre }}</td>
                        <td>{{ $encuentro->fecha_realizacion->format('Y-m-d') }}</td>
                        <td>{{ $encuentro->lugar ?? '—' }}</td>
                        <td>${{ number_format($encuentro->valor, 2) }}</td>
                        <td>
                            @php
                                $badge = ['planificado' => 'info', 'realizado' => 'success', 'cancelado' => 'danger'][$encuentro->estado] ?? 'secondary';
                            @endphp
                            <span class="badge badge-{{ $badge }}">{{ ucfirst($encuentro->estado) }}</span>
                        </td>
                        <td><span class="badge badge-secondary">{{ $encuentro->personas_count }}</span></td>
                        <td>
                            <a href="{{ route('encuentros.show', $encuentro) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('encuentros.edit', $encuentro) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('encuentros.destroy', $encuentro) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este encuentro?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay encuentros registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
