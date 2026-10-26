@extends('layouts.admin')

@php($titulo = $sin ? 'Sin iniciar' : ($nivel ? $nivel->nombre : 'Todos los miembros'))

@section('title', 'Miembros por nivel')

@section('page-title', 'Miembros · '.$titulo)

@section('page-actions')
    <a href="{{ route('niveles.index') }}" class="btn btn-outline-secondary"><i class="fas fa-route me-1" aria-hidden="true"></i>Volver a Niveles</a>
@endsection

@section('content')
@if($nivel)
    <nav class="niveles-vistas mb-3" aria-label="Qué lista ver">
        <ul class="nav nav-pills">
            @foreach($vistas as $clave => $etiqueta)
                <li class="nav-item">
                    <a class="nav-link @if($vista === $clave) active @endif" @if($vista === $clave) aria-current="page" @endif
                       href="{{ route('niveles.miembros.index', ['nivel' => $nivel->id, 'vista' => $clave]) }}">{{ $etiqueta }}</a>
                </li>
            @endforeach
        </ul>
    </nav>
    @if($nivel->descripcion)<p class="text-muted">{{ $nivel->descripcion }}</p>@endif
@endif

<x-ui.grilla :grilla="$grilla" :pagina="$pagina" :estado="$estado" :por-pagina="$porPagina" :total="$total" :titulo="$titulo" icono="fa-route">
    <x-slot:filtros>
        <input type="hidden" name="nivel" value="{{ $sin ? 'sin' : ($nivel?->id) }}">
        <input type="hidden" name="vista" value="{{ $vista }}">
        <x-form.switch name="inactivos" label="Incluir inactivos" :marcado="!empty($estado['inactivos'])" col="col-auto" />
    </x-slot:filtros>
</x-ui.grilla>
@endsection
