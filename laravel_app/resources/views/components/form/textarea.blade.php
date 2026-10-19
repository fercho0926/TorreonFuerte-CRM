@props(['name', 'label' => null, 'icono' => null, 'required' => false, 'ayuda' => null, 'ejemplo' => null, 'value' => null, 'col' => 'col-12', 'rows' => 3, 'visibleSi' => null, 'id' => null, 'ocultarEtiqueta' => false])
@php
    $id = $id ?? \App\Support\Formulario::id($name);
    $clave = \App\Support\Formulario::clave($name);
    $tieneError = $errors->has($clave);
    $descrito = trim(($ayuda ? $id.'-ayuda ' : '').$id.'-error');
@endphp
<div class="{{ $col }}" @if($visibleSi) data-visible-si="{{ $visibleSi }}" @endif>
    <div class="fm-campo @if($tieneError) tiene-error @endif">
        @include('components.form._etiqueta', ['id' => $id, 'label' => $label, 'required' => $required, 'ocultar' => $ocultarEtiqueta])
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
            @if($ejemplo) placeholder="{{ $ejemplo }}" @endif
            @if($required) required aria-required="true" @endif
            @if($tieneError) aria-invalid="true" @endif
            aria-describedby="{{ $descrito }}"
            {{ $attributes->merge(['class' => 'form-control'.($tieneError ? ' is-invalid' : '')]) }}>{{ old($clave, $value) }}</textarea>
        @include('components.form._pie', ['id' => $id, 'clave' => $clave, 'ayuda' => $ayuda])
    </div>
</div>
