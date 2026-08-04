@extends('layouts.admin')

@section('title', 'Detalle del evento')

@section('page-title', 'Detalle del evento')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('eventos.edit', $evento) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('eventos.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $evento->nombre }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Tipo</dt>
            <dd class="col-sm-9">{{ $evento->tipo }}</dd>

            <dt class="col-sm-3">Fecha</dt>
            <dd class="col-sm-9">{{ $evento->fecha->format('Y-m-d') }}</dd>

            <dt class="col-sm-3">Lugar</dt>
            <dd class="col-sm-9">{{ $evento->lugar ?? '—' }}</dd>

            <dt class="col-sm-3">Costo por persona</dt>
            <dd class="col-sm-9">${{ number_format($evento->costo, 2) }}</dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                @php
                    $badge = ['planificado' => 'info', 'realizado' => 'success', 'cancelado' => 'danger'][$evento->estado] ?? 'secondary';
                @endphp
                <span class="badge badge-{{ $badge }}">{{ ucfirst($evento->estado) }}</span>
            </dd>

            <dt class="col-sm-3">Descripción</dt>
            <dd class="col-sm-9">{{ $evento->descripcion ?? '—' }}</dd>

            <dt class="col-sm-3">Total esperado</dt>
            <dd class="col-sm-9">${{ number_format($evento->totalEsperado(), 2) }}</dd>

            <dt class="col-sm-3">Total recaudado</dt>
            <dd class="col-sm-9">${{ number_format($evento->totalRecaudado(), 2) }}</dd>
        </dl>
    </div>
</div>

<div class="card card-outline card-secondary mb-3">
    <div class="card-header">
        <h3 class="card-title">Inscribir persona</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('eventos.personas.store', $evento) }}" class="form-inline">
            @csrf
            <div class="form-group mr-2">
                <select name="persona_id" class="form-control" required>
                    <option value="">Seleccione una persona...</option>
                    @foreach($personasSinInscribir as $persona)
                        <option value="{{ $persona->id }}">{{ $persona->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mr-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="costo_individual" class="form-control" placeholder="Costo (opcional)">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Inscribir</button>
        </form>
        <small class="form-text text-muted">Deja el costo en blanco para usar el costo general del evento (${{ number_format($evento->costo, 2) }}).</small>
    </div>
</div>

<h3 class="mb-3">Personas inscritas ({{ $evento->inscripciones->count() }})</h3>

@forelse($evento->inscripciones as $inscripcion)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{ $inscripcion->persona->nombre_completo }}
                @if($inscripcion->estaPagado())
                    <span class="badge badge-success">Pagado</span>
                @else
                    <span class="badge badge-warning">Pendiente</span>
                @endif
            </h3>
            <div class="card-tools">
                @if($inscripcion->pagos->isEmpty())
                    <form action="{{ route('eventos.personas.destroy', [$evento, $inscripcion]) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Quitar a esta persona del evento?')">Quitar</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Costo aplicable:</strong> ${{ number_format($inscripcion->costoAplicable(), 2) }}</div>
                <div class="col-md-4"><strong>Total pagado:</strong> ${{ number_format($inscripcion->totalPagado(), 2) }}</div>
                <div class="col-md-4"><strong>Saldo pendiente:</strong> ${{ number_format($inscripcion->saldoPendiente(), 2) }}</div>
            </div>

            @if($inscripcion->pagos->isNotEmpty())
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Notas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inscripcion->pagos as $pago)
                            <tr>
                                <td>{{ $pago->fecha_pago->format('Y-m-d') }}</td>
                                <td>${{ number_format($pago->monto, 2) }}</td>
                                <td>{{ $pago->notas ?? '—' }}</td>
                                <td>
                                    <form action="{{ route('eventos.pagos.destroy', [$evento, $inscripcion, $pago]) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('¿Eliminar este abono?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if(!$inscripcion->estaPagado())
                <form method="POST" action="{{ route('eventos.pagos.store', [$evento, $inscripcion]) }}" class="form-inline">
                    @csrf
                    <div class="form-group mr-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" step="0.01" min="0.01" name="monto" class="form-control" placeholder="Monto del abono" required>
                        </div>
                    </div>
                    <div class="form-group mr-2">
                        <input type="date" name="fecha_pago" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group mr-2">
                        <input type="text" name="notas" class="form-control" placeholder="Notas (opcional)">
                    </div>
                    <button type="submit" class="btn btn-success">Registrar abono</button>
                </form>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info">Este evento aún no tiene personas inscritas.</div>
@endforelse
@endsection
