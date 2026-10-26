@extends('layouts.admin')

@section('title', 'Detalle de miembro')

@section('page-title', 'Detalle de miembro')

@section('page-actions')
    @puede('editar', $persona)
        <a href="{{ route('personas.edit', $persona) }}" class="btn btn-primary">Editar</a>
    @endpuede
    @puede('inactivar', $persona)
        @if($persona->estado === 'inactivo')
            <x-form.accion :action="route('personas.reactivar', $persona)" boton="Reactivar" variante="success" icono="fa-undo" />
        @else
            <x-form.confirm :action="route('personas.inactivar', $persona)" method="POST" titulo="¿Inactivar a este miembro?" mensaje="Su historial se conserva." boton="Inactivar" :peligro="false" variante="outline-secondary" tamano="">Inactivar</x-form.confirm>
        @endif
    @endpuede
    @puede('trasladar', \App\Models\Sede::class)
        @if(!$persona->trasladada_a_sede_id)
            <a href="{{ route('traslados.create', $persona) }}" class="btn btn-outline-primary">Trasladar a otra sede</a>
        @endif
    @endpuede
    @puede('eliminar', $persona)
        <x-form.confirm :action="route('personas.destroy', $persona)" method="DELETE" titulo="¿Eliminar definitivamente?" mensaje="Solo es posible si no tiene historial." boton="Eliminar" :peligro="true" variante="outline-danger" tamano="">Eliminar</x-form.confirm>
    @else
        @if(\App\Http\Controllers\Controller::usuarioDeLaPeticion()?->esAdmin() && $persona->cuenta()->exists())
            {{-- Solo en pantallas anchas: en el celular no debe empujar «Llamar» y «WhatsApp» hacia abajo. --}}
            <span class="text-muted small align-self-center d-none d-lg-inline"><i class="fas fa-user-lock me-1" aria-hidden="true"></i>Tiene cuenta de usuario; elimina primero la cuenta.</span>
        @endif
    @endpuede
    <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">Volver</a>
@endsection

@section('content')
@php
    // Cifras del widget: en la pestaña «Datos» las relaciones ya vienen cargadas; en «Histórico» llegan como *_count.
    $cuenta = function (string $relacion) use ($persona) {
        $clave = \Illuminate\Support\Str::snake($relacion).'_count';

        return array_key_exists($clave, $persona->getAttributes()) ? (int) $persona->getAttribute($clave) : $persona->{$relacion}->count();
    };
    $celulasTotal = $cuenta('celulasLideradas') + $cuenta('celulasCoLideradas') + $cuenta('celulas');
    $porcentaje = fn ($parte, $total) => $total > 0 ? (int) min(100, round($parte / $total * 100)) : 0;
@endphp

{{-- Tarjeta de perfil al estilo «User widgets» de AdminLTE 4: cabecera de color, avatar y tres cifras. --}}
<div class="card card-widget widget-user perfil-widget shadow-sm mb-3">
    <div class="widget-user-header text-bg-{{ $persona->estado === 'inactivo' ? 'secondary' : 'primary' }}">
        <h3 class="widget-user-username">{{ $persona->nombre_completo }}</h3>
        <h5 class="widget-user-desc">
            <x-ui.estado :valor="$persona->estado" :etiqueta="$persona->estado_etiqueta" class="border border-light-subtle" />
            @if($persona->es_lider)
                <x-ui.estado valor="lider" class="border border-light-subtle" />
            @endif
        </h5>
        @if($persona->celular_limpio)
            <div class="perfil-contacto">@include('partials.acciones-contacto', ['persona' => $persona])</div>
        @endif
    </div>
    <div class="widget-user-image">
        <x-ui.avatar :miembro="$persona" tamano="lg" />
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-4">
                <div class="description-block">
                    <h5 class="description-header">{{ $celulasTotal }}</h5>
                    <span class="description-text">Células</span>
                </div>
            </div>
            <div class="col-4">
                <div class="description-block">
                    <h5 class="description-header">{{ $cuenta('encuentros') }}</h5>
                    <span class="description-text">Encuentros</span>
                </div>
            </div>
            <div class="col-4">
                <div class="description-block">
                    <h5 class="description-header">{{ $cuenta('inscripcionesEventos') }}</h5>
                    <span class="description-text">Eventos</span>
                </div>
            </div>
        </div>
    </div>
