# Plan de Implementación: Autorregistro y actualización de miembros por código QR

**Rama**: `012-registro-miembros-qr` | **Fecha**: 2026-10-23 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de funcionalidad en `/specs/012-registro-miembros-qr/spec.md`

## Resumen

Cada sede tiene un QR permanente de «Registro de miembros». Quien lo escanea abre un formulario público (sin sesión) que busca al miembro por celular o cédula **dentro de la sede del QR**: si no existe lo crea (como «Visitante» o «Miembro activo», a elección de la persona); si existe, precarga todos sus datos y, al enviar, actualiza al instante solo lo que cambió; si estaba inactivo o eliminado, lo reactiva. Toda creación, actualización y reactivación queda en el historial del miembro (`persona_cambios`) con origen «QR de miembros» y en una lista de registros por QR para el administrador y el pastor.

El enfoque reutiliza lo construido en la funcionalidad 009: la generación del QR (`GeneradorQr`), el patrón de token + apertura/cierre + regeneración, el flujo público de dos pasos con precarga opcional por JavaScript, la búsqueda por celular/cédula dentro de la sede (`RegistroPorQr`), los componentes `x-form.*` y los límites con `RateLimiter`. Lo nuevo es: columnas de QR en `sedes`, una tabla `registros_miembros_qr`, un servicio de actualización, dos controladores (público y de gestión) y los paneles del administrador (pestaña en el detalle de sede) y del pastor (pantalla propia). Decisiones en [research.md](research.md).

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; JavaScript ES modules (Vite 8)

**Dependencias Principales**: Blade, AdminLTE 4 / Bootstrap 5, `bacon/bacon-qr-code` (ya instalado, vía `GeneradorQr`), componentes `x-form.*`; sin librerías nuevas

**Almacenamiento**: Base de datos existente: 1 migración (columnas en `sedes`, tabla nueva `registros_miembros_qr`) y reutiliza `personas` y `persona_cambios`

**Pruebas**: PHPUnit (servicio, controladores, aislamiento, límites), Vitest (precarga ampliada del formulario), Playwright `npm run test:movil` (formulario público y paneles)

**Plataforma Objetivo**: Web responsive e instalable (PWA); el formulario público se abre desde el navegador del celular

**Tipo de Proyecto**: Aplicación web monolítica Laravel (`laravel_app/`)

**Objetivos de Rendimiento**: Contenido útil del formulario < 5 s en 4G lenta (CE-007); envío < 2 s

**Restricciones**: Sin sesión en el formulario; todo acceso a miembros dentro de `conSede(sede del QR)`; nunca vaciar datos; 5 envíos/consultas por minuto por conexión; respuestas con `Cache-Control: no-store`; sin datos de personas en el service worker

**Escala/Alcance**: Una sede = un QR; cientos de miembros por sede; picos de registro tras un servicio dominical (ver riesgo del límite en research D5)

## Verificación de la Constitución

*PUERTA: debe pasar antes de la investigación de la Fase 0. Se revisa de nuevo tras el diseño de la Fase 1.*

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple. |
| II. Aislamiento por sede y alcance por rol (NO NEGOCIABLE) | Cumple con atención especial: el formulario es público, así que el token resuelve la sede y todo corre en `conSede`; un miembro solo de otra sede nunca se lee ni modifica. La gestión valida que el pastor solo toque su sede (gate nuevo `gestionarRegistroMiembros`); el administrador accede a cualquier sede. Pruebas de aislamiento entre sedes y roles; las rutas públicas se registran en la lista de ignoradas de `MatrizRutasTest` (como `registro.*`) con su propia prueba de aislamiento. |
| III. Grillas estándar | La lista de registros por QR es una tabla pequeña dentro de un panel de gestión (como «Registrados por QR» de 009), no un listado principal; se reutiliza el parcial de tabla apilable. Si el responsable la considera listado principal, pasaría a `x-ui.grilla` (se anota en riesgos). |
| IV. Pruebas primero | Cumple: pruebas definidas antes del código en `tasks.md`. |
| V. Mejora progresiva | Cumple: paso 1 (buscar) y paso 2 (datos) funcionan en el servidor sin JavaScript; el JS solo precarga al salir del campo. Errores JSON del endpoint de búsqueda (los maneja el controlador, como en 009). |
| VI. Simplicidad | Cumple: se extiende `RegistroPorQr` (búsqueda por sede) en vez de duplicarlo; se reutilizan `GeneradorQr`, `Celular`, `LiderazgoService::reactivar` y los componentes de formulario. Una tabla nueva y dos columnas; sin abstracciones nuevas. |
| VII. Diseño primero para celular | Cumple: formulario público con controles ≥ 44 px, letra ≥ 16 px, teclado por tipo, sin desplazamiento horizontal; el service worker no guarda las rutas; pantallas nuevas agregadas a `tests/movil/pantallas.mjs`. |

