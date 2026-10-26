---

description: "Lista de tareas de la funcionalidad 016: todo usuario es un miembro y el pastor encabeza el liderazgo"
---

# Tareas: Todo usuario es un miembro y el pastor encabeza el liderazgo

**Entrada**: documentos de diseño en `/specs/016-usuario-es-miembro/`

**Requisitos previos**: plan.md, spec.md, research.md, data-model.md, contracts/rutas-y-reglas.md, quickstart.md

**Pruebas**: se incluyen (constitución, principio IV). Cada prueba se escribe **antes** de la implementación que verifica y debe fallar primero.

**Organización**: por historia de usuario. Todas las rutas son relativas a `laravel_app/`. Los miembros son el modelo `Persona`; las cuentas, `User`. Decisiones de referencia: research.md D1–D14.

**Regla de edición** (memoria del proyecto): mucho código de `laravel_app/` está sin commit. Los archivos existentes se modifican **solo con la herramienta Edit**, nunca con `sed`, `php -r` ni scripts de reemplazo.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes).
- **[US1..US6]**: historia de usuario a la que pertenece (spec.md).

---

## Fase 1: Preparación

- [X] T001 Confirmar la línea base: `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` en verde. Anotar el resultado y, de la base local, el total de cuentas, las cuentas con `persona_id` nulo y los líderes con `lider_id` nulo en `specs/016-usuario-es-miembro/quickstart.md` (sección nueva «Línea base»).
- [X] T002 [P] Crear los directorios `tests/Feature/CuentasDeMiembros/` y `app/Console/Commands/` (si no existe).

---

## Fase 2: Fundamentos (bloquea todas las historias)

**Propósito**: celular normalizado, columnas nuevas, relación cuenta ↔ miembro de solo lectura, fábricas que siempre crean el miembro y la regla de «pastor de la sede».

### Pruebas

- [X] T003 [P] Prueba unitaria de `Celular::clave()` en `tests/Unit/CelularClaveTest.php`. Casos: `"300 123 4567"`, `"300-123-4567"`, `"+57 300 123 4567"` y `"573001234567"` dan `"3001234567"`; un número de 11 dígitos queda igual; vacío o sin dígitos da `null`.
- [X] T004 [P] Prueba de la migración 1 en `tests/Feature/CuentasDeMiembros/MigracionColumnasTest.php`:
  - existe `personas.celular_clave` (string(15), nulo, **con índice**);
  - existe `users.revision_vinculo` (json, nulo);
  - `users.name` pasa a ser nulo;
  - los miembros existentes quedan con `celular_clave` relleno.
- [X] T005 [P] Prueba del modelo en `tests/Feature/CuentasDeMiembros/ModeloCuentaMiembroTest.php`:
  - `Persona` calcula `celular_clave` al guardar;
  - `Persona::cuenta()` devuelve la cuenta;
  - `Persona::esPastoral()` es verdadero para el pastor y la pastora de su sede;
  - `User::persona()` encuentra el miembro aunque el contexto de sede activo sea otro (administrador navegando otra sede);
  - `User::factory()->create()` siempre deja `persona_id` con un miembro de la sede de la cuenta (administrador: sede por defecto).

### Implementación

- [X] T006 Agregar `public static function clave(?string $celular): ?string` en `app/Support/Celular.php`: dígitos de `limpio()`; si son 12 y empiezan por `57`, los últimos 10 (research D3).
- [X] T007 Crear la migración `database/migrations/2026_11_01_000001_add_celular_clave_y_revision_vinculo.php` (data-model, migración 1):
  - `personas.celular_clave` string(15) nulo con índice;
  - `users.revision_vinculo` json nulo;
  - `users.name` pasa a nulo;
  - rellenar `celular_clave` con `Celular::clave(celular)` por lotes (`chunkById`, sin filtros globales).
- [X] T008 Modificar `app/Models/Persona.php`:
  - evento `saving` que calcula `celular_clave`;
  - relación `cuenta(): HasOne` a `User` sin filtros globales;
  - método `esPastoral(): bool`, que compara con `sede->pastor_persona_id` y `sede->pastora_persona_id`.
- [X] T009 Modificar `app/Models/User.php`:
  - `persona()` se lee sin filtros globales (`->withoutGlobalScopes()`, como `Sede::pastor()`);
  - agregar `protected $with = ['persona']` y `revision_vinculo` en `Fillable` y en `casts` (`array`).

  Los accesores `name` y `celular` se hacen en US3 (T031).
