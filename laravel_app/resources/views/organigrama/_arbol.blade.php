@if ($estado === 'vacio' || !$raiz)
    <div class="alert alert-secondary" role="status">
        <i class="fas fa-sitemap"></i> Aún no hay pastores, líderes ni miembros para mostrar en el organigrama.
    </div>
@else
    <section class="organigrama" data-organigrama aria-label="{{ __('interfaz.organigrama_de_la_sede') }}">
        <div class="organigrama-acciones" hidden>
            <button type="button" class="btn btn-outline-primary" data-organigrama-accion="expandir"><i class="fas fa-expand-alt"></i> {{ __('interfaz.expandir_todo') }}</button>
            <button type="button" class="btn btn-outline-secondary" data-organigrama-accion="colapsar"><i class="fas fa-compress-alt"></i> {{ __('interfaz.colapsar_todo') }}</button>
        </div>
        <ul class="organigrama-arbol">
            @include('organigrama._nodo', ['nodo' => $raiz])
        </ul>
    </section>
@endif
