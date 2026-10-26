@extends('layouts.admin')

@section('title', 'Miembros')

@section('page-title', 'Miembros')

@puede('crear')
    @section('page-actions')
        <a href="{{ route('personas.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1" aria-hidden="true"></i>Crear miembro</a>
    @endsection
@endpuede

@section('content')
@if($sinPersonaVinculada)
    <div class="alert alert-info">Tu cuenta aún no está asociada a un miembro, por eso no ves a nadie. Pide a un administrador que la vincule.</div>
@endif

<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total">
    <x-slot:filtros>
        <x-form.switch name="inactivos" label="Incluir inactivos" :marcado="!empty($estado['inactivos']) || ($estado['estado'] ?? '') === 'inactivo'" col="col-auto" />
    </x-slot:filtros>
</x-ui.grilla>
@endsection
