@php
    $user = $user ?? null;
    $opcionesRol = collect($roles)->map(fn ($etiqueta, $valor) => ['id' => $valor, 'texto' => $etiqueta])->values()->all();
@endphp

{{-- Solo lo propio de la cuenta: el nombre y el celular son del miembro (spec 016). --}}
<x-form.section titulo="Cuenta" descripcion="Correo con el que el miembro inicia sesión. La clave temporal se le envía por WhatsApp; tú nunca escribes ni ves su contraseña." icono="fa-user-circle" id="seccion-cuenta">
    <x-form.input name="email" label="Correo" type="correo" icono="fa-envelope" :required="true" :value="$user?->email" maxlength="255"
        ayuda="Debe ser único en todo el sistema." />
    <x-form.select name="role" label="Rol" icono="fa-user-shield" :required="true" :opciones="$opcionesRol" :value="$user?->role ?? 'user'" :vacio="null" />
</x-form.section>
