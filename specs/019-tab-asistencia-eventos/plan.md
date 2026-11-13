# Plan de Implementación: Pestaña Asistencia en Eventos

**Rama**: `019-tab-asistencia-eventos` | **Fecha**: 2026-11-13 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de funcionalidad en `/specs/019-tab-asistencia-eventos/spec.md`

## Resumen

La ficha de un evento pasa de 2 pestañas (Datos, Histórico) a 3 (Datos, Asistencia, Histórico), reutilizando el mecanismo de pestañas ya existente (`PestanaHistorico::pestanaActiva()` + `historial._pestanas` con el parámetro `otras`, el mismo patrón que ya usa Sedes para «Organigrama» y «Registro»). «Datos» conserva la información general del evento **y** el panel de registro por código QR; «Asistencia» agrupa el formulario para inscribir un miembro y el control de pagos de los miembros inscritos — contenido que hoy vive en «Datos» (el panel QR se queda en «Datos» por pedido explícito del usuario, ver spec.md § Clarifications). Las redirecciones de inscribir, quitar, registrar abono y eliminar abono pasan a apuntar a `?pestana=asistencia` en vez de a la pestaña por defecto. El listado de miembros inscritos se implementó como una grilla estándar (`App\Grillas\InscripcionesEventoGrilla`) en vez de tarjetas, por volumen; el botón «Registrar abono» de cada fila abre un panel `<details>` (sin JavaScript) con el historial de abonos y el alta de uno nuevo. No hay cambios de base de datos ni de modelos. Ver decisiones en [research.md](research.md).

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; vistas Blade; JavaScript ES modules (Vite) solo donde ya existía

**Dependencias Principales**: Blade, AdminLTE 4 / Bootstrap 5, componentes `x-form.*` / `x-ui.*` y partial `historial._pestanas` (ya existentes); sin librerías nuevas

**Almacenamiento**: Base de datos existente (`eventos`, `evento_persona`, `evento_pagos`); sin migraciones, sin cambios de esquema

**Pruebas**: PHPUnit (contenido por pestaña, permisos, redirecciones, aislamiento por sede), Playwright `npm run test:movil` (320–1920 px, 44 px)

**Plataforma Objetivo**: Web responsive e instalable (PWA); prioridad celular

**Tipo de Proyecto**: Aplicación web monolítica Laravel (`laravel_app/`)

**Objetivos de Rendimiento**: Sin regresión perceptible; `personasSinInscribir` deja de calcularse fuera de «Asistencia», que es lo único que la usa. La carga de `inscripciones` (con `persona`/`pagos`, filtrada por `$visibles`) se mantiene en «Datos» porque `totalEsperado()`/`totalRecaudado()` la necesitan ya filtrada por alcance (ver research.md, D3)

**Restricciones**: Sin desplazamiento horizontal de la página de 320 a 1920 px; funciona sin JavaScript (enlaces reales de pestaña, formularios estándar); no se agrava ni se corrige la ausencia preexistente de `Cache-Control: no-store` en esta ficha (fuera de alcance de esta funcionalidad, ver Verificación de la Constitución)

**Escala/Alcance**: 1 vista (`eventos/show.blade.php`), 1 controlador (`EventosController`), 1 partial reutilizado (`historial._pestanas`); ningún evento tiene más de unos pocos cientos de inscripciones

## Verificación de la Constitución

