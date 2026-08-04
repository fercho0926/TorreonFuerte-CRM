@extends('layouts.admin')

@section('title', 'Células')

@section('page-title', 'Células / Grupos pequeños')

@section('content')
@if($puedeGestionarEstructura)
    <div class="row mb-3">
        <div class="col-12 text-right">
            <a href="{{ route('celulas.create') }}" class="btn btn-primary">Crear célula</a>
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
        <h3 class="card-title">Listado de células</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Líder</th>
                    <th>Co-líder</th>
                    <th>Sede</th>
                    <th>Reunión</th>
                    <th>Estado</th>
                    <th>Miembros</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($celulas as $celula)
                    <tr>
                        <td>{{ $celula->id }}</td>
                        <td>{{ $celula->nombre }}</td>
                        <td>{{ $celula->lider->nombre_completo }}</td>
                        <td>{{ $celula->coLider?->nombre_completo ?? '—' }}</td>
                        <td>{{ $celula->sede ?? '—' }}</td>
                        <td>{{ $celula->dia_reunion ?? '—' }} {{ $celula->hora_reunion ? substr($celula->hora_reunion, 0, 5) : '' }}</td>
                        <td>
                            <span class="badge badge-{{ $celula->estado === 'activa' ? 'success' : 'secondary' }}">{{ ucfirst($celula->estado) }}</span>
                        </td>
                        <td><span class="badge badge-info">{{ $celula->miembros_count }}</span></td>
                        <td>
                            <a href="{{ route('celulas.show', $celula) }}" class="btn btn-sm btn-info">Ver</a>
                            @if($puedeGestionarEstructura)
                                <a href="{{ route('celulas.edit', $celula) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('celulas.destroy', $celula) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta célula?')">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay células registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
