---

description: "Lista de tareas: Optimización de Personas, Líderes, Células y Ministerios"
---

# Tareas: Optimización de Personas, Líderes, Células y Ministerios

**Entrada**: documentos de diseño en `specs/006-optimizar-personas-celulas/`
**Requisitos previos**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/rutas-y-componentes.md`, `quickstart.md`

**Pruebas**: se incluyen (plan D10): hay una migración que retira datos, búsquedas con alcance por sede y reglas de pertenencia que cambian.

**Formato**: `[ID] [P?] [Historia] Descripción con ruta`. `[P]` = se puede hacer en paralelo (archivos distintos, sin dependencia pendiente). Las rutas son relativas a la raíz del repositorio; el código está en `laravel_app/`.

**Reglas de oro**:
- No perder ninguna persona, líder, célula ni ministerio (FR-014): la migración solo retira el dato de red.
- El aislamiento por sede y los permisos no cambian: toda búsqueda usa `AlcanceService`.
- Migraciones reversibles; nunca `migrate:fresh` sobre la base local; probar sobre una copia o una base temporal.
- Textos en español; los componentes `x-form.*` y `x-ui.*` existentes se reutilizan.
- Evitar heredocs largos en Bash: escribir archivos con la herramienta de escritura.

**Orden de las historias**: Fundamentos (estado «En célula», buscador y componentes) → US1 (una tarjeta) → US2 (quitar Red) → US3 (designar líder) → US5 (células) → US4 (grilla del líder) → US7 (ministerios y menú). US6 (estado «En célula») se hace dentro de Fundamentos porque US3, US5 y US7 dependen de él; sus pruebas van en su propia fase. Los números son los de `spec.md`.

## Fase 1: Preparación

- [X] T001 Anotar la línea base de datos con una copia de la base local (`php artisan tinker` sobre una copia en el directorio temporal): número de personas, líderes (`es_lider`), células y ministerios; guardarlo en `specs/006-optimizar-personas-celulas/quickstart.md` (sección «Línea base de datos») para compararlo tras la migración (SC-007)
- [X] T002 [P] Medir el tiempo de `/personas`, `/celulas/create` y `/ministerios/create` con una base temporal de 500 personas de una sede (fábrica `Persona::factory()->count(500)`), anotarlo en `quickstart.md` como línea base de rendimiento

---

## Fase 2: Fundamentos (bloquea todas las historias)

**⚠️ CRÍTICO**: ninguna historia puede empezar hasta terminar esta fase.

- [X] T003 Añadir el estado `celula` («En célula») a `laravel_app/app/Models/Persona.php` (`ESTADOS`), excluirlo de `scopeSinLider` (`estado` distinto de `inactivo` y de `celula`) y mantenerlo como activo en `scopeActivas`; añadir `celula` => ['En célula', 'dark'] en `laravel_app/app/Support/Estados.php`
- [X] T004 [P] Actualizar `laravel_app/app/Http/Requests/PersonaRequest.php`: el estado acepta `celula`; el documento sigue obligatorio solo para `miembro_activo` y para líderes, y opcional para `celula`, `visitante` y `nuevo_creyente`
- [X] T005 [P] Prueba `laravel_app/tests/Feature/Personas/EstadoEnCelulaTest.php`: crear una persona con estado `celula` sin documento (con nombres, apellidos y celular) funciona; `estado_etiqueta` es «En célula»; `scopeActivas` la incluye; `scopeSinLider` no la incluye; la insignia usa `Estados::color('celula')`; actualizar `laravel_app/tests/Feature/Interfaz/EstadosTest.php` para incluir el estado nuevo
- [X] T006 Crear `laravel_app/app/Services/BuscadorPersonas.php` con `buscar(User $user, array $filtros): array` que devuelve `['resultados' => …, 'mas' => bool]`: parte de `AlcanceService::personasVisibles($user)`, aplica `Persona::buscar($q)` (mínimo 2 caracteres; con menos devuelve vacío), `activas` (por defecto verdadero: `estado` distinto de `inactivo`), `sin_lideres` (`es_lider` falso), `excluir` (ids a omitir), ordena `apellidos` y `nombres`, pagina de 20 en 20 con `pagina` y cada resultado trae `id`, `texto` (nombre completo), `apoyo` («CC 1234 · 310 555 9999» o «Sin documento · celular»), `estado` y `sinDocumento`
- [X] T007 [P] Prueba `laravel_app/tests/Feature/Personas/BuscadorPersonasTest.php` (con `EscenarioDosSedes`): una búsqueda de 1 letra devuelve vacío; por nombre, apellido, documento y celular encuentra a la persona; no devuelve inactivas ni, con `sin_lideres`, líderes; `excluir` omite ids; pagina (21 personas → 20 y `mas` verdadero, segunda página 1); un pastor de la sede A nunca ve personas de la B; un líder solo ve su ámbito; con 500 personas responde en menos de 1 segundo
- [X] T008 Añadir en `laravel_app/app/Http/Controllers/PersonasController.php` la acción `buscar(Request $request)` (JSON, exige sesión, usa `BuscadorPersonas`, cabecera `Cache-Control: no-store`) y registrar `GET /personas/buscar` (`personas.buscar`) **antes** de `personas/{persona}` en `laravel_app/routes/web.php`
- [X] T009 Añadir en `PersonasController.php` la acción `rapida(Request $request)`: autoriza `crear`; valida `nombres`, `apellidos` y `celular` o `email` (mismas reglas de `PersonaRequest`); fuerza `estado = celula`; si quien crea es líder (`isLiderCelula`) la deja a su cargo (`lider_id`) como en `store`; aplica el aviso de duplicados existente y devuelve 409 `{duplicado: {id, nombre}}` salvo que llegue `confirmar_duplicado`; responde 201 con `{id, texto, apoyo, estado}` y registra el cambio `creada` con `LiderazgoService::registrarCambio`; registrar `POST /personas/rapida` (`personas.rapida`) en `routes/web.php`
- [X] T010 [P] Prueba `laravel_app/tests/Feature/Personas/AltaRapidaTest.php`: crea con estado `celula` y responde 201 con el formato del contrato; sin celular ni correo responde 422; un posible duplicado responde 409 y con `confirmar_duplicado` crea; un líder la deja a su cargo; un usuario sin permiso `crear` recibe 403; la persona queda en la sede de quien la crea
- [X] T011 Crear `laravel_app/resources/js/forms/buscador-remoto.js`: exporta `iniciarBuscadorRemoto(select, opciones)` que activa Select2 con `ajax` (retardo 250 ms, `minimumInputLength` 2, paginación de 20, parámetros `q`, `pagina`, `activas`, `sin_lideres`, `excluir` tomados de `data-filtros`), lee la fuente de `data-fuente`, muestra `texto` y `apoyo` (con la insignia de estado) y conserva las opciones ya seleccionadas del HTML; y la función pura `parametrosDeBusqueda(termino, pagina, filtros)`
- [X] T012 [P] Prueba Vitest `laravel_app/resources/js/forms/buscador-remoto.test.js`: `parametrosDeBusqueda` arma la consulta con filtros y excluye lo ya seleccionado; con menos de 2 letras no consulta; procesa `mas` para la paginación; no falla si no hay jQuery
- [X] T013 Actualizar `laravel_app/resources/js/forms/selectores.js` para delegar en `iniciarBuscadorRemoto` cuando el `<select>` tenga `data-fuente` y mantener el comportamiento local actual en los demás casos; registrar el módulo en `laravel_app/resources/js/forms/index.js`
- [X] T014 [P] Actualizar `laravel_app/resources/views/components/form/select.blade.php` y `multiselect.blade.php` con las propiedades `fuente`, `filtros` y `crear` (generan `data-fuente`, `data-filtros`, `data-crear`); con `fuente` no se vuelcan todas las opciones: solo las seleccionadas; sin JavaScript conservan el `<select>` nativo con lo ya seleccionado
- [X] T015 Crear `laravel_app/resources/js/forms/alta-rapida.js`: añade al multiselect con `data-crear` un botón «Crear persona nueva» que abre un diálogo de SweetAlert2 (nombres, apellidos, celular, correo), envía `POST` con el token CSRF a la URL de `data-crear`, maneja 201 (añade la persona como opción seleccionada), 409 (pregunta si se confirma el duplicado y reenvía con `confirmar_duplicado`) y 422 (muestra los errores en el diálogo); y su prueba `laravel_app/resources/js/forms/alta-rapida.test.js`
- [X] T016 Ejecutar `php artisan test`, `npm test` y `npm run build`; corregir regresiones (PHPUnit 325 y Vitest 53 deben seguir pasando)

**Punto de control**: estado «En célula», buscador, alta rápida y componentes remotos listos y probados.

---

## Fase 3: Historia de usuario 1 - Formulario de personas con una sola tarjeta (Prioridad: P1) 🎯 MVP

**Objetivo**: una única tarjeta «Datos personales» con celular, correo, estado, tipo y número de documento (FR-001 a FR-003).

**Prueba independiente**: abrir crear y editar persona y comprobar la tarjeta única y que guardar sigue funcionando.

- [X] T017 [P] [US1] Prueba `laravel_app/tests/Feature/Personas/TarjetaUnicaTest.php`: el formulario de crear contiene en `#seccion-personal` los campos `nombres`, `apellidos`, `fecha_nacimiento`, `celular`, `email`, `estado`, `tipo_documento` y `numero_documento`; no existen las secciones `#seccion-contacto` ni `#seccion-estado-documento`; el estado ofrece «En célula»; documento obligatorio solo con `miembro_activo`; editar muestra los valores en la misma tarjeta; la ficha agrupa contacto, estado y documento en el bloque de datos personales; guardar con los mismos datos que antes sigue funcionando (celular o correo obligatorio al crear, aviso de duplicados)
- [X] T018 [US1] Fusionar las secciones «Contacto» y «Estado y documento» dentro de «Datos personales» en `laravel_app/resources/views/personas/_form.blade.php` conservando `data-*`, máscaras, ayudas y `requeridoSi` del documento (obligatorio según el estado); ajustar los títulos y descripciones
- [X] T019 [P] [US1] Reorganizar `laravel_app/resources/views/personas/show.blade.php` para mostrar celular, correo, estado, tipo y número de documento dentro del bloque de datos personales, sin tarjetas separadas
- [X] T020 [P] [US1] Ajustar las pruebas existentes que dependían de las tarjetas eliminadas (`laravel_app/tests/Feature/Formularios/EstructuraFormulariosTest.php`, `CamposAyudaTest.php`, `AccesibilidadFormulariosTest.php`) y `laravel_app/tests/Support/RecorreFormularios.php` si listan secciones
- [X] T021 [US1] Ejecutar `php artisan test --filter="Personas|Formularios"`; corregir hasta verde

