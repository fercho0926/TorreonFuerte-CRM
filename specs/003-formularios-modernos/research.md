# Investigación (Fase 0): Formularios modernos

No quedaban marcadores `NEEDS CLARIFICATION`. Las decisiones siguientes parten de la spec (con sus 5 clarificaciones) y de la revisión del frontend actual: Blade + AdminLTE 3 / Bootstrap 4 / jQuery, Vite 8, Tailwind 4 importado en `app.css`, y 36 vistas con formularios (18 pantallas de formulario, ~12 formularios incrustados y 5 filtros).

## D1. Componentes Blade en lugar de HTML repetido

- **Decisión**: componentes Blade anónimos (`<x-form.layout>`, `section`, `input`, `select`, `multiselect`, `date`, `money`, `switch`, `textarea`, `actions`, `lista-asistencia`, `confirm`) que encapsulan el HTML, los atributos `data-*` y la accesibilidad. Cada formulario se escribe declarando sus secciones y campos.
- **Justificación**: hoy cada formulario repite ~15 líneas por campo (etiqueta, input, `@error`); con componentes el estilo y los comportamientos se aplican en un solo lugar y los formularios nuevos los heredan (FR-018, SC-006).
- **Alternativas**: parciales con `@include` (rechazado: sin props ni slots); Livewire/Alpine (rechazado: añade un framework y estado en servidor que no se necesita).

## D2. Mejora progresiva: HTML nativo primero, JavaScript encima

- **Decisión**: los componentes emiten controles nativos (`<select>`, `<input type="date">`, `<input type="tel">`, casillas) con sus atributos de validación HTML5; un módulo JS los mejora al detectar `data-form`. Sin JavaScript el formulario se completa y se valida en el servidor.
- **Justificación**: FR-020 y la validación del servidor como fuente de verdad (FR-021).
- **Alternativas**: renderizar controles personalizados en el cliente (rechazado: se rompe sin JS y es peor para accesibilidad).

## D3. Selectores con búsqueda: Select2 con el tema de Bootstrap 4

- **Decisión**: `select2` (ya presente por AdminLTE) con `@ttskch/select2-bootstrap4-theme` e idioma español; una opción con búsqueda cuando hay más de 10 opciones (`minimumResultsForSearch`), múltiple con etiquetas removibles. Búsqueda **sin distinguir tildes ni mayúsculas** con un `matcher` propio que normaliza con `String.prototype.normalize('NFD')`. El dato de apoyo (documento/celular) se muestra con una plantilla de resultado a partir de atributos `data-apoyo` de cada `<option>`.
- **Contador y acciones masivas**: pequeño código propio sobre los eventos de Select2 (`select2:select`, `select2:unselect`) para el contador y "Seleccionar resultados / Limpiar" (FR-013).
- **Rendimiento**: filtra en el cliente sobre las opciones ya renderizadas (sin nuevos endpoints). Con 1.000 personas por sede el HTML añade ~100 KB; se mide en la prueba de escala. Si una sede supera ~3.000 personas se evaluará búsqueda por servidor (no necesario hoy).
- **Alternativas**: Tom Select / Choices.js (rechazadas: AdminLTE y el resto del panel ya usan jQuery + Bootstrap 4 y Select2 encaja sin estilos nuevos); `<datalist>` (rechazado: sin etiquetas, sin dato de apoyo y accesibilidad dispar).

## D4. Fechas: Flatpickr

- **Decisión**: `flatpickr` con localización `es`, formato visible `d/m/Y` y valor enviado `Y-m-d` (el mismo que hoy), atajos "Hoy" y "Ayer" mediante botones propios y `maxDate`/`minDate` leídos de `data-max`/`data-min` (nacimiento ≤ hoy). En móvil usa el selector nativo (`disableMobile: false` para teclados cómodos) — el campo base sigue siendo `type="date"` en el HTML sin JS.
- **Justificación**: pequeño (~20 KB), sin dependencia de moment ni jQuery, buena accesibilidad.
- **Alternativas**: Tempus Dominus (rechazado: requiere moment y es más pesado); solo `type="date"` (rechazado: sin atajos ni formato uniforme).

