# Contrato (delta): Alta y edición de miembro desde el panel interno

Rutas con sesión, sin cambios de nombre ni de permisos: `personas.store` / `personas.update` (`PersonaRequest`), `personas.rapida` (JSON, alta rápida desde una célula) y la creación combinada de cuenta + miembro de `UsersController` («Nuevo usuario», funcionalidad 016, que reutiliza `PersonaRequest::reglasDeAlta()`).

## Validación de entrada

| Campo | Antes | Ahora |
|-------|-------|-------|
| `celular` | Obligatorio solo si no hay `email` (`required_without:email`) | **Siempre obligatorio** |
| `email` | Obligatorio solo si no hay `celular` (`required_without:celular`) | Sigue opcional; **ya no sustituye** al celular |
| `tipo_documento` | No existía en la validación | **Nuevo, obligatorio** |
| `numero_documento` | No existía en la validación | **Nuevo, obligatorio** |

Mensajes nuevos junto al campo, en español, equivalentes a los ya usados en los formularios públicos de QR (`tipo_documento.required`, `numero_documento.required`).

## Editar un miembro al que le falta celular o documento

No hay un paso ni un endpoint nuevo: al abrir `personas.edit` para esa ficha, los campos `celular`/`tipo_documento`/`numero_documento` llegan vacíos y, al ser obligatorios, `personas.update` los rechaza hasta que se completen — sin tocar ningún otro dato que no se haya cambiado (research D2, Historia de Usuario 3 escenario 3).

## Formulario (`_form.blade.php` y el de alta rápida en células)

- `resources/views/personas/_form.blade.php` no tenía campos de documento; se agregan `tipo_documento` (`x-form.select`, opciones `Persona::TIPOS_DOCUMENTO`) y `numero_documento` (`x-form.input`), junto a `celular`, los tres marcados obligatorios con los mismos componentes `x-form.*` ya usados en el resto del formulario (sin formularios escritos a mano, principio VI).
- `resources/views/celulas/_form.blade.php` (alta rápida de un miembro desde una célula) recibe los mismos tres campos obligatorios.
- La descripción de la sección «Datos personales» («Basta con el celular o el correo») se actualiza para reflejar que celular y documento ahora son siempre obligatorios.

## Duplicados (`avisarDuplicado` / `buscarDuplicada`)

Sin cambios: la detección de un posible duplicado (mismo nombre, apellidos y celular) sigue igual.

## Documento repetido en la misma sede (regla nueva en el formulario)

`numero_documento` nunca se validaba en `PersonaRequest` porque el campo no existía en el formulario interno; la restricción única `(sede_id, numero_documento)` solo vivía en la base de datos. Al agregar el campo como obligatorio, se agrega también `Rule::unique('personas', 'numero_documento')->where('sede_id', …)->ignore($persona)` a `PersonaRequest::rules()`, para que escribir el documento de otro miembro de la misma sede se rechace con un mensaje claro junto al campo (422) en vez de un error técnico por la restricción de base de datos.
