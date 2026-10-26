---

description: "Lista de tareas para implementar roles y permisos por módulo"
---

# Tareas: Roles y permisos por módulo

**Entrada**: Documentos de diseño en `/specs/013-roles-permisos/`

**Prerrequisitos**: plan.md, spec.md, research.md, data-model.md, contracts/gestion-roles.md, contracts/autorizacion-modulos.md, quickstart.md

**Pruebas**: Obligatorias por el principio IV de la constitución (PHPUnit, Vitest y `npm run test:movil`); se escriben antes del código. **La prueba de caracterización (T003) debe ejecutarse contra el código actual, antes de cualquier refactor.**

**Organización**: Tareas agrupadas por historia de usuario. Esta funcionalidad modifica comportamiento de seguridad existente: ninguna tarea de las Fases 2 y 5 se da por terminada si la comparación contra la caracterización (T010) muestra diferencias no justificadas.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: Se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[HU]**: Historia de usuario (HU1…HU6)
- Las rutas son relativas a la raíz del repositorio; el código vive en `laravel_app/`

## Convenciones

- PHP en `laravel_app/app/...`; pruebas en `laravel_app/tests/Feature/Roles/`.
- Mientras la funcionalidad 010 no cambie los identificadores, los modelos siguen llamándose `Persona`/`personas`; los textos visibles dicen «miembros».
- Nunca se ejecuta `migrate:fresh` sobre la base local; las pruebas usan bases temporales y la migración siembra los 4 roles.
- Ejecuta `npm run test:movil` solo una vez a la vez.
- Las claves de módulo son exactamente: `tablero`, `miembros`, `lideres`, `sin_lider`, `celulas`, `ministerios`, `eventos`, `encuentros`, `organigrama`, `registro_miembros`, `usuarios`, `solicitudes_clave`.

---

## Fase 1: Preparación

- [X] T001 Verificar la línea base ejecutando `php artisan test` y `npm test` en `laravel_app/` y anotar si hay pruebas en rojo previas a este trabajo.
- [X] T002 [P] Crear los directorios vacíos `laravel_app/tests/Feature/Roles/`, `laravel_app/resources/views/roles/` y `laravel_app/resources/js/roles/`.

---

## Fase 2: Fundamentos (bloquea todas las historias)

**⚠️ CRÍTICO**: Ninguna historia empieza hasta terminar esta fase. El orden importa: primero se captura el comportamiento actual, después se agregan datos y servicio, y al final se reescriben los métodos de `User` comprobando que nada cambió.

