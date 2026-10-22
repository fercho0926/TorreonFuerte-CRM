@extends('layouts.admin')

@section('title', 'Detalle de la célula')

@section('page-title', 'Detalle de la célula')

@section('page-actions')
    @if($puedeGestionarEstructura)
        <a href="{{ route('celulas.edit', $celula) }}" class="btn btn-primary">Editar</a>
    @endif
    <a href="{{ route('celulas.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')


<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title">{{ $celula->nombre }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Líder</dt>
            <dd class="col-sm-9">{{ $celula->lider->nombre_completo }}</dd>

            <dt class="col-sm-3">Co-líder</dt>
            <dd class="col-sm-9">{{ $celula->coLider?->nombre_completo ?? '—' }}</dd>

            <dt class="col-sm-3">Sede</dt>
            <dd class="col-sm-9">{{ $celula->sede ?? '—' }}</dd>

            <dt class="col-sm-3">Reunión</dt>
            <dd class="col-sm-9">
                {{ $celula->dia_reunion ?? 'Sin definir' }}
                @if($celula->hora_reunion) a las {{ substr($celula->hora_reunion, 0, 5) }} @endif
                @if($celula->lugar_reunion) — {{ $celula->lugar_reunion }} @endif
            </dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">
                <x-ui.estado :valor="$celula->estado" />
            </dd>
        </dl>
    </div>
</div>

@if($puedeAdministrar)
    <x-form.layout :action="route('celulas.miembros.store', $celula)" modo="incrustado" guardar="Agregar integrantes" icono="fa-user-plus" :leyenda="false">
        <x-form.section titulo="Agregar integrantes" descripcion="Escribe para buscar por nombre, apellido, documento o celular. Si la persona aún no existe, créala desde aquí: quedará «En célula»." icono="fa-user-plus" id="seccion-agregar">
            <x-form.multiselect name="personas[]" label="Personas" icono="fa-users" :required="true" :opciones="[]" :seleccionados="[]"
                :fuente="route('personas.buscar')" :filtros="['activas' => 1, 'excluir' => $celula->miembros->pluck('id')->implode(',')]" :crear="route('personas.rapida')" />
        </x-form.section>
    </x-form.layout>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Miembros ({{ $celula->miembros->count() }})</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="tabla-apilable table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nombre completo</th>
                    <th>Documento</th>
                    <th>Celular</th>
                    <th>Estado</th>
                    @if($puedeAdministrar)
                        <th>Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($celula->miembros as $miembro)
                    <tr>
                        <td data-etiqueta="Nombre completo" data-primaria>{{ $miembro->nombre_completo }}</td>
                        <td data-etiqueta="Documento">{{ $miembro->tipo_documento }} {{ $miembro->numero_documento }}</td>
                        <td data-etiqueta="Celular">{{ $miembro->celular ?? '—' }}</td>
                        <td data-etiqueta="Estado"><x-ui.estado :valor="$miembro->estado" :etiqueta="$miembro->estado_etiqueta" /></td>
                        @if($puedeAdministrar)
                            <td data-etiqueta="Acciones">
                                <x-form.confirm :action="route('celulas.miembros.destroy', [$celula, $miembro])" method="DELETE" titulo="¿Quitar a esta persona de la célula?"
                                    :mensaje="$miembro->nombre_completo.' dejará de pertenecer a esta célula; su historial se conserva.'" boton="Quitar" :peligro="true">Quitar</x-form.confirm>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $puedeAdministrar ? 5 : 4 }}"><x-ui.vacio mensaje="Esta célula aún no tiene integrantes." icono="fa-users" /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($puedeAdministrar)
    <x-form.layout :action="route('celulas.reuniones.store', $celula)" modo="incrustado" guardar="Guardar reunión" icono="fa-save" :deshabilitar="$celula->miembros->isEmpty()" :leyenda="false">
        <x-form.section titulo="Registrar reunión" descripcion="Fecha, tema y quiénes asistieron." icono="fa-calendar-check" id="seccion-reunion">
            <x-form.date name="fecha" label="Fecha" :required="true" :value="now()->format('Y-m-d')" col="col-md-4" max="hoy" />
            <x-form.input name="tema" label="Tema" icono="fa-comment-dots" ejemplo="Ej. Estudio de Efesios 1" col="col-md-8" maxlength="255" />
            <x-form.textarea name="notas" label="Notas" :rows="2" />
            <div class="col-12">
                <label class="fm-etiqueta">Asistencia</label>
                <x-form.lista-asistencia name="asistio[]" etiqueta="Asistió" vacio="Agrega miembros a la célula antes de registrar asistencia."
                    :filas="$celula->miembros->map(fn ($m) => ['id' => $m->id, 'nombre' => $m->nombre_completo, 'apoyo' => $m->celular])->all()"
                    :marcados="$celula->miembros->pluck('id')->all()" />
            </div>
        </x-form.section>
    </x-form.layout>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Historial de reuniones ({{ $celula->reuniones->count() }})</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="tabla-apilable table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tema</th>
                    <th>Asistencia</th>
                    @if($puedeAdministrar)
                        <th></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($celula->reuniones as $reunion)
                    <tr>
                        <td data-etiqueta="Fecha" data-primaria>{{ $reunion->fecha->format('Y-m-d') }}</td>
                        <td data-etiqueta="Tema">{{ $reunion->tema ?? '—' }}</td>
                        <td data-etiqueta="Asistencia"><x-ui.insignia color="info">{{ $reunion->totalAsistio() }} / {{ $reunion->asistentes->count() }}</x-ui.insignia></td>
                        @if($puedeAdministrar)
                            <td data-etiqueta="">
                                <x-form.confirm :action="route('celulas.reuniones.destroy', [$celula, $reunion])" method="DELETE" titulo="¿Eliminar esta reunión?"
                                    mensaje="Se borrará la reunión y su registro de asistencia." boton="Eliminar" :peligro="true" variante="outline-danger" tamano="sm">Eliminar</x-form.confirm>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $puedeAdministrar ? 4 : 3 }}" class="text-center">Aún no hay reuniones registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.panel-registro-qr', ['destino' => $celula])
@endsection
