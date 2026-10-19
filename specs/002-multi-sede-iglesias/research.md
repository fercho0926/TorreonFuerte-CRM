# Investigación (Fase 0): Multi-sede

No quedaban marcadores `NEEDS CLARIFICATION` en el contexto técnico. Las decisiones siguientes parten de la spec (con sus 5 clarificaciones) y de la revisión del código actual: modelos `Persona`, `Red`, `Zona`, `Celula`, `CelulaReunion`, `Encuentro`, `Evento`, `EventoPersona`, `EventoPago`, `Ministerio`, `PersonaCambio`, `User`; sesión propia (`user_id`); `AlcanceService`; módulo Usuarios solo-admin.

## D1. Estrategia de multi-tenencia: una base, columna `sede_id`

- **Decisión**: una sola base de datos con `sede_id` en cada tabla de datos de la iglesia.
- **Justificación**: el hosting es cPanel compartido, se esperan decenas de sedes y se necesita una vista global del admin; una base por sede multiplicaría migraciones, copias de seguridad y complejidad sin necesidad a esta escala.
- **Alternativas**: base por sede (rechazada: operación y migraciones por sede, difícil el resumen global); esquema por sede (no aplica a MySQL/SQLite); subdominio por sede (fuera de alcance).

## D2. Aislamiento: filtro global + asignación automática, que falla cerrado

- **Decisión**: trait `PerteneceASede` con `SedeScope` (filtro global) y evento `creating` que asigna `sede_id` desde `SedeContext`. Reglas del filtro:
  - usuario no admin con sede → `where sede_id = su sede`;
  - usuario no admin sin sede → `1 = 0` (no ve nada);
  - admin con **sede activa** → `where sede_id = sede activa`;
  - admin con **"Todas las sedes"** → sin filtro (solo lectura);
  - sin usuario autenticado en la web → `1 = 0`; en consola (seeders, migraciones, `tinker`) → sin filtro.
- **Justificación**: un único punto de control evita olvidar un `where` en algún controlador (hoy hay ~10 controladores con consultas directas como `Persona::orderBy(...)`); el *route model binding* usa el mismo filtro, así que un enlace directo a otra sede da 404 sin revelar su existencia (FR-008).
- **Alternativas**: filtrar en cada controlador (rechazada: propenso a fugas); políticas solamente (no cubren listados ni selectores).

## D3. Qué tablas llevan `sede_id`

- **Decisión**: columna `sede_id` (clave foránea a `sedes`, **no nula** tras la migración) y el trait en: `personas`, `redes`, `zonas`, `celulas`, `celula_reuniones`, `encuentros`, `eventos`, `evento_persona` (inscripciones), `evento_pagos`, `ministerios`, `persona_cambios`. `users.sede_id` es **nullable** (el admin no tiene sede).
- **Tablas puente sin columna** (`celula_persona`, `encuentro_persona`, `ministerio_persona`, `celula_reunion_asistencia`): quedan aisladas porque sus dos extremos son modelos con filtro; los controladores ya validan existencia de ambos lados y se añade una validación explícita de misma sede al asociar.
- **Justificación**: los modelos que se consultan directamente (inscripciones, pagos, reuniones, bitácora) necesitan su propio filtro; duplicar `sede_id` ahí es barato y evita *joins* para aislar.
- **Alternativas**: derivar la sede por *join* con el padre (rechazada: más lenta y fácil de olvidar).

## D4. Unicidad por sede

- **Decisión**: `personas (sede_id, numero_documento)` único; `redes (sede_id, clave)` y `zonas (sede_id, clave)` únicos; se eliminan las unicidades globales actuales de `numero_documento`, `redes.clave` y `zonas.clave`. `users.email` **sigue único global** (es la credencial de acceso). `sedes.nombre` único.
- **Justificación**: FR-010; evita revelar a una sede que un documento existe en otra. Los mensajes de validación de unicidad se acotan por sede con `Rule::unique(...)->where('sede_id', ...)`.
- **Alternativas**: unicidad global del documento (rechazada: filtra información entre iglesias).

