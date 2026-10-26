---

description: "Lista de tareas de la funcionalidad 015: historial de cambios en toda la plataforma"
---

# Tareas: Historial de cambios en toda la plataforma (pestaña «Histórico»)

**Entrada**: documentos de diseño en `/specs/015-historial-cambios/`

**Requisitos previos**: plan.md, spec.md, research.md, data-model.md, contracts/ (rutas-y-permisos, servicio-historial), quickstart.md

**Pruebas**: se incluyen (constitución, principio IV; CE-001, CE-005, CE-006, CE-007). Cada prueba se escribe **antes** de la implementación que verifica y debe fallar primero.

**Organización**: por historia de usuario. Todas las rutas son relativas a `laravel_app/`. Los miembros siguen siendo el modelo `Persona`; los niveles, `NivelFormacion`. Decisiones de referencia: research.md D1–D16.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[US1..US5]**: historia de usuario a la que pertenece (spec.md)

---

## Fase 1: Preparación

- [X] T001 Confirmar la línea base antes de empezar: `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` en verde; anotar los números y los conteos actuales de `persona_cambios`, `sede_cambios`, `rol_cambios` y `nivel_cambios` de la base local en `specs/015-historial-cambios/quickstart.md` (sección nueva «Línea base»)
- [X] T002 [P] Crear los directorios `tests/Unit/Historial/`, `tests/Feature/Historial/`, `resources/views/historial/`, `resources/views/components/historial/` y `resources/views/control-cambios/`

---

## Fase 2: Fundamentos (bloquea todas las historias)

**Propósito**: tabla, modelo inalterable, catálogo de acciones, servicio de escritura, rasgo de modelos, lectura base, componentes de presentación e integración de los historiales existentes.

### Pruebas

- [X] T003 [P] Prueba de la migración de `historial_cambios`: columnas y nulabilidad exactas de data-model.md (`actor` string(12) no nulo; `actor_nombre` string(120) no nulo; `modulo` string(30) no nulo; `accion` string(40) no nulo; `categoria` string(24) nulo; `sujeto_tipo` string(30) no nulo; `sujeto_id` no nulo; `sujeto_nombre` string(160) no nulo; `relacionado_*` nulos; `lote` uuid nulo; `detalle` json nulo; `sede_id` y `user_id` nulos **sin claves foráneas**; sin `updated_at`), los 7 índices, y `personas.trasladada_desde_persona_id` (nulo, indexado, sin clave foránea) en `tests/Feature/Historial/MigracionTablaHistorialTest.php`
- [X] T004 [P] Prueba unitaria del catálogo `AccionesHistorial`: cada acción tiene etiqueta, módulo, icono y, si aplica a personas, una categoría entre `datos`, `encuentros_retiros`, `celulas_ministerios`, `liderazgo`, `niveles`, `sede`; claves únicas; `etiqueta()` de una clave desconocida lanza excepción en `tests/Unit/Historial/AccionesHistorialTest.php`
- [X] T005 [P] Prueba unitaria de `Historial::diferencia()`: solo campos cambiados; etiquetas legibles; claves foráneas traducidas a nombre; estados traducidos con `Estados`; fechas en formato local; booleanos «Sí/No»; vacíos «(vacío)»; `largo = true` por encima de 120 caracteres; campos secretos (`password`, `remember_token`, prefijos `clave`, `token`, `codigo`, sufijo `_hash`) marcados `secreto: true` sin valores; campos ignorados (`updated_at`) excluidos en `tests/Unit/Historial/DiferenciaTest.php`
- [X] T006 [P] Prueba del servicio `Historial` (contracts/servicio-historial.md): `registrar()` guarda actor, `actor_nombre`, sede, módulo, categoría, fotos de nombres y `relacionado_*`; acción desconocida lanza excepción; `accion()` renombra la acción de las entradas generadas dentro (y se anida); `comoActor('qr'|'automatico'|'sistema')` cambia actor y nombre y conserva `user_id` del que lo provocó; `sinRegistro()` no escribe; si la transacción se revierte no queda entrada; `sincronizacion()` sin agregados ni quitados no escribe nada, y con cambios escribe una entrada resumen con `agregados`/`quitados` y una por persona, todas con el mismo `lote` en `tests/Feature/Historial/ServicioHistorialTest.php`
- [X] T007 [P] Prueba del rasgo `RegistraHistorial` sobre `Persona`: `created` escribe «Creado» con `detalle.datos`; `updated` sin diferencias no escribe; `updated` con cambios escribe `cambios`; `deleted` (borrado lógico) escribe «Eliminado» con `datos`; `restored` escribe «Restaurado» en `tests/Feature/Historial/RasgoRegistraHistorialTest.php`
- [X] T008 [P] Prueba de inalterabilidad: `CambioHistorial::update()`, `save()` sobre una entrada existente y `delete()` lanzan excepción; borrar una persona (incluso `forceDelete`) o un usuario no borra ni anula sus entradas, y `actor_nombre` se conserva en `tests/Feature/Historial/InalterableTest.php`
- [X] T009 [P] Prueba de integración de historiales existentes: con filas sembradas en `persona_cambios`, `sede_cambios`, `rol_cambios` y `nivel_cambios`, la migración de datos crea el mismo número de entradas en `historial_cambios`, conserva `created_at` y `user_id`, guarda el JSON original en `detalle.legado`, traduce la acción al catálogo nuevo y llena `trasladada_desde_persona_id` a partir de `despues.persona_origen_id` (CE-007) en `tests/Feature/Historial/MigracionHistorialTest.php`
- [X] T010 [P] Prueba de los escritores reemplazados: las acciones que hoy usan `LiderazgoService::registrarCambio()`, `SedeService`, `RolService` y `NivelesService` escriben ahora en `historial_cambios` (una entrada por acción, sin duplicar con el rasgo) en `tests/Feature/Historial/EscritoresReemplazadosTest.php`