- [X] T010 [P] Modificar `database/factories/UserFactory.php`:
  - `persona_id` crea un `Persona::factory()` en la sede de la cuenta (administrador: `SedeFactory::idPorDefecto()`);
  - el miembro recibe `nombres`, `apellidos` y `celular` derivados de `name` y `celular` si vienen;
  - agregar el estado `conMiembro(array $datos)`.
- [X] T011 [P] Agregar el estado `pastorDe(Sede $sede)` en `database/factories/PersonaFactory.php`: `es_lider = true`, `lider_id = null` y actualiza `sedes.pastor_persona_id`.
- [X] T012 Agregar `liderPorDefecto(Persona $persona): Persona` en `app/Services/LiderazgoService.php`: devuelve el pastor de la sede del miembro o lanza `ValidationException` en `lider_id` con «Primero asigna el pastor de la sede.» (research D9).
- [X] T013 Ejecutar `php artisan test` y corregir las pruebas existentes que fallen por la fábrica (unos 30 usos de `User::factory` en 22 archivos y los 11 lugares con `'name' =>`). Pasar `name`/`celular` al miembro con `conMiembro()`, editando cada archivo con Edit.

**Punto de control**: fundamentos listos y suite en verde. Las historias pueden empezar.

---

## Fase 3: Historia de Usuario 1 - Crear un usuario a partir de un miembro existente (Prioridad: P1) 🎯 MVP

**Objetivo**: «Nuevo usuario» pide el celular, encuentra al miembro y vincula la cuenta sin volver a escribir el nombre.

**Prueba independiente**: con el miembro «Milton Pérez» (3001234567), crear un usuario con «+57 300 123 4567». La cuenta queda ligada a Milton y no se crea otro miembro.

### Pruebas

- [X] T014 [P] [US1] Prueba de `CuentasDeMiembros::buscarPorCelular()` en `tests/Feature/CuentasDeMiembros/BuscarMiembroPorCelularTest.php`. Estados:
  - `encontrado` (un miembro visible sin cuenta);
  - `varios` (dos miembros con el mismo `celular_clave`);
  - `con_cuenta`;
  - `inactivo`;
  - `otra_sede` (existe solo en otra sede);
  - `nuevo` (no existe);
  - `invalido` (falla `Celular::regla()`).

  Además, la normalización encuentra al mismo miembro con espacios, guiones y +57.
- [X] T015 [P] [US1] Prueba HTTP del flujo en `tests/Feature/CuentasDeMiembros/CrearUsuarioConMiembroTest.php` (parte «existente»):
  - `GET users/create` muestra solo el campo celular (y la sede para el administrador);
  - `GET users/create?celular=…` muestra la tarjeta con el nombre;
  - `POST users` con `persona_id`, `email` y `role` crea la cuenta vinculada sin crear un miembro;
  - con `persona_id` de un miembro con cuenta, inactivo, de otro celular o fuera del alcance: error en `persona_id` y nada creado;
  - un segundo `GET` con el mismo celular muestra «ya tiene una cuenta» con enlace a `users.edit` y sin formulario.
- [X] T016 [P] [US1] Prueba de aislamiento en `tests/Feature/CuentasDeMiembros/CuentasDeMiembrosAislamientoTest.php`:
  - un pastor de la sede A busca el celular de un miembro de la sede B; la respuesta contiene el aviso «Ese celular pertenece a un miembro de otra sede; solicita su traslado» y **no** contiene el nombre, los apellidos ni el nombre de la sede B;
  - un `POST` con `persona_id` de la sede B se rechaza.

### Implementación

- [X] T017 [US1] Crear `app/Services/CuentasDeMiembros.php` con `buscarPorCelular(string $celular, User $actor, ?int $sedeId = null): array`, que devuelve `['estado' => …, 'personas' => Collection]` (contracts/rutas-y-reglas.md):
  - busca por `celular_clave` dentro de `AlcanceService::personasVisibles($actor)` en el contexto de sede (`SedeContext::conSede()` si el administrador eligió sede);
  - para `otra_sede`, solo hace `exists()` sin filtro de sede (research D8).
