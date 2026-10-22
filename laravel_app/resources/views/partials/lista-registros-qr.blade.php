{{-- Registrados por QR de un destino. Requiere $registros (RegistroQr con su persona). --}}
<x-ui.tarjeta titulo="Registrados por QR" icono="fa-user-check" :contador="$registros->count()" :tabla="true" id="registrados-qr">
    @if($registros->isEmpty())
        <p class="p-3 mb-0 text-muted">Todavía nadie se ha registrado con el QR.</p>
    @else
        <table class="tabla-apilable table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Celular</th>
                    <th>Resultado</th>
                    <th>Fecha y hora</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $registro)
                    <tr>
                        <td data-etiqueta="Nombre">
                            {{-- Las personas de otra sede o pendientes no tienen ficha enlazable. --}}
                            @if($registro->persona && !$registro->persona->trashed() && in_array($registro->resultado, ['nueva', 'existente'], true))
                                <a href="{{ route('personas.show', $registro->persona) }}">{{ $registro->nombreCompleto() }}</a>
                            @else
                                {{ $registro->nombreCompleto() }}
                            @endif
                        </td>
                        <td data-etiqueta="Celular">{{ $registro->celular }}</td>
                        <td data-etiqueta="Resultado">
                            <x-ui.insignia :color="['nueva' => 'success', 'existente' => 'info', 'de_otra_sede' => 'secondary', 'pendiente' => 'warning'][$registro->resultado] ?? 'secondary'">{{ $registro->etiquetaResultado() }}</x-ui.insignia>
                        </td>
                        <td data-etiqueta="Fecha y hora">{{ $registro->created_at->format('Y-m-d H:i') }}</td>
                        <td data-etiqueta="Nota">{{ $registro->nota_correccion ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-ui.tarjeta>
