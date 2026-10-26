# Plan de Implementación: Roles y permisos por módulo

**Rama**: `013-roles-permisos` | **Fecha**: 2026-10-23 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de funcionalidad en `/specs/013-roles-permisos/spec.md`

## Resumen

Hoy la aplicación tiene 4 roles fijos (`admin`, `pastor`, `lider_celula`, `user`) cuyo comportamiento está escrito en unos 26 puntos del código (políticas, gates, controladores, vistas, `AlcanceService`). Esta funcionalidad los reemplaza por **roles configurables**: una pestaña «Roles» dentro de Usuarios (solo administrador) donde se crean roles con un **nivel por módulo** (sin acceso, lectura, escritura) y un **alcance de datos** (toda la sede, su rama, su registro), y donde los 4 roles actuales pasan a ser roles editables del sistema (Administrador protegido).

El enfoque, en cuatro piezas:

1. **Datos**: tablas `roles`, `rol_permisos` y `rol_cambios`; `users.role` **se conserva como cadena** que apunta a `roles.clave` (los 4 roles actuales conservan sus claves), de modo que las cuentas, las fábricas y las pruebas existentes siguen funcionando. Una migración siembra los 4 roles con exactamente los permisos que hoy tienen (derivados del código, ver [research.md](research.md) D3).
2. **Autorización central**: un servicio `AutorizacionRoles` resuelve en cada petición el nivel del usuario en cada módulo (una consulta por petición, así los cambios se aplican en la siguiente acción) y un middleware `modulo:{clave}` en los grupos de rutas exige lectura (GET) o escritura (resto de métodos) por dirección directa. Gates y políticas existentes pasan a delegar en ese servicio.
3. **Alcance de datos**: `AlcanceService` y `User::tieneAccesoTotal()/isLiderCelula()` dejan de mirar el nombre del rol y leen el alcance del rol (`sede`, `rama`, `registro`).
4. **Interfaz**: pestaña «Roles» (grilla estándar, formulario con matriz de permisos, detalle con usuarios e historial, duplicar y eliminar con reglas de seguridad) y selectores de rol de Usuarios alimentados por la tabla, con la regla de no escalada de permisos (RF-015).

Antes de tocar nada se escribe una **prueba de caracterización** que registra qué puede hacer hoy cada rol en cada ruta; el criterio CE-002 («conservan exactamente el acceso») se verifica comparando contra ella.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; JavaScript ES modules (Vite 8)

**Dependencias Principales**: Blade, AdminLTE 4 / Bootstrap 5, componentes `x-form.*` y `x-ui.*`, grilla estándar (DataTables 2); sin librerías nuevas

**Almacenamiento**: Base de datos existente; 1 migración (3 tablas nuevas + siembra de los 4 roles). `users` no cambia de estructura.

**Pruebas**: PHPUnit (caracterización antes/después, matriz módulo × nivel × rol, escalada de permisos, protección del administrador, migración), Vitest (matriz de permisos), Playwright `npm run test:movil` (pestaña Roles y formulario)

**Plataforma Objetivo**: Web responsive e instalable (PWA)

**Tipo de Proyecto**: Aplicación web monolítica Laravel (`laravel_app/`)

**Objetivos de Rendimiento**: Una consulta extra por petición para resolver permisos (< 5 ms); pestaña Roles < 3 s en 4G lenta

**Restricciones**: Mismo acceso que hoy tras la migración (CE-002); aplicar cambios en la siguiente acción sin cerrar sesión (RF-012); aislamiento por sede independiente del rol; acciones destructivas reservadas al administrador (D4)

**Escala/Alcance**: Decenas de roles, cientos de usuarios; 12 módulos configurables; 4 roles de sistema

## Verificación de la Constitución

