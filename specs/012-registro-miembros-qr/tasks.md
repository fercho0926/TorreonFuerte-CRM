---

description: "Lista de tareas para implementar el autorregistro y actualización de miembros por QR"
---

# Tareas: Autorregistro y actualización de miembros por código QR

**Entrada**: Documentos de diseño en `/specs/012-registro-miembros-qr/`

**Prerrequisitos**: plan.md, spec.md, research.md, data-model.md, contracts/formulario-publico.md, contracts/gestion-qr-miembros.md, quickstart.md

**Pruebas**: Obligatorias por el principio IV de la constitución (PHPUnit, Vitest y `npm run test:movil`); se escriben antes del código y deben fallar antes de implementar.

**Organización**: Las tareas se agrupan por historia de usuario para poder implementarlas y probarlas de forma independiente.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: Se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[HU]**: Historia de usuario a la que pertenece (HU1…HU5)
- Todas las rutas son relativas a la raíz del repositorio; el código vive en `laravel_app/`

## Convenciones

- PHP: `laravel_app/app/...`; pruebas en `laravel_app/tests/Feature/RegistroMiembrosQr/`
- Vistas: `laravel_app/resources/views/registro-miembros/` y `laravel_app/resources/views/partials/`
- Mientras la funcionalidad 010 no se aplique en el repositorio, los modelos se llaman `Persona`/`personas`; los textos visibles dicen «miembros».
- Nunca se ejecuta `migrate:fresh` sobre la base local; las pruebas usan bases temporales.
- Ejecuta `npm run test:movil` solo una vez a la vez (dos corridas simultáneas comparten servidor y base y dan falsos fallos).

---

## Fase 1: Preparación

**Propósito**: Línea base y estructura mínima.

- [X] T001 Verificar la línea base ejecutando `php artisan test` y `npm test` en `laravel_app/` y anotar si hay pruebas en rojo previas a este trabajo.
- [X] T002 [P] Crear los directorios vacíos `laravel_app/resources/views/registro-miembros/` y `laravel_app/tests/Feature/RegistroMiembrosQr/`.

---

## Fase 2: Fundamentos (bloquea todas las historias)

**Propósito**: Datos, modelos, búsqueda reutilizable, permisos y rutas que todas las historias necesitan.

**⚠️ CRÍTICO**: Ninguna historia puede empezar hasta terminar esta fase.

