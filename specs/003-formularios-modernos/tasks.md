---

description: "Lista de tareas para la renovación de los formularios (modernos, organizados e interactivos)"
---

# Tareas: Formularios modernos, organizados e interactivos

**Entrada**: Documentos de diseño en `/specs/003-formularios-modernos/`

**Prerrequisitos**: plan.md, spec.md, research.md, data-model.md, contracts/componentes-formulario.md, quickstart.md

**Pruebas**: Se incluyen pruebas porque el plan las exige: PHPUnit sobre el HTML que genera cada formulario, Vitest para las funciones JavaScript puras y un recorrido manual guiado (quickstart) para lo que solo se ve en el navegador.

**Organización**: Las tareas se agrupan por historia de usuario. Las historias P1 (US1, US2, US3) son el mínimo; US4 y US5 son P2; US6 es P3. **La entrega es única** (clarificación Q5): todas las historias y todos los formularios se publican juntos, aunque el trabajo se ordene por historias.

> ⚠️ **Reglas de seguridad de este trabajo**: no cambiar `name`, `action`, `method`, rutas, validaciones, permisos ni controladores (FR-021); no ejecutar `migrate:fresh` contra la base del `.env`; no guardar borradores ni datos de personas en el navegador (FR-010). Las 157 pruebas actuales deben seguir pasando en cada punto de control.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: Se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[Historia]**: Historia de usuario (US1…US6)
- Todas las rutas son relativas a la raíz del repositorio (`C:\WorkSpace\TorreonFuerte-CRM`); el código vive en `laravel_app/`

## Convenciones de ruta

- Componentes Blade: `laravel_app/resources/views/components/form/` · Vistas: `laravel_app/resources/views/`
- CSS: `laravel_app/resources/css/` · JavaScript: `laravel_app/resources/js/forms/`
- Pruebas PHP: `laravel_app/tests/Feature/Formularios/` · Pruebas JS: junto al módulo (`*.test.js`)

---

## Fase 1: Preparación (infraestructura compartida)

**Propósito**: Dependencias, herramientas de prueba y línea base.

- [ ] T001 Ejecutar `php artisan test` y `npm run build` en `laravel_app/` y confirmar la línea base: 157 pruebas en verde y compilación correcta (anotar el tamaño de `laravel_app/public/build/assets/app-*.js` y `app-*.css` para comparar al final)
- [ ] T002 Declarar en `laravel_app/package.json` las dependencias `select2`, `@ttskch/select2-bootstrap4-theme`, `flatpickr`, `sweetalert2` e `inputmask` (las tres que ya están en `node_modules` por AdminLTE con su versión instalada) y, en desarrollo, `vitest` y `jsdom`; añadir el script `"test": "vitest run"`; ejecutar `npm install` y confirmar que `npm run build` sigue funcionando
- [ ] T003 [P] Crear `laravel_app/vitest.config.js` (entorno `jsdom`, incluir `resources/js/**/*.test.js`)
- [ ] T004 [P] Crear `laravel_app/tests/Support/RecorreFormularios.php`: trait que, con los traits `IniciaSesion` y `ConSedes`, arma una sede con datos (persona, líder, célula, evento con inscripción, encuentro, ministerio, usuario) y expone la lista de rutas GET de formularios del inventario de `data-model.md` con sus parámetros, para reutilizarla en todas las pruebas de formularios

---

## Fase 2: Fundamentos (prerrequisitos bloqueantes)

**Propósito**: Estilos base, componentes básicos y arranque del módulo JS que necesitan todas las historias.

**⚠️ CRÍTICO**: Ninguna historia puede empezar hasta terminar esta fase.

