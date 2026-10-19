@extends('layouts.admin')

@section('title', 'Detalle del líder')

@section('page-title', 'Detalle del líder')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        @puede('editar', $lider)
            <a href="{{ route('personas.edit', $lider) }}" class="btn btn-warning">Editar</a>
        @endpuede
        @puede('designarLider')
            <a href="{{ route('lideres.quitar', $lider) }}" class="btn btn-outline-danger">Quitar rol</a>
        @endpuede
        <a href="{{ route('lideres.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-sm-4">
        <div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
            <div class="info-box-content"><span class="info-box-text">Personas</span><span class="info-box-number">{{ $miembros->count() }}</span></div></div>
    </div>
    <div class="col-sm-4">
        <div class="info-box"><span class="info-box-icon bg-secondary"><i class="fas fa-people-arrows"></i></span>
            <div class="info-box-content"><span class="info-box-text">Células</span><span class="info-box-number">{{ $celulas->count() }}</span></div></div>
    </div>
    <div class="col-sm-4">
        <div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content"><span class="info-box-text">Última reunión</span>
                <span class="info-box-number">{{ $ultimaReunion ? \Illuminate\Support\Carbon::parse($ultimaReunion)->translatedFormat('j \d\e F \d\e Y') : '—' }}</span></div></div>
    </div>
</div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $lider->nombre_completo }}</h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            @include('partials.acciones-contacto', ['persona' => $lider])
        </div>
        <dl class="row">
            <dt class="col-sm-3">Red</dt>
            <dd class="col-sm-9"><span class="badge badge-primary">{{ $lider->redLider?->nombre ?? '—' }}</span></dd>

            <dt class="col-sm-3">Zona</dt>
            <dd class="col-sm-9"><span class="badge badge-info">{{ $lider->zona?->nombre ?? '—' }}</span></dd>

            <dt class="col-sm-3">Documento</dt>
            <dd class="col-sm-9">{{ $lider->numero_documento ? $lider->tipo_documento.' '.$lider->numero_documento : '—' }}</dd>

            <dt class="col-sm-3">Fecha de nacimiento</dt>
            <dd class="col-sm-9">{{ $lider->fecha_nacimiento ? $lider->fecha_nacimiento_legible.' ('.$lider->edad.' años)' : '—' }}</dd>

            <dt class="col-sm-3">Celular</dt>
            <dd class="col-sm-9">{{ $lider->celular ?? '—' }}</dd>

            <dt class="col-sm-3">Correo</dt>
            <dd class="col-sm-9">{{ $lider->email ?? '—' }}</dd>

            <dt class="col-sm-3">Dirección</dt>
            <dd class="col-sm-9">{{ $lider->direccion ?? '—' }}</dd>

            <dt class="col-sm-3">Barrio / Ciudad</dt>
            <dd class="col-sm-9">{{ $lider->barrio ?? '—' }} / {{ $lider->ciudad ?? '—' }}</dd>
        </dl>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Células ({{ $celulas->count() }})</h3>
    </div>
    <div class="card-body">
        @forelse($celulas as $celula)
            <p>
                <a href="{{ route('celulas.show', $celula) }}">{{ $celula->nombre }}</a>
                — {{ $celula->miembros_count }} miembros
                @if($celula->co_lider_id === $lider->id)<span class="badge badge-secondary">Co-líder</span>@endif
            </p>
        @empty
            <p class="text-muted mb-0">Este líder aún no dirige ninguna célula.</p>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Personas a su cargo ({{ $miembros->count() }})</h3>
    </div>
    <div class="card-body p-0">
        <div class="d-md-none p-2">
            @forelse($miembros as $miembro)
                @include('partials.persona-tarjeta', ['persona' => $miembro])
            @empty
                <p class="text-center text-muted my-3">Este líder aún no tiene personas asignadas.</p>
            @endforelse
        </div>
        <div class="d-none d-md-block table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Celular</th>
                        <th>Ciudad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($miembros as $miembro)
                        <tr>
                            <td><a href="{{ route('personas.show', $miembro) }}">{{ $miembro->nombre_completo }}</a></td>
                            <td>{{ $miembro->estado_etiqueta }}</td>
                            <td>{{ $miembro->celular ?? '—' }}</td>
                            <td>{{ $miembro->ciudad ?? '—' }}</td>
                            <td>
                                @puede('editar', $miembro)
                                    <a href="{{ route('personas.edit', $miembro) }}" class="btn btn-sm btn-warning">Editar</a>
                                @endpuede
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Este líder aún no tiene personas asignadas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
