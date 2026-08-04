@extends('layouts.admin')

@section('title', 'Detalle del líder')

@section('page-title', 'Detalle del líder')

@section('content')
<div class="row mb-3">
    <div class="col-12 text-right">
        <a href="{{ route('lideres.edit', $lider) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('lideres.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $lider->nombre_completo }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Red</dt>
            <dd class="col-sm-9"><span class="badge badge-primary">{{ $lider->red }}</span></dd>

            <dt class="col-sm-3">Documento</dt>
            <dd class="col-sm-9">{{ $lider->tipo_documento }} {{ $lider->numero_documento }}</dd>

            <dt class="col-sm-3">Fecha de nacimiento</dt>
            <dd class="col-sm-9">{{ optional($lider->fecha_nacimiento)->format('Y-m-d') ?? '—' }}</dd>

            <dt class="col-sm-3">Celular</dt>
            <dd class="col-sm-9">{{ $lider->celular ?? '—' }}</dd>

            <dt class="col-sm-3">Email</dt>
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
        <h3 class="card-title">Personas que lidera ({{ $miembros->count() }})</h3>
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
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($miembros as $miembro)
                    <tr>
                        <td>{{ $miembro->id }}</td>
                        <td>{{ $miembro->nombre_completo }}</td>
                        <td>{{ $miembro->tipo_documento }} {{ $miembro->numero_documento }}</td>
                        <td>{{ $miembro->celular }}</td>
                        <td>{{ $miembro->ciudad }}</td>
                        <td>
                            <a href="{{ route('personas.edit', $miembro) }}" class="btn btn-sm btn-warning">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Este líder aún no tiene personas asignadas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