### Implementación

- [X] T011 Crear la migración `database/migrations/2026_10_30_000001_create_historial_cambios_table.php` con las columnas, tipos, nulabilidad e índices de data-model.md (`(sujeto_tipo, sujeto_id, categoria, created_at)`, `(sujeto_tipo, sujeto_id, created_at)`, `(sede_id, created_at)`, `(user_id, created_at)`, `(modulo, created_at)`, `(relacionado_tipo, relacionado_id)`, `(lote)`), **sin claves foráneas** y sin `updated_at`
- [X] T012 [P] Crear la migración `database/migrations/2026_10_30_000002_add_trasladada_desde_persona_id_to_personas.php` (bigint nulo, índice, sin clave foránea) y agregar el campo a `#[Fillable]`/`casts` de `app/Models/Persona.php`
- [X] T013 [P] Crear el catálogo `app/Support/AccionesHistorial.php` con todas las acciones de data-model.md (clave → etiqueta, módulo, icono FontAwesome, categoría para personas) y los métodos `existe()`, `etiqueta()`, `modulo()`, `categoria()`, `icono()`, `deModulo()` y `categorias()` (con las etiquetas de sección «Cambios de datos», «Encuentros y retiros», «Células y ministerios», «Liderazgo», «Niveles», «Sede»)
- [X] T014 Crear el modelo `app/Models/CambioHistorial.php` (tabla `historial_cambios`, `UPDATED_AT = null`, `casts` de `detalle` a `array`, excepción en `updating` y `deleting`, filtro global de sede en lectura con `exigeSede()` falso para entradas globales, relación `usuario()` sin filtros, accesores `etiqueta`, `icono`, `es_legado`) y registrar el *morph map* de los 16 alias de data-model.md en `app/Providers/AppServiceProvider.php` (depende de T011, T013)
- [X] T015 Crear el servicio `app/Services/Historial.php` con `registrar()`, `diferencia()`, `sincronizacion()`, `accion()`, `comoActor()` y `sinRegistro()` según contracts/servicio-historial.md; actor por defecto: usuario autenticado (`actor_nombre` = `name`), o `sistema` si no hay sesión; inserción en la conexión y transacción en curso; inserción por lotes en `sincronizacion()` (depende de T014)
- [X] T016 Crear el rasgo `app/Models/Concerns/RegistraHistorial.php` (escucha `created`, `updated`, `deleted`, `restored`; métodos que cada modelo define: `aliasHistorial()`, `moduloHistorial()`, `nombreHistorial()`, `camposHistorial()`, `camposIgnorados()`, `camposSecretos()`, `sujetosHistorial()`; acciones por defecto `<alias>_creado/_editado/_eliminado/_restaurado` según el catálogo; respeta `Historial::accion()`) (depende de T015)
- [X] T017 Crear las migraciones de permisos y datos: `database/migrations/2026_10_30_000003_permisos_modulo_control_cambios.php` (fila `control_cambios` por rol: `admin` → 1, demás → 0, idempotente, como `2026_10_29_000002_permisos_modulo_niveles.php`) y agregar `CONTROL_CAMBIOS = 'control_cambios'` al catálogo de `app/Support/Modulos.php` (etiqueta «Control de cambios», icono `fa-history`, `solo_lectura` verdadero, descripción «Todo lo que ha cambiado en la plataforma: quién, cuándo y qué»)
- [X] T018 Crear la migración de datos `database/migrations/2026_10_30_000004_migrar_historiales_existentes.php`: copia `persona_cambios`, `sede_cambios`, `rol_cambios` y `nivel_cambios` a `historial_cambios` (`actor = usuario` o `sistema` si no hay `user_id`, `actor_nombre` desde `users.name` o «Sistema», `sujeto_nombre` desde el registro o desde `rol_nombre`/`nombre`, acción traducida al catálogo, categoría, `detalle.legado` con `accion`, `antes` y `despues`), rellena `personas.trasladada_desde_persona_id` y verifica los conteos (depende de T011, T012, T013)
- [X] T019 Reemplazar los escritores actuales por `Historial`: eliminar `registrarCambio()` de `app/Services/LiderazgoService.php` y cambiar todas sus llamadas (en `app/Http/Controllers/PersonasController.php`, `app/Services/TrasladoService.php`, `app/Services/RegistroMiembrosQr.php`, `app/Services/AvancesNivelService.php` y demás que aparezcan con `grep registrarCambio`); cambiar `app/Services/SedeService.php`, `app/Services/RolService.php` y `app/Services/NivelesService.php` para que escriban con `Historial::registrar()` (los cambios de permisos de un rol, como `cambios` por módulo «Eventos: lectura → escritura»); eliminar `app/Models/PersonaCambio.php`, `SedeCambio.php`, `RolCambio.php`, `NivelCambio.php` y sus relaciones `cambios()` en los modelos (depende de T015, T018)
- [X] T020 Crear el servicio de lectura `app/Services/LecturaHistorial.php`: `deRegistro(Model $sujeto, array $filtros, int $pagina)` (lista única con filtros `desde`, `hasta`, `usuario`, `accion`, 20 por página), `seccionesDePersona(Persona $p, array $filtros, array $paginas)` (las 6 categorías con contador, una consulta paginada por sección, cadena de traslados por `trasladada_desde_persona_id` leída con `SedeContext::sinFiltro` solo para esa cadena), `puedeEnlazar(User, string $tipo, int $id)` y `ocultarSensibles(CambioHistorial, User)` según la tabla de visibilidad de data-model.md; filtros inválidos se ignoran (depende de T014)
- [X] T021 [P] Crear los componentes de presentación `resources/views/components/historial/entrada.blade.php` (fecha y hora local, fecha del evento si difiere, icono + etiqueta de acción, actor con «Automático · por …», relacionado con enlace solo si `puedeEnlazar`, lista «Campo: antes → después», «(vacío)», «Clave cambiada», «—» para sensibles, `<details>` para largos, sede de origen si difiere, sin botones de editar ni borrar), `resources/views/components/historial/lista.blade.php` (tarjetas apiladas, paginador, «Sin movimientos») y `resources/views/components/historial/filtros.blade.php` (formulario `GET` con `x-form.*`, plegable en teléfono, conserva `pestana=historico`)
- [X] T022 [P] Crear el parcial de pestañas `resources/views/historial/_pestanas.blade.php` (patrón de `resources/views/sedes/show.blade.php`: `<ul class="nav nav-tabs">` con enlaces reales, `aria-current`, pestaña «Datos» por defecto y «Histórico» con icono `fa-history`; acepta pestañas adicionales) y el rasgo de controlador `app/Http/Controllers/Concerns/PestanaHistorico.php` (lee `pestana`, filtros y páginas de la petición y devuelve los datos para la vista con `LecturaHistorial`)

