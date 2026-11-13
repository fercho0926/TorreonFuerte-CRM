---

description: "Lista de tareas para la funcionalidad: Pestaña Asistencia en Eventos"
---

# Tareas: Pestaña Asistencia en Eventos

> **Nota de alcance (implementación, 2026-11-13)**: dos cambios posteriores a esta lista, hechos a pedido del usuario durante la implementación: (1) el panel «Registro por código QR» se quedó en «Datos», no se trasladó a «Asistencia» (afecta las tareas que lo mencionan); (2) las tarjetas de «Miembros inscritos» se convirtieron en una grilla estándar (`App\Grillas\InscripcionesEventoGrilla` + `EventoInscripcionesController`, rutas `eventos.inscripciones.*`), con un panel `<details>` por fila para registrar abonos. Ver spec.md § Clarifications y research.md para el detalle; las tareas de abajo describen el diseño original (tarjetas + QR en Asistencia).

**Entrada**: Documentos de diseño de `specs/019-tab-asistencia-eventos/`

**Prerrequisitos**: plan.md, spec.md, research.md, data-model.md, contracts/pestana-asistencia-eventos.md, quickstart.md

**Pruebas**: Esta funcionalidad SÍ incluye tareas de prueba — el Principio IV de la constitución exige pruebas automáticas escritas antes o junto con el código, sin excepción.

**Organización**: Las tareas se agrupan por historia de usuario (spec.md) para poder implementar y probar cada una de forma independiente.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[Historia]**: a qué historia de usuario pertenece la tarea (US1, US2, US3)
- Las rutas de archivo son relativas a `laravel_app/`

---

## Fase 1: Fundacional (bloqueante para todas las historias)

**Propósito**: habilitar el valor `asistencia` como pestaña reconocida, antes de que cualquier historia pueda implementarse o probarse.

**⚠️ CRÍTICO**: ninguna historia de usuario puede empezar hasta cerrar esta fase.

- [ ] T001 En `app/Http/Controllers/EventosController.php`, método `show()`: calcular `$pestana = $this->pestanaActiva($request, ['asistencia']);` una sola vez (reemplaza la llamada actual `$this->pestanaActiva($request)` usada solo para detectar `'historico'`). Pasar siempre `'pestana' => $pestana` a la vista `eventos.show` en los tres casos (`historico`, `asistencia`, `datos`), igual que ya hace `SedesController::show()` con sus pestañas adicionales.
- [ ] T002 En `resources/views/eventos/show.blade.php`, actualizar el `@include('historial._pestanas', ...)` para pasar `'pestana' => $pestana` (en vez de `$pestana ?? 'datos'`) y agregar `'otras' => ['asistencia' => ['Asistencia', 'fa-user-check']]`, siguiendo el mismo patrón que `resources/views/sedes/show.blade.php` usa para «Organigrama»/«Registro».

**Punto de control**: al abrir `eventos.show` con `?pestana=asistencia` ya no cae a «Datos» por defecto y la barra de pestañas muestra «Datos», «Asistencia», «Histórico» en ese orden (aunque el contenido de «Asistencia» todavía no exista: lo agrega la Historia 2).

---

## Fase 2: Historia de Usuario 1 - Ver los datos del evento sin el ruido de inscripciones (Prioridad: P1) 🎯 MVP

**Objetivo**: la pestaña «Datos» muestra únicamente la información general del evento, sin el formulario de inscripción, el panel QR ni las tarjetas de miembros inscritos.

**Prueba independiente**: abrir la ficha de un evento con miembros inscritos y pagos; en «Datos» (por defecto) no debe verse ninguno de esos tres bloques.

### Pruebas para la Historia de Usuario 1 ⚠️

> **NOTA**: escribir estas pruebas primero y verificar que fallan antes de tocar la vista.

- [ ] T003 [P] [US1] En `tests/Feature/Eventos/PestanaAsistenciaTest.php` (archivo nuevo), prueba `test_la_pestana_datos_no_muestra_inscripcion_ni_qr_ni_tarjetas_de_pago`: usando `EscenarioDosSedes` (evento con inscripción y pago ya sembrados), `GET eventos.show` sin `pestana` y `assertDontSee` sobre «Inscribir miembro», «Registro por código QR» y el nombre de la persona inscrita; `assertSee` sobre el nombre del evento y su descripción.
- [ ] T004 [P] [US1] En el mismo archivo, prueba `test_la_pestana_datos_de_un_evento_sin_inscritos_no_muestra_mensaje_de_inscripciones`: evento sin inscripciones, `GET eventos.show` sin `pestana`, `assertDontSee('Este evento aún no tiene miembros inscritos.')`.

