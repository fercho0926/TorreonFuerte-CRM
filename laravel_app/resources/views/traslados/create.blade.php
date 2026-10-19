@extends('layouts.admin')

@section('title', 'Trasladar persona')

@section('page-title', 'Trasladar persona a otra sede')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif

<div class="callout callout-info">
    <strong>{{ $persona->nombre_completo }}</strong> — sede actual: {{ $persona->sede?->nombre }}<br>
    <small>
        Se crea un registro nuevo en la sede destino, sin líder, célula ni ministerios (aparecerá en "Sin líder asignado").
        El registro de aquí queda inactivo y conserva su historial (asistencias, pagos, células).
    </small>
</div>

@if(array_sum($pendientes) > 0)
    <div class="alert alert-warning">
        <strong>Antes de trasladar hay que reasignar lo que esta persona dirige:</strong>
        <ul class="mb-0">
            @if($pendientes['personas'])<li>{{ $pendientes['personas'] }} persona(s) a su cargo — reasígnalas con "Quitar rol" del líder.</li>@endif
            @if($pendientes['celulas'])<li>{{ $pendientes['celulas'] }} célula(s) — cambia su líder o co-líder.</li>@endif
            @if($pendientes['ministerios'])<li>{{ $pendientes['ministerios'] }} ministerio(s) — cambia su encargado.</li>@endif
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('traslados.store', $persona) }}">
    @csrf
    <div class="card card-primary">
        <div class="card-body">
            <div class="form-group">
                <label>Sede destino</label>
                <select name="sede_destino_id" class="form-control @error('sede_destino_id') is-invalid @enderror" required>
                    <option value="">Seleccione...</option>
                    @foreach($sedes as $sede)
                        <option value="{{ $sede->id }}" {{ (string) old('sede_destino_id') === (string) $sede->id ? 'selected' : '' }}>{{ $sede->nombre }} — {{ $sede->ciudad }}</option>
                    @endforeach
                </select>
                @error('sede_destino_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary" {{ array_sum($pendientes) > 0 ? 'disabled' : '' }}>Trasladar</button>
            <a href="{{ route('personas.show', $persona) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </div>
</form>
@endsection
