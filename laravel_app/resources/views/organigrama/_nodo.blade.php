<li class="organigrama-nodo" data-tipo="{{ $nodo['tipo'] }}">
    @if ($nodo['totalHijos'] > 0)
        <details>
            <summary class="organigrama-tarjeta">
                <span class="organigrama-cuerpo">
                    @include('organigrama._nombre', ['nodo' => $nodo])
                    <small class="organigrama-rol">{{ $nodo['rol'] }}</small>
                    @include('organigrama._pastores', ['nodo' => $nodo])
                </span>
                <span class="organigrama-contador badge text-bg-secondary">{{ $nodo['etiquetaHijos'] }}</span>
            </summary>
            <ul class="organigrama-hijos">
                @foreach ($nodo['hijos'] as $hijo)
                    @include('organigrama._nodo', ['nodo' => $hijo])
                @endforeach
            </ul>
        </details>
    @else
        <div class="organigrama-tarjeta">
            <span class="organigrama-cuerpo">
                @include('organigrama._nombre', ['nodo' => $nodo])
                <small class="organigrama-rol">{{ $nodo['rol'] }}</small>
                @include('organigrama._pastores', ['nodo' => $nodo])
            </span>
            @if (in_array($nodo['tipo'], ['sede', 'lider', 'sin_lider'], true))
                <span class="organigrama-contador badge text-bg-light">{{ $nodo['etiquetaHijos'] }}</span>
            @endif
        </div>
    @endif
</li>
