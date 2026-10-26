# Contrato: Gestión del QR de miembros (administrador y pastor)

Rutas con sesión. Autorización por el gate `gestionarRegistroMiembros($user, $sede)`: el administrador puede con cualquier sede; el pastor solo con la suya (`sede_id` propio); líder de célula y rol usuario, nunca (403). Un pastor que pide otra sede recibe 403.

| Método | Ruta | Nombre | Quién | Descripción |
|--------|------|--------|-------|-------------|
| GET | `/sedes/{sede}?pestana=registro` | `sedes.show` | Administrador | Pestaña «Registro de miembros» dentro del detalle de sede (junto a «Datos» y «Organigrama»). |
| GET | `/registro-miembros` | `registro-miembros.index` | Pastor | Pantalla propia con el mismo panel, de su sede. Opción de menú «Registro de miembros» visible solo para el rol pastor (gate `verRegistroMiembros`). |
| GET | `/sedes/{sede}/registro-miembros/qr.svg` | `registro-miembros.svg` | Admin / pastor de la sede | Descarga el QR (SVG con nombre de la sede y «Registro de miembros»). |
| POST | `/sedes/{sede}/registro-miembros/abrir` | `registro-miembros.abrir` | Admin / pastor de la sede | Alterna abierto y cerrado. |
| POST | `/sedes/{sede}/registro-miembros/regenerar` | `registro-miembros.regenerar` | Admin / pastor de la sede | Genera un token nuevo; el enlace y el QR anteriores dejan de funcionar. |

## Panel `panel-registro-miembros` (parcial compartido)

Contenido: QR en pantalla, enlace con botón «Copiar», estado (abierto o cerrado; cerrado también si la sede está inactiva), «Descargar QR», «Cerrar/Abrir registro» y «Regenerar enlace» (ambas con confirmación mediante `x-form.confirm`), y la lista de registros.

### Lista de registros por QR

Tabla apilable en celular, más recientes primero, con: nombre (enlace a la ficha cuando existe y el usuario la puede ver), celular, resultado (nueva, actualizada, reactivada, sin cambios), fecha y hora y detalle de los cambios (campo, valor anterior → valor nuevo). Solo registros de la sede mostrada; un pastor nunca ve los de otra sede.

## Reglas

- Las respuestas del panel llevan `Cache-Control: no-store, private` (lista personas) y el SVG también.
- Las acciones usan `x-form.*` (principio VI); sin formularios escritos a mano.
- El enlace al QR en `sedes.show` solo se muestra al administrador (módulo exclusivo); la pantalla del pastor usa su propia sede sin parámetro de sede en la URL de lectura.
- Todas las rutas con `{sede}` quedan cubiertas por `MatrizRutasTest` (otra sede responde 403 o 404); las rutas públicas `registro-sede.*` se agregan a su lista de ignoradas y se prueban en `Aislamiento` propio.
