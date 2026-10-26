{{--
    Pestañas de una ficha con «Histórico» (spec 015). Enlaces reales con ?pestana=…: funcionan sin JavaScript y se
    pueden abrir directamente. Variables: $url (dirección de la ficha), $pestana (activa), $otras (opcional:
    clave → [etiqueta, icono]) para fichas que ya tenían más pestañas, como Sedes.
--}}
@php
    $otras ??= [];
    $todas = ['datos' => ['Datos', null]] + $otras + ['historico' => ['Histórico', 'fa-history']];
@endphp
<ul class="nav nav-tabs mb-3 historial-pestanas">
    @foreach($todas as $clave => [$etiqueta, $icono])
        @php($activa = $pestana === $clave)
        <li class="nav-item">
            <a class="nav-link @if($activa) active @endif" @if($activa) aria-current="page" @endif
               href="{{ $clave === 'datos' ? $url : $url.(str_contains($url, '?') ? '&' : '?').'pestana='.$clave }}">
                @if($icono)<i class="fas {{ $icono }}" aria-hidden="true"></i> @endif{{ $etiqueta }}
            </a>
        </li>
    @endforeach
</ul>
