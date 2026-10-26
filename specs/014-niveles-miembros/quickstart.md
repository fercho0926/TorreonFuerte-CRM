# Guía de Validación Rápida: Niveles de formación de los miembros

## Requisitos

- `php artisan migrate` aplicado (crea las tablas, carga los cuatro niveles y da el permiso `niveles` a los roles existentes). Haz una copia de la base antes de migrar.
- `npm install` y `npm run build`.
- Usuarios de ejemplo del `DatabaseSeeder` (la contraseña de ejemplo está indicada allí).

## Automática

```bash
php artisan test                 # incluye tests/Unit/Niveles y tests/Feature/Niveles
npm test                         # Vitest (si se agregó JavaScript)
npm run build
npm run test:movil               # auditoría a 320/360/390/430 px con las pantallas nuevas
```

Esperado: todo en verde, con la lista de excepciones de `tests/movil/excepciones.json` vacía.

## Manual

1. **Plan**: abrir **Niveles**; ver los cuatro niveles en orden: Libres para servir, Aliviando el fuego, Caminando en santidad, Viviendo en el espíritu.
2. **Crear y ordenar**: crear «Nivel de prueba»; subirlo a la posición 2 y bajarlo de nuevo; desactivarlo y reactivarlo; eliminarlo (sin registros se permite). Un nombre repetido muestra error.
3. **Registro manual**: abrir la ficha de un miembro; ver «Aún no inicia su camino»; en «Registrar avance» elegir el nivel 1 y la fecha, guardar: el camino marca la estación 1 completada y la 2 como siguiente. Intentar registrar el nivel 1 otra vez: «Ya tiene este nivel completado». Quitarlo: vuelve a pendiente.
4. **Automático por encuentro**: crear un encuentro «Retiro 2» realizado, vinculado al nivel 2, con un miembro marcado como asistente: su camino marca el nivel 2 con la fecha del encuentro y origen «Encuentro». Desmarcar la asistencia: vuelve a pendiente. Cancelar el encuentro: tampoco cuenta.
5. **Saltado**: en un miembro con el nivel 3 completado y el 2 sin completar, el camino muestra el 3 completo, el 2 «Pendiente (saltado)» y la siguiente estación es la 2.
6. **Quién está en cada nivel**: en Niveles, ver tarjetas con conteos y «Sin iniciar»; abrir una tarjeta: la lista (nombre, celular, «Llamar» y «WhatsApp»), filtrar, ordenar y exportar a CSV; los conteos coinciden con las fichas.
7. **Miembros**: la columna «Nivel» muestra los puntos de progreso y el filtro por nivel devuelve solo los de ese nivel actual.
8. **Tablero**: la tarjeta «Miembros por nivel» coincide con la vista de Niveles; con un rol sin acceso a Niveles no aparece.
9. **Permisos**: con un rol de solo lectura se ve todo sin botones de crear, ordenar ni registrar; sin acceso, el módulo no aparece y la dirección responde 403.
10. **Aislamiento**: un pastor de otra sede no ve ni cuenta a los miembros de la primera; un líder solo ve su rama.
11. **Celular**: en 360 px el camino se ve completo sin desplazamiento lateral, las estaciones y botones se tocan bien y el texto indica el estado; con «reducir movimiento» activado en el sistema no hay pulso.
12. **Traslado**: trasladar un miembro con avances a otra sede: su camino se conserva en el nuevo registro.

Referencias: [contratos](contracts/), [modelo](data-model.md), [investigación](research.md).

## Resultado de la verificación final

- `php artisan test`: todas las pruebas en verde (incluye matriz de aislamiento y guardián de terminología).
- `npm test`: 114 pruebas en verde. `npm run build`: correcto.
- `npm run test:movil`: 404 aprobadas (0 fallos), incluidas las pantallas de niveles y `niveles.spec.js` a 320/360/390/430 px.
