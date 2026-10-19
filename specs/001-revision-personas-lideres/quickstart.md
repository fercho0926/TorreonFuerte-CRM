# Guía de validación (quickstart)

Escenarios para comprobar de extremo a extremo que la funcionalidad cumple la [spec](spec.md). Detalle de datos en [data-model.md](data-model.md) y de rutas en [contracts/rutas-ui.md](contracts/rutas-ui.md).

## Requisitos previos

- PHP ^8.3, Composer y Node (ya usados por `laravel_app/`).
- Dependencias instaladas y `.env` configurado (`laravel_app/.env.example`, SQLite por defecto).

## Preparación

```powershell
cd laravel_app
composer install
php artisan migrate:fresh --seed
php artisan serve
```

El sembrado debe crear: un admin, un pastor, dos líderes (con su red y zona) con sus usuarios, personas con y sin líder, y una célula por líder.

## Pruebas automatizadas

```powershell
php artisan test
```

Resultado esperado: pruebas de búsqueda, alcance por rol, liderazgo, "sin líder" y migración de datos en verde.

## Escenarios manuales

| # | Escenario | Pasos | Resultado esperado |
|---|-----------|-------|--------------------|
| 1 | Registrar visitante (HU2, SC-001) | Sesión como líder → Personas → Crear → nombres, apellidos, celular → Guardar | Se guarda con estado "visitante" sin documento, en menos de 1 minuto |
| 2 | Buscar (HU2, SC-002) | Escribir parte de un apellido, documento o celular | Lista reducida inmediatamente, paginada |
| 3 | Duplicado | Registrar misma persona (mismo nombre y celular) | Aviso con enlace a la ficha existente y casilla para continuar |
| 4 | Designar líder existente (HU3) | Líderes → Designar → buscar persona registrada → red y zona | La persona aparece en Líderes sin duplicar registro |
| 5 | Quitar líder con dependientes | Quitar rol a un líder con personas y células | Pantalla con conteos y opción de reasignar; no se completa sin decisión |
| 6 | Red duplicada | Crear red "jovenes" existiendo "Jóvenes" | El sistema rechaza y sugiere la existente |
| 7 | Alcance por rol (HU4, SC-004) | Sesión como líder → abrir listado y selectores | Solo personas, líderes y redes de su ámbito; sin botones de acciones no permitidas |
| 8 | Reasignar fuera de ámbito | Como líder, intentar asignar una persona a un líder ajeno (manipulando el formulario) | 403/422 con mensaje; no se guarda |
| 9 | Coherencia con células (HU1) | Cambiar el líder de un miembro de célula | Se retira de la célula anterior o pide elegir una del nuevo líder |
| 10 | Sin líder (FR-024) | Registrar persona sin líder; entrar como pastor | Aparece en `/sin-lider`; un líder recibe 403 y no la ve |
| 11 | Inactivar | Inactivar persona con pagos de eventos | Sale del listado por defecto, conserva historial; reactivable |
| 12 | Móvil (HU5, SC-007) | Abrir listado y ficha en 375 px de ancho | Tarjetas legibles sin desplazamiento horizontal; botones llamar/WhatsApp funcionan |
| 13 | Migración (SC-006) | Con datos de la versión anterior: `php artisan migrate` | Redes normalizadas sin duplicados, usuarios `lider_zona` convertidos a líder, ninguna persona perdida |

## Criterio de finalización

Todos los escenarios pasan y `php artisan test` queda en verde.
