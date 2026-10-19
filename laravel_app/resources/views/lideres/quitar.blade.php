@extends('layouts.admin')

@section('title', 'Quitar rol de líder')

@section('page-title', 'Quitar rol de líder')

@section('content')
@if(session('error'))
    <div class="alert alert-warning">{{ session('error') }}</div>
@endif
@if(!empty($mensajeError))
    <div class="alert alert-danger">{{ $mensajeError }}</div>
@endif

<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">{{ $lider->nombre_completo }}</h3>
    </div>
    <form method="POST" action="{{ route('lideres.destroy', $lider) }}">
        @csrf
        @method('DELETE')
        <div class="card-body">
            <p>
                Al quitar el rol, <strong>no se elimina a la persona</strong>. Esto es lo que quedaría sin líder:
            </p>
            <ul>
                <li><strong>{{ $impacto['personas'] }}</strong> persona(s) a su cargo</li>
                <li><strong>{{ $impacto['celulas'] }}</strong> célula(s)</li>
            </ul>

            @if($personas->isNotEmpty())
                <details class="mb-2"><summary>Ver personas</summary>
                    <ul>@foreach($personas as $p)<li>{{ $p->nombre_completo }}</li>@endforeach</ul>
                </details>
            @endif
            @if($celulas->isNotEmpty())
                <details class="mb-3"><summary>Ver células</summary>
                    <ul>@foreach($celulas as $c)<li>{{ $c->nombre }}</li>@endforeach</ul>
                </details>
            @endif

            @if($impacto['personas'] > 0 || $impacto['celulas'] > 0)
                <div class="form-group">
                    <label>Reasignar a otro líder</label>
                    <select name="reasignar_a" class="form-control @error('reasignar_a') is-invalid @enderror">
                        <option value="">No reasignar</option>
                        @foreach($otrosLideres as $otro)
                            <option value="{{ $otro->id }}">{{ $otro->nombre_completo }}</option>
                        @endforeach
                    </select>
                    @error('reasignar_a')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="form-text text-muted">Las células solo pueden quedar a cargo de otro líder.</small>
                </div>

                @if($impacto['celulas'] === 0)
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="confirmar_sin_lider" name="confirmar_sin_lider" value="1">
                        <label class="custom-control-label" for="confirmar_sin_lider">Confirmo que sus personas queden sin líder (aparecerán en "Sin líder asignado")</label>
                    </div>
                @endif
            @endif
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-danger">Quitar rol de líder</button>
            <a href="{{ route('lideres.show', $lider) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
