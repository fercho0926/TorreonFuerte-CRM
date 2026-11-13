@props([
    'action' => '',
    'method' => 'POST',
    'guardar' => 'Guardar',
    'cancelar' => null,
    'modo' => 'completo',
    'sinCambios' => false,
    'boton' => 'primary',
    'icono' => null,
    'leyenda' => true,
    'deshabilitar' => false,
    'global' => false,
])
@php
    $metodo = strtoupper($method);
    $envio = $metodo === 'GET' ? 'GET' : 'POST';
    $conErrores = $errors->any() && $modo !== 'filtro';
@endphp
<form method="{{ $envio }}" action="{{ $action }}" data-form="1" data-modo="{{ $modo }}" @if($sinCambios || in_array($modo, ['filtro', 'login'], true)) data-sin-aviso @endif
    {{ $attributes->merge(['class' => 'fm-form']) }}>
    @if($envio !== 'GET')
        @csrf
        @if(!in_array($metodo, ['GET', 'POST'], true))
            @method($metodo)
        @endif
    @endif

    @if($conErrores)
        @php($mensajesVistos = [])
        <div class="alert alert-danger fm-resumen" role="alert" data-resumen-servidor>
            <strong>{{ __('interfaz.revisa_los_campos_marcados') }}</strong>
            <ul>
                @foreach($errors->keys() as $campoConError)
                    @php($mensaje = $errors->first($campoConError))
                    {{-- Dos campos relacionados (p. ej. «celular o correo») pueden fallar la misma regla con el
                         mismo texto; se muestra una sola vez, enlazando al primer campo al que aplica. --}}
                    @continue(in_array($mensaje, $mensajesVistos, true))
                    @php($mensajesVistos[] = $mensaje)
                    @php($ancla = \App\Support\Formulario::id(preg_replace('/\.(\w+)/', '[$1]', $campoConError)))
                    <li><a href="#{{ $ancla }}" data-ir-a-campo>{{ $mensaje }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($leyenda && in_array($modo, ['completo'], true))
        <p class="fm-leyenda"><span class="fm-req-marca">*</span> {{ __('interfaz.campo_obligatorio') }}</p>
    @endif

    @if(in_array($modo, ['filtro', 'linea'], true))
        <div class="row align-items-end fm-linea">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif

    <x-form.actions :guardar="$guardar" :cancelar="$cancelar" :modo="$modo" :boton="$boton" :metodo="$envio" :icono="$icono" :deshabilitar="$deshabilitar" :global="$global" />
</form>
