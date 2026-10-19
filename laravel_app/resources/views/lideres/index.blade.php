@extends('layouts.admin')

@section('title', 'Líderes')

@section('page-title', 'Líderes')

@section('content')
@puede('designarLider')
    <div class="row mb-3">
        <div class="col-12 text-right">
            <a href="{{ route('lideres.designar') }}" class="btn btn-primary">Designar líder</a>
        </div>
    </div>
@endpuede

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@include('partials.ayuda-terminos')

<div class="card card-outline card-secondary mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('lideres.index') }}" class="form-row">
            <div class="form-group col-md-4">
                <label>Buscar</label>
                <input type="search" name="q" class="form-control" value="{{ $filtros['q'] ?? '' }}" placeholder="Nombre, documento o celular">
            </div>
            <div class="form-group col-md-3">
                <label>Red</label>
                <select name="red_id" class="form-control">
                    <option value="">Todas</option>
                    @foreach($redes as $red)
                        <option value="{{ $red->id }}" {{ (string) ($filtros['red_id'] ?? '') === (string) $red->id ? 'selected' : '' }}>{{ $red->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Zona</label>
                <select name="zona_id" class="form-control">
                    <option value="">Todas</option>
                    @foreach($zonas as $zona)
                        <option value="{{ $zona->id }}" {{ (string) ($filtros['zona_id'] ?? '') === (string) $zona->id ? 'selected' : '' }}>{{ $zona->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary mr-2">Buscar</button>
                <a href="{{ route('lideres.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Líderes ({{ $lideres->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <div class="d-md-none p-2">
            @forelse($lideres as $lider)
                @include('partials.persona-tarjeta', ['persona' => $lider])
            @empty
                <p class="text-center text-muted my-3">No hay líderes registrados.</p>
            @endforelse
        </div>

        <div class="d-none d-md-block table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Red</th>
                        <th>Zona</th>
                        <th>Personas</th>
                        <th>Células</th>
                        <th>Celular</th>
                        @todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lideres as $lider)
                        <tr>
                            <td><a href="{{ route('lideres.show', $lider) }}">{{ $lider->nombre_completo }}</a></td>
                            <td>{{ $lider->redLider?->nombre ?? '—' }}</td>
                            <td>{{ $lider->zona?->nombre ?? '—' }}</td>
                            <td><span class="badge badge-info">{{ $lider->miembros_count }}</span></td>
                            <td><span class="badge badge-secondary">{{ $lider->celulas_lideradas_count }}</span></td>
                            <td>{{ $lider->celular ?? '—' }}</td>
                            @todasLasSedes<td>{{ $lider->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-nowrap">
                                <a href="{{ route('lideres.show', $lider) }}" class="btn btn-sm btn-info">Ver</a>
                                @puede('designarLider')
                                    <a href="{{ route('personas.edit', $lider) }}" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="{{ route('lideres.quitar', $lider) }}" class="btn btn-sm btn-outline-danger">Quitar rol</a>
                                @endpuede
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
    @if($lideres->hasPages())
        <div class="card-footer">{{ $lideres->links() }}</div>
    @endif
</div>
@endsection
