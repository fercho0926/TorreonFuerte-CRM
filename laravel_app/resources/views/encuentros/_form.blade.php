@php
    $encuentro = $encuentro ?? null;
    $seleccionadas = $personasSeleccionadas ?? [];
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombre del encuentro</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $encuentro?->nombre) }}" required>
            @error('nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Fecha de realización</label>
            <input type="date" name="fecha_realizacion" class="form-control @error('fecha_realizacion') is-invalid @enderror" value="{{ old('fecha_realizacion', optional($encuentro?->fecha_realizacion)->format('Y-m-d')) }}" required>
            @error('fecha_realizacion')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Estado</label>
            <select name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                @foreach(\App\Models\Encuentro::ESTADOS as $estado)
                    <option value="{{ $estado }}" {{ old('estado', $encuentro?->estado ?? 'planificado') == $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                @endforeach
            </select>
            @error('estado')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Lugar</label>
            <input type="text" name="lugar" class="form-control @error('lugar') is-invalid @enderror" value="{{ old('lugar', $encuentro?->lugar) }}">
            @error('lugar')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Valor</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                </div>
                <input type="number" step="0.01" min="0" name="valor" class="form-control @error('valor') is-invalid @enderror" value="{{ old('valor', $encuentro?->valor) }}" required>
            </div>
            @error('valor')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>Notas</label>
    <textarea name="notas" class="form-control @error('notas') is-invalid @enderror" rows="3">{{ old('notas', $encuentro?->notas) }}</textarea>
    @error('notas')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <label>Personas participantes</label>
    <div class="border rounded p-2" style="max-height: 280px; overflow-y: auto;">
        @forelse($personas as $persona)
            <div class="form-check">
                <input type="checkbox" name="personas[]" value="{{ $persona->id }}" class="form-check-input" id="persona-{{ $persona->id }}"
                    {{ in_array($persona->id, old('personas', $seleccionadas)) ? 'checked' : '' }}>
                <label class="form-check-label" for="persona-{{ $persona->id }}">
                    {{ $persona->nombre_completo }}
                    @if($persona->es_lider)
                        <span class="badge badge-primary">Líder</span>
                    @endif
                </label>
            </div>
        @empty
            <p class="text-muted mb-0">No hay personas registradas todavía.</p>
        @endforelse
    </div>
    @error('personas')<span class="text-danger">{{ $message }}</span>@enderror
</div>
