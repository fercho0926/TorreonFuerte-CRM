@extends('layouts.admin')

@section('title', 'Crear usuario')

@section('page-title', 'Crear nuevo usuario')

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Nuevo usuario</h3>
    </div>
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Rol</label>
                <select name="role" class="form-control @error('role') is-invalid @enderror" required>
                    @foreach($roles as $valor => $etiqueta)
                        <option value="{{ $valor }}" {{ old('role', 'user') == $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                @error('role')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            @if($sedes->isNotEmpty())
                <div class="form-group">
                    <label>Sede</label>
                    <select name="sede_id" class="form-control @error('sede_id') is-invalid @enderror">
                        <option value="">— Sede activa{{ $sedeEfectivaId ? '' : ' (elige una)' }} —</option>
                        @foreach($sedes as $sede)
                            <option value="{{ $sede->id }}" {{ (string) old('sede_id', $sedeEfectivaId) === (string) $sede->id ? 'selected' : '' }}>{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                    @error('sede_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    <small class="form-text text-muted">Los administradores del sistema no pertenecen a ninguna sede.</small>
                </div>
            @endif
            <div class="form-group">
                <label>Persona vinculada</label>
                <select name="persona_id" class="form-control @error('persona_id') is-invalid @enderror">
                    <option value="">Ninguna (cuenta técnica, ej. administrador del sistema)</option>
                    @foreach($personas as $persona)
                        <option value="{{ $persona->id }}" {{ (string) old('persona_id') === (string) $persona->id ? 'selected' : '' }}>{{ $persona->nombre_completo }}</option>
                    @endforeach
                </select>
                @error('persona_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <small class="form-text text-muted">Necesaria para roles de líder de célula/zona: así el sistema sabe qué célula(s) puede ver.</small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar usuario</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
