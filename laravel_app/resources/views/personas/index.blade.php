@extends('layouts.admin')

@section('title', 'Miembros')

@section('page-title', 'Miembros')

@puede('crear')
    @section('page-actions')
        <a href="{{ route('personas.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1" aria-hidden="true"></i>{{ __('interfaz.crear_miembro') }}</a>
    @endsection
@endpuede

@section('content')
@if($sinPersonaVinculada)
    <div class="alert alert-info">{{ __('interfaz.tu_cuenta_aun_no_esta_asociada_a_un_miem') }}</div>
@endif

<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total">
    <x-slot:filtros>
        <x-form.switch name="inactivos" label="Incluir inactivos" :marcado="!empty($estado['inactivos']) || ($estado['estado'] ?? '') === 'inactivo'" col="col-auto" />
    </x-slot:filtros>
</x-ui.grilla>
@endsection
