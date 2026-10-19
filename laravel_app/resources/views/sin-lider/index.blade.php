@extends('layouts.admin')

@section('title', 'Sin líder asignado')

@section('page-title', 'Sin líder asignado')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif

<div class="card card-outline card-secondary mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('sin-lider.index') }}" class="form-inline">
            <input type="search" name="q" class="form-control mr-2" value="{{ $q }}" placeholder="Nombre, documento o celular">
            <button type="submit" class="btn btn-primary mr-2">Buscar</button>
            <a href="{{ route('sin-lider.index') }}" class="btn btn-outline-secondary">Limpiar</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Personas esperando un líder ({{ $personas->total() }})</h3>
    </div>
    <div class="card-body p-0">
        @forelse($personas as $persona)
            <form method="POST" action="{{ route('sin-lider.asignar', $persona) }}" class="border-bottom p-2">
                @csrf
                <div class="form-row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <a href="{{ route('personas.show', $persona) }}"><strong>{{ $persona->nombre_completo }}</strong></a>
                        <span class="badge badge-{{ $persona->estado === 'visitante' ? 'warning' : 'success' }}">{{ $persona->estado_etiqueta }}</span>
                        <div class="small text-muted">{{ $persona->celular ?? 'sin celular' }} @if($persona->zona) · {{ $persona->zona->nombre }} @endif</div>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        @include('partials.acciones-contacto', ['persona' => $persona])
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <select name="lider_id" class="form-control form-control-sm" required>
                            <option value="">Elegir líder...</option>
                            @foreach($lideres as $lider)
                                <option value="{{ $lider->id }}">{{ $lider->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-success btn-block">Asignar</button>
                    </div>
                </div>
            </form>
        @empty
            <p class="text-center text-muted my-4">No hay personas sin líder.</p>
        @endforelse
    </div>
    @if($personas->hasPages())
        <div class="card-footer">{{ $personas->links() }}</div>
    @endif
</div>
@endsection