- [X] T003 Crear `laravel_app/tests/Feature/Roles/CaracterizacionTest.php`: con las rutas registradas (`Route::getRoutes()`), crear un usuario por rol (`admin`, `pastor`, `lider_celula`, `user`) de la misma sede, sustituir los parámetros por ids reales de una sede de prueba (escenario de `tests/Support/EscenarioDosSedes.php`) y registrar el código HTTP que cada rol obtiene en cada ruta con sesión para los métodos GET y para POST/PUT/DELETE con cuerpo vacío (se espera 302/403/404/422 sin escribir datos); el resultado se compara con `laravel_app/tests/Support/matriz-acceso-base.json`; si la variable de entorno `GENERAR_MATRIZ_BASE=1` está definida, escribe el archivo en vez de compararlo. **Generarlo ahora, con el código actual sin modificar**: `GENERAR_MATRIZ_BASE=1 php artisan test --filter=Caracterizacion`, y revisar el JSON a mano (debe coincidir con la tabla de [research.md](research.md) D3).
- [X] T004 Crear la migración `laravel_app/database/migrations/2026_10_28_000001_create_roles_y_permisos.php`: tabla `roles` con `id`, `clave` (cadena máx. 40, única), `nombre` (cadena máx. 60, única), `descripcion` (cadena máx. 255, nula), `alcance` (cadena máx. 10: `sede`, `rama` o `registro`), `es_sistema` (booleano, por defecto falso), `asignable` (booleano, por defecto falso) y marcas de tiempo; tabla `rol_permisos` con `rol_id` (FK a `roles`, borrado en cascada), `modulo` (cadena máx. 30), `nivel` (entero pequeño: 0 sin acceso, 1 lectura, 2 escritura) e índice único `(rol_id, modulo)`; tabla `rol_cambios` con `rol_id` (entero nulo, FK a `roles`, a nulo al borrar), `rol_clave` (cadena máx. 40), `rol_nombre` (cadena máx. 60), `user_id` (entero nulo, FK a `users`, a nulo al borrar), `accion` (cadena máx. 20: `creado`, `editado`, `duplicado`, `eliminado`), `antes` (JSON nulo), `despues` (JSON nulo) y `created_at`; la migración siembra (de forma idempotente) los 4 roles `admin` («Administrador»), `pastor` («Pastor»), `lider_celula` («Líder») y `user` («Usuario») con `es_sistema` verdadero, `asignable` verdadero en `lider_celula` y `user` y falso en `admin` y `pastor`, el alcance (`sede`, `sede`, `rama`, `registro`) y los niveles de la tabla de [research.md](research.md) D3; si existe un `users.role` fuera de esas 4 claves, lanza una excepción con un mensaje que lo indica; `down()` elimina las tres tablas.
- [X] T005 [P] Crear los modelos `laravel_app/app/Models/Rol.php` (tabla `roles`, `#[Fillable]` de `nombre`, `descripcion`, `alcance`, `asignable`; relaciones `permisos()`, `usuarios()` por `users.role = roles.clave`, `cambios()`; método `nivelEn(string $modulo): int`; `esAdministrador(): bool`), `laravel_app/app/Models/RolPermiso.php` y `laravel_app/app/Models/RolCambio.php` (casts de `antes` y `despues` a arreglo). Sin filtro global de sede (los roles son globales).
- [X] T006 [P] Crear `laravel_app/app/Support/Modulos.php` con el catálogo fijo de los 12 módulos (clave, etiqueta en español, icono Font Awesome, grupo del menú, y las rutas que cubre según [contracts/autorizacion-modulos.md](contracts/autorizacion-modulos.md)), el método `rutas(): array` (patrones de nombre de ruta por módulo) y `todos(): array`. No incluye Sedes ni Roles.
- [X] T007 [P] Crear `laravel_app/app/Support/Niveles.php` con las constantes `SIN_ACCESO = 0`, `LECTURA = 1`, `ESCRITURA = 2`, las etiquetas «Sin acceso», «Lectura», «Escritura», la lista de alcances (`sede` «Toda la sede», `rama` «Solo su rama», `registro` «Solo su registro») y el orden `registro < rama < sede`.
- [X] T008 [P] Crear `laravel_app/database/factories/RolFactory.php` (clave única derivada del nombre, alcance `sede`, no de sistema) y un método `conPermisos(array $niveles)`.
- [X] T009 Crear `laravel_app/app/Services/AutorizacionRoles.php` con `nivel(?User, string $modulo): int` (0 sin usuario o sin rol vigente; 2 siempre para `admin`), `puedeLeer`, `puedeEscribir`, `alcance(?User): string` (`sede` para `admin`), `rolesAsignables(User $actor)` y `puedeAsignar(User $actor, Rol $rol)` aplicando el techo de [research.md](research.md) D10; los permisos del rol se leen **una vez por petición** y se guardan en los atributos de la petición (`request()->attributes`), sin sesión ni caché entre peticiones; registrar como singleton por petición en `laravel_app/app/Providers/AppServiceProvider.php`.
- [X] T010 Reescribir en `laravel_app/app/Models/User.php` la relación `rol()` (por `role` = `roles.clave`), `nivelEn()` y `alcance()` delegando en `AutorizacionRoles`, y `esAdmin()`/`isAdmin()` (clave `admin`), `tieneAccesoTotal()` (alcance `sede`) e `isLiderCelula()` (alcance `rama`) sin mirar nombres de rol salvo la clave `admin`; conservar `isPastor()` como `role === 'pastor'` solo para compatibilidad (no usarlo en reglas nuevas) y retirar `ROLES` y `ROLE_LABELS` sustituyéndolos por `Rol` en todo el código (`laravel_app/app/Grillas/UsuariosGrilla.php`, `SolicitudesClaveGrilla.php`, `laravel_app/app/Providers/AppServiceProvider.php` línea del `rolEtiqueta`, y las vistas `users/_celda.blade.php`, `users/_form.blade.php`, `claves/mostrar.blade.php`, `claves/_celda-solicitud.blade.php`, `cuenta/mi-cuenta.blade.php` y `sedes/show.blade.php`). Ejecutar `php artisan test --filter=Caracterizacion` y la suite completa: **debe quedar idéntico a la matriz base y en verde**.
- [X] T011 Agregar en `laravel_app/app/Providers/AppServiceProvider.php` el gate `administrador` (solo `admin`) y la directiva Blade `@puedeModulo('clave', 'lectura'|'escritura') … @endpuedeModulo` apoyada en `AutorizacionRoles`.

**Punto de control**: tablas y roles sembrados, `AutorizacionRoles` disponible, `User` reescrito, caracterización idéntica y suite en verde.

---

## Fase 3: Historia de Usuario 1 - Ver los roles en la pestaña ROLES (Prioridad: P1) 🎯 MVP

**Meta**: El administrador abre Usuarios › Roles, ve los 4 roles con su alcance y cantidad de usuarios, y abre un rol para ver su matriz.

**Prueba Independiente**: Abrir Usuarios › Roles y comprobar los 4 roles con los permisos de [research.md](research.md) D3.

### Pruebas de HU1 (escribir primero; deben fallar)

