@extends('layouts.admin')

@section('title', 'Personas')

@section('page-title', 'Personas')

@section('content')
@puede('crear')
    <div class="row mb-3">
        <div class="col-12 text-right">
            <a href="{{ route('personas.create') }}" class="btn btn-primary">Crear persona</a>
        </div>
    </div>
@endpuede

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($sinPersonaVinculada)
    <div class="alert alert-info">Tu cuenta aún no está asociada a una persona, por eso no ves a nadie. Pide a un administrador que la vincule.</div>
@endif

<div class="card card-outline card-secondary mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('personas.index') }}">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Buscar</label>
                    <input type="search" name="q" class="form-control" value="{{ $filtros['q'] }}" placeholder="Nombre, apellido, documento o celular">
                </div>
                <div class="form-group col-md-2">
                    <label>Estado</label>
                    <select name="estado" class="form-control">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Persona::ESTADOS as $valor => $etiqueta)
                            <option value="{{ $valor }}" {{ $filtros['estado'] === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Red</label>
                    <select name="red_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($redes as $red)
                            <option value="{{ $red->id }}" {{ (string) $filtros['red_id'] === (string) $red->id ? 'selected' : '' }}>{{ $red->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Zona</label>
                    <select name="zona_id" class="form-control">
                        <option value="">Todas</option>
                        @foreach($zonas as $zona)
                            <option value="{{ $zona->id }}" {{ (string) $filtros['zona_id'] === (string) $zona->id ? 'selected' : '' }}>{{ $zona->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Líder</label>
                    <select name="lider_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($lideres as $lider)
                            <option value="{{ $lider->id }}" {{ (string) $filtros['lider_id'] === (string) $lider->id ? 'selected' : '' }}>{{ $lider->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" class="custom-control-input" id="inactivos" name="inactivos" value="1" {{ $filtros['inactivos'] ? 'checked' : '' }}>
                    <label class="custom-control-label" for="inactivos">Incluir inactivos</label>
                </div>
                <button type="submit" class="btn btn-primary mr-2">Buscar</button>
                <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Personas ({{ $personas->total() }})</h3>
    </div>
    <div class="card-body p-0">
        {{-- Tarjetas en pantallas pequeñas --}}
        <div class="d-md-none p-2">
            @forelse($personas as $persona)
                @include('partials.persona-tarjeta', ['persona' => $persona])
            @empty
                <p class="text-center text-muted my-3">No se encontraron personas.</p>
            @endforelse
        </div>

        {{-- Tabla desde pantallas medianas --}}
        <div class="d-none d-md-block table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Celular</th>
                        <th>Red</th>
                        <th>Líder</th>
                        @todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personas as $persona)
                        <tr>
                            <td>
                                <a href="{{ route('personas.show', $persona) }}">{{ $persona->nombre_completo }}</a>
                                @if($persona->es_lider)<span class="badge badge-primary">Líder</span>@endif
                            </td>
                            <td><span class="badge badge-{{ $persona->estado === 'inactivo' ? 'secondary' : ($persona->estado === 'visitante' ? 'warning' : 'success') }}">{{ $persona->estado_etiqueta }}</span></td>
                            <td>{{ $persona->celular ?? '—' }}</td>
                            <td>{{ $persona->redLider?->nombre ?? $persona->lider?->redLider?->nombre ?? '—' }}</td>
                            <td>{{ $persona->lider?->nombre_completo ?? '—' }}</td>
                            @todasLasSedes<td>{{ $persona->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-nowrap">
                                <a href="{{ route('personas.show', $persona) }}" class="btn btn-sm btn-info">Ver</a>
                                @puede('editar', $persona)
                                    <a href="{{ route('personas.edit', $persona) }}" class="btn btn-sm btn-warning">Editar</a>
                                @endpuede
                                @puede('inactivar', $persona)
                                    @if($persona->estado !== 'inactivo')
                                        <form action="{{ route('personas.inactivar', $persona) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('¿Inactivar a esta persona? Su historial se conserva.')">Inactivar</button>
                                        </form>
                                    @endif
                                @endpuede
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No se encontraron personas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($personas->hasPages())
        <div class="card-footer">{{ $personas->links() }}</div>
    @endif
</div>
@endsection
