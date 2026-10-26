@extends('layouts.admin')

@section('title', 'Niveles')

@section('page-title', 'Niveles de formación')

@puedeModulo('niveles', 'escritura')
    @section('page-actions')
        <a href="{{ route('niveles.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1" aria-hidden="true"></i>Crear nivel</a>
    @endsection
@endpuedeModulo

@section('content')
<section aria-labelledby="quien-titulo" class="mb-4">
    <h3 id="quien-titulo" class="h5 mb-2">Quién está en cada nivel</h3>
    <p class="text-muted small mb-2">Cada tarjeta cuenta a los miembros activos cuyo nivel actual es ese (el más alto que completaron). Toca una para ver quiénes son.</p>
    <div class="row g-2 niveles-tarjetas">
        @foreach($resumen['niveles'] as $i => $fila)
            <div class="col-6 col-lg-3">
                <a href="{{ route('niveles.miembros.index', ['nivel' => $fila['nivel']->id]) }}" class="nivel-tarjeta">
                    <span class="nivel-tarjeta-numero">{{ $i + 1 }}</span>
                    <span class="nivel-tarjeta-nombre">{{ $fila['nivel']->nombre }}</span>
                    <span class="nivel-tarjeta-total">{{ $fila['total'] }} {{ $fila['total'] === 1 ? 'miembro' : 'miembros' }}</span>
                </a>
            </div>
        @endforeach
        <div class="col-6 col-lg-3">
            <a href="{{ route('niveles.miembros.index', ['nivel' => 'sin']) }}" class="nivel-tarjeta nivel-tarjeta--sin">
                <span class="nivel-tarjeta-numero"><i class="fas fa-hourglass-start" aria-hidden="true"></i></span>
                <span class="nivel-tarjeta-nombre">Sin iniciar</span>
                <span class="nivel-tarjeta-total">{{ $resumen['sin_iniciar'] }} {{ $resumen['sin_iniciar'] === 1 ? 'miembro' : 'miembros' }}</span>
            </a>
        </div>
    </div>
</section>

<section aria-labelledby="plan-titulo" class="card mb-4">
    <div class="card-header"><h3 id="plan-titulo" class="card-title"><i class="fas fa-route me-1" aria-hidden="true"></i>Plan de niveles</h3></div>
    <ul class="list-group list-group-flush plan-niveles">
        @forelse($plan as $i => $nivel)
            <li class="list-group-item plan-nivel @if(!$nivel->activo) plan-nivel--inactivo @endif">
                <span class="plan-nivel-numero" aria-hidden="true">{{ $nivel->posicion }}</span>
                <div class="plan-nivel-texto">
                    <strong>{{ $nivel->nombre }}</strong>
                    @unless($nivel->activo)<x-ui.estado valor="inactivo" />@endunless
                    @if($nivel->descripcion)<small class="d-block text-muted">{{ $nivel->descripcion }}</small>@endif
                </div>
                @puedeModulo('niveles', 'escritura')
                    <div class="plan-nivel-acciones">
                        <x-form.accion :action="route('niveles.subir', $nivel)" boton="Subir" icono="fa-arrow-up" variante="outline-secondary" :disabled="$loop->first" tamano="sm" />
                        <x-form.accion :action="route('niveles.bajar', $nivel)" boton="Bajar" icono="fa-arrow-down" variante="outline-secondary" :disabled="$loop->last" tamano="sm" />
                        <a href="{{ route('niveles.edit', $nivel) }}" class="btn btn-outline-primary btn-sm" title="Editar" aria-label="Editar {{ $nivel->nombre }}"><i class="fas fa-edit" aria-hidden="true"></i></a>
                        @if($nivel->activo)
                            <x-form.confirm :action="route('niveles.desactivar', $nivel)" titulo="¿Desactivar este nivel?" mensaje="Dejará de aparecer en el camino y en los conteos. Los avances ya registrados se conservan y vuelven si lo reactivas." boton="Desactivar" variante="outline-secondary" tamano="sm">Desactivar</x-form.confirm>
                        @else
                            <x-form.accion :action="route('niveles.reactivar', $nivel)" boton="Reactivar" icono="fa-undo" variante="outline-success" tamano="sm" />
                        @endif
                        <x-form.confirm :action="route('niveles.destroy', $nivel)" method="DELETE" titulo="¿Eliminar este nivel?" mensaje="Solo se puede si no tiene miembros ni encuentros asociados." boton="Eliminar" :peligro="true" variante="outline-danger" tamano="sm" title="Eliminar" :aria-label="'Eliminar '.$nivel->nombre"><i class="fas fa-trash" aria-hidden="true"></i></x-form.confirm>
                    </div>
                @endpuedeModulo
            </li>
        @empty
            <li class="list-group-item"><x-ui.vacio mensaje="Aún no hay niveles en el plan." icono="fa-route" /></li>
        @endforelse
    </ul>
</section>

<section aria-labelledby="historial-titulo" class="card mb-4">
    <div class="card-header"><h3 id="historial-titulo" class="card-title"><i class="fas fa-clock-rotate-left me-1" aria-hidden="true"></i>Historial del plan</h3></div>
    <ul class="list-group list-group-flush">
        @forelse($cambios as $cambio)
            <li class="list-group-item small">
                <strong>{{ $cambio->etiqueta }}</strong> · {{ $cambio->sujeto_nombre }}
                <span class="text-muted d-block">{{ $cambio->actor_nombre }} · {{ $cambio->created_at?->locale('es')->translatedFormat('j M Y H:i') }}</span>
            </li>
        @empty
            <li class="list-group-item small text-muted">Aún no hay cambios registrados.</li>
        @endforelse
    </ul>
</section>
@endsection
