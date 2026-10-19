@props(['titulo', 'descripcion' => null, 'icono' => 'fa-folder-open', 'id' => null, 'visibleSi' => null])
<section @if($id) id="{{ $id }}" @endif class="fm-seccion" @if($visibleSi) data-visible-si="{{ $visibleSi }}" @endif {{ $attributes }}>
    <header class="fm-seccion__cab">
        <span class="fm-seccion__ico" aria-hidden="true"><i class="fas {{ $icono }}"></i></span>
        <div>
            <h3 class="fm-seccion__titulo">{{ $titulo }}</h3>
            @if($descripcion)
                <p class="fm-seccion__desc">{{ $descripcion }}</p>
            @endif
        </div>
    </header>
    <div class="fm-seccion__cuerpo">
        <div class="row">
            {{ $slot }}
        </div>
    </div>
</section>