- [X] T018 [US1] Agregar a `app/Services/CuentasDeMiembros.php` el método `crear(array $cuenta, ?Persona $persona, ?array $miembro, User $actor): User`, en `DB::transaction` (esta historia: rama con `$persona`):
  - valida que la persona sea visible, no tenga cuenta y no esté inactiva;
  - crea `User` con `email`, `role`, `password` aleatorio, `persona_id` y `sede_id` (administrador: `null`; otros: `persona.sede_id`);
  - completa `personas.email` con el de la cuenta si el miembro no tiene correo (research D2).
- [X] T019 [P] [US1] Crear el componente `resources/views/components/form/radio.blade.php`:
  - props `name`, `label`, `opciones` (`[valor => ['texto', 'apoyo']]`), `value`, `required`, `ayuda`;
  - cada opción es una tarjeta tocable de al menos 44 px y con letra de 16 px;
  - mismo patrón de etiqueta y error que `x-form.select` (`_etiqueta`, `Formulario::id`).
- [X] T020 [US1] Reescribir `create()` en `app/Http/Controllers/UsersController.php`:
  - sin `celular`: paso 1;
  - con `celular`: llama a `buscarPorCelular()` y pasa `$busqueda` a la vista;
  - el administrador envía `sede_id`.
- [X] T021 [US1] Reescribir `store()` en `app/Http/Controllers/UsersController.php`:
  - reglas: `celular` obligatorio con `Celular::regla()`; `persona_id` obligatorio sin `miembro`; `email` obligatorio, correo y único; `role` en `rolesAsignables`; `sede_id` obligatorio para el administrador y prohibido para los demás;
  - **no** acepta `name`;
  - delega en `CuentasDeMiembros::crear()` y luego llama a `mostrarClave()` como hoy.
- [X] T022 [US1] Reescribir `resources/views/users/create.blade.php`:
  - paso 1: formulario `GET` con `x-form.input` celular, `type="tel"`, `inputmode="tel"` (y `x-form.select` de sede para el administrador);
  - paso 2, según `$busqueda['estado']`: tarjeta del miembro + `persona_id` oculto, `x-form.radio` para `varios`, o los avisos de `con_cuenta`, `inactivo` (enlace a la ficha), `otra_sede` e `invalido`;
  - enlace «Cambiar celular» que vuelve al paso 1.
- [X] T023 [US1] Reducir `resources/views/users/_form.blade.php` a los campos de cuenta (`email`, `role`). Quitar `name`, `celular` y el desplegable de miembro de `create`, y quitar `personas` de `datosFormulario()` en `app/Http/Controllers/UsersController.php`.

**Punto de control**: US1 se prueba sola (quickstart §2).

---

## Fase 4: Historia de Usuario 2 - Crear el miembro al crear el usuario (Prioridad: P1)

**Objetivo**: si el celular no existe, se crean el miembro y la cuenta en una sola operación.

**Prueba independiente**: crear un usuario con un celular inexistente. Aparece el miembro en «Miembros» y la cuenta está vinculada; si falta un dato, no se crea nada.

### Pruebas

- [X] T024 [P] [US2] Agregar la parte «nuevo» a `tests/Feature/CuentasDeMiembros/CrearUsuarioConMiembroTest.php`:
  - `GET users/create?celular=<inexistente>` muestra los campos `miembro[...]`;
  - `POST` completo crea el miembro (con `celular` = el buscado, `email` = el de la cuenta, `estado` `miembro_activo`) y la cuenta vinculada;
  - sin `miembro[apellidos]`: error en ese campo y **ni miembro ni cuenta** creados;
  - si la cuenta falla (correo repetido) tampoco queda el miembro;
  - el administrador con `sede_id` crea el miembro en esa sede y la cuenta con `sede_id = null` si el rol es administrador.

### Implementación

- [X] T025 [US2] Extraer de `app/Http/Requests/PersonaRequest.php` un método estático `reglasDeAlta(string $prefijo = ''): array` con las reglas obligatorias del alta de miembro, reutilizable con el prefijo `miembro.`. `rules()` lo usa sin cambiar su comportamiento.
- [X] T026 [US2] Completar en `app/Services/CuentasDeMiembros.php` la rama de `crear()` con `$miembro`:
  - en la misma transacción, crea `Persona` en la sede elegida (`SedeContext::conSede()`), con el celular buscado, el correo de la cuenta y `estado = 'miembro_activo'`;
  - luego crea la cuenta vinculada.
