@extends('layouts.admin')

@section('title', 'Designar líder')

@section('page-title', 'Designar líder')

@section('content')
@include('partials.ayuda-terminos')

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Buscar a la persona que será líder</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('lideres.designar') }}" class="form-inline mb-3">
            <input type="search" name="q" class="form-control mr-2" value="{{ $q }}" placeholder="Nombre, documento o celular" autofocus>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>

        @if($q !== null && $q !== '')
            @forelse($candidatas as $persona)
                <form method="POST" action="{{ route('lideres.designar.store') }}" class="border rounded p-2 mb-2">
                    @csrf
                    <input type="hidden" name="persona_id" value="{{ $persona->id }}">
                    <div class="form-row align-items-end">
                        <div class="col-md-4 mb-2">
                            <strong>{{ $persona->nombre_completo }}</strong>
                            <div class="small text-muted">{{ $persona->numero_documento ? $persona->tipo_documento.' '.$persona->numero_documento : 'Sin documento' }} · {{ $persona->celular ?? 'sin celular' }}</div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small mb-0">Red</label>
                            <select name="red_id" class="form-control form-control-sm" required>
                                <option value="">Seleccione...</option>
                                @foreach($redes as $red)
                                    <option value="{{ $red->id }}">{{ $red->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small mb-0">Zona</label>
                            <select name="zona_id" class="form-control form-control-sm">
                                <option value="">Sin zona</option>
                                @foreach($zonas as $zona)
                                    <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-sm btn-success btn-block">Designar</button>
                        </div>
                    </div>
                    @if(!$persona->numero_documento)
                        <div class="small text-danger">Esta persona no tiene documento; edítala para completarlo antes de designarla.</div>
                    @endif
                </form>
            @empty
                <p class="text-muted">No se encontraron personas (o ya son líderes).</p>
            @endforelse
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
    </div>
    <div class="card-footer">
        <a href="{{ route('personas.create', ['es_lider' => 1]) }}" class="btn btn-outline-primary">Nueva persona como líder</a>
        <a href="{{ route('lideres.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>
@endsection
