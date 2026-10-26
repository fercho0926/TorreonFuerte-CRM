# Tareas: Renombrar "Personas" a "Miembros"

**Entrada**: Documentos de diseño en `/specs/010-renombrar-personas-miembros/`

**Requisitos previos**: plan.md, spec.md, research.md, data-model.md, contracts/rutas.md, quickstart.md

**Pruebas**: el plan y la constitución (principio IV) exigen pruebas: se actualizan las existentes y se agregan pruebas de redirección, de terminología y de migración. Todas las rutas son relativas a `laravel_app/` salvo que se indique.

**Formato**: `- [ ] [ID] [P?] [Historia?] Descripción con ruta`. `[P]` = se puede hacer en paralelo (archivos distintos, sin dependencia pendiente).

**Reglas generales de redacción** (aplican a toda tarea de texto): concordancia en masculino («un miembro», «los miembros», «ningún miembro», «miembro inactivo», «nuevo miembro», «este miembro»); NO reemplazar a ciegas; NO tocar contenido escrito por usuarios; el estado guardado `miembro_activo` y su etiqueta no cambian; `Celula::miembros()` no cambia de nombre; `Persona::miembros()` (miembros a cargo de un líder) pasa a `liderados()`.

## Fase 1: Preparación

- [X] T001 Hacer copia de respaldo de `database/database.sqlite` fuera del repositorio y anotar la línea base de conteos (miembros, cambios, inscripciones a eventos, pertenencias a células y ministerios, usuarios con miembro vinculado, registros QR) según `specs/010-renombrar-personas-miembros/quickstart.md` sección 1
- [X] T002 Ejecutar `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` y registrar el resultado inicial (deben estar en verde antes de empezar)
- [X] T003 [P] Generar el inventario de apariciones de «persona(s)» por archivo (excluyendo `vendor/`, `node_modules/`, `storage/`, `public/build/` y `database/migrations/` históricas) y guardarlo en `specs/010-renombrar-personas-miembros/inventario.md` para marcar avance

## Fase 2: Fundamentos (bloquea las historias)

- [X] T004 Crear la prueba guardiana de terminología `tests/Feature/Interfaz/TerminologiaMiembrosTest.php`: recorre `resources/views`, `lang`, `config/menu.php`, `public/manifest.webmanifest` y los mensajes de `app/` buscando «persona(s)» y falla si aparece fuera de la lista de excepciones declarada en `tests/Support/excepciones-terminologia.php` (p. ej. «datos personales»); debe fallar al inicio (rojo)
- [X] T005 [P] Crear `tests/Support/excepciones-terminologia.php` con la lista inicial de excepciones justificadas (vacía salvo «personal/personales» como adjetivo)
- [X] T006 [P] Agregar al `README.md` una nota de glosario: «Persona» (histórico, specs 001–009) = «Miembro»; el estado `miembro_activo` es un estado, no la entidad

**Punto de control**: base verde, prueba guardiana en rojo, inventario listo.

## Fase 3: Historia de Usuario 1 - Ver «Miembros» en toda la interfaz (Prioridad: P1) 🎯 MVP

**Objetivo**: ningún texto visible dice «persona(s)» referido a miembros, con concordancia correcta.

**Prueba independiente**: la prueba guardiana pasa y el recorrido manual del quickstart sección 4 no muestra «persona(s)».

### Pruebas de la Historia 1

- [X] T007 [P] [US1] Actualizar las aserciones de texto de `tests/Feature/Interfaz/MenuTest.php`, `tests/Feature/Interfaz/ListadosTest.php`, `tests/Feature/Interfaz/TableroTest.php` y `tests/Feature/PresentacionPersonasTest.php` para esperar «Miembros»/«miembro» (rojo antes de la implementación)
- [X] T008 [P] [US1] Agregar a `tests/Feature/Grillas/ExportacionGrillasTest.php` la aserción de que el CSV del listado se llama `miembros-AAAA-MM-DD.csv` y sus encabezados usan «miembro(s)»

### Implementación de la Historia 1