*PUERTA: debe pasar antes de la investigación de la Fase 0. Se revisa de nuevo tras el diseño de la Fase 1.*

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple. |
| II. Aislamiento por sede y alcance por rol (NO NEGOCIABLE) | Cumple y se refuerza: el aislamiento por sede (`SedeScope`) no depende del rol; el alcance pasa a ser dato del rol pero se sigue aplicando **en la consulta** (`AlcanceService`). Se agrega una prueba de caracterización antes del refactor, una matriz de aislamiento por rol y por módulo, y pruebas de «no escalada de permisos». Las rutas nuevas entran a `MatrizRutasTest`. |
| III. Grillas estándar | La lista de roles usa `<x-ui.grilla>` con `App\Grillas\RolesGrilla`; la prueba que detecta tablas simples sigue en verde. Las tablas de «usuarios del rol» e «historial» dentro del detalle son pequeñas (no listados principales). La exportación de roles no se ofrece (no es necesaria; ver D9). |
| IV. Pruebas primero | Cumple: la caracterización se escribe **antes** del refactor y las pruebas de cada historia antes de su código. |
| V. Mejora progresiva | Cumple: el formulario de rol funciona sin JavaScript (radios nativos por módulo); el JS solo agrega atajos («todo lectura», «todo sin acceso») y el aviso de usuarios afectados se renderiza en el servidor. |
| VI. Simplicidad | Una tabla de roles, una de permisos y una de historial; un servicio de autorización y un middleware; se reutilizan Gates, políticas, `Menu`, `x-form.*`, `x-ui.grilla`. Se evita una librería de permisos externa. Excepción justificada: un componente nuevo `x-form.matriz-permisos` (no existe equivalente). |
| VII. Diseño primero para celular | Cumple: la matriz de permisos se presenta como tarjetas apiladas por módulo con 3 opciones de ≥ 44 px; pantallas nuevas en `tests/movil/pantallas.mjs`. |

**Resultado de la puerta**: sin violaciones. La excepción de VI (componente nuevo) está justificada arriba; no requiere Seguimiento de Complejidad.

**Reevaluación tras el diseño (Fase 1)**: se mantiene. Riesgos fuera de la constitución quedan documentados en research (D3 sobre permisos actuales heredados, D4 sobre acciones reservadas, D8 sobre Organigrama para no administradores).

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/013-roles-permisos/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── gestion-roles.md
│   └── autorizacion-modulos.md
├── checklists/
│   └── requirements.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código Fuente (raíz del repositorio, `laravel_app/`)

```text
laravel_app/
├── database/
│   ├── migrations/2026_10_28_000001_create_roles_y_permisos.php   # roles, rol_permisos, rol_cambios + siembra de los 4 roles
│   └── factories/RolFactory.php
├── app/
│   ├── Models/{Rol,RolPermiso,RolCambio}.php                      # nuevos
│   ├── Models/User.php                                            # rol(), nivelEn(), alcance; esAdmin/tieneAccesoTotal/isLiderCelula reescritos
│   ├── Support/Modulos.php                                        # catálogo fijo de módulos (clave, etiqueta, icono, grupo, rutas)
│   ├── Support/Niveles.php                                        # 0 sin acceso, 1 lectura, 2 escritura + alcances
│   ├── Services/
│   │   ├── AutorizacionRoles.php                                  # nivel(User, módulo) por petición; jerarquía; techo de asignación
│   │   ├── RolService.php                                         # crear/editar/duplicar/eliminar con reglas e historial
│   │   └── AlcanceService.php                                     # lee el alcance del rol
│   ├── Policies/{PersonaPolicy,UserPolicy,SedePolicy}.php         # delegan en AutorizacionRoles
│   ├── Providers/AppServiceProvider.php                           # gates por módulo; Blade @puedeModulo
│   ├── Http/
│   │   ├── Middleware/AutorizaModulo.php                          # alias «modulo»
│   │   ├── Controllers/RolesController.php                        # nuevo (solo administrador)
│   │   ├── Controllers/UsersController.php                        # roles dinámicos, no escalada, último administrador
│   │   ├── Controllers/AuthController.php                         # Tablero filtrado por permisos
│   │   └── Requests/RolRequest.php
│   └── Grillas/RolesGrilla.php
├── config/menu.php                                                # cada opción declara su módulo
├── routes/web.php                                                 # grupos con ->middleware('modulo:…') + rutas de roles
├── resources/
│   ├── views/roles/{index,form,show}.blade.php
│   ├── views/components/form/matriz-permisos.blade.php
│   ├── views/users/index.blade.php                                # + pestañas Usuarios | Roles
│   ├── views/dashboard.blade.php                                  # tarjetas condicionadas
│   └── js/roles/{matriz.js,matriz.test.js}                        # atajos de la matriz (mejora progresiva)
└── tests/
    ├── Support/matriz-acceso-base.json                            # caracterización previa (generada antes del refactor)
    ├── Feature/Roles/{Caracterizacion,Matriz,Gestion,Escalada,Administrador,Migracion,Historial}Test.php
    ├── Feature/Aislamiento/MatrizRutasTest.php                    # + rutas de roles
    └── movil/{pantallas.mjs,roles.spec.js}
```

**Decisión de Estructura**: monolito Laravel existente. La regla de acceso vive en un único servicio (`AutorizacionRoles`) y se aplica por dos vías complementarias: middleware por módulo (red de seguridad contra dirección directa) y políticas/gates (acciones finas dentro de un módulo). Las políticas dejan de conocer nombres de rol.

## Seguimiento de Complejidad

Sin violaciones de la constitución que justificar.
