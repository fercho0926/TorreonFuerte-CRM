# Plan de Implementación: Auditoría QA y mejoras de UX/UI del CRM

**Rama**: `018-auditoria-qa-crm` | **Fecha**: 2026-11-10 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de funcionalidad en `/specs/018-auditoria-qa-crm/spec.md`

## Resumen

Esta funcionalidad corrige 7 defectos concretos encontrados en una sesión de QA en vivo contra producción, sin agregar pantallas nuevas. La investigación de código (hecha antes de este plan) localizó la causa exacta de cada uno:

1. **Fuga de información en errores (RF-001/RF-002, P1)**: el `.env` real del servidor de producción tiene `APP_DEBUG=true` (contradice `laravel_app/.env.production`, que sí trae `APP_DEBUG=false`). Además, `.cpanel.yml` copia con `cp -R laravel_app/* $DEPLOYPATH`, y ese patrón `*` de Bash **no incluye archivos ocultos** (`.env`), así que el pipeline de despliegue nunca ha tocado el `.env` real del servidor desde su creación manual. Esto es, ante todo, una corrección operativa (editar el `.env` del servidor y limpiar caché de configuración), no un cambio de código; como refuerzo de código se agrega una vista `errors/405.blade.php` para que el *fallback* no dependa solo de que `APP_DEBUG` esté bien puesto.
2. **Credenciales de demo en el login (RF-003, P1)**: `resources/views/auth/login.blade.php:23` imprime el texto de forma incondicional, sin importar el entorno.
3. **Mensaje de validación obsoleto tras elegir fecha con el calendario (RF-004, P2)**: `resources/js/forms/fechas.js` usa Flatpickr con `altInput: true`; al quedar listo, intercambia los `id` entre el campo real oculto (`name="fecha_nacimiento"`) y el campo visible (`altInput`), pero el estado `is-invalid`/mensaje de error que puso `validar.js` sigue atado al campo oculto. Cuando el usuario elige una fecha, el evento `change` ocurre sobre el `altInput` (visible), que nunca tuvo la clase `is-invalid`, así que la revalidación delegada de `resources/js/forms/validar.js` (`e.target.classList.contains('is-invalid')`) nunca se dispara y el mensaje queda huérfano.
4. **Fecha de nacimiento obligatoria solo en el QR (RF-005, P2)**: es una decisión de diseño explícita en `resources/views/registro/formulario.blade.php:52` (`<x-form.date ... :required="true">`), mientras que el alta interna (`PersonaRequest`) la deja opcional. Se alinea con el trabajo ya en curso de la funcionalidad **017** (que agrega obligatoriedad a celular/documento en los mismos cuatro puntos de alta); este plan solo decide el criterio para fecha de nacimiento y coordina los archivos compartidos para no chocar con 017.
5. **GET accidental a una ruta solo-POST (RF-006, P2)**: se reprodujo una vez durante la sesión de QA (en `/login` y en `/registro/{token}/buscar`), pero la revisión de código no encontró en `resources/js/forms/registro-qr.js` ni en `envio.js` ninguna llamada que emita un `GET` hacia esas rutas — el `fetch` de precarga ya usa `method: 'POST'` y solo escucha cambios en `celular`/`numero_documento`, no en la fecha. Este plan trata el síntoma cierto (la página de depuración expuesta, ítem 1) como la corrección obligatoria, y agrega una tarea de **reproducción dirigida** (con las herramientas de red del navegador) como prerrequisito antes de tocar código de envío de formularios, para no arreglar a ciegas algo que hoy no se puede reproducir de forma determinista.
6. **Notificación emergente superpuesta con el encabezado (RF-007, P3)**: `resources/js/forms/avisos.js` usa SweetAlert2 con `position: 'top-end'` sin compensar la altura del `navbar` fijo del panel (`layouts.admin`).
7. **Mensaje de validación duplicado (RF-009, P3)**: es un efecto esperado — pero confuso — de `@foreach($errors->keys() ...)` en `resources/views/components/form/layout.blade.php:32-35`: `celular` y `email` fallan cada uno su propia regla `required_without` con el mismo texto humano, así que la lista de errores lo repite una vez por campo.
8. **Fila "Loading..." que no desaparece (RF-008, P3)**: el objeto de idioma `ESPAÑOL` en `resources/js/grilla/grilla.js` traduce `processing`, `emptyTable` y `zeroRecords`, pero **no** `loadingRecords`, que es el texto que DataTables muestra en el cuerpo de la tabla mientras espera la primera respuesta AJAX; por eso se ve en inglés y parece una fila que "se quedó pegada".
9. **Marca de agua de la sede (RF-010, P3)**: ajuste de opacidad/tamaño en el CSS de la marca de agua de sede activa.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; JavaScript ES modules (Vite)

