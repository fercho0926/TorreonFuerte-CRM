@extends('layouts.admin')

@section('title', 'Ministerios')

@section('page-title', 'Ministerios')

@section('page-actions')
    <a href="{{ route('ministerios.create') }}" class="btn btn-primary">Crear ministerio</a>
@endsection

@section('content')
<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Listado de ministerios" />
@endsection
