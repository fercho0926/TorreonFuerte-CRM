# Plan de Implementación: Corregir errores del registro por QR y exigir celular y documento

**Rama**: `017-registro-qr-datos-obligatorios` | **Fecha**: 2026-11-10 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de funcionalidad en `/specs/017-registro-qr-datos-obligatorios/spec.md`

## Resumen

Celular y documento (tipo y número) pasan de opcionales a obligatorios en **todos** los formularios de alta de un miembro: los tres QR públicos (eventos/encuentros/células de la funcionalidad 009, miembros de la funcionalidad 012) y el panel interno (`PersonaRequest`, el alta rápida desde célula y el alta combinada de «Nuevo usuario»). Un miembro ya existente al que le falte alguno de esos datos queda forzado a completarlo la próxima vez que se le encuentre o se le edite — sin necesidad de una "puerta" aparte: al ser los campos siempre obligatorios en el formulario, y al permitir el servicio rellenar (nunca sobrescribir) un dato vacío, la obligatoriedad y el relleno de huecos son la misma mecánica.

La investigación de código (antes de escribir la especificación) encontró tres fallas concretas detrás de «a veces muestra error»:

1. `RegistroPorQr::registrar()` (eventos/encuentros/células) no tiene ningún bloqueo contra envíos simultáneos del mismo celular — a diferencia de `RegistroMiembrosQr::registrar()`, que sí usa `Cache::lock` desde la funcionalidad 012. Un doble toque o dos pestañas pueden chocar contra la restricción única `registros_qr_destino_celular_unique` (`QueryException` sin capturar → pantalla de error técnico) o crear dos fichas de `Persona` duplicadas.
2. El `Cache::lock(...)->block(10, …)` que ya protege el QR de miembros puede agotar su espera (`LockTimeoutException`) sin que nada la capture, lo que también termina en una pantalla de error técnico.
3. Cuando el celular o el documento ya pertenecen a alguien de otra sede, `RegistroPorQr::registrar()` **no crea ni inscribe** a la persona nueva que debería crear según la propia funcionalidad 009 (bug de implementación, no de diseño): solo queda una fila de bitácora con resultado `de_otra_sede` y la persona nunca se inscribe, aunque la pantalla de confirmación igual dice «¡Listo, quedaste registrado!».

Este plan corrige las tres fallas, generaliza a eventos/encuentros/células el mismo patrón de bloqueo e idempotencia que ya funciona en miembros, y añade la obligatoriedad de celular y documento en los cuatro puntos de alta (tres QR + panel interno), reutilizando al máximo lo ya construido en 009 y 012. Decisiones detalladas en [research.md](research.md).

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; JavaScript ES modules (Vite)

**Dependencias Principales**: Blade, AdminLTE 4 / Bootstrap 5, componentes `x-form.*`; `Illuminate\Support\Facades\Cache` (ya usado en la funcionalidad 012); sin librerías nuevas

**Almacenamiento**: Base de datos existente, **sin migraciones de esquema**: `personas.celular`, `personas.tipo_documento` y `personas.numero_documento` siguen siendo columnas nulas a nivel de base de datos (fichas antiguas incompletas no se tocan en bloque); la obligatoriedad se exige en la validación de los formularios (`FormRequest`), no en la columna. Las columnas `resultado` de `registros_qr` y `registros_miembros_qr` ya son `string` libres: solo se agregan valores nuevos (`ya_registrado`, `dato_completado`), sin cambiar su tipo ni su longitud.

**Pruebas**: PHPUnit (servicios, controladores públicos, panel interno, concurrencia, aislamiento), Vitest si cambia JavaScript de formulario, Playwright (`npm run test:movil`) para las pantallas que ganan campos obligatorios

**Plataforma Objetivo**: Web responsive e instalable (PWA); formularios públicos desde el navegador del celular, panel interno desde sesión

**Tipo de Proyecto**: Aplicación web monolítica Laravel (`laravel_app/`)

**Objetivos de Rendimiento**: mismos de 009/012 (contenido útil del formulario < 5 s en 4G lenta; envío < 2 s); el choque temporal (RF-007) se resuelve dentro del mismo `block(10)` ya usado en 012, sin agregar demora perceptible al caso normal

**Restricciones**: nunca sobrescribir un dato ya guardado (solo rellenar vacíos); nunca revelar ni modificar una ficha de otra sede; el reintento de un choque temporal cuenta contra los límites de frecuencia ya existentes (aclaración de la especificación); respuestas públicas con `Cache-Control: no-store, private` (sin cambios)

**Escala/Alcance**: mismo volumen que 009/012 (cientos de miembros por sede, picos tras el servicio dominical) más el panel interno (bajo volumen, uso por administrador/pastor/líder)

## Verificación de la Constitución

