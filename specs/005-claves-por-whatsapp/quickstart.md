# Guía de validación: Claves por WhatsApp

## Requisitos previos

- Base temporal sembrada (no usar `migrate:fresh` sobre la base local): `.env` de prueba con su `APP_KEY` y `php artisan migrate --seed`. Usuarios de ejemplo en el README (`admin@example.com`, `pastor.centro@example.com`, `lider.centro@example.com`; contraseña `password`).
- `npm install && npm run build` si hay cambios de vistas o JavaScript.
- Navegador con sesión de WhatsApp Web o la aplicación instalada (para el escenario 3); no hace falta ninguna cuenta de empresa.

## Comandos

- `php artisan test --filter=Claves` y luego `php artisan test` completo.
- `npm test` si se añade el módulo de copiar mensaje.

## Escenarios de validación

| # | Escenario | Pasos | Resultado esperado |
|---|-----------|-------|--------------------|
| 1 | Crear usuario sin contraseña (HU1, FR-001/002) | Como pastor, Usuarios → Crear; llenar nombre, correo, rol y celular; guardar | No hay campos de contraseña; aparece «Clave generada» con la clave, el celular y los botones |
| 2 | Celular inválido (HU1) | Guardar sin celular o con letras | Error claro; no se crea la cuenta |
| 3 | Enviar por WhatsApp (FR-002/015) | Pulsar «Enviar por WhatsApp» y «Ya la envié» | Se abre WhatsApp con el mensaje y el número correctos; el estado pasa a `enviada` |
| 4 | Primer ingreso (HU4, FR-008) | Entrar con el correo y la clave temporal | Se redirige a «Elige tu contraseña»; ninguna otra pantalla abre hasta cambiarla |
| 5 | Reglas de la contraseña (HU4) | Probar 7 caracteres, igual a la temporal, confirmación distinta | Se rechaza con mensajes que explican cómo corregirlo |
| 6 | Clave usada (SC-003) | Tras elegir contraseña, intentar entrar con la temporal | Rechazada |
| 7 | Vencimiento (FR-003) | Con la base, adelantar `clave_vence_en` y entrar | «Tu clave temporal venció; pide una nueva» |
| 8 | «¿Olvidaste tu contraseña?» (HU2, FR-004/005) | En el login, enviar un correo existente y uno inexistente | Mismo mensaje y misma apariencia en ambos |
| 9 | Panel de solicitudes (FR-009) | Como pastor de la sede, abrir Solicitudes de clave | Aparece solo la solicitud de su sede; la insignia del menú muestra el número |
| 10 | Atender (HU2) | «Generar y enviar por WhatsApp» | Pantalla «Clave generada»; la solicitud pasa a atendida; la clave anterior deja de servir |
| 11 | Otra sede (FR-017) | Como pastor de otra sede, abrir la misma solicitud | 404/403; no la ve en su panel |
| 12 | Reenviar desde Usuarios (HU3, FR-010) | Usuarios → «Enviar nueva clave» → confirmar | Nueva clave; la anterior queda `reemplazada` |
| 13 | Permisos del pastor (D10) | Pastor intenta generar clave a un pastor o admin | 403 |
| 14 | Límites (FR-006) | Pedir 4 recuperaciones seguidas para la misma cuenta; 6 fallos de inicio de sesión | Bloqueo con mensaje; no se crean solicitudes extra |
| 15 | Registro sin secretos (SC-005) | Revisar `registros_clave`, `sessions` y `storage/logs` tras los escenarios | Ninguna clave ni hash de clave temporal aparece |
| 16 | Mi cuenta (FR-011) | Con sesión, cambiar contraseña con la actual | Funciona; con actual incorrecta se rechaza |
| 17 | Cuentas antiguas (D11) | Entrar con un usuario existente sin celular | Funciona con su contraseña actual; «Enviar nueva clave» pide completar el celular |

## Criterios de salida

- `php artisan test` y `npm test` en verde; `npm run build` sin errores.
- Escenarios 1–17 verificados con base temporal; limpiar `.env` de prueba, base y servidor temporales al terminar.

## Resultado de la revisión de seguridad (T051, 2026-10-19)

- Ninguna llamada a `Log::`, `logger()`, `dd()` ni `dump()` en `app/` registra claves (solo existen los avisos de las migraciones de datos).
- Ninguna vista imprime contraseñas; los campos de contraseña son `type="password"` y el valor nunca se rellena.
- Las respuestas que muestran una clave (`users.store`, `users.clave.store`, `solicitudes-clave.atender`) salen de `App\Http\Controllers\Concerns\MuestraClave` con `Cache-Control: no-store, private` y sin redirección.
- La prueba `SeguimientoClavesTest::test_ninguna_clave_emitida_queda_en_la_base_ni_en_el_log` busca la clave (con y sin guion) en `users`, `registros_clave`, `solicitudes_clave`, `sessions`, `cache`, `jobs` y el log.
- Pendiente de decisión (fuera de alcance): el alta de sede (`SedeService`) todavía pide la contraseña de pastor y pastora, y el login muestra la línea «Demo: admin@example.com / password».
