@extends('layouts.admin')

@section('title', 'Encuentros')

@section('page-title', 'Encuentros')

@section('page-actions')
    <a href="{{ route('encuentros.create') }}" class="btn btn-primary">Crear encuentro</a>
@endsection

@section('content')
<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Listado de encuentros" />
@endsection
