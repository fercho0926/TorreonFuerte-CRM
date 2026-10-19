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
        <div class="alert alert-danger fm-resumen" role="alert" data-resumen-servidor>
            <strong>Revisa los campos marcados:</strong>
            <ul>
                @foreach($errors->keys() as $campoConError)
                    @php($ancla = \App\Support\Formulario::id(preg_replace('/\.(\w+)/', '[$1]', $campoConError)))
                    <li><a href="#{{ $ancla }}" data-ir-a-campo>{{ $errors->first($campoConError) }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($leyenda && in_array($modo, ['completo'], true))
        <p class="fm-leyenda"><span class="fm-req-marca">*</span> Campo obligatorio</p>
    @endif

    @if(in_array($modo, ['filtro', 'linea'], true))
        <div class="row align-items-end fm-linea">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif

    <x-form.actions :guardar="$guardar" :cancelar="$cancelar" :modo="$modo" :boton="$boton" :metodo="$envio" :icono="$icono" :deshabilitar="$deshabilitar" />
</form>
