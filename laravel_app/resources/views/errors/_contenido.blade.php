{{-- Contenido común de las páginas de error: icono, código, mensaje y acción para volver. --}}
<div class="card card-{{ $tipo }} card-outline">
    <div class="card-body text-center py-5">
        <i class="fas {{ $icono }} fa-4x text-{{ $tipo }} mb-3" aria-hidden="true"></i>
        <h2 class="fw-bold">{{ $codigo }}</h2>
        <p class="lead mb-4">{{ $mensaje }}</p>
        <a href="{{ url('/') }}" class="btn btn-primary"><i class="fas fa-home me-1" aria-hidden="true"></i>Volver al inicio</a>
    </div>
</div>
