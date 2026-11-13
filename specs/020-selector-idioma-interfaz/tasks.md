---

description: "Lista de tareas para la funcionalidad: Selector de idioma en la interfaz (español/inglés)"
---

# Tareas: Selector de idioma en la interfaz (español/inglés)

**Entrada**: Documentos de diseño desde `specs/020-selector-idioma-interfaz/`

**Prerrequisitos**: plan.md, spec.md, research.md, data-model.md, contracts/idioma-route.md, quickstart.md

**Pruebas**: El principio IV de la constitución ("Pruebas primero") exige pruebas automáticas para toda funcionalidad; por eso las tareas de prueba de cada historia NO son opcionales en este proyecto.

**Organización**: Las tareas se agrupan por historia de usuario para poder implementarlas y probarlas de forma independiente. Todas las rutas de archivo son relativas a `laravel_app/`.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: Se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[Historia]**: A qué historia de usuario pertenece la tarea (US1, US2, US3)

## Convenciones de ruta

Proyecto único Laravel + Blade (`laravel_app/`), sin frontend separado — ver `plan.md`, sección "Estructura del Proyecto".

---

## Fase 1: Preparación (Infraestructura Compartida)

**Propósito**: ajustes de configuración base, antes de tocar código de la funcionalidad.

- [X] T001 Cambiar `APP_LOCALE=en` → `APP_LOCALE=es` y `APP_FALLBACK_LOCALE=en` → `APP_FALLBACK_LOCALE=es` en `laravel_app/.env`, y `APP_FALLBACK_LOCALE=en` → `APP_FALLBACK_LOCALE=es` en `laravel_app/.env.example` (RF-004, RF-006; `config/app.php` ya lee estas variables, no requiere cambios).

---

## Fase 2: Fundacional (Prerrequisitos Bloqueantes)

**Propósito**: infraestructura que DEBE estar completa antes de empezar cualquier historia de usuario.

**⚠️ CRÍTICO**: ninguna historia de usuario puede empezar hasta que esta fase esté terminada.

- [X] T002 Crear la migración `laravel_app/database/migrations/2026_11_13_000000_add_idioma_a_users_table.php` que agrega a `users` la columna `idioma`: string corto, **obligatoria** (no nula), **valor por defecto `'es'`** — ver `data-model.md`: "`idioma` DEBE ser `es` o `en`; por defecto `es`".
- [X] T003 En `laravel_app/app/Models/User.php`: agregar `'idioma'` a la lista del atributo `#[Fillable([...])]`, agregar una entrada para `idioma` en `camposHistorial()` (mismo patrón que `email`/`role`, para que el cambio quede en el historial de auditoría existente), y validar en la capa de aplicación que solo se acepten los valores `es`/`en` (la restricción vive en la validación del controlador de la Fase 3, no en el modelo).
- [X] T004 Crear `laravel_app/app/Http/Middleware/EstableceIdioma.php` siguiendo el patrón de `app/Http/Middleware/ResuelveSede.php`: en `handle()`, toma el usuario autenticado (si existe) y llama a `App::setLocale($usuario->idioma ?? config('app.locale'))` antes de continuar la petición.
- [X] T005 Registrar `EstableceIdioma` en la pila de middleware `web` de `laravel_app/bootstrap/app.php`, junto a `ResuelveSede` y `ExigeCambioDeClave` (`$middleware->web(append: [...])`).
- [X] T006 [P] Crear `laravel_app/lang/en/validation.php` con la traducción al inglés de cada clave de `laravel_app/lang/es/validation.php` (incluida la sección `attributes`), para que los mensajes de validación de formularios también cambien de idioma (RF-002).
- [X] T007 [P] Crear los archivos `laravel_app/lang/es/interfaz.php` y `laravel_app/lang/en/interfaz.php`, cada uno devolviendo un arreglo PHP vacío (`return [];`), como destino de la extracción de texto de las vistas.
- [X] T008 Crear `laravel_app/app/Console/Commands/ExtraerTextosInterfaz.php` (comando Artisan de un solo uso, ej. `php artisan interfaz:extraer {rutas*}`): recorre los archivos `.blade.php` indicados, detecta el texto literal en español fuera de código Blade/PHP (etiquetas, botones, mensajes), genera una clave legible por archivo+texto, agrega la entrada correspondiente a `lang/es/interfaz.php` **con el texto exacto que tiene hoy la vista** (RF-005: no se reescribe el español a mano) y reemplaza ese texto en la vista por `{{ __('interfaz.clave') }}`. Dejar documentado en el propio comando que es una herramienta de una sola vez.

**Punto de control**: la base está lista — ya se puede empezar con las historias de usuario.

---

## Fase 3: Historia de Usuario 1 - Cambiar el idioma de toda la interfaz (Prioridad: P1) 🎯 MVP

