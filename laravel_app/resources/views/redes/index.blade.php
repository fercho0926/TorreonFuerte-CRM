@extends('layouts.admin')

@section('title', 'Redes')

@section('page-title', 'Redes')

@section('content')
<div class="callout callout-info py-2 px-3 mb-3">
    <small><strong>Red:</strong> agrupación temática de líderes (por ejemplo, Jóvenes o Matrimonios). Si dos redes quedaron escritas de forma distinta, usa "Fusionar".</small>
</div>

<x-form.layout :action="route('redes.store')" modo="incrustado" guardar="Crear red" icono="fa-plus" :leyenda="false">
    <x-form.section titulo="Nueva red" descripcion="Los nombres no se repiten dentro de la sede (sin distinguir mayúsculas ni tildes)." icono="fa-sitemap" id="seccion-nueva-red">
        <x-form.input name="nombre" label="Nombre de la red" icono="fa-sitemap" :required="true" ejemplo="Ej. Jóvenes" col="col-md-8" maxlength="255" />
    </x-form.section>
</x-form.layout>

<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Redes ({{ $redes->count() }})</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr><th>Nombre</th><th>Líderes</th>@todasLasSedes<th>Sede</th>@endtodasLasSedes<th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse($redes as $red)
                    <tr>
                        <td>
                            <x-form.layout :action="route('redes.update', $red)" method="PUT" modo="linea" guardar="Guardar" boton="warning" :leyenda="false" :id="'red-'.$red->id">
                                <x-form.input name="nombre" :id="'fm-red-nombre-'.$red->id" label="Nombre" :ocultarEtiqueta="true" :value="$red->nombre" :required="true" col="col-auto" maxlength="255" icono="fa-sitemap" />
                                <x-form.switch name="activa" :id="'fm-red-activa-'.$red->id" label="Activa" :marcado="$red->activa" col="col-auto" />
                            </x-form.layout>
                        </td>
                        <td><x-ui.insignia color="info">{{ $red->lideres_count }}</x-ui.insignia></td>
                        @todasLasSedes<td>{{ $red->sede?->nombre ?? '—' }}</td>@endtodasLasSedes
                        <td class="text-nowrap">
                            @if($redes->count() > 1)
                                <x-form.layout :action="route('redes.fusionar', $red)" modo="linea" guardar="Fusionar" boton="outline-secondary" :leyenda="false" :id="'fusionar-'.$red->id"
                                    data-confirm="1" data-confirm-titulo="¿Fusionar esta red con otra?"
                                    :data-confirm-mensaje="'Los líderes de «'.$red->nombre.'» pasarán a la red elegida y esta red se eliminará.'" data-confirm-boton="Fusionar" data-confirm-peligro="0">
                                    <x-form.select name="destino_id" :id="'fm-destino-'.$red->id" label="Fusionar con" :ocultarEtiqueta="true" vacio="Fusionar con..." col="col-auto" buscar
                                        :opciones="$redes->where('id', '!=', $red->id)->map(fn ($o) => ['id' => $o->id, 'texto' => $o->nombre])->values()->all()" />
                                </x-form.layout>
                            @endif
                            @if($red->lideres_count === 0)
                                <x-form.confirm :action="route('redes.destroy', $red)" method="DELETE" titulo="¿Eliminar esta red?" :mensaje="'Se eliminará la red «'.$red->nombre.'». No tiene líderes.'" boton="Eliminar" :peligro="true" variante="outline-danger">Eliminar</x-form.confirm>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-ui.vacio mensaje="Aún no hay redes." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