- [X] T003 Crear la migración `laravel_app/database/migrations/2026_10_27_000001_add_registro_miembros_a_sedes.php`: en `sedes` agregar `registro_token` (cadena de 32, nula al inicio, relleno con `Str::random(32)` para las filas existentes y luego única con el nombre `sedes_registro_token_unique`) y `registro_abierto` (booleano, por defecto verdadero); crear la tabla `registros_miembros_qr` con `id`, `sede_id` (FK a `sedes`, borrado en cascada), `persona_id` (entero nulo, FK a `personas`, a nulo al borrar), `nombres` (cadena máx. 100), `apellidos` (cadena máx. 100), `celular` (cadena máx. 20), `resultado` (cadena máx. 20: `nueva`, `actualizada`, `reactivada` o `sin_cambios`), `cambios` (JSON nulo), marcas de tiempo e índice `(sede_id, created_at)`; sin restricción de unicidad por celular; implementar `down()`. Seguir el estilo de `2026_10_26_000001_add_registro_qr_a_eventos_encuentros_celulas.php`.
- [X] T004 [P] Extender `laravel_app/app/Models/Sede.php`: agregar `registro_token` y `registro_abierto` a `#[Fillable]`, cast booleano de `registro_abierto`, generación de `registro_token` al crear (`Str::random(32)`), `registroMiembrosAbierto(): bool` (`registro_abierto` y `activa`), `regenerarTokenRegistro(): void` y `urlRegistroMiembros(): string` (ruta `registro-sede.show`, generando token si falta), más la relación `registrosMiembrosQr()` hacia `RegistroMiembroQr` con los más recientes primero.
- [X] T005 [P] Crear `laravel_app/app/Models/RegistroMiembroQr.php` (tabla `registros_miembros_qr`, `PerteneceASede`, `#[Fillable]` con `sede_id`, `persona_id`, `nombres`, `apellidos`, `celular`, `resultado`, `cambios`; cast array de `cambios`; constante `RESULTADOS = ['nueva', 'actualizada', 'reactivada', 'sin_cambios']`; relación `persona()` con `withTrashed()`; `nombreCompleto()` y `etiquetaResultado()`), siguiendo `laravel_app/app/Models/RegistroQr.php`.
- [X] T006 [P] Agregar a `PersonaCambio::ACCIONES` en `laravel_app/app/Models/PersonaCambio.php` las acciones `creada_qr` => «Creado por QR de miembros», `actualizada_qr` => «Actualizado por QR de miembros» y `reactivada_qr` => «Reactivado por QR de miembros».
- [X] T007 Refactorizar `laravel_app/app/Services/RegistroPorQr.php`: extraer de `buscar()` un método público `buscarEnSede(int $sedeId, ?string $celular, ?string $documento): array` con la misma lógica y resultados (nueva, existente, pendiente, conflicto, de_otra_sede) y hacer que `buscar(Model $destino, …)` lo llame con `$destino->sede_id`; el comportamiento y las pruebas de la funcionalidad 009 no deben cambiar.
- [X] T008 Definir en `laravel_app/app/Providers/AppServiceProvider.php` los gates `gestionarRegistroMiembros` (`User $user`, `?Sede $sede`: verdadero para `admin` con cualquier sede, y para `pastor` solo si `$user->sede_id === $sede->id`; falso para cualquier otro rol) y `verRegistroMiembros` (`User $user`: verdadero solo si el rol es `pastor`, para el menú).
- [X] T009 Agregar la opción «Registro de miembros» (icono `fa-qrcode`, ruta `registro-miembros.index`, `activa_en` `['registro-miembros.*']`, `permiso` `['verRegistroMiembros', Sede::class]`) a la sección «Congregación» de `laravel_app/config/menu.php`.
- [X] T010 Registrar en `laravel_app/routes/web.php` las rutas públicas `registro-sede.show` (GET `/registro-sede/{token}`), `registro-sede.buscar` (POST `/registro-sede/{token}/buscar`), `registro-sede.store` (POST `/registro-sede/{token}`) y `registro-sede.listo` (GET `/registro-sede/{token}/listo`) hacia `RegistroMiembrosPublicoController`, y las de gestión `registro-miembros.index` (GET `/registro-miembros`), `registro-miembros.svg` (GET `/sedes/{sede}/registro-miembros/qr.svg`), `registro-miembros.abrir` (POST `/sedes/{sede}/registro-miembros/abrir`) y `registro-miembros.regenerar` (POST `/sedes/{sede}/registro-miembros/regenerar`) hacia `RegistroMiembrosQrController`; importar ambos controladores.
- [X] T011 Agregar `registro-sede.show`, `registro-sede.buscar`, `registro-sede.store` y `registro-sede.listo` a la constante `IGNORADAS` de `laravel_app/tests/Feature/Aislamiento/MatrizRutasTest.php` (son públicas por diseño, como `registro.*`; su aislamiento se prueba en T044) y confirmar que las rutas con `{sede}` de gestión quedan cubiertas por la matriz (otra sede responde 403 o 404).
- [X] T012 Crear los esqueletos `laravel_app/app/Http/Controllers/RegistroMiembrosPublicoController.php` y `laravel_app/app/Http/Controllers/RegistroMiembrosQrController.php` (clases con los métodos vacíos de los contratos que respondan 501 hasta implementarlos en cada historia) y `laravel_app/app/Services/RegistroMiembrosQr.php` (constructor con `SedeContext` y `RegistroPorQr`, método `registrar(Sede $sede, array $datos): array` que por ahora lance `LogicException`), para que las rutas de T010 carguen.
- [X] T013 [P] Crear `laravel_app/database/factories/RegistroMiembroQrFactory.php` (para pruebas de la lista) y agregar `HasFactory` a `RegistroMiembroQr` si falta.
- [X] T014 Ejecutar `php artisan migrate` en una base temporal de pruebas (con `php artisan test --filter=MatrizRutas`) y comprobar que migra, que `php artisan route:list --name=registro` muestra las 8 rutas nuevas y que las 20 pruebas de `RegistroQr` de la funcionalidad 009 siguen en verde (`php artisan test --filter=RegistroQr`).

**Punto de control**: migración aplicada, modelos listos, `buscarEnSede` disponible, gates y rutas registrados, 009 en verde.

