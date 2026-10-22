@php($sede = $sede ?? null)
<x-form.input name="nombre" label="Nombre de la sede" icono="fa-church" :required="true" ejemplo="Ej. Torreón Fuerte Norte" :value="$sede?->nombre" maxlength="255" />
<x-form.input name="ciudad" label="Ciudad" icono="fa-city" :required="true" :value="$sede?->ciudad" maxlength="255" />
<x-form.input name="direccion" label="Dirección" icono="fa-map-marker-alt" :value="$sede?->direccion" col="col-md-6" maxlength="255" />
<x-form.input name="telefono" label="Teléfono" type="celular" icono="fa-phone" mascara="celular" :value="$sede?->telefono" col="col-md-3" maxlength="30" />
<x-form.input name="email" label="Correo de contacto" type="correo" icono="fa-envelope" :value="$sede?->email" col="col-md-3" maxlength="255" />
