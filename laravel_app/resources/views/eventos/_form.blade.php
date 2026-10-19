@php
    $evento = $evento ?? null;
    $opcionesTipo = collect(\App\Models\Evento::TIPOS)->map(fn ($t) => ['id' => $t, 'texto' => $t])->all();
    $opcionesEstado = collect(\App\Models\Evento::ESTADOS)->map(fn ($e) => ['id' => $e, 'texto' => ucfirst($e)])->all();
@endphp

<x-form.section titulo="Datos del evento" descripcion="Qué evento es, cuándo y dónde." icono="fa-glass-cheers" id="seccion-evento">
    <x-form.input name="nombre" label="Nombre del evento" icono="fa-heading" :required="true" ejemplo="Ej. Cena de varones 2026" :value="$evento?->nombre" maxlength="255" />
    <x-form.select name="tipo" label="Tipo" icono="fa-tags" :required="true" :opciones="$opcionesTipo" :value="$evento?->tipo" col="col-md-3" />
    <x-form.date name="fecha" label="Fecha" :required="true" :value="$evento?->fecha" col="col-md-3" />
    <x-form.input name="lugar" label="Lugar" icono="fa-map-marker-alt" :value="$evento?->lugar" col="col-md-6" maxlength="255" />
    <x-form.select name="estado" label="Estado" icono="fa-toggle-on" :required="true" :opciones="$opcionesEstado" :value="$evento?->estado ?? 'planificado'" :vacio="null" col="col-md-6" />
    <x-form.textarea name="descripcion" label="Descripción" :value="$evento?->descripcion" ayuda="Opcional: detalles del evento." />
</x-form.section>

<x-form.section titulo="Costo" descripcion="Cuánto cuesta por persona." icono="fa-dollar-sign" id="seccion-costo">
    <x-form.money name="costo" label="Costo por persona" :required="true" :value="$evento?->costo ?? 0"
        ayuda="Se puede ajustar por persona o pagar en varios abonos desde el detalle del evento." />
</x-form.section>

@if(isset($personas))
    @php($opcionesPersona = \App\Support\Formulario::opciones($personas, fn ($p) => $p->nombre_completo, fn ($p) => $p->numero_documento ? 'Doc. '.$p->numero_documento : ($p->celular ? 'Cel. '.$p->celular : null)))
    <x-form.section titulo="Inscritos" descripcion="Personas a inscribir ahora; podrás inscribir más desde el detalle del evento." icono="fa-user-plus" id="seccion-inscritos">
        <x-form.multiselect name="personas[]" label="Personas a inscribir" :opciones="$opcionesPersona" vacio="No hay personas registradas todavía." />
    </x-form.section>
@endif
