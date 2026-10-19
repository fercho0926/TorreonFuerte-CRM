---

description: "Lista de tareas de la funcionalidad 007: grillas uniformes"
---

# Tareas: Grillas uniformes con ordenamiento, filtros, paginación y exportación

**Entrada**: documentos de diseño en `/specs/007-grillas-datatables/`

**Requisitos previos**: plan.md, spec.md, research.md, data-model.md, contracts/rutas-y-componentes.md

**Pruebas**: se incluyen porque el plan y la especificación las exigen (RF-014, CE-004, CE-006). Cada prueba se escribe antes de su implementación.

**Organización**: por historia de usuario. Todas las rutas son relativas a `laravel_app/`.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[HU1..HU4]**: historia de usuario a la que pertenece (spec.md)

---

## Fase 1: Preparación

- [X] T001 Confirmar que `package.json` ya incluye `datatables.net-bs5` y `datatables.net-responsive-bs5` y que `npm run build` y `php artisan test` están en verde antes de empezar (línea base: PHPUnit 395, Vitest 74)
- [X] T002 [P] Crear los directorios `app/Grillas/`, `tests/Feature/Grillas/` y `tests/Unit/Grillas/`

---

## Fase 2: Fundamentos (bloquea todas las historias)

**Propósito**: base reutilizable y refactor de personas sobre ella sin cambiar su comportamiento visible (RF-013, RF-017).

### Pruebas

- [X] T003 [P] Prueba unitaria de `Columna` (valores por defecto, `acciones` nunca ordenable, filtro lista/texto/ninguno) en `tests/Unit/Grillas/ColumnaTest.php`
- [X] T004 [P] Prueba unitaria de la base `Grilla`: lista blanca de orden y filtros (una clave desconocida cae a la primera columna), `length` solo 10/25/50/100 (otro valor pasa a 25), neutralización de `= + - @` en CSV, fuente `Collection` y fuente `Builder` en `tests/Unit/Grillas/GrillaTest.php`
- [X] T005 [P] Prueba del contrato común (JSON DataTables con `draw`, `recordsTotal`, `recordsFiltered`, `data`; `401` JSON sin sesión; `403` JSON sin permiso; CSV con BOM, `;` y `text/csv`) parametrizada sobre las grillas registradas en `tests/Feature/Grillas/ContratoGrillaTest.php`
- [X] T006 [P] Pruebas Vitest de `grilla.js` (`parametrosFiltro`, `urlExportar`, `retardo`) en `resources/js/grilla/grilla.test.js`, conservando las de `personas.test.js`

### Implementación

- [X] T007 [P] Crear el valor inmutable `Columna` (clave, título, ordenable, filtro, expresión de orden/filtro, celda, exportar, prioridad, visible) en `app/Grillas/Columna.php`
- [X] T008 Crear la clase base `Grilla` con `columnas($usuario)`, `consulta($usuario, $estado)`, filtros por columna, búsqueda general, orden por lista blanca, paginación, `datos()` y `filas()` para `Builder` y `Collection` en `app/Grillas/Grilla.php`
- [X] T009 Agregar a `Grilla` la exportación CSV por lotes de 500 (BOM, separador `;`, neutralización de fórmulas, valores exportables por columna) en `app/Grillas/Grilla.php`
- [X] T010 Crear el trait `ServicioGrilla` con las acciones `datos()` y `exportar()` (respuesta `401`/`403` en JSON, autorización por política) en `app/Http/Controllers/Concerns/ServicioGrilla.php`
- [X] T011 [P] Crear el componente Blade `<x-ui.grilla>` (barra con contador, exportar y limpiar; `<table data-grilla ...>`; fila de filtros por columna; paginador del servidor de respaldo; estado vacío) en `resources/views/components/ui/grilla.blade.php`
- [X] T012 [P] Generalizar `resources/js/grilla/personas.js` en `resources/js/grilla/grilla.js`: activa DataTables en toda `table[data-grilla]`, lee las columnas y filtros de los atributos `data-*`, mantiene `deferLoading`, Responsive, idioma español y el aviso de error con reintento; actualizar la importación en `resources/js/app.js` y eliminar `personas.js`
- [X] T013 Refactorizar personas: crear `app/Grillas/PersonasGrilla.php` (columnas nombre, estado, celular, líder, sede, acciones), usarla en `PersonasController` (`index`, `datos`, `exportar` mediante `ServicioGrilla`), reescribir `resources/views/personas/index.blade.php` con `<x-ui.grilla>` y eliminar `app/Services/GrillaPersonas.php`
- [X] T014 Verificar que las pruebas de personas siguen en verde sin cambios de comportamiento (`GrillaDatosTest`, `PersonasBusquedaTest`, `FiltroEnCelulaTest`, `AislamientoPersonasTest`, `AlcancePorRolTest`) y ajustar solo nombres de clases
- [X] T015 Documentar el patrón (cómo crear una grilla en 4 pasos: clase `Grilla`, rutas `{m}.datos` y `{m}.exportar`, trait en el controlador, `<x-ui.grilla>` en la vista) en la sección «Grillas» de `README.md`

