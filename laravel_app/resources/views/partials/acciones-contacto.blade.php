@if($persona->celular_limpio)
    <a href="tel:{{ $persona->celular_limpio }}" class="btn btn-sm btn-outline-primary" title="Llamar"><i class="fas fa-phone"></i> Llamar</a>
    <a href="https://wa.me/{{ $persona->celular_whatsapp }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="Escribir por WhatsApp"><i class="fab fa-whatsapp"></i> WhatsApp</a>
@endif
