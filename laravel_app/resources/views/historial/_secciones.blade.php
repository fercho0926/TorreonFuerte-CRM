{{-- Histórico del miembro en secciones (clarificación 4). Variables: $historico (de PestanaHistorico::historicoDePersona). --}}
<x-historial.filtros :accion="$historico['url']" :filtros="$historico['filtros']" :usuarios="$historico['usuarios']" />

<div class="historial-secciones">
    @foreach($historico['secciones'] as $categoria => $seccion)
        <x-ui.tarjeta :titulo="$seccion['titulo']" :contador="$seccion['total']" id="seccion-{{ $categoria }}" class="historial-seccion">
            <x-historial.lista :entradas="$seccion['entradas']" />
        </x-ui.tarjeta>
    @endforeach
</div>
