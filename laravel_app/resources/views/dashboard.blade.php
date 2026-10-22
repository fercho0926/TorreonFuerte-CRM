@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Panel de administración')

@section('content')
<div class="d-lg-none">
    @include('partials.accesos-rapidos')
</div>

@if($resumenSedes)
    <div class="card card-primary card-outline mb-4">
        <div class="card-header"><h3 class="card-title">Resumen por sede</h3></div>
        <div class="card-body table-responsive p-0">
            <table class="tabla-apilable table table-hover mb-0">
                <thead>
                    <tr><th>Sede</th><th>Estado</th><th>Personas</th><th>Líderes</th><th>Células</th><th>Eventos próximos</th><th>Recaudo de eventos</th></tr>
                </thead>
                <tbody>
                    @foreach($resumenSedes as $fila)
                        <tr>
                            <td data-etiqueta="Sede" data-primaria><a href="{{ route('sedes.show', $fila['sede']) }}">{{ $fila['sede']->nombre }}</a></td>
                            <td data-etiqueta="Estado">{{ $fila['sede']->activa ? 'Activa' : 'Inactiva' }}</td>
                            <td data-etiqueta="Personas">{{ $fila['personas'] }}</td>
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
                        <td data-etiqueta="Personas" data-primaria>{{ $resumenSedes->sum('personas') }}</td>
                        <td data-etiqueta="Líderes">{{ $resumenSedes->sum('lideres') }}</td>
                        <td data-etiqueta="Células">{{ $resumenSedes->sum('celulas') }}</td>
                        <td data-etiqueta="Eventos próximos">{{ $resumenSedes->sum('eventos_proximos') }}</td>
                        <td data-etiqueta="Recaudo de eventos">${{ number_format($resumenSedes->sum('recaudo'), 2) }}</td>
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
                                <td data-etiqueta="Inscritos"><x-ui.insignia color="secondary">{{ $evento->inscripciones->count() }}</x-ui.insignia></td>
                                <td data-etiqueta="Esperado">${{ number_format($evento->totalEsperado(), 2) }}</td>
                                <td data-etiqueta="Recaudado">${{ number_format($evento->totalRecaudado(), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.vacio mensaje="No hay eventos próximos programados." icono="fa-calendar" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4 d-none d-lg-block">
        @include('partials.accesos-rapidos')
    </div>
</div>

<div class="row">
    @if($personasSinLider !== null)
        <div class="col-lg-6">
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
    <div class="col-lg-{{ $personasSinLider !== null ? '6' : '12' }}">
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
</div>
@endsection