---

## Fase 4: Historia de usuario 2 - Quitar el concepto «Red» (Prioridad: P1)

**Objetivo**: ninguna mención ni dato de red en la aplicación, sin perder personas ni líderes (FR-004, FR-014).

**Prueba independiente**: recorrer menú, líderes, personas, tablero y ayudas sin encontrar red; comparar conteos tras migrar.

- [X] T022 [P] [US2] Prueba de guardia `laravel_app/tests/Feature/Personas/SinRedTest.php`: ninguna ruta se llama `redes.*`; `resources/`, `app/` y `config/` no contienen `redLider`, `red_id`, `Red::` ni la palabra «Redes» en vistas, menú ni ayudas (con lista de excepciones para migraciones antiguas y `specs/`); el menú no tiene «Redes»; el formulario de persona y el de designar líder no tienen campo `red_id`; el tablero no tiene «Personas por red»
- [X] T023 [P] [US2] Prueba de migración `laravel_app/tests/Feature/Migraciones/DropRedesTest.php`: con datos de personas, líderes, células y ministerios con red asignada, aplicar la migración deja los mismos conteos, la tabla `redes` y la columna `personas.red_id` ya no existen y `down()` las recrea vacías
- [X] T024 [US2] Crear la migración `laravel_app/database/migrations/2026_10_25_000001_drop_redes.php`: quita la clave foránea y la columna `personas.red_id` (con `dropConstrainedForeignId` y comprobando `hasColumn`) y la tabla `redes` (`dropIfExists`); `down()` recrea `redes` (`sede_id`, `nombre`, `clave`, `activa`, índice `redes_sede_clave_unique`) y `personas.red_id` nulos; quitar `redes` de `laravel_app/app/Support/MigracionSedes.php` (`TABLAS`) y ajustar `tests/Unit/ModelosConSedeTest.php`
- [X] T025 [US2] Eliminar `laravel_app/app/Models/Red.php`, `laravel_app/app/Http/Controllers/RedesController.php`, `laravel_app/resources/views/redes/` y las rutas `redes.*` de `laravel_app/routes/web.php`; quitar la opción «Redes» de `laravel_app/config/menu.php`
- [X] T026 [US2] Quitar la red del código: `Persona::redLider()`, `red_id` en `$fillable` y consultas de `laravel_app/app/Models/Persona.php`; `laravel_app/app/Http/Requests/PersonaRequest.php`; `laravel_app/app/Http/Controllers/PersonasController.php` (filtros, `with('redLider')`, `datosFormulario`, `datosGuardables`); `LideresController.php`; `SinLiderController.php`; `laravel_app/app/Services/AlcanceService.php` (`redesVisibles`); `LiderazgoService.php` (`designarLider` sin red y las trazas `red_id`); `SedeService.php` (`Red::class` en `tieneDatos`/`eliminar`/`resumen`); `AuthController.php` (consulta «Personas por red» del tablero)
- [X] T027 [P] [US2] Quitar la red de las vistas: `laravel_app/resources/views/personas/{_form,index,show}.blade.php`, `lideres/{designar,index,show}.blade.php`, `encuentros/show.blade.php`, `ministerios/show.blade.php`, `partials/ayuda-terminos.blade.php`, `sin-lider/index.blade.php` y el bloque «Personas por red» de `dashboard.blade.php`
- [X] T028 [P] [US2] Actualizar `laravel_app/database/factories/PersonaFactory.php` (`lider()` sin red), `laravel_app/database/seeders/DatabaseSeeder.php` (sin redes), `laravel_app/app/Support/MigracionRedesZonas.php` (retirar `clave()` si ya nadie la usa o dejarla solo si hace falta) y `README.md` (retirar la mención de Redes)
- [X] T029 [US2] Ajustar todas las pruebas que usaban redes (`tests/Feature/CatalogosTest.php`, `AlcancePorRolTest.php`, `LiderazgoTest.php`, `TrasladoPersonaTest.php`, `Aislamiento/*`, `MigracionDatosTest.php`, `MigracionSedesTest.php`, `tests/Support/EscenarioDosSedes.php`, `PersonaFactory`) hasta que `php artisan test` quede en verde
- [X] T030 [US2] Probar la migración sobre una **copia** de la base local (no sobre la original), comparar los conteos con la línea base de T001 y anotar el resultado en `quickstart.md`

