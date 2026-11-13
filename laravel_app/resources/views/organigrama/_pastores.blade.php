@if ($nodo['tipo'] === 'sede')
    <span class="organigrama-pastores">
        @forelse ($nodo['pastores'] as $pastor)
            <span class="organigrama-pastor"><i class="fas fa-user-shield" aria-hidden="true"></i>
                @if ($pastor['puedeVerFicha'])
                    <a href="{{ route('personas.show', $pastor['id']) }}" title="{{ $pastor['nombre'] }}">{{ $pastor['nombre'] }}</a>
                @else
                    <span title="{{ $pastor['nombre'] }}">{{ $pastor['nombre'] }}</span>
                @endif
                <small class="organigrama-rol">{{ __('interfaz.pastor') }}</small></span>
        @empty
            <span class="organigrama-aviso"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> {{ __('interfaz.sin_pastor_asignado') }}</span>
        @endforelse
    </span>
@endif
