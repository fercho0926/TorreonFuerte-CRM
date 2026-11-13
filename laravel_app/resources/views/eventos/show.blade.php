@extends('layouts.admin')

@section('title', 'Detalle del evento')

@section('page-title', 'Detalle del evento')

@section('page-actions')
    @puedeModulo('eventos', 'escritura')
        <a href="{{ route('eventos.edit', $evento) }}" class="btn btn-primary">{{ __('interfaz.editar') }}</a>
    @endpuedeModulo
    <a href="{{ route('eventos.index') }}" class="btn btn-outline-secondary">{{ __('interfaz.volver') }}</a>
@endsection

@section('content')
@include('historial._pestanas', ['url' => route('eventos.show', $evento), 'pestana' => $pestana, 'otras' => [
    'asistencia' => ['Asistencia', 'fa-user-check'],
]])

@if($pestana === 'historico')
    @include('historial._lista', ['historico' => $historico])
@elseif($pestana === 'asistencia')
@puedeModulo('eventos', 'escritura')
<details class="boton-despliegue mb-4" @if($errors->has('persona_id') || $errors->has('costo_individual')) open @endif>
    <summary class="btn btn-primary">
        <i class="fas fa-user-plus me-1" aria-hidden="true"></i>Inscribir miembro
    </summary>
    <div class="mt-3">
        <x-form.layout :action="route('eventos.personas.store', $evento)" modo="incrustado" guardar="Inscribir" icono="fa-user-plus" :leyenda="false">
            <x-form.section titulo="Inscribir miembro" descripcion="Deja el costo en blanco para usar el costo general del evento." icono="fa-user-plus" id="seccion-inscribir">
                <x-form.select name="persona_id" label="Miembro" icono="fa-user" :required="true" col="col-md-7" buscar vacio="Seleccione un miembro..."
                    :opciones="\App\Support\Formulario::opciones($personasSinInscribir, fn ($p) => $p->nombre_completo, fn ($p) => $p->numero_documento ? 'Doc. '.$p->numero_documento : ($p->celular ? 'Cel. '.$p->celular : null))" />
                <x-form.money name="costo_individual" label="Costo individual (opcional)" col="col-md-5" ejemplo="Costo general"
                    :ayuda="'Costo general del evento: $'.number_format($evento->costo, 2)" />
            </x-form.section>
        </x-form.layout>
    </div>
</details>
@endpuedeModulo

<x-ui.grilla :grilla="$grillaInscripciones" :pagina="$paginaInscripciones" :estado="$estadoInscripciones" :por-pagina="$porPaginaInscripciones" :total="$totalInscripciones" titulo="Miembros inscritos" icono="fa-user-friends" />
@else
<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">{{ $evento->nombre }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">{{ __('interfaz.tipo') }}</dt>
            <dd class="col-sm-9">{{ $evento->tipo }}</dd>

            <dt class="col-sm-3">{{ __('interfaz.fecha') }}</dt>
            <dd class="col-sm-9">{{ $evento->fecha->format('Y-m-d') }}</dd>

            <dt class="col-sm-3">{{ __('interfaz.lugar') }}</dt>
            <dd class="col-sm-9">{{ $evento->lugar ?? '—' }}</dd>

            <dt class="col-sm-3">{{ __('interfaz.costo_por_miembro') }}</dt>
            <dd class="col-sm-9">${{ number_format($evento->costo, 2) }}</dd>

            <dt class="col-sm-3">{{ __('interfaz.estado') }}</dt>
            <dd class="col-sm-9">
                <x-ui.estado :valor="$evento->estado" />
            </dd>

            <dt class="col-sm-3">{{ __('interfaz.descripcion') }}</dt>
            <dd class="col-sm-9">{{ $evento->descripcion ?? '—' }}</dd>

            <dt class="col-sm-3">{{ __('interfaz.total_esperado') }}</dt>
            <dd class="col-sm-9">${{ number_format($evento->totalEsperado(), 2) }}</dd>

            <dt class="col-sm-3">{{ __('interfaz.total_recaudado') }}</dt>
            <dd class="col-sm-9">${{ number_format($evento->totalRecaudado(), 2) }}</dd>
        </dl>
    </div>
</div>

@include('partials.panel-registro-qr', ['destino' => $evento])
@endif
@endsection
