@extends('layouts.admin')

@section('title', 'Encuentros')

@section('page-title', 'Encuentros')

@section('page-actions')
    <a href="{{ route('encuentros.create') }}" class="btn btn-primary">Crear encuentro</a>
@endsection

@section('content')


<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Listado de encuentros <small class="text-muted ms-2">{{ $encuentros->count() }} resultados</small></h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Lugar</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Participantes</th>
                    @todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th>
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
                            <x-ui.estado :valor="$encuentro->estado" />
                        </td>
                        <td><x-ui.insignia color="secondary">{{ $encuentro->personas_count }}</x-ui.insignia></td>
                        @todasLasSedes<td>{{ $encuentro->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-end text-nowrap">
                            <x-ui.acciones-fila :ver="route('encuentros.show', $encuentro)" :editar="route('encuentros.edit', $encuentro)" :eliminar="route('encuentros.destroy', $encuentro)" eliminar-titulo="¿Eliminar este encuentro?" eliminar-mensaje="Esta acción no se puede deshacer." :nombre="$encuentro->nombre" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-ui.vacio mensaje="No hay encuentros registrados." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