---

## Fase 5: Historia de usuario 6 - Estado «En célula» en listados y formularios (Prioridad: P1)

**Objetivo**: el estado nuevo se puede elegir, filtrar y distinguir (FR-006).

**Prueba independiente**: crear una persona «En célula», filtrarla y comprobar que no sale en «Sin líder asignado».

- [X] T031 [P] [US6] Prueba `laravel_app/tests/Feature/Personas/FiltroEnCelulaTest.php`: el filtro de estado del listado de personas ofrece «En célula» y devuelve solo esas personas; la insignia se dibuja con `x-ui.estado`; no aparece en `/sin-lider`; una persona «En célula» puede cambiar a miembro activo (pidiendo documento) sin perder su pertenencia a una célula
- [X] T032 [US6] Incluir `celula` en las opciones de estado del formulario (`laravel_app/resources/views/personas/_form.blade.php`) y del filtro (`personas/index.blade.php`) usando `Persona::ESTADOS`, y mostrar la insignia correspondiente en listado, ficha y tarjeta móvil (`partials/persona-tarjeta.blade.php`)

---

## Fase 6: Historia de usuario 3 - Designar líder con autocompletado (Prioridad: P1)

**Objetivo**: elegir a la persona escribiendo en un campo con autocompletado de personas activas (FR-005).

