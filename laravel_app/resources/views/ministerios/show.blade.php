@extends('layouts.admin')

@section('title', 'Detalle del ministerio')

@section('page-title', 'Detalle del ministerio')

@section('page-actions')
    <a href="{{ route('ministerios.edit', $ministerio) }}" class="btn btn-primary">Editar</a>
    <a href="{{ route('ministerios.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')


<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">{{ $ministerio->nombre }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Encargado</dt>
            <dd class="col-sm-9">
                @if($ministerio->encargado)
                    {{ $ministerio->encargado->nombre_completo }}
                @else
                    <span class="text-muted">Sin encargado asignado</span>
                @endif
            </dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                <x-ui.estado :valor="$ministerio->estado" />
            </dd>

            <dt class="col-sm-3">Descripción</dt>
            <dd class="col-sm-9">{{ $ministerio->descripcion ?? '—' }}</dd>
        </dl>
    </div>
</div>

<x-form.layout :action="route('ministerios.integrantes', $ministerio)" method="PUT" modo="incrustado" guardar="Guardar cambios de integrantes" icono="fa-save" :deshabilitar="$ministerio->integrantes->isEmpty()" :leyenda="false">
    <x-form.section :titulo="'Integrantes ('.$ministerio->integrantes->count().')'" descripcion="Rol y fecha de ingreso de cada integrante." icono="fa-users" id="seccion-integrantes">
        <div class="col-12">
            <x-form.lista-asistencia name="integrantes[]" :interruptor="false" vacio="Este ministerio aún no tiene integrantes. Agrégalos desde &quot;Editar&quot;."
                :columnas="['Red']"
                :filas="$ministerio->integrantes->map(fn ($p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre_completo.($p->es_lider ? ' (líder)' : ''),
                    'extra' => ['Red' => $p->redLider?->nombre ?? $p->lider?->redLider?->nombre ?? '—'],
                    'campos' => [
                        ['etiqueta' => 'Rol en el ministerio', 'name' => 'rol['.$p->id.']', 'valor' => $p->pivot->rol, 'tipo' => 'texto', 'ejemplo' => 'Ej. Coordinador, Integrante...'],
                        ['etiqueta' => 'Fecha de ingreso', 'name' => 'fecha_ingreso['.$p->id.']', 'valor' => $p->pivot->fecha_ingreso, 'tipo' => 'fecha'],
                    ],
                ])->all()" />
        </div>
    </x-form.section>
</x-form.layout>
@endsection
