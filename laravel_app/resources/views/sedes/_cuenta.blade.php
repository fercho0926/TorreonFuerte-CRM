{{-- Campos de una cuenta pastoral. Variables: $prefijo ('pastor' | 'pastora'), $obligatoria --}}
<x-form.input :name="$prefijo.'[nombres]'" label="Nombres" icono="fa-user" :required="$obligatoria" />
<x-form.input :name="$prefijo.'[apellidos]'" label="Apellidos" icono="fa-user" :required="$obligatoria" />
<x-form.input :name="$prefijo.'[email]'" label="Correo (para iniciar sesión)" type="email" icono="fa-envelope" :required="$obligatoria" col="col-md-5" />
<x-form.input :name="$prefijo.'[password]'" label="Contraseña inicial" type="password" icono="fa-lock" :required="$obligatoria" minlength="8" ayuda="Mínimo 8 caracteres." col="col-md-4" />
<x-form.input :name="$prefijo.'[celular]'" label="Celular" type="tel" icono="fa-phone" mascara="celular" col="col-md-3" maxlength="20" />