- [X] T009 [US1] Cambiar la etiqueta del menú a «Miembros» en `config/menu.php` (línea de `personas.index`; la ruta se renombra en US2)
- [X] T010 [P] [US1] Actualizar `lang/es/validation.php`: atributos y mensajes «persona(s)» → «miembro(s)» (FR-003)
- [X] T011 [P] [US1] Actualizar `public/manifest.webmanifest` (descripción: «Gestión de miembros, líderes, células, eventos y ministerios de la iglesia.») y subir la versión de caché en `public/sw.js`
- [X] T012 [P] [US1] Actualizar textos de las grillas: `app/Grillas/PersonasGrilla.php`, `SinLiderGrilla.php`, `LideresGrilla.php` (columna `personas` → clave `miembros`, etiqueta «Miembros»), `CelulasGrilla.php`, `EncuentrosGrilla.php`, `UsuariosGrilla.php`, `SedesGrilla.php`, `MinisteriosGrilla.php`, `app/Grillas/Columna.php` (etiquetas, títulos, nombre del archivo exportado)
- [X] T013 [P] [US1] Actualizar mensajes de éxito, error y confirmación en `app/Http/Controllers/PersonasController.php`, `CelulasController.php`, `EncuentrosController.php`, `EventosController.php`, `LideresController.php`, `MinisteriosController.php`, `TrasladosController.php`, `SinLiderController.php`, `UsersController.php`, `RegistroPublicoController.php`, `RecuperacionClaveController.php`, `CuentaController.php`, `AuthController.php`, `Controller.php`
- [X] T014 [P] [US1] Actualizar textos en `app/Http/Requests/PersonaRequest.php`, `app/Http/Requests/RegistroQrRequest.php`, `app/Policies/PersonaPolicy.php` (mensajes de denegación) y `app/Support/Formulario.php`, `app/Support/Permisos.php`
- [X] T015 [P] [US1] Actualizar textos de servicios: `app/Services/TrasladoService.php`, `LiderazgoService.php`, `SedeService.php`, `RegistroPorQr.php`, `AlcanceService.php` y modelos `app/Models/Celula.php`, `Encuentro.php`, `Evento.php`, `Ministerio.php`, `Sede.php`, `User.php` (solo cadenas visibles y comentarios, sin renombrar identificadores todavía)
- [X] T016 [P] [US1] Actualizar vistas del módulo: `resources/views/personas/{index,create,edit,show,_form,_celda}.blade.php` y `resources/views/partials/persona-tarjeta.blade.php`
- [X] T017 [P] [US1] Actualizar vistas de células y encuentros: `resources/views/celulas/{show,_form}.blade.php`, `resources/views/encuentros/{show,_celda,_form}.blade.php`
- [X] T018 [P] [US1] Actualizar vistas de eventos, ministerios y líderes: `resources/views/eventos/{show,_form}.blade.php`, `resources/views/ministerios/_form.blade.php`, `resources/views/lideres/{designar,quitar,show,_celda}.blade.php`
- [X] T019 [P] [US1] Actualizar vistas de sedes, traslados, sin-lider, usuarios y claves: `resources/views/sedes/{pastores,show,_celda}.blade.php`, `traslados/create.blade.php`, `sin-lider/{index,_celda}.blade.php`, `users/{_celda,_form}.blade.php`, `claves/solicitudes.blade.php`
- [X] T020 [P] [US1] Actualizar parciales, componentes, tablero y registro público: `resources/views/partials/{accesos-rapidos,acciones-contacto,ayuda-terminos,lista-registros-qr,panel-registro-qr}.blade.php`, `resources/views/components/form/{lista-asistencia,multiselect}.blade.php`, `resources/views/dashboard.blade.php`, `resources/views/registro/formulario.blade.php`
- [X] T021 [P] [US1] Actualizar textos de JavaScript y estilos: `resources/js/forms/{alta-rapida,buscador-remoto,registro-qr,selectores,borrador}.js`, `resources/js/layout/menu-recordado.js`, `resources/css/forms.css` (solo textos y comentarios; las URL se cambian en US2)
- [X] T022 [P] [US1] Actualizar datos de demostración visibles en `database/seeders/DatabaseSeeder.php` (nombres/etiquetas de ejemplo, no identificadores)
- [X] T023 [US1] Revisar a mano la concordancia de género y número de cada frase cambiada en T009–T022 (muestra mínima de 20 pantallas y mensajes, SC-006) y corregir
- [X] T024 [US1] Ejecutar `php artisan test --filter=Terminologia` hasta verde; completar `tests/Support/excepciones-terminologia.php` solo con excepciones justificadas
- [ ] T025 [US1] Ejecutar `npm test`, `npm run build` y `npm run test:movil`; ajustar si «Miembros» desborda controles de 44 px o líneas de 320 px (FR-011)

**Punto de control**: la Historia 1 es entregable por sí sola (MVP).

## Fase 4: Historia de Usuario 2 - Direcciones web con «miembros» sin romper las antiguas (Prioridad: P2)

**Objetivo**: `/miembros/*` es la dirección oficial; `/personas/*` redirige conservando método, identificadores y consulta.

**Prueba independiente**: quickstart sección 5; pruebas de redirección en verde.

