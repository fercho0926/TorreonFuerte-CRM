@extends('layouts.admin')

@section('title', 'Detalle de persona')

@section('page-title', 'Detalle de persona')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        @puede('editar', $persona)
            <a href="{{ route('personas.edit', $persona) }}" class="btn btn-warning">Editar</a>
        @endpuede
        @puede('inactivar', $persona)
            @if($persona->estado === 'inactivo')
                <form action="{{ route('personas.reactivar', $persona) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Reactivar</button>
                </form>
            @else
                <form action="{{ route('personas.inactivar', $persona) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary" onclick="return confirm('¿Inactivar a esta persona? Su historial se conserva.')">Inactivar</button>
                </form>
            @endif
        @endpuede
        @puede('trasladar', \App\Models\Sede::class)
            @if(!$persona->trasladada_a_sede_id)
                <a href="{{ route('traslados.create', $persona) }}" class="btn btn-outline-primary">Trasladar a otra sede</a>
            @endif
        @endpuede
        @puede('eliminar', $persona)
            <form action="{{ route('personas.destroy', $persona) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Eliminar definitivamente? Solo es posible si no tiene historial.')">Eliminar</button>
            </form>
        @endpuede
        <a href="{{ route('personas.index') }}" class="btn btn-secondary">Volver</a>
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
        <h3 class="card-title">
            {{ $persona->nombre_completo }}
            <span class="badge badge-light">{{ $persona->estado_etiqueta }}</span>
            @if($persona->es_lider)
                <span class="badge badge-warning">Líder</span>
            @endif
        </h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            @include('partials.acciones-contacto', ['persona' => $persona])
        </div>
        <dl class="row">
            <dt class="col-sm-3">Documento</dt>
            <dd class="col-sm-9">{{ $persona->numero_documento ? $persona->tipo_documento.' '.$persona->numero_documento : '—' }}</dd>

            <dt class="col-sm-3">Fecha de nacimiento</dt>
            <dd class="col-sm-9">
                @if($persona->fecha_nacimiento)
                    {{ $persona->fecha_nacimiento_legible }} ({{ $persona->edad }} años)
                @else
                    —
                @endif
            </dd>

            <dt class="col-sm-3">Celular</dt>
            <dd class="col-sm-9">{{ $persona->celular ?? '—' }}</dd>

            <dt class="col-sm-3">Correo</dt>
            <dd class="col-sm-9">{{ $persona->email ?? '—' }}</dd>

            <dt class="col-sm-3">Dirección</dt>
            <dd class="col-sm-9">{{ $persona->direccion ?? '—' }}</dd>

            <dt class="col-sm-3">Barrio / Ciudad</dt>
            <dd class="col-sm-9">{{ $persona->barrio ?? '—' }} / {{ $persona->ciudad ?? '—' }}</dd>

            @if($persona->es_lider)
                <dt class="col-sm-3">Red</dt>
                <dd class="col-sm-9"><span class="badge badge-info">{{ $persona->redLider?->nombre ?? '—' }}</span></dd>

                <dt class="col-sm-3">Zona</dt>
                <dd class="col-sm-9"><span class="badge badge-info">{{ $persona->zona?->nombre ?? '—' }}</span></dd>
            @endif

            <dt class="col-sm-3">Líder</dt>
            <dd class="col-sm-9">
                @if($persona->lider)
                    <a href="{{ route('personas.show', $persona->lider) }}">{{ $persona->lider->nombre_completo }}</a>
                @else
                    <span class="text-muted">Sin líder asignado</span>
                @endif
            </dd>
        </dl>
    </div>
</div>