## D5. Contexto de sede: `SedeContext` + middleware `ResuelveSede`

- **Decisión**: servicio `SedeContext` que resuelve la sede efectiva por petición: `user.sede_id` para no admin; para admin, `sede_activa_id` guardado en sesión (o `null` = "Todas"). El middleware `ResuelveSede`:
  1. cierra la sesión si el usuario no admin tiene sede **inactiva** o no tiene sede (mensaje claro);
  2. para admin, valida que la sede activa exista;
  3. **bloquea escrituras** (POST/PUT/PATCH/DELETE) cuando el admin está en "Todas las sedes" o la sede está inactiva (excepto el módulo Sedes, cambio de sede activa, login y logout).
- **Justificación**: FR-005, FR-019 y clarificación Q2. El modelo además rechaza crear sin sede (defensa en profundidad).
- **Alternativas**: elegir sede en cada formulario (rechazada por el usuario).

## D6. Usuario actual sin recursión

- **Decisión**: el usuario autenticado se carga con `withoutGlobalScopes()`; `User` usa el trait pero el filtro del propio usuario autenticado no se aplica al resolver el contexto. El listado de usuarios de un pastor se filtra por su sede con el mismo filtro global.
- **Justificación**: el filtro global necesita al usuario para saber la sede; evitar que la carga del usuario dispare el filtro.

## D7. Roles y permisos

- **Decisión**:
  - **admin**: único rol con visión total y el único con acceso al módulo Sedes (`SedePolicy`).
  - **pastor** (incluye a la pastora, mismo rol): visión total **de su sede**, gestiona catálogos y usuarios de su sede; solo crea cuentas `lider_celula` y `user`; no crea ni modifica `pastor`/`admin`, ni cambia su sede o rol.
  - **lider_celula** y **user**: sin cambios (alcance por líder dentro de la sede).
  - `User::tieneAccesoTotal()` (hoy admin|pastor) se mantiene como "ve todo **dentro de su alcance de sede**"; se añade `esAdmin()` para lo global.
- **Justificación**: clarificación Q3 (el pastor gestiona usuarios de su sede; las cuentas de pastor/pastora las controla el admin).
- **Alternativas**: pastora como rol distinto (rechazada: permisos idénticos según la spec).

## D8. Alta atómica de sede con pastor y pastora

- **Decisión**: `SedeService::crear()` en una transacción: crea la `Sede`; crea la `Persona` y el `User` (rol `pastor`, `sede_id`) del pastor y, si se indica, de la pastora; enlaza `sedes.pastor_persona_id` / `pastora_persona_id`; registra la bitácora. Si algo falla (p. ej. correo repetido) se revierte todo (FR-003).
- **Contraseña**: el admin define una contraseña inicial por cuenta (o se genera); no se envía por correo en esta versión (fuera de alcance).
- **Alternativas**: crear sede y pastores en pasos separados (rechazada por riesgo de sedes a medias).

## D9. Cambio de pastor o pastora

- **Decisión**: el admin puede reemplazar al pastor o a la pastora de una sede: crea la cuenta nueva (o designa a una cuenta de la misma sede) y la anterior pasa a `lider_celula` (conserva su persona y sus datos). Queda en la bitácora de la sede.
- **Justificación**: Historia 4 de la spec ("pierde el rol de pastor, conserva su registro").

## D10. Desactivar sedes

- **Decisión**: `sedes.activa`. Una sede inactiva: sus usuarios no pueden iniciar sesión ni seguir en sesión (lo verifica el middleware en cada petición), el admin la ve en lectura y no admite escrituras en sus datos; no se pueden eliminar sedes con datos.
- **Alternativas**: borrado lógico (rechazada: la "desactivación" ya cubre el caso y es reversible).

## D11. Traslado de personas: copiar a la sede nueva, inactivar la de origen

