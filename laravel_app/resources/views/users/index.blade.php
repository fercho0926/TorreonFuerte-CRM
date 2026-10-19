@extends('layouts.admin')

@section('title', 'Usuarios')

@section('page-title', 'Usuarios registrados')

@section('page-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary">Crear usuario</a>
@endsection

@section('content')
<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" titulo="Lista de usuarios">
    @if($sedes->isNotEmpty())
        <x-slot:filtros>
            <x-form.select name="sede_id" label="Sede" icono="fa-church" :opciones="$sedes->map(fn ($x) => ['id' => $x->id, 'texto' => $x->nombre])->all()" :value="$estado['sede_id'] ?? null" vacio="Todas" col="col-md-4" />
        </x-slot:filtros>
    @endif
</x-ui.grilla>
@endsection
