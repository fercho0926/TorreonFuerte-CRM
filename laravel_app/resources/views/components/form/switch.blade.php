@props(['name', 'label', 'valor' => '1', 'marcado' => false, 'ayuda' => null, 'col' => 'col-12', 'visibleSi' => null, 'id' => null, 'icono' => null])
@php
    $id = $id ?? \App\Support\Formulario::id($name);
    $clave = \App\Support\Formulario::clave($name);
    $tieneError = $errors->has($clave);
    $descrito = trim(($ayuda ? $id.'-ayuda ' : '').$id.'-error');
    // old() distingue "no se envió" de "se envió desmarcado" cuando la página vuelve con errores.
    $estaMarcado = old('_token') !== null && !is_array(old($clave)) ? (string) old($clave) === (string) $valor : (bool) $marcado;
@endphp
<div class="{{ $col }}" @if($visibleSi) data-visible-si="{{ $visibleSi }}" @endif>
    <div class="fm-campo @if($tieneError) tiene-error @endif">
        <div class="form-check form-switch fm-switch">
            <input type="checkbox" class="form-check-input @if($tieneError) is-invalid @endif" id="{{ $id }}" name="{{ $name }}" value="{{ $valor }}"
                @checked($estaMarcado) @if($tieneError) aria-invalid="true" @endif aria-describedby="{{ $descrito }}" {{ $attributes }}>
            <label class="form-check-label" for="{{ $id }}">@if($icono)<i class="fas {{ $icono }} me-1 text-muted"></i>@endif{{ $label }}</label>
        </div>
        @include('components.form._pie', ['id' => $id, 'clave' => $clave, 'ayuda' => $ayuda])
    </div>
</div>
