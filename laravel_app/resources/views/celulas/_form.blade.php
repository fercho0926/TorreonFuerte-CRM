@php
    $celula = $celula ?? null;
    $opcionesPersona = \App\Support\Formulario::opciones($personas, fn ($p) => $p->nombre_completo, fn ($p) => $p->numero_documento ? 'Doc. '.$p->numero_documento : ($p->celular ? 'Cel. '.$p->celular : null));
    $opcionesEstado = collect(\App\Models\Celula::ESTADOS)->map(fn ($e) => ['id' => $e, 'texto' => ucfirst($e)])->all();
    $integrantes = $integrantes ?? collect();
    $opcionesIntegrantes = \App\Support\Formulario::opciones($integrantes, fn ($p) => $p->nombre_completo, fn ($p) => $p->numero_documento ? 'Doc. '.$p->numero_documento : ($p->celular ? 'Cel. '.$p->celular : null));
    $opcionesDia = collect(\App\Models\Celula::DIAS)->map(fn ($d) => ['id' => $d, 'texto' => $d])->all();
@endphp

<x-form.section titulo="Datos del grupo" descripcion="Cómo se llama la célula y en qué estado está." icono="fa-people-arrows" id="seccion-grupo">
    <x-form.input name="nombre" label="Nombre del grupo" icono="fa-users" :required="true" ejemplo="Ej. Célula Los Olivos" :value="$celula?->nombre" maxlength="255" />
    <x-form.input name="sede" label="Sede o sector" icono="fa-map-marker-alt" :value="$celula?->sede" col="col-md-3" maxlength="255" />
    <x-form.select name="estado" label="Estado" icono="fa-toggle-on" :required="true" :opciones="$opcionesEstado" :value="$celula?->estado ?? 'activa'" :vacio="null" col="col-md-3" />
</x-form.section>

<x-form.section titulo="Liderazgo" descripcion="Quién dirige la célula y quién lo acompaña." icono="fa-user-tie" id="seccion-liderazgo">
    <x-form.select name="lider_id" label="Líder" icono="fa-user-tie" :required="true" :opciones="$opcionesPersona" :value="$celula?->lider_id" buscar />
    <x-form.select name="co_lider_id" label="Co-líder" icono="fa-user-friends" :opciones="$opcionesPersona" :value="$celula?->co_lider_id" vacio="Sin co-líder" buscar />
</x-form.section>

<x-form.section titulo="Reunión" descripcion="Cuándo y dónde se reúne." icono="fa-calendar-check" id="seccion-reunion">
    <x-form.select name="dia_reunion" label="Día de reunión" icono="fa-calendar-day" :opciones="$opcionesDia" :value="$celula?->dia_reunion" vacio="Sin definir" col="col-md-4" />
    <x-form.input name="hora_reunion" label="Hora de reunión" type="time" icono="fa-clock" :value="$celula?->hora_reunion ? substr($celula->hora_reunion, 0, 5) : ''" col="col-md-4" />
    <x-form.input name="lugar_reunion" label="Lugar de reunión" icono="fa-map-pin" :value="$celula?->lugar_reunion" col="col-md-4" maxlength="255" />
</x-form.section>

<x-form.section titulo="Integrantes" descripcion="Miembros que asisten a esta célula. Escribe para buscar por nombre, apellido, documento o celular; si aún no existe, créalo desde aquí." icono="fa-users" id="seccion-integrantes">
    <input type="hidden" name="integrantes_presente" value="1">
    <x-form.multiselect name="integrantes[]" label="Integrantes" :opciones="$opcionesIntegrantes" :seleccionados="$integrantes->pluck('id')->all()" icono="fa-users"
        :fuente="route('personas.buscar')" :filtros="['activas' => 1]" :crear="route('personas.rapida')"
        ayuda="Los miembros «En célula» aún no son parte de la iglesia, pero así se les da seguimiento." />
</x-form.section>