**Punto de control**: `php artisan test --filter=Historial` en verde para T003–T010; las entradas antiguas ya están en la tabla nueva y nada escribe en las tablas viejas.

---

## Fase 3: Historia de Usuario 1 - Pestaña «Histórico» del miembro (Prioridad: P1) 🎯 MVP

**Objetivo**: la ficha del miembro (y del líder) tiene la pestaña «Histórico» con las 6 secciones, y todo lo que le pasa al miembro (datos, encuentros, eventos y retiros, células, ministerios, liderazgo, niveles, traslados) queda registrado.

**Prueba independiente**: cambiar la dirección de un miembro, marcar su asistencia a un encuentro e inscribirlo en un retiro con dos abonos; abrir `personas/{id}?pestana=historico` y ver las entradas en sus secciones con fecha, hora, usuario y detalle.

### Pruebas para la Historia 1

- [X] T023 [P] [US1] Prueba de la pestaña del miembro: sin `pestana` se ve la ficha de datos; con `?pestana=historico` aparecen las 6 secciones en orden con su contador y «Sin movimientos» si están vacías; la edición de dirección y celular muestra «Dirección: … → …» y «Celular: … → …» con fecha, hora y usuario, y no lista campos sin cambio; los filtros `desde`/`hasta`/`usuario` se aplican a todas las secciones; `pagina_datos` pagina solo su sección (20 por página); funciona sin JavaScript (HTML del servidor) en `tests/Feature/Historial/PestanaMiembroTest.php`
- [X] T024 [P] [US1] Prueba de entradas de encuentros en el miembro: inscribir (`store`/`update` con `personas`), quitar, marcar asistencia y valor pagado (`actualizarAsistencia`) generan `inscrito_encuentro`, `quitado_encuentro`, `asistio_encuentro`, `no_asistio_encuentro` y `pago_encuentro` en la sección «Encuentros y retiros» con `detalle.fecha_evento` y enlace al encuentro; guardar dos veces sin cambios no duplica en `tests/Feature/Historial/MiembroEncuentrosTest.php`
- [X] T025 [P] [US1] Prueba de entradas de eventos en el miembro: inscripción, retiro de inscripción, abono registrado y abono eliminado aparecen en «Encuentros y retiros» con el valor; un usuario que ve la ficha pero no tiene lectura en Eventos ve la inscripción sin enlace y el monto como «—» en `tests/Feature/Historial/MiembroEventosTest.php`
- [X] T026 [P] [US1] Prueba de células, ministerios y liderazgo en el miembro: ingreso y salida de célula (agregar/quitar miembro y `actualizarIntegrantes` de célula), asistencia a reunión de célula, ingreso y salida de ministerio, cambio de líder, designación y retiro como líder aparecen en «Células y ministerios» y «Liderazgo» en `tests/Feature/Historial/MiembroCelulasMinisteriosTest.php`
- [X] T027 [P] [US1] Prueba de niveles y estado en el miembro: nivel registrado y quitado a mano, nivel completado por encuentro (`actor = automatico`, «Automático · por …»), inactivación, reactivación y alta/actualización por QR de miembros («Registro público por QR · …») en sus secciones en `tests/Feature/Historial/MiembroNivelesEstadoTest.php`
- [X] T028 [P] [US1] Prueba de permisos de la pestaña del miembro: administrador, pastor, líder (sobre un miembro de su rama) y el rol usuario (sobre sí mismo) la ven; un líder sobre un miembro fuera de su rama recibe 403/404 igual que la ficha; usuario de otra sede no la ve; desaparece la tarjeta «Historial de cambios» y la habilidad `verBitacora` en `tests/Feature/Historial/PermisosPestanaMiembroTest.php`
- [X] T029 [P] [US1] Prueba de la pestaña del líder: `lideres/{lider}?pestana=historico` muestra las mismas secciones que su ficha de miembro más los cambios de liderazgo en `tests/Feature/Historial/PestanaLiderTest.php`

