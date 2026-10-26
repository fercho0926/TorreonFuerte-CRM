{{-- Histórico de una ficha con lista única y filtro por acción. Variables: $historico (de PestanaHistorico::historicoDeRegistro). --}}
<x-historial.filtros :accion="$historico['url']" :filtros="$historico['filtros']" :usuarios="$historico['usuarios']" :acciones="$historico['acciones']" />

<x-ui.tarjeta titulo="Histórico" icono="fa-history" :contador="$historico['entradas']->total()">
    <x-historial.lista :entradas="$historico['entradas']" vacio="Sin cambios registrados." />
</x-ui.tarjeta>