**Objetivo**: un control visible e interactivo que, al pulsarse, cambia de inmediato el idioma de todo el "chrome" de la interfaz (menú lateral, encabezado, botones y etiquetas comunes) y de al menos las pantallas principales.

**Prueba independiente**: iniciar sesión, abrir cualquier pantalla, pulsar el control de idioma y comprobar que el menú, los botones y las etiquetas visibles cambian de idioma de inmediato; volver a pulsar el control contrario y comprobar que el texto en español vuelve a ser idéntico al que había antes de esta funcionalidad.

### Pruebas para la Historia de Usuario 1

> **NOTA**: escribir estas pruebas primero y comprobar que fallan antes de implementar (principio IV).

- [X] T009 [P] [US1] Prueba de característica en `laravel_app/tests/Feature/Interfaz/SelectorIdiomaTest.php`: un usuario autenticado hace `POST /idioma` con `idioma=en` (ver contrato en `contracts/idioma-route.md`); la respuesta redirige y la siguiente petición a cualquier ruta interna devuelve HTML cuyo `<html lang="...">` es `en` y contiene al menos una etiqueta traducida del menú lateral.
- [X] T010 [P] [US1] Prueba en el mismo archivo: `POST /idioma` con un valor distinto de `es`/`en` responde con error de validación (422) y no cambia el idioma actual del usuario.

### Implementación para la Historia de Usuario 1

- [X] T011 [US1] Crear `laravel_app/app/Http/Controllers/IdiomaController.php` con el método `update()`: valida `idioma` con `Rule::in(['es', 'en'])`, lo guarda en el usuario autenticado y responde `redirect()->back()` (mismo patrón que `SedeActivaController::update()`); ver contrato completo en `contracts/idioma-route.md`.
- [X] T012 [US1] En `laravel_app/routes/web.php`, agregar `Route::post('/idioma', [IdiomaController::class, 'update'])->name('idioma.update')` dentro del grupo de rutas autenticadas; y en `laravel_app/app/Http/Middleware/ResuelveSede.php`, agregar `'idioma.update'` a la constante `EXENTAS` (mismo motivo que `'sede-activa.update'`: un administrador en "Todas las sedes" debe poder cambiar su idioma sin tener una sede activa).
- [X] T013 [US1] Agregar el control de idioma al header de `laravel_app/resources/views/layouts/admin.blade.php`, junto al selector de sede activa existente: dos formularios `<form method="POST" action="{{ route('idioma.update') }}">` con `@csrf` y un `<button type="submit" name="idioma" value="es">`/`value="en"` cada uno (bandera o texto "ES"/"EN"), con un tamaño de al menos 44×44 px y **sin** depender de `onchange` ni de JavaScript (RF-001, RF-007, RF-008).
- [X] T014 [US1] Cambiar `<html lang="es" ...>` por `<html lang="{{ app()->getLocale() }}" ...>` en `laravel_app/resources/views/layouts/admin.blade.php` y en `laravel_app/resources/views/layouts/auth.blade.php`.
- [X] T015 [US1] Ejecutar el comando de extracción (T008) sobre `resources/views/layouts/`, `resources/views/partials/` y `resources/views/components/` (omitiendo las vistas públicas ya excluidas del alcance, ver Fase 5) y revisar manualmente las claves generadas en `lang/es/interfaz.php`.
- [X] T016 [US1] Completar `laravel_app/lang/en/interfaz.php` con la traducción al inglés de cada clave generada en T015.
- [X] T017 [US1] Corregir `laravel_app/config/menu.php` y `laravel_app/app/Support/Menu.php` para que las etiquetas del menú lateral (`titulo`, `etiqueta`) dejen de ser texto fijo en español y pasen a resolverse con `__()` **dentro de `Menu::secciones()`** (que se ejecuta en cada petición), usando nuevas claves en `lang/es/menu.php`/`lang/en/menu.php` indexadas por la `clave`/`ruta` ya existente en `config/menu.php`. Importante: no llamar `__()` directamente dentro de `config/menu.php`, porque un `config:cache` futuro congelaría el idioma del momento en que se generó la caché.
- [X] T018 [US1] Ejecutar el comando de extracción (T008) sobre dos módulos representativos de distintos roles, por ejemplo `resources/views/celulas/` y `resources/views/users/`, y completar sus claves en `lang/en/interfaz.php`, para que la prueba independiente de esta historia cubra pantallas reales además del layout.

**Punto de control**: en este punto, la Historia de Usuario 1 debe ser completamente funcional y comprobable de forma independiente.

---

## Fase 4: Historia de Usuario 2 - Recordar la preferencia de idioma (Prioridad: P2)