---

## Fase 3: Historia de Usuario 1 - Habilitar y compartir el QR de la sede (Prioridad: P1) 🎯 MVP

**Meta**: El administrador (pestaña del detalle de sede) y el pastor (pantalla propia) ven, descargan, cierran, abren y regeneran el QR de miembros de la sede.

**Prueba Independiente**: Abrir la sede, ver y descargar el QR y comprobar que su enlace muestra el formulario de esa sede y no el de otra; cerrar y regenerar.

### Pruebas de HU1 (escribir primero; deben fallar)

- [X] T015 [P] [HU1] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/GestionTest.php`: el administrador ve en `sedes.show` con `?pestana=registro` el QR, el enlace de `registro-sede.show` y los botones «Descargar QR», «Cerrar registro» y «Regenerar enlace»; el pastor ve lo mismo en `registro-miembros.index` solo de su sede; `registro-miembros.svg` descarga un SVG con `Cache-Control: no-store` y el nombre de la sede; `abrir` alterna `registro_abierto`; `regenerar` cambia `registro_token` y el enlace anterior responde 404.
- [X] T016 [P] [HU1] En el mismo archivo agregar la matriz de permisos: un pastor de la sede A recibe 403 en `svg`, `abrir` y `regenerar` de la sede B (por URL manual); un líder de célula y el rol `user` reciben 403 en todas las rutas de gestión; el menú muestra «Registro de miembros» solo al pastor (no al administrador ni al líder); sin sesión redirige al inicio de sesión.
- [X] T017 [P] [HU1] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/CierreTest.php`: con `registro_abierto` falso o con la sede inactiva, `registro-sede.show` muestra «Registro cerrado» y `buscar`/`store` no guardan nada (409 en JSON); con token inexistente o de 32 caracteres inválidos responde 404; un token regenerado deja de funcionar y el nuevo sí.

### Implementación de HU1

- [X] T018 [HU1] Implementar en `laravel_app/app/Http/Controllers/RegistroMiembrosQrController.php`: `index()` (pantalla del pastor con su sede de `SedeContext`, `autorizar('verRegistroMiembros', Sede::class)` y respuesta `no-store`), `svg(GeneradorQr $generador, Sede $sede)` (SVG de 640 px con `Content-Disposition` y `Cache-Control: no-store, private`), `abrir(Sede $sede)` y `regenerar(Sede $sede)`; cada acción con `ensureLoggedIn()` y `Gate::allows('gestionarRegistroMiembros', $sede)` o 403, siguiendo `laravel_app/app/Http/Controllers/RegistroQrController.php`.
- [X] T019 [HU1] Implementar en `laravel_app/app/Http/Controllers/RegistroMiembrosPublicoController.php` la resolución de la sede por token (`Sede::where('registro_token', $token)`, con token de exactamente 32 caracteres, 404 si no existe) y los métodos `show` (formulario paso 1 o vista `registro-miembros.cerrado` si la sede no está abierta), con `Cache-Control: no-store, private`.
- [X] T020 [HU1] Crear `laravel_app/resources/views/partials/panel-registro-miembros.blade.php` basado en `partials/panel-registro-qr.blade.php`: QR con `GeneradorQr`, enlace con botón «Copiar» (`data-copiar`), estado abierto/cerrado, «Descargar QR», «Cerrar/Abrir registro» y «Regenerar enlace» con `x-form.confirm`; recibe `$sede`.
- [X] T021 [P] [HU1] Crear `laravel_app/resources/views/registro-miembros/index.blade.php` (pantalla del pastor, layout `layouts.admin`, título «Registro de miembros», incluye el panel de T020 con la sede del pastor) y `laravel_app/resources/views/registro-miembros/cerrado.blade.php` (layout `layouts.auth`, mensaje «Registro cerrado» con el nombre de la sede, basada en `registro/cerrado.blade.php`).
- [X] T022 [HU1] Agregar a `laravel_app/resources/views/sedes/show.blade.php` y a `SedesController::show` (`laravel_app/app/Http/Controllers/SedesController.php`) la pestaña «Registro de miembros» (`?pestana=registro`, icono `fa-qrcode`) junto a «Datos» y «Organigrama» (si la pestaña «Organigrama» aún no existe en el repositorio, crear las pestañas «Datos» y «Registro de miembros» con el mismo formato `nav nav-tabs` de enlaces), que incluye el panel de T020; la respuesta de esa pestaña lleva `Cache-Control: no-store, private`.
- [X] T023 [HU1] Ejecutar `php artisan test --filter=RegistroMiembrosQr` y dejar T015–T017 en verde.