- [ ] T005 Crear `laravel_app/resources/css/forms.css` con los tokens (colores del tema actual del panel, radio de borde, sombras suaves, espacios) y las clases `.fm-seccion` (tarjeta de sección), `.fm-campo` (etiqueta encima, ícono a la izquierda con grupo de entrada, ayuda `.fm-ayuda`, error debajo), estados `.is-valid`/`.is-invalid` con transición, `.fm-acciones` (barra fija abajo que respeta el menú lateral y no se superpone a la marca de agua de la sede), leyenda de obligatorio y diseño de una columna en teléfono; importarlo en `laravel_app/resources/css/app.css` junto con los CSS de `select2`, el tema `@ttskch/select2-bootstrap4-theme`, `flatpickr` y `sweetalert2`
- [ ] T006 Actualizar `laravel_app/resources/js/app.js`: exponer jQuery como `window.$` y `window.jQuery`, registrar el plugin `select2` y su idioma `es`, importar `flatpickr` (localización `es`), `sweetalert2` e `inputmask`, e importar `./forms/index.js`
- [ ] T007 [P] Crear `laravel_app/resources/js/forms/texto.js` con funciones puras exportadas: `normalizar(texto)` (minúsculas y sin tildes con `normalize('NFD')`), `soloDigitos(texto)`, `formatearMiles(numero)`, `formatearCelular(digitos)` y `hoy()`/`ayer()` en formato `Y-m-d`
- [ ] T008 [P] Prueba `laravel_app/resources/js/forms/texto.test.js` (Vitest): normalizar "Ángela Pérez" → "angela perez", limpiar celulares con espacios y símbolos, formateo de miles y celular, `hoy`/`ayer`
- [ ] T009 Crear `laravel_app/resources/js/forms/index.js`: al cargar el documento descubre cada `form[data-form]` y llama a los módulos de comportamiento (se registran en las fases siguientes; cada uno es opcional y no debe fallar si falta), y expone los eventos `form:invalido`, `form:enviando` y `form:cambios` del contrato
- [ ] T010 Crear los componentes base `laravel_app/resources/views/components/form/layout.blade.php` (`<form>` con `@csrf`, `@method`, `data-form`, contenedor de secciones y barra de acciones; props `action`, `method`, `guardar`, `cancelar`, `modo`, `sinCambios`; con el modo "Todas las sedes: solo consulta" muestra el aviso y deshabilita el botón principal usando `@todasLasSedes`), `section.blade.php` (tarjeta con `titulo`, `descripcion`, `icono`, `id`, `visibleSi`) y `actions.blade.php` (barra fija con botón principal y enlace de cancelar)
- [ ] T011 [P] Crear `laravel_app/resources/views/components/form/input.blade.php` (tipos `text`, `email`, `tel`, `number`, `password`; `<label for>`, ícono, `ayuda`, `ejemplo`, `required` con `aria-required`, `@error` con `aria-invalid` y `aria-describedby`, `inputmode` y `autocomplete` adecuados, valor por defecto `old(name, ...)`)
- [ ] T012 [P] Crear `laravel_app/resources/views/components/form/textarea.blade.php` y `laravel_app/resources/views/components/form/switch.blade.php` (casilla con valor `1`, aspecto de interruptor, mismo contrato de etiqueta, ayuda y error)
- [ ] T013 [P] Crear `laravel_app/resources/views/components/form/select.blade.php` (control nativo `<select>`, prop `:opciones` con `id`, `texto` y `apoyo` opcional impreso como `data-apoyo`, opción vacía opcional, mismo contrato de etiqueta, ayuda y error)
- [ ] T014 [P] Crear `laravel_app/resources/views/components/form/date.blade.php` (`<input type="date">` nativo con `max`/`min` y `data-fecha`) y `laravel_app/resources/views/components/form/money.blade.php` (`<input inputmode="decimal">` con `data-monto`), ambos con el mismo contrato de etiqueta, ayuda y error
- [ ] T015 Prueba `laravel_app/tests/Feature/Formularios/ComponentesBaseTest.php`: renderiza cada componente base con `Blade::render` y comprueba `<label for>` con el `id` del control, `aria-required` cuando es obligatorio, `aria-invalid` y `aria-describedby` con error, marca `data-form` en el layout, `@csrf` y `@method`, y el valor previo con `old()`

**Punto de control**: Estilos, componentes base y arranque JS listos; `php artisan test` y `npm test` siguen en verde.

---

## Fase 3: Historia de usuario 1 - Formularios organizados en secciones claras (Prioridad: P1) 🎯 MVP

**Objetivo**: Cada formulario de la aplicación se presenta en tarjetas por sección con título, descripción e ícono, campos obligatorios marcados, barra de acciones fija y diseño de una columna en teléfono.

**Prueba independiente**: Mostrar el formulario de persona a 3 usuarios y pedirles registrar una persona; encuentran cada campo sin ayuda y saben cuáles son obligatorios.

### Pruebas de la historia 1

- [ ] T016 [P] [US1] Prueba `laravel_app/tests/Feature/Formularios/EstructuraFormulariosTest.php`: para cada pantalla de formulario del inventario (persona crear/editar, célula, encuentro, evento, ministerio, usuario, sede, pastores, traslado, designar líder, quitar rol) la página responde 200 y contiene `data-form`, al menos una sección `.fm-seccion` con título, la leyenda de campos obligatorios, la barra `.fm-acciones` y que los `name` de los campos coinciden con los de antes (lista fija por formulario)
- [ ] T017 [P] [US1] Prueba en `laravel_app/tests/Feature/Formularios/EstructuraFormulariosTest.php`: el formulario de persona tiene las secciones "Datos personales", "Contacto", "Documento y estado", "Ubicación" y "Liderazgo", con sus campos en la sección correcta

### Implementación de la historia 1