**Prueba independiente**: en «Designar líder», escribir parte de un nombre, elegir una persona y designarla.

- [X] T033 [P] [US3] Prueba `laravel_app/tests/Feature/Lideres/DesignarConAutocompletadoTest.php`: la pantalla trae un `select` con `data-fuente` hacia `personas.buscar` y `data-filtros` con `sin_lideres` y `activas`; no existe el botón «Buscar» ni el campo `red_id`; designar con `persona_id` marca `es_lider`; una persona inactiva o que ya es líder es rechazada por el servidor; sin documento se rechaza con el mensaje existente; un pastor no puede designar a una persona de otra sede (404)
- [X] T034 [US3] Rediseñar `laravel_app/resources/views/lideres/designar.blade.php`: un solo formulario con `x-form.select` remoto (`fuente` a `personas.buscar`, `filtros` `activas` y `sin_lideres`), aviso inmediato si el resultado elegido tiene `sinDocumento` (con enlace a editar a la persona) y botón «Designar líder»; sin red
- [X] T035 [US3] Actualizar `laravel_app/app/Http/Controllers/LideresController.php` (`designar` ya no consulta candidatas; `storeDesignar` valida solo `persona_id`, exige que sea activa, visible y no líder) y `LiderazgoService::designarLider(Persona $persona, ?User $por)`
- [X] T036 [US3] Ejecutar `php artisan test --filter="Lideres|Liderazgo"`; corregir hasta verde

