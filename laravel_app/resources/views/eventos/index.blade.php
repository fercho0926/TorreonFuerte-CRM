@extends('layouts.admin')

@section('title', 'Eventos')

@section('page-title', 'Eventos')

@section('page-actions')
    <a href="{{ route('eventos.create') }}" class="btn btn-primary">Crear evento</a>
@endsection

@section('content')
<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Listado de eventos" />
@endsection
