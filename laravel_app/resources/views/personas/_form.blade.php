@php
    $persona = $persona ?? null;
    $esLiderActual = (bool) old('es_lider', $persona?->es_lider ?? ($esLiderInicial ?? false));
    $duplicado = session('duplicado');
@endphp

@if($duplicado)
    <div class="alert alert-warning">
        <strong>Posible duplicado:</strong> ya existe una persona con el mismo nombre y celular:
        @if($duplicado['puedeVer'])
            <a href="{{ route('personas.show', $duplicado['id']) }}" target="_blank">{{ $duplicado['nombre'] }}</a>.
        @else
            {{ $duplicado['nombre'] }}.
        @endif
        <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="confirmar_duplicado" name="confirmar_duplicado" value="1">
            <label class="custom-control-label" for="confirmar_duplicado">Es otra persona, guardar de todos modos</label>
        </div>
    </div>
@endif

<h5 class="mb-3">Datos personales</h5>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombres</label>
            <input type="text" name="nombres" class="form-control @error('nombres') is-invalid @enderror" value="{{ old('nombres', $persona?->nombres) }}" required>
            @error('nombres')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Apellidos</label>
            <input type="text" name="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos', $persona?->apellidos) }}" required>
            @error('apellidos')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Celular</label>
            <input type="tel" name="celular" class="form-control @error('celular') is-invalid @enderror" value="{{ old('celular', $persona?->celular) }}">
            @error('celular')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-group">
            <label>Correo</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $persona?->email) }}">
            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            <small class="form-text text-muted">Basta con celular o correo para registrar a una persona.</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento', optional($persona?->fecha_nacimiento)->format('Y-m-d')) }}">
            @error('fecha_nacimiento')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Estado</label>
            <select name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                @foreach(\App\Models\Persona::ESTADOS as $valor => $etiqueta)
                    <option value="{{ $valor }}" {{ old('estado', $persona?->estado ?? 'visitante') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                @endforeach
            </select>
            @error('estado')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Tipo de documento</label>
            <select name="tipo_documento" class="form-control @error('tipo_documento') is-invalid @enderror">
                <option value="">Sin documento</option>
                @foreach(\App\Models\Persona::TIPOS_DOCUMENTO as $tipo)
                    <option value="{{ $tipo }}" {{ old('tipo_documento', $persona?->tipo_documento) == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                @endforeach
            </select>
            @error('tipo_documento')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Número de documento</label>
            <input type="text" name="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror" value="{{ old('numero_documento', $persona?->numero_documento) }}">
            @error('numero_documento')<span class="invalid-feedback">{{ $message }}</span>@enderror
            <small class="form-text text-muted">Opcional para visitantes; obligatorio para miembros activos y líderes.</small>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Dirección</label>
    <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $persona?->direccion) }}">
    @error('direccion')<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Barrio</label>
            <input type="text" name="barrio" class="form-control @error('barrio') is-invalid @enderror" value="{{ old('barrio', $persona?->barrio) }}">
            @error('barrio')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Ciudad</label>
            <input type="text" name="ciudad" class="form-control @error('ciudad') is-invalid @enderror" value="{{ old('ciudad', $persona?->ciudad) }}">
            @error('ciudad')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<hr>
<h5 class="mb-3">Liderazgo</h5>
@include('partials.ayuda-terminos')

<div class="form-group">
    <label>Líder directo</label>
    <select name="lider_id" class="form-control @error('lider_id') is-invalid @enderror">
        <option value="">Sin líder asignado</option>
        @foreach($lideres as $lider)
            <option value="{{ $lider->id }}" {{ (string) old('lider_id', $persona?->lider_id) === (string) $lider->id ? 'selected' : '' }}>
                {{ $lider->nombre_completo }}@if($lider->redLider) — {{ $lider->redLider->nombre }}@endif
            </option>
        @endforeach
    </select>
    @error('lider_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
    <small class="form-text text-muted">Es el líder que cuida de esta persona. Si cambia, la persona sale de las células de su líder anterior.</small>
</div>

@if($puedeDesignarLider)
    <div class="custom-control custom-checkbox mb-3">
        <input type="checkbox" class="custom-control-input" id="es_lider" name="es_lider" value="1" {{ $esLiderActual ? 'checked' : '' }}>
        <label class="custom-control-label" for="es_lider">Esta persona es líder</label>
    </div>

    <div id="datos-lider" class="row" @if(!$esLiderActual) style="display:none" @endif>
        <div class="col-md-6">
            <div class="form-group">
                <label>Red</label>
                <select name="red_id" class="form-control @error('red_id') is-invalid @enderror">
                    <option value="">Seleccione...</option>
                    @foreach($redes as $red)
                        <option value="{{ $red->id }}" {{ (string) old('red_id', $persona?->red_id) === (string) $red->id ? 'selected' : '' }}>{{ $red->nombre }}</option>
                    @endforeach
                </select>
                @error('red_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Zona <small class="text-muted">(opcional)</small></label>
                <select name="zona_id" class="form-control @error('zona_id') is-invalid @enderror">
                    <option value="">Sin zona</option>
                    @foreach($zonas as $zona)
                        <option value="{{ $zona->id }}" {{ (string) old('zona_id', $persona?->zona_id) === (string) $zona->id ? 'selected' : '' }}>{{ $zona->nombre }}</option>
                    @endforeach
                </select>
                @error('zona_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <script>
        document.getElementById('es_lider').addEventListener('change', function () {
            document.getElementById('datos-lider').style.display = this.checked ? '' : 'none';
        });
    </script>
@elseif($persona?->es_lider)
    <p><span class="badge badge-primary">Líder</span> {{ $persona->redLider?->nombre }} @if($persona->zona) · {{ $persona->zona->nombre }} @endif</p>
@endif