- [ ] T018 [US1] Migrar el formulario de persona a componentes en `laravel_app/resources/views/personas/_form.blade.php` (secciones Datos personales, Contacto, Documento y estado, Ubicación y Liderazgo con íconos y descripciones; conservar `@include('partials.ayuda-terminos')`, el aviso de duplicado y todos los `name`) y envolverlo con `<x-form.layout>` en `laravel_app/resources/views/personas/create.blade.php` y `laravel_app/resources/views/personas/edit.blade.php`
- [ ] T019 [P] [US1] Migrar `laravel_app/resources/views/celulas/_form.blade.php`, `create.blade.php` y `edit.blade.php` (secciones "Datos del grupo", "Liderazgo" y "Reunión"; nombres de campo `nombre`, `sede`, `estado`, `lider_id`, `co_lider_id`, `dia_reunion`, `hora_reunion`, `lugar_reunion` sin cambios)
- [ ] T020 [P] [US1] Migrar `laravel_app/resources/views/encuentros/_form.blade.php`, `create.blade.php` y `edit.blade.php` (secciones "Datos del encuentro" y "Participantes") conservando los `name`
- [ ] T021 [P] [US1] Migrar `laravel_app/resources/views/eventos/_form.blade.php`, `create.blade.php` y `edit.blade.php` (secciones "Datos del evento", "Costo" y "Inscritos") conservando los `name`
- [ ] T022 [P] [US1] Migrar `laravel_app/resources/views/ministerios/_form.blade.php`, `create.blade.php` y `edit.blade.php` (secciones "Datos del ministerio" e "Integrantes") conservando los `name`
- [ ] T023 [P] [US1] Migrar `laravel_app/resources/views/users/create.blade.php` y `laravel_app/resources/views/users/edit.blade.php` (secciones "Cuenta", "Acceso" y "Vínculo con persona"; conservar la selección de sede del admin y la lista de roles asignables)
- [ ] T024 [P] [US1] Migrar `laravel_app/resources/views/sedes/_datos.blade.php`, `_cuenta.blade.php`, `create.blade.php`, `edit.blade.php` y `pastores.blade.php` (secciones "Datos de la iglesia", "Pastor", "Pastora (opcional)") conservando los `name` anidados `pastor[...]` y `pastora[...]`
- [ ] T025 [P] [US1] Migrar `laravel_app/resources/views/traslados/create.blade.php`, `laravel_app/resources/views/lideres/quitar.blade.php` y `laravel_app/resources/views/lideres/designar.blade.php` (formulario de sede destino, de reasignación con el conteo de personas y células, y de búsqueda y designación) conservando los `name`
- [ ] T026 [P] [US1] Migrar los formularios incrustados de `laravel_app/resources/views/celulas/show.blade.php` (agregar miembro y registrar reunión), `laravel_app/resources/views/eventos/show.blade.php` (inscribir persona y registrar abono), `laravel_app/resources/views/ministerios/show.blade.php` (actualizar integrantes) y `laravel_app/resources/views/encuentros/show.blade.php` (registrar asistencia) al modo `incrustado` de `<x-form.layout>` con secciones breves, conservando los `name`
- [ ] T027 [P] [US1] Migrar los formularios incrustados de `laravel_app/resources/views/redes/index.blade.php` y `laravel_app/resources/views/zonas/index.blade.php` (crear, editar en línea y fusionar) y `laravel_app/resources/views/sin-lider/index.blade.php` (asignar líder por persona) al modo `incrustado`, conservando los `name`
- [ ] T028 [US1] Ajustar `laravel_app/resources/css/forms.css` tras ver los formularios migrados: separación entre secciones, ancho máximo cómodo en computador, una sola columna en teléfono de 360 px sin desplazamiento horizontal, controles de al menos 44 px de alto al tacto, y verificar que la marca de agua de la sede (`.marca-agua-sede`) y la barra fija no se tapan entre sí ni tapan campos
- [ ] T029 [US1] Ejecutar `php artisan test` en `laravel_app/` y corregir las pruebas de negocio de `laravel_app/tests/Feature/` que dependan de textos o estructura HTML de los formularios (los `name` y rutas no cambian) hasta dejar las 157 previas en verde

**Punto de control**: Todos los formularios de pantalla completa e incrustados están organizados en secciones; el negocio no cambió.

---

## Fase 4: Historia de usuario 2 - Validación inmediata y mensajes claros (Prioridad: P1)

**Objetivo**: Mensajes al salir del campo, estado correcto/error, resumen de errores con salto al primero y conservación de lo escrito cuando el servidor rechaza.

**Prueba independiente**: Llenar el formulario de persona con un correo mal escrito y un celular incompleto; el sistema los marca antes de enviar y, al enviar, lleva al primer error.

### Pruebas de la historia 2

- [ ] T030 [P] [US2] Prueba `laravel_app/resources/js/forms/validar.test.js` (Vitest con jsdom): un correo inválido al hacer `blur` muestra el mensaje en `.invalid-feedback` y marca `is-invalid`/`aria-invalid`; al corregir pasa a `is-valid`; al enviar con errores se cancela el envío, se enfoca el primer campo con error y se crea el resumen con `role="alert"` y un enlace por error; `data-requerido-si` convierte un campo en obligatorio
- [ ] T031 [P] [US2] Prueba `laravel_app/tests/Feature/Formularios/ErroresConservanDatosTest.php`: al enviar persona, célula, evento y sede con errores del servidor (documento repetido, campo obligatorio vacío, correo repetido) la respuesta conserva todo lo escrito (`old()`), marca los campos con `is-invalid` y `aria-invalid`, muestra el mensaje bajo el campo y el resumen de errores con enlaces `#campo`