*PUERTA: debe pasar antes de la investigación de la Fase 0. Se revisa de nuevo tras el diseño de la Fase 1.*

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple. |
| II. Aislamiento por sede y alcance por rol (NO NEGOCIABLE) | Cumple sin cambios: toda la búsqueda y escritura sigue dentro de `conSede`/`sinFiltro` ya existentes. El arreglo del caso «de otra sede» en eventos (D5) **no** lee ni modifica la ficha ajena: solo hace que se cree, como ya manda la spec 009, un registro nuevo en la sede del QR. Pruebas de aislamiento ya existentes (`MatrizRutasTest`, `Aislamiento*Test`) deben seguir en verde. |
| III. Grillas estándar | No aplica: no se crea ningún listado principal nuevo; las listas pequeñas ya existentes (panel de eventos/encuentros/células, panel de miembros) solo ganan dos valores de resultado más. |
| IV. Pruebas primero | Cumple: pruebas de validación obligatoria, relleno de huecos, concurrencia (dos envíos simultáneos) y del bug de «otra sede» se escriben antes del código, en `tasks.md`. |
| V. Mejora progresiva | Cumple: la obligatoriedad se valida en el servidor (`FormRequest`) igual con o sin JavaScript; el JavaScript solo precarga los campos que ya existan. |
| VI. Simplicidad | Cumple con fuerza: no se crean servicios, tablas ni abstracciones nuevas. Se extiende el patrón «rellenar solo si está vacío» que `RegistroMiembrosQr::cambios()` ya aplica a `tipo_documento`/`numero_documento`, para que también cubra `celular`, y la misma extensión se replica en `RegistroPorQr` (hoy no toca ningún dato de una persona existente). Se reutiliza tal cual el `Cache::lock` de la funcionalidad 012 en vez de inventar otro mecanismo de concurrencia. |
| VII. Diseño primero para celular | Cumple: los campos que pasan a obligatorios (celular, tipo y número de documento) ya existen en los formularios públicos con sus controles ≥ 44 px / letra ≥ 16 px / teclado por tipo; el formulario interno de miembro los agrega siguiendo los mismos componentes `x-form.*`. Pantallas que ganan campos se agregan a `tests/movil/pantallas.mjs` si corresponde. |

**Resultado de la puerta**: sin violaciones; no se requiere Seguimiento de Complejidad.

**Reevaluación tras el diseño (Fase 1)**: se mantiene sin violaciones. El único riesgo de producto (fichas antiguas que se quedan incompletas hasta que alguien las toque) ya fue aceptado por el responsable en la cláusula de Supuestos de la especificación.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/017-registro-qr-datos-obligatorios/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── cambios-formularios-qr.md
│   └── cambios-panel-interno.md
├── checklists/
│   └── requirements.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código Fuente (raíz del repositorio, `laravel_app/`)

```text
laravel_app/
├── app/
│   ├── Models/
│   │   ├── RegistroQr.php                     # + resultado ya_registrado, dato_completado y sus etiquetas
│   │   └── RegistroMiembroQr.php               # + resultado dato_completado y su etiqueta
│   ├── Services/
│   │   ├── RegistroPorQr.php                   # + Cache::lock por destino+celular; ya_registrado en vez de
│   │   │                                       #   lanzar excepción; relleno de celular/documento vacíos en
│   │   │                                       #   persona EXISTENTE; arregla el caso «de otra sede» (D5)
│   │   └── RegistroMiembrosQr.php               # + celular entra a la lista de campos rellenables en vacío;
│   │                                            #   resultado dato_completado cuando el relleno fue el único
│   │                                            #   motivo del cambio
│   └── Http/
│       ├── Requests/
│       │   ├── RegistroQrRequest.php            # tipo/numero_documento pasan a 'required'
│       │   ├── RegistroMiembroQrRequest.php      # tipo/numero_documento pasan a 'required'
│       │   └── PersonaRequest.php                # celular y documento siempre obligatorios; ya no acepta
│       │                                         #   «celular o correo»; reglasDeAlta() gana tipo/numero_documento
│       └── Controllers/
│           ├── RegistroPublicoController.php     # captura LockTimeoutException → mensaje de reintento (RF-007)
│           ├── RegistroMiembrosPublicoController.php  # captura LockTimeoutException → mensaje de reintento
│           └── PersonasController.php            # valida igual en `rapida()` (alta rápida desde célula)
├── resources/
│   ├── views/
│   │   ├── registro/formulario.blade.php         # tipo/numero_documento y celular siempre obligatorios
│   │   ├── registro/confirmacion.blade.php        # + mensaje para resultado ya_registrado
│   │   ├── registro-miembros/formulario.blade.php # tipo/numero_documento y celular siempre obligatorios
│   │   ├── registro-miembros/confirmacion.blade.php # + mensaje para resultado dato_completado
│   │   ├── personas/_form.blade.php               # + campos tipo_documento / numero_documento (no existían)
│   │   └── celulas/_form.blade.php                # alta rápida de miembro: mismos campos obligatorios
│   └── js/forms/registro-qr.js                    # ajuste si la precarga condicionaba el atributo required
└── tests/
    ├── Feature/RegistroQr/{Concurrencia,OtraSedeTest,ObligatoriosTest}.php   # nuevos
    ├── Feature/RegistroMiembrosQr/{Concurrencia,ObligatoriosTest}.php         # nuevos
    ├── Feature/Personas/{PersonaRequestTest,RapidaTest}.php                  # ajustados
    └── movil/pantallas.mjs                                                   # + pantallas que ganan campos
```

**Decisión de Estructura**: monolito Laravel existente; no se agregan servicios, controladores ni tablas nuevas. Todo el trabajo es una extensión acotada de `RegistroPorQr`, `RegistroMiembrosQr`, los `FormRequest` de alta y las vistas correspondientes.

## Seguimiento de Complejidad

Sin violaciones de la constitución que justificar.
