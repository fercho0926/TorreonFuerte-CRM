@props([
    'name', 'label' => null, 'opciones' => [], 'value' => null, 'icono' => null, 'required' => false, 'ayuda' => null,
    'vacio' => 'Seleccione...', 'col' => 'col-md-6', 'buscar' => false, 'visibleSi' => null, 'requeridoSi' => null, 'id' => null, 'ocultarEtiqueta' => false,
])
@php
    $id = $id ?? \App\Support\Formulario::id($name);
    $clave = \App\Support\Formulario::clave($name);
    $tieneError = $errors->has($clave);
    $descrito = trim(($ayuda ? $id.'-ayuda ' : '').$id.'-error');
    $actual = (string) old($clave, $value);
    $lista = \App\Support\Formulario::opciones($opciones);
@endphp
<div class="{{ $col }}" @if($visibleSi) data-visible-si="{{ $visibleSi }}" @endif>
    <div class="fm-campo @if($tieneError) tiene-error @endif">
        @include('components.form._etiqueta', ['id' => $id, 'label' => $label, 'required' => $required, 'ocultar' => $ocultarEtiqueta])
        <div class="input-group">
            @if($icono)
                <span class="input-group-text" aria-hidden="true"><i class="fas {{ $icono }}"></i></span>
            @endif
            <select id="{{ $id }}" name="{{ $name }}" data-selector
                @if($buscar) data-buscar @endif
                @if($requeridoSi) data-requerido-si="{{ $requeridoSi }}" @endif
                @if($required) required aria-required="true" @endif
                @if($tieneError) aria-invalid="true" @endif
                aria-describedby="{{ $descrito }}"
                {{ $attributes->merge(['class' => 'form-control'.($tieneError ? ' is-invalid' : '')]) }}>
                @if($vacio !== null)
                    <option value="">{{ $vacio }}</option>
                @endif
                @foreach($lista as $o)
                    <option value="{{ $o['id'] }}" @if($o['apoyo']) data-apoyo="{{ $o['apoyo'] }}" @endif @selected($actual !== '' && $actual === (string) $o['id'])>{{ $o['texto'] }}</option>
                @endforeach
            </select>
        </div>
        @include('components.form._pie', ['id' => $id, 'clave' => $clave, 'ayuda' => $ayuda])
    </div>
</div>
