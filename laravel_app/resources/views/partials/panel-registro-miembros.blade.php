{{--
    Panel «Registro de miembros por QR» de una sede: QR, enlace, descarga, apertura/cierre, regeneración y la lista
    de registros. Requiere $sede. Lo usan la pestaña del detalle de sede (administrador) y la pantalla del pastor.
--}}
@puede('gestionarRegistroMiembros', $sede)
    @php
        $abierto = $sede->registroMiembrosAbierto();
        $enlace = $sede->urlRegistroMiembros();
        $registros = $sede->registrosMiembrosQr()->with('persona')->get();
    @endphp
    <x-ui.tarjeta titulo="Registro de miembros por QR" icono="fa-qrcode" :contador="$registros->count()" id="registro-miembros-qr">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-auto text-center">
                <div class="d-inline-block bg-white p-2 border rounded" role="img" aria-label="Código QR del registro de miembros de {{ $sede->nombre }}" style="max-width: 240px;">
                    {!! app(\App\Services\GeneradorQr::class)->svg($enlace, 220) !!}
                </div>
            </div>
            <div class="col">
                <p class="mb-2">
                    @if($abierto)
                        <x-ui.estado valor="activo" etiqueta="Registro abierto" />
                    @else
                        <x-ui.estado valor="inactivo" etiqueta="Registro cerrado" />
                        @if($sede->registro_abierto && !$sede->activa)
                            <small class="text-muted ms-1">Se cierra solo mientras la sede está inactiva.</small>
                        @endif
                    @endif
                </p>
                <p class="text-muted small">Quien lo escanee puede registrarse como miembro nuevo o actualizar sus datos.</p>
                <label for="enlace-registro-miembros" class="form-label">Enlace de registro</label>
                <div class="input-group mb-3">
                    <input type="text" id="enlace-registro-miembros" class="form-control" value="{{ $enlace }}" readonly>
                    <button type="button" class="btn btn-outline-secondary" data-copiar="{{ $enlace }}">
                        <i class="fas fa-copy me-1" aria-hidden="true"></i>Copiar
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('registro-miembros.svg', $sede) }}" class="btn btn-primary">
                        <i class="fas fa-download me-1" aria-hidden="true"></i>Descargar QR
                    </a>
                    <x-form.confirm :action="route('registro-miembros.abrir', $sede)" tamano=""
                        :titulo="$sede->registro_abierto ? '¿Cerrar el registro de miembros?' : '¿Abrir el registro de miembros?'"
                        :mensaje="$sede->registro_abierto ? 'Quien escanee el QR verá que el registro está cerrado.' : 'Cualquiera con el QR podrá registrarse o actualizar sus datos.'"
                        :boton="$sede->registro_abierto ? 'Cerrar registro' : 'Abrir registro'" variante="outline-secondary">
                        {{ $sede->registro_abierto ? 'Cerrar registro' : 'Abrir registro' }}
                    </x-form.confirm>
                    <x-form.confirm :action="route('registro-miembros.regenerar', $sede)" tamano=""
                        titulo="¿Generar un enlace nuevo?" mensaje="El QR y el enlace anteriores dejarán de funcionar; tendrás que compartir el nuevo."
                        boton="Generar enlace nuevo" :peligro="true" variante="outline-danger">
                        Regenerar enlace
                    </x-form.confirm>
                </div>
            </div>
        </div>
    </x-ui.tarjeta>

    @include('partials.lista-registros-miembros', ['registros' => $registros])
@endpuede
