# Investigación: Todo usuario es un miembro y el pastor encabeza el liderazgo

**Funcionalidad**: `016-usuario-es-miembro` | **Fecha**: 2026-10-23

Estado actual relevante (revisado en el código):

- `users` tiene columnas propias `name` y `celular`, y un `persona_id` **opcional** (`nullOnDelete`). `UsersController` pide nombre, correo y celular, y el vínculo con el miembro es un desplegable opcional.
- El ingreso es **por correo** (`AuthController`, `RecuperacionClaveController`). El celular de la cuenta se usa para entregar claves por WhatsApp (`ClavesTemporales`, `MuestraClave`).
- `SedeService::crearCuentaPastoral()` ya crea el miembro y la cuenta juntos; `vincularPersona()` crea un miembro a partir de una cuenta suelta.
- `personas.celular` se guarda tal como se escribe (con índice simple); `Celular::limpio()` deja solo dígitos.
- El pastor y la pastora son miembros (`sedes.pastor_persona_id`, `sedes.pastora_persona_id`), pero **no** están marcados como líderes (`es_lider = false`) y su `lider_id` puede tener cualquier valor.
- Se llega a ser líder por tres caminos: `LideresController::storeDesignar()`, el alta y la edición de miembros (`PersonasController`, casilla `es_lider`) y la reasignación al quitar un rol (`LiderazgoService::quitarRol()`).
- `TrasladoService::trasladar()` crea un miembro **nuevo** en la sede destino; la cuenta del miembro original no se mueve.
- El historial único de la spec 015 (`App\Services\Historial`) ya existe y registra los cambios de `Persona` y `User`.

---

## D1. Una sola fuente para nombre y celular

**Decisión**: se **eliminan** las columnas `users.name` y `users.celular`. `User` expone los atributos de solo lectura `name` y `celular`, que leen del miembro vinculado (`nombre_completo` y `celular`). La relación `persona()` se lee sin el filtro global de sede (como `Sede::pastor()`), porque el miembro de un administrador pertenece a una sede concreta y el administrador navega por todas. Las consultas que ordenaban o filtraban por `users.name` (grillas `UsuariosGrilla` y `SolicitudesClaveGrilla`, `orderBy('name')` en `SedesController` y `RolesController`) pasan a ordenar con una subconsulta sobre `personas` y a buscar con `whereHas('persona', …)`.

**Justificación**: es la única forma de que «Milton en usuarios» y «Milton en miembros» no puedan diferir (RF-006). Los accesores mantienen intacto el código y las vistas que leen `$user->name` y `$user->celular`, con lo que el cambio es pequeño.

**Alternativas descartadas**:
- *Copias sincronizadas por eventos del modelo*: siguen siendo dos datos, y cualquier escritura masiva (`update()` en consulta) o restauración los desincroniza.
- *Vista o columna calculada en la base*: SQLite y MySQL las manejan distinto, y las pruebas usan SQLite.

## D2. El correo sigue siendo de la cuenta

**Decisión**: `users.email` se conserva como credencial de ingreso; el ingreso y la recuperación no cambian. Cuando el miembro se crea desde «Nuevo usuario», su `personas.email` toma ese mismo correo. Si el miembro ya existe y no tiene correo, se le completa con el de la cuenta; si tiene uno distinto, se respeta el suyo.

**Justificación**: la especificación habla de nombre y celular. El correo es la credencial; moverlo al miembro obligaría a rehacer el ingreso, la recuperación y el límite de intentos, y eso queda fuera del alcance.

**Corrección a la especificación**: el escenario 2 de la Historia 3 y RF-008 dicen que el celular del miembro se usa «para entrar». El ingreso es por correo; el celular del miembro se usa para **identificar a la persona al crear la cuenta y para entregar claves por WhatsApp**. La redacción ya se corrigió en `spec.md`.

## D3. Buscar al miembro por celular

**Decisión**:
- Nueva columna `personas.celular_clave`, indexada, con la forma canónica del celular: `Celular::clave()`, que devuelve los dígitos y, si son 12 y empiezan por `57`, los últimos 10.
- La columna se llena en el evento `saving` de `Persona` y se rellena para los datos existentes en la migración.
- La búsqueda compara `celular_clave` por igualdad, dentro del contexto de sede (`SedeContext` y alcance del actor).

**Justificación**: cumple el escenario 4 de la Historia 1 (espacios, guiones, +57) con una consulta indexada, sin reescribir los celulares que la gente ya escribió.

**Alternativas descartadas**:
- *Normalizar `personas.celular` en sitio*: altera datos visibles y el historial de miles de miembros.
- *Búsqueda `LIKE` sobre varias variantes*: lenta y con falsos positivos.

## D4. Flujo de «Nuevo usuario» sin JavaScript

