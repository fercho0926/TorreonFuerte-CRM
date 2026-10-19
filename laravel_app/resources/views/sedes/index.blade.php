@extends('layouts.admin')

@section('title', 'Sedes')

@section('page-title', 'Sedes')

@section('page-actions')
    <a href="{{ route('sedes.create') }}" class="btn btn-primary">Nueva sede</a>
@endsection

@section('content')
<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Iglesias" />
@endsection
