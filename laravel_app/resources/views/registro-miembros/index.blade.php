@extends('layouts.admin')

@section('title', 'Registro de miembros')

@section('page-title', 'Registro de miembros')

@section('content')
    @include('partials.panel-registro-miembros', ['sede' => $sede])
@endsection
