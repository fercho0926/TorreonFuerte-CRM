@extends('layouts.admin')

@section('title', 'Redes')

@section('page-title', 'Redes')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif

<div class="callout callout-info py-2 px-3 mb-3">
    <small><strong>Red:</strong> agrupación temática de líderes (por ejemplo, Jóvenes o Matrimonios). Si dos redes quedaron escritas de forma distinta, usa "Fusionar".</small>
</div>

<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Nueva red</h3></div>
    <form method="POST" action="{{ route('redes.store') }}" class="card-body form-inline">
        @csrf
        <input type="text" name="nombre" class="form-control mr-2" placeholder="Nombre de la red" required>
        <button type="submit" class="btn btn-primary">Crear red</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Redes ({{ $redes->count() }})</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr><th>Nombre</th><th>Líderes</th><th>Activa</th>@todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse($redes as $red)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('redes.update', $red) }}" class="form-inline">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nombre" class="form-control form-control-sm mr-2" value="{{ $red->nombre }}" required>
                                <div class="custom-control custom-checkbox mr-2">
                                    <input type="checkbox" class="custom-control-input" id="activa-{{ $red->id }}" name="activa" value="1" {{ $red->activa ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="activa-{{ $red->id }}">Activa</label>
                                </div>
                                <button type="submit" class="btn btn-sm btn-warning">Guardar</button>
                            </form>
                        </td>
                        <td><span class="badge badge-info">{{ $red->lideres_count }}</span></td>
                        <td>{{ $red->activa ? 'Sí' : 'No' }}</td>
                        @todasLasSedes<td>{{ $red->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-nowrap">
                            @if($redes->count() > 1)
                                <form method="POST" action="{{ route('redes.fusionar', $red) }}" class="form-inline d-inline">
                                    @csrf
                                    <select name="destino_id" class="form-control form-control-sm mr-1" required>
                                        <option value="">Fusionar con...</option>
                                        @foreach($redes->where('id', '!=', $red->id) as $otra)
                                            <option value="{{ $otra->id }}">{{ $otra->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Los líderes de esta red pasarán a la red elegida y esta se eliminará. ¿Continuar?')">Fusionar</button>
                                </form>
                            @endif
                            @if($red->lideres_count === 0)
                                <form method="POST" action="{{ route('redes.destroy', $red) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta red?')">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Aún no hay redes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