**Decisión**: el alta pasa a ser de **dos pasos sobre la misma ruta**:
1. `GET /users/create` muestra solo el campo celular (y la sede, si el actor es administrador).
2. `GET /users/create?celular=…` vuelve a mostrar la página con el resultado:
   - **un miembro**: tarjeta con su nombre, `persona_id` oculto y campos de cuenta (correo, rol);
   - **varios miembros**: el responsable elige uno con botones de opción;
   - **ninguno**: los campos obligatorios del miembro más los de la cuenta;
   - **el miembro ya tiene cuenta**: aviso con enlace a esa cuenta, sin formulario;
   - **miembro inactivo**: aviso con enlace para reactivarlo;
   - **celular registrado en otra sede**: aviso genérico, sin nombre ni datos (D8).

`POST /users` vuelve a validar todo en el servidor. No se agrega JavaScript; la búsqueda funciona con un formulario `GET` (principio V).

**Alternativas descartadas**:
- *Autocompletado con endpoint JSON*: agrega un endpoint y JavaScript sin necesidad, porque el celular identifica a una sola persona.

## D5. Alta atómica de miembro y cuenta

**Decisión**: nuevo servicio `CuentasDeMiembros`, con estas operaciones:
- `crear(datosCuenta, ?Persona, ?datosMiembro, User $actor)`: hace en una transacción el alta del miembro (si hace falta) y la de la cuenta.
- `vincular(User, Persona)`: cambia el miembro de una cuenta (lo usan la revisión manual y el traslado).
- `unificar()`: unifica las cuentas existentes (D7).

Los datos obligatorios del miembro se validan con las mismas reglas de `PersonaRequest`, extraídas a un método reutilizable. `SedeService::crearCuentaPastoral()` y `vincularPersona()` se reescriben sobre este servicio.

**Justificación**: un solo lugar garantiza RF-001, RF-002 y RF-005, y reutiliza las reglas existentes (principio VI).

## D6. Integridad: una cuenta por miembro y celulares sin choque

**Decisión**:
- Índice único en `users.persona_id`. Cuando no queda ninguna cuenta sin miembro (D7), `users.persona_id` pasa a `NOT NULL`. La clave foránea cambia de `nullOnDelete` a `restrictOnDelete`.
- Validación al guardar un miembro con cuenta: su `celular_clave` no puede coincidir con el de otro miembro **con cuenta** (RF-009). La validación vive en `PersonaRequest` y en `CuentasDeMiembros`.
- `PersonaPolicy::eliminar` niega el borrado de un miembro con cuenta y la vista lo explica (RF-010). Como `Persona` usa borrado lógico, la restricción de la clave foránea solo protege el borrado físico; la regla de la aplicación es la que cuenta.
- El formulario de edición de usuario ya no tiene nombre, celular ni desplegable de miembro. Muestra el miembro con un enlace a su ficha y solo deja cambiar el correo, el rol y la sede (si aplica).

## D7. Unificación de las cuentas existentes

**Decisión**: una migración de datos ejecuta `CuentasDeMiembros::unificar()`, que es idempotente. Hay además un comando `php artisan miembros:unificar-usuarios` para repetirla y ver el resumen. Para cada cuenta sin `persona_id`:

1. Se buscan miembros con su `celular_clave`: en su sede, o en todas las sedes si es administrador.
2. **Una coincidencia** cuyo nombre comparte al menos una palabra con el de la cuenta (comparación sin tildes ni mayúsculas): se vinculan.
3. **Ninguna coincidencia**: se crea el miembro con el nombre, celular y correo de la cuenta, en su sede (administrador: la sede de menor `id`). Se vinculan.
4. **Varias coincidencias, o una cuyo nombre no comparte ninguna palabra**: se crea el miembro como en el paso 3 para no dejar la cuenta sin persona, y la cuenta queda **marcada para revisión** con los candidatos en `users.revision_vinculo` (JSON).

En las cuentas que ya tenían miembro, si el nombre o el celular difieren de los del miembro, prevalecen los del miembro y la diferencia se registra en el historial (escenario 4 de la Historia 4).

**Revisión manual**:
- La grilla de usuarios tiene un filtro «Por revisar», solo para el administrador.
- En la edición de una cuenta marcada se puede elegir uno de los candidatos. `vincular()` mueve la cuenta, y el miembro creado automáticamente se elimina (borrado lógico) si no tiene otros datos asociados (células, eventos, avances, líder de alguien); si tiene datos, se conserva y se avisa.

**Orden de las migraciones**:
1. Crear `celular_clave` y `revision_vinculo`.
2. Unificar.
3. Eliminar `users.name` y `users.celular`; `persona_id` único y `NOT NULL`.

