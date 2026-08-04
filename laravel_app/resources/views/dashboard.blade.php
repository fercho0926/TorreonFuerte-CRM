@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Panel de administración')

@section('content')
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
                <p>Líderes de red</p>
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
                <h3>{{ $activeSessions }}</h3>
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
                                <a href="{{ route('personas.index', ['red' => $fila->red_grupo]) }}">{{ $fila->red_grupo }}</a>
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
