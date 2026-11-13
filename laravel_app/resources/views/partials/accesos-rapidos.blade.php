{{-- Accesos rápidos del tablero (se muestra arriba en teléfono y a la derecha en pantallas grandes). --}}
<div class="card card-primary card-outline mb-4">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-bolt me-1" aria-hidden="true"></i>{{ __('interfaz.accesos_rapidos') }}</h3></div>
        <div class="card-body d-grid gap-2">
            @puede('crear')
                <a href="{{ route('personas.create') }}" class="btn btn-primary"><i class="fas fa-user-plus me-1" aria-hidden="true"></i>{{ __('interfaz.registrar_miembro') }}</a>
            @endpuede
            @puede('designarLider')
                <a href="{{ route('lideres.designar') }}" class="btn btn-outline-primary"><i class="fas fa-user-tie me-1" aria-hidden="true"></i>{{ __('interfaz.designar_lider') }}</a>
            @endpuede
            @puedeModulo('eventos', 'escritura')
                <a href="{{ route('eventos.create') }}" class="btn btn-outline-primary"><i class="fas fa-glass-cheers me-1" aria-hidden="true"></i>{{ __('interfaz.crear_evento') }}</a>
            @endpuedeModulo
            @puedeModulo('celulas')
                <a href="{{ route('celulas.index') }}" class="btn btn-outline-secondary"><i class="fas fa-people-arrows me-1" aria-hidden="true"></i>{{ __('interfaz.ver_celulas') }}</a>
            @endpuedeModulo
        </div>
    </div>
