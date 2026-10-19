@extends('layouts.admin')

@section('title', 'Sin líder asignado')

@section('page-title', 'Sin líder asignado')

@section('content')
<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Personas esperando un líder" />
@endsection
