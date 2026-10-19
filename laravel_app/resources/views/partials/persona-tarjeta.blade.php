<div class="card mb-2">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <a href="{{ route('personas.show', $persona) }}"><strong>{{ $persona->nombre_completo }}</strong></a>
                @if($persona->es_lider)<x-ui.estado valor="lider" />@endif
                <div class="small text-muted">
                    <x-ui.estado :valor="$persona->estado" :etiqueta="$persona->estado_etiqueta" />
                    @if($persona->lider) · Líder: {{ $persona->lider->nombre_completo }} @endif
                </div>
            </div>
        </div>
        <div class="mt-2">
            @include('partials.acciones-contacto', ['persona' => $persona])
            <a href="{{ route('personas.show', $persona) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
        </div>
    </div>
</div>
