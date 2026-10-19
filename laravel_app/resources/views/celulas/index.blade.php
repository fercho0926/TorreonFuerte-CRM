@extends('layouts.admin')

@section('title', 'Células')

@section('page-title', 'Células / Grupos pequeños')

@if($puedeGestionarEstructura)
    @section('page-actions')
            <a href="{{ route('celulas.create') }}" class="btn btn-primary">Crear célula</a>
    @endsection
@endif

@section('content')
<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Listado de células" />
@endsection
