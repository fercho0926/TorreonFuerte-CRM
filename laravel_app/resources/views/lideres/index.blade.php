@extends('layouts.admin')

@section('title', 'Líderes')

@section('page-title', 'Líderes')

@puede('designarLider')
    @section('page-actions')
            <a href="{{ route('lideres.designar') }}" class="btn btn-primary">Designar líder</a>
    @endsection
@endpuede

@section('content')

@include('partials.ayuda-terminos')

<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" />
@endsection
