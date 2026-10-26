@extends('layouts.admin')

@section('title', 'Detalle del líder')

@section('page-title', 'Detalle del líder')

@section('page-actions')
    @puede('editar', $lider)
        <a href="{{ route('personas.edit', $lider) }}" class="btn btn-primary">Editar</a>
    @endpuede
    @puede('designarLider')
        <a href="{{ route('lideres.quitar', $lider) }}" class="btn btn-outline-danger">Quitar rol</a>
    @endpuede
    <a href="{{ route('lideres.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')
@include('historial._pestanas', ['url' => route('lideres.show', $lider), 'pestana' => $pestana ?? 'datos'])

@if(($pestana ?? 'datos') === 'historico')
    @include('historial._secciones', ['historico' => $historico])
@else
<div class="row">
    <div class="col-sm-4">
        <div class="info-box"><span class="info-box-icon text-bg-info"><i class="fas fa-users"></i></span>
            <div class="info-box-content"><span class="info-box-text">Miembros</span><span class="info-box-number">{{ $miembros->count() }}</span></div></div>
    </div>
    <div class="col-sm-4">
        <div class="info-box"><span class="info-box-icon text-bg-secondary"><i class="fas fa-people-arrows"></i></span>
            <div class="info-box-content"><span class="info-box-text">Células</span><span class="info-box-number">{{ $celulas->count() }}</span></div></div>
    </div>
    <div class="col-sm-4">
        <div class="info-box"><span class="info-box-icon text-bg-success"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content"><span class="info-box-text">Última reunión</span>
                <span class="info-box-number">{{ $ultimaReunion ? \Illuminate\Support\Carbon::parse($ultimaReunion)->translatedFormat('j \d\e F \d\e Y') : '—' }}</span></div></div>
    </div>
</div>

<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">{{ $lider->nombre_completo }}</h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            @include('partials.acciones-contacto', ['persona' => $lider])
        </div>
        <dl class="row">
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

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Células ({{ $celulas->count() }})</h3>
    </div>
    <div class="card-body">
        @forelse($celulas as $celula)
            <p>
                <a href="{{ route('celulas.show', $celula) }}">{{ $celula->nombre }}</a>
                — {{ $celula->miembros_count }} miembros
                @if($celula->co_lider_id === $lider->id)<x-ui.insignia color="secondary">Co-líder</x-ui.insignia>@endif
            </p>
        @empty
            <p class="text-muted mb-0">Este líder aún no dirige ninguna célula.</p>
        @endforelse
    </div>
</div>

<div class="card card-primary card-outline mb-4" id="personas-a-cargo">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users me-1" aria-hidden="true"></i>Miembros a su cargo <small class="text-muted ms-2">{{ $miembros->count() }}</small></h3>
    </div>
    @if($miembros->isEmpty())
        <div class="card-body"><x-ui.vacio mensaje="Aún no lidera a nadie." icono="fa-user-friends" /></div>
    @else
        {{-- Grilla compacta con altura máxima: no alarga la página aunque tenga muchos miembros. --}}
        <div class="card-body table-responsive p-0" style="max-height: 20rem; overflow-y: auto;">
            <table class="tabla-apilable table table-sm table-hover align-middle mb-0">
                <thead class="sticky-top bg-body">
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Celular</th>
                        <th>Célula</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($miembros as $miembro)
                        <tr>
                            <td data-etiqueta="Nombre" data-primaria><a href="{{ route('personas.show', $miembro) }}">{{ $miembro->nombre_completo }}</a></td>
                            <td data-etiqueta="Estado"><x-ui.estado :valor="$miembro->estado" :etiqueta="$miembro->estado_etiqueta" /></td>
                            <td data-etiqueta="Celular">{{ $miembro->celular ?? '—' }}</td>
                            <td data-etiqueta="Célula">{{ $miembro->celulas->pluck('nombre')->implode(', ') ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif
@endsection
