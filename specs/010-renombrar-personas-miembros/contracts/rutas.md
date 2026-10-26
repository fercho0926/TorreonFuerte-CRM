# Contrato de Rutas: Miembros

Interfaz que la aplicación expone a navegadores, marcadores y enlaces ya enviados.

## Rutas nuevas (nombre → dirección)

| Nombre | Método | Dirección |
|---|---|---|
| `miembros.index` | GET | `/miembros` |
| `miembros.create` | GET | `/miembros/create` |
| `miembros.buscar` | GET | `/miembros/buscar` |
| `miembros.datos` | GET | `/miembros/datos` |
| `miembros.exportar` | GET | `/miembros/exportar` |
| `miembros.rapida` | POST | `/miembros/rapida` |
| `miembros.store` | POST | `/miembros` |
| `miembros.show` | GET | `/miembros/{miembro}` |
| `miembros.edit` | GET | `/miembros/{miembro}/edit` |
| `miembros.update` | PUT | `/miembros/{miembro}` |
| `traslados.create` / `traslados.store` | GET / POST | `/miembros/{miembro}/trasladar` |
| `miembros.inactivar` | POST | `/miembros/{miembro}/inactivar` |
| `miembros.reactivar` | POST | `/miembros/{miembro}/reactivar` |
| `miembros.destroy` | DELETE | `/miembros/{miembro}` |
| `sin-lider.asignar` | POST | `/sin-lider/{miembro}/asignar` |
| `eventos.miembros.store` | POST | `/eventos/{evento}/miembros` |
| `eventos.miembros.destroy` | DELETE | `/eventos/{evento}/miembros/{inscripcion}` |
| `eventos.pagos.store` | POST | `/eventos/{evento}/miembros/{inscripcion}/pagos` |

## Rutas antiguas (compatibilidad)

Toda dirección que empiece por `/personas` (y `/eventos/{evento}/personas…`) responde:

- **GET/HEAD**: redirección **301** a la misma dirección con `miembros`, conservando la cadena de consulta.
- **POST/PUT/PATCH/DELETE**: redirección **308** (conserva método y cuerpo).
- El control de acceso lo aplica la ruta destino; la redirección no revela si el miembro existe ni su sede.

## Cuerpos de petición (nombres de campo visibles en formularios)

Los campos `persona_id` y `personas[]` pasan a `miembro_id` y `miembros[]`. Durante la transición, los controladores aceptan los nombres antiguos como alias de entrada (un formulario abierto antes del despliegue sigue funcionando) y responden los errores con los nombres nuevos.

## Respuestas JSON

`/miembros/buscar` y `/miembros/rapida` mantienen la forma de respuesta actual (identificador y etiqueta); solo cambia la dirección. Los errores siguen respondiendo JSON (principio V).

## Exportaciones

Archivo CSV de `/miembros/exportar`: nombre `miembros-AAAA-MM-DD.csv`; encabezados con «miembro(s)» donde corresponda.
