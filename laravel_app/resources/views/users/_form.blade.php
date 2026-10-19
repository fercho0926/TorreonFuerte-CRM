@php
    $user = $user ?? null;
    $editando = $user !== null;
    $opcionesRol = collect($roles)->map(fn ($etiqueta, $valor) => ['id' => $valor, 'texto' => $etiqueta])->values()->all();
    $opcionesPersona = \App\Support\Formulario::opciones($personas, fn ($p) => $p->nombre_completo, fn ($p) => $p->numero_documento ? 'Doc. '.$p->numero_documento : ($p->celular ? 'Cel. '.$p->celular : null));
@endphp

<x-form.section titulo="Cuenta" descripcion="Nombre y correo con el que la persona inicia sesión." icono="fa-user-circle" id="seccion-cuenta">
    <x-form.input name="name" label="Nombre" icono="fa-user" :required="true" :value="$user?->name" maxlength="255" />
    <x-form.input name="email" label="Correo" type="email" icono="fa-envelope" :required="true" :value="$user?->email" maxlength="255"
        ayuda="Debe ser único en todo el sistema." />
</x-form.section>

<x-form.section titulo="Acceso" descripcion="Contraseña y rol que define lo que puede hacer." icono="fa-key" id="seccion-acceso">
    <x-form.input name="password" :label="$editando ? 'Contraseña nueva (opcional)' : 'Contraseña'" type="password" icono="fa-lock" :required="!$editando" minlength="6"
        ayuda="Mínimo 6 caracteres." />
    <x-form.input name="password_confirmation" label="Confirmar contraseña" type="password" icono="fa-lock" :required="!$editando" />
    <x-form.select name="role" label="Rol" icono="fa-user-shield" :required="true" :opciones="$opcionesRol" :value="$user?->role ?? 'user'" :vacio="null" />
    @if($sedes->isNotEmpty() && !$editando)
        <x-form.select name="sede_id" label="Sede" icono="fa-church" :opciones="$sedes->map(fn ($s) => ['id' => $s->id, 'texto' => $s->nombre])->all()" :value="$sedeEfectivaId"
            :vacio="$sedeEfectivaId ? 'Sede activa' : 'Elige una sede'" ayuda="Los administradores del sistema no pertenecen a ninguna sede." />
    @endif
</x-form.section>

<x-form.section titulo="Vínculo con persona" descripcion="Para roles de líder: así el sistema sabe qué personas y células puede ver." icono="fa-link" id="seccion-vinculo">
    <x-form.select name="persona_id" label="Persona vinculada" icono="fa-address-card" :opciones="$opcionesPersona" :value="$user?->persona_id"
        vacio="Ninguna (cuenta técnica, ej. administrador del sistema)" col="col-12" buscar />
</x-form.section>
