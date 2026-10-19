@extends('layouts.admin')

@section('title', 'Personas')

@section('page-title', 'Personas')

@puede('crear')
    @section('page-actions')
        <a href="{{ route('personas.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1" aria-hidden="true"></i>Crear persona</a>
    @endsection
@endpuede

@section('content')
@if($sinPersonaVinculada)
    <div class="alert alert-info">Tu cuenta aún no está asociada a una persona, por eso no ves a nadie. Pide a un administrador que la vincule.</div>
@endif

@php
    $opcionesEstado = collect(\App\Models\Persona::ESTADOS)->map(fn ($e, $v) => ['id' => $v, 'texto' => $e])->values()->all();
    $opcionesRed = $redes->map(fn ($r) => ['id' => $r->id, 'texto' => $r->nombre])->all();
    $opcionesLider = $lideres->map(fn ($l) => ['id' => $l->id, 'texto' => $l->nombre_completo, 'apoyo' => $l->numero_documento ? 'Doc. '.$l->numero_documento : null])->all();
@endphp
<x-form.layout :action="route('personas.index')" method="GET" modo="filtro" guardar="Buscar" icono="fa-search" :leyenda="false">
    <x-form.input name="q" label="Buscar" icono="fa-search" :value="$filtros['q']" ejemplo="Nombre, apellido, documento o celular" col="col-md-4" />
    <x-form.select name="estado" label="Estado" icono="fa-user-check" :opciones="$opcionesEstado" :value="$filtros['estado']" vacio="Todos" col="col-md-2" />
    <x-form.select name="red_id" label="Red" icono="fa-sitemap" :opciones="$opcionesRed" :value="$filtros['red_id']" vacio="Todas" col="col-md-2" />
    <x-form.select name="lider_id" label="Líder" icono="fa-user-tie" :opciones="$opcionesLider" :value="$filtros['lider_id']" vacio="Todos" col="col-md-2" buscar />
    <x-form.switch name="inactivos" label="Incluir inactivos" :marcado="$filtros['inactivos']" col="col-auto" />
    <div class="col-auto fm-campo"><a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">Limpiar</a></div>
</x-form.layout>

<x-ui.tarjeta titulo="Personas" icono="fa-address-card" :contador="$personas->total()" :tabla="true">
    {{-- Tarjetas en pantallas pequeñas --}}
    <div class="d-md-none p-2">
        @forelse($personas as $persona)
            @include('partials.persona-tarjeta', ['persona' => $persona])
        @empty
            <x-ui.vacio mensaje="No se encontraron personas." icono="fa-address-card" />
        @endforelse
    </div>

    {{-- Tabla desde pantallas medianas --}}
    <div class="d-none d-md-block">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Celular</th>
                    <th>Red</th>
                    <th>Líder</th>
                    @todasLasSedes<th>Sede</th>@endtodasLasSedes
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($personas as $persona)
                    <tr>
                        <td>
                            <a href="{{ route('personas.show', $persona) }}">{{ $persona->nombre_completo }}</a>
                            @if($persona->es_lider)<x-ui.estado valor="lider" />@endif
                        </td>
                        <td><x-ui.estado :valor="$persona->estado" :etiqueta="$persona->estado_etiqueta" /></td>
                        <td>{{ $persona->celular ?? '—' }}</td>
                        <td>{{ $persona->redLider?->nombre ?? $persona->lider?->redLider?->nombre ?? '—' }}</td>
                        <td>{{ $persona->lider?->nombre_completo ?? '—' }}</td>
                        @todasLasSedes<td>{{ $persona->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-end text-nowrap">
                            <x-ui.acciones-fila :ver="route('personas.show', $persona)" :editar="route('personas.edit', $persona)" :puede-editar="\App\Support\Permisos::puede('editar', $persona)" :nombre="$persona->nombre_completo">
                                @puede('inactivar', $persona)
                                    @if($persona->estado !== 'inactivo')
                                        <x-form.confirm :action="route('personas.inactivar', $persona)" method="POST" titulo="¿Inactivar a esta persona?" mensaje="Su historial se conserva." boton="Inactivar" :peligro="false" variante="outline-secondary" tamano="sm">Inactivar</x-form.confirm>
                                    @endif
                                @endpuede
                            </x-ui.acciones-fila>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"><x-ui.vacio mensaje="No se encontraron personas." icono="fa-address-card" /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($personas->hasPages())
        <x-slot:pie>{{ $personas->links() }}</x-slot:pie>
    @endif
</x-ui.tarjeta>
@endsection
