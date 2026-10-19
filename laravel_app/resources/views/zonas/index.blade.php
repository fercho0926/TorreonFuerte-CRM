@extends('layouts.admin')

@section('title', 'Zonas')

@section('page-title', 'Zonas')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif

<div class="callout callout-info py-2 px-3 mb-3">
    <small><strong>Zona:</strong> sector geográfico que agrupa líderes. Es opcional y sirve para filtrar y ubicar a los líderes.</small>
</div>

<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Nueva zona</h3></div>
    <form method="POST" action="{{ route('zonas.store') }}" class="card-body form-inline">
        @csrf
        <input type="text" name="nombre" class="form-control mr-2" placeholder="Nombre de la zona" required>
        <button type="submit" class="btn btn-primary">Crear zona</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Zonas ({{ $zonas->count() }})</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr><th>Zona</th><th>Líderes</th>@todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse($zonas as $zona)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('zonas.update', $zona) }}" class="form-inline">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nombre" class="form-control form-control-sm mr-2" value="{{ $zona->nombre }}" required>
                                <div class="custom-control custom-checkbox mr-2">
                                    <input type="checkbox" class="custom-control-input" id="zona-activa-{{ $zona->id }}" name="activa" value="1" {{ $zona->activa ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="zona-activa-{{ $zona->id }}">Activa</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-warning">Guardar</button>
                            </form>
                        </td>
                        <td><span class="badge badge-info">{{ $zona->lideres_count }}</span></td>
                        @todasLasSedes<td>{{ $zona->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td>
                            @if($zona->lideres_count === 0)
                                <form method="POST" action="{{ route('zonas.destroy', $zona) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta zona?')">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Aún no hay zonas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
