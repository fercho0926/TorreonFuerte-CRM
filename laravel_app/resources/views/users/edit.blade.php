@extends('layouts.admin')

@section('title', 'Editar usuario')

@section('page-title', 'Editar usuario')

@section('content')
<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">Editar usuario</h3>
    </div>
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Contraseña nueva (opcional)</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="form-group">
                <label>Rol</label>
                <select name="role" class="form-control @error('role') is-invalid @enderror">
                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Usuario</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
                @error('role')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Actualizar usuario</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