### Pruebas de la Historia 2

- [ ] T026 [P] [US2] Crear `tests/Feature/Miembros/RedireccionAntiguaTest.php`: GET `/personas`, `/personas/{id}`, `/personas/exportar?q=ana` → 301 a `/miembros…` con consulta intacta; POST/PUT/DELETE antiguos → 308; cubrir también `/eventos/{id}/personas…`
- [ ] T027 [P] [US2] Agregar a `tests/Feature/Aislamiento/MatrizRutasTest.php` las rutas `miembros.*` y la redirección antigua; probar que un usuario de otra sede recibe la misma respuesta (403/404) por la dirección antigua y la nueva, sin revelar existencia (principio II)

### Implementación de la Historia 2

- [ ] T028 [US2] En `routes/web.php` renombrar las rutas a `/miembros/*` con nombres `miembros.*` y parámetro `{miembro}`, `/eventos/{evento}/miembros…` (`eventos.miembros.*`) y `/sin-lider/{miembro}/asignar`, según `specs/010-renombrar-personas-miembros/contracts/rutas.md`
- [ ] T029 [US2] En `routes/web.php` agregar el grupo de compatibilidad: `/personas/{cualquiera?}` y `/eventos/{evento}/personas/{cualquiera?}` con 301 para GET/HEAD y 308 para los demás métodos, conservando la cadena de consulta, registrado sin duplicar la lógica de autorización
- [ ] T030 [US2] Actualizar todas las referencias `route('personas.*')`, `routes personas.*` y `activa_en` en `config/menu.php`, `resources/views/**`, `app/Providers/AppServiceProvider.php`, controladores, grillas y servicios a `miembros.*` (buscar con `grep -rn "personas\.\(index\|create\|show\|edit\|store\|update\|destroy\|buscar\|datos\|exportar\|rapida\|inactivar\|reactivar\)"`)
- [ ] T031 [P] [US2] Cambiar las URL en JavaScript y sus pruebas: `resources/js/forms/alta-rapida.test.js`, `borrador.test.js`, `buscador-remoto.test.js`, `buscador-remoto.js` (comentario) y cualquier `data-fuente`/`data-crear` en vistas que apunte a `/personas/…`
- [ ] T032 [P] [US2] Actualizar `tests/movil/pantallas.mjs` y las especificaciones `tests/movil/*.spec.js` con las URL `/miembros`
- [ ] T033 [US2] Hacer que los controladores acepten los alias de entrada antiguos (`persona_id`, `personas[]`) mapeándolos a `miembro_id`/`miembros[]` y respondan errores con los nombres nuevos (contrato, sección «Cuerpos de petición») en `CelulasController.php`, `EncuentrosController.php`, `EventosController.php`, `LideresController.php`, `MinisteriosController.php`
- [ ] T034 [US2] Ejecutar `php artisan route:list` y comprobar que no queda ninguna ruta con nombre `personas.*`; ejecutar `php artisan test`, `npm test`, `npm run build`, `npm run test:movil`

**Punto de control**: Historias 1 y 2 funcionan sin tocar la base de datos.

## Fase 5: Historia de Usuario 3 - Datos, permisos y código consistentes (Prioridad: P1)

**Objetivo**: nomenclatura interna `Miembro` en código y base de datos, sin perder datos ni abrir brechas entre sedes.

**Prueba independiente**: conteos iguales antes/después, `up`+`down`+`up` estable, pruebas de aislamiento en verde (quickstart secciones 2–3 y 6).

### Pruebas de la Historia 3

- [ ] T035 [P] [US3] Crear `tests/Feature/Migraciones/RenombreMiembrosTest.php`: crea datos con el esquema anterior, ejecuta la migración y comprueba tablas/columnas nuevas, conteos y relaciones intactos (miembros, cambios, pivotes, usuarios vinculados, pagos, registros QR, sedes con pastor/pastora); verifica `down` y un segundo `up`
- [ ] T036 [P] [US3] Actualizar `tests/Feature/MigracionDatosTest.php`, `MigracionSedesTest.php` y `tests/Feature/Migraciones/DropRedesTest.php` para que sean válidos con el esquema nuevo

### Código (etapa 2 del plan)

