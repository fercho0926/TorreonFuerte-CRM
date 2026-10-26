# Investigación: Roles y permisos por módulo

No quedaban marcadores `NEEDS CLARIFICATION`; estas son las decisiones técnicas, apoyadas en una lectura del código actual.

## Hallazgos del código actual

- Los roles son cadenas en `users.role` (`admin`, `pastor`, `lider_celula`, `user`) con constantes `User::ROLES` y `ROLE_LABELS`; los métodos `esAdmin()`, `isPastor()`, `isLiderCelula()` y `tieneAccesoTotal()` (= admin o pastor) se usan en ~26 puntos: `PersonaPolicy`, `UserPolicy`, `SedePolicy`, `AlcanceService`, gates de `AppServiceProvider`, `ResuelveSede`, `SedeContext`, `AuthController`, `CelulasController`, `PersonasController`, grillas y vistas.
- **Eventos, Encuentros y Ministerios no tienen ninguna comprobación de rol**: cualquier usuario con sesión (incluido el rol `user`) puede ver y modificar todo en su sede. Células exige `tieneAccesoTotal()` para escribir. Líderes exige admin/pastor para designar. Sin líder: admin/pastor. Usuarios y Solicitudes de clave: admin y pastor (con exportación solo del administrador). Sedes y Traslados: solo administrador. Eliminar miembro: solo administrador.
- Los tests crean usuarios con `role => 'pastor'` y similares en ~65 archivos: conviene no cambiar el tipo ni el valor de `users.role`.

## D1. Dónde se guardan los roles: `roles` + `rol_permisos`, y `users.role` se conserva

- **Decisión**: tabla `roles` (clave única, nombre, descripción, alcance, `es_sistema`, `asignable`) y `rol_permisos` (rol, módulo, nivel). `users.role` sigue siendo una cadena que referencia `roles.clave`; los 4 roles actuales conservan sus claves. Relación `User::rol()` por clave.
- **Justificación**: cero cambios en `users`, en las fábricas y en las ~65 pruebas que ya crean usuarios con esas claves; la migración de datos es solo sembrar 4 filas. Los roles nuevos reciben una clave generada a partir del nombre (única, minúsculas sin acentos).
- **Alternativas descartadas**: `users.rol_id` (obliga a migrar todos los usuarios y cambiar fábricas y pruebas); paquete externo de permisos (añade dependencia y su propio modelo; no encaja con niveles por módulo ni con el alcance de datos).

## D2. Catálogo de módulos y niveles

- **Decisión**: los módulos son una lista fija en código (`App\Support\Modulos`): `tablero`, `miembros`, `lideres`, `sin_lider`, `celulas`, `ministerios`, `eventos`, `encuentros`, `organigrama`, `registro_miembros`, `usuarios`, `solicitudes_clave`. Niveles numéricos acumulativos: 0 sin acceso, 1 lectura, 2 escritura. Reservados al administrador (no configurables): Sedes, Roles, traslados, eliminar miembro, vista «Todas las sedes» y selector de sede activa.
- **Justificación**: la lista de módulos cambia solo con código; guardarla en base de datos obligaría a sincronizarla. Números acumulativos hacen trivial la comparación («tiene al menos lectura»).

## D3. Permisos de los roles actuales (migración sin cambio de acceso)

La siembra reproduce el comportamiento observado hoy; a partir de ella el administrador puede restringir:

| Módulo | admin | pastor | lider_celula | user |
|--------|:-----:|:------:|:------------:|:----:|
| Tablero | E | E | L | L |
| Miembros | E | E | E | L |
| Líderes | E | E | L | L |
| Sin líder asignado | E | E | — | — |
| Células | E | E | E* | L |
| Ministerios | E | E | E | E |
| Eventos | E | E | E | E |
| Encuentros | E | E | E | E |
| Organigrama | E | — | — | — |
| Registro de miembros por QR | E | E | — | — |
| Usuarios | E | E | — | — |
| Solicitudes de clave | E | E | — | — |
| **Alcance de datos** | sede | sede | rama | registro |

(E = escritura, L = lectura, — = sin acceso.)

\* Hoy el líder agrega y quita miembros y registra reuniones de **sus** células, pero no crea, edita ni elimina células (eso exige alcance «toda la sede»). Con tres niveles esto se conserva así: Células en escritura para el líder, y las acciones de estructura (crear, editar y eliminar célula) exigen además alcance `sede`. Lo mismo aplica a los QR de eventos, encuentros y células: escritura en el módulo del destino y, ademas, alcance `sede` o ser líder o co-líder de esa célula (como hoy).

- **Riesgo documentado**: Ministerios, Eventos y Encuentros quedan en escritura para `lider_celula` y `user` porque hoy no tienen restricción alguna. Es el comportamiento actual (CE-002 exige conservarlo), pero es un hueco de seguridad que ahora el administrador podrá cerrar desde la pestaña Roles. Se recomienda revisarlo al terminar.
- **Verificación**: una prueba de caracterización (T-caracterización) registra, antes del refactor, el resultado HTTP de cada ruta por rol y se compara después; cualquier diferencia debe ser una decisión explícita.

## D4. Acciones reservadas al administrador (fuera de los 3 niveles)

- **Decisión**: con solo tres niveles, «escritura» incluiría eliminar y trasladar, que hoy son solo del administrador. Se mantienen reservadas: **eliminar miembro, trasladar miembros entre sedes, gestión de Sedes, gestión de Roles y selector «Todas las sedes»**. Ningún nivel de ningún rol las concede salvo al rol Administrador.
- **Justificación**: conserva el acceso actual del pastor (que no elimina ni traslada) sin introducir permisos por acción, que la especificación deja fuera.
- **Alternativa descartada**: dejar que escritura incluya eliminar (amplía permisos del pastor sin avisar).

