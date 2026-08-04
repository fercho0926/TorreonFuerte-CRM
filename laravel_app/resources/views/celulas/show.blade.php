@extends('layouts.admin')

@section('title', 'Detalle de la célula')

@section('page-title', 'Detalle de la célula')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        @if($puedeGestionarEstructura)
            <a href="{{ route('celulas.edit', $celula) }}" class="btn btn-warning">Editar</a>
        @endif
        <a href="{{ route('celulas.index') }}" class="btn btn-secondary">Volver</a>
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
        <h3 class="card-title">{{ $celula->nombre }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Líder</dt>
            <dd class="col-sm-9">{{ $celula->lider->nombre_completo }}</dd>

            <dt class="col-sm-3">Co-líder</dt>
            <dd class="col-sm-9">{{ $celula->coLider?->nombre_completo ?? '—' }}</dd>

            <dt class="col-sm-3">Sede</dt>
            <dd class="col-sm-9">{{ $celula->sede ?? '—' }}</dd>

            <dt class="col-sm-3">Reunión</dt>
            <dd class="col-sm-9">
                {{ $celula->dia_reunion ?? 'Sin definir' }}
                @if($celula->hora_reunion) a las {{ substr($celula->hora_reunion, 0, 5) }} @endif
                @if($celula->lugar_reunion) — {{ $celula->lugar_reunion }} @endif
            </dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                <span class="badge badge-{{ $celula->estado === 'activa' ? 'success' : 'secondary' }}">{{ ucfirst($celula->estado) }}</span>
            </dd>
        </dl>
    </div>
</div>

@if($puedeAdministrar)
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title">Agregar miembro</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('celulas.miembros.store', $celula) }}" class="form-inline">
                @csrf
                <div class="form-group mr-2">
                    <select name="persona_id" class="form-control" required>
                        <option value="">Seleccione una persona...</option>
                        @foreach($personasSinAsignar as $persona)
                            <option value="{{ $persona->id }}">{{ $persona->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Agregar</button>
            </form>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Miembros ({{ $celula->miembros->count() }})</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Nombre completo</th>
                    <th>Documento</th>
                    <th>Celular</th>
                    @if($puedeAdministrar)
                        <th>Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($celula->miembros as $miembro)
                    <tr>
                        <td>{{ $miembro->nombre_completo }}</td>
                        <td>{{ $miembro->tipo_documento }} {{ $miembro->numero_documento }}</td>
                        <td>{{ $miembro->celular ?? '—' }}</td>
                        @if($puedeAdministrar)
                            <td>
                                <form action="{{ route('celulas.miembros.destroy', [$celula, $miembro]) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Quitar a esta persona de la célula?')">Quitar</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $puedeAdministrar ? 4 : 3 }}" class="text-center">Esta célula aún no tiene miembros asignados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($puedeAdministrar)
    <div class="card card-outline card-secondary mb-3">
        <div class="card-header">
            <h3 class="card-title">Registrar reunión</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('celulas.reuniones.store', $celula) }}">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Tema</label>
                            <input type="text" name="tema" class="form-control" placeholder="Ej. Estudio de Efesios 1">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notas</label>
                    <textarea name="notas" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Asistencia</label>
                    <div class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                        @forelse($celula->miembros as $miembro)
                            <div class="form-check">
                                <input type="checkbox" name="asistio[]" value="{{ $miembro->id }}" class="form-check-input" id="asiste-{{ $miembro->id }}" checked>
                                <label class="form-check-label" for="asiste-{{ $miembro->id }}">{{ $miembro->nombre_completo }}</label>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Agrega miembros a la célula antes de registrar asistencia.</p>
                        @endforelse
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" {{ $celula->miembros->isEmpty() ? 'disabled' : '' }}>Guardar reunión</button>
            </form>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Historial de reuniones ({{ $celula->reuniones->count() }})</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tema</th>
                    <th>Asistencia</th>
                    @if($puedeAdministrar)
                        <th></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($celula->reuniones as $reunion)
                    <tr>
                        <td>{{ $reunion->fecha->format('Y-m-d') }}</td>
                        <td>{{ $reunion->tema ?? '—' }}</td>
                        <td><span class="badge badge-info">{{ $reunion->totalAsistio() }} / {{ $reunion->asistentes->count() }}</span></td>
                        @if($puedeAdministrar)
                            <td>
                                <form action="{{ route('celulas.reuniones.destroy', [$celula, $reunion]) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('¿Eliminar esta reunión?')">Eliminar</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $puedeAdministrar ? 4 : 3 }}" class="text-center">Aún no hay reuniones registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
