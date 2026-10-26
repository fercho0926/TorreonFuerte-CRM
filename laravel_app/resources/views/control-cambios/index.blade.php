@extends('layouts.admin')

@section('title', 'Control de cambios')

@section('page-title', 'Control de cambios')

@section('content')
<div class="callout callout-info py-2 px-3 mb-3">
    <small>Todo lo que ha cambiado en la plataforma: quién, cuándo y qué. Las entradas no se pueden editar ni borrar, y este listado no se exporta.</small>
</div>

<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Cambios" icono="fa-history">
    <x-slot:filtros>
        <x-form.input name="desde" type="datetime-local" label="Desde" icono="fa-calendar-alt" :value="$estado['desde'] ?? ''" col="col-sm-6 col-md-3" />
        <x-form.input name="hasta" type="datetime-local" label="Hasta" icono="fa-calendar-alt" :value="$estado['hasta'] ?? ''" col="col-sm-6 col-md-3" />
    </x-slot:filtros>
</x-ui.grilla>
@endsection
