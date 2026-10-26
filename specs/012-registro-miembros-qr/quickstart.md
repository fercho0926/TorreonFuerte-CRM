# Guía Rápida de Validación: Autorregistro y actualización de miembros por código QR

Pasos para comprobar de punta a punta la funcionalidad. Estructura de datos en [data-model.md](data-model.md); contratos en [contracts/formulario-publico.md](contracts/formulario-publico.md) y [contracts/gestion-qr-miembros.md](contracts/gestion-qr-miembros.md).

## Requisitos previos

- Trabajar en `laravel_app/`.
- Ejecutar la migración nueva en la base local con `php artisan migrate` (**nunca** `migrate:fresh` sobre la base local; las pruebas usan bases temporales).
- Datos de ejemplo: al menos dos sedes, un administrador, un pastor por sede, un miembro activo con celular y cédula, uno inactivo y uno eliminado.

## Automático

```bash
php artisan test --filter=RegistroMiembrosQr   # servicio, público, gestión, aislamiento, límites
php artisan test --filter=RegistroQr           # 009 sigue en verde tras extraer buscarEnSede
php artisan test                               # suite completa
npm test                                       # precarga ampliada del formulario
npm run build
npm run test:movil                             # una sola corrida, sin otras ejecuciones a la vez
```

Resultado esperado: todo en verde y `tests/movil/excepciones.json` vacío.

## Manual: gestión del QR

1. Como administrador, abrir **Sedes › una sede › pestaña «Registro de miembros»**. **Esperado**: se ve el QR, el enlace, «Descargar QR» y la lista de registros vacía.
2. Descargar el QR y escanearlo con un celular. **Esperado**: abre el formulario de esa sede.
3. Cerrar el registro y volver a abrir el enlace. **Esperado**: «Registro cerrado». Reabrir.
4. Regenerar el enlace. **Esperado**: el enlace anterior da «no válido»; el nuevo funciona.
5. Como pastor, abrir **Registro de miembros** del menú. **Esperado**: ve el panel de su sede y no puede usar las acciones de otra sede (403).
6. Como líder de célula, buscar la opción. **Esperado**: no aparece en el menú y la ruta responde 403.

## Manual: formulario público (sin sesión)

7. Escribir un celular que no existe, completar datos y elegir «Visitante». **Esperado**: confirmación; el miembro aparece con estado visitante y en el historial «Creado por QR de miembros».
8. Repetir con «Miembro activo». **Esperado**: queda como miembro activo.
9. Escribir el celular de un miembro existente. **Esperado**: se precargan todos sus datos (incluidos cédula, correo, dirección, barrio y ciudad).
10. Cambiar la dirección y el correo y enviar. **Esperado**: confirmación con lo actualizado; la ficha muestra los datos nuevos; el historial muestra «Actualizado por QR de miembros» con valores anteriores y nuevos.
11. Enviar de nuevo sin cambios. **Esperado**: «tus datos ya estaban al día» y no hay nueva entrada de historial.
12. Dejar vacío el barrio que ya estaba guardado y enviar. **Esperado**: el barrio se conserva.
13. Usar el celular de un miembro inactivo y luego de uno eliminado. **Esperado**: se reactivan (recuperan su estado anterior), con entradas «Reactivado por QR de miembros».
14. Usar la cédula de un miembro con el celular de otro. **Esperado**: mensaje de conflicto y nada cambia.
15. Usar un celular que solo existe en otra sede. **Esperado**: se crea un miembro nuevo en la sede del QR; la ficha de la otra sede no cambia.
16. Hacer 6 envíos o consultas en un minuto desde la misma conexión. **Esperado**: el sexto responde «espera un minuto».
17. Desactivar JavaScript y repetir 7 y 10. **Esperado**: el flujo de dos pasos funciona igual.

## Responsive

18. Probar el formulario y el panel a 320, 768 y 1920 px. **Esperado**: sin desplazamiento horizontal de la página, controles de 44 px o más.
