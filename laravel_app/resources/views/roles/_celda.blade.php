{{-- Celda de la grilla de roles. Variables: $fila (Rol con usuarios_count), $columna. --}}
@php($rol = $fila)
@switch($columna)
    @case('nombre')
        <a href="{{ route('roles.show', $rol) }}" class="fw-semibold">{{ $rol->nombre }}</a>
        @break
    @case('descripcion')
        {{ $rol->descripcion ?? '—' }}
        @break
    @case('alcance')
        {{ $rol->etiquetaAlcance() }}
        @break
    @case('usuarios')
        <x-ui.insignia color="info">{{ $rol->usuarios_count }}</x-ui.insignia>
        @break
    @case('tipo')
        <x-ui.insignia :color="$rol->es_sistema ? 'secondary' : 'primary'">{{ $rol->es_sistema ? 'Sistema' : 'Personalizado' }}</x-ui.insignia>
        @break
    @case('acciones')
        <x-ui.acciones-fila :ver="route('roles.show', $rol)" :editar="route('roles.edit', $rol)" :puede-editar="!$rol->esAdministrador()" :eliminar="route('roles.destroy', $rol)" :puede-eliminar="!$rol->es_sistema" eliminar-titulo="¿Eliminar este rol?" eliminar-mensaje="Solo se puede si ningún usuario lo tiene; su historial se conserva." :nombre="$rol->nombre" />
        @break
@endswitch
