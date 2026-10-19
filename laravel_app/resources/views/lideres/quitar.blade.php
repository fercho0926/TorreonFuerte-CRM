@extends('layouts.admin')

@section('title', 'Quitar rol de líder')

@section('page-title', 'Quitar rol de líder')

@section('content')
@if(session('error'))
    <div class="alert alert-warning">{{ session('error') }}</div>
@endif
@if(!empty($mensajeError))
    <div class="alert alert-danger" role="alert">{{ $mensajeError }}</div>
@endif

<x-form.layout :action="route('lideres.destroy', $lider)" method="DELETE" guardar="Quitar rol de líder" boton="danger" icono="fa-user-minus" :cancelar="route('lideres.show', $lider)" :leyenda="false">
    <x-form.section :titulo="$lider->nombre_completo" descripcion="Al quitar el rol no se elimina a la persona. Esto es lo que quedaría sin líder." icono="fa-user-tie" id="seccion-impacto">
        <div class="col-12">
            <ul>
                <li><strong>{{ $impacto['personas'] }}</strong> persona(s) a su cargo</li>
                <li><strong>{{ $impacto['celulas'] }}</strong> célula(s)</li>
            </ul>
            @if($personas->isNotEmpty())
                <details class="mb-2"><summary>Ver personas</summary>
                    <ul>@foreach($personas as $p)<li>{{ $p->nombre_completo }}</li>@endforeach</ul>
                </details>
            @endif
            @if($celulas->isNotEmpty())
                <details class="mb-3"><summary>Ver células</summary>
                    <ul>@foreach($celulas as $c)<li>{{ $c->nombre }}</li>@endforeach</ul>
                </details>
            @endif
        </div>

        @if($impacto['personas'] > 0 || $impacto['celulas'] > 0)
            <x-form.select name="reasignar_a" label="Reasignar a otro líder" icono="fa-user-friends" vacio="No reasignar" col="col-12" buscar
                :opciones="$otrosLideres->map(fn ($o) => ['id' => $o->id, 'texto' => $o->nombre_completo, 'apoyo' => $o->numero_documento ? 'Doc. '.$o->numero_documento : null])->all()"
                ayuda="Las células solo pueden quedar a cargo de otro líder." />

            @if($impacto['celulas'] === 0)
                <x-form.switch name="confirmar_sin_lider" label="Confirmo que sus personas queden sin líder" icono="fa-exclamation-triangle"
                    ayuda="Aparecerán en &quot;Sin líder asignado&quot; para que las asignes a otro líder." />
            @endif
        @endif
    </x-form.section>
</x-form.layout>
@endsection
