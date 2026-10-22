{{--
    Lista de personas con un interruptor y/o campos por fila (asistencia, pagos, integrantes...).
    Una sola estructura de datos: tabla en computador; en teléfono el CSS convierte cada fila en una
    tarjeta (sin duplicar los controles que se envían).
    Props:
      filas   = [['id' =>, 'nombre' =>, 'apoyo' =>, 'extra' => ['Etiqueta' => 'valor'],
                  'campos' => [['etiqueta' =>, 'name' =>, 'valor' =>, 'tipo' => 'texto|monto|fecha', 'ejemplo' =>]]]]
      name    = nombre del arreglo del interruptor (ej. asistio[])
      marcados, etiqueta, columnas (encabezados de 'extra'), interruptor (false = solo campos)
--}}
@props(['filas' => [], 'name' => 'asistio[]', 'marcados' => [], 'etiqueta' => 'Asistió', 'vacio' => 'No hay personas para mostrar.', 'columnas' => [], 'interruptor' => true])
@php
    $marcadosTexto = array_map('strval', (array) old(\App\Support\Formulario::clave($name), $marcados));
    $prefijo = \App\Support\Formulario::id($name);
    $encabezadosCampos = collect($filas)->first()['campos'] ?? [];
@endphp
<div class="fm-lista" data-lista-asistencia>
    <p class="fm-lista-total" aria-live="polite"><span data-lista-total>{{ count($filas) }}</span> persona(s)</p>
    @if(count($filas) === 0)
        <p class="text-muted">{{ $vacio }}</p>
    @else
        <div class="table-responsive-md">
            <table class="table table-hover fm-lista__tabla tabla-apilable mb-0">
                <thead>
                    <tr>
                        <th scope="col">Persona</th>
                        @foreach($columnas as $col)
                            <th scope="col">{{ $col }}</th>
                        @endforeach
                        @foreach($encabezadosCampos as $campo)
                            <th scope="col">{{ $campo['etiqueta'] }}</th>
                        @endforeach
                        @if($interruptor)
                            <th scope="col" class="text-center">{{ $etiqueta }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($filas as $fila)
                        @php($idCheck = $prefijo.'-'.$fila['id'])
                        <tr class="fm-lista__fila">
                            <td data-etiqueta="Persona">
                                <span class="fm-persona-card__nombre">{{ $fila['nombre'] }}</span>
                                @if(!empty($fila['apoyo']))
                                    <span class="fm-persona-card__apoyo d-block">{{ $fila['apoyo'] }}</span>
                                @endif
                            </td>
                            @foreach($columnas as $col)
                                <td data-etiqueta="{{ $col }}">{{ $fila['extra'][$col] ?? '—' }}</td>
                            @endforeach
                            @foreach(($fila['campos'] ?? []) as $campo)
                                @php($idCampo = \App\Support\Formulario::id($campo['name']))
                                @php($tipo = $campo['tipo'] ?? 'texto')
                                <td data-etiqueta="{{ $campo['etiqueta'] }}" class="fm-lista__campo">
                                    <label for="{{ $idCampo }}" class="visually-hidden">{{ $campo['etiqueta'] }}: {{ $fila['nombre'] }}</label>
                                    <input id="{{ $idCampo }}" name="{{ $campo['name'] }}" class="form-control form-control-sm"
                                        value="{{ old(\App\Support\Formulario::clave($campo['name']), $campo['valor'] ?? '') }}"
                                        @if($tipo === 'monto') type="text" inputmode="decimal" autocomplete="off" data-monto placeholder="0" @endif
                                        @if($tipo === 'fecha') type="date" data-fecha data-atajos="hoy,ayer" @endif
                                        @if($tipo === 'texto') type="text" @endif
                                        @if(!empty($campo['ejemplo'])) placeholder="{{ $campo['ejemplo'] }}" @endif>
                                </td>
                            @endforeach
                            @if($interruptor)
                                <td class="text-md-center fm-lista__marca" data-etiqueta="{{ $etiqueta }}">
                                    <div class="form-check form-switch d-inline-block">
                                        <input type="checkbox" class="form-check-input" id="{{ $idCheck }}" name="{{ $name }}" value="{{ $fila['id'] }}"
                                            @checked(in_array((string) $fila['id'], $marcadosTexto, true))>
                                        <label class="form-check-label" for="{{ $idCheck }}"><span class="visually-hidden">{{ $etiqueta }}: {{ $fila['nombre'] }}</span></label>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
