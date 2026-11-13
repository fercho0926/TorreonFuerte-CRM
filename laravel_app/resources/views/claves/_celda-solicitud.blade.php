{{-- Celda de la grilla de solicitudes de clave. Variables: $fila (SolicitudClave), $columna. --}}
@php($solicitud = $fila)
@switch($columna)
    @case('cuenta')
        {{ $solicitud->user->name }}
        <small class="text-muted d-block">{{ $solicitud->user->email }}</small>
        @unless($solicitud->user->celular)
            <small class="text-danger d-block"><i class="fas fa-exclamation-circle" aria-hidden="true"></i> {{ __('interfaz.sin_celular_de_whatsapp') }} <a href="{{ route('users.edit', $solicitud->user) }}">{{ __('interfaz.completalo') }}</a> {{ __('interfaz.antes_de_generar_la_clave') }}</small>
        @endunless
        @break
    @case('rol')
        {{ \App\Models\User::etiquetaDeRol($solicitud->user->role) }}
        @break
    @case('sede')
        {{ $solicitud->getRelationValue('sede')?->nombre ?? '—' }}
        @break
    @case('pedida')
        {{ $solicitud->ultima_solicitud_en->locale('es')->diffForHumans() }}
        @break
    @case('estado')
        <x-ui.estado :valor="$solicitud->estadoVigente()" />
        @if($solicitud->atendidaPor)<small class="text-muted d-block">por {{ $solicitud->atendidaPor->name }}</small>@endif
        @break
    @case('acciones')
        @if($solicitud->estado === 'pendiente' && \App\Support\Permisos::puede('gestionarClaves', $solicitud->user))
            <x-form.confirm :action="route('solicitudes-clave.atender', $solicitud)" titulo="¿Generar una clave nueva?"
                :mensaje="'La contraseña actual de '.$solicitud->user->name.' dejará de servir. Se abrirá la clave para enviarla por WhatsApp.'" boton="Generar clave" variante="success" tamano="sm">
                <i class="fab fa-whatsapp me-1" aria-hidden="true"></i>Generar clave
            </x-form.confirm>
            <x-form.confirm :action="route('solicitudes-clave.descartar', $solicitud)" titulo="¿Descartar la solicitud?" mensaje="No se generará ninguna clave." boton="Descartar" variante="outline-secondary" tamano="sm">{{ __('interfaz.descartar') }}</x-form.confirm>
        @endif
        @break
@endswitch