### Implementación para la Historia 1

- [X] T030 [US1] Agregar `RegistraHistorial` a `app/Models/Persona.php` con `aliasHistorial()` = `persona`, módulo `miembros`, nombre `nombre_completo`, `camposHistorial()` con etiquetas (Nombres, Apellidos, Tipo de documento, Número de documento, Fecha de nacimiento, Celular, Correo, Dirección, Barrio, Ciudad, Estado, Líder, Es líder…), traducción de `lider_id` al nombre del líder y de `estado` con `Estados`, e ignorados (`updated_at`, `inactivado_en`, `trasladada_en`, `trasladada_a_sede_id`, `trasladada_desde_persona_id`) (depende de T016)
- [X] T031 [US1] Dar nombre de negocio a las acciones del miembro con `Historial::accion()` en `app/Http/Controllers/PersonasController.php` (`inactivar` → `inactivado`, `reactivar` → `reactivado`, cambio de estado → `estado`) y en `app/Services/LiderazgoService.php` (`cambiarLider` → `lider_cambiado`, designar y quitar líder → `designado_lider` / `quitado_lider`), envolviendo en `DB::transaction()` las acciones con varias escrituras
- [X] T032 [US1] Registrar la asistencia de encuentros en `app/Http/Controllers/EncuentrosController.php`: `store` y `update` con `Historial::sincronizacion()` sobre `personas()->sync()` (`inscrito_encuentro` / `quitado_encuentro`, resumen en el encuentro); `actualizarAsistencia` compara el pivote antes y después y escribe por persona `asistio_encuentro`, `no_asistio_encuentro` o `pago_encuentro` (con `modulo_sensible = encuentros` en el valor) y una entrada resumen `asistencia_actualizada`, todo en una transacción con `detalle.fecha_evento` = fecha del encuentro
- [X] T033 [P] [US1] Agregar `RegistraHistorial` a `app/Models/EventoPersona.php` (acciones `inscrito_evento` / `quitado_evento`, `sujetosHistorial()` = evento y persona con relacionado cruzado, `costo_individual` con `modulo_sensible = eventos`) y a `app/Models/EventoPago.php` (acciones `abono_registrado` / `abono_eliminado`, sujetos evento y persona, `monto` con `modulo_sensible = eventos`, `fecha_pago` como `fecha_evento`) (depende de T016)
- [X] T034 [US1] Registrar células y ministerios en el miembro: en `app/Http/Controllers/CelulasController.php` (`agregarMiembro`, `quitarMiembro`, la sincronización de miembros con `attach`/`detach` y la asistencia de reuniones con `asistentes()->sync()` → `ingreso_celula`, `salida_celula`, `asistio_reunion`), en `app/Services/LiderazgoService.php` (salida de células al cambiar de líder) y en `app/Http/Controllers/MinisteriosController.php` (`store`, `update`, `actualizarIntegrantes` → `ingreso_ministerio`, `salida_ministerio`, resumen `integrantes_actualizados`), todo con `Historial::sincronizacion()`
- [X] T035 [US1] Registrar niveles en el miembro: agregar `RegistraHistorial` a `app/Models/AvanceNivel.php` (sujeto persona, relacionado nivel, acciones `nivel_registrado` / `nivel_quitado`) y envolver la sincronía automática de `app/Services/AvancesNivelService.php` en `Historial::comoActor('automatico', 'Automático', …, $por)` con acción `nivel_automatico`, eliminando sus escrituras manuales al historial viejo (depende de T016)
- [X] T036 [US1] Registrar los registros públicos por QR: envolver `app/Services/RegistroPorQr.php` y `app/Services/RegistroMiembrosQr.php` en `Historial::comoActor('qr', 'Registro público por QR · '.$nombreDelQr, …)`, usar `sincronizacion()` para `syncWithoutDetaching` de encuentros, eventos y células, y las acciones `creado_qr`, `actualizado_qr`, `reactivado_qr`
- [X] T037 [US1] Registrar el traslado en `app/Services/TrasladoService.php`: llenar `trasladada_desde_persona_id` en la persona nueva y escribir `trasladado_desde` (en la nueva, categoría `sede`) y `trasladado_a` (en la de origen) con el nombre de la sede, dentro de la transacción existente
- [X] T038 [US1] Agregar la pestaña a la ficha del miembro: en `app/Http/Controllers/PersonasController.php` (`show`) usar `PestanaHistorico` y quitar la carga de `cambios` y `verBitacora`; en `resources/views/personas/show.blade.php` incluir `historial._pestanas`, mover el contenido actual a la pestaña «Datos», crear `resources/views/historial/_secciones.blade.php` (6 secciones con título, contador y `<x-historial.lista>`) y eliminar la tarjeta «Historial de cambios»; quitar `verBitacora` de `app/Policies/PersonaPolicy.php` (depende de T020, T021, T022)
- [X] T039 [US1] Agregar la pestaña a la ficha del líder en `app/Http/Controllers/LideresController.php` (`show`) y `resources/views/lideres/show.blade.php`, reutilizando `historial/_secciones.blade.php` (depende de T038)

**Punto de control**: el MVP funciona: el histórico del miembro muestra todo lo que le pasó, en secciones, desde el celular y sin JavaScript.

---

## Fase 4: Historia de Usuario 2 - Todo cambio en la plataforma queda registrado (Prioridad: P1)

**Objetivo**: toda acción de escritura de cualquier módulo deja su entrada, con nombres de negocio, actores especiales y sin secretos.

