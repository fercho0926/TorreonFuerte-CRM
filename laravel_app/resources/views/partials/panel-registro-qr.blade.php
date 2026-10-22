{{--
    Panel «Registro por QR» de la ficha de un evento, encuentro o célula: QR, enlace, descarga, apertura/cierre
    y la lista de quienes se registraron. Requiere $destino. Solo se muestra a quien puede gestionarlo.
--}}
@puede('gestionarRegistroQr', $destino)
    @php
        $tipoQr = $destino->tipoRegistroQr();
        $abierto = $destino->estaAbierto();
        $registros = $destino->registrosQr()->with('persona')->get();
    @endphp
    <x-ui.tarjeta titulo="Registro por QR" icono="fa-qrcode" :contador="$registros->count()" id="registro-qr">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-auto text-center">
                <div class="d-inline-block bg-white p-2 border rounded" role="img" aria-label="Código QR del registro de {{ $destino->nombre }}" style="max-width: 240px;">
                    {!! app(\App\Services\GeneradorQr::class)->svg($destino->urlRegistro(), 220) !!}
                </div>
            </div>
            <div class="col">
                <p class="mb-2">
                    @if($abierto)
                        <x-ui.estado valor="activo" etiqueta="Registro abierto" />
                    @else
                        <x-ui.estado valor="inactivo" etiqueta="Registro cerrado" />
                        @if($destino->registro_abierto)
                            <small class="text-muted ms-1">Se cierra solo cuando la actividad no está vigente.</small>
                        @endif
                    @endif
                </p>
                <label for="enlace-registro-{{ $tipoQr }}" class="form-label">Enlace de registro</label>
                <div class="input-group mb-3">
                    <input type="text" id="enlace-registro-{{ $tipoQr }}" class="form-control" value="{{ $destino->urlRegistro() }}" readonly>
                    <button type="button" class="btn btn-outline-secondary" data-copiar="{{ $destino->urlRegistro() }}">
                        <i class="fas fa-copy me-1" aria-hidden="true"></i>Copiar
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('registro-qr.svg', [$tipoQr, $destino->getKey()]) }}" class="btn btn-primary">
                        <i class="fas fa-download me-1" aria-hidden="true"></i>Descargar QR
                    </a>
                    <x-form.confirm :action="route('registro-qr.abrir', [$tipoQr, $destino->getKey()])" tamano=""
                        :titulo="$destino->registro_abierto ? '¿Cerrar el registro por QR?' : '¿Abrir el registro por QR?'"
                        :mensaje="$destino->registro_abierto ? 'Quien escanee el QR verá que el registro está cerrado.' : 'Cualquiera con el QR podrá registrarse.'"
                        :boton="$destino->registro_abierto ? 'Cerrar registro' : 'Abrir registro'" variante="outline-secondary">
                        {{ $destino->registro_abierto ? 'Cerrar registro' : 'Abrir registro' }}
                    </x-form.confirm>
                    <x-form.confirm :action="route('registro-qr.regenerar', [$tipoQr, $destino->getKey()])" tamano=""
                        titulo="¿Generar un enlace nuevo?" mensaje="El QR y el enlace anteriores dejarán de funcionar; tendrás que compartir el nuevo."
                        boton="Generar enlace nuevo" :peligro="true" variante="outline-danger">
                        Regenerar enlace
                    </x-form.confirm>
                </div>
            </div>
        </div>
    </x-ui.tarjeta>

    @include('partials.lista-registros-qr', ['registros' => $registros])
@endpuede