---

## Fase 7: Historia de usuario 5 - Integrantes de células, incluso creándolos (Prioridad: P1)

**Objetivo**: registrar y quitar integrantes con multiselect autocompletado y crear personas desde la célula; sin depender del líder directo (FR-008 a FR-010, D4).

**Prueba independiente**: crear una célula, elegir integrantes buscando por nombre, crear uno nuevo desde la célula y comprobar que quedó como integrante.

- [X] T037 [P] [US5] Prueba `laravel_app/tests/Feature/Celulas/IntegrantesTest.php` (con `EscenarioDosSedes`): crear una célula con `integrantes[]` los sincroniza con `fecha_ingreso` de hoy; editar conserva la fecha de quienes ya estaban, añade y quita; quitar a alguien no lo borra como persona; añadir a una célula a una persona cuyo líder directo es otro o nulo funciona y **no cambia su `lider_id`**; una persona inactiva no se acepta como nueva; una persona de otra sede se rechaza (422/404); `celulas.miembros.store` acepta `personas[]`; la lista de asistencia de la ficha incluye a los integrantes; la ficha de la célula y el formulario ofrecen el multiselect remoto y el botón «Crear persona nueva»
- [X] T038 [US5] Actualizar `laravel_app/app/Http/Controllers/CelulasController.php`: `store` y `update` validan `integrantes` (`array`, cada id existente en la sede con `app(SedeContext::class)->existe('personas')`) y sincronizan con `sync` preservando `fecha_ingreso` de los existentes (`syncWithoutDetaching` para los nuevos con la fecha de hoy y `detach` de los retirados, sin tocar integrantes inactivos que no se enviaron); `agregarMiembro` acepta `personas[]` y **elimina** la comprobación de `lider_id` y su mensaje; `quitarMiembro` se mantiene
- [X] T039 [US5] Actualizar `laravel_app/resources/views/celulas/_form.blade.php` con la sección «Integrantes» (`x-form.multiselect name="integrantes[]"` con `fuente` a `personas.buscar`, `filtros` `activas`, `crear` a `personas.rapida` y los integrantes actuales cargados) y `celulas/create.blade.php`/`edit.blade.php` si hace falta
- [X] T040 [US5] Actualizar `laravel_app/resources/views/celulas/show.blade.php`: reemplazar el formulario de «agregar persona» por el multiselect remoto con «Crear persona nueva», mostrar el estado de cada integrante con `x-ui.estado` y conservar «Quitar»
- [X] T041 [US5] Ajustar las pruebas que asumían la regla anterior (`laravel_app/tests/Feature/CoherenciaCelulaLiderTest.php` y las de `ModulosRelacionadosTest.php`) para la nueva regla: la pertenencia a una célula no depende del líder directo, pero el líder directo sigue siendo la fuente de verdad del vínculo pastoral
- [X] T042 [US5] Ejecutar `php artisan test --filter="Celulas|Coherencia|Modulos"` y `npm test`; corregir hasta verde