**Dependencias Principales**: Blade, AdminLTE 4 / Bootstrap 5, DataTables 2, Flatpickr (ya en uso, `resources/js/forms/fechas.js`), SweetAlert2 (`resources/js/forms/swal.js`); sin librerías nuevas

**Almacenamiento**: Sin cambios de esquema. Ningún requisito de esta funcionalidad toca una migración ni una columna.

**Pruebas**: PHPUnit (vistas de error, mensajes de validación), Vitest (`fechas.js`/`validar.js` ya tienen suites — ver `resources/js/forms/*.test.js` — se amplían para cubrir la sincronización Flatpickr↔validación; `grilla.js` si tiene prueba unitaria del objeto de idioma), verificación manual en navegador real para RF-001/002/006 (no se puede fijar `APP_DEBUG` desde una prueba automática: es configuración de servidor) y Playwright (`npm run test:movil`) para confirmar que ningún ajuste visual (toast, marca de agua) rompe la vista móvil.

**Plataforma Objetivo**: Aplicación web responsive e instalable (PWA) en `https://crm.torreonfuerte.org.co/`; el servidor de producción es un hosting cPanel desplegado vía `.cpanel.yml`.

**Tipo de Proyecto**: Aplicación web monolítica Laravel (`laravel_app/`) + 1 cambio operativo fuera del repositorio (el `.env` real del servidor de producción).

**Objetivos de Rendimiento**: sin objetivos nuevos; los cambios son correctivos y no deben añadir latencia perceptible (el ajuste de `fechas.js`/`validar.js` ocurre en el navegador, sin red adicional).

**Restricciones**: ningún cambio puede revertir `APP_DEBUG=false` en ningún ambiente; ninguna página de error (pública o autenticada) puede volver a mostrar trazas, rutas de servidor ni encabezados de la solicitud; el criterio de obligatoriedad de `fecha_nacimiento` debe quedar documentado en un solo lugar para que la funcionalidad 017 (en curso, mismo archivo `registro/formulario.blade.php` y `PersonaRequest.php`) no lo contradiga.

**Escala/Alcance**: todas las sedes y todos los roles (el hallazgo de seguridad afecta a cualquier visitante anónimo, no solo a un rol); volumen de tráfico igual al actual, sin cambios de capacidad.

## Verificación de la Constitución

*PUERTA: debe pasar antes de la investigación de la Fase 0. Se revisa de nuevo tras el diseño de la Fase 1.*

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple. |
| II. Aislamiento por sede y alcance por rol (NO NEGOCIABLE) | Cumple sin cambios: ningún requisito toca consultas de datos ni alcance por sede/rol; el hallazgo de seguridad es de configuración de entorno (depuración), no de alcance de datos. |
| III. Grillas estándar | Cumple: el único ajuste a una grilla (RF-008) es el objeto de idioma compartido `ESPAÑOL` en `grilla.js`, usado por todas las grillas; no se crea ningún listado nuevo ni se cambia su contrato (orden, filtro, exportación, paginación). |
| IV. Pruebas primero | Cumple con una excepción declarada: RF-001/RF-002/RF-006 dependen de la configuración real del servidor (`APP_DEBUG`), que no se puede fijar ni revertir desde una prueba automática de este repositorio; se verifican con una prueba manual documentada en `quickstart.md` y, donde el código sí cambia (vista `errors/405.blade.php`), con una prueba de Feature que simula el entorno de producción (`App::detectEnvironment`/config en memoria) sin tocar el servidor real. El resto de requisitos (RF-004, RF-007, RF-008, RF-009, RF-010) sí llevan prueba automática antes del arreglo. |
| V. Mejora progresiva | Cumple: el arreglo de RF-004 debe seguir funcionando con el `<input type="date">` nativo si Flatpickr no carga (ya es el comportamiento documentado en `fechas.js`); no se cambia esa garantía. |
| VI. Simplicidad | Cumple: todos los arreglos reutilizan componentes existentes (`x-form.date`, `validar.js`, `avisos.js`, `grilla.js`, `errors._contenido`); RF-009 se resuelve con una `Rule` con un solo mensaje bajo una clave compartida en vez de duplicar lógica de validación. |
| VII. Diseño primero para celular | Cumple: el ajuste de la notificación emergente (RF-007) y de la marca de agua (RF-010) se revisan también en los anchos de `tests/movil/pantallas.mjs` para no introducir desbordamiento horizontal ni texto bajo 16 px. |