### Implementación para la Historia de Usuario 1

- [ ] T005 [US1] En `resources/views/eventos/show.blade.php`, dentro de la rama `@else` (datos), quitar el formulario `x-form.layout` de «Inscribir miembro», el bloque de `@forelse($evento->inscripciones as $inscripcion)` con sus tarjetas, y el `@include('partials.panel-registro-qr', ...)`. La rama `@else` debe quedar únicamente con el `<dl>` de información general (nombre, tipo, fecha, lugar, costo, estado, descripción, total esperado, total recaudado) y el `<x-ui.estado>`. (El contenido retirado se reubica en la Historia 2, tarea T008 — no se borra, se traslada.)

**Punto de control**: T003 y T004 pasan; la pestaña «Datos» queda limpia. (La Historia 2 aún no ha reubicado el contenido retirado, así que de forma temporal desaparece hasta completarla — por eso T005 y T008 se implementan en la misma sesión de trabajo.)

---

## Fase 3: Historia de Usuario 2 - Gestionar inscripciones y pagos desde la pestaña Asistencia (Prioridad: P1) 🎯 MVP

**Objetivo**: la pestaña «Asistencia» agrupa el formulario de inscripción, el panel de código QR y las tarjetas de miembros inscritos con su control de pagos; toda acción (inscribir, quitar, abonar, eliminar abono) deja al usuario en «Asistencia».

**Prueba independiente**: abrir `?pestana=asistencia`, inscribir un miembro, registrarle un abono y eliminarlo, sin salir nunca de esa pestaña; repetir como usuario de solo lectura y comprobar que ve la lista pero no los controles ni el panel QR.

### Pruebas para la Historia de Usuario 2 ⚠️

> **NOTA**: escribir estas pruebas primero y verificar que fallan antes de tocar el controlador/la vista.

- [ ] T006 [P] [US2] En `tests/Feature/Eventos/PestanaAsistenciaTest.php`, prueba `test_la_pestana_asistencia_muestra_formulario_qr_y_tarjetas_con_permiso_de_escritura`: usuario con escritura en Eventos, `GET eventos.show?pestana=asistencia`, `assertSee` sobre «Inscribir miembro», «Registro por código QR», «Miembros inscritos (1)» y el nombre de la persona inscrita con su saldo pendiente.
- [ ] T007 [P] [US2] En el mismo archivo, prueba `test_la_pestana_asistencia_en_solo_lectura_oculta_formulario_acciones_y_panel_qr`: usuario con solo lectura en Eventos, `GET eventos.show?pestana=asistencia`, `assertSee` el nombre de la persona inscrita y su estado de pago, `assertDontSee` «Inscribir miembro», «Registro por código QR» y el botón «Quitar».
- [ ] T008 [P] [US2] En el mismo archivo, prueba `test_inscribir_abonar_y_eliminar_abono_dejan_al_usuario_en_asistencia`: `POST eventos.personas.store`, `POST eventos.pagos.store` y `DELETE eventos.pagos.destroy` deben redirigir con `assertRedirect` a una URL que contenga `pestana=asistencia`.
- [ ] T009 [P] [US2] En el mismo archivo, prueba `test_asistencia_sin_inscritos_muestra_formulario_qr_y_mensaje_vacio`: evento sin inscripciones, usuario con escritura, `?pestana=asistencia` muestra el formulario, el panel QR y «Este evento aún no tiene miembros inscritos.».

### Implementación para la Historia de Usuario 2

