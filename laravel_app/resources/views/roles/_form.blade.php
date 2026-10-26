{{-- Campos del formulario de rol. Variables: $rol (Rol o nulo), $niveles (módulo → nivel), $duplicado (bool, opcional). --}}
@php
    $rol = $rol ?? null;
    $niveles = $niveles ?? [];
    $duplicado = $duplicado ?? false;
@endphp
<x-form.section titulo="Datos del rol" descripcion="Cómo se llama y qué datos ve quien lo tenga." icono="fa-user-shield" id="seccion-rol">
    <x-form.input name="nombre" label="Nombre del rol" icono="fa-tag" :required="true" ejemplo="Ej. Secretaría" :value="$duplicado ? null : $rol?->nombre" maxlength="60" col="col-md-6" />
    <x-form.input name="descripcion" label="Descripción" icono="fa-align-left" :value="$rol?->descripcion" maxlength="255" col="col-md-6" />
    <x-form.select name="alcance" label="Alcance de datos" icono="fa-eye" :required="true" vacio="Elige el alcance" col="col-md-6"
        :opciones="\App\Support\Niveles::ALCANCES" :value="$rol?->alcance"
        ayuda="Toda la sede: ve todo lo de su sede. Solo su rama: él, sus miembros y sus células. Solo su registro: únicamente su ficha." />
    <x-form.switch name="asignable" label="Asignable por quien gestiona usuarios" :marcado="(bool) $rol?->asignable"
        ayuda="Si se marca, quien tenga escritura en Usuarios podrá dar este rol, siempre que no tenga más permisos que él." col="col-md-6" />
</x-form.section>

<x-form.matriz-permisos :niveles="$niveles" titulo="Permisos por módulo" />
