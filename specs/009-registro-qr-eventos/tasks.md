# Tareas: Registro por código QR a eventos, encuentros y células

**Entrada**: documentos de diseño en `/specs/009-registro-qr-eventos/`

**Requisitos previos**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/rutas-registro.md`, `quickstart.md`

**Pruebas**: se incluyen porque la constitución (principio IV) exige pruebas primero; cada fase de historia escribe sus pruebas antes de la implementación.

**Organización**: tareas agrupadas por historia de usuario. Todas las rutas son relativas a `laravel_app/`.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[US#]**: historia de usuario a la que pertenece

---

## Fase 1: Preparación

- [X] T001 Agregar la dependencia `bacon/bacon-qr-code` con `composer require bacon/bacon-qr-code` en `composer.json` y `composer.lock`, y verificar que genera SVG sin extensiones adicionales
- [X] T002 [P] (ya cumplido: `public/sw.js` solo guarda `/build/assets/*` e `/iconos/*`; no hizo falta cambiar nada) Excluir `/registro/*` del caché del service worker (buscar el archivo del service worker en `public/` o `resources/js/`) y documentarlo en `tests/movil/` si hay una lista de rutas

---

## Fase 2: Fundamentos (bloquea todas las historias)

**⚠️ CRÍTICO**: ninguna historia puede empezar hasta terminar esta fase.

- [X] T003 Crear la migración `database/migrations/2026_10_26_000001_add_registro_qr_a_eventos_encuentros_celulas.php`: en `eventos`, `encuentros` y `celulas` agregar `registro_token` (cadena de 32, única, no nula tras rellenar las filas existentes con `Str::random(32)`) y `registro_abierto` (booleano, por defecto verdadero)
- [X] T004 Crear la migración `database/migrations/2026_10_26_000002_create_registros_qr_table.php` con los campos de `data-model.md`: `sede_id`; `registrable_type`/`registrable_id` (polimórfico); `persona_id` nulo; `nombres`, `apellidos` (máximo 100); `celular` (normalizado, solo dígitos); `fecha_nacimiento` (obligatoria); `tipo_documento` y `numero_documento` nulos; `resultado` (`nueva`, `existente`, `de_otra_sede`, `pendiente`); `nota_correccion` (texto, nulo); `timestamps`; índice único (`registrable_type`, `registrable_id`, `celular`)
- [X] T005 [P] Crear el modelo `app/Models/RegistroQr.php` con `PerteneceASede`, atributos rellenables, relación `registrable()` (morphTo), relación `persona()` con `withoutGlobalScopes()` y constante `RESULTADOS`
- [X] T006 [P] Crear el trait `app/Models/Concerns/TieneRegistroQr.php`: genera `registro_token` al crear, `estaAbierto()` (`registro_abierto` y estado ni cancelado ni realizado; célula: activa), `regenerarToken()`, relación `registrosQr()` (morphMany) y `urlRegistro()`
- [X] T007 Usar `TieneRegistroQr` en `app/Models/Evento.php`, `app/Models/Encuentro.php` y `app/Models/Celula.php` y agregar `registro_abierto` a sus campos rellenables (depende de T003, T006)
- [X] T008 [P] Crear `app/Services/GeneradorQr.php`: recibe una URL y devuelve el SVG con `bacon/bacon-qr-code` (depende de T001)
- [X] T009 Definir la habilidad `gestionarRegistroQr` con el mismo alcance que editar el destino (administrador y pastor de la sede; líder solo de su célula) en `app/Providers/AppServiceProvider.php` o la política existente, siguiendo el patrón de `Permisos`
- [X] T010 [P] Crear las fábricas o ayudantes de prueba para `RegistroQr` en `database/factories/RegistroQrFactory.php` y reutilizar `tests/Support/EscenarioDosSedes.php`
- [X] T011 Crear el esqueleto de `app/Services/RegistroPorQr.php` con los métodos `resolverDestino(string $token)` (busca el token en las tres tablas con `SedeContext::sinFiltro()` y devuelve el destino o nulo) y `buscarPersona(destino, celular, documento)` (dentro de `conSede`), sin la lógica de inscripción todavía (depende de T007)

**Punto de control**: migraciones aplicadas, modelos y servicio base listos.

---

## Fase 3: Historia de Usuario 1 - Generar y compartir el QR (Prioridad: P1) 🎯 MVP

**Objetivo**: el administrador ve, descarga, abre/cierra y regenera el QR de un evento, encuentro o célula.

**Prueba independiente**: crear un evento, abrir su ficha, ver y descargar el QR, y comprobar que el enlace apunta a su formulario.

### Pruebas de la Historia 1 (escribir primero, deben fallar)

- [X] T012 [P] [US1] Prueba `tests/Feature/RegistroQr/PanelTest.php`: la ficha de evento, encuentro y célula muestra el QR y el enlace; la descarga `registro-qr.svg` devuelve SVG; abrir/cerrar y regenerar cambian el estado y el token (el token anterior deja de resolver)
- [X] T013 [P] [US1] Prueba `tests/Feature/RegistroQr/AislamientoTest.php` (parte admin): un pastor de la sede A recibe 404 o 403 al ver, descargar, abrir/cerrar o regenerar el QR de un destino de la sede B; un usuario sin permiso recibe 403

### Implementación de la Historia 1

- [X] T014 [US1] Crear `app/Http/Controllers/RegistroQrController.php` con `svg`, `abrir` y `regenerar`; validar `{tipo}` ∈ `evento|encuentro|celula`, resolver el modelo con el filtro de sede (404 fuera de alcance) y exigir `gestionarRegistroQr`
- [X] T015 [US1] Agregar a `routes/web.php` las rutas `registro-qr.svg`, `registro-qr.abrir` y `registro-qr.regenerar` según `contracts/rutas-registro.md`
- [X] T016 [P] [US1] Crear la vista parcial `resources/views/partials/panel-registro-qr.blade.php` con el QR en línea, el enlace con botón «Copiar», el botón «Descargar», el interruptor abrir/cerrar y «Regenerar enlace» (con confirmación usando el componente de confirmación existente); formularios solo con componentes `x-form.*` y diseño móvil (controles de 44 px)
- [X] T017 [US1] Incluir el panel en `resources/views/eventos/show.blade.php`, `resources/views/encuentros/show.blade.php` y `resources/views/celulas/show.blade.php` solo si el usuario tiene `gestionarRegistroQr`
- [X] T018 [US1] (se reutilizó `data-copiar` y `resources/js/forms/copiar.js` ya existentes) Agregar a `resources/js/forms/` el comportamiento de «Copiar enlace» (con prueba Vitest `resources/js/forms/copiar-enlace.test.js`) y registrarlo en `resources/js/forms/index.js`

**Punto de control**: el administrador gestiona el QR de las tres fichas; MVP del canal.

---

## Fase 4: Historia de Usuario 2 - Registro de persona nueva (Prioridad: P1)

**Objetivo**: una persona sin sesión escanea el QR, completa sus datos y queda creada e inscrita.

**Prueba independiente**: abrir el enlace sin sesión con un celular nuevo, enviar el formulario y ver a la persona en Personas y en inscritos.

### Pruebas de la Historia 2 (escribir primero)

- [X] T019 [P] [US2] Prueba `tests/Feature/RegistroQr/FlujoNuevaPersonaTest.php`: para evento, encuentro y célula, un celular nuevo crea la persona en la sede del destino (estado «Visitante»; en célula «En célula»), crea la inscripción (`evento_persona`, `encuentro_persona`, `celula_persona`) y un `RegistroQr` con `resultado = nueva`; redirige a `registro.listo`
- [X] T020 [P] [US2] Prueba `tests/Feature/RegistroQr/ValidacionTest.php`: celular inválido (menos de 10 dígitos), nombres o apellidos vacíos o de más de 100 caracteres, fecha de nacimiento ausente, futura o anterior a 1900, número de documento sin tipo de documento; los errores aparecen junto al campo y no se crea nada; segundo envío con el mismo celular responde «ya estás registrado» sin duplicar
- [X] T021 [P] [US2] Prueba `tests/Feature/RegistroQr/DestinoCerradoTest.php`: destino cancelado, realizado, célula inactiva, registro desactivado y token inexistente o regenerado muestran «registro cerrado» o 404 «enlace no válido» sin revelar datos

### Implementación de la Historia 2

- [X] T022 [US2] Completar en `app/Services/RegistroPorQr.php` el método `registrar(destino, datos)`: normaliza el celular con `App\Support\Celular::limpio`, y dentro de `SedeContext::conSede` crea la `Persona` (estado «Visitante», sin líder, sede del destino), la inscripción del destino (célula: `fecha_ingreso` hoy y estado «En célula» con `Estados`) y el `RegistroQr` `nueva`, todo en una transacción; rechaza el duplicado de (destino, celular)
- [X] T023 [P] [US2] Crear `app/Http/Requests/RegistroQrRequest.php` con las reglas de `data-model.md` (celular con `Celular::regla()`, nombres y apellidos obligatorios máximo 100, `fecha_nacimiento` fecha pasada posterior a 1900, `tipo_documento` en `Persona::TIPOS_DOCUMENTO` obligatorio si hay `numero_documento`) y mensajes en español
- [X] T024 [US2] Crear `app/Http/Controllers/RegistroPublicoController.php` con `show`, `store` y `listo`: resuelve el destino con `RegistroPorQr::resolverDestino`, muestra cerrado o 404 según el caso, agrega `Cache-Control: no-store`
- [X] T025 [US2] Agregar a `routes/web.php` el grupo público `registro.show`, `registro.store` y `registro.listo` con `throttle` y registrar `registro.*` en `ResuelveSede::EXENTAS` (`app/Http/Middleware/ResuelveSede.php`)
- [X] T026 [P] [US2] Crear las vistas `resources/views/registro/formulario.blade.php`, `confirmacion.blade.php` y `cerrado.blade.php` sobre el diseño `layouts/auth.blade.php`, con componentes `x-form.*`, `inputmode="numeric"` para celular y cédula, letra de 16 px y controles de 44 px
- [X] T027 [US2] Ajustar `resources/css/forms.css` para el formulario público a 320 px sin desplazamiento horizontal y ejecutar `npm run build`

**Punto de control**: una persona nueva se registra de punta a punta.

---

## Fase 5: Historia de Usuario 3 - Persona existente por cédula o celular (Prioridad: P1)

**Objetivo**: buscar a la persona, precargar sus datos y evitar duplicados, incluyendo conflictos y personas de otra sede.

**Prueba independiente**: con una persona ya creada, escribir su cédula o celular, ver los datos precargados y comprobar que la inscripción queda en esa misma persona.

### Pruebas de la Historia 3 (escribir primero)

- [X] T028 [P] [US3] Prueba `tests/Feature/RegistroQr/BusquedaTest.php`: `registro.buscar` por celular y por cédula devuelve `existente` con nombres, apellidos y fecha de nacimiento (sin documento, correo ni dirección); cédula inexistente devuelve `nueva`; cédula y celular de personas distintas, o dos personas con el mismo celular, devuelve `conflicto` y no guarda nada
- [X] T029 [P] [US3] Prueba `tests/Feature/RegistroQr/FlujoExistenteTest.php`: persona de la sede se inscribe sin crear otra persona, sus datos en `personas` no cambian y las diferencias escritas quedan en `nota_correccion`; persona inactiva o con borrado lógico genera `RegistroQr` `pendiente` sin inscripción ni persona nueva; ya miembro de la célula no se duplica
- [X] T030 [P] [US3] Prueba `tests/Feature/RegistroQr/AislamientoTest.php` (parte pública): el celular o cédula de una persona de la sede B en el enlace de la sede A responde `nueva` en la búsqueda (no revela existencia), no se precarga nada, la persona de B no cambia, no se crea persona en A y se guarda `RegistroQr` `de_otra_sede` con lo escrito
- [X] T031 [P] [US3] Prueba `tests/Feature/RegistroQr/LimitesTest.php`: más de 30 búsquedas por minuto por IP o 10 envíos por 10 minutos por IP y celular responde 429, en JSON cuando se pide JSON
- [X] T032 [P] [US3] Prueba Vitest `resources/js/forms/registro-qr.test.js`: al salir del campo de cédula o celular consulta `registro.buscar` y precarga o limpia los campos; sin JavaScript el paso 1 funciona por servidor

### Implementación de la Historia 3

- [X] T033 [US3] Completar `buscarPersona` en `app/Services/RegistroPorQr.php`: busca por cédula (si se escribió) y por celular dentro de `conSede`; clasifica 0, 1, conflicto (cédula y celular de personas distintas, o celular repetido) y persona inactiva o eliminada lógicamente (con `withTrashed`); para «otra sede» hace una consulta de solo existencia con `sinFiltro()` sin leer datos
- [X] T034 [US3] Extender `registrar` en `app/Services/RegistroPorQr.php`: resultado `existente` (inscribe, no modifica la persona, guarda diferencias en `nota_correccion`), `pendiente` y `de_otra_sede` (solo `RegistroQr` con `persona_id` nulo, sin inscripción)
- [X] T035 [US3] Agregar `buscar` a `app/Http/Controllers/RegistroPublicoController.php` y la ruta `registro.buscar`: responde JSON (`nueva`, `existente`, `conflicto`, `pendiente`) o el paso 2 del formulario sin JS; nunca devuelve documento, correo ni dirección; para «otra sede» responde `nueva`
- [X] T036 [US3] Agregar el límite de frecuencia con `RateLimiter` (30 consultas por minuto por IP; 10 envíos por 10 minutos por IP y celular; `Retry-After`) en `RegistroPublicoController` siguiendo el patrón de `app/Http/Controllers/RecuperacionClaveController.php`, y responder los errores en JSON porque la ruta no es `api/*`
- [X] T037 [P] [US3] Crear `resources/js/forms/registro-qr.js` (precarga con `fetch` por cédula o celular, sin guardar datos en el navegador) y registrarlo en `resources/js/forms/index.js`; ejecutar `npm test` y `npm run build`

**Punto de control**: sin duplicados y sin fugas entre sedes.

---

## Fase 6: Historia de Usuario 4 - Ver quién se registró por QR (Prioridad: P2)

**Objetivo**: el administrador ve en cada ficha quiénes llegaron por QR, con fecha, hora y origen.

**Prueba independiente**: registrar una persona nueva, una existente y una de otra sede, y comprobar que las tres aparecen marcadas en la ficha.

### Pruebas de la Historia 4 (escribir primero)

- [X] T038 [P] [US4] Prueba `tests/Feature/RegistroQr/ListaRegistradosTest.php`: la ficha muestra «Registrados por QR» con nombre, celular, resultado, fecha y hora; las de «otra sede» van marcadas y sin enlace a la ficha de la persona; las nuevas y existentes enlazan a `personas.show`; un usuario de otra sede no ve esos registros; la persona creada por QR aparece en `personas.index` de la sede del destino

### Implementación de la Historia 4

- [X] T039 [US4] (se carga en `partials/panel-registro-qr.blade.php`, sin tocar los controladores) Cargar `registrosQr` con la persona (sin filtro solo para mostrar nombre ya guardado) en `EventosController::show`, `EncuentrosController::show` y `CelulasController::show` dentro de `app/Http/Controllers/`
- [X] T040 [P] [US4] Crear la vista parcial `resources/views/partials/lista-registros-qr.blade.php` (tabla pequeña con clase `tabla-apilable`, insignias de resultado «Nueva persona», «Ya existía», «De otra sede», «Pendiente» y la nota de corrección) e incluirla en las tres fichas
- [X] T041 [US4] (insignia «QR» en `eventos/show`; en encuentros y células la lista «Registrados por QR» de la misma ficha cubre el origen) Marcar en las listas de inscritos existentes (`resources/views/eventos/show.blade.php`, `encuentros/show.blade.php`, `celulas/show.blade.php`) las inscripciones que tienen un `RegistroQr`, con la insignia «QR»

**Punto de control**: el administrador sabe quién se registró desde una sola pantalla.

---

## Fase 7: Pulido y transversales

- [X] T042 [P] Actualizar `tests/Feature/Aislamiento/MatrizRutasTest.php` para cubrir las rutas `registro.*` (públicas) y `registro-qr.*` con parámetros de la sede B, incluyendo el parámetro `{token}` y `{tipo}/{id}`
- [X] T043 [P] Agregar la sección «Registro por QR» al `README.md` (uso, límites de frecuencia, despliegue con `composer install` por la dependencia nueva) y la mención en `DEPLOY_CPanel.md`
- [X] T044 [P] Ejecutar `npm run test:movil` con la ruta pública a 320 px y 1920 px y corregir; `tests/movil/excepciones.json` debe quedar vacío
- [X] T045 Ejecutar `php artisan test`, `npm test` y `npm run build` en verde y recorrer en el navegador los 10 escenarios de `quickstart.md`
- [X] T046 (no hizo falta enmendar la constitución) Enmendar `.specify/memory/constitution.md` solo si el flujo público exige un principio nuevo (por ejemplo, «Rutas públicas con límite de frecuencia»); si no, dejarlo igual

---

## Dependencias y orden de ejecución

- **Fase 1** → **Fase 2** (bloquea todo) → **Fases 3, 4, 5, 6** → **Fase 7**.
- **US1** no depende de otras historias (solo de la Fase 2).
- **US2** depende de la Fase 2 y de T011; es independiente de US1 (se puede probar con el enlace del modelo).
- **US3** extiende `RegistroPorQr` y `RegistroPublicoController` creados en US2 (T033–T036 después de T022–T025).
- **US4** necesita registros creados por US2 y US3 para mostrarse completos, pero su vista se puede probar con fábricas.
- Dentro de cada historia: pruebas → servicios → controladores y rutas → vistas.

### Oportunidades de paralelismo

- Fase 2: T005, T006, T008, T010 en paralelo tras T003 y T004.
- US1: T012 y T013 juntas; T016 y T018 en paralelo a T014.
- US2: T019, T020 y T021 juntas; T023 y T026 en paralelo.
- US3: T028 a T032 juntas; T037 en paralelo a T033 a T036.
- Fase 7: T042, T043 y T044 en paralelo.

## Estrategia de implementación

1. **MVP**: Fases 1, 2 y 3 (el administrador ya obtiene el QR), seguidas de la Historia 2 para que alguien pueda registrarse.
2. **Entrega incremental**: US2 → US3 (identidad y aislamiento) → US4 (visibilidad) → pulido.
3. Como US2 sin US3 podría crear duplicados con personas existentes, **no se publica US2 sin US3**.
4. Cada punto de control termina con `php artisan test` en verde.

## Notas

- Las migraciones nunca se ejecutan con `migrate:fresh` sobre la base local; las pruebas usan bases temporales.
- No hacer commits salvo que lo pida el responsable del proyecto.
- Total: 46 tareas.