- [X] T012 [P] [HU1] Crear `laravel_app/tests/Feature/Roles/ListaRolesTest.php`: el administrador ve `roles.index` con los roles Administrador, Pastor, Líder y Usuario (nombre, descripción, alcance, cantidad de usuarios, marca de sistema); `roles.datos` devuelve JSON con la estructura de las demás grillas y respeta orden, filtro y paginación; `roles.show` muestra la matriz de solo lectura con los niveles sembrados; pastor, líder y usuario reciben 403 en `roles.*` y no ven la pestaña «Roles» en `usuarios.index`; sin sesión redirige al inicio de sesión; las respuestas llevan `Cache-Control: no-store, private`.
- [X] T013 [P] [HU1] Crear `laravel_app/tests/Feature/Roles/MigracionTest.php`: tras migrar hay exactamente 4 roles de sistema con la matriz de D3 (comparar los 12 módulos de cada uno), `admin` con escritura en todo y alcance `sede`; la migración es idempotente (ejecutarla de nuevo no duplica); con un `users.role` desconocido lanza la excepción; cada usuario existente conserva su `role` y su rol existe.

### Implementación de HU1

- [X] T014 [HU1] Verificar que `laravel_app/database/seeders/DatabaseSeeder.php` y `laravel_app/database/factories/UserFactory.php` siguen creando usuarios con las claves `admin`, `pastor`, `lider_celula` y `user` (que ya existen como roles tras migrar) sin cambios de valores, y que `php artisan db:seed` funciona sobre la base temporal de pruebas.
- [X] T015 [HU1] Crear `laravel_app/app/Grillas/RolesGrilla.php` (extiende `Grilla`, patrón de `UsuariosGrilla`): columnas nombre, descripción, alcance (etiqueta de `Niveles`), usuarios (conteo) y tipo («Sistema»/«Personalizado»); orden inicial por nombre; búsqueda por nombre; sin exportación a CSV; `puedeVer` solo para el administrador.
- [X] T016 [HU1] Crear `laravel_app/app/Http/Controllers/RolesController.php` con `index`, `datos` y `show`, usando `ServicioGrilla` como `UsersController`; cada acción con `ensureLoggedIn()` y `Gate::allows('administrador')` o 403; respuestas con `Cache-Control: no-store, private`.
- [X] T017 [HU1] Registrar en `laravel_app/routes/web.php` las rutas `roles.index` (GET `/roles`), `roles.datos` (GET `/roles/datos`) y `roles.show` (GET `/roles/{rol}`, con `{rol}` resuelto por `clave`) y proteger el grupo con `->middleware('modulo:…')` reservado: usar el gate `administrador`, no un módulo.
- [X] T018 [P] [HU1] Crear las vistas `laravel_app/resources/views/roles/index.blade.php` (grilla estándar `<x-ui.grilla>` bajo el encabezado de pestañas) y `laravel_app/resources/views/roles/show.blade.php` (datos del rol y matriz de solo lectura con `Niveles`), con textos en español.
- [X] T019 [HU1] Agregar el encabezado de pestañas «Usuarios | Roles» (`nav nav-tabs`, enlaces a `users.index` y `roles.index`) en `laravel_app/resources/views/users/index.blade.php` y `roles/index.blade.php` mediante un parcial `laravel_app/resources/views/users/_pestanas.blade.php`; la pestaña «Roles» solo se renderiza si `Gate::allows('administrador')`.
- [X] T020 [HU1] Ejecutar `php artisan test --filter=Roles` y dejar T012–T013 en verde.

**Punto de control**: el administrador ve y consulta los 4 roles; los demás no ven la pestaña.

---

## Fase 4: Historia de Usuario 2 - Crear un rol con permisos por módulo (Prioridad: P1)

**Meta**: El administrador crea un rol con nombre, descripción, alcance y un nivel por módulo, y queda disponible para asignar.

**Prueba Independiente**: Crear «Secretaría» con lectura en Miembros y escritura en Eventos y verlo en la lista y en el selector de usuarios.

### Pruebas de HU2 (escribir primero; deben fallar)

- [X] T021 [P] [HU2] Crear `laravel_app/tests/Feature/Roles/CrearRolTest.php`: crear un rol guarda `roles` y las 12 filas de `rol_permisos` con los niveles elegidos, una `clave` única y estable (minúsculas, sin acentos ni espacios, generada del nombre) y una fila en `rol_cambios` con acción `creado`; validaciones: `nombre` requerido, máx. 60, único sin distinguir mayúsculas ni acentos («Secretaria» y «secretaría» chocan); `descripcion` opcional, máx. 255; `alcance` requerido y uno de `sede`, `rama`, `registro`; `permisos[modulo]` solo 0, 1 o 2 y solo claves del catálogo (rechazar módulos desconocidos y Sedes/Roles); un rol sin ningún permiso se puede guardar; el rol creado aparece en `roles.index` y en el selector de rol del formulario de usuarios del administrador; los errores aparecen junto al campo y se conserva lo escrito.
- [X] T022 [P] [HU2] Crear `laravel_app/resources/js/roles/matriz.test.js` (Vitest + jsdom): los atajos «Todo en lectura» y «Todo sin acceso» marcan el radio correspondiente de cada módulo; no fallan sin matriz; no modifican el campo `alcance`.

### Implementación de HU2

