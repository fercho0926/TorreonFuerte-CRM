@extends('layouts.admin')

@section('title', 'Detalle del encuentro')

@section('page-title', 'Detalle del encuentro')

@section('page-actions')
    <a href="{{ route('encuentros.edit', $encuentro) }}" class="btn btn-primary">Editar</a>
    <a href="{{ route('encuentros.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')


<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">{{ $encuentro->nombre }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Fecha de realización</dt>
            <dd class="col-sm-9">{{ $encuentro->fecha_realizacion->format('Y-m-d') }}</dd>

            <dt class="col-sm-3">Lugar</dt>
            <dd class="col-sm-9">{{ $encuentro->lugar ?? '—' }}</dd>

            <dt class="col-sm-3">Valor</dt>
            <dd class="col-sm-9">${{ number_format($encuentro->valor, 2) }}</dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                <x-ui.estado :valor="$encuentro->estado" />
            </dd>

            <dt class="col-sm-3">Notas</dt>
            <dd class="col-sm-9">{{ $encuentro->notas ?? '—' }}</dd>

            <dt class="col-sm-3">Total recaudado</dt>
            <dd class="col-sm-9">${{ number_format($encuentro->totalRecaudado(), 2) }}</dd>
        </dl>
    </div>
</div>

<x-form.layout :action="route('encuentros.asistencia', $encuentro)" method="PUT" modo="incrustado" guardar="Guardar asistencia y pagos" icono="fa-save" :deshabilitar="$encuentro->personas->isEmpty()" :leyenda="false">
    <x-form.section :titulo="'Personas participantes ('.$encuentro->personas->count().')'" descripcion="Marca quién asistió y cuánto pagó cada persona." icono="fa-users" id="seccion-asistencia">
        <div class="col-12">
            <x-form.lista-asistencia name="asistio[]" etiqueta="Asistió" vacio="Este encuentro aún no tiene personas asignadas."
                :columnas="['Documento', 'Celular']"
                :filas="$encuentro->personas->map(fn ($p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre_completo,
                    'extra' => [
                        'Documento' => trim(($p->tipo_documento ?? '').' '.($p->numero_documento ?? '')) ?: '—',
                        'Celular' => $p->celular ?? '—',
                    ],
                    'campos' => [['etiqueta' => 'Valor pagado', 'name' => 'valor_pagado['.$p->id.']', 'valor' => $p->pivot->valor_pagado, 'tipo' => 'monto']],
                ])->all()"
                :marcados="$encuentro->personas->filter(fn ($p) => $p->pivot->asistio)->pluck('id')->all()" />
        </div>
    </x-form.section>
</x-form.layout>

@include('partials.panel-registro-qr', ['destino' => $encuentro])
@endsection
