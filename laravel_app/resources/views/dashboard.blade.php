@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Panel de administración')

@section('content')
@if($resumenSedes)
    <div class="card card-outline card-primary">
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
                    <tr class="font-weight-bold">
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
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $personaCount }}</h3>
                <p>Personas registradas</p>
            </div>
            <div class="icon">
                <i class="fas fa-address-card"></i>
            </div>
            <a href="{{ route('personas.index') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $liderCount }}</h3>
                <p>Líderes</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <a href="{{ route('lideres.index') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $userCount }}</h3>
                <p>Usuarios registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $activeSessions ?? '—' }}</h3>
                <p>Sesiones activas</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Personas por red</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Red</th>
                    <th>Total de personas</th>
                </tr>
            </thead>
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
                        <td><span class="badge badge-info">{{ $fila->total }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">Aún no hay personas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $eventoCount }}</h3>
                <p>Eventos registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-glass-cheers"></i>
            </div>
            <a href="{{ route('eventos.index') }}" class="small-box-footer">Ver más <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>${{ number_format($totalEsperadoEventos, 2) }}</h3>
                <p>Total esperado en eventos</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>${{ number_format($totalRecaudadoEventos, 2) }}</h3>
                <p>Total recaudado en eventos</p>
            </div>
            <div class="icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Próximos eventos</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover text-nowrap">
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
                        <td><span class="badge badge-secondary">{{ $evento->inscripciones->count() }}</span></td>
                        <td>${{ number_format($evento->totalEsperado(), 2) }}</td>
                        <td>${{ number_format($evento->totalRecaudado(), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay eventos próximos programados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Bienvenido, {{ $userName }}</h3>
    </div>
    <div class="card-body">
        <p>Desde aquí podrás empezar a construir el CRM. El login ya está conectado a usuarios reales.</p>
    </div>
</div>
@endsection