- [ ] T010 [US2] En `resources/views/eventos/show.blade.php`, agregar la rama `@elseif($pestana === 'asistencia')` con, en este orden: el formulario `x-form.layout` de «Inscribir miembro» (gated por `@puedeModulo('eventos', 'escritura')`), el contador «Miembros inscritos (N)», el `@forelse($evento->inscripciones as $inscripcion)` con sus tarjetas de costo/pagos/abonos/quitar, y al final `@include('partials.panel-registro-qr', ['destino' => $evento])`. Es el mismo contenido retirado de «Datos» en T005, sin cambios de marcado. (Depende de T005.)
- [ ] T011 [US2] En `app/Http/Controllers/EventosController.php`, método `show()`: calcular `personasSinInscribir` únicamente cuando `$pestana === 'asistencia'` (colección vacía en los otros casos). `$evento->load(['inscripciones' => ...])` con el filtro `$visibles` se mantiene incondicional (no solo para «asistencia»), porque `totalEsperado()`/`totalRecaudado()` de «Datos» la necesitan ya filtrada por alcance — condicionarla rompería el principio II (ver research.md, D3). (Depende de T001.)
- [ ] T012 [US2] En `app/Http/Controllers/EventosController.php`, cambiar el `redirect()->route('eventos.show', $evento)` por `redirect()->route('eventos.show', [$evento, 'pestana' => 'asistencia'])` en los métodos `agregarPersona`, `quitarPersona`, `registrarPago` y `eliminarPago`.
- [ ] T013 [P] [US2] Actualizar `tests/Feature/RegistroQr/PanelTest.php`: en `rutaFicha()`, cuando `$destino === 'evento'`, agregar `?pestana=asistencia` a la URL devuelta (célula y encuentro no cambian).
- [ ] T014 [P] [US2] Actualizar `tests/Feature/RegistroQr/ListaRegistradosTest.php`: en `test_la_ficha_lista_a_los_registrados_con_su_origen_fecha_y_hora`, `test_sin_registros_se_muestra_el_estado_vacio` y `test_otra_sede_no_ve_los_registros`, al construir la URL de la ficha para `evento` (o cuando `$destino === 'evento'`), agregar `?pestana=asistencia` (célula y encuentro no cambian).
- [ ] T015 [P] [US2] Actualizar `tests/Feature/RegistroQr/AislamientoTest.php::test_el_admin_de_la_sede_ve_de_otra_sede_sin_enlace_a_la_ficha`: cambiar `route('eventos.show', $this->A['evento'])` y `route('eventos.show', $this->B['evento'])` por `route('eventos.show', [$this->A['evento'], 'pestana' => 'asistencia'])` / `[$this->B['evento'], 'pestana' => 'asistencia']`.
- [ ] T016 [P] [US2] Actualizar `tests/Feature/Aislamiento/AislamientoEventosTest.php` línea 29: cambiar `route('eventos.show', $this->A['evento'])` por `route('eventos.show', [$this->A['evento'], 'pestana' => 'asistencia'])` para que la aserción `assertSee('PersonaAlfa')` siga siendo válida.
- [ ] T017 [P] [US2] Actualizar `tests/Feature/Formularios/ConfirmacionesTest.php` líneas 40 y 63: cambiar `route('eventos.show', $this->A['evento'])` por `route('eventos.show', [$this->A['evento'], 'pestana' => 'asistencia'])` en ambos usos (la clave del arreglo de `test_las_acciones_delicadas_declaran_el_dialogo_propio` y la llamada de `test_el_dialogo_incluye_el_detalle_de_las_consecuencias`).
- [ ] T018 [P] [US2] Actualizar `tests/Support/RecorreFormularios.php`: en `pantallasConFormulariosIncrustados()`, cambiar `'evento (ficha)' => route('eventos.show', $this->A['evento'])` por `'evento (ficha)' => route('eventos.show', [$this->A['evento'], 'pestana' => 'asistencia'])`, para que `EstructuraFormulariosTest`, `InventarioFormulariosTest` y `AccesibilidadFormulariosTest` sigan encontrando allí el formulario de inscripción y los de abono.

**Punto de control**: T006–T009 pasan; T013–T018 (pruebas que existían antes de esta funcionalidad) vuelven a pasar; la Historia 1 y la Historia 2 juntas dejan la ficha del evento funcionalmente completa.

---

## Fase 4: Historia de Usuario 3 - Navegar entre las tres pestañas en el orden correcto (Prioridad: P2)

**Objetivo**: las tres pestañas aparecen siempre en el orden Datos → Asistencia → Histórico, y los enlaces existentes con `?pestana=datos` / `?pestana=historico` (y uno desconocido) se comportan igual que antes de esta funcionalidad.

**Prueba independiente**: abrir la ficha de un evento y verificar el orden de las pestañas y que cada valor de `?pestana=` lleva al contenido correcto, incluido un valor inválido.