**Prueba independiente**: ejecutar cada ruta de escritura con datos válidos y comprobar exactamente su entrada; errores de validación o permisos no escriben nada.

### Pruebas para la Historia 2

- [X] T040 [P] [US2] Prueba de cobertura de rutas (contracts/servicio-historial.md, «Prueba de cobertura»): recorre `Route::getRoutes()` y, para cada ruta `POST`/`PUT`/`DELETE` que no esté en una lista explícita de exentas (`login`, `logout`, `sede-activa.update`, `registro*.buscar`, `recuperacion.store` si no cambia datos), exige un caso en el mapa de la prueba que ejecuta la acción y verifica al menos una entrada con usuario, acción y sujeto esperados; si aparece una ruta nueva sin caso, falla (CE-001) en `tests/Feature/Historial/CoberturaHistorialTest.php`
- [X] T041 [P] [US2] Prueba de no-registro: errores de validación, respuestas 403 y ediciones sin cambios no crean entradas en Miembros, Células, Eventos, Usuarios y Roles en `tests/Feature/Historial/SinCambioSinEntradaTest.php`
- [X] T042 [P] [US2] Prueba de secretos (CE-005): crear un usuario, generar y cambiar su clave (`users.clave.store`, `cuenta.clave.update`, `cambiar-clave.update`), atender una solicitud de clave y regenerar un QR; luego recorrer **todas** las entradas y fallar si alguna contiene el valor de una clave, token o código, o un campo secreto con valor; las entradas dicen «Clave generada» / «Clave cambiada» en `tests/Feature/Historial/SecretosHistorialTest.php`
- [X] T043 [P] [US2] Prueba de acciones masivas (RF-006): guardar la asistencia de 30 personas en un encuentro escribe 1 entrada resumen + 30 individuales con el mismo `lote`; guardarla de nuevo sin cambios no escribe nada en `tests/Feature/Historial/AccionesMasivasTest.php`

### Implementación para la Historia 2

- [X] T044 [P] [US2] Agregar `RegistraHistorial` a `app/Models/Celula.php` (nombre, líder, colíder, día, hora, dirección… con claves foráneas traducidas) y a `app/Models/CelulaReunion.php` (sujeto célula, acciones `reunion_registrada` / `reunion_eliminada`, `fecha` como `fecha_evento`) (depende de T016)
- [X] T045 [P] [US2] Agregar `RegistraHistorial` a `app/Models/Ministerio.php`, `app/Models/Evento.php` y `app/Models/Encuentro.php` (con `nivel_id` traducido al nombre del nivel y `estado` del encuentro legible) (depende de T016)
- [X] T046 [P] [US2] Agregar `RegistraHistorial` a `app/Models/NivelFormacion.php` y quitar de `app/Services/NivelesService.php` las escrituras que ahora cubre el rasgo, dejando `nivel_reordenado`, `nivel_desactivado` y `nivel_reactivado` con `Historial::accion()` (depende de T016, T019)
- [X] T047 [P] [US2] Agregar `RegistraHistorial` a `app/Models/Sede.php` y ajustar `app/Services/SedeService.php` para usar `Historial::accion()` en desactivar, reactivar y cambiar pastores (`pastores_cambiados`) (depende de T016, T019)
- [X] T048 [P] [US2] Agregar `RegistraHistorial` a `app/Models/User.php` (nombre, correo, rol (nombre del rol), sede, persona vinculada, activo; `password`, `remember_token` y cualquier campo de clave temporal como secretos) y usar `Historial::accion()` en `app/Http/Controllers/UsersController.php` (`rol_cambiado` cuando cambia el rol), `app/Http/Controllers/ClavesUsuarioController.php` (`clave_generada`, `clave_enviada`), `app/Http/Controllers/CuentaController.php` y `app/Http/Controllers/RecuperacionClaveController.php` (`clave_cambiada`) (depende de T016)
- [X] T049 [P] [US2] Agregar `RegistraHistorial` a `app/Models/Rol.php` (o mantener la escritura explícita de `app/Services/RolService.php` si el rasgo no ve los permisos) con los cambios por módulo en `cambios`, y acciones `rol_creado`, `rol_editado`, `rol_duplicado`, `rol_eliminado` (depende de T016, T019)
- [X] T050 [P] [US2] Agregar `RegistraHistorial` a `app/Models/SolicitudClave.php` (acciones `solicitud_creada`, `solicitud_atendida`, `solicitud_descartada` desde `app/Http/Controllers/SolicitudesClaveController.php` y `RecuperacionClaveController`) y a `app/Models/RegistroQr.php` y `app/Models/RegistroMiembroQr.php` (acciones `qr_abierto`, `qr_regenerado`, `qr_cerrado` desde `app/Http/Controllers/RegistroQrController.php` y `app/Http/Controllers/RegistroMiembrosQrController.php`; `token` como secreto) (depende de T016)
- [X] T051 [US2] Registrar las acciones restantes de `app/Http/Controllers/SinLiderController.php` (`asignar` → `lider_cambiado`) y la eliminación de células, ministerios, eventos y encuentros con su foto `detalle.datos` en sus controladores; envolver en `DB::transaction()` toda acción con varias escrituras que aún no lo esté
- [X] T052 [US2] Marcar `app/Support/MigracionSedes.php`, `app/Support/MigracionRedesZonas.php` y `database/seeders/*` para que escriban con `Historial::comoActor('sistema', 'Sistema', …)` o `Historial::sinRegistro()`, de modo que los datos de ejemplo no llenen el historial de ruido

