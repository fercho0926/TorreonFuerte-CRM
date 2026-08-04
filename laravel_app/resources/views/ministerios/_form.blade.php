@php
    $ministerio = $ministerio ?? null;
    $seleccionadas = $personasSeleccionadas ?? [];
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombre del ministerio</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $ministerio?->nombre) }}" placeholder="Ej. Alabanza, Niños, Jóvenes..." required>
            @error('nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Encargado</label>
            <select name="encargado_id" class="form-control @error('encargado_id') is-invalid @enderror">
                <option value="">Sin encargado asignado</option>
                @foreach($personas as $persona)
                    <option value="{{ $persona->id }}" {{ (string) old('encargado_id', $ministerio?->encargado_id) === (string) $persona->id ? 'selected' : '' }}>{{ $persona->nombre_completo }}</option>
                @endforeach
            </select>
            @error('encargado_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Estado</label>
            <select name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                @foreach(\App\Models\Ministerio::ESTADOS as $estado)
                    <option value="{{ $estado }}" {{ old('estado', $ministerio?->estado ?? 'activo') == $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                @endforeach
            </select>
            @error('estado')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>Descripción</label>
    <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion', $ministerio?->descripcion) }}</textarea>
    @error('descripcion')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="form-group">
    <label>Integrantes</label>
    <div class="border rounded p-2" style="max-height: 280px; overflow-y: auto;">
        @forelse($personas as $persona)
            <div class="form-check">
                <input type="checkbox" name="personas[]" value="{{ $persona->id }}" class="form-check-input" id="integrante-{{ $persona->id }}"
                    {{ in_array($persona->id, old('personas', $seleccionadas)) ? 'checked' : '' }}>
                <label class="form-check-label" for="integrante-{{ $persona->id }}">
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
    <small class="form-text text-muted">El rol y la fecha de ingreso de cada integrante se editan desde el detalle del ministerio.</small>
</div>
