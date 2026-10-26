@php($nivel = $nivel ?? null)
<x-form.section titulo="Datos del nivel" descripcion="Un nivel es una etapa del plan de retiros. El orden se cambia desde la lista del plan." icono="fa-route" id="seccion-nivel">
    <x-form.input name="nombre" label="Nombre del nivel" icono="fa-heading" :required="true" ejemplo="Ej. Libres para servir" :value="$nivel?->nombre" maxlength="80" col="col-12" />
    <x-form.textarea name="descripcion" label="Descripción" :value="$nivel?->descripcion" ayuda="Opcional: de qué trata este nivel. Se muestra en el detalle del camino." />
</x-form.section>
