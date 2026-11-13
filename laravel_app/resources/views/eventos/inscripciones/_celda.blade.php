{{--
    Celda de la grilla «Miembros inscritos» de un evento (spec 019). Variables: $fila (EventoPersona), $columna, $grilla.
    El panel «Registrar abono»/«Ver abonos» es un <details> nativo: funciona sin JavaScript (se despliega con el
    comportamiento propio del navegador); con CSS se ve como un panel flotante (ver resources/css/grilla.css).
--}}
@php($inscripcion = $fila)
@switch($columna)
    @case('nombre')
        {{ $inscripcion->persona->nombre_completo }}
        @if($grilla->seRegistroPorQr($inscripcion))
            <x-ui.insignia color="primary" title="Se registró con el QR">QR</x-ui.insignia>
        @endif
        @break

    @case('costo')
        ${{ number_format($inscripcion->costoAplicable(), 2) }}
        @break

    @case('pagado')
        ${{ number_format($inscripcion->totalPagado(), 2) }}
        @break

    @case('saldo')
        ${{ number_format($inscripcion->saldoPendiente(), 2) }}
        @break

    @case('estado')
        <x-ui.estado :valor="$inscripcion->estaPagado() ? 'pagado' : 'pendiente'" />
        @break

    @case('acciones')
        @php($puedeEscribir = \App\Support\Permisos::modulo('eventos', 'escritura'))
        @php($puedeAbonar = $puedeEscribir && !$inscripcion->estaPagado())
        <div class="d-flex gap-2 justify-content-end flex-wrap">
            @if($inscripcion->pagos->isNotEmpty() || $puedeAbonar)
                @php($esteFormularioFallo = $errors->any() && (int) old('inscripcion_id') === $inscripcion->id)
                <details class="grilla-panel" @if($esteFormularioFallo) open @endif>
                    <summary class="btn btn-sm {{ $puedeAbonar ? 'btn-success' : 'btn-outline-secondary' }}">
                        <i class="fas fa-money-bill-wave me-1" aria-hidden="true"></i>{{ $puedeAbonar ? 'Registrar abono' : 'Ver abonos' }}
                        @if($inscripcion->pagos->isNotEmpty())<x-ui.insignia color="light">{{ $inscripcion->pagos->count() }}</x-ui.insignia>@endif
                    </summary>
                    <div class="grilla-panel-contenido">
                        <h3 class="h6">{{ $inscripcion->persona->nombre_completo }}</h3>
                        <div class="small mb-2">
                            <div><strong>Costo aplicable:</strong> ${{ number_format($inscripcion->costoAplicable(), 2) }}</div>
                            <div><strong>Total pagado:</strong> ${{ number_format($inscripcion->totalPagado(), 2) }}</div>
                            <div><strong>Saldo pendiente:</strong> ${{ number_format($inscripcion->saldoPendiente(), 2) }}</div>
                        </div>

                        @if($inscripcion->pagos->isNotEmpty())
                            <table class="tabla-apilable table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Notas</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inscripcion->pagos as $pago)
                                        <tr>
                                            <td data-etiqueta="Fecha" data-primaria>{{ $pago->fecha_pago->format('Y-m-d') }}</td>
                                            <td data-etiqueta="Monto">${{ number_format($pago->monto, 2) }}</td>
                                            <td data-etiqueta="Notas">{{ $pago->notas ?? '—' }}</td>
                                            <td data-etiqueta="">
                                                @puedeModulo('eventos', 'escritura')
                                                <x-form.confirm :action="route('eventos.pagos.destroy', [$inscripcion->evento_id, $inscripcion, $pago])" method="DELETE" titulo="¿Eliminar este abono?"
                                                    :mensaje="'Se borrará el abono de $'.number_format($pago->monto, 2).' registrado el '.$pago->fecha_pago->format('d/m/Y').'.'" boton="Eliminar" :peligro="true" variante="outline-danger">Eliminar</x-form.confirm>
                                                @endpuedeModulo
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        @if($puedeAbonar)
                            <x-form.layout :action="route('eventos.pagos.store', [$inscripcion->evento_id, $inscripcion])" modo="incrustado" guardar="Registrar abono" icono="fa-plus-circle" boton="success" :leyenda="false" :id="'abono-'.$inscripcion->id">
                                <input type="hidden" name="inscripcion_id" value="{{ $inscripcion->id }}">
                                <x-form.section titulo="Registrar abono" icono="fa-money-bill-wave" :id="'seccion-abono-'.$inscripcion->id">
                                    <x-form.money name="monto" :id="'fm-monto-'.$inscripcion->id" label="Monto del abono" :required="true" col="col-12" data-limpiar-al-cargar />
                                    <x-form.date name="fecha_pago" :id="'fm-fecha-pago-'.$inscripcion->id" label="Fecha" :required="true" :value="now()->format('Y-m-d')" max="hoy" col="col-12" />
                                    <x-form.input name="notas" :id="'fm-notas-'.$inscripcion->id" label="Notas (opcional)" icono="fa-sticky-note" col="col-12" maxlength="255" />
                                </x-form.section>
                            </x-form.layout>
                        @endif
                    </div>
                </details>
            @endif

            @php($logs = $grilla->logsDe($inscripcion))
            <details class="grilla-panel">
                <summary class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-history me-1" aria-hidden="true"></i>Ver logs
                    @if($logs->isNotEmpty())<x-ui.insignia color="light">{{ $logs->count() }}</x-ui.insignia>@endif
                </summary>
                <div class="grilla-panel-contenido">
                    <h3 class="h6">Trazabilidad de {{ $inscripcion->persona->nombre_completo }}</h3>
                    @if($logs->isEmpty())
                        <p class="text-muted mb-0 py-2">Sin movimientos registrados.</p>
                    @else
                        <div class="historial-lista">
                            @foreach($logs as $log)
                                <x-historial.entrada :cambio="$log" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </details>

            @if($puedeEscribir && $inscripcion->pagos->isEmpty())
                <x-form.confirm :action="route('eventos.personas.destroy', [$inscripcion->evento_id, $inscripcion])" method="DELETE" titulo="¿Quitar a este miembro del evento?"
                    :mensaje="$inscripcion->persona->nombre_completo.' dejará de estar inscrito en este evento.'" boton="Quitar" :peligro="true" tamano="sm">Quitar</x-form.confirm>
            @endif
        </div>
        @break
@endswitch
