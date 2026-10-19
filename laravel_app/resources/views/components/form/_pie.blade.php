{{-- Ayuda y error de un campo (debajo del control). Variables: $id, $clave, $ayuda --}}
@if(!empty($ayuda))
    <small id="{{ $id }}-ayuda" class="fm-ayuda">{{ $ayuda }}</small>
@endif
<div id="{{ $id }}-error" class="invalid-feedback" role="alert">{{ $errors->first($clave) }}</div>
<div class="fm-ok" aria-hidden="true"><i class="fas fa-check-circle"></i> Correcto</div>