- [ ] T037 [US3] Renombrar el modelo `app/Models/Persona.php` → `app/Models/Miembro.php` (clase `Miembro`, `$table = 'miembros'`), la relación `miembros()` (hasMany) → `liderados()` y `miembros_count` → `liderados_count`; conservar `SedeScope` y casts sin cambios
- [ ] T038 [P] [US3] Renombrar `app/Models/PersonaCambio.php` → `MiembroCambio.php` (`$table = 'miembro_cambios'`) y `app/Models/EventoPersona.php` → `EventoMiembro.php` (`$table = 'evento_miembro'`)
- [ ] T039 [US3] Actualizar relaciones y claves en `app/Models/{Celula,CelulaReunion,Encuentro,Evento,EventoPago,Ministerio,RegistroQr,Sede,User}.php` (`persona_id` → `miembro_id`, pivotes `*_miembro`, `pastor_miembro_id`, `pastora_miembro_id`, `evento_miembro_id`; `Celula::miembros()` se conserva)
- [ ] T040 [P] [US3] Renombrar `app/Http/Controllers/PersonasController.php` → `MiembrosController.php`, `app/Http/Requests/PersonaRequest.php` → `MiembroRequest.php`, `app/Policies/PersonaPolicy.php` → `MiembroPolicy.php`; actualizar registro de políticas, `AppServiceProvider.php` (`Persona::class` → `Miembro::class`, `personasVisibles` → `miembrosVisibles`) y `routes/web.php`
- [ ] T041 [P] [US3] Renombrar `app/Grillas/PersonasGrilla.php` → `MiembrosGrilla.php` y `app/Services/BuscadorPersonas.php` → `BuscadorMiembros.php`; actualizar `AlcanceService`, `TrasladoService`, `LiderazgoService`, `RegistroPorQr`, `SedeService`, `SedeContext` (reglas `existe('personas')` → `existe('miembros')`), `app/Support/{MigracionSedes,MigracionRedesZonas,Permisos}.php`
- [ ] T042 [US3] Actualizar controladores y vistas con nombres de variable, claves de petición y `with()`/`withCount()` (`personas`, `persona`, `personaIds`, `personasDisponibles()` → `miembros…`); renombrar `resources/views/personas/` → `resources/views/miembros/` y `partials/persona-tarjeta.blade.php` → `miembro-tarjeta.blade.php` con todas sus referencias `@include`/`view()`
- [ ] T043 [P] [US3] Renombrar `database/factories/PersonaFactory.php` → `MiembroFactory.php` y actualizar `CelulaFactory.php` y `database/seeders/DatabaseSeeder.php`

### Base de datos (etapa 3 del plan)

- [ ] T044 [US3] Inspeccionar el esquema real (`php artisan db:table` o `sqlite3 .schema`) y confirmar contra `data-model.md` la lista completa de tablas, columnas e índices cuyo nombre contenga `persona`; documentar diferencias en `specs/010-renombrar-personas-miembros/data-model.md`
- [ ] T045 [US3] Crear `database/migrations/2026_10_27_000001_renombrar_personas_a_miembros.php`: `Schema::rename` de `personas`, `persona_cambios`, `evento_persona`, `encuentro_persona`, `celula_persona`, `ministerio_persona`; `renameColumn` de `persona_id`, `pastor_persona_id`, `pastora_persona_id`, `evento_persona_id`; renombrado de índices con nombre explícito (p. ej. `cambios_sede_persona_index`); idempotente (omite lo ya renombrado) y con `down()` completo; NO editar las migraciones históricas
- [ ] T046 [US3] Revisar que ninguna migración histórica posterior a la nueva dependa del nombre antiguo y que `php artisan migrate` desde cero (base temporal de pruebas) funcione; aplicar la migración a una copia de la base local y comparar conteos con T001 (SC-003)
- [ ] T047 [US3] Si la migración o `renameColumn` falla en el motor de producción, aplicar el plan B de `research.md` D3 (conservar nombres de tabla con `$table`) y dejar constancia en `research.md`

### Pruebas renombradas (con el código)