- [X] T023 [HU2] Crear `laravel_app/app/Http/Requests/RolRequest.php` con las reglas de [data-model.md](data-model.md): `nombre` requerido string máx. 60 único sin distinguir mayúsculas ni acentos (ignorando el rol editado), `descripcion` nullable string máx. 255, `alcance` requerido `in:sede,rama,registro`, `asignable` booleano, `permisos` arreglo con las 12 claves del catálogo y valores `in:0,1,2`; mensajes en español; autorización solo del administrador.
- [X] T024 [HU2] Crear `laravel_app/app/Services/RolService.php` con `crear(array $datos, User $por): Rol` (genera `clave` única a partir del nombre con `Str::ascii`/`Str::slug` con guion bajo y sufijo numérico si choca, guarda permisos y escribe `rol_cambios` con acción `creado`) y `etiquetaDeCambios`; todo en transacción.
- [X] T025 [HU2] Agregar a `RolesController` los métodos `create` y `store` y registrar `roles.create` (GET `/roles/create`) y `roles.store` (POST `/roles`) en `laravel_app/routes/web.php`.
- [X] T026 [P] [HU2] Crear el componente `laravel_app/resources/views/components/form/matriz-permisos.blade.php` (una fila o tarjeta por módulo del catálogo con tres radios nativos «Sin acceso», «Lectura», «Escritura»; valores por defecto 0; en celular tarjetas apiladas con opciones de al menos 44 × 44 px y letra de al menos 16 px; recibe los niveles actuales y el modo de solo lectura).
- [X] T027 [P] [HU2] Crear `laravel_app/resources/views/roles/form.blade.php` con `x-form.layout`, `x-form.input` (nombre, descripción), `x-form.select` (alcance con las 3 etiquetas de `Niveles`), casilla «Asignable por quien gestiona usuarios» y el componente de matriz; sin `<form>` escrito a mano; y `laravel_app/resources/views/roles/create.blade.php` que lo incluye.
- [X] T028 [P] [HU2] Crear `laravel_app/resources/js/roles/matriz.js` (`iniciarMatriz(raiz)`: botones «Todo en lectura» y «Todo sin acceso», ocultos en el HTML y mostrados por JS) y cargarlo solo en esta pantalla desde `laravel_app/resources/js/app.js` (importación dinámica con `[data-matriz-permisos]`).
- [X] T029 [HU2] Hacer que los selectores de rol de `laravel_app/resources/views/users/_form.blade.php` y las reglas `in` de `laravel_app/app/Http/Controllers/UsersController.php` lean los roles de la tabla (por ahora todos para el administrador; el techo para otros se completa en HU5).
- [X] T030 [HU2] Ejecutar `php artisan test --filter=Roles` y `npm test`; dejar T021–T022 en verde.

**Punto de control**: se pueden crear roles con permisos y asignarlos desde el formulario de usuarios.

---

## Fase 5: Historia de Usuario 3 - Que los permisos se cumplan en toda la aplicación (Prioridad: P1)

**Meta**: Lo definido en el rol se aplica de verdad: menú, pantallas, botones, datos de grillas, exportaciones, búsquedas y direcciones directas, siempre con el aislamiento por sede.

**Prueba Independiente**: Para cada módulo, probar usuarios sin acceso, con lectura y con escritura: menú, pantalla, botones y envíos a mano.

### Pruebas de HU3 (escribir primero; deben fallar)

- [X] T031 [P] [HU3] Crear `laravel_app/tests/Feature/Roles/MatrizModulosTest.php` con un proveedor de datos módulo × nivel (0, 1, 2): para cada módulo de [contracts/autorizacion-modulos.md](contracts/autorizacion-modulos.md) crear un rol con ese nivel en ese módulo y 0 en los demás, e iniciar sesión con un usuario de ese rol: con 0 la ruta de lectura del módulo responde 403, el menú no muestra la opción y `datos`/`buscar`/`exportar` también responden 403; con 1 las rutas GET responden 200 y toda ruta POST/PUT/DELETE responde 403 sin escribir datos y las vistas no muestran botones de crear, editar, inactivar, reactivar, trasladar, asignar líder, abrir/cerrar/regenerar QR, atender solicitudes ni generar claves; con 2 las acciones del módulo responden con éxito dentro del alcance.
- [X] T032 [P] [HU3] Crear `laravel_app/tests/Feature/Roles/AislamientoRolesTest.php` (con `EscenarioDosSedes`): un rol con escritura en cualquier módulo y alcance `sede` nunca ve ni modifica datos de otra sede (listados, fichas, grillas, búsquedas, exportaciones); con alcance `rama` solo ve a sí mismo, a sus miembros directos, los de sus células y las células que lidera o co-lidera; con alcance `registro` solo su propia ficha; Eventos, Encuentros y Ministerios se ven completos (de su sede) con lectura sea cual sea el alcance, y dentro de ellos las personas listadas respetan el alcance; la exportación de datos de acceso (usuarios y solicitudes de clave) y las acciones reservadas (eliminar miembro, trasladar, Sedes, Roles, selector de sede activa, «Todas las sedes») siguen siendo solo del administrador aunque el rol tenga escritura en el módulo.
- [X] T033 [P] [HU3] Crear `laravel_app/tests/Feature/Roles/CambioInmediatoTest.php`: con una sesión abierta, cambiar a 0 el nivel del rol del usuario en un módulo, eliminar su rol (reasignando antes) o cambiarle el rol, y comprobar que su siguiente petición ya se evalúa con el nivel vigente sin cerrar sesión; un usuario sin rol vigente obtiene acceso solo a Mi cuenta y al inicio.
- [X] T034 [P] [HU3] Crear `laravel_app/tests/Feature/Roles/TableroPorPermisosTest.php`: el Tablero muestra cada total, tarjeta y acceso rápido solo si el rol tiene al menos lectura en su módulo y calcula los totales dentro del alcance del rol; un rol sin ningún módulo ve el Tablero sin cifras ni accesos.
- [X] T035 [P] [HU3] Ampliar `laravel_app/tests/Feature/Aislamiento/MatrizRutasTest.php` con una prueba que recorra `Route::getRoutes()` y falle si una ruta con sesión no pertenece a un módulo del catálogo (`Modulos::rutas()`), a la lista de reservadas del administrador ni a la lista de públicas; y agregar `roles.*` a los casos de otra sede (los roles son globales: solo administrador).

