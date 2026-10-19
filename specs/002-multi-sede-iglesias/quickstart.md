# Guía de validación (quickstart): Multi-sede

Escenarios para comprobar de extremo a extremo que la funcionalidad cumple la [spec](spec.md). Detalle de datos en [data-model.md](data-model.md) y de rutas en [contracts/rutas-ui.md](contracts/rutas-ui.md).

## Requisitos previos

- PHP ^8.3, Composer y Node (ya usados por `laravel_app/`).
- La funcionalidad `001-revision-personas-lideres` implementada (Personas, Líderes, Redes, Zonas).
- **Copia de seguridad de la base de datos antes de migrar** en cualquier entorno con datos reales.

> ⚠️ `migrate:fresh` borra la base indicada en `.env`. Para probar sin riesgo, usa una base temporal: `DB_DATABASE=<ruta>\prueba.sqlite php artisan migrate:fresh --seed`.

## Preparación

```powershell
cd laravel_app
composer install
php artisan migrate:fresh --seed     # solo en una base de desarrollo desechable
php artisan serve
```

El sembrado debe crear: un admin; tres sedes (por ejemplo "Torreón Fuerte Centro", "Norte" y "Sur") con su pastor y pastora; líderes, personas, células, eventos y ministerios distintos en cada sede.

## Pruebas automatizadas

```powershell
php artisan test
```

Resultado esperado: pruebas de sedes, de roles y de usuarios; pruebas de aislamiento por módulo; la matriz que recorre todas las rutas; la de cobertura de modelos con `sede_id`; la de traslado y la de migración de datos, todas en verde junto con las 54 previas.

## Escenarios manuales

| # | Escenario | Pasos | Resultado esperado |
|---|-----------|-------|--------------------|
| 1 | Crear sede completa (HU1, SC-001) | Admin → Sedes → Nueva sede → datos + pastor + pastora → Guardar | La sede aparece en el listado; el pastor y la pastora pueden iniciar sesión; todo en menos de 3 min |
| 2 | Sede sin pastora (HU1) | Crear sede indicando solo al pastor | Se crea correctamente; la pastora se añade después |
| 3 | Alta atómica (HU1) | Crear sede con un correo de pastora ya existente | Error por campo y no queda sede ni pastor a medias |
| 4 | Módulo solo admin (HU1) | Entrar como pastor y abrir `/sedes` | 403 y el menú "Sedes" no aparece |
| 5 | Aislamiento de listados (HU2, SC-002) | Pastor de A: abrir personas, líderes, células, encuentros, eventos, ministerios, redes, zonas | Solo datos de A; nada de B |
| 6 | Enlace directo (HU2) | Pastor de A abre la URL de una persona, célula o evento de B | 404, sin revelar que existe |
| 7 | Búsqueda y selectores (HU2) | Pastor de A busca el nombre de una persona de B; abre formularios con selectores de líder, red, ministerio | Sin resultados de B; los selectores solo ofrecen datos de A |
| 8 | Documento repetido entre sedes (HU2) | Registrar en A y en B personas con el mismo documento | Ambas se crean; ninguna sede ve a la otra |
| 9 | Usuarios del pastor (HU2) | Pastor de A → Usuarios → crear líder; intentar crear pastor o admin | Líder creado en A; pastor/admin → 403; solo ve usuarios de A |
| 10 | Sede activa del admin (HU3) | Admin elige sede A en la barra; crea una persona; luego elige "Todas las sedes" e intenta crear | Persona queda en A; con "Todas" se bloquea con mensaje |
| 11 | Resumen global (HU3) | Admin en "Todas las sedes" → Dashboard | Tabla por sede y total global rotulado; cada registro de listados muestra su sede |
| 12 | Cambio de pastor (HU4) | Admin → Sedes → sede A → Cambiar pastor | El nuevo pastor entra a A; el anterior pasa a líder y conserva su registro |
| 13 | Desactivar sede (HU4) | Admin desactiva B | Usuarios de B no pueden iniciar sesión ni seguir en sesión; el admin ve sus datos en lectura y puede reactivarla |
| 14 | Eliminar sede con datos (HU4) | Admin intenta eliminar B | 422: solo se puede desactivar |
| 15 | Traslado de persona | Admin (sede activa A) → persona → Trasladar a B | Registro nuevo en B sin líder/célula/ministerios; el de A queda inactivo con su historial |
| 16 | Traslado bloqueado | Intentar trasladar a un líder con personas a cargo | 422 con el detalle de lo que hay que reasignar |
| 17 | Migración (HU5, SC-004) | Con una copia de los datos actuales: `php artisan migrate` | Todo queda en la primera sede; conteos iguales antes y después; los pastores actuales solo ven esa sede; cero filas sin sede |
| 18 | Escala (SC-003) | Crear 20 sedes seguidas (seeder de prueba) y abrir listados con 1.000 personas en una sede | Alta igual de fácil y listados en menos de 2 s |

## Criterio de finalización

Todos los escenarios pasan, `php artisan test` queda en verde y la consulta de verificación (filas sin sede en las 11 tablas) devuelve cero.

## Resultado de la verificación (implementación)

- `php artisan test`: 154 pruebas en verde (54 de la funcionalidad 001 + las nuevas de multi-sede).
- Escenario 17 (migración): verificado en una base temporal con datos de una sola iglesia: se creó la primera sede, todas las filas quedaron en ella, el admin quedó sin sede, el pastor quedó como pastor de la sede y no quedaron filas sin sede.
- Sembrado (`migrate:fresh --seed`) verificado en una base temporal: 3 sedes, cada una con pastor, pastora, líder y datos propios; segunda ejecución sin duplicados.
- Pendiente: recorrido visual manual de los escenarios en el navegador (tarea T069).
