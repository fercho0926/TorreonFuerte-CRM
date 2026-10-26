@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Panel de administración')

{{-- Diseño basado en «Dashboard v2» de AdminLTE 4: info-boxes, resumen mensual con gráfica y metas,
     y dos columnas (8/4) con listas, tabla, cajas de color y gráfica de dona. --}}
@php
    $porcentaje = fn ($parte, $total) => $total > 0 ? (int) round($parte / $total * 100) : 0;
    $iniciales = fn (string $nombre) => collect(preg_split('/\s+/', trim($nombre)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $coloresAvatar = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];

    if ($mensual) {
        $nuevosMes = end($mensual['miembros']);
        $nuevosMesAnterior = $mensual['miembros'][count($mensual['miembros']) - 2] ?? 0;
        $variacion = $nuevosMesAnterior > 0 ? (int) round(($nuevosMes - $nuevosMesAnterior) / $nuevosMesAnterior * 100) : null;
        $graficaMensual = [
            'tipo' => 'mensual',
            'categorias' => $mensual['meses'],
            'series' => array_values(array_filter([
                ['name' => 'Miembros nuevos', 'data' => $mensual['miembros']],
                $mensual['recaudo'] ? ['name' => 'Recaudo de eventos', 'data' => $mensual['recaudo']] : null,
            ])),
        ];
    }

    $graficaEstados = ['tipo' => 'dona', 'etiquetas' => array_keys($porEstado), 'valores' => array_values($porEstado)];

    // Metas del resumen (barras de progreso a la derecha de la gráfica): solo las que el usuario puede ver.
    $metas = [];
    if ($personasSinLider !== null && $personaCount > 0) {
        $metas[] = ['texto' => 'Miembros con líder', 'parte' => $personaCount - $sinLiderTotal, 'total' => $personaCount, 'color' => 'primary'];
    }
    if ($porEstado) {
        $activos = $porEstado[\App\Models\Persona::ESTADOS['miembro_activo']] ?? 0;
        $metas[] = ['texto' => 'Miembros activos', 'parte' => $activos, 'total' => array_sum($porEstado), 'color' => 'success'];
    }
    if ($resumenNiveles && $resumenNiveles['total'] > 0) {
        $metas[] = ['texto' => 'Con nivel de formación iniciado', 'parte' => $resumenNiveles['total'] - $resumenNiveles['sin_iniciar'], 'total' => $resumenNiveles['total'], 'color' => 'warning'];
    }
    if ($totalEsperadoEventos > 0) {
        $metas[] = ['texto' => 'Recaudo de eventos', 'parte' => $totalRecaudadoEventos, 'total' => $totalEsperadoEventos, 'color' => 'danger', 'dinero' => true];
    }
@endphp

@section('content')
<div class="d-lg-none">
    @include('partials.accesos-rapidos')
</div>

{{-- Fila de info-boxes --}}
<div class="row">
    @puedeModulo('miembros')
        <div class="col-12 col-sm-6 col-md-3"><x-ui.info-caja :valor="number_format($personaCount)" texto="Miembros registrados" icono="fa-address-card" color="info" :enlace="route('personas.index')" /></div>
    @endpuedeModulo
    @puedeModulo('lideres')
        <div class="col-12 col-sm-6 col-md-3"><x-ui.info-caja :valor="number_format($liderCount)" texto="Líderes" icono="fa-user-tie" color="danger" :enlace="route('lideres.index')" /></div>
    @endpuedeModulo
    @if($celulaCount !== null)
        <div class="col-12 col-sm-6 col-md-3"><x-ui.info-caja :valor="number_format($celulaCount)" texto="Células" icono="fa-people-arrows" color="success" :enlace="route('celulas.index')" /></div>
    @endif
    @if($personasSinLider !== null)
        <div class="col-12 col-sm-6 col-md-3"><x-ui.info-caja :valor="number_format($sinLiderTotal)" texto="Sin líder asignado" icono="fa-user-clock" color="warning" :enlace="route('sin-lider.index')" /></div>
    @endif
</div>

@if($resumenSedes)
    <div class="card mb-4">
        <div class="card-header"><h3 class="card-title">Resumen por sede</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="tabla-apilable table table-hover mb-0">
                <thead>
                    <tr><th>Sede</th><th>Estado</th><th>Miembros</th><th>Líderes</th><th>Células</th><th>Eventos próximos</th><th>Recaudo de eventos</th></tr>
                </thead>
                <tbody>
                    @foreach($resumenSedes as $fila)
                        <tr>
                            <td data-etiqueta="Sede" data-primaria><a href="{{ route('sedes.show', $fila['sede']) }}">{{ $fila['sede']->nombre }}</a></td>
                            <td data-etiqueta="Estado"><x-ui.insignia :color="$fila['sede']->activa ? 'success' : 'secondary'">{{ $fila['sede']->activa ? 'Activa' : 'Inactiva' }}</x-ui.insignia></td>
                            <td data-etiqueta="Miembros">{{ $fila['personas'] }}</td>
                            <td data-etiqueta="Líderes">{{ $fila['lideres'] }}</td>
                            <td data-etiqueta="Células">{{ $fila['celulas'] }}</td>
                            <td data-etiqueta="Eventos próximos">{{ $fila['eventos_proximos'] }}</td>
                            <td data-etiqueta="Recaudo de eventos">${{ number_format($fila['recaudo'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="2">Total global (todas las sedes)</td>
                        <td data-etiqueta="Miembros" data-primaria>{{ $resumenSedes->sum('personas') }}</td>
                        <td data-etiqueta="Líderes">{{ $resumenSedes->sum('lideres') }}</td>
                        <td data-etiqueta="Células">{{ $resumenSedes->sum('celulas') }}</td>
                        <td data-etiqueta="Eventos próximos">{{ $resumenSedes->sum('eventos_proximos') }}</td>
                        <td data-etiqueta="Recaudo de eventos">${{ number_format($resumenSedes->sum('recaudo'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <p class="text-muted small">Las cifras de este tablero suman <strong>todas las sedes</strong>. Elige una sede activa en la barra superior para ver una sola.</p>
@endif

{{-- Resumen mensual: gráfica (8) + metas (4) y pie con indicadores --}}
@if($mensual)
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Resumen de los últimos 12 meses</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Plegar">
                <i data-lte-icon="expand" class="fas fa-plus"></i><i data-lte-icon="collapse" class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="{{ $metas ? 'col-md-8' : 'col-12' }}">
                <p class="text-center"><strong>{{ ucfirst($mensual['desde']->locale('es')->isoFormat('D MMM YYYY')) }} – {{ ucfirst($mensual['hasta']->locale('es')->isoFormat('D MMM YYYY')) }}</strong></p>
                <div class="grafica-tablero" data-grafica='@json($graficaMensual)' role="img" aria-label="Miembros nuevos{{ $mensual['recaudo'] ? ' y recaudo de eventos' : '' }} por mes"></div>
            </div>
            @if($metas)
                <div class="col-md-4">
                    <p class="text-center"><strong>Metas</strong></p>
                    @foreach($metas as $meta)
                        <div class="progress-group">
                            {{ $meta['texto'] }}
                            <span class="float-end">
                                @if($meta['dinero'] ?? false)
                                    <b>${{ number_format($meta['parte'], 0) }}</b>/${{ number_format($meta['total'], 0) }}
                                @else
                                    <b>{{ number_format($meta['parte']) }}</b>/{{ number_format($meta['total']) }}
                                @endif
                            </span>
                            <div class="progress progress-sm" role="progressbar" aria-label="{{ $meta['texto'] }}" aria-valuenow="{{ $porcentaje($meta['parte'], $meta['total']) }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar text-bg-{{ $meta['color'] }}" style="width: {{ $porcentaje($meta['parte'], $meta['total']) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-6 col-md-3">
                <div class="text-center border-end">
                    @if($variacion !== null)
                        <span class="text-{{ $variacion >= 0 ? 'success' : 'danger' }}"><i class="fas fa-caret-{{ $variacion >= 0 ? 'up' : 'down' }}" aria-hidden="true"></i> {{ abs($variacion) }}%</span>
                    @else
                        <span class="text-secondary"><i class="fas fa-minus" aria-hidden="true"></i></span>
                    @endif
                    <h5 class="fw-bold mb-0">{{ number_format($nuevosMes) }}</h5>
                    <span class="text-uppercase small text-muted">Nuevos este mes</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center border-md-end">
                    <span class="text-info"><i class="fas fa-calendar" aria-hidden="true"></i> 12 meses</span>
                    <h5 class="fw-bold mb-0">{{ number_format(array_sum($mensual['miembros'])) }}</h5>
                    <span class="text-uppercase small text-muted">Miembros nuevos</span>
                </div>
            </div>
            @puedeModulo('eventos')
                <div class="col-6 col-md-3">
                    <div class="text-center border-end">
                        <span class="text-success"><i class="fas fa-hand-holding-usd" aria-hidden="true"></i> {{ $porcentaje($totalRecaudadoEventos, $totalEsperadoEventos) }}%</span>
                        <h5 class="fw-bold mb-0">${{ number_format($totalRecaudadoEventos, 2) }}</h5>
                        <span class="text-uppercase small text-muted">Total recaudado en eventos</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center">
                        <span class="text-warning"><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i> {{ $eventoCount }} eventos</span>
                        <h5 class="fw-bold mb-0">${{ number_format($totalEsperadoEventos, 2) }}</h5>
                        <span class="text-uppercase small text-muted">Total esperado en eventos</span>
                    </div>
                </div>
            @endpuedeModulo
        </div>
    </div>
</div>
@endif

<div class="row">
    {{-- Columna principal --}}
    <div class="col-lg-8">
        @puedeModulo('miembros')
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus me-1" aria-hidden="true"></i>Miembros nuevos</h3>
                @if($personasNuevas->isNotEmpty())
                    <div class="card-tools"><x-ui.insignia color="success">{{ $personasNuevas->count() }} recientes</x-ui.insignia></div>
                @endif
            </div>
            <div class="card-body p-0">
                @if($personasNuevas->isEmpty())
                    <div class="p-3"><x-ui.vacio mensaje="Aún no hay miembros registrados." icono="fa-address-card" /></div>
                @else
                    <ul class="lista-miembros">
                        @foreach($personasNuevas as $persona)
                            <li>
                                <a href="{{ route('personas.show', $persona) }}" class="lista-miembros-enlace">
                                    <span class="lista-miembros-avatar text-bg-{{ $coloresAvatar[$persona->id % count($coloresAvatar)] }}" aria-hidden="true">{{ $iniciales($persona->nombre_completo) }}</span>
                                    <span class="lista-miembros-nombre">{{ $persona->nombre_completo }}</span>
                                    <span class="lista-miembros-fecha">{{ $persona->created_at?->diffForHumans() }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="card-footer text-center"><a href="{{ route('personas.index') }}">Ver todos los miembros</a></div>
        </div>
        @endpuedeModulo

        @puedeModulo('eventos')
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-glass-cheers me-1" aria-hidden="true"></i>Próximos eventos</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="tabla-apilable table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Inscritos</th>
                            <th>Esperado</th>
                            <th>Recaudado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proximosEventos as $evento)
                            <tr>
                                <td data-etiqueta="Nombre" data-primaria><a href="{{ route('eventos.show', $evento) }}">{{ $evento->nombre }}</a></td>
                                <td data-etiqueta="Tipo">{{ $evento->tipo }}</td>
                                <td data-etiqueta="Fecha">{{ $evento->fecha->format('Y-m-d') }}</td>
                                <td data-etiqueta="Inscritos"><x-ui.insignia color="info">{{ $evento->inscripciones->count() }}</x-ui.insignia></td>
                                <td data-etiqueta="Esperado">${{ number_format($evento->totalEsperado(), 2) }}</td>
                                <td data-etiqueta="Recaudado">${{ number_format($evento->totalRecaudado(), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.vacio mensaje="No hay eventos próximos programados." icono="fa-calendar" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                @puedeModulo('eventos', 'escritura')
                    <a href="{{ route('eventos.create') }}" class="btn btn-sm btn-primary float-start">Crear evento</a>
                @endpuedeModulo
                <a href="{{ route('eventos.index') }}" class="btn btn-sm btn-secondary float-end">Ver todos los eventos</a>
            </div>
        </div>
        @endpuedeModulo

        @if($resumenNiveles)
            @puedeModulo('niveles')
                @include('partials.tarjeta-niveles')
            @endpuedeModulo
        @endif
    </div>

    {{-- Columna lateral --}}
    <div class="col-lg-4">
        <div class="d-none d-lg-block">
            @include('partials.accesos-rapidos')
        </div>

        @puedeModulo('eventos')
            <x-ui.info-caja :relleno="true" color="warning" icono="fa-glass-cheers" texto="Eventos registrados" :valor="number_format($eventoCount)" :enlace="route('eventos.index')"
                :progreso="$porcentaje($proximosEventos->count(), max(1, $eventoCount))" :detalle="$proximosEventos->count().' próximos'" />
            <x-ui.info-caja :relleno="true" color="success" icono="fa-hand-holding-usd" texto="Recaudado en eventos" :valor="'$'.number_format($totalRecaudadoEventos, 2)"
                :progreso="$porcentaje($totalRecaudadoEventos, $totalEsperadoEventos)" :detalle="$porcentaje($totalRecaudadoEventos, $totalEsperadoEventos).'% de lo esperado'" />
        @endpuedeModulo
        @puedeModulo('usuarios')
            <x-ui.info-caja :relleno="true" color="danger" icono="fa-users" texto="Usuarios registrados" :valor="number_format($userCount)" />
        @endpuedeModulo
        @if($activeSessions !== null)
            <x-ui.info-caja :relleno="true" color="info" icono="fa-chart-line" texto="Sesiones activas" :valor="number_format($activeSessions)" />
        @endif

        @if(array_sum($porEstado) > 0)
            <div class="card mb-4">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie me-1" aria-hidden="true"></i>Miembros por estado</h3></div>
                <div class="card-body">
                    <div class="grafica-tablero" data-grafica='@json($graficaEstados)'
                        role="img" aria-label="Miembros por estado: {{ collect($porEstado)->map(fn ($n, $e) => "$e $n")->implode(', ') }}"></div>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($porEstado as $etiqueta => $total)
                        <li class="list-group-item d-flex justify-content-between">
                            {{ $etiqueta }}
                            <span class="fw-semibold">{{ number_format($total) }} <small class="text-muted">({{ $porcentaje($total, array_sum($porEstado)) }}%)</small></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($personasSinLider !== null)
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-clock me-1" aria-hidden="true"></i>Miembros sin líder</h3>
                    <div class="card-tools"><x-ui.insignia color="warning">{{ $sinLiderTotal }}</x-ui.insignia></div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($personasSinLider as $persona)
                            <li class="list-group-item d-flex align-items-center gap-2">
                                <span class="lista-miembros-avatar lista-miembros-avatar--chico text-bg-warning" aria-hidden="true">{{ $iniciales($persona->nombre_completo) }}</span>
                                <a href="{{ route('personas.show', $persona) }}" class="flex-grow-1 text-truncate">{{ $persona->nombre_completo }}</a>
                                <x-ui.estado :valor="$persona->estado" :etiqueta="$persona->estado_etiqueta" />
                            </li>
                        @empty
                            <li class="list-group-item"><x-ui.vacio mensaje="Todos los miembros tienen líder." icono="fa-check-circle" /></li>
                        @endforelse
                    </ul>
                </div>
                @if($sinLiderTotal > 5)
                    <div class="card-footer text-center"><a href="{{ route('sin-lider.index') }}">Ver los {{ $sinLiderTotal }} miembros</a></div>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