**Punto de control**: `CoberturaHistorialTest` en verde: no queda ninguna ruta de escritura sin historial.

---

## Fase 5: Historia de Usuario 3 - Pestaña «Histórico» en cada módulo (Prioridad: P2)

**Objetivo**: las fichas de célula, ministerio, evento, encuentro, nivel, sede, usuario y rol tienen su pestaña «Histórico» con lista única y filtro por acción.

**Prueba independiente**: agregar un miembro a una célula, registrar una reunión y cambiar su nombre; abrir `celulas/{id}?pestana=historico` y ver las tres entradas.

### Pruebas para la Historia 3

- [X] T053 [P] [US3] Prueba de las pestañas de módulo: para célula, ministerio, evento, encuentro (`show`), nivel (`niveles/{id}/edit`), sede (`sedes/{id}`, junto a Datos, Organigrama y Registro de miembros), usuario (`users/{id}/edit`) y rol (`roles/{rol}`): sin parámetro se ve la pestaña de datos; con `?pestana=historico` se ve la lista con fecha, hora, usuario, acción y detalle; el filtro `accion` y la paginación funcionan; la autorización es la misma que la de la ficha en `tests/Feature/Historial/PestanasModulosTest.php`
- [X] T054 [P] [US3] Prueba de contenidos por módulo: célula (miembro agregado, reunión, cambio de líder), evento (inscripción, retiro, abono, abono eliminado con miembro y valor), rol («Eventos: lectura → escritura»), usuario (cambio de rol y de sede con antes y después, sin claves) y entradas antiguas migradas mezcladas en orden cronológico en `tests/Feature/Historial/ContenidoPestanasModulosTest.php`

### Implementación para la Historia 3

- [X] T055 [P] [US3] Agregar la pestaña a `app/Http/Controllers/CelulasController.php` + `resources/views/celulas/show.blade.php` y a `app/Http/Controllers/MinisteriosController.php` + `resources/views/ministerios/show.blade.php` con `PestanaHistorico`, `historial._pestanas` y `<x-historial.lista>` (depende de T020–T022)
- [X] T056 [P] [US3] Agregar la pestaña a `app/Http/Controllers/EventosController.php` + `resources/views/eventos/show.blade.php` y a `app/Http/Controllers/EncuentrosController.php` + `resources/views/encuentros/show.blade.php` (depende de T020–T022)
- [X] T057 [P] [US3] Agregar la pestaña a `app/Http/Controllers/NivelesController.php` (`edit`) + `resources/views/niveles/edit.blade.php` y a `app/Http/Controllers/UsersController.php` (`edit`) + `resources/views/users/edit.blade.php` (con cuidado de no anidar el formulario de edición dentro de la pestaña «Histórico») (depende de T020–T022)
- [X] T058 [P] [US3] Agregar la pestaña «Histórico» a la barra existente de `resources/views/sedes/show.blade.php` (reemplazando la tarjeta «Historial de la sede») y a `app/Http/Controllers/SedesController.php`; agregarla a `resources/views/roles/show.blade.php` y `app/Http/Controllers/RolesController.php`, reemplazando la tarjeta «Historial del rol» y `resources/views/roles/_celda.blade.php` si ya no se usa (depende de T020–T022)

**Punto de control**: todas las fichas tienen su «Histórico».

---

## Fase 6: Historia de Usuario 4 - Módulo «Control de cambios» (Prioridad: P2)

**Objetivo**: listado de todo lo ocurrido en la plataforma con la grilla estándar, sin exportación, por defecto solo para el administrador.

**Prueba independiente**: hacer cambios en tres módulos con dos usuarios; filtrar por un usuario y un rango de fechas y ver solo sus cambios; buscar una célula eliminada.

### Pruebas para la Historia 4

- [X] T059 [P] [US4] Prueba del módulo: el administrador ve `control-cambios` con columnas Fecha y hora, Usuario, Sede (en «Todas las sedes»), Módulo, Acción, Registro y Detalle, ordenado por fecha descendente; filtros por usuario, módulo, acción, rango de fecha y hora y nombre del registro (incluidos eliminados); el enlace del registro abre su ficha en `?pestana=historico` solo si existe y se puede ver; no hay botón de exportar y `/control-cambios/exportar` responde 404 en `tests/Feature/Historial/ControlCambiosTest.php`
- [X] T060 [P] [US4] Prueba de permisos y aislamiento del módulo: pastor, líder y usuario reciben 403 y no ven la opción del menú; al conceder lectura al rol pastor desde Roles, el pastor ve solo entradas de su sede; un rol sin acceso total con el módulo concedido solo ve entradas de personas y células visibles; el administrador en modo sede ve solo esa sede en `tests/Feature/Historial/PermisosControlCambiosTest.php`
- [X] T061 [P] [US4] Ajustar las pruebas de grillas para la excepción del principio III: agregar la lista de grillas sin exportación (`control-cambios`) y excluirla de los casos de exportación en `tests/Feature/Grillas/ContratoGrillaTest.php`, `tests/Feature/Grillas/ExportacionGrillasTest.php` y `tests/Feature/Grillas/AlcanceGrillasTest.php`; agregar un caso que verifica que esa grilla no muestra el botón de exportar y que su ruta no existe; incluirla en `tests/Feature/Grillas/GuardaGrillaEstandarTest.php` y `RespaldoSinJsTest.php`

### Implementación para la Historia 4

