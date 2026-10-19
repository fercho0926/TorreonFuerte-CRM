@extends('layouts.admin')

@section('title', 'Detalle del encuentro')

@section('page-title', 'Detalle del encuentro')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('encuentros.edit', $encuentro) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('encuentros.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $encuentro->nombre }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Fecha de realización</dt>
            <dd class="col-sm-9">{{ $encuentro->fecha_realizacion->format('Y-m-d') }}</dd>

            <dt class="col-sm-3">Lugar</dt>
            <dd class="col-sm-9">{{ $encuentro->lugar ?? '—' }}</dd>

            <dt class="col-sm-3">Valor</dt>
            <dd class="col-sm-9">${{ number_format($encuentro->valor, 2) }}</dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                @php
                    $badge = ['planificado' => 'info', 'realizado' => 'success', 'cancelado' => 'danger'][$encuentro->estado] ?? 'secondary';
                @endphp
                <span class="badge badge-{{ $badge }}">{{ ucfirst($encuentro->estado) }}</span>
            </dd>

            <dt class="col-sm-3">Notas</dt>
            <dd class="col-sm-9">{{ $encuentro->notas ?? '—' }}</dd>

            <dt class="col-sm-3">Total recaudado</dt>
            <dd class="col-sm-9">${{ number_format($encuentro->totalRecaudado(), 2) }}</dd>
        </dl>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Personas participantes ({{ $encuentro->personas->count() }})</h3>
    </div>
    <form method="POST" action="{{ route('encuentros.asistencia', $encuentro) }}">
        @csrf
        @method('PUT')
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Nombre completo</th>
                        <th>Documento</th>
                        <th>Red</th>
                        <th>Asistió</th>
                        <th>Valor pagado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($encuentro->personas as $persona)
                        <tr>
                            <td>{{ $persona->nombre_completo }}</td>
                            <td>{{ $persona->tipo_documento }} {{ $persona->numero_documento }}</td>
                            <td>{{ $persona->redLider?->nombre ?? $persona->lider?->redLider?->nombre ?? '—' }}</td>
                            <td>
                                <input type="checkbox" name="asistio[]" value="{{ $persona->id }}" {{ $persona->pivot->asistio ? 'checked' : '' }}>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" step="0.01" min="0" name="valor_pagado[{{ $persona->id }}]" class="form-control" value="{{ $persona->pivot->valor_pagado }}">
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Este encuentro aún no tiene personas asignadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($encuentro->personas->isNotEmpty())
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Guardar asistencia y pagos</button>
            </div>
        @endif
    </form>
</div>
@endsection