- **Decisión**: `TrasladoService::trasladar(Persona, Sede)`:
  1. valida que la persona **no sea líder con personas o células a cargo ni encargada de un ministerio** (si lo es, error 422 con el detalle: hay que reasignar antes, usando las pantallas existentes);
  2. crea en la sede destino un **nuevo registro** con los datos de contacto y el estado, **sin líder, célula ni ministerios** (queda en "Sin líder asignado");
  3. deja el registro de origen como `inactivo` con `trasladada_a_sede_id` y `trasladada_en`, conservando todo su historial (asistencias, pagos, células) en la sede de origen;
  4. falla si el documento ya existe en la sede destino; registra bitácora en ambos registros.
- **Justificación**: clarificación Q4. Mover la misma fila dejaría enlaces cruzados (inscripciones y asistencias de eventos de otra sede apuntando a una persona de la nueva); copiar mantiene el aislamiento perfecto (SC-002).
- **Alternativas**: cambiar el `sede_id` de la fila (rechazada: rompe el aislamiento del historial); no permitir traslados (descartada por el usuario).

## D12. Migración de datos existentes

- **Decisión**: migraciones en cuatro pasos dentro del mismo lanzamiento:
  1. crear `sedes` y `sede_cambios`;
  2. añadir `sede_id` **nullable** a las tablas de datos y a `users`;
  3. **migración de datos**: crear la primera sede (nombre desde configuración `SEDE_INICIAL`, por defecto "Sede principal"), asignar `sede_id` a todas las filas existentes y a todos los usuarios que no sean admin; reportar conteos por tabla;
  4. volver `sede_id` **no nulo** (salvo `users`), crear los índices compuestos y reemplazar las unicidades globales por las de D4.
- **Reversible** hasta el paso 3; el paso 4 documenta el `down()`. Copia de seguridad previa recomendada.
- **Riesgos**: reconstruir tablas en SQLite con claves foráneas (se probó con `change()` en la funcionalidad 001); en MySQL se usan `ALTER` nativos.

## D13. Resumen global del admin

- **Decisión**: el tablero del admin muestra, por sede, personas, líderes, células y próximos eventos, calculados con consultas agrupadas sin filtro global y rotuladas por sede; los totales monetarios se muestran **por sede** y como total global rotulado (SC y caso límite de la spec).
- **Alternativas**: sumar todo sin distinguir (rechazada por la spec).

## D14. Estrategia de pruebas de aislamiento (SC-002, SC-005)

- **Decisión**:
  - **Pruebas por módulo**: con dos sedes con datos cruzados, un pastor de A no ve nada de B en listados, selectores, búsquedas, enlaces directos, creación y edición (personas, líderes, redes, zonas, células y reuniones, encuentros, eventos con pagos, ministerios, usuarios, dashboard).
  - **Matriz de rutas** (`MatrizRutasTest`): recorre las rutas registradas con parámetros de modelo, pide cada GET como pastor de A con IDs de recursos de B y exige 404/403; y cada POST/PUT/DELETE con IDs ajenos.
  - **Prueba de cobertura de modelos** (`ModelosConSedeTest`): lista las tablas con columna `sede_id` y exige que su modelo use el trait.
  - **Prueba de no nulos** (SC-007): tras crear/trasladar, ninguna fila queda sin sede.
- **Justificación**: el riesgo principal es una pantalla olvidada; la prueba de matriz lo detecta de forma automática.

## D15. Bitácora de sedes (FR-022)

- **Decisión**: tabla `sede_cambios` (sede, usuario, acción, antes, después, fecha) con acciones `creada`, `editada`, `desactivada`, `reactivada`, `pastor_cambiado`, `pastora_cambiada`; visible solo para el admin en el detalle de la sede.

## D16. Interfaz

- **Decisión**: la barra superior muestra el nombre de la sede del usuario (todos) y, para el admin, un selector "Sede activa" con la opción "Todas las sedes" y un aviso visible en ese modo ("Solo consulta"). El menú **Sedes** aparece solo para el admin. Los listados del admin en modo "Todas" añaden una columna **Sede**.