- [X] T027 [US2] Validar `miembro.*` con `PersonaRequest::reglasDeAlta('miembro.')` cuando no viene `persona_id`, en `store()` de `app/Http/Controllers/UsersController.php`.
- [X] T028 [US2] En el estado `nuevo` de `resources/views/users/create.blade.php`, mostrar los campos obligatorios del miembro con `x-form.input` y `x-form.select` (`miembro[nombres]`, `miembro[apellidos]` y los demás obligatorios). El celular se muestra fijo con el valor buscado.

**Punto de control**: US1 y US2 juntas cubren todo el alta (quickstart §3).

---

## Fase 5: Historia de Usuario 3 - Un solo nombre y un solo celular para la persona (Prioridad: P1)

**Objetivo**: el nombre y el celular de la cuenta salen siempre del miembro; la cuenta solo edita lo suyo.

**Prueba independiente**: cambiar el nombre del miembro «Milton Pérez». La grilla de usuarios y el encabezado muestran el nombre nuevo sin editar la cuenta.

### Pruebas

- [X] T029 [P] [US3] Prueba en `tests/Feature/CuentasDeMiembros/UnaSolaPersonaTest.php`:
  - `$user->name` = `persona->nombre_completo` y `$user->celular` = `persona->celular`; tras cambiar el miembro, la grilla `users.datos`, `layouts.admin` (encabezado) y el mensaje de `ClavesTemporales` usan los datos nuevos;
  - `users.edit` no tiene campos `name`, `celular` ni `persona_id` y sí el enlace a `personas.show`;
  - `PUT users/{id}` ignora `name`, `celular` y `persona_id`;
  - guardar un miembro con cuenta y con el `celular_clave` de otro miembro con cuenta da el error «Ese celular ya es el de la cuenta de otro miembro.»; un miembro con cuenta sin celular da error;
  - `DELETE personas/{id}` de un miembro con cuenta se niega y la ficha no muestra el botón;
  - la grilla de usuarios ordena y filtra por el nombre del miembro;
  - `SolicitudesClaveGrilla` busca por el nombre del miembro.
- [X] T030 [P] [US3] Prueba en `tests/Feature/CuentasDeMiembros/TrasladoConCuentaTest.php`: al trasladar un miembro con cuenta, la cuenta pasa al miembro nuevo (`persona_id`) y a la sede destino (`sede_id`) en la misma operación; si el traslado falla, la cuenta no cambia.

### Implementación

- [X] T031 [US3] Agregar en `app/Models/User.php` los atributos de solo lectura `name` (`persona?->nombre_completo`) y `celular` (`persona?->celular`), con `Attribute::get`. Quitar `name` y `celular` de `Fillable`.
- [X] T032 [US3] Reescribir `edit()` y `update()` en `app/Http/Controllers/UsersController.php`:
  - se validan y guardan solo `email` y `role`, con la regla del único administrador;
  - en `resources/views/users/edit.blade.php`, tarjeta del miembro vinculado con enlace «Editar datos personales» a `personas.show`.
- [X] T033 [US3] Agregar en `app/Http/Requests/PersonaRequest.php`, si el miembro editado tiene cuenta:
  - `celular` obligatorio;
  - regla que rechaza un `Celular::clave(celular)` igual al `celular_clave` de otro miembro con cuenta, con el mensaje «Ese celular ya es el de la cuenta de otro miembro.» (RF-009).
- [X] T034 [US3] En `app/Policies/PersonaPolicy.php`, negar la eliminación de un miembro con `cuenta`. En `resources/views/personas/show.blade.php`, ocultar el botón «Eliminar» y mostrar «Tiene cuenta de usuario; elimina primero la cuenta.» (RF-010).
- [X] T035 [P] [US3] En `app/Grillas/UsuariosGrilla.php`, la columna «Nombre»:
  - ordena con una subconsulta `Persona::withoutGlobalScopes()->select(apellidos || ' ' || nombres)` sobre `personas.id = users.persona_id`;
  - filtra y busca con `whereHas('persona', …)` sobre `nombres`/`apellidos` (sin filtros globales);
  - `valor` = `$u->name`.
- [X] T036 [P] [US3] Cambiar en `app/Grillas/SolicitudesClaveGrilla.php` el orden y la búsqueda por `users.name` a la subconsulta y el `whereHas` sobre el miembro, como en T035.
- [X] T037 [P] [US3] Cambiar `orderBy('name')` por el orden según el nombre del miembro en `app/Http/Controllers/SedesController.php` (líneas 85 y 128) y en `app/Http/Controllers/RolesController.php` (línea 134).
- [X] T038 [US3] En `trasladar()` de `app/Services/TrasladoService.php`, dentro de la transacción: si el miembro origen tiene cuenta, `CuentasDeMiembros::vincular($cuenta, $nueva)` y `sede_id` = destino. Agregar `vincular(User, Persona, ?User $por)` en `app/Services/CuentasDeMiembros.php`; si había `revision_vinculo`, lo limpia (research D13).