## D5. Máscaras: Inputmask solo para presentación

- **Decisión**: `inputmask` para celular (grupos de dígitos), documento (solo dígitos y letras), y monto (`numeric` con separador de miles y símbolo). Se usa `autoUnmask: true` y `removeMaskOnSubmit: true` para que el **valor enviado no cambie** (FR-015, FR-021): el servidor recibe los mismos dígitos y números de hoy.
- **Teclados en teléfono**: `type="tel"`/`inputmode="numeric"`/`inputmode="decimal"` y `autocomplete` adecuados, también sin JS.
- **Alternativas**: máscaras propias (rechazado: casos límite de pegado y cursor); Cleave/IMask (rechazadas: `inputmask` ya está instalado por AdminLTE).

## D6. Validación en el cliente: API de validación del navegador + módulo propio

- **Decisión**: se usa la **Constraint Validation API** (atributos `required`, `type=email`, `pattern`, `minlength`, `max`) con mensajes en español, activada al salir del campo (`blur`) y al enviar; el módulo `validar.js`: muestra el mensaje debajo del campo (`.invalid-feedback`), marca `is-valid`/`is-invalid`, enlaza el mensaje con `aria-describedby`, arma un **resumen de errores** (`role="alert"`) al intentar enviar y enfoca el primer campo con error. Reglas dependientes (documento obligatorio al ser miembro) se expresan con `data-requerido-si`.
- **Errores del servidor**: al recargar con errores de Laravel, el mismo módulo marca los campos, muestra el resumen y enfoca el primero (FR-008); los valores se conservan con `old()` como hoy.
- **Justificación**: sin librería de validación; el servidor sigue decidiendo (FR-021) y el cliente solo adelanta los avisos.
- **Alternativas**: jQuery Validate / Parsley (rechazadas: duplican reglas y mensajes ya definidos en el servidor).

## D7. Campos dependientes por atributos declarativos

- **Decisión**: atributos `data-visible-si="campo:valor"` y `data-requerido-si="campo:valor"` interpretados por `dependientes.js`, con transición CSS. Los campos ocultos se **deshabilitan** para no enviarse (equivale a hoy, donde la sección se oculta pero se envía vacía; el servidor ya normaliza `red_id`/`zona_id` cuando no es líder).
- **Casos cubiertos**: red y zona al marcar "es líder"; documento obligatorio con estado miembro activo o líder; costo individual del evento solo si se activa; campos de cuenta nueva al cambiar pastor.
- **Alternativas**: Alpine.js `x-show` (rechazado: nueva dependencia).

## D8. Envío, aviso de cambios sin guardar y diálogos

- **Decisión**:
  - `envio.js`: al enviar un formulario válido, el botón principal muestra un indicador de progreso y se deshabilita (también cubre `Enter`); se restaura si el servidor responde con error de validación (la página recarga) o si el envío se cancela.
  - **Cambios sin guardar**: se compara una huella del formulario al cargar y al salir (`beforeunload`); no se avisa si no hay cambios ni al enviar. **No se guardan borradores** (clarificación Q4).
  - `confirmar.js` + `sweetalert2` para los 16 `onclick="return confirm(...)"` actuales: se reemplazan por `<x-form.confirm>` o `data-confirm="..."` con el texto de las consecuencias; sin JS cae al envío directo (el servidor ya exige permisos y reglas).
  - `avisos.js`: los `alert-success` pasan a avisos tipo "toast" que se cierran solos (5 s), con `role="status"`; los errores permanecen visibles.
- **Alternativas**: `toastr` (rechazado: se prefiere un único paquete para diálogos y avisos); `<dialog>` nativo (rechazado: estilos y animaciones propias a mantener).

## D9. Estilo visual: tarjetas, íconos y tokens de diseño (clarificación Q1)