**Resultado de la puerta**: sin violaciones; la única excepción (prueba manual para los hallazgos de configuración de servidor) está justificada arriba y no requiere entrada en Seguimiento de Complejidad porque no es una violación de un principio, sino un límite real de lo que una prueba automatizada de este repositorio puede verificar (no tiene acceso al servidor de producción).

**Reevaluación tras el diseño (Fase 1)**: se mantiene sin violaciones. El diseño de Fase 1 (ver `data-model.md` y `contracts/`) no agrega entidades, tablas ni endpoints nuevos.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/018-auditoria-qa-crm/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── paginas-de-error.md
│   └── registro-qr-confirmacion.md
├── checklists/
│   └── requirements.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código Fuente (raíz del repositorio, `laravel_app/`)

```text
laravel_app/
├── (fuera del repo) .env del servidor de producción   # RF-001/002: corregir APP_DEBUG/APP_ENV y limpiar caché
├── .cpanel.yml                                         # + paso que verifica APP_DEBUG=false tras desplegar
├── resources/
│   ├── views/
│   │   ├── auth/login.blade.php                        # RF-003: quitar/condicionar el texto "Demo: ..."
│   │   ├── errors/405.blade.php                        # RF-001 (refuerzo): página amigable para método no permitido
│   │   └── components/form/layout.blade.php            # RF-009: resumen de errores no repite el mismo texto
│   └── js/
│       ├── forms/
│       │   ├── fechas.js                                # RF-004: sincronizar is-invalid entre altInput y campo real
│       │   ├── validar.js                                # RF-004: revalidar también sobre el altInput de Flatpickr
│       │   ├── avisos.js                                 # RF-007: posición del toast bajo el navbar fijo
│       │   └── registro-qr.js                             # RF-006: punto de partida de la reproducción dirigida
│       └── grilla/grilla.js                              # RF-008: agregar `loadingRecords` al objeto ESPAÑOL
├── app/Http/Requests/PersonaRequest.php                 # RF-005/RF-009: criterio de fecha_nacimiento + Rule de contacto
│                                                         #   (coordinar con la funcionalidad 017, mismo archivo)
├── resources/views/registro/formulario.blade.php        # RF-004/RF-005 (coordinar con la funcionalidad 017)
├── resources/css/ (o equivalente de AdminLTE)            # RF-007/RF-010: reglas de posición/opacidad
└── tests/
    ├── Feature/Errores/Error405Test.php                  # nuevo
    ├── Feature/Personas/ValidacionContactoTest.php        # nuevo (RF-009, un solo mensaje)
    ├── js/forms/fechas.test.js                            # ampliado (RF-004)
    ├── js/forms/validar.test.js                           # ampliado (RF-004)
    ├── js/grilla/grilla.test.js                           # nuevo o ampliado (RF-008)
    └── movil/pantallas.mjs                                # revisar capturas de login, crear-miembro y detalle de evento
```

**Decisión de Estructura**: monolito Laravel existente; no se agregan servicios, controladores ni tablas nuevas. El único trabajo fuera del repositorio es la corrección del `.env` real del servidor (operativa, documentada en `quickstart.md` para quien tenga acceso al hosting). Todo lo demás son ajustes acotados a archivos ya existentes.

## Seguimiento de Complejidad

Sin violaciones de la constitución que justificar.
