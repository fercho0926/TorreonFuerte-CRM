@props([
    'name', 'label' => null, 'value' => null, 'icono' => 'fa-dollar-sign', 'required' => false, 'ayuda' => null,
    'ejemplo' => '0', 'col' => 'col-md-6', 'visibleSi' => null, 'id' => null, 'ocultarEtiqueta' => false,
])
@php
    $id = $id ?? \App\Support\Formulario::id($name);
    $clave = \App\Support\Formulario::clave($name);
    $tieneError = $errors->has($clave);
    $descrito = trim(($ayuda ? $id.'-ayuda ' : '').$id.'-error');
    $actual = old($clave, $value);
@endphp
<div class="{{ $col }}" @if($visibleSi) data-visible-si="{{ $visibleSi }}" @endif>
    <div class="fm-campo @if($tieneError) tiene-error @endif">
        @include('components.form._etiqueta', ['id' => $id, 'label' => $label, 'required' => $required, 'ocultar' => $ocultarEtiqueta])
        <div class="input-group">
            @if($icono)
                <span class="input-group-text" aria-hidden="true"><i class="fas {{ $icono }}"></i></span>
            @endif
            <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $actual }}" inputmode="decimal" autocomplete="off" data-monto
                @if($ejemplo) placeholder="{{ $ejemplo }}" @endif
                @if($required) required aria-required="true" @endif
                @if($tieneError) aria-invalid="true" @endif
                aria-describedby="{{ $descrito }}"
                {{ $attributes->merge(['class' => 'form-control'.($tieneError ? ' is-invalid' : '')]) }}>
        </div>
        @include('components.form._pie', ['id' => $id, 'clave' => $clave, 'ayuda' => $ayuda])
    </div>
</div>