### Implementación de la historia 2

- [ ] T032 [US2] Crear `laravel_app/resources/js/forms/validar.js`: validación con la API de validación del navegador al salir del campo (`blur`) y al enviar, mensajes en español (obligatorio, correo, patrón, longitud mínima y máxima, máximo/mínimo de fecha), `.invalid-feedback` bajo el campo, estados `is-valid`/`is-invalid`, `aria-invalid` y `aria-describedby`, resumen de errores con `role="alert"` y enlaces, foco al primer campo con error, evento `form:invalido`, y reglas `data-requerido-si="campo:valor[,valor]"`
- [ ] T033 [US2] Mostrar el resumen de errores del servidor en `laravel_app/resources/views/components/form/layout.blade.php` (lista con enlaces `#id` a cada campo, `role="alert"`) y hacer que `validar.js` lo use al cargar: marcar los campos con error del servidor, desplazar y enfocar el primero
- [ ] T034 [P] [US2] Añadir a `laravel_app/resources/views/components/form/input.blade.php`, `select.blade.php`, `date.blade.php`, `money.blade.php`, `textarea.blade.php` y `switch.blade.php` los atributos de validación nativa que hoy valida el servidor (`required`, `maxlength`, `type="email"`, `pattern` donde aplique) sin cambiar lo que el servidor exige, y el indicador discreto de "correcto"
- [ ] T035 [US2] Conectar `validar.js` en `laravel_app/resources/js/forms/index.js` y comprobar con `npm test` y `php artisan test` que la validación del cliente no altera los valores enviados

**Punto de control**: Las historias 1 y 2 funcionan; la validación del servidor sigue siendo la fuente de verdad.

---

## Fase 5: Historia de usuario 3 - Selectores con búsqueda y selección múltiple cómoda (Prioridad: P1)

**Objetivo**: Selectores con búsqueda sin tildes y dato de apoyo; selección múltiple como etiquetas removibles con contador; asistencia y abonos como tarjetas en teléfono.

**Prueba independiente**: En el formulario de evento, seleccionar 10 personas de una lista de 500 escribiendo parte de sus nombres, comprobando que quedan como etiquetas y se guardan.

### Pruebas de la historia 3

- [ ] T036 [P] [US3] Prueba `laravel_app/resources/js/forms/selectores.test.js` (Vitest): el `matcher` ignora mayúsculas y tildes ("angela" encuentra "Ángela"), también busca por el dato de apoyo (documento o celular), y el contador refleja el número de opciones seleccionadas
- [ ] T037 [P] [US3] Prueba `laravel_app/tests/Feature/Formularios/SelectoresTest.php`: los selectores de líder, persona, red y zona imprimen `data-apoyo` (documento o celular) por opción; el formulario de evento, encuentro y ministerio usa `<select multiple name="personas[]">` y solo ofrece personas de la sede y del ámbito del usuario (como hoy); el envío con varias personas guarda las mismas inscripciones que antes
- [ ] T038 [P] [US3] Prueba `laravel_app/tests/Feature/Formularios/ListaAsistenciaTest.php`: el componente genera una sola estructura de datos con dos vistas (`d-none d-md-block` para la tabla y `d-md-none` para las tarjetas), con el mismo número de personas y un interruptor `name="asistio[]"` por persona marcado según `:marcados`

### Implementación de la historia 3