**Punto de control**: US3 se prueba sola (quickstart §4).

---

## Fase 6: Historia de Usuario 5 - Todo líder nuevo queda bajo el pastor de la sede (Prioridad: P1)

**Objetivo**: al crear o designar un líder, su líder es el pastor; ningún líder queda sin líder; el pastor y la pastora no tienen líder.

**Prueba independiente**: designar como líder a un miembro que tenía otro líder. Queda bajo el pastor y el historial lo muestra.

### Pruebas

- [X] T039 [P] [US5] Prueba en `tests/Feature/Lideres/PastorCabezaTest.php`:
  - `POST lideres/designar` deja `lider_id` = pastor **aunque tuviera otro líder**, con entradas de historial «rol_lider» y «lider», y el mensaje «{nombre} ahora es líder, bajo el pastor {pastor}.»;
  - `POST personas` con `es_lider` deja `lider_id` = pastor;
  - `PUT personas/{id}` de un no líder a líder lo pone bajo el pastor;
  - `PUT` que deja sin `lider_id` a un líder (no pastoral) da el error «Todo líder depende del pastor o de otro líder.»;
  - `PUT` que asigna líder al pastor o a la pastora da el error «El pastor es la cabeza de la sede y no tiene líder.»;
  - en una sede sin pastor, designar o crear un líder da «Primero asigna el pastor de la sede.» y nada cambia;
  - un líder se puede mover bajo otro líder (no el pastor) sin ciclos.
- [X] T040 [P] [US5] Prueba en `tests/Feature/Lideres/InvariantesLiderazgoTest.php`, sobre el `DatabaseSeeder` y sobre una sede armada con fábricas tras ejecutar la migración 3:
  - todo `es_lider` no pastoral tiene `lider_id`;
  - el pastor y la pastora tienen `lider_id` nulo y `es_lider` verdadero;
  - no hay ciclos;
  - la migración 3 es idempotente y registra en el historial con actor `sistema`.
- [X] T041 [P] [US5] Agregar a `tests/Feature/Organigrama/` (archivo existente del organigrama o `PastorCabezaOrganigramaTest.php`): con todos los líderes bajo el pastor, el organigrama muestra a esos líderes como primer nivel bajo la cabecera de la sede y no hay líderes sueltos.

### Implementación

- [X] T042 [US5] Modificar `app/Services/LiderazgoService.php`:
  - `designarLider()`: en la misma transacción, si la persona no es pastoral, `cambiarLider($persona, liderPorDefecto($persona), $por)`; si es pastoral, `lider_id = null`;
  - `validarLider()`: rechaza asignar líder a una persona pastoral;
  - `cambiarLider($persona, null)`: lanza la validación si `$persona->es_lider` y no es pastoral.
- [X] T043 [US5] En `app/Http/Requests/PersonaRequest.php`:
  - si `es_lider` y el miembro no es pastoral: al pasar a líder (no lo era o es alta), `lider_id` se fuerza al pastor de la sede (`liderPorDefecto`); si ya era líder, `lider_id` es obligatorio;
  - si es pastoral, `lider_id` debe ser nulo.

  Mensajes exactos de contracts/rutas-y-reglas.md.
- [X] T044 [US5] Ajustar `app/Http/Controllers/PersonasController.php` (alta con `es_lider`, línea ~362; edición, línea ~253) para que el paso a líder use `LiderazgoService::designarLider()` en lugar de actualizar `es_lider` directamente, de modo que se aplique el pastor.
- [X] T045 [US5] En `storeDesignar()` de `app/Http/Controllers/LideresController.php`:
  - mensaje de éxito «{nombre} ahora es líder, bajo el pastor {pastor}.»;
  - la `ValidationException` de sede sin pastor se devuelve en `persona_id`.
- [X] T046 [US5] Crear la migración de datos `database/migrations/2026_11_01_000003_pastor_cabeza_del_liderazgo.php` (research D11). Por cada sede con pastor:
  - pastor y pastora: `es_lider = true`, `lider_id = null`;
  - todo líder no pastoral sin `lider_id`: `lider_id` = pastor;
  - cada cambio se registra con `Historial::comoActor('sistema', …)`;
  - idempotente; sin filtros globales; sedes sin pastor omitidas.
