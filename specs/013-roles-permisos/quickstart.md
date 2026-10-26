# Guía Rápida de Validación: Roles y permisos por módulo

Pasos para comprobar de punta a punta la funcionalidad. Datos en [data-model.md](data-model.md); contratos en [contracts/gestion-roles.md](contracts/gestion-roles.md) y [contracts/autorizacion-modulos.md](contracts/autorizacion-modulos.md); decisiones en [research.md](research.md).

## Requisitos previos

- Trabajar en `laravel_app/`.
- **Antes de implementar**: generar la caracterización de acceso actual (`php artisan test --filter=Caracterizacion` crea y guarda `tests/Support/matriz-acceso-base.json` con el resultado HTTP de cada ruta por rol). Se usa después para demostrar que nada cambió (CE-002).
- Migrar con `php artisan migrate` (**nunca** `migrate:fresh` sobre la base local). Las pruebas usan bases temporales y la migración siembra los 4 roles.
- Datos de ejemplo: al menos dos sedes, un administrador, un pastor, un líder con miembros y un usuario simple.

## Automático

```bash
php artisan test --filter=Roles               # caracterización, matriz módulo×nivel×rol, gestión, escalada, administrador, migración
php artisan test --filter=MatrizRutas         # toda ruta con sesión pertenece a un módulo o es reservada/pública
php artisan test                              # suite completa (las ~65 pruebas con roles siguen en verde)
npm test
npm run build
npm run test:movil                            # una sola corrida a la vez
```

Resultado esperado: todo en verde; la comparación contra la matriz base no muestra diferencias; `tests/movil/excepciones.json` vacío.

## Manual

1. Como administrador, abrir **Usuarios › Roles**. **Esperado**: aparecen Administrador, Pastor, Líder y Usuario con su alcance y cantidad de usuarios; Administrador marcado como del sistema.
2. Abrir **Pastor**. **Esperado**: la matriz muestra los niveles de [research.md](research.md) D3.
3. Crear el rol «Secretaría»: alcance «Toda la sede», lectura en Miembros, escritura en Eventos, asignable. **Esperado**: aparece en la lista y en el selector de rol de Usuarios.
4. Crear un usuario con ese rol e iniciar sesión con él en otra ventana. **Esperado**: el menú muestra Tablero, Miembros y Eventos; Miembros sin botones de crear, editar, inactivar ni eliminar; Eventos con ellos.
5. Escribir a mano la dirección de Usuarios con ese usuario. **Esperado**: rechazo de acceso.
6. Con ese usuario aún conectado, quitar a «Secretaría» la escritura en Eventos y pulsar en Eventos. **Esperado**: en su siguiente acción ve el rechazo o la pantalla sin botones, sin cerrar sesión.
7. Editar un rol con usuarios. **Esperado**: aviso con el número de usuarios afectados antes de guardar.
8. Intentar eliminar un rol con usuarios y luego el rol Administrador. **Esperado**: ambos rechazados con mensaje claro.
9. Intentar editar Administrador. **Esperado**: solo lectura y aviso de rol protegido.
10. Como pastor, crear un usuario. **Esperado**: solo ofrece roles asignables con permisos y alcance no mayores a los suyos; enviar a mano «Administrador» se rechaza.
11. Como pastor, intentar abrir **Usuarios › Roles** o `/roles`. **Esperado**: 403 y sin pestaña.
12. Abrir el historial de «Secretaría». **Esperado**: cambios con autor, fecha, nivel anterior y nuevo.
13. Con un rol que no tiene lectura en Eventos, abrir el Tablero. **Esperado**: no aparece el total ni el acceso rápido de eventos.
14. Cambiar el rol del último administrador. **Esperado**: rechazado.

## Responsive

15. Probar la pestaña Roles y el formulario a 320, 768 y 1920 px. **Esperado**: sin desplazamiento horizontal; la matriz en tarjetas por módulo con opciones de ≥ 44 px.