</div>

@include('historial._pestanas', ['url' => route('personas.show', $persona), 'pestana' => $pestana])

@if($pestana === 'historico')
    @include('historial._secciones', ['historico' => $historico])
@else
@php
    $inscripciones = $persona->inscripcionesEventos->sortByDesc(fn ($i) => $i->evento->fecha);
    $costoEventos = $inscripciones->sum(fn ($i) => $i->costoAplicable());
    $pagadoEventos = $inscripciones->sum(fn ($i) => $i->totalPagado());
    $asistidos = $persona->encuentros->filter(fn ($e) => $e->pivot->asistio)->count();
    $ministeriosTotal = $persona->ministeriosLiderados->count() + $persona->ministerios->count();
    $camino = \App\Support\Permisos::modulo('niveles') ? app(\App\Services\CaminoService::class)->camino($persona) : null;
@endphp

{{-- Fila de info-boxes («Dashboard v2») --}}
<div class="row">
    @if($camino)
        <div class="col-12 col-sm-6 col-md-3">
            <x-ui.info-caja icono="fa-route" color="warning" texto="Niveles completados" :valor="$camino['completados'].' de '.$camino['total']"
                :progreso="$porcentaje($camino['completados'], $camino['total'])" :detalle="$camino['actual']?->nombre" />
        </div>
    @endif
    <div class="col-12 col-sm-6 col-md-3">
        <x-ui.info-caja icono="fa-users" color="info" texto="Miembros a su cargo" :valor="$persona->miembros->count()" />
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <x-ui.info-caja icono="fa-church" color="success" texto="Encuentros asistidos" :valor="$asistidos.' de '.$persona->encuentros->count()"
            :progreso="$porcentaje($asistidos, $persona->encuentros->count())" />
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <x-ui.info-caja icono="fa-hands-helping" color="danger" texto="Ministerios" :valor="$ministeriosTotal" />
    </div>
</div>

