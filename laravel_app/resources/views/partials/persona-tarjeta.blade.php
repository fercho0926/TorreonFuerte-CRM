<div class="card mb-2">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <a href="{{ route('personas.show', $persona) }}"><strong>{{ $persona->nombre_completo }}</strong></a>
                @if($persona->es_lider)<span class="badge badge-primary">Líder</span>@endif
                <div class="small text-muted">
                    <span class="badge badge-{{ $persona->estado === 'inactivo' ? 'secondary' : ($persona->estado === 'visitante' ? 'warning' : 'success') }}">{{ $persona->estado_etiqueta }}</span>
                    @if($persona->lider) · Líder: {{ $persona->lider->nombre_completo }} @endif
                </div>
            </div>
        </div>
        <div class="mt-2">
            @include('partials.acciones-contacto', ['persona' => $persona])
            <a href="{{ route('personas.show', $persona) }}" class="btn btn-sm btn-info">Ver</a>
        </div>
    </div>
</div>