**Justificación**: cumple RF-001 desde el primer despliegue sin perder ninguna cuenta y deja los casos dudosos a la vista del administrador.

**Alternativa descartada**:
- *Dejar `persona_id` nulo en los casos dudosos*: rompe RF-001 y obliga a mantener `users.name` para mostrarlos.

## D8. Aislamiento entre sedes en la búsqueda

**Decisión**: la búsqueda solo devuelve miembros visibles para el actor (`AlcanceService::personasVisibles()` dentro del contexto de sede). Para el caso límite «celular de otra sede», se hace una comprobación de **existencia** sin filtro de sede, que solo produce el aviso genérico «Ese celular pertenece a un miembro de otra sede; solicita su traslado». No muestra nombre, sede ni ningún dato.

**Justificación**: evita duplicar a la persona (RF-002, CE-002) y solo revela que el número existe. Se declara como excepción menor al principio II, con una prueba que verifica que la respuesta no contiene datos del miembro.

## D9. El pastor como cabeza del liderazgo

**Decisión**: nuevo método `LiderazgoService::liderPorDefecto(Persona)`, que devuelve el pastor de la sede del miembro o lanza una `ValidationException` («Primero asigna el pastor de la sede») si no hay. Las reglas:

1. **Designar o crear líder** (`designarLider()`, alta y edición en `PersonasController`): en la misma transacción, `lider_id` = pastor de la sede, **reemplazando el líder anterior** (clarificación P1). El cambio usa `cambiarLider()` para registrar el historial. Si el miembro es el propio pastor o la pastora, no se le asigna líder.
2. **Todo líder tiene líder**: `cambiarLider($lider, null)` lanza una validación si el miembro es líder y no es pastor ni pastora. `PersonaRequest` exige `lider_id` cuando `es_lider` es verdadero y el miembro no es pastor ni pastora; si no se elige ninguno, se propone el pastor.
3. **El pastor y la pastora no tienen líder**: `validarLider()` rechaza asignarles uno. Pasan a `es_lider = true`, para que puedan ser líderes de otros (la validación actual exige `es_lider`).
4. **Retiro de células**: al poner a un líder bajo el pastor, `cambiarLider()` lo retira de las células del líder anterior, igual que hoy con cualquier cambio de líder. Se conserva ese comportamiento.

**Justificación**: concentra la regla en `LiderazgoService` (principio VI) y aprovecha la validación de ciclos y el historial que ya existen.

## D10. Cambio de pastor o pastora

**Decisión**: `SedeService::cambiarPastor()` hace en la misma transacción:
- el nuevo pastor o pastora: `lider_id = null`, `es_lider = true`;
- si cambia el **pastor**: todos los miembros con `lider_id` = pastor anterior pasan al nuevo pastor (`LiderazgoService::reasignarDependientes()`, que hace una actualización por lotes y registra una entrada de historial por persona, **sin** retirarlos de células, porque la estructura se conserva);
- el pastor o pastora anterior queda como líder bajo el nuevo pastor.

**Justificación**: implementa la Historia 6 y deja una sola cabeza (CE-005).

## D11. Corrección de los datos de liderazgo existentes

**Decisión**: una migración de datos, idempotente, por cada sede con pastor:
- marca al pastor y a la pastora como líderes y les quita el líder;
- asigna al pastor como líder de todo líder que no tenga uno (RF-019);
- registra cada cambio en el historial como acción del sistema (`Historial::comoActor('sistema', …)`).

Las sedes sin pastor se omiten y se informan en el resumen del comando `miembros:unificar-usuarios`.

## D12. Organigrama

**Decisión**: `OrganigramaService` hoy toma como raíz a los líderes sin `lider_id`. Pasa a tomar como raíz a los líderes **cuyo líder es el pastor o la pastora**, que se muestran en la cabecera de la sede. Así el organigrama no cambia visualmente y deja de haber líderes sueltos.

## D13. Traslados

**Decisión**: al trasladar a un miembro con cuenta, `TrasladoService::trasladar()` mueve la cuenta al miembro nuevo con `CuentasDeMiembros::vincular()` y actualiza `users.sede_id` a la sede destino, en la misma transacción. Si el miembro trasladado era líder en la sede destino, aplica D9 (queda bajo el pastor de destino).

## D14. Fábricas y pruebas existentes

**Decisión**: `UserFactory` crea siempre su miembro: `persona_id` con `Persona::factory()` en la sede de la cuenta, y para un administrador, en la sede por defecto. Los estados que hoy pasan `name` o `celular` a la fábrica de usuarios se trasladan a la persona con un estado `->conMiembro([...])`. Hay unos 30 usos en 22 archivos de prueba y 11 lugares con `'name' =>`. `PersonaFactory` acepta `->pastorDe($sede)`.
