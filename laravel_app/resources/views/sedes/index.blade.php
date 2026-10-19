@extends('layouts.admin')

@section('title', 'Sedes')

@section('page-title', 'Sedes')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('sedes.create') }}" class="btn btn-primary">Nueva sede</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
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
                        <td><span class="badge badge-info">{{ $fila['personas'] }}</span></td>
                        <td><span class="badge badge-primary">{{ $fila['lideres'] }}</span></td>
                        <td><span class="badge badge-secondary">{{ $fila['celulas'] }}</span></td>
                        <td>
                            @if($sede->activa)
                                <span class="badge badge-success">Activa</span>
                            @else
                                <span class="badge badge-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('sedes.show', $sede) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('sedes.edit', $sede) }}" class="btn btn-sm btn-warning">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center">Aún no hay sedes. Crea la primera.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