---

## Fase 8: Historia de usuario 4 - Ver a quién lidera un líder (Prioridad: P2)

**Objetivo**: grilla compacta «Personas a su cargo» en la ficha del líder (FR-007).

**Prueba independiente**: abrir un líder con y sin personas a cargo.

- [X] T043 [P] [US4] Prueba `laravel_app/tests/Feature/Lideres/PersonasACargoTest.php`: la ficha muestra la tarjeta «Personas a su cargo» con nombre (enlace a la ficha), estado, celular y célula; ordenada por apellido; un líder sin nadie muestra «Aún no lidera a nadie»; no incluye a una persona integrante de su célula cuyo líder directo es otro; un líder con 100 personas a cargo no genera más de 3 consultas adicionales; un usuario con alcance limitado solo ve las personas que puede ver
- [X] T044 [US4] Reescribir la tarjeta de personas a cargo en `laravel_app/resources/views/lideres/show.blade.php` como grilla compacta (`table-sm` dentro de una tarjeta con altura máxima y desplazamiento, `x-ui.estado`, columnas nombre, estado, celular y célula, contador en el título) y cargar en `LideresController::show` la relación `celulas` de los miembros con `with` para evitar consultas repetidas
- [X] T045 [US4] Ejecutar `php artisan test --filter=Lideres`; corregir hasta verde

---

## Fase 9: Historia de usuario 7 - Ministerios en Congregación con selección autocompletada (Prioridad: P2)

**Objetivo**: menú ordenado y selección de integrantes por búsqueda (FR-011, FR-012).

**Prueba independiente**: ver «Ministerios» bajo Congregación y elegir integrantes escribiendo parte del nombre.

- [X] T046 [P] [US7] Prueba `laravel_app/tests/Feature/Ministerios/IntegrantesBuscadorTest.php`: el menú tiene «Ministerios» dentro de «Congregación» y no en «Actividades»; el formulario de crear y editar usa el multiselect con `data-fuente` hacia `personas.buscar` y los integrantes actuales ya cargados; guardar sincroniza los integrantes; una persona de otra sede o inactiva nueva se rechaza; el rol y la fecha de ingreso de los existentes se conservan
- [X] T047 [US7] Mover «Ministerios» a la sección Congregación de `laravel_app/config/menu.php` (después de Células) y ajustar `laravel_app/tests/Feature/Interfaz/MenuTest.php`, `MarcoDePaginaTest.php` y `MarcaDeSedeTest.php` si listan secciones
- [X] T048 [US7] Cambiar `laravel_app/resources/views/ministerios/_form.blade.php` para usar `x-form.multiselect` con `fuente`, `filtros` `activas` y los integrantes actuales, y revisar `MinisteriosController` para validar `personas[]` con el alcance de sede y conservar rol y fecha de ingreso de los ya existentes
- [X] T049 [US7] Ejecutar `php artisan test --filter="Ministerios|Interfaz"`; corregir hasta verde

---

## Fase 10: Pulido y verificación final