- [ ] T039 [US3] Crear `laravel_app/resources/js/forms/selectores.js`: inicializa `select2` con el tema Bootstrap 4 en `<select>` de una opción con más de 10 opciones o `data-buscar`, y en `<select multiple>`; `matcher` propio con `normalizar` de `texto.js` (nombre y `data-apoyo`); plantilla de resultado que muestra el dato de apoyo; etiquetas removibles; contador "N seleccionadas"; botones "Seleccionar resultados" y "Limpiar" que actúan sobre los resultados filtrados; textos en español; conservar el `name` y valor enviado
- [ ] T040 [US3] Crear `laravel_app/resources/views/components/form/multiselect.blade.php` (`<select multiple>` nativo con `name="personas[]"` por defecto, props `:opciones`, `:seleccionados`, contador y botones de ayuda con `data-*`, mismo contrato de etiqueta, ayuda y error)
- [ ] T041 [US3] Crear `laravel_app/resources/views/components/form/lista-asistencia.blade.php` según el contrato: props `:filas`, `name`, `:marcados`, `columnas`; una sola estructura de datos con `<table>` desde `md` y tarjetas con interruptor grande y botón grande hasta `sm`, y estilos correspondientes en `laravel_app/resources/css/forms.css`
- [ ] T042 [US3] Aplicar `<x-form.multiselect>` en `laravel_app/resources/views/eventos/_form.blade.php`, `laravel_app/resources/views/encuentros/_form.blade.php` y `laravel_app/resources/views/ministerios/_form.blade.php` en lugar de la lista de casillas de personas, conservando `name="personas[]"` y los valores marcados con `old()`
- [ ] T043 [US3] Aplicar `<x-form.select>` con `data-apoyo` y búsqueda en `laravel_app/resources/views/personas/_form.blade.php` (líder directo, red, zona), `laravel_app/resources/views/celulas/_form.blade.php` (líder y co-líder), `laravel_app/resources/views/users/create.blade.php` y `edit.blade.php` (persona vinculada), `laravel_app/resources/views/lideres/quitar.blade.php` y `laravel_app/resources/views/sin-lider/index.blade.php` (líder de destino), `laravel_app/resources/views/traslados/create.blade.php` (sede destino) y `laravel_app/resources/views/redes/index.blade.php` (red de destino de la fusión)
- [ ] T044 [US3] Aplicar `<x-form.lista-asistencia>` en la asistencia de reuniones de `laravel_app/resources/views/celulas/show.blade.php` (`name="asistio[]"`), de encuentros en `laravel_app/resources/views/encuentros/show.blade.php` y en las inscripciones y abonos de `laravel_app/resources/views/eventos/show.blade.php` (columnas costo, pagado y saldo), conservando los envíos actuales
- [ ] T045 [US3] Conectar `selectores.js` en `laravel_app/resources/js/forms/index.js` y ajustar `laravel_app/resources/css/forms.css` para etiquetas, contador, lista de resultados y tamaño táctil de los selectores

**Punto de control**: Las historias 1, 2 y 3 funcionan; los datos enviados no cambian.

---

## Fase 6: Historia de usuario 4 - Campos que reaccionan y ayudan a escribir (Prioridad: P2)

**Objetivo**: Campos dependientes con transición, máscaras de celular, documento y monto, calendario con atajos y teclados adecuados en teléfono, sin cambiar los valores enviados.

**Prueba independiente**: En el formulario de persona, cambiar el estado a "miembro activo" y comprobar que el documento pasa a obligatorio de inmediato; marcar "es líder" y comprobar que aparecen red y zona.

### Pruebas de la historia 4

- [ ] T046 [P] [US4] Prueba `laravel_app/resources/js/forms/mascaras.test.js` (Vitest): el celular escrito como "(310) 555-9999" se envía como "3105559999"; el documento solo conserva dígitos y letras; el monto "50.000,50" se envía como "50000.50"; pegar valores con espacios y símbolos no pierde información
- [ ] T047 [P] [US4] Prueba `laravel_app/resources/js/forms/dependientes.test.js` (Vitest): `data-visible-si="es_lider:1"` muestra la sección al marcar la casilla y la oculta al desmarcar (los campos ocultos quedan `disabled` y no se envían); `data-requerido-si="estado:miembro_activo,nuevo_creyente"` activa y desactiva `required` según el valor
- [ ] T048 [P] [US4] Prueba `laravel_app/tests/Feature/Formularios/CamposAyudaTest.php`: el HTML de persona declara `data-visible-si` para red y zona, `data-requerido-si` para el documento, `type="tel"` e `inputmode` en celular, `data-fecha` con `data-max` en la fecha de nacimiento y el monto de evento con `inputmode="decimal"`; el envío de persona con celular con espacios y símbolos sigue guardándose igual que antes

### Implementación de la historia 4

- [ ] T049 [US4] Crear `laravel_app/resources/js/forms/dependientes.js`: lee `data-visible-si="campo:valor"` en campos y secciones (mostrar/ocultar con transición CSS y deshabilitar lo oculto) y `data-requerido-si` (alterna `required` y la marca de obligatorio), reaccionando a `change` e `input` del campo origen y al cargar la página
- [ ] T050 [US4] Crear `laravel_app/resources/js/forms/mascaras.js` con `inputmask`: `data-mascara="celular"` (grupos de dígitos, máximo 20), `data-mascara="documento"` (letras y dígitos) y `data-monto` (`numeric` con separador de miles y símbolo), con `removeMaskOnSubmit: true` para que el valor enviado sea el de siempre
- [ ] T051 [US4] Crear `laravel_app/resources/js/forms/fechas.js` con `flatpickr` (`es`, visible `d/m/Y`, valor enviado `Y-m-d`), `data-max` y `data-min`, botones de atajo "Hoy" y "Ayer" (`data-atajos`), y que el campo base siga siendo `<input type="date">` sin JavaScript
- [ ] T052 [US4] Aplicar los atributos en las vistas: en `laravel_app/resources/views/personas/_form.blade.php` (`data-visible-si` en red y zona con `es_lider`, `data-requerido-si` en tipo y número de documento con `estado:miembro_activo`, máscara de celular y documento, fecha de nacimiento con `data-max` de hoy); en `laravel_app/resources/views/eventos/_form.blade.php` y `laravel_app/resources/views/eventos/show.blade.php` (montos y fechas de abono); en `laravel_app/resources/views/celulas/_form.blade.php`, `celulas/show.blade.php`, `encuentros/_form.blade.php` y `eventos/_form.blade.php` (fechas y horas); y celular en `laravel_app/resources/views/sedes/_cuenta.blade.php` y `laravel_app/resources/views/sedes/_datos.blade.php`
- [ ] T053 [US4] Conectar `dependientes.js`, `mascaras.js` y `fechas.js` en `laravel_app/resources/js/forms/index.js`, ajustar `laravel_app/resources/css/forms.css` (transición de mostrar/ocultar, estilo del calendario y de los atajos) y verificar con `npm test` y `php artisan test` que ningún valor enviado cambió

