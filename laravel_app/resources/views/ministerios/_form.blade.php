@php
    $ministerio = $ministerio ?? null;
    $seleccionadas = $personasSeleccionadas ?? [];
    $opcionesEstado = collect(\App\Models\Ministerio::ESTADOS)->map(fn ($e) => ['id' => $e, 'texto' => ucfirst($e)])->all();
    $opcionesPersona = \App\Support\Formulario::opciones($personas, fn ($p) => $p->nombre_completo.($p->es_lider ? ' (líder)' : ''), fn ($p) => $p->numero_documento ? 'Doc. '.$p->numero_documento : ($p->celular ? 'Cel. '.$p->celular : null));
@endphp

<x-form.section titulo="Datos del ministerio" descripcion="Nombre, encargado y estado." icono="fa-hands-helping" id="seccion-ministerio">
    <x-form.input name="nombre" label="Nombre del ministerio" icono="fa-heading" :required="true" ejemplo="Ej. Alabanza, Niños, Jóvenes..." :value="$ministerio?->nombre" maxlength="255" />
    <x-form.select name="encargado_id" label="Encargado" icono="fa-user-tie" :opciones="$opcionesPersona" :value="$ministerio?->encargado_id" vacio="Sin encargado asignado" col="col-md-3" buscar />
    <x-form.select name="estado" label="Estado" icono="fa-toggle-on" :required="true" :opciones="$opcionesEstado" :value="$ministerio?->estado ?? 'activo'" :vacio="null" col="col-md-3" />
    <x-form.textarea name="descripcion" label="Descripción" :value="$ministerio?->descripcion" ayuda="Opcional: a qué se dedica el ministerio." />
</x-form.section>

<x-form.section titulo="Integrantes" descripcion="El rol y la fecha de ingreso de cada integrante se editan desde el detalle del ministerio." icono="fa-users" id="seccion-integrantes">
    <x-form.multiselect name="personas[]" label="Integrantes" :opciones="$opcionesPersona" :seleccionados="$seleccionadas" vacio="No hay personas registradas todavía." />
</x-form.section>
