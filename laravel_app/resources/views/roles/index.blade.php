@extends('layouts.admin')

@section('title', 'Roles')

@section('page-title', 'Roles y permisos')

@section('page-actions')
    @if(Route::has('roles.create'))
        <a href="{{ route('roles.create') }}" class="btn btn-primary">Crear rol</a>
    @endif
@endsection

@section('content')
@include('users._pestanas', ['activa' => 'roles'])
<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Roles de la aplicación" />
@endsection