**Objetivo**: la preferencia de idioma persiste en el perfil del usuario (no en una cookie) y se aplica automáticamente al volver a iniciar sesión; los usuarios sin preferencia ven español por defecto.

**Prueba independiente**: elegir inglés, cerrar sesión, volver a iniciar sesión y comprobar que la interfaz sigue en inglés sin volver a elegirlo; iniciar sesión con un usuario que nunca cambió su idioma y comprobar que ve español.

### Pruebas para la Historia de Usuario 2

- [X] T019 [P] [US2] Prueba en `laravel_app/tests/Feature/Interfaz/SelectorIdiomaTest.php`: un usuario con `idioma = 'en'` ya guardado cierra sesión e inicia sesión de nuevo; la primera petición autenticada después de iniciar sesión ya responde en inglés, sin que el usuario tenga que volver a elegir el idioma.
- [X] T020 [P] [US2] Prueba: un usuario creado sin indicar `idioma` explícitamente tiene `idioma === 'es'` (el valor por defecto de la migración T002), confirmando RF-004.

### Implementación para la Historia de Usuario 2

- [X] T021 [US2] Confirmar que `EstableceIdioma` (T004) lee el idioma siempre desde la columna persistente del usuario autenticado (no desde sesión ni cookie), de modo que lo construido en la Historia 1 ya cumple la persistencia entre sesiones y dispositivos exigida por RF-003; ajustar si en T004 se hubiera usado por error un valor de sesión.
- [X] T022 [US2] Revisar los flujos existentes de alta de usuarios (alta manual, "usuario = miembro", *factories* de prueba) y confirmar que ninguno deja `idioma` en `null` o en un valor fuera de `es`/`en`; si alguno no usa el valor por defecto de la migración, asignar `'es'` explícitamente.

**Punto de control**: en este punto, las Historias de Usuario 1 y 2 deben funcionar juntas y de forma independiente.

---

## Fase 5: Historia de Usuario 3 - Cobertura completa y consistente de los dos idiomas (Prioridad: P3)

**Objetivo**: el 100% de las pantallas internas en alcance (ver RF-009) muestran su texto en el idioma elegido, con respaldo a español cuando falta una traducción, y las grillas estándar (DataTables) también cambian de idioma.

**Prueba independiente**: recorrer cada pantalla principal del CRM en ambos idiomas y comprobar que ningún texto de interfaz queda en el idioma contrario; forzar una clave sin traducción al inglés y comprobar que se muestra en español en vez de vacía.

### Pruebas para la Historia de Usuario 3

- [X] T023 [P] [US3] Prueba en `laravel_app/tests/Feature/Interfaz/RespaldoIdiomaTest.php`: con idioma `en`, una clave presente solo en `lang/es/interfaz.php` (ausente en `lang/en/interfaz.php`) se muestra en español gracias a `fallback_locale`, en vez de aparecer vacía o como la clave cruda (RF-006).
- [X] T024 [P] [US3] Prueba Vitest en `laravel_app/resources/js/grilla/grilla.test.js`: con `document.documentElement.lang = 'en'`, la grilla usa el arreglo de idioma en inglés en vez de `ESPAÑOL`.

### Implementación para la Historia de Usuario 3

- [X] T025 [US3] [P] Ejecutar el comando de extracción (T008) sobre el resto de los directorios de vistas internas no cubiertos en la Fase 3: `celulas` (resto), `claves`, `control-cambios`, `cuenta`, `encuentros`, `eventos`, `historial`, `lideres`, `ministerios`, `niveles`, `organigrama`, `personas`, `roles`, `sedes`, `sin-lider`, `traslados`, `users` (resto), `auth`, `errors`, y únicamente `registro-miembros/index.blade.php` (el resto de ese directorio — `cerrado`, `confirmacion`, `formulario` — es público y queda fuera de alcance por RF-009, igual que todo `resources/views/registro/`). Al ser archivos distintos por subcarpeta, puede dividirse en varias ejecuciones paralelas.
- [X] T026 [US3] Completar `laravel_app/lang/en/interfaz.php` con la traducción al inglés de todas las claves generadas en T025.
- [X] T027 [US3] En `laravel_app/resources/js/grilla/grilla.js`, agregar un objeto `INGLES` con las mismas claves que `ESPAÑOL` (línea 12) pero en inglés, y elegir entre `ESPAÑOL`/`INGLES` según `document.documentElement.lang` al construir la opción `language` de `DataTable` (línea 139), en vez de usar siempre `ESPAÑOL`.
- [X] T028 [US3] Confirmar que `document.documentElement.lang` refleja el idioma activo gracias al cambio de T014 (sin necesidad de una variable global nueva) y que `grilla.js` lo lee correctamente en ambos idiomas.
- [X] T029 [US3] Revisar los mensajes flash generados por los controladores existentes (ej. `with('success', ...)` en `SedeActivaController` y controladores similares) y envolver con `__()` los que sean texto fijo de interfaz, ya que el comando de extracción (T008) solo cubre archivos `.blade.php`, no código PHP de controladores.
- [X] T030 [US3] Auditoría final: recorrer manualmente cada pantalla principal cubierta en T015, T018 y T025, en ambos idiomas, confirmando que ningún texto de interfaz queda en el idioma contrario (CE-002).

