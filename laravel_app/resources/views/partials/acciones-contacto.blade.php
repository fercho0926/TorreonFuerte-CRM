@if($persona->celular_limpio)
    {{-- Solo WhatsApp: nunca se llama a los miembros, el contacto es siempre por WhatsApp. --}}
    <a href="https://wa.me/{{ $persona->celular_whatsapp }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="Escribir por WhatsApp"><i class="fab fa-whatsapp"></i> WhatsApp</a>
@endif
