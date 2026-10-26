# Guía Rápida de Validación: Organigrama interactivo por sede

Pasos para comprobar de punta a punta que la funcionalidad cumple la especificación. Detalles de estructura en [data-model.md](data-model.md) y [contracts/pantalla-organigrama.md](contracts/pantalla-organigrama.md).

## Requisitos previos

- Trabajar en `laravel_app/`.
- Base local con datos de ejemplo (`php artisan db:seed` en entorno de desarrollo; **nunca** `migrate:fresh` sobre la base local).
- Usuario de ejemplo `admin@example.com` / `password`.
- Al menos dos sedes, una con pastor, varios líderes y miembros por líder, y alguna persona sin líder.

## Automático

```bash
php artisan test --filter=Organigrama   # estructura, alcance por rol, aislamiento entre sedes, pantalla
php artisan test                        # suite completa en verde
npm test                                # incluye organigrama.test.js
npm run build                           # compilación sin errores
npm run test:movil                      # 320–1920 px, controles de 44 px, sin desplazamiento horizontal
```

Resultado esperado: todo en verde y `tests/movil/excepciones.json` sigue vacío.

## Manual en el navegador

1. Iniciar sesión como admin y elegir una sede en el selector de la barra superior.
2. Abrir **Congregación › Organigrama**. **Esperado**: se ve la sede con sus pastores y los líderes colapsados; ningún nivel inferior visible.
3. Tocar un líder. **Esperado**: aparecen exactamente sus miembros; el contador del líder coincide con los que se ven.
4. Pulsar **Expandir todo** y luego **Colapsar todo**. **Esperado**: se abren y cierran todos los niveles sin recargar.
5. Tocar un nombre. **Esperado**: abre la ficha y, al volver, se muestra el organigrama.
6. Cambiar a «Todas las sedes». **Esperado**: mensaje para elegir una sede, sin datos.
7. Iniciar sesión como Pastor de la sede A. **Esperado**: solo la sede A; no hay forma de ver la B (no existe parámetro de sede).
8. Iniciar sesión como Líder. **Esperado**: solo su rama (él y sus miembros), sin pastores ni otros líderes.
9. Iniciar sesión con rol `user`. **Esperado**: 403.

## Responsive

10. Abrir las herramientas de desarrollo y probar 320, 768 y 1920 px. **Esperado**: sin barra de desplazamiento horizontal de la página; vertical con sangría en estrecho, horizontal tipo organigrama en ancho (≥ 992 px).
11. Con JavaScript desactivado, abrir la pantalla. **Esperado**: la jerarquía se expande y colapsa con el control nativo; no se ven botones «Expandir/Colapsar todo».

## Casos límite

12. Desactivar a un miembro. **Esperado**: desaparece del organigrama.
13. Sede sin pastor. **Esperado**: aviso «Sin pastor asignado».
14. Líder sin miembros. **Esperado**: nodo sin opción de expandir y con «0 miembros».
15. Persona sin líder. **Esperado**: aparece bajo «Sin líder asignado» (solo admin/pastor).