**Punto de control**: las tres historias de usuario deben funcionar de forma independiente y en conjunto.

---

## Fase Final: Pulido y Aspectos Transversales

**Propósito**: validaciones que afectan a toda la funcionalidad, no a una historia en particular.

- [X] T031 [P] Ejecutar `npm run test:movil` y confirmar que el nuevo control de idioma cumple el tamaño táctil mínimo sin necesidad de agregar una excepción nueva en `tests/movil/excepciones.json` (RF-008).
- [X] T032 [P] Ejecutar `php artisan test` completo y `npm test` completo; confirmar cero regresiones en las pruebas existentes (CE-004).
- [X] T033 Recorrer los 6 escenarios de `specs/020-selector-idioma-interfaz/quickstart.md` y documentar cualquier ajuste necesario.
- [X] T034 Eliminar `app/Console/Commands/ExtraerTextosInterfaz.php` (T008) una vez completada toda la extracción (T015, T018, T025), por ser una herramienta de una sola vez que ya cumplió su propósito (principio de Simplicidad).

---

## Dependencias y Orden de Ejecución

### Dependencias entre Fases

- **Preparación (Fase 1)**: sin dependencias — puede empezar de inmediato.
- **Fundacional (Fase 2)**: depende de la Fase 1 — BLOQUEA todas las historias de usuario.
- **Historias de Usuario (Fase 3+)**: todas dependen de que la Fase Fundacional esté completa.
  - US2 depende de que exista el control y la ruta de US1 (T011-T013) para tener algo que probar, aunque su persistencia ya queda resuelta por el diseño de T002-T004.
  - US3 depende de US1 (reutiliza el comando de extracción T008 y el cambio de `<html lang>` de T014).
- **Pulido (Fase Final)**: depende de que las historias que se quieran entregar estén completas.

### Dentro de Cada Historia

- Pruebas (T009-T010, T019-T020, T023-T024) se escriben y deben fallar antes de implementar (principio IV).
- Modelo/migración antes que middleware; middleware antes que controlador; controlador antes que vista.
- Cada historia se da por completa antes de pasar a la siguiente en el orden P1 → P2 → P3.

### Oportunidades de Paralelismo

- T006 y T007 (Fase 2) se pueden hacer en paralelo (archivos distintos).
- T009 y T010 (pruebas de US1) se pueden escribir en paralelo.
- T019 y T020 (pruebas de US2) se pueden escribir en paralelo.
- T023 y T024 (pruebas de US3) se pueden escribir en paralelo.
- T025 (extracción del resto de las vistas) se puede dividir por subcarpeta entre varias ejecuciones o personas.

---

## Ejemplo de Paralelismo: Historia de Usuario 1

```bash
# Lanzar juntas las pruebas de la Historia de Usuario 1:
Tarea: "Prueba de cambio de idioma en tests/Feature/Interfaz/SelectorIdiomaTest.php (T009)"
Tarea: "Prueba de valor inválido de idioma en tests/Feature/Interfaz/SelectorIdiomaTest.php (T010)"
```

---

## Estrategia de Implementación

### MVP Primero (solo Historia de Usuario 1)

1. Completar la Fase 1: Preparación.
2. Completar la Fase 2: Fundacional (CRÍTICO — bloquea todas las historias).
3. Completar la Fase 3: Historia de Usuario 1.
4. **DETENERSE Y VALIDAR**: probar la Historia 1 de forma independiente (quickstart.md, escenarios 1 y 5).
5. Mostrar/desplegar si está listo — ya es una funcionalidad útil por sí sola.

### Entrega Incremental

1. Preparación + Fundacional → base lista.
2. Agregar Historia 1 → probar de forma independiente → MVP.
3. Agregar Historia 2 → probar de forma independiente (persistencia entre sesiones).
4. Agregar Historia 3 → probar de forma independiente (cobertura completa y grillas).
5. Cada historia agrega valor sin romper las anteriores.

## Notas

- [P] = archivos distintos, sin dependencias pendientes.
- La etiqueta de historia conecta cada tarea con su historia de usuario para trazabilidad.
- Verificar que las pruebas fallan antes de implementar.
- Detenerse en cada punto de control para validar la historia de forma independiente.
- Evitar: tareas vagas, conflictos de archivo repetido, dependencias entre historias que rompan su independencia.
