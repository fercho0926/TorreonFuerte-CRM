@extends('layouts.admin')

@section('title', 'Detalle de persona')

@section('page-title', 'Detalle de persona')

@section('page-actions')
    @puede('editar', $persona)
        <a href="{{ route('personas.edit', $persona) }}" class="btn btn-primary">Editar</a>
    @endpuede
    @puede('inactivar', $persona)
        @if($persona->estado === 'inactivo')
            <x-form.accion :action="route('personas.reactivar', $persona)" boton="Reactivar" variante="success" icono="fa-undo" />
        @else
            <x-form.confirm :action="route('personas.inactivar', $persona)" method="POST" titulo="¿Inactivar a esta persona?" mensaje="Su historial se conserva." boton="Inactivar" :peligro="false" variante="outline-secondary" tamano="">Inactivar</x-form.confirm>
        @endif
    @endpuede
    @puede('trasladar', \App\Models\Sede::class)
        @if(!$persona->trasladada_a_sede_id)
            <a href="{{ route('traslados.create', $persona) }}" class="btn btn-outline-primary">Trasladar a otra sede</a>
        @endif
    @endpuede
    @puede('eliminar', $persona)
        <x-form.confirm :action="route('personas.destroy', $persona)" method="DELETE" titulo="¿Eliminar definitivamente?" mensaje="Solo es posible si no tiene historial." boton="Eliminar" :peligro="true" variante="outline-danger" tamano="">Eliminar</x-form.confirm>
    @endpuede
    <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')


<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">
            {{ $persona->nombre_completo }}
            <x-ui.estado :valor="$persona->estado" :etiqueta="$persona->estado_etiqueta" />
            @if($persona->es_lider)
                <x-ui.estado valor="lider" />
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
                <dd class="col-sm-9"><x-ui.insignia color="info">{{ $persona->redLider?->nombre ?? '—' }}</x-ui.insignia></dd>
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
    <div class="card mb-4">
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

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Células</h3>
    </div>
    <div class="card-body">
        @foreach($persona->celulasLideradas as $celula)
            <p><x-ui.estado valor="lider" /> <a href="{{ route('celulas.show', $celula) }}">{{ $celula->nombre }}</a> — {{ $celula->miembros->count() }} miembros</p>
        @endforeach
        @foreach($persona->celulasCoLideradas as $celula)
            <p><x-ui.insignia color="secondary">Co-líder</x-ui.insignia> <a href="{{ route('celulas.show', $celula) }}">{{ $celula->nombre }}</a> — {{ $celula->miembros->count() }} miembros</p>
        @endforeach
        @foreach($persona->celulas as $celula)
            <p><x-ui.insignia color="light" class="border">Miembro</x-ui.insignia> <a href="{{ route('celulas.show', $celula) }}">{{ $celula->nombre }}</a> — líder: {{ $celula->lider->nombre_completo }}</p>
        @endforeach
        @if($persona->celulasLideradas->isEmpty() && $persona->celulasCoLideradas->isEmpty() && $persona->celulas->isEmpty())
            <p class="text-muted mb-0">No pertenece a ninguna célula todavía.</p>
        @endif
    </div>
</div>

<div class="card mb-4">
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
                                <x-ui.estado valor="si" />
                            @else
                                <x-ui.estado valor="no" />
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

<div class="card mb-4">
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
                                <x-ui.estado valor="pagado" />
                            @else
                                <x-ui.estado valor="pendiente" />
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

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Ministerios</h3>
    </div>
    <div class="card-body">
        @foreach($persona->ministeriosLiderados as $ministerio)
            <p><x-ui.insignia color="primary">Encargado</x-ui.insignia> <a href="{{ route('ministerios.show', $ministerio) }}">{{ $ministerio->nombre }}</a></p>
        @endforeach
        @foreach($persona->ministerios as $ministerio)
            <p>
                <x-ui.insignia color="light" class="border">{{ $ministerio->pivot->rol ?? 'Integrante' }}</x-ui.insignia>
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
    <div class="card mb-4">
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