- [X] T062 [US4] Crear la grilla `app/Grillas/CambiosGrilla.php` (`id()` = `control-cambios`, columnas y filtros de contracts/rutas-y-permisos.md, búsqueda general en `sujeto_nombre`, `relacionado_nombre`, `actor_nombre` y detalle, `ordenInicial()` fecha descendente, `puedeVer()` con `Permisos::modulo('control_cambios')`, `puedeExportar()` siempre `false`, alcance de `AlcanceService` para usuarios sin acceso total) y su celda `resources/views/control-cambios/_celda.blade.php` reutilizando `<x-historial.entrada>` en modo resumen (depende de T014, T021)
- [X] T063 [US4] Crear `app/Http/Controllers/ControlCambiosController.php` (`index`, `datos`) con el rasgo `ServicioGrilla`, la vista `resources/views/control-cambios/index.blade.php` con `<x-ui.grilla>` sin exportación, las rutas `control-cambios.index` y `control-cambios.datos` bajo `Route::middleware('modulo:control_cambios')` en `routes/web.php` (sin ruta de exportar) y la opción «Control de cambios» en `config/menu.php` (depende de T062, T017)
- [X] T064 [US4] Si `<x-ui.grilla>` dibuja el botón de exportar sin consultar `puedeExportar()`, ajustar `resources/views/components/ui/grilla.blade.php` para ocultarlo cuando la grilla no exporta (depende de T062)

**Punto de control**: el administrador audita la plataforma entera desde un solo listado.

---

## Fase 7: Historia de Usuario 5 - El historial no se puede alterar (Prioridad: P3)

**Objetivo**: ninguna vía de la aplicación edita o borra entradas, y eliminar registros o usuarios no borra su historial.

**Prueba independiente**: eliminar un evento con historial y verlo en «Control de cambios»; comprobar que no hay rutas ni botones de edición o borrado.

### Pruebas para la Historia 5

- [X] T065 [P] [US5] Prueba de rutas y vistas: `route:list` no tiene ninguna ruta `PUT`, `PATCH` o `DELETE` bajo `control-cambios` ni con `historico`; el HTML de la pestaña y de la grilla no contiene formularios de edición ni de borrado; eliminar un evento, una célula y un usuario deja sus entradas visibles en «Control de cambios» con su foto de datos y el nombre del actor en `tests/Feature/Historial/InalterableRutasTest.php`

### Implementación para la Historia 5

- [X] T066 [US5] Revisar las relaciones `cascadeOnDelete` hacia tablas que alimentan el historial y confirmar que ninguna afecta a `historial_cambios` (no tiene claves foráneas); documentar en el comentario de `app/Models/CambioHistorial.php` por qué no las tiene y por qué el modelo prohíbe `update`/`delete` (depende de T014)

**Punto de control**: el historial es confiable.

---

## Fase 8: Pulido y aspectos transversales

- [X] T067 [P] Prueba del histórico del miembro trasladado (clarificación 3, RF-023): tras trasladar, el pastor de la sede destino ve **todas** las entradas anteriores (usuarios, montos, nombres) con la sede de origen indicada y sin enlaces a fichas de la otra sede; no ve ninguna otra entrada de la sede de origen ni por la pestaña ni por «Control de cambios»; un traslado doble (A → B → C) muestra la cadena completa en `tests/Feature/Historial/TrasladoHistorialTest.php`
- [X] T068 [P] Prueba de aislamiento general (CE-006): con `tests/Support/EscenarioDosSedes.php`, ningún usuario de la sede A ve entradas de la sede B en ninguna pestaña ni en «Control de cambios», salvo la cadena del miembro trasladado en `tests/Feature/Historial/AislamientoHistorialTest.php`
- [X] T069 [P] Prueba de rendimiento (CE-004): con 5 000 entradas sembradas para un miembro (sembradas con una inserción masiva directa sobre `historial_cambios`), la primera página de `personas/{id}?pestana=historico` responde en menos de 2 s y usa un número acotado de consultas (sin N+1) en `tests/Feature/Historial/RendimientoHistorialTest.php`
- [X] T070 [P] Prueba de catálogo completo: toda acción escrita durante la suite existe en `AccionesHistorial` y todo campo registrado tiene etiqueta (ningún nombre técnico en `cambios.*.etiqueta`) en `tests/Feature/Historial/CatalogoCompletoTest.php`
- [X] T071 Crear la migración `database/migrations/2026_10_30_000005_drop_historiales_antiguos.php`: verifica que `historial_cambios` tiene al menos tantas entradas con `detalle.legado` como filas tienen `persona_cambios`, `sede_cambios`, `rol_cambios` y `nivel_cambios`; si coinciden, borra las cuatro tablas; si no, aborta con un mensaje claro (depende de T018, T019); actualizar `tests/Feature/Historial/MigracionHistorialTest.php`
- [X] T072 [P] Agregar a la auditoría móvil las pantallas `personas/1?pestana=historico`, `lideres/3?pestana=historico`, `celulas/1?pestana=historico`, `eventos/1?pestana=historico`, `encuentros/1?pestana=historico`, `sedes/1?pestana=historico`, `roles/pastor?pestana=historico`, `users/1/edit?pestana=historico`, `niveles/1/edit?pestana=historico` y `control-cambios` en `tests/movil/pantallas.mjs`; corregir lo que falle sin agregar excepciones a `tests/movil/excepciones.json`
- [X] T073 [P] Agregar estilos de la pestaña y las entradas (tarjetas, icono + texto de acción, «antes → después» que se parte en líneas en teléfono, `<details>` tocable de 44 px) en `resources/css/historial.css` e importarlo en el punto de entrada de Vite
- [X] T074 [P] Documentar en `laravel_app/README.md` una sección «Historial de cambios»: cómo agregar `RegistraHistorial` a un modelo nuevo, cuándo usar `Historial::sincronizacion()`, `accion()` y `comoActor()`, la regla de secretos, la pestaña `?pestana=historico` y que el historial no se exporta
- [X] T075 [P] Enmendar `.specify/memory/constitution.md` (versión 1.2.0) para dejar constancia de las dos excepciones de la spec 015: la grilla `control-cambios` sin exportación (principio III) y el histórico del miembro trasladado visible en su sede actual (principio II), con fecha y motivo
- [X] T076 Ejecutar la guía `specs/015-historial-cambios/quickstart.md` completa en el navegador (como administrador, pastor y líder; en escritorio y en 360 px) y dejar `php artisan test`, `npm test`, `npm run test:movil` y `npm run build` en verde

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias.
- **Fundamentos (Fase 2)**: depende de la Fase 1 y **bloquea todas las historias**. Dentro de la fase: T011 → T014 → T015 → T016; T013 antes de T014 y T018; T018 → T019; T014 → T020; T021 y T022 en paralelo con T020.
- **Historias (Fases 3–7)**: dependen de la Fase 2.
  - **US1 (P1)**: sin dependencias de otras historias. Es el MVP.
  - **US2 (P1)**: independiente de US1 en archivos de modelos; comparte controladores con US1 (`EncuentrosController`, `CelulasController`, `MinisteriosController`), así que conviene hacerla **después** de US1 para no editar los mismos archivos a la vez.
  - **US3 (P2)**: usa los componentes de la Fase 2; sus pestañas muestran más contenido si US2 ya está hecha, pero se puede probar con las entradas que genera el rasgo.
  - **US4 (P2)**: solo depende de la Fase 2 (T014, T017, T021). Se puede hacer en paralelo con US1.
  - **US5 (P3)**: depende de la Fase 2; su prueba de eliminación se apoya en US4 para consultar registros borrados.