- **Decisión**: `forms.css` define variables (colores del tema actual del panel, radio de borde, sombras suaves, espacios) y las clases: tarjeta de sección (`.fm-seccion`), campo con ícono a la izquierda (grupo de entrada de Bootstrap), etiqueta encima, ayuda `.fm-ayuda`, error debajo, estados válido/inválido con transición, barra de acciones fija `.fm-acciones` (pegada abajo, con fondo y sombra, respetando el menú lateral y la marca de agua de la sede). Los íconos son Font Awesome (ya instalado).
- **Sin etiqueta flotante ni línea inferior** (descartadas por el usuario).
- **Marca de agua**: `pointer-events: none` y `z-index` inferior; se verifica que no tape campos ni el botón fijo.
- **Alternativas**: reescribir con Tailwind puro (rechazado: el panel es AdminLTE/Bootstrap y mezclar clases generaría dos sistemas de estilo).

## D10. Formularios con tablas de datos (clarificación Q3)

- **Decisión**: componente `lista-asistencia` que genera **la misma información en dos presentaciones**: `<table>` desde `md` y tarjetas con interruptor/botón grande por persona hasta `sm` (CSS `d-none d-md-block` / `d-md-none`, sin duplicar datos en el servidor: se renderiza una sola estructura de datos con dos vistas). Cubre asistencia de reuniones de célula y de encuentros, integrantes de ministerios, inscripciones y abonos de eventos.
- **Alternativas**: tabla con desplazamiento horizontal (rechazada por el usuario).

## D11. Accesibilidad

- **Decisión**: cada campo tiene `<label for>`, `aria-describedby` hacia ayuda y error, `aria-invalid` según el estado, `aria-required`; el resumen de errores usa `role="alert"` y lleva enlaces a cada campo; Select2 con su soporte de teclado; los diálogos de SweetAlert2 atrapan el foco y se cierran con `Esc`; contraste de textos de ayuda y errores ≥ 4,5:1.
- **Verificación**: PHPUnit comprueba `for`/`id` y `aria-*` en el HTML generado; el recorrido manual del quickstart incluye uso solo con teclado y un lector de pantalla (NVDA o VoiceOver) en la pantalla de persona.

## D12. Pruebas

- **Decisión**:
  - **PHPUnit (HTML del servidor)**: por cada formulario del inventario, la página responde 200 y contiene el marcador `data-form`, secciones con título, campos obligatorios marcados con `aria-required`, etiquetas asociadas y ningún `onclick="return confirm"`; el envío con errores conserva los valores y marca los campos (`ErroresConservanDatosTest`); sin JS el formulario envía (`SinJavascriptTest` = prueba de formularios nativos).
  - **Vitest**: funciones puras (`texto.js` normalizar/formatear, reglas de `validar.js`, conversión de máscaras).
  - **Las 157 pruebas existentes** de negocio y permisos deben seguir pasando sin cambios de comportamiento (SC-009).
  - **Manual guiado** (quickstart): comportamientos de navegador (calendario, selectores, diálogos, teléfono de 360 px, teclado).
- **Alternativas**: pruebas E2E con Playwright/Cypress (rechazadas por ahora: infraestructura pesada para un equipo pequeño; el recorrido manual con el navegador cubre la interacción).

## D13. Despliegue de activos

- **Decisión**: los activos se compilan localmente (`npm install && npm run build`) y `public/build` se incluye en el paquete de despliegue (cPanel sin Node), como ya se hace; `DEPLOY_CPanel.md` se actualiza con el paso de compilación y la advertencia de excluir `node_modules`.
- **Riesgo**: olvidar compilar deja el panel con el CSS/JS anterior (la mejora progresiva evita que se rompa, pero se vería sin estilo nuevo): se añade una verificación en el quickstart.

## D14. Migración de las vistas

- **Decisión**: migrar **todas** las vistas del inventario (spec FR-017) en la misma entrega, empezando por los componentes y los comportamientos base y migrando luego formulario por formulario, con una prueba de inventario que falla si aparece una vista con `<form method="POST">` sin el marcador de componentes.
- **Orden interno recomendado** (solo para trabajar): componentes y JS → Personas (sirve de referencia) → resto. La entrega es única (clarificación Q5).
