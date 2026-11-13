# Guía Rápida de Validación: Pestaña Asistencia en Eventos

Pasos para comprobar de punta a punta que la funcionalidad cumple la especificación. Detalles de contenido por pestaña en [data-model.md](data-model.md) y [contracts/pestana-asistencia-eventos.md](contracts/pestana-asistencia-eventos.md).

## Requisitos previos

- Trabajar en `laravel_app/`.
- Base local con datos de ejemplo (`php artisan db:seed` en entorno de desarrollo; **nunca** `migrate:fresh` sobre la base local).
- Usuario de ejemplo `admin@example.com` / `password`.
- Un evento con al menos un miembro inscrito, un abono registrado y el registro por código QR abierto.

## Automático

```bash
php artisan test --filter=Eventos          # contenido por pestaña, permisos, redirecciones
php artisan test --filter=RegistroQr        # panel QR reubicado en Asistencia (destino evento)
php artisan test                            # suite completa en verde
npm run build                               # compilación sin errores
npm run test:movil                          # incluye eventos/1?pestana=asistencia
```

Resultado esperado: todo en verde y `tests/movil/excepciones.json` sigue vacío.

## Manual en el navegador

1. Iniciar sesión como admin y abrir la ficha de un evento con inscripciones y pagos.
2. Pestaña **Datos** (la que abre por defecto). **Esperado**: solo nombre, tipo, fecha, lugar, costo, estado, descripción, total esperado y total recaudado; ningún formulario de inscripción, panel QR ni tarjeta de miembro.
3. Hacer clic en **Asistencia** (segunda pestaña, entre Datos e Histórico). **Esperado**: aparece el formulario «Inscribir miembro», el panel «Registro por código QR» y, debajo, «Miembros inscritos (N)» con una tarjeta por cada uno (costo aplicable, total pagado, saldo pendiente, abonos).
4. Inscribir un miembro nuevo desde ese formulario. **Esperado**: mensaje de éxito y la página vuelve a mostrarse en **Asistencia** (no en Datos), con el nuevo miembro en la lista.
5. Registrar un abono para un miembro inscrito. **Esperado**: igual que el paso 4, permanece en Asistencia con el saldo actualizado.
6. Eliminar ese abono. **Esperado**: permanece en Asistencia; el abono desaparece de la tabla.
7. Quitar a un miembro sin abonos. **Esperado**: permanece en Asistencia; el miembro desaparece de la lista y el contador baja.
8. Hacer clic en **Histórico**. **Esperado**: sin cambios respecto al comportamiento anterior a esta funcionalidad.
9. Abrir la URL con `?pestana=datos` y con `?pestana=historico` directamente. **Esperado**: ambas siguen funcionando igual que antes de este cambio.

## Permisos

10. Iniciar sesión con un usuario que tenga solo **lectura** en Eventos y abrir **Asistencia**. **Esperado**: ve la lista de miembros inscritos con su estado de pago, sin formulario de inscripción, sin botones de quitar/abonar/eliminar y sin el panel de código QR (ya exigía escritura antes de este cambio).
11. Con ese mismo usuario, abrir **Datos**. **Esperado**: ve la información general del evento, igual que un usuario con escritura.

## Casos límite

12. Evento sin ningún miembro inscrito, con permiso de escritura. **Esperado**: en Asistencia se ve el formulario de inscripción, el panel QR y el mensaje «Este evento aún no tiene miembros inscritos.»; nada de esto aparece en Datos.
13. El mismo evento sin inscripciones, con un usuario de solo lectura. **Esperado**: en Asistencia solo se ve el mensaje de estado vacío.
14. Abrir la ficha con un valor de `pestana` desconocido (por ejemplo `?pestana=lo-que-sea`). **Esperado**: se muestra la pestaña Datos, igual que el comportamiento actual.

## Responsive

15. Probar 320, 768 y 1920 px en la pestaña Asistencia. **Esperado**: sin desplazamiento horizontal de la página; las tarjetas y la tabla de abonos se apilan como en el resto de la aplicación.