**Punto de control**: Las historias 1 a 4 funcionan; los valores enviados son idénticos a los de antes.

---

## Fase 7: Historia de usuario 5 - Retroalimentación al guardar y protección de cambios (Prioridad: P2)

**Objetivo**: Botón con progreso sin doble envío, aviso de cambios sin guardar, diálogos de confirmación propios y avisos de éxito que desaparecen solos.

**Prueba independiente**: Llenar un formulario, intentar cerrar la página y recibir el aviso; guardar con doble clic rápido y comprobar que se crea un solo registro.

### Pruebas de la historia 5

- [ ] T054 [P] [US5] Prueba `laravel_app/resources/js/forms/envio.test.js` (Vitest): al enviar un formulario válido el botón principal se deshabilita y muestra el indicador, un segundo envío se ignora, se restaura si el envío se cancela por validación, y la huella de valores detecta cambios (sin avisar si no hay cambios ni al enviar); nada se escribe en `localStorage` ni `sessionStorage`
- [ ] T055 [P] [US5] Prueba `laravel_app/tests/Feature/Formularios/ConfirmacionesTest.php`: ninguna vista de la aplicación contiene `onclick="return confirm"` (escaneo de `laravel_app/resources/views`); las acciones delicadas (inactivar, eliminar persona, quitar rol, eliminar abono, quitar inscrito, desactivar sede, eliminar célula/ministerio/evento/encuentro/usuario/red/zona) usan `data-confirm` con `data-confirm-titulo` y `data-confirm-mensaje`; el servidor sigue aplicando permisos y reglas (403/404/422) sin importar la confirmación
- [ ] T056 [P] [US5] Prueba `laravel_app/tests/Feature/Formularios/AvisosTest.php`: los mensajes flash de éxito se imprimen en el contenedor de avisos (`role="status"`, `data-avisos`) y los de error permanecen visibles con `role="alert"`

### Implementación de la historia 5

- [ ] T057 [US5] Crear `laravel_app/resources/js/forms/envio.js`: al enviar un formulario válido deshabilita el botón principal con indicador de progreso (incluye `Enter`), ignora envíos repetidos, restaura el botón si el envío se cancela, y calcula la huella de valores al cargar para el aviso `beforeunload` solo cuando hay cambios (omitido con `data-sin-aviso`, sin tocar `localStorage` ni `sessionStorage`); emite `form:enviando` y `form:cambios`
- [ ] T058 [US5] Crear `laravel_app/resources/js/forms/confirmar.js` con `sweetalert2`: intercepta los formularios y botones con `data-confirm`, muestra título, mensaje de consecuencias y botones "Continuar"/"Cancelar" (en español, foco inicial en Cancelar si es de peligro, cierre con `Esc`), y envía el formulario al confirmar
- [ ] T059 [US5] Crear `laravel_app/resources/js/forms/avisos.js` con `sweetalert2` (modo aviso): convierte los mensajes de éxito en avisos discretos que se cierran solos a los 5 s con `role="status"`, y deja los de error visibles; sin JavaScript los mensajes siguen apareciendo como alertas normales
- [ ] T060 [US5] Crear `laravel_app/resources/views/components/form/confirm.blade.php` según el contrato (props `action`, `method`, `titulo`, `mensaje`, `boton`, `peligro`; genera `data-confirm*` y el botón con el ícono correspondiente)
- [ ] T061 [US5] Añadir en `laravel_app/resources/views/layouts/admin.blade.php` el contenedor de avisos (`data-avisos`, `aria-live="polite"`) que recibe los mensajes flash `success` y `error` y retirar de las vistas los bloques repetidos `@if(session('success')) ... alert-success` en `laravel_app/resources/views/celulas/`, `encuentros/`, `eventos/`, `lideres/`, `ministerios/`, `personas/`, `redes/`, `sedes/`, `sin-lider/`, `users/` y `zonas/` (los errores de formulario no cambian)
- [ ] T062 [US5] Reemplazar los `onclick="return confirm(...)"` por `<x-form.confirm>` o `data-confirm` con el detalle de las consecuencias en `laravel_app/resources/views/celulas/index.blade.php`, `celulas/show.blade.php`, `encuentros/index.blade.php`, `eventos/index.blade.php`, `eventos/show.blade.php`, `ministerios/index.blade.php`, `personas/index.blade.php`, `personas/show.blade.php`, `redes/index.blade.php`, `sedes/show.blade.php`, `users/index.blade.php` y `zonas/index.blade.php`
- [ ] T063 [US5] Conectar `envio.js`, `confirmar.js` y `avisos.js` en `laravel_app/resources/js/forms/index.js`, ajustar estilos del botón con progreso y de los diálogos en `laravel_app/resources/css/forms.css` y verificar con `npm test` y `php artisan test`

