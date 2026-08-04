@extends('layouts.admin')

@section('title', 'Personas')

@section('page-title', 'Personas')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('personas.create') }}" class="btn btn-primary">Crear persona</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card card-outline card-secondary mb-3">
    <div class="card-header">
        <h3 class="card-title">Filtros</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('personas.index') }}" class="form-inline">
            <div class="form-group mr-3">
                <label class="mr-2">Red</label>
                <select name="red" class="form-control" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    @foreach($redes as $red)
                        <option value="{{ $red }}" {{ $filtroRed === $red ? 'selected' : '' }}>{{ $red }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mr-3">
                <label class="mr-2">Líder de célula</label>
                <select name="lider_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach($lideres as $lider)
                        <option value="{{ $lider->id }}" {{ (string) $filtroLiderId === (string) $lider->id ? 'selected' : '' }}>{{ $lider->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            @if($filtroRed || $filtroLiderId)
                <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">Limpiar filtros</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Listado de personas ({{ $personas->count() }})</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th>Documento</th>
                    <th>Celular</th>
                    <th>Ciudad</th>
                    <th>Red</th>
                    <th>Líder de célula</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($personas as $persona)
                    <tr>
                        <td>{{ $persona->id }}</td>
                        <td>
                            {{ $persona->nombre_completo }}
                            @if($persona->es_lider)
                                <span class="badge badge-primary">Líder</span>
                            @endif
                        </td>
                        <td>{{ $persona->tipo_documento }} {{ $persona->numero_documento }}</td>
                        <td>{{ $persona->celular }}</td>
                        <td>{{ $persona->ciudad }}</td>
                        <td>{{ $persona->red ?? $persona->lider?->red ?? '—' }}</td>
                        <td>{{ $persona->lider?->nombre_completo ?? '—' }}</td>
                        <td>
                            <a href="{{ route('personas.show', $persona) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('personas.edit', $persona) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('personas.destroy', $persona) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta persona?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay personas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
