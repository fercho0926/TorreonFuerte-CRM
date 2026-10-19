{{-- Etiqueta de un campo (encima del control). Variables: $id, $label, $required --}}
@if(!empty($label))
    <label for="{{ $id }}" @class(['fm-etiqueta', 'fm-req' => !empty($required), 'visually-hidden' => !empty($ocultar)])>{{ $label }}</label>
@endif