- [ ] T048 [P] [US3] Renombrar y actualizar `tests/Feature/Personas/` → `tests/Feature/Miembros/` (`AltaRapidaTest`, `BuscadorMiembrosTest`, `EstadoEnCelulaTest`, `FiltroEnCelulaTest`, `GrillaDatosTest`, `SinRedTest`, `TarjetaUnicaTest`)
- [ ] T049 [P] [US3] Renombrar y actualizar `tests/Feature/Aislamiento/AislamientoPersonasTest.php` → `AislamientoMiembrosTest.php` y las demás pruebas de aislamiento (`AislamientoCelulasTest`, `AislamientoEventosTest`, `AislamientoMinisteriosTest`, `AislamientoUsuariosTest`, `UsuarioSinSedeTest`)
- [ ] T050 [P] [US3] Renombrar y actualizar `tests/Feature/{PersonasBusquedaTest,PersonasInactivarTest,PersonasRegistroTest,PresentacionPersonasTest,TrasladoPersonaTest}.php`, `tests/Feature/Lideres/PersonasACargoTest.php`, `tests/Feature/RegistroQr/FlujoNuevaPersonaTest.php` a su nombre «Miembro(s)»
- [ ] T051 [P] [US3] Actualizar el resto de pruebas con referencias a la entidad: `tests/Feature/{AlcancePorRolTest,CoherenciaCelulaLiderTest,DashboardGlobalAdminTest,EscalaSedesTest,LiderazgoTest,MarcaDeSedeTest,ModulosRelacionadosTest,PermisosAccionesTest,SedeActivaAdminTest,SinLiderTest}.php`, `tests/Feature/{Celulas,Claves,Formularios,Grillas,Interfaz,Ministerios,Movil,RegistroQr,Sedes}/**`, `tests/Support/{ConSedes,EscenarioDosSedes,IniciaSesion,RecorreFormularios}.php`, `tests/Unit/{CelularTest,ModelosConSedeTest,SedeScopeTest}.php`
- [ ] T052 [US3] Ejecutar `php artisan test` completo; resolver fallos de aislamiento antes que cualquier otro (principio II)
- [ ] T053 [US3] Probar con un usuario de cada rol (SuperAdmin, Admin, Pastor, Líder) los flujos de `quickstart.md` sección 4 (crear, editar, inactivar, reactivar, trasladar, inscribir a evento con pago, célula, ministerio, designar líder, registro QR, claves por WhatsApp)

**Punto de control**: las tres historias funcionan y los datos están íntegros.

## Fase 6: Pulido y transversal

- [ ] T054 [P] Actualizar `README.md` (sección «Grillas», glosario y cualquier mención a «personas») y la guía de uso si existe
- [ ] T055 [P] Enmendar `.specify/memory/constitution.md`: principio VII «no guarda datos de miembros, líderes…»; versión 1.1.1, fecha 2026-10-22 y motivo (terminología, spec 010)
- [ ] T056 [P] Actualizar la memoria del proyecto en `C:\Users\Ferney\.claude\projects\C--WorkSpace-TorreonFuerte-CRM\memory\` (entradas que mencionan «Personas»: `repo-laravel-actual.md`, `grillas-estandar.md`, `diseno-movil.md`) y `MEMORY.md`
- [ ] T057 Búsqueda final de rastros (quickstart sección 6): `grep -rIni "persona" app resources routes lang config public tests` excluyendo vendor/node_modules/build/migraciones históricas/specs; justificar o corregir cada resultado restante
- [ ] T058 Ejecutar `php artisan test`, `npm test`, `npm run build`, `npm run test:movil` en verde y confirmar que `tests/movil/excepciones.json` sigue vacío
- [ ] T059 Verificación en el navegador de los flujos principales en escritorio y celular, y de las direcciones antiguas (quickstart secciones 4, 5 y 7)
- [ ] T060 Cerrar la lista de verificación del quickstart; los commits se hacen solo cuando el responsable del proyecto lo pida

## Dependencias y orden de ejecución

- **Fase 1 → Fase 2** primero (línea base, prueba guardiana en rojo).
- **US1** (Fase 3) depende solo de la Fase 2; es el MVP.
- **US2** (Fase 4) depende de US1 solo en lo que respecta a `config/menu.php` (T009 antes de T030); la redirección es independiente.
- **US3** (Fase 5) va después de US1 y US2 para no mezclar renombrados de texto y de identificadores; dentro de ella: modelos (T037–T039) → controladores/servicios (T040–T043) → esquema (T044–T047) → pruebas (T048–T053). La migración (T045) y el cambio de `$table` deben aplicarse juntos.
- **Pulido** (Fase 6) al final.

## Oportunidades de paralelismo

- Fase 2: T005 y T006 en paralelo.
- US1: T010–T022 tocan archivos distintos y son paralelizables; T023–T025 son secuenciales al final.
- US2: T026, T027, T031, T032 en paralelo; T028–T030 secuenciales sobre `routes/web.php`.
- US3: T038, T040, T041, T043 en paralelo tras T037; T048–T051 en paralelo.
- Pulido: T054–T056 en paralelo.

## Estrategia de implementación

1. **MVP**: Fases 1–3 (US1). Se puede desplegar: el usuario ya ve «Miembros» en toda la interfaz.
2. **Incremento 2**: Fase 4 (US2), direcciones nuevas y redirección.
3. **Incremento 3**: Fase 5 (US3), renombrado interno y de base de datos, con copia de respaldo y verificación de conteos antes de producción.
4. Cada incremento termina con las cuatro verificaciones en verde (`php artisan test`, `npm test`, `npm run build`, `npm run test:movil`).