**Punto de control**: el QR se ve, descarga, cierra, reabre y regenera; permisos correctos para admin, pastor y roles sin acceso.

---

## Fase 4: Historia de Usuario 2 - Registrarse como miembro nuevo (Prioridad: P1)

**Meta**: Una persona escanea el QR, escribe sus datos, elige «Visitante» o «Miembro activo» y queda creada en la sede.

**Prueba Independiente**: Abrir el enlace sin sesión, registrar un celular nuevo con todos los datos y verificar que aparece en miembros con el estado elegido.

### Pruebas de HU2 (escribir primero; deben fallar)

- [X] T024 [P] [HU2] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/ServicioNuevaTest.php` sobre `RegistroMiembrosQr`: crea el miembro en la sede del QR con `estado` igual a `visitante` o `miembro_activo` según `estado_inicial`; guarda `email`, `direccion`, `barrio` y `ciudad` cuando vienen y los deja nulos cuando no; deja `persona_cambios` con acción `creada_qr` (`user_id` nulo) y una fila en `registros_miembros_qr` con resultado `nueva`; un miembro que solo existe en otra sede se crea nuevo sin tocar la otra ficha; dos envíos seguidos con el mismo celular no crean dos miembros.
- [X] T025 [P] [HU2] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/PublicoNuevaTest.php` sobre las rutas: flujo en dos pasos sin JavaScript (`buscar` devuelve el paso 2 vacío, `store` redirige a `registro-sede.listo`); validación con los mensajes junto al campo: `celular` obligatorio con `Celular::regla()` (mínimo 10 dígitos), `nombres` y `apellidos` obligatorios con máx. 100, `fecha_nacimiento` obligatoria, no futura y desde 1900-01-01, `tipo_documento` obligatorio si hay `numero_documento` (máx. 30) y dentro de `Persona::TIPOS_DOCUMENTO`, `estado_inicial` obligatorio y solo `visitante` o `miembro_activo` cuando el miembro no existe, `email` válido con máx. 255, `direccion`, `barrio` y `ciudad` opcionales con máx. 255; cuando faltan datos no se crea nada; las respuestas llevan `Cache-Control: no-store, private`.
- [X] T026 [P] [HU2] Crear `laravel_app/resources/js/forms/registro-miembros.test.js` (Vitest + jsdom): al salir del campo `celular` o `numero_documento` el formulario consulta `data-registro-buscar` y, si la respuesta es `nueva`, no llena nada y muestra los campos del paso de datos; no falla si no hay red; no guarda nada en el navegador.

### Implementación de HU2

