{{--
    Acción de un solo botón sin confirmación (reactivar, cambiar de sede activa...).
    El contenido del componente son campos ocultos opcionales; el texto del botón va en la prop "boton".
--}}
@props(['action', 'method' => 'POST', 'boton' => 'Continuar', 'variante' => 'primary', 'tamano' => '', 'icono' => null])
@php($metodo = strtoupper($method))
<form method="POST" action="{{ $action }}" class="d-inline" data-form="1" data-modo="accion" data-sin-aviso>
    @csrf
    @if(!in_array($metodo, ['GET', 'POST'], true))
        @method($metodo)
    @endif
    {{ $slot }}
    <button type="submit" data-boton-principal {{ $attributes->merge(['class' => trim('btn '.($tamano ? 'btn-'.$tamano : '').' btn-'.$variante)]) }}>@if($icono)<i class="fas {{ $icono }} me-1"></i>@endif{{ $boton }}</button>
</form>
