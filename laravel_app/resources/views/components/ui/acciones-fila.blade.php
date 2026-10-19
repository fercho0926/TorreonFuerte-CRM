{{--
    Acciones de una fila con iconos fijos: ver, editar y eliminar (con diálogo de confirmación).
    Solo se dibuja el botón cuyo enlace se indique. "nombre" completa el texto accesible.
--}}
@props(['ver' => null, 'editar' => null, 'eliminar' => null, 'puedeEditar' => true, 'puedeEliminar' => true, 'nombre' => '', 'eliminarTitulo' => '¿Eliminar?', 'eliminarMensaje' => 'Esta acción no se puede deshacer.'])
<div class="d-inline-flex flex-wrap gap-1 align-items-center">
    @if($ver)
        <a href="{{ $ver }}" class="btn btn-sm btn-outline-secondary" title="Ver" aria-label="Ver {{ $nombre }}"><i class="fas fa-eye" aria-hidden="true"></i></a>
    @endif
    @if($editar && $puedeEditar)
        <a href="{{ $editar }}" class="btn btn-sm btn-outline-primary" title="Editar" aria-label="Editar {{ $nombre }}"><i class="fas fa-edit" aria-hidden="true"></i></a>
    @endif
    {{ $slot }}
    @if($eliminar && $puedeEliminar)
        <x-form.confirm :action="$eliminar" method="DELETE" :titulo="$eliminarTitulo" :mensaje="$eliminarMensaje" boton="Eliminar" :peligro="true" variante="outline-danger" title="Eliminar" aria-label="Eliminar {{ $nombre }}"><i class="fas fa-trash" aria-hidden="true"></i></x-form.confirm>
    @endif
</div>
