# Plan de implementación: Formularios modernos, organizados e interactivos

**Rama**: `003-formularios-modernos` | **Fecha**: 2026-10-19 | **Spec**: [spec.md](spec.md)

**Entrada**: Especificación de la funcionalidad en `/specs/003-formularios-modernos/spec.md`

## Resumen

Se renuevan **todos** los formularios de la aplicación Laravel existente (`laravel_app/`) en una sola entrega, **sin cambiar reglas de negocio, permisos ni datos**:

- **Componentes Blade reutilizables** (`<x-form.*>`): tarjetas por sección con ícono y descripción, campos con etiqueta encima, ícono a la izquierda, ayuda y error debajo, barra de acciones fija. Todos los formularios (y los futuros) se construyen con ellos.
- **Mejora progresiva con JavaScript** (un módulo propio pequeño + 4 librerías ya presentes o ligeras): validación al salir del campo con salto al primer error, selectores con búsqueda y selección múltiple como etiquetas, campos dependientes, máscaras (celular, documento, monto), calendario con atajos, botón con progreso sin doble envío, aviso de cambios sin guardar, diálogos de confirmación y avisos de éxito propios.
- **Todo sigue funcionando sin JavaScript**: los componentes generan controles nativos (`select`, `input type="date"`, casillas) y la validación del servidor sigue siendo la fuente de verdad.
- **Tablas de datos** (asistencia, abonos, integrantes): tarjetas con interruptor en teléfono y tabla en computador.
- **Accesibilidad**: etiquetas asociadas, `aria-describedby` / `aria-invalid`, resumen de errores anunciado, manejo completo con teclado.
- **Pruebas**: PHPUnit sobre el HTML que genera cada formulario (estructura, accesibilidad, ausencia de `confirm()` nativo) y pruebas unitarias de los módulos JS puros (formateo, normalización, validadores); las interacciones del navegador se validan con el recorrido de `quickstart.md`.

## Contexto técnico

**Lenguaje/Versión**: PHP ^8.3 (Blade) y JavaScript ES modules (Node 22 / npm 10 para compilar)

**Dependencias principales**: Laravel ^13.8, AdminLTE 3 / Bootstrap 4 / jQuery ya instalados; Vite 8. **Nuevas** (frontend): `select2` y `@ttskch/select2-bootstrap4-theme` (selectores con búsqueda), `flatpickr` (calendario), `sweetalert2` (diálogos y avisos), `inputmask` (máscaras). `select2`, `sweetalert2` e `inputmask` ya están en `node_modules` como dependencias de AdminLTE: se declaran de forma explícita en `package.json`. Desarrollo: `vitest` + `jsdom` para pruebas unitarias de JS.

**Almacenamiento**: N/A — no hay cambios de esquema ni de datos.

**Pruebas**: PHPUnit ^12.5 (hoy 157 pruebas) + Vitest para módulos JS puros; recorrido manual guiado en navegador (quickstart).

**Plataforma objetivo**: Aplicación web server-rendered (Blade) en hosting compartido cPanel **sin Node en el servidor**: los activos se compilan localmente (`npm run build`) y `public/build` se sube con el despliegue.

**Tipo de proyecto**: Aplicación web monolítica Laravel (sesión propia, filtro de sede ya implementado).

**Objetivos de rendimiento**: formularios interactivos al instante (< 100 ms al filtrar un selector de 1.000 opciones); aumento del peso del paquete JS/CSS ≤ 150 KB comprimido; sin degradar el tiempo de carga de las pantallas.

**Restricciones**: no cambiar nombres de campos ni rutas (los controladores y validaciones no se tocan, salvo mostrar errores); sin dependencias pesadas ni que requieran servidor Node; funcionar con JavaScript deshabilitado; marca de agua de la sede y modo "solo consulta" siguen funcionando; español.

**Escala/Alcance**: 36 vistas con formularios; ~18 pantallas de formulario completas, ~12 formularios incrustados (asistencia, pagos, miembros, reuniones, inscripciones, integrantes) y 5 filtros de listados; ~12 componentes Blade; 1 módulo JS con ~8 comportamientos.

## Verificación contra la constitución

`.specify/memory/constitution.md` sigue siendo la plantilla sin completar: **no hay principios que evaluar ni puertas que fallar**. Se aplican como principios de trabajo los de la spec: no cambiar el comportamiento de negocio, funcionar sin JavaScript, accesibilidad y simplicidad (módulo propio pequeño en lugar de un framework de formularios).