- [X] T027 [HU2] Crear `laravel_app/app/Http/Requests/RegistroMiembroQrRequest.php` con las reglas de [data-model.md](data-model.md): `celular` required string max 20 + `Celular::regla()`; `nombres`/`apellidos` required string max 100; `fecha_nacimiento` required date `before_or_equal:today` `after_or_equal:1900-01-01`; `tipo_documento` nullable `required_with:numero_documento` `Rule::in(Persona::TIPOS_DOCUMENTO)`; `numero_documento` nullable string max 30; `estado_inicial` nullable `in:visitante,miembro_activo`; `email` nullable email max 255; `direccion`, `barrio`, `ciudad` nullable string max 255; en `prepareForValidation` convertir a nulo los textos vacíos y recortar espacios; mensajes en español como `RegistroQrRequest`. `estado_inicial` se exige en el servicio solo para miembros nuevos (si falta, error en ese campo).
- [X] T028 [HU2] Implementar en `laravel_app/app/Services/RegistroMiembrosQr.php` la creación: dentro de `SedeContext::conSede($sede->id, …)` y `DB::transaction`, buscar con `RegistroPorQr::buscarEnSede`; si el resultado es `nueva` u `otra sede`, exigir `estado_inicial`, crear `Persona` con `sede_id`, datos normalizados (`normalizarCelular`), registrar `PersonaCambio` con acción `creada_qr` y guardar `RegistroMiembroQr` con resultado `nueva`; devolver `['resultado' => 'nueva', 'persona' => …, 'cambios' => []]`.
- [X] T029 [HU2] Implementar en `RegistroMiembrosPublicoController` los métodos `buscar` (JSON o paso 2 sin JavaScript, usando `buscarEnSede`; otra sede se trata como `nueva` sin revelar nada), `store` (valida con `RegistroMiembroQrRequest`, llama al servicio y redirige a `registro-sede.listo` con el resultado en sesión) y `listo`; todos con `Cache-Control: no-store, private` y errores JSON para las peticiones que lo esperan, siguiendo `laravel_app/app/Http/Controllers/RegistroPublicoController.php`.
- [X] T030 [P] [HU2] Crear `laravel_app/resources/views/registro-miembros/formulario.blade.php` (layout `layouts.auth`): paso 1 con `celular` y `numero_documento` opcional; paso 2 con `x-form.layout`/`x-form.section`/`x-form.input`/`x-form.date`/`x-form.select`: celular, nombres, apellidos, fecha de nacimiento, tipo y número de documento, correo (`type` email), dirección, barrio, ciudad y un grupo de opciones obligatorio «¿Cómo te registras?» con «Visitante» y «Miembro activo»; sin `<form>` escrito a mano; teclado adecuado por campo; nombre de la sede en el encabezado; base `registro/formulario.blade.php`.
- [X] T031 [P] [HU2] Crear `laravel_app/resources/views/registro-miembros/confirmacion.blade.php` con el mensaje según el resultado de sesión (`nueva`: «Listo, quedaste registrado como …»; `actualizada`, `reactivada` y `sin_cambios` se completan en HU3 y HU4), base `registro/confirmacion.blade.php`.
- [X] T032 [HU2] Crear `laravel_app/resources/js/forms/registro-miembros.js` (reutilizando `consultar` de `registro-qr.js`) que, al salir de `celular` o `numero_documento`, consulte el endpoint de búsqueda y deje el formulario listo; registrarlo en `laravel_app/resources/js/forms/index.js` solo para formularios con `data-registro-miembros-buscar`.
- [X] T033 [HU2] Ejecutar `php artisan test --filter=RegistroMiembrosQr` y `npm test`; dejar T024–T026 en verde.

**Punto de control**: una persona nueva se registra con el estado que elige, sin duplicados y sin JavaScript.

---

## Fase 5: Historia de Usuario 3 - Actualizar los datos de un miembro existente (Prioridad: P1)

**Meta**: Si el celular o la cédula existen en la sede, se precargan todos los datos y se actualiza al instante solo lo que cambió, sin borrar nada y con historial.

**Prueba Independiente**: Con un miembro existente, digitar su celular, cambiar dirección y correo, enviar, y comprobar la ficha y el historial.

### Pruebas de HU3 (escribir primero; deben fallar)

- [X] T034 [P] [HU3] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/ServicioActualizarTest.php`: actualiza solo los campos que cambiaron (`nombres`, `apellidos`, `fecha_nacimiento`, `email`, `direccion`, `barrio`, `ciudad`); la comparación ignora mayúsculas y espacios sobrantes (no cuenta como cambio); un campo vacío no borra el dato guardado; solo escribe `tipo_documento` y `numero_documento` si la ficha no tenía cédula; no cambia `celular`, `estado`, `lider_id`, `es_lider`, células, ministerios ni `sede_id`; guarda `actualizada_qr` en `persona_cambios` con `antes` y `despues` de los campos modificados y `RegistroMiembroQr` con `cambios` `{campo: [antes, después]}`; sin cambios devuelve `sin_cambios`, no escribe en la ficha ni en `persona_cambios`; dos `sin_cambios` idénticos en menos de 60 s no crean otra fila en `registros_miembros_qr`; cédula ya usada por otro miembro de la sede se rechaza como conflicto sin escribir.
- [X] T035 [P] [HU3] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/PublicoActualizarTest.php`: `buscar` en JSON devuelve para un miembro existente `estado` `existente` con todos sus datos (`nombres`, `apellidos`, `fecha_nacimiento`, `celular`, `tipo_documento`, `numero_documento`, `email`, `direccion`, `barrio`, `ciudad`; los vacíos se omiten); la búsqueda por cédula también precarga el celular; el paso 2 sin JavaScript viene con los datos; `store` redirige a `listo` y la confirmación lista lo actualizado; sin cambios muestra «tus datos ya estaban al día».
- [X] T036 [P] [HU3] Ampliar `laravel_app/resources/js/forms/registro-miembros.test.js`: con respuesta `existente` precarga todos los campos permitidos (incluidos `tipo_documento`, `numero_documento`, `email`, `direccion`, `barrio`, `ciudad`) sin pisar lo que la persona ya escribió, y muestra el aviso «Encontramos tus datos».