**Resultado de la puerta**: sin violaciones; no se requiere Seguimiento de Complejidad.

**Reevaluación tras el diseño (Fase 1)**: se mantiene sin violaciones. Riesgos de producto aceptados por el responsable (actualización inmediata, precarga completa de datos, límite estricto) se documentan en research D4–D6 y en los supuestos de la especificación.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/012-registro-miembros-qr/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── formulario-publico.md
│   └── gestion-qr-miembros.md
├── checklists/
│   └── requirements.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código Fuente (raíz del repositorio, `laravel_app/`)

```text
laravel_app/
├── database/migrations/
│   └── 2026_10_27_000001_add_registro_miembros_a_sedes.php   # columnas en sedes + tabla registros_miembros_qr
├── app/
│   ├── Models/
│   │   ├── Sede.php                           # + registro_miembros_token/abierto, urlRegistroMiembros(), regenerar
│   │   ├── RegistroMiembroQr.php              # nuevo
│   │   └── PersonaCambio.php                  # + acciones creada_qr, actualizada_qr, reactivada_qr
│   ├── Services/
│   │   ├── RegistroPorQr.php                  # extraer buscarEnSede(int $sedeId, …) reutilizable
│   │   └── RegistroMiembrosQr.php             # nuevo: crear / actualizar / reactivar + historial
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── RegistroMiembrosPublicoController.php   # nuevo: show, buscar, store, listo
│   │   │   ├── RegistroMiembrosQrController.php        # nuevo: pantalla del pastor, svg, abrir, regenerar
│   │   │   └── SedesController.php                     # + pestaña «Registro de miembros»
│   │   └── Requests/RegistroMiembroQrRequest.php       # nuevo
│   └── Providers/AppServiceProvider.php       # + gates gestionarRegistroMiembros / verRegistroMiembros
├── config/menu.php                            # + «Registro de miembros» (solo pastor)
├── routes/web.php                             # + rutas públicas y de gestión
├── resources/
│   ├── views/
│   │   ├── registro-miembros/{formulario,cerrado,confirmacion,index}.blade.php
│   │   ├── partials/panel-registro-miembros.blade.php   # QR + enlace + abrir/cerrar/regenerar + lista (compartido admin/pastor)
│   │   └── sedes/show.blade.php               # + pestaña
│   └── js/forms/registro-qr.js                # precarga ampliada (todos los campos permitidos)
└── tests/
    ├── Feature/RegistroMiembrosQr/{Servicio,Publico,Gestion,Aislamiento,Limites}Test.php
    ├── Feature/Aislamiento/MatrizRutasTest.php # + rutas públicas en IGNORADAS
    └── movil/{pantallas.mjs,registro-miembros.spec.js}
```

**Decisión de Estructura**: monolito Laravel existente. La lógica vive en `RegistroMiembrosQr` (probable sin HTTP); `RegistroPorQr` solo se refactoriza para exponer la búsqueda por sede y no se cambia su comportamiento (las pruebas de 009 deben seguir en verde).

## Seguimiento de Complejidad

Sin violaciones de la constitución que justificar.