**Punto de control**: Las historias 1 a 5 funcionan; no quedan cuadros de confirmación nativos.

---

## Fase 8: Historia de usuario 6 - Mismo estilo moderno en todos los formularios (Prioridad: P3)

**Objetivo**: Todos los formularios restantes (filtros, inicio de sesión, barra superior) comparten estilo y comportamientos, y una prueba de inventario garantiza que ninguno quede fuera.

**Prueba independiente**: Abrir todos los formularios de la lista de alcance y comprobar visualmente que comparten tarjetas, tipografía, colores, ayudas y botones.

### Pruebas de la historia 6

- [ ] T064 [P] [US6] Prueba `laravel_app/tests/Feature/Formularios/InventarioFormulariosTest.php`: recorre todas las vistas de `laravel_app/resources/views` y falla si encuentra un `<form` con `method="POST"` (o `@method`) sin `data-form` o sin pasar por `<x-form.layout>`/`<x-form.confirm>`, con una lista explícita de excepciones justificadas (cerrar sesión y cambio de sede activa de la barra superior); recorre además las rutas de formularios del inventario y comprueba el marcador `data-form` y la ausencia de `onclick="return confirm"`
- [ ] T065 [P] [US6] Prueba `laravel_app/tests/Feature/Formularios/SinJavascriptTest.php`: los controles nativos están presentes (`<select>`, `<input type="date">`, `<input type="tel">`, casillas), un formulario se envía y se valida en el servidor sin tocar ningún atributo `data-*`, los errores vuelven con `old()`, y con admin en "Todas las sedes" el botón principal aparece deshabilitado con el aviso de solo consulta

### Implementación de la historia 6

- [ ] T066 [P] [US6] Migrar los filtros de `laravel_app/resources/views/personas/index.blade.php`, `laravel_app/resources/views/lideres/index.blade.php`, `laravel_app/resources/views/sin-lider/index.blade.php` y `laravel_app/resources/views/users/index.blade.php` al modo `filtro` de `<x-form.layout>` (campos en línea, selectores con búsqueda, método `GET`, "Buscar" y "Limpiar") conservando los nombres de parámetros `q`, `estado`, `red_id`, `zona_id`, `lider_id`, `inactivos`, `sede_id`
- [ ] T067 [P] [US6] Migrar `laravel_app/resources/views/auth/login.blade.php` a una versión compacta de `<x-form.layout>` (correo con ícono, contraseña con mostrar/ocultar, mensaje de error de credenciales visible y accesible, botón con progreso) conservando `email`, `password` y la ruta `login.post`
- [ ] T068 [US6] Revisar y migrar cualquier formulario restante que aparezca en `InventarioFormulariosTest` (por ejemplo el selector de sede activa y el cierre de sesión de `laravel_app/resources/views/layouts/admin.blade.php`, que mantienen su aspecto compacto pero comparten el estilo de campos y botones) hasta que la prueba pase sin excepciones adicionales
- [ ] T069 [US6] Verificar la convivencia con la marca de agua de la sede y el modo "Todas las sedes: solo consulta" en todos los modos de formulario (completo, incrustado, filtro, confirmación y login), ajustando `laravel_app/resources/css/forms.css` y `laravel_app/resources/views/components/form/layout.blade.php` si algo estorba

**Punto de control**: Las seis historias funcionan y el inventario no encuentra formularios fuera de los componentes.

---

## Fase 9: Pulido y asuntos transversales

**Propósito**: Accesibilidad, rendimiento, documentación, compilación y validación final.