## D5. Resolución de permisos en cada petición

- **Decisión**: `AutorizacionRoles` carga una vez por petición los permisos del rol del usuario (una consulta con la clave del rol) y los guarda en los atributos de la petición. No se guardan permisos en sesión ni en caché entre peticiones.
- **Justificación**: RF-012 (cambios en la siguiente acción) se cumple por construcción; una consulta indexada por petición es despreciable. No hay invalidaciones que olvidar.
- **Alternativa descartada**: caché de permisos con invalidación (más rápido, pero introduce el riesgo de permisos obsoletos).

## D6. Aplicación del acceso: middleware por módulo + políticas

- **Decisión**: middleware `modulo:{clave}` en grupos de rutas de `routes/web.php`: los métodos `GET/HEAD` exigen lectura y los demás exigen escritura; una rutas puede declarar `modulo:{clave},escritura` (por ejemplo, exportaciones con datos de acceso, que hoy son solo del administrador, o `GET` que dispara efectos). Las políticas y gates existentes (`PersonaPolicy::crear/editar/…`, `UserPolicy`, gates del QR) delegan en el mismo servicio para las acciones finas y las vistas (`@puedeModulo('eventos','escritura')`).
- **Justificación**: el middleware es la red de seguridad central ante direcciones directas (RF-009, CE-003); las políticas siguen decidiendo el detalle por registro. Así ninguna ruta nueva queda abierta por olvido: `MatrizRutasTest` falla si una ruta con sesión no declara módulo (se agrega esa verificación).
- **Alternativas descartadas**: solo políticas (ya hay controladores sin ninguna, como Eventos); solo middleware (no cubre reglas por registro como «solo su rama»).

## D7. Alcance de datos

- **Decisión**: `AlcanceService` y los métodos de `User` leen `rol->alcance`: `sede` (equivale a `tieneAccesoTotal`), `rama` (equivale a `isLiderCelula`: él, sus miembros y sus células) y `registro` (solo su ficha). Para módulos sin dueño (Eventos, Encuentros, Ministerios) el alcance no oculta registros; solo filtra personas e inscritos mostrados dentro de ellos (aclaración 2). Los totales del Tablero se calculan dentro del alcance.
- **Justificación**: es el comportamiento actual de los roles de sistema, ahora como dato; el aislamiento por sede sigue en `SedeScope` y no depende del rol.

## D8. Organigrama y Registro de miembros para no administradores

- **Decisión**: hoy el Organigrama es una pestaña del módulo Sedes (solo administrador) y el Registro de miembros tiene una pantalla del pastor. Para que un rol no administrador con «Organigrama» en lectura tenga dónde verlo, se agrega la pantalla `/organigrama` (reutiliza `OrganigramaService` de la sede del usuario) con opción de menú **solo visible si el rol tiene ese permiso**. El administrador sigue usando la pestaña de Sedes.
- **Justificación**: sin ella el módulo «Organigrama» sería configurable pero inaccesible. Reaparece en el menú únicamente para quien lo reciba, por lo que respeta la preferencia de no sumar opciones al menú de todos.
- **Pendiente de confirmar por el responsable** (se señala en el informe): alternativa de no ofrecer Organigrama a roles no administradores y dejarlo fuera de la matriz.

## D9. Roles y pestaña «Roles»

- **Decisión**: `usuarios.index` muestra pestañas «Usuarios | Roles» (enlaces a rutas distintas, funciona sin JavaScript). La lista de roles usa la grilla estándar (`RolesGrilla`: nombre, descripción, alcance, usuarios, sistema; ordenar, filtrar, paginar). Sin exportación a CSV (no hay datos de personas). Formulario con matriz de permisos (radios nativos por módulo); detalle con usuarios e historial; duplicar abre el formulario precargado.
- **Reglas**: nombre único sin distinguir mayúsculas ni acentos; clave generada y estable (no cambia al renombrar); `administrador` no editable; no se elimina un rol con usuarios; no se puede dejar el sistema sin un usuario con rol Administrador.

## D10. Techo de asignación (no escalada de permisos)

- **Decisión**: un rol R es asignable por un actor si R es `asignable`, su alcance es menor o igual al del actor (`registro < rama < sede`) y, para cada módulo, el nivel de R es menor o igual al del actor. El administrador asigna cualquiera. Un actor no cambia su propio rol ni toca cuentas cuyo rol no podría asignar. La regla se aplica en el servidor al validar y en el selector.
- **Justificación**: es la aclaración 1; con comparación módulo a módulo no hace falta una jerarquía de roles aparte.

## D11. Menú y Tablero

- **Decisión**: cada opción de `config/menu.php` declara su módulo y se muestra con lectura; el Tablero (`AuthController::dashboard`) calcula y muestra cada total solo si hay lectura en su módulo, con el alcance del rol.
- **Justificación**: aclaración 4 y RF-009.

## D12. Migración y reversión

- **Decisión**: una migración crea las tablas y siembra los 4 roles con la matriz de D3; es idempotente. `down()` elimina las tablas (los usuarios conservan `role`). Cualquier valor de `users.role` fuera de las 4 claves (por ejemplo, restos como `lider_zona`) se registra y detiene la migración con un mensaje claro, para no dejar usuarios sin rol.