### Implementación de HU3

- [X] T037 [HU3] Implementar en `RegistroMiembrosQr` la actualización: para `existente` calcular los campos enviados no vacíos y distintos (comparación sin mayúsculas ni espacios), aplicar solo esos con `Persona::update`, registrar `PersonaCambio` `actualizada_qr` con `antes`/`despues`, guardar `RegistroMiembroQr` con resultado `actualizada` y `cambios`, o `sin_cambios` sin tocar la ficha ni el historial (con la regla de no duplicar un `sin_cambios` idéntico reciente); respetar la unicidad de cédula por sede (`personas_sede_documento_unique`) tratando el caso como conflicto.
- [X] T038 [HU3] Ampliar `RegistroMiembrosPublicoController::buscar` para devolver, en JSON, todos los campos del miembro existente de la sede (según [contracts/formulario-publico.md](contracts/formulario-publico.md)), y para precargar el paso 2 renderizado sin JavaScript.
- [X] T039 [P] [HU3] Ajustar `registro-miembros/formulario.blade.php` para precargar todos los campos del miembro encontrado (`$persona`) y completar `registro-miembros/confirmacion.blade.php` con los resultados `actualizada` (lista de campos actualizados) y `sin_cambios` («tus datos ya estaban al día»).
- [X] T040 [HU3] Ampliar `laravel_app/resources/js/forms/registro-miembros.js` para precargar todos los campos del JSON.
- [X] T041 [HU3] Ejecutar `php artisan test --filter=RegistroMiembrosQr` y `npm test`; dejar T034–T036 en verde.

**Punto de control**: los miembros actualizan sus datos al instante, sin duplicados ni borrados, con historial reversible.

---

## Fase 6: Historia de Usuario 4 - Casos especiales de identificación (Prioridad: P2)

**Meta**: Resolver sin romper datos: conflicto cédula/celular, celular repetido, inactivo o eliminado (se reactiva), otra sede y límite de intentos.

**Prueba Independiente**: Probar cada caso con datos preparados y comprobar el mensaje y que ninguna ficha ajena cambia.

### Pruebas de HU4 (escribir primero; deben fallar)