### Implementación de HU3

- [X] T036 [HU3] Crear `laravel_app/app/Http/Middleware/AutorizaModulo.php` (alias `modulo` registrado en `laravel_app/bootstrap/app.php`): `modulo:{clave}[,lectura|escritura]`; sin nivel, GET/HEAD exigen lectura y los demás métodos escritura; sin sesión redirige al inicio de sesión; sin el nivel responde 403 con «No tienes permiso para esta acción» (JSON en las peticiones que lo esperan).
- [X] T037 [HU3] Reorganizar `laravel_app/routes/web.php` en grupos con `->middleware('modulo:…')` según el mapa de [contracts/autorizacion-modulos.md](contracts/autorizacion-modulos.md) (`dashboard`→`tablero`, `personas.*`→`miembros`, `lideres.*`→`lideres`, `sin-lider.*`→`sin_lider`, `celulas.*`→`celulas`, `ministerios.*`→`ministerios`, `eventos.*`→`eventos`, `encuentros.*`→`encuentros`, `registro-miembros.*` y `registro-qr.*`→`registro_miembros` (los `registro-qr` de eventos, encuentros y células usan el módulo del destino), `users.*` y `claves.*`→`usuarios`, `solicitudes-clave.*`→`solicitudes_clave`); las exportaciones de datos de acceso llevan `modulo:usuarios,escritura` y `modulo:solicitudes_clave,escritura`; las rutas reservadas (`sedes.*`, `traslados.*`, `sede-activa.*`, `roles.*`, eliminar miembro) usan el gate `administrador`; las públicas (`login*`, `olvide-clave`, `registro.*`, `registro-sede.*`, `mi-cuenta`, `cambiar-clave`, `logout`) quedan fuera.
- [X] T038 [HU3] Completar `laravel_app/app/Support/Modulos.php::rutas()` con los patrones de nombre de ruta de cada módulo y exponer las listas de reservadas y públicas que usa la prueba de T035.
- [X] T039 [HU3] Reescribir `laravel_app/app/Policies/PersonaPolicy.php` para que `ver`, `crear`, `editar`, `inactivar`, `designarLider`, `verSinLider`, `gestionarCatalogos` y `verBitacora` consulten `AutorizacionRoles` (módulos `miembros`, `lideres`, `sin_lider`; lectura/escritura) y el alcance; `eliminar` queda solo para `admin` (reservada); sin comparar nombres de rol.
- [X] T040 [HU3] Reescribir `laravel_app/app/Policies/UserPolicy.php` (`verUsuarios`, `crear`, `gestionar`, `gestionarClaves`, `verSolicitudesClave`) con el módulo `usuarios`/`solicitudes_clave`, misma sede, y retirar `ROLES_DEL_PASTOR`; `rolesAsignables` pasa a delegar en `AutorizacionRoles::rolesAsignables` (completado en HU5). `laravel_app/app/Policies/SedePolicy.php` queda solo para `admin`.
- [X] T041 [HU3] Reescribir en `laravel_app/app/Providers/AppServiceProvider.php` los gates `gestionarRegistroQr` (escritura del módulo del destino: `eventos`, `encuentros` o `celulas`, más pertenencia a la célula cuando el alcance es `rama`), `gestionarRegistroMiembros` (escritura en `registro_miembros` y sede propia; administrador cualquiera) y `verRegistroMiembros` (lectura en `registro_miembros` y no administrador).
- [X] T042 [HU3] Reescribir `laravel_app/app/Services/AlcanceService.php` para que `personasVisibles`, `lideresVisibles` y `celulasVisibles` usen el alcance del rol (`sede`, `rama`, `registro`) en vez de los métodos por nombre de rol, conservando exactamente las consultas actuales (incluida la rama: él, sus miembros directos, los de sus células y los líderes de esas células); actualizar `laravel_app/app/Http/Controllers/CelulasController.php`, `PersonasController.php` y `laravel_app/app/Http/Controllers/Controller.php` (`ensurePuedeGestionarEstructura` pasa a escritura en `celulas`).
- [X] T043 [HU3] Hacer que los módulos sin dueño filtren por alcance solo las personas listadas dentro de ellos: ajustar las consultas de inscritos y asistentes en `laravel_app/app/Http/Controllers/EventosController.php`, `EncuentrosController.php` y las vistas `eventos/show.blade.php`, `encuentros/show.blade.php` y `ministerios/show.blade.php` para usar `AlcanceService::personasVisibles` al listar personas.
- [X] T044 [HU3] Cambiar `laravel_app/config/menu.php` y `laravel_app/app/Support/Menu.php` para que cada opción declare `modulo` y se muestre con lectura (se retira `permiso` basado en roles); la opción «Registro de miembros» usa `registro_miembros` y no se muestra al administrador; mantener insignias.
- [X] T045 [HU3] Condicionar el Tablero: en `laravel_app/app/Http/Controllers/AuthController.php` (`dashboard`) calcular cada total solo si hay lectura en su módulo y dentro del alcance, y en `laravel_app/resources/views/dashboard.blade.php` y `laravel_app/resources/views/partials/accesos-rapidos.blade.php` mostrar cada tarjeta, total y acceso rápido solo con ese permiso.
- [X] T046 [P] [HU3] Proteger botones y acciones con `@puedeModulo('<módulo>', 'escritura')` en las vistas de `laravel_app/resources/views/personas/`, `celulas/`, `lideres/`, `sin-lider/`, `ministerios/`, `eventos/`, `encuentros/`, `users/`, `claves/`, `registro-miembros/` y `partials/panel-registro-qr.blade.php`/`panel-registro-miembros.blade.php`, y las columnas de acciones de las grillas (`laravel_app/app/Grillas/*.php`) con el mismo criterio; sin botones de cambio de datos con solo lectura.
- [X] T047 [P] [HU3] Retirar de `laravel_app/app/Http/Controllers/SedeActivaController.php`, `ResuelveSede.php`, `SedeContext.php` y `AuthController.php` las comparaciones por nombre de rol que no sean la clave `admin` y confirmar con la prueba de T032 que el aislamiento por sede no depende del rol.
- [ ] T048 [HU3] Crear la pantalla de Organigrama para roles que no son administrador (decisión D8 de [research.md](research.md); **confirmar con el responsable antes de hacerla**): `laravel_app/app/Http/Controllers/OrganigramaController.php` (`index`), ruta `organigrama.index` (GET `/organigrama`, `modulo:organigrama`), vista `laravel_app/resources/views/organigrama/index.blade.php` que reutiliza el parcial `organigrama/_arbol`, y la opción de menú con `modulo: organigrama` oculta al administrador (que usa la pestaña de Sedes).
- [X] T049 [HU3] Ejecutar `php artisan test` (suite completa), comparar `CaracterizacionTest` contra `matriz-acceso-base.json` (cualquier diferencia debe ser una decisión justificada anotada en [research.md](research.md)) y dejar T031–T035 en verde.

