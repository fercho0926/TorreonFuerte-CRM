@props([
    'name', 'label' => null, 'value' => null, 'icono' => 'fa-calendar-alt', 'required' => false, 'ayuda' => null,
    'max' => null, 'min' => null, 'atajos' => 'hoy,ayer', 'col' => 'col-md-6', 'visibleSi' => null, 'requeridoSi' => null, 'id' => null, 'ocultarEtiqueta' => false,
])
@php
    $id = $id ?? \App\Support\Formulario::id($name);
    $clave = \App\Support\Formulario::clave($name);
    $tieneError = $errors->has($clave);
    $descrito = trim(($ayuda ? $id.'-ayuda ' : '').$id.'-error');
    $actual = old($clave, $value);
    if ($actual instanceof \DateTimeInterface) {
        $actual = $actual->format('Y-m-d');
    }
@endphp
<div class="{{ $col }}" @if($visibleSi) data-visible-si="{{ $visibleSi }}" @endif>
    <div class="fm-campo @if($tieneError) tiene-error @endif">
        @include('components.form._etiqueta', ['id' => $id, 'label' => $label, 'required' => $required, 'ocultar' => $ocultarEtiqueta])
        <div class="input-group">
            @if($icono)
                <span class="input-group-text" aria-hidden="true"><i class="fas {{ $icono }}"></i></span>
            @endif
            <input type="date" id="{{ $id }}" name="{{ $name }}" value="{{ $actual }}" data-fecha
                @if($max) max="{{ $max === 'hoy' ? now()->toDateString() : $max }}" data-max="{{ $max === 'hoy' ? 'hoy' : $max }}" @endif
                @if($min) min="{{ $min }}" data-min="{{ $min }}" @endif
                @if($atajos) data-atajos="{{ $atajos }}" @endif
                @if($requeridoSi) data-requerido-si="{{ $requeridoSi }}" @endif
                @if($required) required aria-required="true" @endif
                @if($tieneError) aria-invalid="true" @endif
                aria-describedby="{{ $descrito }}"
                {{ $attributes->merge(['class' => 'form-control'.($tieneError ? ' is-invalid' : '')]) }}>
        </div>
        @include('components.form._pie', ['id' => $id, 'clave' => $clave, 'ayuda' => $ayuda])
    </div>
</div>
