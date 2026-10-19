@extends('layouts.admin')

@section('title', 'Eventos')

@section('page-title', 'Eventos')

@section('page-actions')
    <a href="{{ route('eventos.create') }}" class="btn btn-primary">Crear evento</a>
@endsection

@section('content')


<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Listado de eventos <small class="text-muted ms-2">{{ $eventos->count() }} resultados</small></h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
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
                    @todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th>
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
                            <x-ui.estado :valor="$evento->estado" />
                        </td>
                        <td><x-ui.insignia color="secondary">{{ $evento->inscripciones_count }}</x-ui.insignia></td>
                        @todasLasSedes<td>{{ $evento->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-end text-nowrap">
                            <x-ui.acciones-fila :ver="route('eventos.show', $evento)" :editar="route('eventos.edit', $evento)" :eliminar="route('eventos.destroy', $evento)" eliminar-titulo="¿Eliminar este evento?" eliminar-mensaje="Se perderá el historial de inscritos y abonos." :nombre="$evento->nombre" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-ui.vacio mensaje="No hay eventos registrados." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
