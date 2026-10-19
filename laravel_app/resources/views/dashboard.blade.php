@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Panel de administración')

@section('content')
@if($resumenSedes)
    <div class="card card-primary card-outline mb-4">
        <div class="card-header"><h3 class="card-title">Resumen por sede</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Sede</th><th>Estado</th><th>Personas</th><th>Líderes</th><th>Células</th><th>Eventos próximos</th><th>Recaudo de eventos</th></tr>
                </thead>
                <tbody>
                    @foreach($resumenSedes as $fila)
                        <tr>
                            <td><a href="{{ route('sedes.show', $fila['sede']) }}">{{ $fila['sede']->nombre }}</a></td>
                            <td>{{ $fila['sede']->activa ? 'Activa' : 'Inactiva' }}</td>
                            <td>{{ $fila['personas'] }}</td>
                            <td>{{ $fila['lideres'] }}</td>
                            <td>{{ $fila['celulas'] }}</td>
                            <td>{{ $fila['eventos_proximos'] }}</td>
                            <td>${{ number_format($fila['recaudo'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="2">Total global (todas las sedes)</td>
                        <td>{{ $resumenSedes->sum('personas') }}</td>
                        <td>{{ $resumenSedes->sum('lideres') }}</td>
                        <td>{{ $resumenSedes->sum('celulas') }}</td>
                        <td>{{ $resumenSedes->sum('eventos_proximos') }}</td>
                        <td>${{ number_format($resumenSedes->sum('recaudo'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <p class="text-muted small">Las tarjetas de abajo suman <strong>todas las sedes</strong>. Elige una sede activa en la barra superior para ver una sola.</p>
@endif
<div class="row">
    <div class="col-lg-3 col-6"><x-ui.caja :valor="$personaCount" texto="Personas registradas" icono="fa-address-card" color="info" :enlace="route('personas.index')" /></div>
    <div class="col-lg-3 col-6"><x-ui.caja :valor="$liderCount" texto="Líderes" icono="fa-user-tie" color="primary" :enlace="route('lideres.index')" /></div>
    <div class="col-lg-3 col-6"><x-ui.caja :valor="$userCount" texto="Usuarios registrados" icono="fa-users" color="success" /></div>
    @if($personasSinLider !== null)
        <div class="col-lg-3 col-6"><x-ui.caja :valor="$sinLiderTotal" texto="Sin líder asignado" icono="fa-user-clock" color="warning" :enlace="route('sin-lider.index')" /></div>
    @endif
</div>

<div class="row">
    <div class="col-lg-3 col-6"><x-ui.caja :valor="$eventoCount" texto="Eventos registrados" icono="fa-glass-cheers" color="secondary" :enlace="route('eventos.index')" /></div>
    <div class="col-lg-3 col-6"><x-ui.caja :valor="'$'.number_format($totalEsperadoEventos, 2)" texto="Total esperado en eventos" icono="fa-file-invoice-dollar" color="info" /></div>
    <div class="col-lg-3 col-6"><x-ui.caja :valor="'$'.number_format($totalRecaudadoEventos, 2)" texto="Total recaudado en eventos" icono="fa-hand-holding-usd" color="success" /></div>
    @if($activeSessions !== null)
        <div class="col-lg-3 col-6"><x-ui.caja :valor="$activeSessions" texto="Sesiones activas" icono="fa-chart-line" color="primary" /></div>
    @endif
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-info card-outline mb-4">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-glass-cheers me-1" aria-hidden="true"></i>Próximos eventos</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle mb-0">
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
                                <td><a href="{{ route('eventos.show', $evento) }}">{{ $evento->nombre }}</a></td>
                                <td>{{ $evento->tipo }}</td>
                                <td>{{ $evento->fecha->format('Y-m-d') }}</td>
                                <td><x-ui.insignia color="secondary">{{ $evento->inscripciones->count() }}</x-ui.insignia></td>
                                <td>${{ number_format($evento->totalEsperado(), 2) }}</td>
                                <td>${{ number_format($evento->totalRecaudado(), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.vacio mensaje="No hay eventos próximos programados." icono="fa-calendar" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-primary card-outline mb-4">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-bolt me-1" aria-hidden="true"></i>Accesos rápidos</h3></div>
            <div class="card-body d-grid gap-2">
                @puede('crear')
                    <a href="{{ route('personas.create') }}" class="btn btn-primary"><i class="fas fa-user-plus me-1" aria-hidden="true"></i>Registrar persona</a>
                @endpuede
                @puede('designarLider')
                    <a href="{{ route('lideres.designar') }}" class="btn btn-outline-primary"><i class="fas fa-user-tie me-1" aria-hidden="true"></i>Designar líder</a>
                @endpuede
                <a href="{{ route('eventos.create') }}" class="btn btn-outline-primary"><i class="fas fa-glass-cheers me-1" aria-hidden="true"></i>Crear evento</a>
                <a href="{{ route('celulas.index') }}" class="btn btn-outline-secondary"><i class="fas fa-people-arrows me-1" aria-hidden="true"></i>Ver células</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @if($personasSinLider !== null)
        <div class="col-lg-4">
            <div class="card card-warning card-outline mb-4">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-user-clock me-1" aria-hidden="true"></i>Personas sin líder</h3></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($personasSinLider as $persona)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('personas.show', $persona) }}">{{ $persona->nombre_completo }}</a>
                                <x-ui.estado :valor="$persona->estado" :etiqueta="$persona->estado_etiqueta" />
                            </li>
                        @empty
                            <li class="list-group-item"><x-ui.vacio mensaje="Todas las personas tienen líder." icono="fa-check-circle" /></li>
                        @endforelse
                    </ul>
                </div>
                @if($sinLiderTotal > 5)
                    <div class="card-footer text-center"><a href="{{ route('sin-lider.index') }}">Ver las {{ $sinLiderTotal }} personas</a></div>
                @endif
            </div>
        </div>
    @endif
    <div class="col-lg-{{ $personasSinLider !== null ? '4' : '6' }}">
        <div class="card card-success card-outline mb-4">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-plus me-1" aria-hidden="true"></i>Personas nuevas</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($personasNuevas as $persona)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('personas.show', $persona) }}">{{ $persona->nombre_completo }}</a>
                            <small class="text-muted">{{ $persona->created_at?->diffForHumans() }}</small>
                        </li>
                    @empty
                        <li class="list-group-item"><x-ui.vacio mensaje="Aún no hay personas registradas." icono="fa-address-card" /></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-{{ $personasSinLider !== null ? '4' : '6' }}">
        <div class="card card-secondary card-outline mb-4">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-sitemap me-1" aria-hidden="true"></i>Personas por red</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Red</th><th class="text-end">Personas</th></tr></thead>
                    <tbody>
                        @forelse($personasPorRed as $fila)
                            <tr>
                                <td>
                                    @if($fila->red_grupo)
                                        <a href="{{ route('personas.index', ['red_id' => $fila->red_id]) }}">{{ $fila->red_grupo }}</a>@if($fila->sede_nombre && $resumenSedes) <small class="text-muted">({{ $fila->sede_nombre }})</small>@endif
                                    @else
                                        <span class="text-muted">Sin red asignada</span>
                                    @endif
                                </td>
                                <td class="text-end"><x-ui.insignia color="info">{{ $fila->total }}</x-ui.insignia></td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><x-ui.vacio mensaje="Aún no hay personas registradas." icono="fa-address-card" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