**Punto de control**: personas funciona igual sobre la base común; `php artisan test`, `npm test` y `npm run build` en verde.

---

## Fase 3: Historia de Usuario 1 - Todos los listados principales iguales (Prioridad: P1) MVP

**Objetivo**: los 9 listados restantes usan la grilla estándar con ordenar, filtros por columna, filas por página y paginador, respetando el alcance (RF-001 a RF-004, RF-006, RF-007, RF-010 a RF-012).

**Prueba independiente**: abrir cada listado, ordenar, filtrar por columna, cambiar filas por página y comprobar que no hay filas fuera del alcance del usuario.

### Pruebas

- [X] T016 [P] [HU1] Prueba de aislamiento por sede y por rol en las nueve grillas (pastor de otra sede, líder de célula, administrador en todas las sedes con columna «Sede») en `tests/Feature/Grillas/AlcanceGrillasTest.php`
- [X] T017 [P] [HU1] Prueba de conservación de parámetros GET existentes (`q`, `sede_id`, `estado` de solicitudes de clave) y de la primera página renderizada en el servidor en `tests/Feature/Grillas/RespaldoSinJsTest.php`

### Implementación (un listado por tarea; cada una incluye su clase, controlador, rutas `{m}.datos` y `{m}.exportar` antes de `/{m}/{id}`, vista y ajuste de pruebas existentes)

