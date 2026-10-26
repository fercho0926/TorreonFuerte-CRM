{{--
    Filtros de la pestaña «Histórico» (formulario GET: funciona sin JavaScript y conserva la pestaña).
    Props: accion (url de la ficha), filtros (de LecturaHistorial::filtros), usuarios (id → nombre),
    acciones (clave → etiqueta; vacío en el miembro, que se organiza por secciones), extra (parámetros a conservar).
--}}
@props(['accion', 'filtros', 'usuarios' => [], 'acciones' => [], 'extra' => []])
@php
    $hayFiltros = array_filter($filtros) !== [];
    $actores = ['qr' => 'Registro público por QR', 'automatico' => 'Automático', 'sistema' => 'Sistema'] + $usuarios;
@endphp
<details class="historial-filtros mb-3" @if($hayFiltros) open @endif>
    <summary class="btn btn-outline-secondary btn-sm"><i class="fas fa-filter me-1" aria-hidden="true"></i>Filtrar el histórico</summary>
    <div class="pt-3">
        <x-form.layout :action="$accion" method="GET" modo="filtro" guardar="Filtrar" icono="fa-filter" :leyenda="false">
            <input type="hidden" name="pestana" value="historico">
            @foreach($extra as $nombre => $valor)
                <input type="hidden" name="{{ $nombre }}" value="{{ $valor }}">
            @endforeach
            <x-form.date name="desde" label="Desde" :value="$filtros['desde']" atajos="" col="col-sm-6 col-md-3" />
            <x-form.date name="hasta" label="Hasta" :value="$filtros['hasta']" atajos="" col="col-sm-6 col-md-3" />
            <x-form.select name="usuario" label="Usuario" :opciones="$actores" :value="$filtros['usuario']" vacio="Todos" col="col-sm-6 col-md-3" />
            @if($acciones)
                <x-form.select name="accion" label="Acción" :opciones="$acciones" :value="$filtros['accion']" vacio="Todas" col="col-sm-6 col-md-3" />
            @endif
            @if($hayFiltros)
                <div class="col-auto fm-campo"><a href="{{ $accion }}?pestana=historico" class="btn btn-outline-secondary">Limpiar</a></div>
            @endif
        </x-form.layout>
    </div>
</details>