Re-evaluación tras el diseño: sin cambios; no se introduce complejidad que requiera justificación.

## Estructura del proyecto

### Documentación (esta funcionalidad)

```text
specs/003-formularios-modernos/
├── plan.md
├── research.md
├── data-model.md        # sin cambios de datos; modelo de los conceptos de interfaz
├── quickstart.md
├── contracts/
│   └── componentes-formulario.md   # API de componentes, atributos data-* y accesibilidad
└── tasks.md             # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio)

```text
laravel_app/
├── package.json                               # + select2, tema bootstrap4, flatpickr, sweetalert2, inputmask; dev: vitest, jsdom
├── vite.config.js                             # sin cambios de fondo (nuevas importaciones vía app.js/app.css)
├── resources/
│   ├── css/
│   │   ├── app.css                            # importa forms.css y los CSS de las librerías
│   │   └── forms.css                          # nuevo: tokens, tarjetas, campos, estados, barra fija, tarjetas móviles
│   ├── js/
│   │   ├── app.js                             # inicializa jQuery global, plugins y el módulo de formularios
│   │   └── forms/
│   │       ├── index.js                       # arranque: descubre [data-form] y activa comportamientos
│   │       ├── validar.js                     # validación al salir del campo, resumen, foco al primer error
│   │       ├── selectores.js                  # select2 (una opción / múltiple), búsqueda sin tildes, contador
│   │       ├── dependientes.js                # mostrar/ocultar/obligatorio según otros campos
│   │       ├── mascaras.js                    # celular, documento, monto (inputmask) sin alterar el valor enviado
│   │       ├── fechas.js                      # flatpickr es, atajos hoy/ayer, límites (max=hoy)
│   │       ├── envio.js                       # botón con progreso, bloqueo de doble envío, aviso de cambios sin guardar
│   │       ├── confirmar.js                   # diálogos [data-confirm] con sweetalert2
│   │       ├── avisos.js                      # avisos de éxito/errores que se cierran solos
│   │       └── texto.js                       # utilidades puras (normalizar sin tildes, formatear) — probadas con vitest
│   └── views/
│       ├── components/form/                   # componentes Blade anónimos
│       │   ├── layout.blade.php               # <x-form.layout>: <form>, errores, barra de acciones fija
│       │   ├── section.blade.php              # tarjeta con título, descripción e ícono
│       │   ├── input.blade.php                # texto, email, tel, number, password (ícono, ayuda, error, máscara)
│       │   ├── select.blade.php               # una opción, con búsqueda si hay > 10 opciones
│       │   ├── multiselect.blade.php          # selección múltiple con etiquetas y contador
│       │   ├── date.blade.php                 # fecha con calendario y atajos
│       │   ├── money.blade.php                # monto con separador de miles
│       │   ├── switch.blade.php               # casilla/interruptor
│       │   ├── textarea.blade.php
│       │   ├── actions.blade.php              # barra de acciones fija (guardar/cancelar)
│       │   ├── lista-asistencia.blade.php     # tabla en computador / tarjetas con interruptor en teléfono
│       │   └── confirm.blade.php              # <form> de acción delicada con data-confirm
│       ├── (36 vistas con formularios migradas a los componentes)
│       └── layouts/admin.blade.php            # contenedor de avisos y carga de activos
└── tests/
    ├── Feature/Formularios/
    │   ├── EstructuraFormulariosTest.php      # cada formulario usa los componentes (data-form), secciones, obligatorios, etiquetas asociadas
    │   ├── AccesibilidadFormulariosTest.php   # for/id, aria-describedby, resumen de errores, sin confirm() nativo
    │   ├── ErroresConservanDatosTest.php      # el servidor conserva lo escrito y marca campos con error
    │   └── SinJavascriptTest.php              # controles nativos presentes y envío funcional
    └── js/ (vitest)                           # texto.test.js, validar.test.js, mascaras.test.js
```

**Decisión de estructura**: la apariencia vive en **componentes Blade** (un solo lugar) y el comportamiento en **un módulo JS** que se activa por atributos `data-*` (mejora progresiva). Los controladores, rutas, validaciones y permisos **no cambian**; solo se migran las vistas.

## Seguimiento de complejidad

Sin violaciones de la constitución que justificar.
