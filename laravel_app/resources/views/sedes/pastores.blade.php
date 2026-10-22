@extends('layouts.admin')

@section('title', 'Cambiar pastor o pastora')

@section('page-title', 'Cambiar pastor o pastora')

@section('content')
<div class="callout callout-info">
    <strong>{{ $sede->nombre }}</strong><br>
    Pastor actual: {{ $sede->pastor?->nombre_completo ?? '—' }} · Pastora actual: {{ $sede->pastora?->nombre_completo ?? '—' }}<br>
    <small>La cuenta anterior pasa a líder y conserva su registro como persona.</small>
</div>

<x-form.layout :action="route('sedes.pastores.update', $sede)" method="PUT" guardar="Guardar cambio" icono="fa-save" :cancelar="route('sedes.show', $sede)">
    <x-form.section titulo="¿Quién cambia?" descripcion="Elige el cargo y, si ya tiene cuenta en esta sede, designa esa cuenta." icono="fa-exchange-alt" id="seccion-cargo">
        <x-form.select name="rol" label="Cargo" icono="fa-user-tie" :required="true" :opciones="[['id' => 'pastor', 'texto' => 'Pastor'], ['id' => 'pastora', 'texto' => 'Pastora']]" :vacio="null" />
        <x-form.select name="user_id" label="Cuenta existente de esta sede" icono="fa-user-check" vacio="— No, crear una cuenta nueva —" buscar
            :opciones="$cuentas->map(fn ($c) => ['id' => $c->id, 'texto' => $c->name, 'apoyo' => $c->email])->all()" />
    </x-form.section>

    <x-form.section titulo="Cuenta nueva" descripcion="Completa estos datos solo si no designas una cuenta existente." icono="fa-user-plus" id="seccion-cuenta-nueva">
        <x-form.input name="nombres" label="Nombres" icono="fa-user" />
        <x-form.input name="apellidos" label="Apellidos" icono="fa-user" />
        <x-form.input name="email" label="Correo" type="correo" icono="fa-envelope" col="col-md-5" />
        <x-form.input name="password" label="Contraseña inicial" type="password" icono="fa-lock" minlength="8" col="col-md-4" />
        <x-form.input name="celular" label="Celular" type="celular" icono="fa-phone" mascara="celular" col="col-md-3" />
    </x-form.section>
</x-form.layout>
@endsection
