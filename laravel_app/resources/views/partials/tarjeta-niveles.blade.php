{{-- Tarjeta «Miembros por nivel» del tablero (spec 014). Variable: $resumenNiveles (NivelesService::resumen). --}}
@php($maximo = max(1, $resumenNiveles['niveles']->max('total') ?? 0, $resumenNiveles['sin_iniciar']))
<div class="card card-warning card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-route me-1" aria-hidden="true"></i>Miembros por nivel</h3>
        <div class="card-tools"><a href="{{ route('niveles.index') }}" class="btn btn-sm btn-outline-secondary">Ver niveles</a></div>
    </div>
    <ul class="list-group list-group-flush tarjeta-niveles">
        @forelse($resumenNiveles['niveles'] as $i => $fila)
            <li class="list-group-item p-0">
                <a href="{{ route('niveles.miembros.index', ['nivel' => $fila['nivel']->id]) }}" class="tarjeta-nivel">
                    <span class="tarjeta-nivel-numero" aria-hidden="true">{{ $i + 1 }}</span>
                    <span class="tarjeta-nivel-cuerpo">
                        <span class="tarjeta-nivel-nombre">{{ $fila['nivel']->nombre }}</span>
                        <span class="tarjeta-nivel-barra" aria-hidden="true"><span style="width: {{ round($fila['total'] / $maximo * 100) }}%"></span></span>
                    </span>
                    <strong class="tarjeta-nivel-total">{{ $fila['total'] }}</strong>
                </a>
            </li>
        @empty
            <li class="list-group-item"><x-ui.vacio mensaje="El plan de niveles aún no tiene niveles." icono="fa-route" /></li>
        @endforelse
        <li class="list-group-item p-0">
            <a href="{{ route('niveles.miembros.index', ['nivel' => 'sin']) }}" class="tarjeta-nivel tarjeta-nivel--sin">
                <span class="tarjeta-nivel-numero" aria-hidden="true"><i class="fas fa-hourglass-start"></i></span>
                <span class="tarjeta-nivel-cuerpo">
                    <span class="tarjeta-nivel-nombre">Sin iniciar</span>
                    <span class="tarjeta-nivel-barra" aria-hidden="true"><span style="width: {{ round($resumenNiveles['sin_iniciar'] / $maximo * 100) }}%"></span></span>
                </span>
                <strong class="tarjeta-nivel-total">{{ $resumenNiveles['sin_iniciar'] }}</strong>
            </a>
        </li>
    </ul>
</div>