<div class="row">
    {{-- Columna principal --}}
    <div class="col-lg-8">
        @puedeModulo('niveles')
            <div class="card mb-4">
                <div class="card-body">
                    <x-niveles.camino :miembro="$persona" />
                </div>
            </div>
        @endpuedeModulo

        @if($persona->miembros->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users me-1" aria-hidden="true"></i>Miembros a su cargo</h3>
                    <div class="card-tools"><x-ui.insignia color="info">{{ $persona->miembros->count() }} miembros</x-ui.insignia></div>
                </div>
                <div class="card-body p-0">
                    <ul class="lista-miembros">
                        @foreach($persona->miembros as $miembro)
                            <li>
                                <a href="{{ route('personas.show', $miembro) }}" class="lista-miembros-enlace">
                                    <x-ui.avatar :miembro="$miembro" />
                                    <span class="lista-miembros-nombre">{{ $miembro->nombre_completo }}</span>
                                    <span class="lista-miembros-fecha">{{ $miembro->celular ?? 'Sin celular' }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-glass-cheers me-1" aria-hidden="true"></i>Eventos</h3>
                <div class="card-tools"><x-ui.insignia color="secondary">{{ $inscripciones->count() }}</x-ui.insignia></div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="tabla-apilable table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Fecha</th>
                            <th>Costo</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inscripciones as $inscripcion)
                            <tr>
                                <td data-etiqueta="Nombre" data-primaria><a href="{{ route('eventos.show', $inscripcion->evento) }}">{{ $inscripcion->evento->nombre }}</a></td>
                                <td data-etiqueta="Fecha">{{ $inscripcion->evento->fecha->translatedFormat('j \d\e F \d\e Y') }}</td>
                                <td data-etiqueta="Costo">${{ number_format($inscripcion->costoAplicable(), 2) }}</td>
                                <td data-etiqueta="Pagado">${{ number_format($inscripcion->totalPagado(), 2) }}</td>
                                <td data-etiqueta="Saldo">${{ number_format($inscripcion->saldoPendiente(), 2) }}</td>
                                <td data-etiqueta="Estado"><x-ui.estado :valor="$inscripcion->estaPagado() ? 'pagado' : 'pendiente'" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.vacio mensaje="No está inscrito en eventos." icono="fa-calendar" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-church me-1" aria-hidden="true"></i>Encuentros</h3>
                <div class="card-tools"><x-ui.insignia color="secondary">{{ $persona->encuentros->count() }}</x-ui.insignia></div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="tabla-apilable table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Fecha</th>
                            <th>Asistió</th>
                            <th>Valor pagado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($persona->encuentros as $encuentro)
                            <tr>
                                <td data-etiqueta="Nombre" data-primaria><a href="{{ route('encuentros.show', $encuentro) }}">{{ $encuentro->nombre }}</a></td>
                                <td data-etiqueta="Fecha">{{ $encuentro->fecha_realizacion->translatedFormat('j \d\e F \d\e Y') }}</td>
                                <td data-etiqueta="Asistió"><x-ui.estado :valor="$encuentro->pivot->asistio ? 'si' : 'no'" /></td>
                                <td data-etiqueta="Valor pagado">{{ $encuentro->pivot->valor_pagado !== null ? '$'.number_format($encuentro->pivot->valor_pagado, 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-ui.vacio mensaje="No ha participado en encuentros." icono="fa-church" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Columna lateral --}}
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card me-1" aria-hidden="true"></i>Datos personales</h3></div>
            <ul class="list-group list-group-flush perfil-datos">
                <li class="list-group-item">
                    <i class="fas fa-birthday-cake" aria-hidden="true"></i><span class="perfil-datos-etiqueta">Fecha de nacimiento</span>
                    <span>{{ $persona->fecha_nacimiento ? $persona->fecha_nacimiento_legible.' ('.$persona->edad.' años)' : '—' }}</span>
                </li>
                <li class="list-group-item"><i class="fas fa-mobile-alt" aria-hidden="true"></i><span class="perfil-datos-etiqueta">Celular</span><span>{{ $persona->celular ?? '—' }}</span></li>
                <li class="list-group-item"><i class="fas fa-envelope" aria-hidden="true"></i><span class="perfil-datos-etiqueta">Correo</span><span class="text-break">{{ $persona->email ?? '—' }}</span></li>
                <li class="list-group-item"><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span class="perfil-datos-etiqueta">Dirección</span><span>{{ $persona->direccion ?? '—' }}</span></li>
                <li class="list-group-item"><i class="fas fa-city" aria-hidden="true"></i><span class="perfil-datos-etiqueta">Barrio / Ciudad</span><span>{{ $persona->barrio ?? '—' }} / {{ $persona->ciudad ?? '—' }}</span></li>
                <li class="list-group-item">
                    <i class="fas fa-user-tie" aria-hidden="true"></i><span class="perfil-datos-etiqueta">Líder</span>
                    @if($persona->lider)
                        <a href="{{ route('personas.show', $persona->lider) }}" class="d-inline-flex align-items-center gap-2"><x-ui.avatar :miembro="$persona->lider" tamano="sm" />{{ $persona->lider->nombre_completo }}</a>
                    @else
                        <span class="text-muted">Sin líder asignado</span>
                    @endif
                </li>
            </ul>
        </div>

        @if($inscripciones->isNotEmpty())
            <x-ui.info-caja :relleno="true" color="success" icono="fa-hand-holding-usd" texto="Pagado en eventos" :valor="'$'.number_format($pagadoEventos, 2)"
                :progreso="$porcentaje($pagadoEventos, $costoEventos)" :detalle="'Saldo: $'.number_format(max(0, $costoEventos - $pagadoEventos), 2)" />
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-people-arrows me-1" aria-hidden="true"></i>Células</h3>
                <div class="card-tools"><x-ui.insignia color="success">{{ $celulasTotal }}</x-ui.insignia></div>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($persona->celulasLideradas as $celula)
                    <li class="list-group-item perfil-item">
                        <span class="perfil-item-icono text-bg-primary" aria-hidden="true"><i class="fas fa-people-arrows"></i></span>
                        <span class="perfil-item-cuerpo"><a href="{{ route('celulas.show', $celula) }}" class="fw-semibold">{{ $celula->nombre }}</a><small class="d-block text-muted">{{ $celula->miembros->count() }} miembros</small></span>
                        <x-ui.estado valor="lider" />
                    </li>
                @endforeach
                @foreach($persona->celulasCoLideradas as $celula)
                    <li class="list-group-item perfil-item">
                        <span class="perfil-item-icono text-bg-info" aria-hidden="true"><i class="fas fa-people-arrows"></i></span>
                        <span class="perfil-item-cuerpo"><a href="{{ route('celulas.show', $celula) }}" class="fw-semibold">{{ $celula->nombre }}</a><small class="d-block text-muted">{{ $celula->miembros->count() }} miembros</small></span>
                        <x-ui.insignia color="secondary">Co-líder</x-ui.insignia>
                    </li>
                @endforeach
                @foreach($persona->celulas as $celula)
                    <li class="list-group-item perfil-item">
                        <span class="perfil-item-icono text-bg-success" aria-hidden="true"><i class="fas fa-people-arrows"></i></span>
                        <span class="perfil-item-cuerpo"><a href="{{ route('celulas.show', $celula) }}" class="fw-semibold">{{ $celula->nombre }}</a><small class="d-block text-muted">Líder: {{ $celula->lider->nombre_completo }}</small></span>
                        <x-ui.insignia color="light" class="border">Miembro</x-ui.insignia>
                    </li>
                @endforeach
                @if($celulasTotal === 0)
                    <li class="list-group-item"><x-ui.vacio mensaje="No pertenece a ninguna célula todavía." icono="fa-people-arrows" /></li>
                @endif
            </ul>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-hands-helping me-1" aria-hidden="true"></i>Ministerios</h3>
                <div class="card-tools"><x-ui.insignia color="danger">{{ $ministeriosTotal }}</x-ui.insignia></div>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($persona->ministeriosLiderados as $ministerio)
                    <li class="list-group-item perfil-item">
                        <span class="perfil-item-icono text-bg-danger" aria-hidden="true"><i class="fas fa-hands-helping"></i></span>
                        <span class="perfil-item-cuerpo"><a href="{{ route('ministerios.show', $ministerio) }}" class="fw-semibold">{{ $ministerio->nombre }}</a></span>
                        <x-ui.insignia color="primary">Encargado</x-ui.insignia>
                    </li>
                @endforeach
                @foreach($persona->ministerios as $ministerio)
                    <li class="list-group-item perfil-item">
                        <span class="perfil-item-icono text-bg-warning" aria-hidden="true"><i class="fas fa-hands-helping"></i></span>
                        <span class="perfil-item-cuerpo">
                            <a href="{{ route('ministerios.show', $ministerio) }}" class="fw-semibold">{{ $ministerio->nombre }}</a>
                            @if($ministerio->pivot->fecha_ingreso)
                                <small class="d-block text-muted">Desde {{ \Illuminate\Support\Carbon::parse($ministerio->pivot->fecha_ingreso)->translatedFormat('j \d\e F \d\e Y') }}</small>
                            @endif
                        </span>
                        <x-ui.insignia color="light" class="border">{{ $ministerio->pivot->rol ?? 'Integrante' }}</x-ui.insignia>
                    </li>
                @endforeach
                @if($ministeriosTotal === 0)
                    <li class="list-group-item"><x-ui.vacio mensaje="No pertenece a ningún ministerio todavía." icono="fa-hands-helping" /></li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endif
@endsection