- [X] T018 [P] [HU1] Líderes: `app/Grillas/LideresGrilla.php` (nombre, personas, células, celular, sede, acciones; búsqueda `q`), `LideresController`, `resources/views/lideres/index.blade.php`, rutas en `routes/web.php`
- [X] T019 [P] [HU1] Personas sin líder: `app/Grillas/SinLiderGrilla.php` (incluye la acción de asignar líder por fila), `SinLiderController`, `resources/views/sin-lider/index.blade.php`, rutas en `routes/web.php`
- [X] T020 [P] [HU1] Células: `app/Grillas/CelulasGrilla.php` (nombre, líder, co-líder, reunión, estado, miembros, sede, acciones; se elimina la columna `ID` y la columna «Sede» duplicada), `CelulasController`, `resources/views/celulas/index.blade.php`, rutas en `routes/web.php`
- [X] T021 [P] [HU1] Encuentros: `app/Grillas/EncuentrosGrilla.php` (nombre, fecha, lugar, valor, estado, participantes, sede, acciones), `EncuentrosController`, `resources/views/encuentros/index.blade.php`, rutas en `routes/web.php`
- [X] T022 [P] [HU1] Eventos: `app/Grillas/EventosGrilla.php` (nombre, tipo, fecha, lugar, costo, estado, inscritos, sede, acciones), `EventosController`, `resources/views/eventos/index.blade.php`, rutas en `routes/web.php`
- [X] T023 [P] [HU1] Ministerios: `app/Grillas/MinisteriosGrilla.php` (nombre, encargado, estado, integrantes, sede, acciones), `MinisteriosController`, `resources/views/ministerios/index.blade.php`, rutas en `routes/web.php`
- [X] T024 [P] [HU1] Sedes: `app/Grillas/SedesGrilla.php` sobre la `Collection` de `SedeService::resumen()` (sede, ciudad, pastor, pastora, personas, líderes, células, estado), `SedesController`, `resources/views/sedes/index.blade.php`, rutas en `routes/web.php`
- [X] T025 [P] [HU1] Usuarios: `app/Grillas/UsuariosGrilla.php` (nombre, email, rol, sede, persona vinculada, clave, registrado, acciones; conserva el filtro `sede_id` del administrador), `UsersController`, `resources/views/users/index.blade.php`, rutas en `routes/web.php`
- [X] T026 [P] [HU1] Solicitudes de clave: `app/Grillas/SolicitudesClaveGrilla.php` (cuenta, rol, sede, pedida, estado, acciones; conserva los filtros `pendientes`, `vencidas`, `atendidas`), `SolicitudesClaveController`, `resources/views/claves/solicitudes.blade.php`, rutas en `routes/web.php`
- [X] T027 [HU1] Actualizar las pruebas existentes que dependen del marcado antiguo (`<th>Sede</th>`, `<th>ID</th>`, tarjetas, contador «resultados») en `tests/Feature/Interfaz/ListadosTest.php`, `tests/Feature/SedeActivaAdminTest.php`, `tests/Feature/Interfaz/AccesibilidadInterfazTest.php` y demás afectadas, sin debilitar sus aserciones de alcance

**Punto de control**: las 10 grillas funcionan con el contrato común; `php artisan test` en verde.

---

## Fase 4: Historia de Usuario 2 - Exportar CSV desde cada grilla (Prioridad: P2)

**Objetivo**: cada grilla exporta lo filtrado y ordenado, y los datos de acceso solo los exporta un administrador (RF-005, RF-005b).

**Prueba independiente**: aplicar un filtro, exportar y comprobar que el archivo trae todas las filas filtradas con tildes correctas; como no administrador, pedir la exportación de usuarios y recibir 403.

### Pruebas

- [X] T028 [P] [HU2] Prueba de exportación por grilla: filas filtradas completas (más que una página), orden vigente, BOM, sin HTML en las celdas, sin filas fuera del alcance en `tests/Feature/Grillas/ExportacionGrillasTest.php`
- [X] T029 [P] [HU2] Prueba de permisos: no administrador recibe 403 al pedir directamente `usuarios.exportar` y `solicitudes-clave.exportar`, el botón no aparece; el administrador exporta con los filtros actuales en `tests/Feature/Grillas/ExportacionRestringidaTest.php`

### Implementación

- [X] T030 [HU2] Declarar en cada `*Grilla` los valores exportables en texto plano (estados con su etiqueta, fechas formateadas, conteos) en `app/Grillas/*Grilla.php`
- [X] T031 [HU2] Restringir la exportación de usuarios y solicitudes de clave a administradores (implementado con `puedeExportar()` en `UsuariosGrilla` y `SolicitudesClaveGrilla`, en lugar de políticas nuevas) y aplicarlo en `ServicioGrilla` y en `<x-ui.grilla>` (`puedeExportar`)
- [X] T032 [HU2] Probar una exportación de 5 000 filas en una prueba de rendimiento acotada (sin cargar todo en memoria) en `tests/Feature/Grillas/ExportacionGrandeTest.php`

---

## Fase 5: Historia de Usuario 3 - Regla permanente para grillas futuras (Prioridad: P2)

**Objetivo**: que toda grilla nueva salga estándar y que el incumplimiento falle en las pruebas (RF-013 a RF-015).

**Prueba independiente**: introducir una tabla simple en un `index.blade.php` de prueba y ver que la prueba guarda falla.

