{{--
    Matriz de permisos de un rol: una tarjeta por módulo con tres opciones (Sin acceso, Lectura, Escritura).
    Props: niveles (módulo → nivel actual), soloLectura (solo muestra), titulo. Cada módulo es un grupo de radios
    nativos (`permisos[módulo]`), así funciona sin JavaScript; en celular las tarjetas se apilan.
--}}
@props(['niveles' => [], 'soloLectura' => false, 'titulo' => 'Permisos por módulo'])
@php
    $catalogo = \App\Support\Modulos::catalogo();
    $etiquetas = \App\Support\Niveles::ETIQUETAS;
    $colores = [0 => 'secondary', 1 => 'info', 2 => 'success'];
@endphp
<div class="card card-outline card-primary mb-4" data-matriz-permisos>
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title mb-0">{{ $titulo }}</h3>
        @unless($soloLectura)
            <div class="d-flex flex-wrap gap-2" data-matriz-atajos hidden>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-matriz-todo="1">Todo en lectura</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-matriz-todo="0">Todo sin acceso</button>
            </div>
        @endunless
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($catalogo as $modulo => $datos)
                @php
                    $actual = (int) old('permisos.'.$modulo, $niveles[$modulo] ?? 0);
                    $maximo = $datos['solo_lectura'] ? 1 : 2;
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <fieldset class="border rounded p-3 h-100" @if($soloLectura) disabled @endif>
                        <legend class="float-none w-auto fs-6 fw-semibold px-1 mb-1">
                            <i class="fas {{ $datos['icono'] }} me-1" aria-hidden="true"></i>{{ $datos['etiqueta'] }}
                        </legend>
                        <p class="small text-muted mb-2">{{ $datos['descripcion'] }}</p>
                        @if($soloLectura)
                            <x-ui.insignia :color="$colores[$actual]">{{ $etiquetas[$actual] }}</x-ui.insignia>
                        @else
                            <div class="btn-group w-100" role="group" aria-label="Nivel en {{ $datos['etiqueta'] }}">
                                @foreach($etiquetas as $nivel => $etiqueta)
                                    @continue($nivel > $maximo)
                                    <input type="radio" class="btn-check" name="permisos[{{ $modulo }}]" id="permiso-{{ $modulo }}-{{ $nivel }}" value="{{ $nivel }}" @checked($actual === $nivel)>
                                    <label class="btn btn-outline-primary d-flex align-items-center justify-content-center" style="min-height: 44px;" for="permiso-{{ $modulo }}-{{ $nivel }}">{{ $etiqueta }}</label>
                                @endforeach
                            </div>
                        @endif
                    </fieldset>
                </div>
            @endforeach
        </div>
        @error('permisos')<div class="text-danger small mt-2" role="alert">{{ $message }}</div>@enderror
    </div>
</div>
