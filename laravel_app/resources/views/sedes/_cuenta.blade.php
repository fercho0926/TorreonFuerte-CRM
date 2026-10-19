@php($v = fn ($campo) => old("{$prefijo}.{$campo}"))
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombres</label>
            <input type="text" name="{{ $prefijo }}[nombres]" class="form-control @error($prefijo.'.nombres') is-invalid @enderror" value="{{ $v('nombres') }}" @if($obligatoria) required @endif>
            @error($prefijo.'.nombres')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Apellidos</label>
            <input type="text" name="{{ $prefijo }}[apellidos]" class="form-control @error($prefijo.'.apellidos') is-invalid @enderror" value="{{ $v('apellidos') }}" @if($obligatoria) required @endif>
            @error($prefijo.'.apellidos')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-5">
        <div class="form-group">
            <label>Correo (para iniciar sesión)</label>
            <input type="email" name="{{ $prefijo }}[email]" class="form-control @error($prefijo.'.email') is-invalid @enderror" value="{{ $v('email') }}" @if($obligatoria) required @endif>
            @error($prefijo.'.email')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Contraseña inicial</label>
            <input type="password" name="{{ $prefijo }}[password]" class="form-control @error($prefijo.'.password') is-invalid @enderror" autocomplete="new-password" @if($obligatoria) required @endif>
            @error($prefijo.'.password')<span class="invalid-feedback">{{ $message }}</span>@enderror
            <small class="form-text text-muted">Mínimo 8 caracteres.</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Celular</label>
            <input type="tel" name="{{ $prefijo }}[celular]" class="form-control @error($prefijo.'.celular') is-invalid @enderror" value="{{ $v('celular') }}">
            @error($prefijo.'.celular')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
