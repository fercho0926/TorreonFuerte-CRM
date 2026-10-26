@extends('layouts.admin')

@section('title', 'Eventos')

@section('page-title', 'Eventos')

@section('page-actions')
    @puedeModulo('eventos', 'escritura')
    <a href="{{ route('eventos.create') }}" class="btn btn-primary">Crear evento</a>
    @endpuedeModulo
@endsection

@section('content')
<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Listado de eventos" />
@endsection