- [X] T033 [P] [HU3] Prueba guarda que recorre `resources/views/*/index.blade.php` y falla si alguno contiene `<table` fuera de `<x-ui.grilla>` (lista de excepciones vacía, con mensaje que indica la guía del README) en `tests/Feature/Grillas/GuardaGrillaEstandarTest.php`
- [X] T034 [HU3] Redactar la constitución mínima en español (artefactos en español, aislamiento por sede, pruebas primero, mejora progresiva sin JavaScript, **grillas estándar para todo listado principal**) en `.specify/memory/constitution.md`
- [X] T035 [P] [HU3] Completar la guía de «Grillas» del README con un ejemplo mínimo de definición de grilla y la mención de la prueba guarda en `README.md`

---

## Fase 6: Historia de Usuario 4 - Teléfono y sin JavaScript (Prioridad: P3)

**Objetivo**: adaptación a pantallas estrechas y degradación sin script en todos los listados (RF-008, RF-009).

- [X] T036 [P] [HU4] Asignar `responsivePriority` por columna en cada `*Grilla` (primera columna y acciones con prioridad 1) en `app/Grillas/*Grilla.php`
- [X] T037 [P] [HU4] Ajustar `resources/css/grilla.css` para anchos estrechos (barra apilada, filtros por columna sin desbordar, paginador compacto) y verificar que no hay desplazamiento horizontal de la página
- [X] T038 [HU4] Prueba de que cada listado entrega la tabla del servidor con filas y paginador sin ejecutar JS y de que las acciones por fila están presentes en `tests/Feature/Grillas/RespaldoSinJsTest.php`

---

## Fase 7: Pulido y verificación

- [X] T039 Ejecutar `php artisan test`, `npm test` y `npm run build` y corregir lo que falle
- [X] T040 Verificar `quickstart.md` en el navegador con una copia de la base local (usuarios de ejemplo del `DatabaseSeeder`): los 9 listados, ordenar, filtrar, filas por página, exportar, rol limitado, ancho de teléfono y sin JavaScript; borrar archivos temporales (`.env.pruebaNNN`, copias de base)
- [X] T041 [P] Revisar que no queden referencias a `GrillaPersonas`, `personas.js` ni a tablas simples en vistas de listado (`grep` en `app/`, `resources/`, `tests/`)
- [X] T042 [P] Marcar la memoria del proyecto: actualizar la nota de arquitectura con la regla «todo listado principal usa la grilla estándar»

---

## Dependencias y orden de ejecución

- **Fase 1 → Fase 2 → Fases 3 a 6 → Fase 7**.
- Fase 2 bloquea todo: T007 → T008 → T009 → T010; T011 y T012 en paralelo con T008 a T010; T013 depende de T008 a T012.
- **HU1** (Fase 3) depende de la Fase 2; sus 9 tareas de listado (T018 a T026) son independientes entre sí; T027 va al final de la fase.
- **HU2** depende de HU1 solo para usuarios y solicitudes de clave (T031); el resto puede avanzar tras la Fase 2.
- **HU3** puede empezar tras la Fase 2, pero T033 solo queda en verde cuando termina HU1.
- **HU4** puede avanzar por listado a medida que se migra.

## Ejemplos de ejecución en paralelo

- Fase 2: T003, T004, T005, T006 (pruebas) y luego T007 con T011 y T012.
- HU1: T018 a T026 en paralelo (cada una toca su clase, su controlador, su vista; las rutas en `routes/web.php` se integran en secuencia).
- HU2: T028 y T029 en paralelo.

## Estrategia de implementación

1. **MVP**: Fases 1 y 2 más HU1 con 2 o 3 listados (por ejemplo líderes, células y ministerios) para validar la base antes de migrar el resto.
2. **Entrega incremental**: cada listado migrado se verifica con su prueba de contrato y `php artisan test` antes de pasar al siguiente.
3. **Cierre**: HU2, HU3 y HU4, luego pulido y verificación en navegador.
