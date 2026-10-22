@extends('layouts.admin')

@section('title', 'Detalle del evento')

@section('page-title', 'Detalle del evento')

@section('page-actions')
    <a href="{{ route('eventos.edit', $evento) }}" class="btn btn-primary">Editar</a>
    <a href="{{ route('eventos.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')


<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">{{ $evento->nombre }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Tipo</dt>
            <dd class="col-sm-9">{{ $evento->tipo }}</dd>

            <dt class="col-sm-3">Fecha</dt>
            <dd class="col-sm-9">{{ $evento->fecha->format('Y-m-d') }}</dd>

            <dt class="col-sm-3">Lugar</dt>
            <dd class="col-sm-9">{{ $evento->lugar ?? '—' }}</dd>

            <dt class="col-sm-3">Costo por persona</dt>
            <dd class="col-sm-9">${{ number_format($evento->costo, 2) }}</dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                <x-ui.estado :valor="$evento->estado" />
            </dd>

            <dt class="col-sm-3">Descripción</dt>
            <dd class="col-sm-9">{{ $evento->descripcion ?? '—' }}</dd>

            <dt class="col-sm-3">Total esperado</dt>
            <dd class="col-sm-9">${{ number_format($evento->totalEsperado(), 2) }}</dd>

            <dt class="col-sm-3">Total recaudado</dt>
            <dd class="col-sm-9">${{ number_format($evento->totalRecaudado(), 2) }}</dd>
        </dl>
    </div>
</div>

<x-form.layout :action="route('eventos.personas.store', $evento)" modo="incrustado" guardar="Inscribir" icono="fa-user-plus" :leyenda="false">
    <x-form.section titulo="Inscribir persona" descripcion="Deja el costo en blanco para usar el costo general del evento." icono="fa-user-plus" id="seccion-inscribir">
        <x-form.select name="persona_id" label="Persona" icono="fa-user" :required="true" col="col-md-7" buscar vacio="Seleccione una persona..."
            :opciones="\App\Support\Formulario::opciones($personasSinInscribir, fn ($p) => $p->nombre_completo, fn ($p) => $p->numero_documento ? 'Doc. '.$p->numero_documento : ($p->celular ? 'Cel. '.$p->celular : null))" />
        <x-form.money name="costo_individual" label="Costo individual (opcional)" col="col-md-5" ejemplo="Costo general"
            :ayuda="'Costo general del evento: $'.number_format($evento->costo, 2)" />
    </x-form.section>
</x-form.layout>

@php($inscritasPorQr = $evento->registrosQr()->whereNotNull('persona_id')->pluck('persona_id')->all())
<h3 class="mb-3">Personas inscritas ({{ $evento->inscripciones->count() }})</h3>

@forelse($evento->inscripciones as $inscripcion)
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                {{ $inscripcion->persona->nombre_completo }}
                @if(in_array($inscripcion->persona_id, $inscritasPorQr, true))
                    <x-ui.insignia color="primary" title="Se registró con el QR">QR</x-ui.insignia>
                @endif
                @if($inscripcion->estaPagado())
                    <x-ui.estado valor="pagado" />
                @else
                    <x-ui.estado valor="pendiente" />
                @endif
            </h3>
            <div class="card-tools">
                @if($inscripcion->pagos->isEmpty())
                    <x-form.confirm :action="route('eventos.personas.destroy', [$evento, $inscripcion])" method="DELETE" titulo="¿Quitar a esta persona del evento?"
                        :mensaje="$inscripcion->persona->nombre_completo.' dejará de estar inscrita en este evento.'" boton="Quitar" :peligro="true">Quitar</x-form.confirm>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Costo aplicable:</strong> ${{ number_format($inscripcion->costoAplicable(), 2) }}</div>
                <div class="col-md-4"><strong>Total pagado:</strong> ${{ number_format($inscripcion->totalPagado(), 2) }}</div>
                <div class="col-md-4"><strong>Saldo pendiente:</strong> ${{ number_format($inscripcion->saldoPendiente(), 2) }}</div>
            </div>

            @if($inscripcion->pagos->isNotEmpty())
                <table class="tabla-apilable table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Notas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inscripcion->pagos as $pago)
                            <tr>
                                <td data-etiqueta="Fecha" data-primaria>{{ $pago->fecha_pago->format('Y-m-d') }}</td>
                                <td data-etiqueta="Monto">${{ number_format($pago->monto, 2) }}</td>
                                <td data-etiqueta="Notas">{{ $pago->notas ?? '—' }}</td>
                                <td data-etiqueta="">
                                    <x-form.confirm :action="route('eventos.pagos.destroy', [$evento, $inscripcion, $pago])" method="DELETE" titulo="¿Eliminar este abono?"
                                        :mensaje="'Se borrará el abono de $'.number_format($pago->monto, 2).' registrado el '.$pago->fecha_pago->format('d/m/Y').'.'" boton="Eliminar" :peligro="true" variante="outline-danger">Eliminar</x-form.confirm>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if(!$inscripcion->estaPagado())
                <x-form.layout :action="route('eventos.pagos.store', [$evento, $inscripcion])" modo="incrustado" guardar="Registrar abono" icono="fa-plus-circle" boton="success" :leyenda="false" :id="'abono-'.$inscripcion->id">
                    <x-form.section titulo="Registrar abono" icono="fa-money-bill-wave" :id="'seccion-abono-'.$inscripcion->id">
                        <x-form.money name="monto" :id="'fm-monto-'.$inscripcion->id" label="Monto del abono" :required="true" col="col-md-4" />
                        <x-form.date name="fecha_pago" :id="'fm-fecha-pago-'.$inscripcion->id" label="Fecha" :required="true" :value="now()->format('Y-m-d')" max="hoy" col="col-md-3" />
                        <x-form.input name="notas" :id="'fm-notas-'.$inscripcion->id" label="Notas (opcional)" icono="fa-sticky-note" col="col-md-5" maxlength="255" />
                    </x-form.section>
                </x-form.layout>
            @endif
        </div>
    </div>
@empty
    <div class="alert alert-info">Este evento aún no tiene personas inscritas.</div>
@endforelse

@include('partials.panel-registro-qr', ['destino' => $evento])
@endsection
