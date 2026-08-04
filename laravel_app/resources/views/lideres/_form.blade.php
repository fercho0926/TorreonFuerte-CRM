@php
    $lider = $lider ?? null;
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombres</label>
            <input type="text" name="nombres" class="form-control @error('nombres') is-invalid @enderror" value="{{ old('nombres', $lider?->nombres) }}" required>
            @error('nombres')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Apellidos</label>
            <input type="text" name="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos', $lider?->apellidos) }}" required>
            @error('apellidos')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Tipo de documento</label>
            <select name="tipo_documento" class="form-control @error('tipo_documento') is-invalid @enderror" required>
                <option value="">Seleccione...</option>
                @foreach(\App\Models\Persona::TIPOS_DOCUMENTO as $tipo)
                    <option value="{{ $tipo }}" {{ old('tipo_documento', $lider?->tipo_documento) == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                @endforeach
            </select>
            @error('tipo_documento')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Número de documento</label>
            <input type="text" name="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror" value="{{ old('numero_documento', $lider?->numero_documento) }}" required>
            @error('numero_documento')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento', optional($lider?->fecha_nacimiento)->format('Y-m-d')) }}">
            @error('fecha_nacimiento')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Celular</label>
            <input type="text" name="celular" class="form-control @error('celular') is-invalid @enderror" value="{{ old('celular', $lider?->celular) }}">
            @error('celular')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $lider?->email) }}">
            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>Dirección</label>
    <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $lider?->direccion) }}">
    @error('direccion')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Barrio</label>
            <input type="text" name="barrio" class="form-control @error('barrio') is-invalid @enderror" value="{{ old('barrio', $lider?->barrio) }}">
            @error('barrio')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Ciudad</label>
            <input type="text" name="ciudad" class="form-control @error('ciudad') is-invalid @enderror" value="{{ old('ciudad', $lider?->ciudad) }}">
            @error('ciudad')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Red</label>
            <input type="text" name="red" class="form-control @error('red') is-invalid @enderror" value="{{ old('red', $lider?->red) }}" required>
            @error('red')<span class="invalid-feedback">{{ $message }}</span>@enderror
            <small class="form-text text-muted">Red de la que este líder es responsable.</small>
        </div>
    </div>
</div>
