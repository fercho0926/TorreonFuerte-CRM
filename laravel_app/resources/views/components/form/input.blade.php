@props([
    'name', 'label' => null, 'type' => 'text', 'icono' => null, 'required' => false, 'ayuda' => null, 'ejemplo' => null,
    'value' => null, 'col' => 'col-md-6', 'mascara' => null, 'visibleSi' => null, 'requeridoSi' => null, 'id' => null, 'ocultarEtiqueta' => false,
])
@php
    $id = $id ?? \App\Support\Formulario::id($name);
    $clave = \App\Support\Formulario::clave($name);
    $tieneError = $errors->has($clave);
    $descrito = trim(($ayuda ? $id.'-ayuda ' : '').$id.'-error');
    $modo = ['tel' => 'tel', 'number' => 'numeric', 'email' => 'email'][$type] ?? null;
    $auto = ['password' => 'new-password', 'email' => 'email', 'tel' => 'tel'][$type] ?? 'off';
    $actual = $type === 'password' ? '' : old($clave, $value);
@endphp
<div class="{{ $col }}" @if($visibleSi) data-visible-si="{{ $visibleSi }}" @endif>
    <div class="fm-campo @if($tieneError) tiene-error @endif">
        @include('components.form._etiqueta', ['id' => $id, 'label' => $label, 'required' => $required, 'ocultar' => $ocultarEtiqueta])
        <div class="input-group">
            @if($icono)
                <span class="input-group-text" aria-hidden="true"><i class="fas {{ $icono }}"></i></span>
            @endif
            <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" value="{{ $actual }}"
                @if($modo) inputmode="{{ $modo }}" @endif autocomplete="{{ $auto }}"
                @if($ejemplo) placeholder="{{ $ejemplo }}" @endif
                @if($mascara) data-mascara="{{ $mascara }}" @endif
                @if($requeridoSi) data-requerido-si="{{ $requeridoSi }}" @endif
                @if($required) required aria-required="true" @endif
                @if($tieneError) aria-invalid="true" @endif
                aria-describedby="{{ $descrito }}"
                {{ $attributes->merge(['class' => 'form-control'.($tieneError ? ' is-invalid' : '')]) }}>
        </div>
        @include('components.form._pie', ['id' => $id, 'clave' => $clave, 'ayuda' => $ayuda])
    </div>
</div>