- **Pulido (Fase 8)**: después de las historias deseadas; T071 (borrar tablas viejas) solo después de T018 y T019 y con la migración verificada.

### Dentro de cada historia

- Pruebas primero (deben fallar), luego modelos, luego servicios y controladores, luego vistas.
- Todo cambio de varias escrituras va dentro de una transacción.

### Oportunidades de paralelismo

- Fase 2: T003–T010 (pruebas) en paralelo; T012, T013, T021 y T022 en paralelo.
- US1: T023–T029 en paralelo; T033 en paralelo con T031 y T032.
- US2: T040–T043 en paralelo; T044–T050 en paralelo (un modelo por tarea).
- US3: T053–T054 en paralelo; T055–T058 en paralelo (fichas distintas).
- US4: T059–T061 en paralelo; la historia completa en paralelo con US1.
- Pulido: T067–T070 y T072–T075 en paralelo.

---

## Ejemplo de paralelismo: Historia de Usuario 1

```bash
# Pruebas de la Historia 1, todas juntas:
Tarea: "Prueba de la pestaña del miembro en tests/Feature/Historial/PestanaMiembroTest.php"
Tarea: "Prueba de entradas de encuentros en tests/Feature/Historial/MiembroEncuentrosTest.php"
Tarea: "Prueba de entradas de eventos en tests/Feature/Historial/MiembroEventosTest.php"
Tarea: "Prueba de células, ministerios y liderazgo en tests/Feature/Historial/MiembroCelulasMinisteriosTest.php"
Tarea: "Prueba de niveles y estado en tests/Feature/Historial/MiembroNivelesEstadoTest.php"

# Modelos que no comparten archivos:
Tarea: "RegistraHistorial en app/Models/EventoPersona.php y app/Models/EventoPago.php"
Tarea: "RegistraHistorial en app/Models/AvanceNivel.php"
```

## Ejemplo de paralelismo: Historia de Usuario 2

```bash
Tarea: "RegistraHistorial en app/Models/Celula.php y CelulaReunion.php"
Tarea: "RegistraHistorial en app/Models/Ministerio.php, Evento.php y Encuentro.php"
Tarea: "RegistraHistorial en app/Models/User.php y acciones de claves"
Tarea: "RegistraHistorial en app/Models/SolicitudClave.php, RegistroQr.php y RegistroMiembroQr.php"
```

---

## Estrategia de implementación

### Primero el MVP (solo Historia 1)

1. Fase 1: Preparación.
2. Fase 2: Fundamentos (tabla, servicio, rasgo, lectura, componentes, integración de historiales).
3. Fase 3: Historia 1, la pestaña «Histórico» del miembro con sus secciones.
4. **Parar y validar**: secciones 2 y 6 de `quickstart.md` y `php artisan test --filter=Historial`.
5. Desplegar o mostrar al responsable.

### Entrega incremental

1. Fundamentos → Historia 1 (MVP: histórico del miembro).
2. Historia 2 → todo módulo registra (prueba de cobertura en verde).
3. Historia 4 → «Control de cambios» para el administrador.
4. Historia 3 → pestaña en todas las fichas.
5. Historia 5 + Pulido → inalterabilidad, traslado, aislamiento, rendimiento, móvil, borrado de tablas viejas y enmienda de la constitución.

---

## Notas

- [P] = archivos distintos y sin dependencias pendientes.
- Nunca ejecutar `migrate:fresh` sobre la base local (constitución); las pruebas usan bases temporales.
- No se agregan dependencias de Composer ni de npm.
- Los commits se hacen solo cuando el responsable lo pide.
