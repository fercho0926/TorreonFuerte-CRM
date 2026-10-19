@props([
    'name' => 'personas[]', 'label' => null, 'opciones' => [], 'seleccionados' => [], 'icono' => 'fa-users', 'required' => false,
    'ayuda' => null, 'col' => 'col-12', 'visibleSi' => null, 'id' => null, 'vacio' => 'No hay opciones disponibles todavía.',
])
@php
    $id = $id ?? \App\Support\Formulario::id($name);
    $clave = \App\Support\Formulario::clave($name);
    $tieneError = $errors->has($clave) || $errors->has($clave.'.*');
    $primerError = $errors->first($clave) ?: $errors->first($clave.'.*');
    $descrito = trim(($ayuda ? $id.'-ayuda ' : '').$id.'-error');
    $marcados = array_map('strval', (array) old($clave, $seleccionados));
    $lista = \App\Support\Formulario::opciones($opciones);
@endphp
<div class="{{ $col }}" @if($visibleSi) data-visible-si="{{ $visibleSi }}" @endif>
    <div class="fm-campo @if($tieneError) tiene-error @endif">
        @include('components.form._etiqueta', ['id' => $id, 'label' => $label, 'required' => $required])
        @if(count($lista) === 0)
            <p class="text-muted mb-0">{{ $vacio }}</p>
        @else
            <select id="{{ $id }}" name="{{ $name }}" multiple data-multi
                @if($required) required aria-required="true" @endif
                @if($tieneError) aria-invalid="true" @endif
                aria-describedby="{{ $descrito }}"
                {{ $attributes->merge(['class' => 'form-control'.($tieneError ? ' is-invalid' : ''), 'size' => min(8, max(4, count($lista)))]) }}>
                @foreach($lista as $o)
                    <option value="{{ $o['id'] }}" @if($o['apoyo']) data-apoyo="{{ $o['apoyo'] }}" @endif @selected(in_array((string) $o['id'], $marcados, true))>{{ $o['texto'] }}</option>
                @endforeach
            </select>
            @if(!$ayuda)
                <small class="fm-ayuda">Escribe para buscar. Sin JavaScript, mantén Ctrl (o Cmd) para elegir varias.</small>
            @endif
        @endif
        @if($ayuda)
            <small id="{{ $id }}-ayuda" class="fm-ayuda">{{ $ayuda }}</small>
        @endif
        <div id="{{ $id }}-error" class="invalid-feedback" role="alert">{{ $primerError }}</div>
    </div>
</div>