- [X] T047 [US5] Cambiar en `app/Services/OrganigramaService.php` las raíces de líderes: pasan a ser los líderes cuyo `lider_id` es el pastor o la pastora, excluyendo a ambos, que siguen en la cabecera (research D12).
- [X] T048 [P] [US5] Hacer que `app/Services/SedeService.php` (`crearCuentaPastoral`) cree al pastor y a la pastora con `es_lider = true` y `lider_id = null`. En `database/seeders/DatabaseSeeder.php`, dejar a los líderes sembrados bajo el pastor de su sede.

**Punto de control**: las cuatro historias P1 están completas (quickstart §5, pasos 1–4 y 6).

---

## Fase 7: Historia de Usuario 4 - Unificar las cuentas existentes con sus miembros (Prioridad: P2)

**Objetivo**: ninguna cuenta queda sin miembro; los casos dudosos quedan a la vista del administrador. Al final se eliminan `users.name` y `users.celular`.

**Prueba independiente**: una cuenta «Milton» sin vínculo y un miembro «Milton» con el mismo celular en la misma sede quedan vinculados por la unificación, sin duplicado.

### Pruebas

- [X] T049 [P] [US4] Prueba de `CuentasDeMiembros::unificar()` en `tests/Feature/CuentasDeMiembros/UnificarUsuariosTest.php`. Casos (crear las cuentas sin miembro con `DB::table('users')->insert`):
  - una coincidencia con palabra común → vinculada;
  - sin coincidencia → miembro creado con nombre (primera palabra = `nombres`, resto = `apellidos` o «-»), celular y correo, y vinculada;
  - varias coincidencias → miembro creado y `revision_vinculo` = `{"candidatos": [...], "motivo": "varios", "creado_automatico": id}`;
  - nombre sin palabras en común → igual, con `motivo` = `nombre_distinto`;
  - administrador → busca en todas las sedes y, sin coincidencia, crea el miembro en la sede de menor `id`;
  - cuenta ya vinculada con nombre distinto → prevalece el miembro y queda una entrada de historial;
  - segunda ejecución → sin cambios;
  - el resumen devuelve `vinculadas`, `creadas`, `por_revisar` y `sedes_sin_pastor`.
- [X] T050 [P] [US4] Prueba en `tests/Feature/CuentasDeMiembros/RevisionVinculoTest.php`:
  - el filtro «Vínculo: Por revisar» de `users.datos` solo existe para el administrador y lista solo cuentas con `revision_vinculo`;
  - `PUT users/{id}` con `vincular_a` = candidato vincula y limpia `revision_vinculo`;
  - el miembro creado automáticamente sin datos asociados queda eliminado (borrado lógico); con datos asociados se conserva y se avisa;
  - un pastor no ve el campo ni puede enviarlo.
- [X] T051 [P] [US4] Prueba de la migración 4 en `tests/Feature/CuentasDeMiembros/MigracionPersonaObligatoriaTest.php`:
  - con alguna cuenta sin miembro, la migración se detiene con un mensaje claro;
  - sin ninguna, elimina `users.name` y `users.celular`, y `persona_id` queda no nulo y único;
  - `down()` recrea las columnas rellenas desde el miembro.
- [X] T052 [P] [US4] Prueba del comando `miembros:unificar-usuarios` en `tests/Feature/CuentasDeMiembros/ComandoUnificarTest.php`: imprime el resumen y la tabla de cuentas por revisar (correo, sede, motivo, candidatos) y devuelve 0.

### Implementación

- [X] T053 [US4] Implementar `unificar(): array` en `app/Services/CuentasDeMiembros.php` según research D7:
  - `chunkById` sin filtros globales;
  - comparación de nombres sin tildes ni mayúsculas, por palabras;
  - crea los miembros con `SedeContext::conSede()`;
  - registra en el historial con `Historial::comoActor('sistema', …)`;
  - idempotente.
