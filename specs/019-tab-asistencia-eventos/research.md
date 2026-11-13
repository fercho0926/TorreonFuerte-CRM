# Investigación: Pestaña Asistencia en Eventos

No quedaban marcadores `NEEDS CLARIFICATION`; estas son las decisiones técnicas tomadas.

> **Nota de alcance (implementación, 2026-11-13)**: el usuario pidió explícitamente que el panel «Registro por código QR» se quede en «Datos» y no se traslade a «Asistencia» (revierte D5 más abajo en cuanto a la pestaña, aunque la regla de permiso `gestionarRegistroQr` que describe sigue vigente sin cambios). Además, las tarjetas de «Miembros inscritos» se reemplazaron por una grilla estándar (`App\Grillas\InscripcionesEventoGrilla`) con un panel `<details>` por fila para registrar abonos. Ver spec.md § Clarifications para el detalle; las menciones de «panel QR en Asistencia» en las decisiones D1–D8 de abajo describen el diseño original, ya superado en ese punto.

## D1. Mecanismo de la pestaña nueva: reutilizar `PestanaHistorico` + `historial._pestanas`, no un componente nuevo

- **Decisión**: `EventosController::show()` calcula `$pestana = $this->pestanaActiva($request, ['asistencia'])` (el trait ya acepta una lista de pestañas adicionales válidas) y `eventos/show.blade.php` incluye `historial._pestanas` con `'otras' => ['asistencia' => ['Asistencia', 'fa-user-check']]`.
- **Justificación**: es exactamente el patrón que ya usa `SedesController`/`sedes/show.blade.php` para sus pestañas «Organigrama» y «Registro» (`pestanaActiva($request, ['organigrama', 'registro'])` + `'otras' => [...]`). Reutilizarlo cumple el principio VI (simplicidad) y no toca `historial._pestanas.blade.php`, que ya soporta esta forma de uso.
- **Alternativas descartadas**: construir una barra de pestañas propia para Eventos (duplicaría lo que ya existe); manejar la pestaña con JavaScript/AJAX en vez de un enlace real (rompería el principio V, mejora progresiva).

## D2. Reparto del contenido entre «Datos» y «Asistencia»

- **Decisión**: en `show.blade.php`, el bloque `@else` (hoy único contenido de «Datos») se separa en dos ramas: `@elseif($pestana === 'asistencia')` con el formulario de inscripción (`x-form.layout` → `eventos.personas.store`), el contador «Miembros inscritos (N)», las tarjetas de inscripción/pago y `partials.panel-registro-qr`; y `@else` (datos) con únicamente el `<dl>` de información general (nombre, tipo, fecha, lugar, costo, estado, descripción, total esperado, total recaudado — sin cambios en estos campos, ver aclaración en spec.md sobre mantenerlos en «Datos»).
- **Justificación**: es el pedido explícito del usuario; no exige ninguna variable ni consulta nueva, solo mover el marcado existente de una rama a otra.

## D3. Carga de datos en el controlador: `inscripciones` siempre, `personasSinInscribir` solo en «Asistencia»

- **Decisión original (descartada durante la implementación)**: cargar `$evento->load(['inscripciones' => ...])` únicamente cuando `$pestana === 'asistencia'`.
- **Corrección (hallada al implementar)**: `Evento::totalEsperado()` y `totalRecaudado()` — que se muestran en «Datos» — leen `$this->inscripciones` ya cargada con el filtro de alcance (`$visibles`). Si esa carga se condiciona a «Asistencia», en «Datos» esas sumas dispararían una carga perezosa de la relación **sin** el filtro de `$visibles`, exponiendo inscripciones fuera del alcance del usuario (un líder vería totales de toda la sede, no solo de su rama) — violación del principio II. Por eso `$evento->load(['inscripciones' => ...])` con el filtro `$visibles` se mantiene incondicional (igual que antes de esta funcionalidad); solo `personasSinInscribir` (la lista para el combo de inscripción, sin ninguna relevancia de seguridad) se calcula únicamente cuando `$pestana === 'asistencia'`.
- **Justificación**: la corrección prioriza el principio II (aislamiento por alcance) sobre el ahorro de una consulta; `personasSinInscribir` sigue siendo condicional porque no implica ningún riesgo de alcance, solo evita un cálculo que «Datos» e «Histórico» no usan.
- **Alternativa descartada**: condicionar también la carga de `inscripciones`, aceptando que «Datos» recalcule los totales con una consulta propia ya filtrada — más código para el mismo resultado que simplemente no condicionar la carga.

## D4. Redirecciones tras inscribir, quitar, pagar o eliminar un abono

