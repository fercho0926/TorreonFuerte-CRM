@php
    $evento = $evento ?? null;
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombre del evento</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $evento?->nombre) }}" placeholder="Ej. Cena de varones 2026" required>
            @error('nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Tipo</label>
            <select name="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                <option value="">Seleccione...</option>
                @foreach(\App\Models\Evento::TIPOS as $tipo)
                    <option value="{{ $tipo }}" {{ old('tipo', $evento?->tipo) == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                @endforeach
            </select>
            @error('tipo')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Fecha</label>
            <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', optional($evento?->fecha)->format('Y-m-d')) }}" required>
            @error('fecha')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Lugar</label>
            <input type="text" name="lugar" class="form-control @error('lugar') is-invalid @enderror" value="{{ old('lugar', $evento?->lugar) }}">
            @error('lugar')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Costo por persona</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                </div>
                <input type="number" step="0.01" min="0" name="costo" class="form-control @error('costo') is-invalid @enderror" value="{{ old('costo', $evento?->costo ?? 0) }}" required>
            </div>
            @error('costo')<span class="invalid-feedback">{{ $message }}</span>@enderror
            <small class="form-text text-muted">Se puede ajustar por persona o pagar en varios abonos desde el detalle del evento.</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Estado</label>
            <select name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                @foreach(\App\Models\Evento::ESTADOS as $estado)
                    <option value="{{ $estado }}" {{ old('estado', $evento?->estado ?? 'planificado') == $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                @endforeach
            </select>
            @error('estado')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>Descripción</label>
    <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion', $evento?->descripcion) }}</textarea>
    @error('descripcion')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

@if(isset($personas))
    <div class="form-group">
        <label>Personas a inscribir</label>
        <div class="border rounded p-2" style="max-height: 280px; overflow-y: auto;">
            @forelse($personas as $persona)
                <div class="form-check">
                    <input type="checkbox" name="personas[]" value="{{ $persona->id }}" class="form-check-input" id="persona-{{ $persona->id }}"
                        {{ in_array($persona->id, old('personas', [])) ? 'checked' : '' }}>
                    <label class="form-check-label" for="persona-{{ $persona->id }}">{{ $persona->nombre_completo }}</label>
                </div>
            @empty
                <p class="text-muted mb-0">No hay personas registradas todavía.</p>
            @endforelse
        </div>
        <small class="form-text text-muted">Podrás inscribir más personas después, desde el detalle del evento.</small>
    </div>
@endif
