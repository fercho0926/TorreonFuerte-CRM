@php
    $persona = $persona ?? null;
    $duplicado = session('duplicado');
    $esLider = (bool) ($persona?->es_lider ?? ($esLiderInicial ?? false));
    $opcionesLider = \App\Support\Formulario::opciones(
        $lideres,
        fn ($l) => $l->nombre_completo,
        fn ($l) => $l->numero_documento ? 'Doc. '.$l->numero_documento : ($l->celular ? 'Cel. '.$l->celular : null)
    );
    $opcionesEstado = collect(\App\Models\Persona::ESTADOS)->map(fn ($etiqueta, $valor) => ['id' => $valor, 'texto' => $etiqueta])->values()->all();
@endphp

@if($duplicado)
    <div class="alert alert-warning" role="alert">
        <strong>Posible duplicado:</strong> ya existe una persona con el mismo nombre y celular:
        @if($duplicado['puedeVer'])
            <a href="{{ route('personas.show', $duplicado['id']) }}" target="_blank" rel="noopener">{{ $duplicado['nombre'] }}</a>.
        @else
            {{ $duplicado['nombre'] }}.
        @endif
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" id="confirmar_duplicado" name="confirmar_duplicado" value="1">
            <label class="form-check-label" for="confirmar_duplicado">Es otra persona, guardar de todos modos</label>
        </div>
    </div>
@endif

<x-form.section titulo="Datos personales" descripcion="Nombre, contacto y estado. Basta con el celular o el correo." icono="fa-user" id="seccion-personal">
    <x-form.input name="nombres" label="Nombres" icono="fa-user" :required="true" ejemplo="Ej. Ana María" :value="$persona?->nombres" maxlength="255" />
    <x-form.input name="apellidos" label="Apellidos" icono="fa-user" :required="true" ejemplo="Ej. Pérez Soto" :value="$persona?->apellidos" maxlength="255" />
    <x-form.date name="fecha_nacimiento" label="Fecha de nacimiento" :value="$persona?->fecha_nacimiento" max="hoy" atajos="" col="col-md-4"
        ayuda="Opcional. Con ella se calcula la edad." />
    <x-form.input name="celular" label="Celular" type="celular" icono="fa-phone" mascara="celular" ejemplo="310 555 9999" :value="$persona?->celular" maxlength="20" col="col-md-4" />
    <x-form.input name="email" label="Correo" type="correo" icono="fa-envelope" ejemplo="nombre@correo.com" :value="$persona?->email" maxlength="255" col="col-md-4" />
    <x-form.select name="estado" label="Estado" icono="fa-user-check" :required="true" :opciones="$opcionesEstado" :value="$persona?->estado ?? 'visitante'" :vacio="null" col="col-md-4"
        ayuda="«En célula»: asiste a una célula y aún no es parte de la iglesia." />
</x-form.section>

<x-form.section titulo="Ubicación" descripcion="Dónde vive la persona." icono="fa-map-marker-alt" id="seccion-ubicacion">
    <x-form.input name="direccion" label="Dirección" icono="fa-home" :value="$persona?->direccion" col="col-12" maxlength="255" />
    <x-form.input name="barrio" label="Barrio" icono="fa-map" :value="$persona?->barrio" maxlength="255" />
    <x-form.input name="ciudad" label="Ciudad" icono="fa-city" :value="$persona?->ciudad" maxlength="255" />
</x-form.section>

<x-form.section titulo="Liderazgo" descripcion="Quién cuida de esta persona y si ella misma es líder." icono="fa-user-tie" id="seccion-liderazgo">
    <div class="col-12">@include('partials.ayuda-terminos')</div>

    <x-form.select name="lider_id" label="Líder directo" icono="fa-user-tie" :opciones="$opcionesLider" :value="$persona?->lider_id" vacio="Sin líder asignado" col="col-12" buscar
        ayuda="Es el líder que cuida de esta persona. Si cambia, la persona sale de las células de su líder anterior." />

    @if($puedeDesignarLider)
        <x-form.switch name="es_lider" label="Esta persona es líder" :marcado="$esLider" icono="fa-star" />
    @elseif($persona?->es_lider)
        <div class="col-12"><p><x-ui.estado valor="lider" /></p></div>
    @endif
</x-form.section>