- [X] T054 [US4] Crear la migración de datos `database/migrations/2026_11_01_000002_unificar_usuarios_con_miembros.php`, que llama a `app(CuentasDeMiembros::class)->unificar()` y no hace nada en `down()`.
- [X] T055 [US4] Crear el comando `app/Console/Commands/UnificarUsuarios.php` (`miembros:unificar-usuarios`): ejecuta `unificar()` e imprime el resumen y la tabla de cuentas por revisar.
- [X] T056 [US4] En `app/Grillas/UsuariosGrilla.php`, agregar la columna o filtro «Vínculo» (`Todos | Por revisar`, `whereNotNull('revision_vinculo')`), visible solo si el actor es administrador.
- [X] T057 [US4] En `update()` y `edit()` de `app/Http/Controllers/UsersController.php` y en `resources/views/users/edit.blade.php`:
  - si el actor es administrador y `revision_vinculo` no es nulo, mostrar `x-form.radio` `vincular_a` con los candidatos (nombre, celular, sede);
  - al guardar, llamar a `CuentasDeMiembros::vincular()`;
  - después, eliminar (borrado lógico) el miembro de `creado_automatico` si no tiene células, inscripciones, avances, miembros a cargo ni historial propio fuera de su alta; si los tiene, avisar con `with('warning', …)`.
- [X] T058 [US4] Crear la migración `database/migrations/2026_11_01_000004_users_persona_obligatoria.php` (data-model, migración 4):
  - comprobar que no queda ninguna cuenta con `persona_id` nulo; si queda alguna, lanzar una excepción con el conteo;
  - eliminar `users.name` y `users.celular`;
  - `persona_id` pasa a no nulo, con índice único y FK con `restrictOnDelete`;
  - `down()` recrea `name` (nulo) y `celular` string(20) nulo, los rellena desde `personas`, y vuelve a poner `persona_id` nulo con `nullOnDelete`.
- [X] T059 [US4] Buscar con Grep en `app/`, `resources/views/` y `database/` cualquier uso restante de la columna (`users.name`, `users.celular`, `'name' =>` al crear usuarios, `->update(['name'`). Corregirlos con Edit, incluidos `app/Services/SedeService.php` (`crearCuentaPastoral`, `vincularPersona` → `CuentasDeMiembros`) y `database/seeders/DatabaseSeeder.php`.

**Punto de control**: quickstart §1; ninguna cuenta sin miembro.

---

## Fase 8: Historia de Usuario 6 - Al cambiar el pastor de la sede se mantiene la cabeza (Prioridad: P2)

**Objetivo**: el nuevo pastor queda sin líder, sus dependientes pasan a él y el anterior queda bajo él.

**Prueba independiente**: con tres líderes bajo el pastor «Juan», cambiar el pastor a «Pedro». Los tres quedan bajo Pedro y Pedro no tiene líder.

### Pruebas

- [X] T060 [P] [US6] Prueba en `tests/Feature/Lideres/CambioDePastorTest.php`:
  - al cambiar el **pastor**: los dependientes directos del anterior pasan al nuevo **sin** ser retirados de sus células; el nuevo pastor tiene `lider_id` nulo y `es_lider` verdadero; el anterior queda `es_lider` verdadero bajo el nuevo y su cuenta pasa a `lider_celula`; cada cambio aparece en el historial;
  - al cambiar la **pastora**: la nueva queda sin líder, nadie se mueve y la anterior queda bajo el pastor;
  - si el nuevo pastor era dependiente del anterior, no se forma ningún ciclo.

### Implementación

- [X] T061 [US6] Agregar `reasignarDependientes(Persona $de, Persona $a, ?User $por = null): int` en `app/Services/LiderazgoService.php`: actualización por lotes de `lider_id`, excluyendo a `$a`, con una entrada de historial por persona y sin tocar las células (research D10).
- [X] T062 [US6] Modificar `cambiarPastor()` en `app/Services/SedeService.php`, dentro de la transacción:
  - el nuevo pastor o pastora: `lider_id = null` y `es_lider = true`;
  - si `$rol === 'pastor'` y hay anterior: `reasignarDependientes(anterior, nuevo)` y luego el anterior queda con `lider_id` = nuevo;
  - si `$rol === 'pastora'` y hay anterior: el anterior queda con `lider_id` = pastor de la sede.

  Se conserva el cambio de rol de la cuenta anterior.

**Punto de control**: quickstart §5, paso 5.

---

## Fase 9: Pulido y aspectos transversales

