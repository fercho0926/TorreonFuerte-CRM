@php
    $encuentro = $encuentro ?? null;
    $seleccionadas = $personasSeleccionadas ?? [];
    $opcionesEstado = collect(\App\Models\Encuentro::ESTADOS)->map(fn ($e) => ['id' => $e, 'texto' => ucfirst($e)])->all();
    $opcionesPersona = \App\Support\Formulario::opciones($personas, fn ($p) => $p->nombre_completo.($p->es_lider ? ' (líder)' : ''), fn ($p) => $p->numero_documento ? 'Doc. '.$p->numero_documento : ($p->celular ? 'Cel. '.$p->celular : null));
@endphp

<x-form.section titulo="Datos del encuentro" descripcion="Nombre, fecha, lugar y estado." icono="fa-calendar-check" id="seccion-encuentro">
    <x-form.input name="nombre" label="Nombre del encuentro" icono="fa-heading" :required="true" :value="$encuentro?->nombre" maxlength="255" />
    <x-form.date name="fecha_realizacion" label="Fecha de realización" :required="true" :value="$encuentro?->fecha_realizacion" col="col-md-3" />
    <x-form.select name="estado" label="Estado" icono="fa-toggle-on" :required="true" :opciones="$opcionesEstado" :value="$encuentro?->estado ?? 'planificado'" :vacio="null" col="col-md-3" />
    <x-form.input name="lugar" label="Lugar" icono="fa-map-marker-alt" :value="$encuentro?->lugar" maxlength="255" />
    <x-form.money name="valor" label="Valor" :required="true" :value="$encuentro?->valor ?? 0" />
    <x-form.textarea name="notas" label="Notas" :value="$encuentro?->notas" ayuda="Opcional: detalles que quieras recordar del encuentro." />
</x-form.section>

<x-form.section titulo="Participantes" descripcion="Elige a las personas que participan; puedes cambiarlas después." icono="fa-users" id="seccion-participantes">
    <x-form.multiselect name="personas[]" label="Personas participantes" :opciones="$opcionesPersona" :seleccionados="$seleccionadas" vacio="No hay personas registradas todavía." />
</x-form.section>