- [X] T050 [P] Actualizar `README.md` (secciones de personas, líderes, células y ministerios: estado «En célula», autocompletado, integrantes sin depender del líder directo, sin Red) y `DEPLOY_CPanel.md` (hay una migración nueva que retira `redes`: hacer copia de la base antes de migrar)
- [X] T051 [P] Actualizar `laravel_app/resources/views/partials/ayuda-terminos.blade.php` con el estado «En célula» y quitar la ayuda de red; revisar los textos de las pantallas tocadas
- [X] T052 Sembrar 500 personas en una base temporal, recorrer los 17 escenarios de `specs/006-optimizar-personas-celulas/quickstart.md` con las herramientas de Chrome (incluido el diálogo «Crear persona nueva» y el rendimiento del buscador < 1 s), anotar los resultados y los tiempos frente a la línea base de T002; luego apagar el servidor y borrar el `.env` y la base temporales
- [X] T053 Ejecutar `php artisan test`, `npm test` y `npm run build` completos, verificar que la base local del usuario **no** se tocó (solo la copia de T030) y marcar todas las tareas como [X]

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias.
- **Fundamentos (Fase 2)**: bloquea todas las historias. Orden: T003 → T004/T005; T006 → T007 → T008/T009 → T010; T011 → T012 → T013 → T014 → T015; T016 cierra.
- **US1**: tras Fundamentos (usa el estado `celula` en el formulario).
- **US2 (Red)**: tras Fundamentos; T024 (migración) antes de T025–T029; es la fase más extensa de cambios, hacerla antes de US3 porque modifica `designar` y `LiderazgoService`.
- **US6**: tras US1 (usa el formulario fusionado).
- **US3**: tras US2 y Fundamentos (campo remoto y servicio sin red).
- **US5**: tras Fundamentos y US2 (multiselect, alta rápida y sin red).
- **US4**: tras US2 y US5 (célula en la grilla).
- **US7**: tras Fundamentos (multiselect remoto); independiente del resto.
- **Pulido**: al final.

### Ejemplos de paralelismo

- Fundamentos: T004, T005, T007, T010, T012 y T014 en paralelo en cuanto existan sus dependencias.
- US2: T022, T023, T027 y T028 en paralelo.
- US1: T017, T019 y T020 en paralelo.
- US5, US4 y US7 pueden avanzar en paralelo una vez cerradas Fundamentos y US2.

## Estrategia de implementación

1. **MVP**: Fundamentos + US1 + US6 → formulario de una tarjeta y estado «En célula» ya utilizables.
2. **Incremental**: US2 (Red fuera) → US3 (designar con autocompletado) → US5 (integrantes de células) → US4 (grilla) → US7 (ministerios), con `php artisan test` y `npm test` al cerrar cada fase.
3. **Control de riesgo**: la migración (T024) se prueba primero sobre una copia de la base (T030) y con conteos antes y después; el buscador se mide con 500 personas (T002, T052).

## Notas

- Si durante el recorrido aparece una persona sin celular ni correo en el alta rápida, se muestra el error de validación en el diálogo (no se crea).
- El buscador y el alta rápida están pensados para reutilizarse más adelante en otras pantallas (por ejemplo, líder o co-líder de una célula).
- `/speckit-analyze` puede ejecutarse antes de `/speckit-implement`.

## Notas de implementación

- **Corrección encontrada en el recorrido:** Select2 nunca se iniciaba al cargar la página (orden de evaluación de los `import` en `app.js`); se movió jQuery y Select2 a `resources/js/jquery-global.js`, importado antes de los formularios, y se añadió `OrdenDeCargaJsTest`. También se ajustó `forms.css` para que Select2 conviva con el icono del grupo.
- `PersonasController::rapida` responde siempre JSON con su propio validador, porque el manejador global de excepciones solo renderiza JSON para rutas `api/*`.
- `MigracionRedesZonas` se conserva solo por la migración histórica `2026_10_20_000004`; ya no migra redes si la tabla no existe.
- La migración se aplicó a una copia de la base local (personas 11, líderes 3, células 3, ministerios 1 antes y después). La base original no se tocó.
- La prueba de migración con datos reales sobre SQLite en memoria no es posible (reconstruir `personas` con claves foráneas activas); se cubre con la copia de la base real y con pruebas de conteos y de `down()`/`up()` sobre esquema vacío.