- [X] T063 [P] Agregar `users/create?celular=<miembro sembrado>` (estado encontrado) y `users/create?celular=3990000000` (estado nuevo) a `tests/movil/pantallas.mjs`, y verificar `npm run test:movil` sin excepciones nuevas en `tests/movil/excepciones.json`.
- [X] T064 [P] Prueba de guarda en `tests/Feature/CuentasDeMiembros/SinColumnasDeNombreTest.php`: recorre `app/` y `resources/views/` y falla si aparece `users.name`, `users.celular`, `orderBy('name')` sobre usuarios o `'name' =>` en un `User::create`.
- [X] T065 [P] Actualizar el `README.md` (sección de usuarios y liderazgo): todo usuario es un miembro, el alta en dos pasos, el comando `miembros:unificar-usuarios` y el pastor como cabeza.
- [ ] T066 Recorrer `specs/016-usuario-es-miembro/quickstart.md` completo en el navegador (local) como administrador y como pastor. Anotar los resultados en la sección «Línea base» del quickstart.
- [ ] T067 Cierre: `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` en verde. Ejecutar `php artisan migrate` sobre la base local (**nunca** `migrate:fresh`) y `php artisan miembros:unificar-usuarios`, y revisar la lista «Por revisar».

---

## Dependencias y orden de ejecución

### Entre fases

- **Preparación (F1)**: sin dependencias.
- **Fundamentos (F2)**: dependen de F1 y **bloquean todas las historias**.
- **US1 (F3)**: depende de F2.
- **US2 (F4)**: depende de US1 (comparte `CuentasDeMiembros::crear()`, `create.blade.php` y `store()`).
- **US3 (F5)**: depende de F2. Es independiente de US1 y US2, salvo `users/_form.blade.php` y `edit`, compartidos con T023 (hacer T023 antes de T032).
- **US5 (F6)**: depende solo de F2 (`liderPorDefecto`). Se puede hacer en paralelo con US1–US3.
- **US4 (F7)**: depende de US3 (accesores `name` y `celular`, T031) y de US1 (`CuentasDeMiembros`). La migración 4 (T058) va al final, después de T059.
- **US6 (F8)**: depende de US5 (reglas de pastor en `LiderazgoService`).
- **Pulido (F9)**: depende de todas las historias.

### Dentro de cada historia

Pruebas (en rojo) → servicio y modelo → controlador → vistas → punto de control.

### Archivos compartidos (no paralelizar entre sí)

- `app/Services/CuentasDeMiembros.php`: T017, T018, T026, T038, T053.
- `app/Http/Controllers/UsersController.php`: T020, T021, T023, T027, T032, T057.
- `app/Services/LiderazgoService.php`: T012, T042, T061.
- `app/Http/Requests/PersonaRequest.php`: T025, T033, T043.
- `app/Grillas/UsuariosGrilla.php`: T035, T056.

---

## Ejemplo de paralelismo: Historia de Usuario 1

```text
# Pruebas en paralelo (archivos distintos):
T014 BuscarMiembroPorCelularTest.php
T015 CrearUsuarioConMiembroTest.php
T016 CuentasDeMiembrosAislamientoTest.php

# En paralelo con el servicio:
T019 components/form/radio.blade.php
```

## Ejemplo de paralelismo: Historias 3 y 5 a la vez

```text
# Equipo A (US3): T029, T030 → T031 → T035, T036, T037 en paralelo → T032, T033, T034, T038
# Equipo B (US5): T039, T040, T041 → T042 → T043 → T044, T045 → T046, T047, T048
```

---

## Estrategia de implementación

### MVP primero (solo US1)

1. F1 y F2: fundamentos en verde.
2. F3 (US1): crear cuentas a partir de miembros existentes.
3. **Parar y validar**: quickstart §2. Desde aquí ninguna cuenta nueva nace sin miembro, si el miembro ya existe.

### Entrega incremental

1. + US2: ninguna cuenta nueva nace sin miembro, en ningún caso.
2. + US3: una sola fuente de nombre y celular.
3. + US5: el pastor como cabeza de los líderes.
4. + US4: datos históricos unificados y columnas viejas eliminadas.
5. + US6: cambio de pastor sin romper el árbol.

Cada paso deja la suite en verde y se puede desplegar.

---

## Notas

- Las pruebas se escriben primero y deben fallar antes de implementar.
- Los archivos existentes se editan **solo con Edit** (memoria del proyecto): nada de `sed`, `php -r` ni reemplazos por script sobre `laravel_app/`.
- Nunca `migrate:fresh` sobre la base local; las pruebas usan bases temporales.
- Commits solo cuando el responsable lo pida.
