@extends('layouts.admin')

@section('title', 'Detalle del ministerio')

@section('page-title', 'Detalle del ministerio')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('ministerios.edit', $ministerio) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('ministerios.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $ministerio->nombre }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Encargado</dt>
            <dd class="col-sm-9">
                @if($ministerio->encargado)
                    {{ $ministerio->encargado->nombre_completo }}
                @else
                    <span class="text-muted">Sin encargado asignado</span>
                @endif
            </dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                <span class="badge badge-{{ $ministerio->estado === 'activo' ? 'success' : 'secondary' }}">{{ ucfirst($ministerio->estado) }}</span>
            </dd>

            <dt class="col-sm-3">Descripción</dt>
            <dd class="col-sm-9">{{ $ministerio->descripcion ?? '—' }}</dd>
        </dl>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Integrantes ({{ $ministerio->integrantes->count() }})</h3>
    </div>
    <form method="POST" action="{{ route('ministerios.integrantes', $ministerio) }}">
        @csrf
        @method('PUT')
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Nombre completo</th>
                        <th>Red</th>
                        <th>Rol en el ministerio</th>
                        <th>Fecha de ingreso</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ministerio->integrantes as $persona)
                        <tr>
                            <td>
                                {{ $persona->nombre_completo }}
                                @if($persona->es_lider)
                                    <span class="badge badge-primary">Líder</span>
                                @endif
                            </td>
                            <td>{{ $persona->redLider?->nombre ?? $persona->lider?->redLider?->nombre ?? '—' }}</td>
                            <td>
                                <input type="text" name="rol[{{ $persona->id }}]" class="form-control form-control-sm" value="{{ $persona->pivot->rol }}" placeholder="Ej. Coordinador, Integrante...">
                            </td>
                            <td>
                                <input type="date" name="fecha_ingreso[{{ $persona->id }}]" class="form-control form-control-sm" value="{{ $persona->pivot->fecha_ingreso }}">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Este ministerio aún no tiene integrantes. Agrégalos desde "Editar".</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ministerio->integrantes->isNotEmpty())
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Guardar cambios de integrantes</button>
            </div>
        @endif
    </form>
</div>
@endsection