**Punto de control**: ninguna ruta con sesión queda sin módulo; el acceso de los 4 roles sembrados es idéntico al de antes; los roles nuevos se aplican en la siguiente acción.

---

## Fase 6: Historia de Usuario 4 - Editar, duplicar y eliminar roles con seguridad (Prioridad: P2)

**Meta**: Cambiar permisos, nombre y alcance; duplicar; eliminar con reglas; proteger al Administrador y al último administrador.

**Prueba Independiente**: Editar un rol con usuarios (aviso del conteo), intentar eliminar un rol en uso y el Administrador.

### Pruebas de HU4 (escribir primero; deben fallar)

- [X] T050 [P] [HU4] Crear `laravel_app/tests/Feature/Roles/EditarRolTest.php`: editar guarda nombre, descripción, alcance y permisos y escribe `rol_cambios` con acción `editado`, `antes` y `despues` solo de lo que cambió; sin cambios no escribe historial; la `clave` no cambia al renombrar; editar un rol con usuarios sin el campo `confirmo_afectados=1` vuelve al formulario con el aviso del número de usuarios afectados y no guarda; con él guarda; duplicar (`roles.create` con `?duplicar={rol}`) precarga permisos y alcance y pide un nombre nuevo, y al guardar escribe acción `duplicado`.
- [X] T051 [P] [HU4] Crear `laravel_app/tests/Feature/Roles/EliminarRolTest.php`: eliminar un rol sin usuarios y no de sistema lo borra (con historial `eliminado` que conserva `rol_clave` y `rol_nombre`); eliminar uno con usuarios se rechaza con «N usuarios lo tienen; reasígnalos primero» sin borrar; eliminar un rol de sistema se rechaza.
- [X] T052 [P] [HU4] Crear `laravel_app/tests/Feature/Roles/AdministradorProtegidoTest.php`: editar, renombrar, cambiar alcance o permisos del rol `admin` o eliminarlo responde 403 con «Rol protegido» y no cambia nada; `admin` conserva escritura en los 12 módulos; no se puede dejar el sistema sin un usuario administrador (cambiar el rol, eliminar o desactivar al último se rechaza); el administrador puede cambiar solo la casilla «asignable» de roles que no son `admin`.

