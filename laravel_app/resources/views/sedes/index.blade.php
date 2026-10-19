@extends('layouts.admin')

@section('title', 'Sedes')

@section('page-title', 'Sedes')

@section('page-actions')
    <a href="{{ route('sedes.create') }}" class="btn btn-primary">Nueva sede</a>
@endsection

@section('content')


<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Iglesias ({{ $resumen->count() }})</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Sede</th>
                    <th>Ciudad</th>
                    <th>Pastor</th>
                    <th>Pastora</th>
                    <th>Personas</th>
                    <th>Líderes</th>
                    <th>Células</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($resumen as $fila)
                    @php($sede = $fila['sede'])
                    <tr>
                        <td><a href="{{ route('sedes.show', $sede) }}">{{ $sede->nombre }}</a></td>
                        <td>{{ $sede->ciudad }}</td>
                        <td>{{ $sede->pastor?->nombre_completo ?? '—' }}</td>
                        <td>{{ $sede->pastora?->nombre_completo ?? '—' }}</td>
                        <td><x-ui.insignia color="info">{{ $fila['personas'] }}</x-ui.insignia></td>
                        <td><x-ui.insignia color="primary">{{ $fila['lideres'] }}</x-ui.insignia></td>
                        <td><x-ui.insignia color="secondary">{{ $fila['celulas'] }}</x-ui.insignia></td>
                        <td>
                            @if($sede->activa)
                                <x-ui.estado valor="activa" />
                            @else
                                <x-ui.estado valor="inactiva" />
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <x-ui.acciones-fila :ver="route('sedes.show', $sede)" :editar="route('sedes.edit', $sede)" :nombre="$sede->nombre" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-ui.vacio mensaje="Aún no hay sedes. Crea la primera." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