**Nota**: la implementación de esta historia ya quedó resuelta por la Fase 1 (Fundacional) y la Fase 3 (T010); esta fase agrega las pruebas que lo confirman de forma independiente.

### Pruebas para la Historia de Usuario 3

- [ ] T019 [P] [US3] En `tests/Feature/Eventos/PestanaAsistenciaTest.php`, prueba `test_las_tres_pestanas_aparecen_en_orden_datos_asistencia_historico`: `GET eventos.show` y comprobar con `assertSeeInOrder(['Datos', 'Asistencia', 'Histórico'])` (o comparando las posiciones de `pestana=asistencia` y `pestana=historico` en el HTML).
- [ ] T020 [P] [US3] En el mismo archivo, prueba `test_pestana_desconocida_cae_a_datos`: `GET eventos.show?pestana=lo-que-sea` muestra el mismo contenido que sin parámetro (información general, sin inscripciones).
- [ ] T021 [P] [US3] Ejecutar `php artisan test --filter=PestanasModulosTest` y `php artisan test --filter=ContenidoPestanasModulosTest` (ya cubren `?pestana=historico` para `evento` con el `DataProvider` de fichas) y confirmar que siguen en verde sin modificarlos — documentan que RF-007 no rompió el contrato de «Histórico».

**Punto de control**: las tres historias de usuario quedan completas e independientemente verificables.

---

## Fase 5: Pulido y verificación final

**Propósito**: cerrar la auditoría móvil y la validación de punta a punta de `quickstart.md`.

- [ ] T022 [P] Agregar `'eventos/1?pestana=asistencia'` a `CON_SESION` en `tests/movil/pantallas.mjs`, junto a las entradas existentes `'eventos/1'` y `'eventos/1?pestana=historico'`.
- [ ] T023 Ejecutar la guía de validación completa de `specs/019-tab-asistencia-eventos/quickstart.md`: `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` en verde, más el recorrido manual en el navegador (pasos 1–15) y `tests/movil/excepciones.json` sigue vacío.

---

## Dependencias y orden de ejecución

- **Fundacional (Fase 1)**: sin dependencias; bloquea todas las historias.
- **Historia 1 (Fase 2)**: depende de la Fase 1. T003/T004 (pruebas) antes que T005 (implementación).
- **Historia 2 (Fase 3)**: depende de la Fase 1 y de T005 (Historia 1), porque T010 reubica el contenido que T005 retira de «Datos». T006–T009 (pruebas) antes que T010–T012 (implementación); T013–T018 (reparar pruebas existentes) después de T010–T012.
- **Historia 3 (Fase 4)**: depende de la Fase 1 y de la Fase 3 completas (verifica contenido que ambas entregan).
- **Pulido (Fase 5)**: depende de que las Historias 1, 2 y 3 estén completas.

### Oportunidades de paralelismo

- T003 y T004 (Historia 1) se pueden escribir en paralelo.
- T006–T009 (Historia 2, pruebas nuevas) se pueden escribir en paralelo.
- T013–T018 (Historia 2, reparación de pruebas existentes) tocan archivos distintos y se pueden hacer en paralelo entre sí, una vez cerrados T010–T012.
- T019–T021 (Historia 3) se pueden escribir en paralelo.
- T005 y T010 tocan el mismo archivo (`eventos/show.blade.php`) y deben hacerse en secuencia, lo mismo que T001/T011/T012 sobre `EventosController.php`.

---

## Estrategia de implementación

### MVP (Historias 1 y 2 solamente)

1. Fase 1 (Fundacional).
2. Fase 2 (Historia 1): retirar el contenido de «Datos».
3. Fase 3 (Historia 2): reubicarlo en «Asistencia» y corregir las redirecciones y las pruebas existentes.
4. **Detener y validar**: en este punto la ficha del evento ya tiene las 3 pestañas funcionando con el contenido en el lugar correcto — es el valor completo que pidió el usuario.

### Entrega incremental

1. Fundacional → pestaña reconocida.
2. Historia 1 + Historia 2 → MVP completo (no tiene sentido entregar la Historia 1 sola: dejaría el contenido de inscripción sin ningún lugar donde verlo).
3. Historia 3 → confirma el contrato de navegación con pruebas dedicadas.
4. Pulido → auditoría móvil y validación de punta a punta.
