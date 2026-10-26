{{-- Elección única entre pocas opciones, como tarjetas tocables (44 px, letra de 16 px).
     opciones: [valor => ['texto' => ..., 'apoyo' => ...]] --}}
@props(['name', 'label' => null, 'opciones' => [], 'value' => null, 'required' => false, 'ayuda' => null, 'col' => 'col-12', 'id' => null])
@php
    $id = $id ?? \App\Support\Formulario::id($name);
    $clave = \App\Support\Formulario::clave($name);
    $tieneError = $errors->has($clave);
    $descrito = trim(($ayuda ? $id.'-ayuda ' : '').$id.'-error');
    $actual = (string) old($clave, $value);
@endphp
<div class="{{ $col }}">
    <fieldset class="fm-campo fm-radio @if($tieneError) tiene-error @endif" aria-describedby="{{ $descrito }}">
        @if($label)
            <legend @class(['fm-etiqueta', 'fm-req' => $required])>{{ $label }}</legend>
        @endif
        <div class="list-group">
            @foreach($opciones as $valor => $opcion)
                @php($idOpcion = $id.'-'.$loop->index)
                <label for="{{ $idOpcion }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 fm-radio__opcion">
                    <input type="radio" class="form-check-input flex-shrink-0 m-0 @if($tieneError) is-invalid @endif" id="{{ $idOpcion }}" name="{{ $name }}" value="{{ $valor }}"
                        @checked($actual === (string) $valor) @if($required) required @endif {{ $attributes }}>
                    <span>
                        <span class="d-block">{{ $opcion['texto'] }}</span>
                        @if(!empty($opcion['apoyo']))
                            <small class="text-muted">{{ $opcion['apoyo'] }}</small>
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
        @include('components.form._pie', ['id' => $id, 'clave' => $clave, 'ayuda' => $ayuda])
    </fieldset>
</div>
