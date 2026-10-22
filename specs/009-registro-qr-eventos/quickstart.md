# Guía de Validación Rápida: Registro por QR

## Requisitos previos

```powershell
cd laravel_app
composer install
php artisan migrate          # nunca migrate:fresh sobre la base local
npm install
npm run build
```

Tener al menos dos sedes con datos de prueba (los seeders y las pruebas ya las crean).

## Escenarios

1. **QR en la ficha** — Entrar como pastor de la sede A, abrir un evento, un encuentro y una célula. Esperado: cada ficha muestra su QR, enlace y botón de descarga (US1).
2. **Persona nueva** — En una ventana sin sesión abrir el enlace, escribir un celular nuevo y completar nombres, apellidos y fecha de nacimiento. Esperado: confirmación; la persona aparece en Personas (sede A, «Visitante») y en los inscritos (US2).
3. **Persona existente por cédula** — Abrir el enlace, escribir la cédula de una persona de la sede A. Esperado: datos precargados; al enviar no se crea otra persona y sus datos no cambian (US3).
4. **Doble registro** — Repetir el escenario 3 con el mismo celular. Esperado: mensaje «ya estás registrado», sin duplicados (FR-008).
5. **Otra sede** — Registrar con el celular de una persona de la sede B en el enlace de la sede A. Esperado: no se precarga nada; en la ficha de A aparece «de otra sede» con el nombre y celular escritos, sin enlace a su ficha; la persona de B no cambia.
6. **Conflicto** — Cédula de una persona y celular de otra. Esperado: rechazo con mensaje claro.
7. **Cierre** — Cerrar el registro o cancelar el evento y abrir el enlace. Esperado: «registro cerrado».
8. **Regenerar** — Regenerar el enlace; el anterior responde «enlace no válido».
9. **Límite de frecuencia** — Más de 30 búsquedas por minuto desde la misma IP responde 429.
10. **Celular** — Probar a 320 px y 1920 px: sin desplazamiento horizontal, controles de 44 px, teclado numérico.

## Pruebas automáticas

```powershell
php artisan test --filter=RegistroQr
php artisan test
npm test
npm run test:movil
npm run build
```

Esperado: todo en verde; `MatrizRutasTest` cubre las rutas nuevas.
