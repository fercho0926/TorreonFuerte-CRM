@extends('layouts.admin')

@section('title', 'Células')

@section('page-title', 'Células / Grupos pequeños')

@if($puedeGestionarEstructura)
    @section('page-actions')
            <a href="{{ route('celulas.create') }}" class="btn btn-primary">Crear célula</a>
    @endsection
@endif

@section('content')


<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Listado de células <small class="text-muted ms-2">{{ $celulas->count() }} resultados</small></h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
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
                    @todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th>
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
                            <x-ui.estado :valor="$celula->estado" />
                        </td>
                        <td><x-ui.insignia color="info">{{ $celula->miembros_count }}</x-ui.insignia></td>
                        @todasLasSedes<td>{{ $celula->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-end text-nowrap">
                            <x-ui.acciones-fila :ver="route('celulas.show', $celula)" :editar="route('celulas.edit', $celula)" :eliminar="route('celulas.destroy', $celula)" :puede-editar="$puedeGestionarEstructura" :puede-eliminar="$puedeGestionarEstructura" eliminar-titulo="¿Eliminar esta célula?" :nombre="$celula->nombre" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-ui.vacio mensaje="No hay células registradas." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
