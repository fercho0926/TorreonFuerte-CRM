@if ($nodo['puedeVerFicha'])
    <a href="{{ route('personas.show', $nodo['id']) }}" class="organigrama-nombre" title="{{ $nodo['titulo'] }}">{{ $nodo['titulo'] }}</a>
@else
    <span class="organigrama-nombre" title="{{ $nodo['titulo'] }}">{{ $nodo['titulo'] }}</span>
@endif
