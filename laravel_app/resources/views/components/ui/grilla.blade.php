{{--
    Grilla estándar de listados (ver README → «Grillas»): búsqueda, filtros por columna, orden, filas por página,
    paginación y exportación a CSV. Sin JavaScript se ve la primera página con el paginador del servidor.
    Entradas: $grilla (App\Grillas\Grilla), $pagina (paginador inicial), $estado, $porPagina, $total.
    Slot `filtros`: campos propios del módulo dentro del formulario de búsqueda (nombres = extras de la grilla).
--}}
@props(['grilla', 'pagina', 'estado', 'porPagina' => 25, 'total' => 0, 'titulo' => null, 'icono' => null])
@php
    $usuario = \App\Http\Controllers\Controller::usuarioDeLaPeticion();
    $columnas = $grilla->columnas($usuario);
    $id = $grilla->id();
    $vacio = $grilla->vacio();
    $filas = $grilla->filas($pagina->items(), $usuario);
    $exportable = $grilla->puedeExportar($usuario);
    $consulta = request()->except(['page', 'por_pagina']);
    $orden = collect($columnas)->search(fn ($c) => $c->clave === $estado['orden']);
    $orden = $orden === false ? (int) collect($columnas)->search(fn ($c) => $c->ordenable) : $orden;
    $definiciones = collect($columnas)->map(fn ($c) => ['clave' => $c->clave, 'ordenable' => $c->ordenable, 'prioridad' => $c->prioridad, 'acciones' => $c->esAcciones, 'titulo' => $c->titulo])->values();
    $hayFormulario = $grilla->conBusqueda() || isset($filtros);
@endphp

@if($hayFormulario)
    <x-form.layout :action="route($id.'.index')" method="GET" modo="filtro" guardar="Buscar" icono="fa-search" :leyenda="false" data-grilla-form>
        @if($grilla->conBusqueda())
            <x-form.input name="q" label="Buscar" icono="fa-search" :value="$estado['q'] ?? ''" ejemplo="Escribe para buscar" col="col-md-5" />
        @endif
        {{ $filtros ?? '' }}
        <div class="col-auto fm-campo"><a href="{{ route($id.'.index') }}" class="btn btn-outline-secondary" data-grilla-limpiar>Limpiar</a></div>
    </x-form.layout>
@endif

<div class="card grilla-card mb-4">
    <div class="grilla-barra">
        <span class="text-muted small">
            @if($titulo)<strong class="me-2">@if($icono)<i class="fas {{ $icono }} me-1" aria-hidden="true"></i>@endif{{ $titulo }}</strong>@endif
            <span data-grilla-contador>{{ $pagina->total() }} {{ $pagina->total() === 1 ? 'resultado' : 'resultados' }}</span>
        </span>
        <div class="grilla-acciones">
            {{-- Solo teléfono con JavaScript: panel de filtros y orden (la tabla se ve como tarjetas). --}}
            <button type="button" class="btn btn-outline-secondary btn-sm grilla-filtros-btn" data-grilla-filtros aria-expanded="false"><i class="fas fa-filter me-1" aria-hidden="true"></i>Filtros <span class="badge text-bg-primary" data-grilla-filtros-n>0</span></button>
            <select class="form-select form-select-sm grilla-orden" data-grilla-orden aria-label="Ordenar por"></select>
            @if($exportable)
                <a href="{{ route($id.'.exportar', $consulta) }}" class="btn btn-outline-success btn-sm" data-grilla-exportar><i class="fas fa-file-csv me-1" aria-hidden="true"></i>Exportar CSV</a>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 grilla" data-grilla="{{ $id }}"
               data-url="{{ route($id.'.datos') }}"
               @if($exportable) data-exportar="{{ route($id.'.exportar') }}" @endif
               data-total="{{ $total }}"
               data-filtradas="{{ $pagina->total() }}"
               data-por-pagina="{{ $porPagina }}"
               data-opciones-pagina='@json(\App\Grillas\Grilla::POR_PAGINA)'
               data-columnas='@json($definiciones)'
               data-orden='@json([$orden, $estado['dir'] ?? 'asc'])'
               data-estado='@json($estado)'
               data-vacio="{{ $vacio['mensaje'] }}">
            <thead>
                <tr>
                    @foreach($columnas as $columna)
                        <th scope="col" @class(['text-end' => $columna->esAcciones]) data-columna="{{ $columna->clave }}">{{ $columna->titulo }}</th>
                    @endforeach
                </tr>
                <tr class="grilla-filtros">
                    @foreach($columnas as $columna)
                        <th>
                            @if($columna->filtro === \App\Grillas\Columna::FILTRO_LISTA)
                                <select class="form-select form-select-sm" aria-label="Filtrar por {{ mb_strtolower($columna->titulo) }}" data-filtro="{{ $columna->clave }}">
                                    <option value="">Todos</option>
                                    @foreach($columna->opciones as $valor => $texto)
                                        <option value="{{ $valor }}" @selected(($estado[$columna->clave] ?? '') === (string) $valor)>{{ $texto }}</option>
                                    @endforeach
                                </select>
                            @elseif($columna->filtro === \App\Grillas\Columna::FILTRO_TEXTO)
                                <input type="search" class="form-control form-control-sm" placeholder="Filtrar…" value="{{ $estado[$columna->clave] ?? '' }}" aria-label="Filtrar por {{ mb_strtolower($columna->titulo) }}" data-filtro="{{ $columna->clave }}">
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($filas as $celdas)
                    <tr>
                        @foreach($celdas as $i => $celda)
                            <td @class(['text-end text-nowrap grilla-celda-acciones' => $columnas[$i]->esAcciones, 'grilla-sec' => !$columnas[$i]->esAcciones && $columnas[$i]->prioridad > 3]) data-etiqueta="{{ $columnas[$i]->titulo }}">{!! $celda !!}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr class="grilla-vacio-servidor"><td colspan="{{ count($columnas) }}"><x-ui.vacio :mensaje="$vacio['mensaje']" :icono="$vacio['icono']" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación sin JavaScript; DataTables la reemplaza cuando carga. --}}
    @if($pagina->hasPages())
        <div class="card-footer grilla-pie-servidor">{{ $pagina->links() }}</div>
    @endif
</div>
