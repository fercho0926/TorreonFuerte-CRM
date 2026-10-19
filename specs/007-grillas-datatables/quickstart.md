# Guía de Validación Rápida: Grillas uniformes

## Requisitos

- `php artisan migrate` aplicado en la base local y usuarios de ejemplo del `DatabaseSeeder` (la contraseña de ejemplo está indicada allí).
- `npm install` y `npm run build`.

## Automática

```bash
php artisan test            # incluye tests/Feature/Grillas y la prueba guarda de listados
npm test                    # Vitest: funciones de la grilla
npm run build
```

Esperado: todo en verde. La prueba guarda falla si un `*/index.blade.php` usa `<table>` fuera del componente.

## Manual (por cada listado: líderes, células, encuentros, eventos, ministerios, sedes, sin líder, usuarios, solicitudes de clave)

1. Abrir el listado: se ve la cabecera gris, filtros por columna, contador y «Exportar CSV» (en usuarios y solicitudes solo como administrador).
2. Ordenar por una columna dos veces: cambia el sentido en todo el conjunto.
3. Escribir en un filtro de columna: la grilla y el contador se actualizan.
4. Cambiar las filas por página (10/25/50/100) y paginar.
5. Exportar con un filtro activo: el CSV contiene todas las filas filtradas y abre con tildes en Excel.
6. Con un rol limitado (pastor de otra sede, líder): no aparecen filas ajenas, ni en pantalla ni en el CSV.
7. Reducir el ancho a teléfono: columnas secundarias plegadas, acciones accesibles, sin scroll horizontal de la página.
8. Deshabilitar JavaScript: se ve la primera página con el paginador del servidor.
9. Como no administrador, abrir directamente la exportación de usuarios: respuesta 403.

Referencias: [contratos](contracts/rutas-y-componentes.md), [modelo](data-model.md).
