{{-- Registros hechos por el QR de miembros de una sede. Requiere $registros (RegistroMiembroQr con su miembro). --}}
@php
    $etiquetasCampo = \App\Services\RegistroMiembrosQr::ETIQUETAS;
    $colores = ['nueva' => 'success', 'actualizada' => 'info', 'reactivada' => 'primary', 'sin_cambios' => 'secondary'];
@endphp
<x-ui.tarjeta titulo="Registros por QR" icono="fa-user-check" :contador="$registros->count()" :tabla="true" id="registros-miembros-qr">
    @if($registros->isEmpty())
        <p class="p-3 mb-0 text-muted">Todavía nadie se ha registrado con el QR de miembros.</p>
    @else
        <table class="tabla-apilable table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Celular</th>
                    <th>Resultado</th>
                    <th>Fecha y hora</th>
                    <th>Cambios</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $registro)
                    <tr>
                        <td data-etiqueta="Nombre" data-primaria>
                            @if($registro->persona && !$registro->persona->trashed())
                                <a href="{{ route('personas.show', $registro->persona) }}">{{ $registro->nombreCompleto() }}</a>
                            @else
                                {{ $registro->nombreCompleto() }}
                            @endif
                        </td>
                        <td data-etiqueta="Celular">{{ $registro->celular }}</td>
                        <td data-etiqueta="Resultado">
                            <x-ui.insignia :color="$colores[$registro->resultado] ?? 'secondary'">{{ $registro->etiquetaResultado() }}</x-ui.insignia>
                        </td>
                        <td data-etiqueta="Fecha y hora">{{ $registro->created_at->format('Y-m-d H:i') }}</td>
                        <td data-etiqueta="Cambios">
                            @forelse($registro->cambios ?? [] as $campo => $par)
                                <div>{{ $etiquetasCampo[$campo] ?? $campo }}: {{ $par[0] ?? '—' }} → {{ $par[1] }}</div>
                            @empty
                                —
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-ui.tarjeta>