- [ ] T070 Ejecutar `npm run build` en `laravel_app/`, comparar el tamaño de `laravel_app/public/build/assets/app-*.js` y `app-*.css` con la línea base de T001 (objetivo: aumento ≤ 150 KB comprimido) y confirmar que `public/build/manifest.json` refleja los nuevos activos
- [ ] T071 [P] Prueba `laravel_app/tests/Feature/Formularios/AccesibilidadFormulariosTest.php`: para cada formulario del inventario comprueba que todo `input`, `select` y `textarea` visible tiene `<label for>` con el `id`, que los obligatorios tienen `aria-required`, que los campos con error tienen `aria-invalid` y `aria-describedby`, y que el resumen de errores usa `role="alert"`
- [ ] T072 [P] Prueba de escala `laravel_app/tests/Feature/Formularios/SelectoresEscalaTest.php`: el formulario de evento de una sede con 1.000 personas responde correctamente con un `<select multiple>` de 1.000 opciones y su HTML pesa menos de 250 KB sin comprimir
- [ ] T073 Ejecutar `php artisan test` y `npm test` en `laravel_app/` y corregir fallos en `laravel_app/tests/` y `laravel_app/resources/js/forms/` hasta dejar las 157 pruebas previas y todas las nuevas en verde
- [ ] T074 [P] Actualizar `DEPLOY_CPanel.md` y `README.md` con el paso de compilación de activos (`npm install && npm run build` en local, subir `laravel_app/public/build`, no hay Node en cPanel), los comandos `npm test` y `npm run dev`, y la nota de que los formularios funcionan sin JavaScript
- [ ] T075 [P] Documentar en `README.md` cómo crear un formulario nuevo con los componentes (`<x-form.layout>`, secciones y campos) y la regla de que la prueba de inventario falla si un formulario no los usa
- [ ] T076 Recorrer los 21 escenarios de `specs/003-formularios-modernos/quickstart.md` en el navegador con una base temporal (`DB_DATABASE=<ruta>\prueba.sqlite`, nunca la del `.env`), incluyendo teléfono de 360 px, uso solo con teclado y JavaScript deshabilitado, y anotar cualquier desvío en el propio quickstart

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias; empieza de inmediato.
- **Fundamentos (Fase 2)**: depende de la Fase 1 y **bloquea** todas las historias.
- **Historias (Fases 3–8)**: dependen de la Fase 2; US1 conviene hacerla primero porque deja todas las vistas en componentes.
- **Pulido (Fase 9)**: depende de todas las historias (la entrega es única).

### Dependencias entre historias

- **US1 (P1)**: tras Fundamentos; migra todas las vistas a los componentes base.
- **US2 (P1)**: tras Fundamentos; para ver su efecto en pantalla requiere las vistas migradas de US1 (T018 como mínimo).
- **US3 (P1)**: tras US1 (usa las vistas migradas y los componentes `select`); `lista-asistencia` y `multiselect` reemplazan marcado en esas vistas.
- **US4 (P2)**: tras US1 (añade atributos en las vistas migradas) y US2 (`data-requerido-si` lo interpreta `validar.js` junto a `dependientes.js`).
- **US5 (P2)**: tras US1; T062 recorre vistas de listados y fichas ya migradas.
- **US6 (P3)**: tras US1–US5; la prueba de inventario valida el conjunto.

### Dentro de cada historia

- Las pruebas se escriben primero y deben fallar antes de implementar.
- Componentes Blade y JS → aplicación en las vistas → ajustes de estilo → conexión en `index.js` → verificación.
- Tareas que editan el mismo archivo (por ejemplo `laravel_app/resources/js/forms/index.js`, `forms.css` o `personas/_form.blade.php`) se hacen en orden, no en paralelo.

### Oportunidades de paralelismo

- Fase 1: T003 y T004.
- Fase 2: T007, T008, T011, T012, T013 y T014 (archivos distintos).
- US1: T016 y T017; T019 a T027 (cada una migra archivos distintos).
- US2: T030 y T031; T034.
- US3: T036, T037 y T038.
- US4: T046, T047 y T048.
- US5: T054, T055 y T056.
- US6: T064 y T065; T066 y T067.
- Pulido: T071, T072, T074 y T075.

---

## Ejemplo de paralelismo: Historia de usuario 1

```text
# Migración de formularios en paralelo (archivos distintos), después de T018 (formulario de referencia):
T019 celulas  |  T020 encuentros  |  T021 eventos  |  T022 ministerios
T023 users    |  T024 sedes       |  T025 traslados/quitar/designar
T026 formularios incrustados de fichas  |  T027 redes, zonas y sin-lider
```

---

## Estrategia de implementación

### Entrega única, trabajo por etapas

La entrega es **única** (todos los formularios a la vez), pero se trabaja por etapas para reducir riesgo:

1. Fase 1 y 2: dependencias, estilos y componentes base; las 157 pruebas siguen en verde.
2. US1 (MVP de trabajo): persona como formulario de referencia (T018) y luego el resto en paralelo; punto de control con el negocio sin cambios.
3. US2 y US3: validación y selectores, que son lo que más cambia el trabajo diario.
4. US4 y US5: ayudas de captura, envío seguro, diálogos y avisos.
5. US6 y Pulido: filtros, login, prueba de inventario, compilación, documentación y recorrido manual.

### Notas

- **Riesgo principal**: romper el envío de un formulario al cambiar su HTML. Mitigación: no cambiar `name`, `action` ni `method`; las 157 pruebas de negocio y las pruebas de estructura detectan regresiones en cada punto de control.
- **Sin JavaScript** los formularios deben seguir funcionando (T065); toda mejora es progresiva.
- **Valores enviados idénticos**: máscaras con `removeMaskOnSubmit` y fechas `Y-m-d` (T046, T048, T053).
- **Despliegue**: sin Node en cPanel; hay que compilar y subir `public/build` (T070, T074).
- Hacer commit tras cada tarea o grupo lógico.