*PUERTA: debe pasar antes de la investigación de la Fase 0. Se revisa de nuevo tras el diseño de la Fase 1.*

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple: todos los documentos de esta funcionalidad están en español. |
| II. Aislamiento por sede y alcance por rol | Cumple: no se toca ninguna consulta de alcance; `EventosController::show` sigue usando `idsDePersonasVisibles()` y el filtro global de sede. Ningún dato nuevo se expone; solo cambia en qué pestaña se muestra. |
| III. Grillas estándar | No aplica: las tarjetas de miembros inscritos son contenido de una ficha, no un listado principal (excepción explícita de la constitución). |
| IV. Pruebas primero | Cumple: se actualizan y agregan pruebas PHPUnit (contenido por pestaña, redirecciones, permisos) antes de dar por cerrada la tarea; ver plan de tareas. |
| V. Mejora progresiva | Cumple: la pestaña nueva es un enlace real (`<a href="...?pestana=asistencia">`) generado por `historial._pestanas`; los formularios de inscripción y pago ya son componentes `x-form.*` que funcionan sin JavaScript. No se agrega JavaScript nuevo. |
| VI. Simplicidad | Cumple: se reutiliza el mecanismo de pestañas de `PestanaHistorico`/`historial._pestanas` (mismo patrón que Sedes con «Organigrama»/«Registro»); no se crea ningún componente, servicio ni tabla nueva. |
| VII. Diseño primero para celular | Cumple: se reutilizan los mismos componentes (`x-ui.tarjeta`, `x-form.*`, `tabla-apilable`) que ya cumplían 44 px / 16 px en «Datos»; se agrega `eventos/1?pestana=asistencia` a `tests/movil/pantallas.mjs`. La ficha de evento no agrega ni quita persistencia de datos de personas en el teléfono (no usa el service worker ni `localStorage`); la ausencia de `Cache-Control: no-store` es preexistente a esta funcionalidad (ya exponía nombres de personas en «Datos» antes del cambio) y no se agrava: se documenta como seguimiento fuera de alcance, no como violación nueva. |

**Resultado de la puerta**: sin violaciones nuevas; no se requiere Seguimiento de Complejidad. El punto preexistente de `Cache-Control` se deja anotado en research.md como mejora futura, no como bloqueo de esta funcionalidad.

**Reevaluación tras el diseño (Fase 1)**: se mantiene sin violaciones. El diseño no agrega consultas fuera del alcance existente ni almacenamiento nuevo en el dispositivo.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/019-tab-asistencia-eventos/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── pestana-asistencia-eventos.md
├── checklists/
│   └── requirements.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código Fuente (raíz del repositorio, `laravel_app/`)

```text
laravel_app/
├── app/Http/Controllers/EventosController.php          # show(): $pestana = pestanaActiva($request, ['asistencia'])
│                                                         #   carga inscripciones/personasSinInscribir solo si pestana === 'asistencia'
│                                                         # agregarPersona/quitarPersona/registrarPago/eliminarPago:
│                                                         #   redirigen a eventos.show con ?pestana=asistencia
├── resources/views/eventos/
│   └── show.blade.php                                   # 3 ramas: historico | asistencia | datos (antes: 2)
└── tests/
    ├── Feature/Eventos/
    │   └── PestanaAsistenciaTest.php                     # nuevo: contenido de cada pestaña, permisos, redirecciones
    ├── Feature/RegistroQr/
    │   ├── PanelTest.php                                 # actualizar: destino 'evento' abre con ?pestana=asistencia
    │   ├── ListaRegistradosTest.php                       # idem
    │   └── AislamientoTest.php                            # idem (test_el_admin_de_la_sede_ve_de_otra_sede...)
    ├── Feature/Aislamiento/AislamientoEventosTest.php     # actualizar aserción de "PersonaAlfa" con ?pestana=asistencia
    ├── Feature/Formularios/ConfirmacionesTest.php         # actualizar aserción de "¿Eliminar este abono?"
    ├── Support/RecorreFormularios.php                     # 'evento (ficha)' -> agregar ?pestana=asistencia
    └── movil/pantallas.mjs                                # + 'eventos/1?pestana=asistencia'
```

**Decisión de Estructura**: monolito Laravel existente; no se agregan controladores, servicios, rutas ni tablas. Toda la lógica de reparto de contenido es de vista (`show.blade.php`) y de cuál pestaña está activa (`EventosController`), reutilizando `PestanaHistorico` y `historial._pestanas` tal como ya lo hace `SedesController`.

## Seguimiento de Complejidad

Sin violaciones de la constitución que justificar.
