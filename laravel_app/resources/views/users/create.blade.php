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
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Usuario</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
                @error('role')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar usuario</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
