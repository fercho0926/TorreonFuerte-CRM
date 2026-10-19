@extends('layouts.admin')

@section('title', 'Ministerios')

@section('page-title', 'Ministerios')

@section('page-actions')
    <a href="{{ route('ministerios.create') }}" class="btn btn-primary">Crear ministerio</a>
@endsection

@section('content')


<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Listado de ministerios <small class="text-muted ms-2">{{ $ministerios->count() }} resultados</small></h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
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
                            <x-ui.estado :valor="$ministerio->estado" />
                        </td>
                        <td><x-ui.insignia color="info">{{ $ministerio->integrantes_count }}</x-ui.insignia></td>
                        @todasLasSedes<td>{{ $ministerio->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-end text-nowrap">
                            <x-ui.acciones-fila :ver="route('ministerios.show', $ministerio)" :editar="route('ministerios.edit', $ministerio)" :eliminar="route('ministerios.destroy', $ministerio)" eliminar-titulo="¿Eliminar este ministerio?" eliminar-mensaje="Esta acción no se puede deshacer." :nombre="$ministerio->nombre" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-ui.vacio mensaje="No hay ministerios registrados." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
