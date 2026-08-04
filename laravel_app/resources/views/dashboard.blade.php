@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Panel de administración')

@section('content')
<div class="row">
    <div class="col-lg-4 col-12">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $userCount }}</h3>
                <p>Usuarios registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $activeSessions }}</h3>
                <p>Sesiones activas</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>CRM</h3>
                <p>Panel administrativo</p>
            </div>
            <div class="icon">
                <i class="fas fa-tachometer-alt"></i>
            </div>
        </div>
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
