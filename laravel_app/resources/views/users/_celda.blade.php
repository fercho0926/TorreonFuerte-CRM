{{-- Celda de la grilla de usuarios. Variables: $fila (User), $columna. --}}
@php($user = $fila)
@switch($columna)
    @case('nombre')
        <span class="fw-semibold">{{ $user->name }}</span>
        @break
    @case('email')
        {{ $user->email }}
        @break
    @case('rol')
        <x-ui.insignia color="secondary">{{ \App\Models\User::ROLE_LABELS[$user->role] ?? $user->role }}</x-ui.insignia>
        @break
    @case('sede')
        {{ $user->getRelationValue('sede')?->nombre ?? 'Todas (administrador)' }}
        @break
    @case('persona')
        {{ $user->persona?->nombre_completo ?? '—' }}
        @break
    @case('clave')
        @if($user->ultimoRegistroClave)
            <x-ui.estado :valor="$user->ultimoRegistroClave->estadoVigente()" />
        @else
            <span class="text-muted small">Sin clave temporal</span>
        @endif
        @unless($user->celular)<small class="text-danger d-block"><i class="fas fa-exclamation-circle" aria-hidden="true"></i> Sin celular</small>@endunless
        @break
    @case('registrado')
        {{ $user->created_at->format('Y-m-d H:i') }}
        @break
    @case('acciones')
        <x-ui.acciones-fila :editar="route('users.edit', $user)" :eliminar="route('users.destroy', $user)" :puede-editar="\App\Support\Permisos::puede('gestionar', $user)" :puede-eliminar="\App\Support\Permisos::puede('gestionar', $user)" eliminar-titulo="¿Eliminar usuario?" eliminar-mensaje="Esta acción no se puede deshacer." :nombre="$user->name">
            @if(\App\Support\Permisos::puede('gestionarClaves', $user))
                <x-form.confirm :action="route('users.clave.store', $user)" titulo="¿Enviar una clave nueva?" :mensaje="'La contraseña actual de '.$user->name.' dejará de servir. Se abrirá la clave para enviarla por WhatsApp.'" boton="Generar clave" variante="outline-success" tamano="sm" title="Enviar nueva clave" :aria-label="'Enviar nueva clave a '.$user->name"><i class="fab fa-whatsapp" aria-hidden="true"></i></x-form.confirm>
            @endif
        </x-ui.acciones-fila>
        @break
@endswitch
