{{--
    Formulario de una acción delicada (inactivar, eliminar, quitar, trasladar, desactivar...).
    Con JavaScript pide confirmación con un diálogo propio; sin JavaScript se envía directamente
    (el servidor mantiene sus permisos y reglas). El contenido del componente es la etiqueta del botón.
--}}
@props([
    'action', 'method' => 'POST', 'titulo' => '¿Continuar?', 'mensaje' => '', 'boton' => 'Continuar',
    'peligro' => false, 'variante' => null, 'tamano' => 'sm',
])
@php
    $metodo = strtoupper($method);
    $color = $variante ?? ($peligro ? 'danger' : 'primary');
@endphp
<form method="POST" action="{{ $action }}" class="d-inline" data-confirm="1"
    data-confirm-titulo="{{ $titulo }}" data-confirm-mensaje="{{ $mensaje }}" data-confirm-boton="{{ $boton }}"
    data-confirm-peligro="{{ $peligro ? '1' : '0' }}">
    @csrf
    @if(!in_array($metodo, ['GET', 'POST'], true))
        @method($metodo)
    @endif
    <button type="submit" {{ $attributes->merge(['class' => trim('btn '.($tamano ? 'btn-'.$tamano : '').' btn-'.$color)]) }}>{{ $slot }}</button>
</form>
