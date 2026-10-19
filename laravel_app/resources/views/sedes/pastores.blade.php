@extends('layouts.admin')

@section('title', 'Cambiar pastor o pastora')

@section('page-title', 'Cambiar pastor o pastora')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif

<div class="callout callout-info">
    <strong>{{ $sede->nombre }}</strong><br>
    Pastor actual: {{ $sede->pastor?->nombre_completo ?? '—' }} · Pastora actual: {{ $sede->pastora?->nombre_completo ?? '—' }}<br>
    <small>La cuenta anterior pasa a líder y conserva su registro como persona.</small>
</div>

<form method="POST" action="{{ route('sedes.pastores.update', $sede) }}">
    @csrf
    @method('PUT')
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">Nueva cuenta pastoral</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label>¿Quién cambia?</label>
                <select name="rol" class="form-control" required>
                    <option value="pastor" {{ old('rol') === 'pastor' ? 'selected' : '' }}>Pastor</option>
                    <option value="pastora" {{ old('rol') === 'pastora' ? 'selected' : '' }}>Pastora</option>
                </select>
            </div>

            <div class="form-group">
                <label>Designar una cuenta existente de esta sede</label>
                <select name="user_id" class="form-control">
                    <option value="">— No, crear una cuenta nueva —</option>
                    @foreach($cuentas as $cuenta)
                        <option value="{{ $cuenta->id }}" {{ (string) old('user_id') === (string) $cuenta->id ? 'selected' : '' }}>{{ $cuenta->name }} ({{ $cuenta->email }})</option>
                    @endforeach
                </select>
            </div>

            <hr>
            <p class="text-muted small">O completa los datos de la cuenta nueva:</p>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Nombres</label><input type="text" name="nombres" class="form-control" value="{{ old('nombres') }}"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Apellidos</label><input type="text" name="apellidos" class="form-control" value="{{ old('apellidos') }}"></div></div>
            </div>
            <div class="row">
                <div class="col-md-5"><div class="form-group"><label>Correo</label><input type="email" name="email" class="form-control" value="{{ old('email') }}"></div></div>
                <div class="col-md-4"><div class="form-group"><label>Contraseña inicial</label><input type="password" name="password" class="form-control" autocomplete="new-password"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Celular</label><input type="tel" name="celular" class="form-control" value="{{ old('celular') }}"></div></div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Guardar cambio</button>
            <a href="{{ route('sedes.show', $sede) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </div>
</form>
@endsection