### Implementación de HU4

- [X] T053 [HU4] Ampliar `laravel_app/app/Services/RolService.php` con `actualizar(Rol, array, User): Rol` (calcula `antes`/`despues` de lo que cambió, registra `editado` solo si hubo cambios, mantiene la `clave`), `duplicar(Rol, array, User): Rol` (registra `duplicado`), `eliminar(Rol, User)` (rechaza si `es_sistema` o si tiene usuarios, con mensajes en español, y registra `eliminado` con `rol_clave` y `rol_nombre`) y `usuariosAfectados(Rol): int`.
- [X] T054 [HU4] Agregar a `RolesController` los métodos `edit`, `update` (exige `confirmo_afectados=1` si hay usuarios), `destroy` y la precarga de `duplicar`, y registrar `roles.edit`, `roles.update` y `roles.destroy` en `laravel_app/routes/web.php`; para `admin` todo responde 403 con «Rol protegido».
- [X] T055 [P] [HU4] Crear `laravel_app/resources/views/roles/edit.blade.php` (con el aviso «N usuarios se verán afectados» y la casilla oculta `confirmo_afectados`) y agregar a `roles/show.blade.php` y a la grilla las acciones Editar, Duplicar y Eliminar (esta con `x-form.confirm`), ocultas en roles protegidos.
- [X] T056 [HU4] Proteger al último administrador en `laravel_app/app/Http/Controllers/UsersController.php` (`update` y `destroy`): rechazar con un mensaje claro cambiar el rol, eliminar o desactivar a quien sea el único usuario con `role = 'admin'`.
- [X] T057 [HU4] Ejecutar `php artisan test --filter=Roles` y dejar T050–T052 en verde.

**Punto de control**: se gestiona el ciclo de vida de los roles sin poder dejar la aplicación sin administrador.

---

## Fase 7: Historia de Usuario 5 - Quien tiene escritura en Usuarios crea cuentas solo con roles permitidos (Prioridad: P2)

**Meta**: Un usuario con escritura en Usuarios crea y edita cuentas de su sede con roles asignables sin superar sus propios permisos ni alcance.

**Prueba Independiente**: Con un usuario con escritura en Usuarios, crear una cuenta y comprobar las opciones; intentar enviar a mano otro rol.

### Pruebas de HU5 (escribir primero; deben fallar)

- [X] T058 [P] [HU5] Crear `laravel_app/tests/Feature/Roles/EscaladaTest.php`: un usuario con escritura en `usuarios` ve como opciones solo los roles `asignable` cuyo alcance es menor o igual al suyo y cuyo nivel por módulo es menor o igual al suyo (módulo a módulo); enviar a mano otro rol (incluido `admin`) responde con error de validación sin crear ni cambiar nada; no puede cambiar su propio rol; no puede editar, cambiar el rol, los datos de acceso ni generar claves de una cuenta cuyo rol no podría asignar; solo gestiona cuentas de su propia sede; con solo lectura en Usuarios ve la lista sin acciones de crear, editar ni claves; el administrador ve y puede asignar todos los roles vigentes.

### Implementación de HU5

- [X] T059 [HU5] Implementar en `laravel_app/app/Services/AutorizacionRoles.php` `rolesAsignables(User)` y `puedeAsignar(User, Rol)` con el techo de [research.md](research.md) D10 (rol `asignable`, alcance `<=` el del actor según `Niveles`, nivel `<=` en cada módulo) y devolver todos para `admin`; usarlos en `laravel_app/app/Policies/UserPolicy.php` (`rolesAsignables`, `gestionar`, `gestionarClaves`) y en `UsersController` (`create`, `store`, `edit`, `update`) para las reglas `in` y para negar el cambio del propio rol.
- [X] T060 [HU5] Hacer que `laravel_app/resources/views/users/_form.blade.php` y `users/index.blade.php` muestren solo los roles permitidos y oculten crear/editar/claves con solo lectura; `laravel_app/app/Grillas/UsuariosGrilla.php` y `SolicitudesClaveGrilla.php` limitan la exportación a escritura.
- [X] T061 [HU5] Ejecutar `php artisan test --filter=Roles` y dejar T058 en verde.

**Punto de control**: la delegación de usuarios funciona por permisos y no se puede escalar.

---

## Fase 8: Historia de Usuario 6 - Ver quién tiene cada rol y qué cambió (Prioridad: P3)

**Meta**: Desde un rol, ver sus usuarios y su historial de cambios.

**Prueba Independiente**: Cambiar un permiso y comprobar el historial con fecha y autor.

### Pruebas de HU6 (escribir primero; deben fallar)

- [X] T062 [P] [HU6] Crear `laravel_app/tests/Feature/Roles/HistorialRolTest.php`: `roles.show` lista los usuarios del rol (nombre y sede) y el historial en orden descendente con acción, autor, fecha y los niveles anterior y nuevo por módulo («Eventos: Lectura → Escritura»); un rol eliminado conserva sus entradas de historial con `rol_nombre`; solo el administrador los ve.

### Implementación de HU6

