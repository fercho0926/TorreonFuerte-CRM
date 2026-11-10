# Guía Rápida de Validación: Corregir errores del registro por QR y exigir celular y documento

Pasos para comprobar de punta a punta esta funcionalidad. Estructura de datos en [data-model.md](data-model.md); contratos en [contracts/cambios-formularios-qr.md](contracts/cambios-formularios-qr.md) y [contracts/cambios-panel-interno.md](contracts/cambios-panel-interno.md). Esta funcionalidad **modifica** comportamiento de las funcionalidades 009 y 012: sus propias guías (`specs/009-registro-qr-eventos/quickstart.md`, `specs/012-registro-miembros-qr/quickstart.md`) siguen siendo válidas para lo que no cambió aquí.

## Requisitos previos

- Trabajar en `laravel_app/`.
- Sin migraciones nuevas que ejecutar (ver data-model.md).
- Datos de ejemplo: al menos dos sedes; un administrador; un pastor y un líder de célula de una de ellas; un evento, un encuentro y una célula con su QR abierto; la sede de miembros con su QR abierto; **al menos un miembro de prueba sin documento guardado** y **otro sin celular guardado** (para probar el relleno de huecos); un miembro de otra sede con un celular o documento conocido (para probar el arreglo del caso «de otra sede»).

## Automático

```bash
php artisan test --filter=RegistroQr           # 009 + los casos nuevos (concurrencia, de_otra_sede, obligatorios)
php artisan test --filter=RegistroMiembrosQr   # 012 + los casos nuevos (relleno de celular, dato_completado)
php artisan test --filter=Persona              # PersonaRequest, alta rápida, documento repetido
php artisan test                               # suite completa
npm test                                       # si cambió JavaScript de formulario
npm run build
npm run test:movil                             # una sola corrida, sin otras ejecuciones a la vez
```

Resultado esperado: todo en verde y `tests/movil/excepciones.json` vacío.

## Manual: obligatoriedad de celular y documento

1. Escanear el QR de un evento (o encuntro, o célula) y enviar el formulario de una persona nueva sin escribir el número de documento. **Esperado**: error claro junto al campo «Escribe tu número de documento» (o similar); nada se crea.
2. Repetir completando tipo y número de documento. **Esperado**: se crea la persona con celular y documento guardados, y queda inscrita.
3. Como administrador, abrir **Miembros › Nuevo miembro** y tratar de guardar sin celular ni documento. **Esperado**: ambos se piden como obligatorios; ya no se puede guardar solo con correo.
4. Repetir desde una célula con el alta rápida. **Esperado**: mismo comportamiento.

## Manual: completar el dato que falta a un miembro ya existente

5. Con el miembro de prueba sin documento, escanear el QR de miembros (o de un evento) y escribir su celular. **Esperado**: se precargan sus demás datos y el campo de documento queda vacío y obligatorio; sin completarlo no se puede enviar.
6. Completar el documento y enviar. **Esperado**: confirmación; en el QR de miembros el resultado es «Dato completado» (no «Actualizada»); el historial del miembro muestra el documento nuevo.
7. Con el miembro de prueba sin celular, repetir buscándolo por su número de documento. **Esperado**: mismo comportamiento, completando el celular.
8. Como administrador, abrir la ficha de un miembro con un dato faltante y tratar de guardar un cambio no relacionado (por ejemplo, la ciudad) sin completar el dato que falta. **Esperado**: no se puede guardar hasta completarlo.

## Manual: fallas corregidas («a veces muestra error»)

9. Enviar el formulario de un evento dos veces seguidas muy rápido (doble toque) con los mismos datos. **Esperado**: la segunda respuesta confirma «ya quedaste registrado» (no un error, no una segunda inscripción).
10. Abrir el mismo formulario en dos pestañas con el mismo celular y enviarlas casi al mismo tiempo. **Esperado**: una se procesa normalmente y la otra también confirma «ya quedaste registrado»; nunca una pantalla de error técnico ni dos personas creadas.
11. Registrar por el QR de un evento a alguien cuyo celular o documento ya pertenece a un miembro de **otra** sede. **Esperado**: queda inscrita en la sede del evento (se crea una ficha nueva ahí, con celular y documento), la ficha de la otra sede no se toca, y el administrador la ve en la lista marcada «de otra sede».
12. Como administrador, abrir la lista de registros por QR del evento y de la sede de miembros tras los pasos 6, 9 y 11. **Esperado**: se distinguen los resultados «Dato completado», «Ya registrado» y «De otra sede» junto a los ya conocidos.
13. Como administrador, crear manualmente un miembro con el mismo número de documento que otro miembro ya existente de su sede. **Esperado**: error claro junto al campo documento; nada se guarda (no una pantalla de error técnico).

## Responsive

14. Probar los formularios públicos y el formulario interno de miembro a 320, 768 y 1920 px, con los campos de documento ahora obligatorios. **Esperado**: sin desplazamiento horizontal de la página, controles de 44 px o más, teclado adecuado por campo.