- **Decisión**: `agregarPersona`, `quitarPersona`, `registrarPago` y `eliminarPago` cambian su `redirect()->route('eventos.show', $evento)` por `redirect()->route('eventos.show', [$evento, 'pestana' => 'asistencia'])`.
- **Justificación**: cumple RF-005 (el usuario permanece en «Asistencia» tras cualquier acción); hoy estas acciones redirigen a la pestaña por defecto («Datos»), que es precisamente el comportamiento que el usuario pide cambiar.

## D5. Permiso del panel de código QR: no cambia, pero corrige una suposición de la especificación

- **Hallazgo**: `partials.panel-registro-qr` ya está protegido por completo con `@puede('gestionarRegistroQr', $destino)` (`AppServiceProvider`), y esa regla exige **permiso de escritura** sobre el módulo de eventos (`$user->nivelEn(Modulos::EVENTOS) >= Niveles::ESCRITURA`), sin excepción para accesoTotal. Esto es anterior a esta funcionalidad: un usuario de solo lectura nunca ha visto el panel QR, ni siquiera hoy en «Datos».
- **Decisión**: no se modifica esta regla. Se corrigieron RF-004, la Historia 2 (escenario 4) y el primer caso límite de `spec.md` para reflejar que, en modo de solo lectura, la pestaña «Asistencia» muestra la lista de miembros inscritos con su estado de pago, pero **no** el panel de código QR.
- **Justificación**: mantener la especificación consistente con el comportamiento real del sistema evita escribir una prueba que no puede pasar (un usuario de solo lectura jamás podrá ver el panel QR, se mueva o no de pestaña).

## D6. Pruebas existentes que dejan de pasar por el traslado de contenido

- **Hallazgo**: varias pruebas abren `eventos.show` **sin** `?pestana=...` (es decir, la pestaña «Datos» por defecto) y esperan ver contenido que esta funcionalidad traslada a «Asistencia»:
  - `tests/Feature/RegistroQr/PanelTest.php` y `ListaRegistradosTest.php` (caso `destino = 'evento'` del `DataProvider` compartido con célula/encuentro — esos dos **no** cambian, solo el de evento).
  - `tests/Feature/RegistroQr/AislamientoTest.php::test_el_admin_de_la_sede_ve_de_otra_sede_sin_enlace_a_la_ficha` (verifica un registro QR pendiente en la ficha del evento).
  - `tests/Feature/Aislamiento/AislamientoEventosTest.php` (`assertSee('PersonaAlfa')` sobre la ficha por defecto).
  - `tests/Feature/Formularios/ConfirmacionesTest.php` (confirmación «¿Eliminar este abono?» sobre la ficha por defecto).
  - `tests/Support/RecorreFormularios.php`: la entrada `'evento (ficha)'` de `pantallasConFormulariosIncrustados()` apunta a la ficha por defecto; la consumen `EstructuraFormulariosTest`, `InventarioFormulariosTest` y `AccesibilidadFormulariosTest`, que esperan encontrar allí el formulario de inscripción y los de abono.
- **Decisión**: estas pruebas se actualizan para abrir la ficha con `?pestana=asistencia` cuando el destino es un evento (las de célula y encuentro no cambian: esta funcionalidad no los toca). Se detalla en `tasks.md`.
- **Justificación**: principio IV (pruebas primero) exige que la suite completa quede en verde; el traslado de contenido es intencional, así que las pruebas deben apuntar a la pestaña nueva, no describir el comportamiento viejo.

## D7. `Cache-Control: no-store` en la ficha del evento

- **Hallazgo**: `SedesController::show` ya agrega `Cache-Control: no-store, private` cuando la pestaña activa muestra personas (organigrama, registro). `EventosController::show` **no** lo hace hoy, a pesar de que «Datos» ya muestra nombres de miembros inscritos.
- **Decisión**: esta funcionalidad no agrega ni corrige esa cabecera. El traslado de contenido a «Asistencia» no empeora la exposición actual (la misma información ya se servía sin esa cabecera en «Datos»); corregirlo es una mejora independiente, no pedida en la especificación, y se deja como posible funcionalidad futura.
- **Justificación**: mantener el alcance acotado a lo que pidió el usuario (RF-001 a RF-008); no se introduce una regresión nueva de principio VII, solo se conserva una brecha preexistente.

## D8. Pantalla a agregar a la auditoría móvil

- **Decisión**: agregar `'eventos/1?pestana=asistencia'` a `CON_SESION` en `tests/movil/pantallas.mjs`, junto a las entradas existentes `'eventos/1'` y `'eventos/1?pestana=historico'`.
- **Justificación**: principio VII exige que toda pantalla con contenido nuevo pase la auditoría de 320–1920 px y controles de 44 px; «Asistencia» es contenido que antes solo se auditaba como parte de «Datos» y ahora vive en su propia URL.