- [X] T063 [HU6] Completar `roles/show.blade.php` con las tablas pequeñas «Usuarios con este rol» y «Historial del rol» (`tabla-apilable` con `data-etiqueta`) y los textos de `RolCambio` (método de etiquetas en `RolService` o en el modelo), y cargar usuarios con `withoutGlobalScopes()` solo en esta vista de administrador.
- [X] T064 [HU6] Ejecutar `php artisan test --filter=Roles` y dejar T062 en verde.

**Punto de control**: las seis historias funcionan de forma independiente.

---

## Fase 9: Pulido y asuntos transversales

- [X] T065 [P] Agregar `roles` y `roles/create` a `laravel_app/tests/movil/pantallas.mjs` (con sesión) y crear `laravel_app/tests/movil/roles.spec.js`: la pestaña Roles y el formulario a 320–430 px y 768/1920 px sin desplazamiento horizontal, matriz en tarjetas con opciones de ≥ 44 × 44 px, letra de ≥ 16 px y la lista con filas apiladas como tarjetas.
- [X] T066 [P] Documentar en `laravel_app/README.md` la sección «Roles y permisos» (módulos, niveles, alcance, acciones reservadas al administrador, techo de asignación, cómo se migraron los 4 roles y el hueco de Eventos/Encuentros/Ministerios heredado).
- [X] T067 [P] Actualizar `laravel_app/tests/Feature/Interfaz/TerminologiaMiembrosTest.php` solo si falla por mis textos nuevos (usar «miembros» en comentarios y cadenas).
- [ ] T068 Revisar el hueco heredado de [research.md](research.md) D3 con el responsable (Eventos, Encuentros y Ministerios en escritura para Líder y Usuario) y, si decide cerrarlo, ajustar los niveles de los roles sembrados desde la pestaña Roles (no en código) y anotarlo.
- [X] T069 Ejecutar la suite completa: `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` (una sola corrida), todo en verde salvo fallos preexistentes documentados, y `laravel_app/tests/movil/excepciones.json` vacío.
- [ ] T070 Recorrer manualmente `specs/013-roles-permisos/quickstart.md` en el navegador (escritorio y 320 px, con y sin JavaScript) y corregir lo que falle.

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias.
- **Fundamentos (Fase 2)**: depende de la Fase 1. **T003 antes de T004–T011** (caracterización con el código sin tocar). T010 depende de T005–T009.
- **HU1 (Fase 3)**: depende de la Fase 2.
- **HU2 (Fase 4)**: depende de HU1 (comparte controlador, rutas y formulario).
- **HU3 (Fase 5)**: depende de la Fase 2; puede avanzar en paralelo con HU1/HU2 salvo los archivos compartidos (`routes/web.php`, `AppServiceProvider.php`); se recomienda hacerla después de HU2 para probar roles nuevos.
- **HU4 (Fase 6)**: depende de HU2 y de HU3 (T056 usa los permisos de `usuarios`).
- **HU5 (Fase 7)**: depende de HU3 (políticas de usuarios) y de HU2.
- **HU6 (Fase 8)**: depende de HU1 y de HU4 (historial de edición y eliminación).
- **Pulido (Fase 9)**: depende de las historias incluidas.

### Oportunidades de paralelismo

- Fase 2: T005, T006, T007 y T008 en paralelo (archivos distintos).
- HU1: T012 y T013 en paralelo; T018 en paralelo con T015–T017.
- HU2: T021 y T022 en paralelo; T026, T027 y T028 en paralelo.
- HU3: T031–T035 en paralelo (archivos de prueba distintos); T046 y T047 en paralelo.
- HU4: T050–T052 en paralelo; T055 en paralelo con T053–T054.
- Pulido: T065, T066 y T067 en paralelo.

---

## Estrategia de implementación

### MVP primero (Fundamentos + HU1 + HU2 + HU3)

1. Completar Fases 1 y 2 (con la caracterización **primero**).
2. HU1 (ver roles) y HU2 (crear roles).
3. HU3 (hacer cumplir los permisos): sin ella los roles serían decorativos y **no se debe publicar HU2 sin HU3**.
4. **Parar y validar**: comparar contra la matriz base, crear un rol y comprobar el acceso real de su usuario.

### Entrega incremental

1. Fundamentos → roles sembrados y servicio listos, sin cambio visible.
2. HU1 → pestaña Roles de consulta.
3. HU2 + HU3 → crear roles y que se cumplan (MVP seguro).
4. HU4 → ciclo de vida seguro.
5. HU5 → delegación sin escalada.
6. HU6 → trazabilidad.
7. Pulido y verificación final.

> Nota de seguridad: HU3 es la funcionalidad crítica. Hasta que no estén en verde la caracterización, la matriz módulo × nivel y el aislamiento entre sedes, ninguna otra historia debe darse por terminada.

---

## Notas

- `[P]` = archivos distintos, sin dependencias pendientes.
- La etiqueta [HU] relaciona cada tarea con su historia para dar trazabilidad.
- T048 (Organigrama para no administradores) requiere confirmación del responsable antes de implementarse.
- Los commits solo se hacen cuando el responsable del proyecto lo pida.
