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

<x-form.layout :action="route('lideres.index')" method="GET" modo="filtro" guardar="Buscar" icono="fa-search" :leyenda="false">
    <x-form.input name="q" label="Buscar" icono="fa-search" :value="$filtros['q'] ?? ''" ejemplo="Nombre, documento o celular" col="col-md-4" />
    <x-form.select name="red_id" label="Red" icono="fa-sitemap" :opciones="$redes->map(fn ($r) => ['id' => $r->id, 'texto' => $r->nombre])->all()" :value="$filtros['red_id'] ?? ''" vacio="Todas" col="col-md-4" />
    <div class="col-auto fm-campo"><a href="{{ route('lideres.index') }}" class="btn btn-outline-secondary">Limpiar</a></div>
</x-form.layout>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Líderes ({{ $lideres->total() }})</h3>
    </div>
    <div class="card-body p-0">
        <div class="d-md-none p-2">
            @forelse($lideres as $lider)
                @include('partials.persona-tarjeta', ['persona' => $lider])
            @empty
                <p class="text-center text-muted my-3">No hay líderes registrados.</p>
            @endforelse
        </div>

        <div class="d-none d-md-block table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Red</th>
                        <th>Personas</th>
                        <th>Células</th>
                        <th>Celular</th>
                        @todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lideres as $lider)
                        <tr>
                            <td><a href="{{ route('lideres.show', $lider) }}">{{ $lider->nombre_completo }}</a></td>
                            <td>{{ $lider->redLider?->nombre ?? '—' }}</td>
                            <td><x-ui.insignia color="info">{{ $lider->miembros_count }}</x-ui.insignia></td>
                            <td><x-ui.insignia color="secondary">{{ $lider->celulas_lideradas_count }}</x-ui.insignia></td>
                            <td>{{ $lider->celular ?? '—' }}</td>
                            @todasLasSedes<td>{{ $lider->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-end text-nowrap">
                                <x-ui.acciones-fila :ver="route('lideres.show', $lider)" :editar="route('personas.edit', $lider)" :puede-editar="\App\Support\Permisos::puede('designarLider')" :nombre="$lider->nombre_completo">
                                    @puede('designarLider')
                                        <a href="{{ route('lideres.quitar', $lider) }}" class="btn btn-sm btn-outline-danger" title="Quitar rol" aria-label="Quitar rol de {{ $lider->nombre_completo }}"><i class="fas fa-user-minus" aria-hidden="true"></i></a>
                                    @endpuede
                                </x-ui.acciones-fila>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-ui.vacio mensaje="No hay líderes registrados." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($lideres->hasPages())
        <div class="card-footer">{{ $lideres->links() }}</div>
    @endif
</div>
@endsection
