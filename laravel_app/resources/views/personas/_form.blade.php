@php
    $persona = $persona ?? null;
    $duplicado = session('duplicado');
    $esLider = (bool) ($persona?->es_lider ?? ($esLiderInicial ?? false));
    $opcionesLider = \App\Support\Formulario::opciones(
        $lideres,
        fn ($l) => $l->nombre_completo,
        fn ($l) => $l->numero_documento ? 'Doc. '.$l->numero_documento : ($l->celular ? 'Cel. '.$l->celular : null)
    );
    // «Inactivo» no es una opción al crear: un miembro no nace inactivo, se inactiva luego (botón «Inactivar»).
    $estados = $persona ? \App\Models\Persona::ESTADOS : collect(\App\Models\Persona::ESTADOS)->except('inactivo')->all();
    $opcionesEstado = collect($estados)->map(fn ($etiqueta, $valor) => ['id' => $valor, 'texto' => $etiqueta])->values()->all();
    $opcionesDocumento = array_combine(\App\Models\Persona::TIPOS_DOCUMENTO, \App\Models\Persona::TIPOS_DOCUMENTO);
@endphp

@if($duplicado)
    <div class="alert alert-warning" role="alert">
        <strong>Posible duplicado:</strong> ya existe un miembro con el mismo nombre y celular:
        @if($duplicado['puedeVer'])
            <a href="{{ route('personas.show', $duplicado['id']) }}" target="_blank" rel="noopener">{{ $duplicado['nombre'] }}</a>.
        @else
            {{ $duplicado['nombre'] }}.
        @endif
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" id="confirmar_duplicado" name="confirmar_duplicado" value="1">
            <label class="form-check-label" for="confirmar_duplicado">Es otro miembro, guardar de todos modos</label>
        </div>
    </div>
@endif

<x-form.section titulo="Datos personales" descripcion="Nombre, contacto y estado. El celular es obligatorio: es el canal por el que se envía información por WhatsApp." icono="fa-user" id="seccion-personal">
    <x-form.input name="nombres" label="Nombres" icono="fa-user" :required="true" ejemplo="Ej. Ana María" :value="$persona?->nombres" maxlength="255" />
    <x-form.input name="apellidos" label="Apellidos" icono="fa-user" :required="true" ejemplo="Ej. Pérez Soto" :value="$persona?->apellidos" maxlength="255" />
    <x-form.date name="fecha_nacimiento" label="Fecha de nacimiento" :value="$persona?->fecha_nacimiento" max="hoy" atajos="" col="col-md-4"
        ayuda="Opcional. Con ella se calcula la edad." />
    <x-form.select name="tipo_documento" label="Tipo de documento" icono="fa-id-card" :required="true" :opciones="$opcionesDocumento" :value="$persona?->tipo_documento" col="col-md-4" />
    <x-form.input name="numero_documento" label="Número de documento" type="numero" icono="fa-id-card" :required="true" ejemplo="1234567890" :value="$persona?->numero_documento" maxlength="30" col="col-md-4" />
    <x-form.select name="estado" label="Estado" icono="fa-user-check" :required="true" :opciones="$opcionesEstado" :value="$persona?->estado ?? 'visitante'" :vacio="null" col="col-md-4"
        ayuda="«En célula»: asiste a una célula y aún no es parte de la iglesia." />
    <x-form.input name="invitado_por" label="¿Quién lo invitó?" icono="fa-hand-holding-heart" visibleSi="estado:visitante" ejemplo="Nombre de quien lo invitó" :value="$persona?->invitado_por" maxlength="255" col="col-md-4" />
    <x-form.input name="celular" label="Celular" type="celular" icono="fa-phone" mascara="celular" :required="true" ejemplo="310 555 9999" ayuda="Lo usamos para enviar información por WhatsApp." :value="$persona?->celular" maxlength="20" col="col-md-4" />
    <x-form.input name="email" label="Correo" type="correo" icono="fa-envelope" ejemplo="nombre@correo.com" :value="$persona?->email" maxlength="255" col="col-md-4" />
</x-form.section>

<x-form.section titulo="Ubicación" descripcion="Dónde vive el miembro." icono="fa-map-marker-alt" id="seccion-ubicacion">
    <x-form.input name="barrio" label="Barrio" icono="fa-map" :value="$persona?->barrio" maxlength="255" />
    <x-form.input name="ciudad" label="Ciudad" icono="fa-city" :value="$persona?->ciudad" maxlength="255" />
</x-form.section>

<x-form.section titulo="Liderazgo" descripcion="Quién cuida de este miembro y si él mismo es líder." icono="fa-user-tie" id="seccion-liderazgo">
    <div class="col-12">@include('partials.ayuda-terminos')</div>

    <x-form.select name="lider_id" label="Líder directo" icono="fa-user-tie" :opciones="$opcionesLider" :value="$persona?->lider_id" vacio="Sin líder asignado" col="col-12" buscar
        ayuda="Es el líder que cuida de este miembro. Si cambia, el miembro sale de las células de su líder anterior." />

    {{-- Designar líder se hace desde el módulo Líderes; aquí solo se ve si ya es líder o si se llegó
         desde «Nuevo miembro como líder» (lideres.designar). --}}
    @if($puedeDesignarLider && ($persona || ($esLiderInicial ?? false)))
        <x-form.switch name="es_lider" label="Este miembro es líder" :marcado="$esLider" icono="fa-star" />
    @elseif($persona?->es_lider)
        <div class="col-12"><p><x-ui.estado valor="lider" /></p></div>
    @endif
</x-form.section>