- [X] T042 [P] [HU4] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/ServicioCasosEspecialesTest.php`: cédula y celular de miembros distintos → `ValidationException` en `celular` sin crear ni modificar; celular repetido en más de una ficha → mensaje «contacta al administrador» sin escribir; miembro inactivo → se reactiva con el estado que tenía antes de inactivarse (vía `LiderazgoService::reactivar`), se actualizan sus datos, historial `reactivada_qr` y resultado `reactivada`; miembro eliminado (`SoftDeletes`) → `restore()` más reactivación; un miembro de otra sede con el mismo celular no se lee ni se modifica y se crea uno nuevo en la sede del QR; celular con espacios, guiones o prefijo `+57` se normaliza antes de buscar.
- [X] T043 [P] [HU4] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/LimitesTest.php`: la sexta petición (entre `buscar` y `store`) desde la misma IP en un minuto responde 429 con `Retry-After` (JSON si el cliente lo espera; mensaje «espera un minuto» en HTML) y ninguna escritura; tras avanzar el reloj un minuto vuelve a funcionar; la clave es `registro-miembros:{ip}` y el máximo es una única constante (5).
- [X] T044 [P] [HU4] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/AislamientoTest.php`: con dos sedes (`EscenarioDosSedes`), el token de la sede A nunca devuelve ni modifica miembros de la sede B (por celular, por cédula ni por id); el JSON de `buscar` de un miembro solo de otra sede responde como `nueva` sin ningún dato; cambiar o regenerar el token de A no afecta a B; el formulario de A muestra el nombre de A y nunca el de B.

### Implementación de HU4

- [X] T045 [HU4] Implementar en `RegistroMiembrosQr` la reactivación: ante `pendiente` (inactivo o eliminado) restaurar si estaba eliminado, llamar a `LiderazgoService::reactivar` (que devuelve el estado previo a la inactivación), aplicar la actualización de T037, registrar `PersonaCambio` `reactivada_qr` y `RegistroMiembroQr` con resultado `reactivada`; mantener el rechazo de `conflicto` con el mismo mensaje de 009.
- [X] T046 [HU4] Implementar en `RegistroMiembrosPublicoController` el límite con `RateLimiter` (clave `registro-miembros:{ip}`, constante única `INTENTOS_POR_MINUTO = 5`, ventana de 60 s) aplicado a `buscar` y a `store`, y las respuestas 429 con `Retry-After` descritas en [contracts/formulario-publico.md](contracts/formulario-publico.md).
- [X] T047 [P] [HU4] Completar `registro-miembros/confirmacion.blade.php` con el resultado `reactivada` («Bienvenido de nuevo» y los datos actualizados) y mostrar el mensaje de conflicto como aviso junto al campo `celular` en `formulario.blade.php`.
- [X] T048 [HU4] Ejecutar `php artisan test --filter=RegistroMiembrosQr` y `php artisan test --filter=Aislamiento`; dejar T042–T044 en verde.

**Punto de control**: todos los casos dudosos se resuelven con mensajes claros y sin fugas entre sedes.

---

## Fase 7: Historia de Usuario 5 - Saber quién se registró o actualizó por QR (Prioridad: P2)

**Meta**: El administrador y el pastor ven la lista de lo ocurrido por el QR, con el detalle de los cambios, y los creados por QR se distinguen en el historial.

**Prueba Independiente**: Registrar una persona nueva y actualizar una existente por QR y comprobar que ambas aparecen en la lista con fecha y detalle.

### Pruebas de HU5 (escribir primero; deben fallar)

- [X] T049 [P] [HU5] Crear `laravel_app/tests/Feature/RegistroMiembrosQr/ListaTest.php`: el panel del administrador (pestaña) y la pantalla del pastor listan los registros de su sede, más recientes primero, con nombre (enlace a la ficha cuando existe), celular, resultado (nueva, actualizada, reactivada, sin cambios), fecha y hora y los cambios como «campo: antes → después»; vacío muestra «Todavía nadie se ha registrado»; el pastor de la sede A nunca ve registros de la B; las entradas `creada_qr`, `actualizada_qr` y `reactivada_qr` aparecen con su etiqueta en el historial de la ficha del miembro.

### Implementación de HU5

- [X] T050 [HU5] Crear `laravel_app/resources/views/partials/lista-registros-miembros.blade.php` (tabla `tabla-apilable` con `data-etiqueta` como `partials/lista-registros-qr.blade.php`, insignias por resultado con `x-ui.insignia`, columna de cambios) e incluirla al final de `partials/panel-registro-miembros.blade.php` con los registros de la sede (`$sede->registrosMiembrosQr()->with('persona')->get()`).
- [X] T051 [HU5] Verificar y, si falta, mostrar las nuevas acciones de `PersonaCambio` en el historial de la ficha del miembro (`laravel_app/resources/views/personas/show.blade.php`) usando `PersonaCambio::etiqueta()`; el origen «QR de miembros» debe verse en cada entrada.
- [X] T052 [HU5] Ejecutar `php artisan test --filter=RegistroMiembrosQr` y dejar T049 en verde.

**Punto de control**: las cinco historias funcionan de forma independiente.

---

## Fase 8: Pulido y asuntos transversales

**Propósito**: Cierre según el flujo de desarrollo de la constitución.

- [X] T053 [P] Agregar a `laravel_app/tests/movil/pantallas.mjs` las pantallas con sesión `registro-miembros` (pastor/admin según el usuario de prueba; si el administrador no puede abrirla, usar `sedes/1?pestana=registro`) y la pública `registro-sede/{TOKEN_SEDE}`, y fijar el token de la sede 1 en `laravel_app/tests/movil/servidor.mjs` como se hace con `TOKEN_REGISTRO`.
- [X] T054 [P] Crear `laravel_app/tests/movil/registro-miembros.spec.js` (Playwright, estilo de `tablero.spec.js`): el formulario público a 320–430 px sin desplazamiento horizontal de la página, controles ≥ 44 × 44 px, letra ≥ 16 px y teclado adecuado por campo; el paso 2 es completable con el teclado en pantalla; el panel del QR sin desplazamiento horizontal y con la tabla apilada como tarjetas.
- [X] T055 [P] Verificar que el service worker (`laravel_app/public/sw.js`) no guarda `/registro-sede/*` ni `/registro-miembros` (solo se guardan `/build/assets` e `/iconos`) y agregar la comprobación a `laravel_app/tests/Feature/RegistroMiembrosQr/PublicoNuevaTest.php` (cabecera `no-store, private` en todas las respuestas del formulario).
- [X] T056 [P] Documentar en `laravel_app/README.md` la sección «Registro de miembros por QR» (qué hace, quién lo gestiona, qué actualiza y qué nunca toca, límite de intentos, riesgo de privacidad aceptado y cómo revertir un cambio desde el historial).
- [X] T057 Ejecutar la suite completa: `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` (una sola corrida), todos en verde y con `laravel_app/tests/movil/excepciones.json` vacío.
- [ ] T058 Recorrer manualmente `specs/012-registro-miembros-qr/quickstart.md` en el navegador (escritorio y 320 px, con y sin JavaScript) y corregir lo que falle.

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias.
- **Fundamentos (Fase 2)**: depende de la Fase 1; bloquea todas las historias. T007 y T008 pueden hacerse en paralelo con T003–T006 salvo que T014 espera a todas.
- **HU1 (Fase 3)**: depende de la Fase 2. Es el MVP de gestión.
- **HU2 (Fase 4)**: depende de la Fase 2 y de T019 (resolución del token) de HU1.
- **HU3 (Fase 5)**: depende de HU2 (necesita el servicio y el formulario).
- **HU4 (Fase 6)**: depende de HU3 (extiende el servicio y el controlador público).
- **HU5 (Fase 7)**: depende de HU1 (panel) y de HU3/HU4 para tener los resultados que lista; las pruebas pueden escribirse antes con la fábrica de T013.
- **Pulido (Fase 8)**: depende de las historias incluidas.

### Dentro de cada historia

Pruebas primero (deben fallar), luego servicio, controlador, vistas y JS, y al final la verificación de la historia.

### Oportunidades de paralelismo

- Fase 2: T004, T005, T006 y T013 en paralelo (archivos distintos).
- HU1: T015, T016 y T017 en paralelo (T015 y T016 comparten archivo: hacerlas seguidas); T021 en paralelo con T018–T020.
- HU2: T024, T025 y T026 en paralelo; T030 y T031 en paralelo con T027–T029.
- HU3: T034, T035 y T036 en paralelo; T039 en paralelo con T037–T038.
- HU4: T042, T043 y T044 en paralelo; HU5 puede avanzar en paralelo con HU4 (archivos distintos).
- Pulido: T053, T054, T055 y T056 en paralelo.

### Ejemplo de paralelismo (HU2)

```text
Tarea: "Pruebas del servicio (nuevo miembro)" (T024)
Tarea: "Pruebas del formulario público" (T025)
Tarea: "Pruebas JS de la precarga" (T026)
```

---

## Estrategia de implementación

### MVP primero (HU1 + HU2)

1. Completar Fase 1 y Fase 2.
2. Completar HU1 (QR gestionable) y HU2 (registro de miembros nuevos).
3. **Parar y validar**: escanear el QR, registrarse como nuevo y verlo en el listado de miembros.

### Entrega incremental

1. Fundamentos + HU1 → QR visible y gestionable por administrador y pastor.
2. + HU2 → registro de miembros nuevos (MVP).
3. + HU3 → actualización de miembros existentes.
4. + HU4 → casos especiales, reactivación y límite de intentos.
5. + HU5 → lista de registros e historial visible.
6. Pulido y verificación final.

> Nota de seguridad: HU3 (actualización inmediata y precarga completa de datos) concentra el riesgo de privacidad aceptado por el responsable. No se debe publicar HU3 sin HU4 (límite de intentos) y sin el historial de HU3 funcionando.

---

## Notas

- `[P]` = archivos distintos, sin dependencias pendientes.
- La etiqueta [HU] relaciona cada tarea con su historia para dar trazabilidad.
- Los commits solo se hacen cuando el responsable del proyecto lo pida.