@if($persona->miembros->isNotEmpty())
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Personas a su cargo ({{ $persona->miembros->count() }})</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Celular</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->miembros as $miembro)
                        <tr>
                            <td><a href="{{ route('personas.show', $miembro) }}">{{ $miembro->nombre_completo }}</a></td>
                            <td>{{ $miembro->celular ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Células</h3>
    </div>
    <div class="card-body">
        @foreach($persona->celulasLideradas as $celula)
            <p><span class="badge badge-primary">Líder</span> <a href="{{ route('celulas.show', $celula) }}">{{ $celula->nombre }}</a> — {{ $celula->miembros->count() }} miembros</p>
        @endforeach
        @foreach($persona->celulasCoLideradas as $celula)
            <p><span class="badge badge-secondary">Co-líder</span> <a href="{{ route('celulas.show', $celula) }}">{{ $celula->nombre }}</a> — {{ $celula->miembros->count() }} miembros</p>
        @endforeach
        @foreach($persona->celulas as $celula)
            <p><span class="badge badge-light border">Miembro</span> <a href="{{ route('celulas.show', $celula) }}">{{ $celula->nombre }}</a> — líder: {{ $celula->lider->nombre_completo }}</p>
        @endforeach
        @if($persona->celulasLideradas->isEmpty() && $persona->celulasCoLideradas->isEmpty() && $persona->celulas->isEmpty())
            <p class="text-muted mb-0">No pertenece a ninguna célula todavía.</p>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Encuentros ({{ $persona->encuentros->count() }})</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Asistió</th>
                    <th>Valor pagado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($persona->encuentros as $encuentro)
                    <tr>
                        <td><a href="{{ route('encuentros.show', $encuentro) }}">{{ $encuentro->nombre }}</a></td>
                        <td>{{ $encuentro->fecha_realizacion->translatedFormat('j \d\e F \d\e Y') }}</td>
                        <td>
                            @if($encuentro->pivot->asistio)
                                <span class="badge badge-success">Sí</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </td>
                        <td>{{ $encuentro->pivot->valor_pagado !== null ? '$'.number_format($encuentro->pivot->valor_pagado, 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No ha participado en encuentros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Eventos ({{ $persona->inscripcionesEventos->count() }})</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Costo</th>
                    <th>Pagado</th>
                    <th>Saldo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($persona->inscripcionesEventos as $inscripcion)
                    <tr>
                        <td><a href="{{ route('eventos.show', $inscripcion->evento) }}">{{ $inscripcion->evento->nombre }}</a></td>
                        <td>{{ $inscripcion->evento->fecha->translatedFormat('j \d\e F \d\e Y') }}</td>
                        <td>${{ number_format($inscripcion->costoAplicable(), 2) }}</td>
                        <td>${{ number_format($inscripcion->totalPagado(), 2) }}</td>
                        <td>${{ number_format($inscripcion->saldoPendiente(), 2) }}</td>
                        <td>
                            @if($inscripcion->estaPagado())
                                <span class="badge badge-success">Pagado</span>
                            @else
                                <span class="badge badge-warning">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No está inscrito en eventos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Ministerios</h3>
    </div>
    <div class="card-body">
        @foreach($persona->ministeriosLiderados as $ministerio)
            <p><span class="badge badge-primary">Encargado</span> <a href="{{ route('ministerios.show', $ministerio) }}">{{ $ministerio->nombre }}</a></p>
        @endforeach
        @foreach($persona->ministerios as $ministerio)
            <p>
                <span class="badge badge-light border">{{ $ministerio->pivot->rol ?? 'Integrante' }}</span>
                <a href="{{ route('ministerios.show', $ministerio) }}">{{ $ministerio->nombre }}</a>
                @if($ministerio->pivot->fecha_ingreso)
                    — desde {{ \Illuminate\Support\Carbon::parse($ministerio->pivot->fecha_ingreso)->translatedFormat('j \d\e F \d\e Y') }}
                @endif
            </p>
        @endforeach
        @if($persona->ministeriosLiderados->isEmpty() && $persona->ministerios->isEmpty())
            <p class="text-muted mb-0">No pertenece a ningún ministerio todavía.</p>
        @endif
    </div>
</div>

@puede('verBitacora')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de cambios</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Usuario</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($persona->cambios as $cambio)
                        <tr>
                            <td class="text-nowrap">{{ $cambio->created_at->translatedFormat('j M Y H:i') }}</td>
                            <td>{{ $cambio->etiqueta }}</td>
                            <td>{{ $cambio->usuario?->name ?? '—' }}</td>
                            <td class="small text-muted">{{ $cambio->despues ? json_encode($cambio->despues, JSON_UNESCAPED_UNICODE) : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Sin cambios registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endpuede
@endsection
