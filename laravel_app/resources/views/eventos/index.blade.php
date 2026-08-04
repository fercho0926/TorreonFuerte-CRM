@extends('layouts.admin')

@section('title', 'Eventos')

@section('page-title', 'Eventos')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('eventos.create') }}" class="btn btn-primary">Crear evento</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Listado de eventos</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Lugar</th>
                    <th>Costo</th>
                    <th>Estado</th>
                    <th>Inscritos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($eventos as $evento)
                    <tr>
                        <td>{{ $evento->id }}</td>
                        <td>{{ $evento->nombre }}</td>
                        <td>{{ $evento->tipo }}</td>
                        <td>{{ $evento->fecha->format('Y-m-d') }}</td>
                        <td>{{ $evento->lugar ?? '—' }}</td>
                        <td>${{ number_format($evento->costo, 2) }}</td>
                        <td>
                            @php
                                $badge = ['planificado' => 'info', 'realizado' => 'success', 'cancelado' => 'danger'][$evento->estado] ?? 'secondary';
                            @endphp
                            <span class="badge badge-{{ $badge }}">{{ ucfirst($evento->estado) }}</span>
                        </td>
                        <td><span class="badge badge-secondary">{{ $evento->inscripciones_count }}</span></td>
                        <td>
                            <a href="{{ route('eventos.show', $evento) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('eventos.edit', $evento) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('eventos.destroy', $evento) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este evento? Se perderá el historial de inscritos y abonos.')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay eventos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
