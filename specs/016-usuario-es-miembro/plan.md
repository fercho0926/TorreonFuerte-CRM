# Plan de Implementación: Todo usuario es un miembro y el pastor encabeza el liderazgo

**Rama**: `016-usuario-es-miembro` (la rama git de trabajo sigue siendo `modulos`) | **Fecha**: 2026-10-23 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación en `/specs/016-usuario-es-miembro/spec.md`

## Resumen

Dos reglas de negocio:

1. **Una sola persona**: toda cuenta de usuario pertenece a exactamente un miembro, y el nombre y el celular solo existen en el miembro.
2. **El pastor es la cabeza**: todo líder nuevo o designado queda bajo el pastor de su sede, y solo el pastor y la pastora no tienen líder.

Enfoque técnico:
- Se eliminan `users.name` y `users.celular`. `User` los expone como atributos de solo lectura tomados del miembro, y `users.persona_id` pasa a ser obligatorio y único.
- «Nuevo usuario» se vuelve un flujo de dos pasos sin JavaScript. Primero se busca al miembro por el celular normalizado, guardado en la columna nueva `personas.celular_clave`. Si existe, se vincula; si no, se crean el miembro y la cuenta en una sola transacción, con el servicio nuevo `CuentasDeMiembros`.
- Una migración de datos idempotente unifica las cuentas existentes y deja los casos dudosos marcados para revisión del administrador.
- `LiderazgoService` concentra la regla del pastor: líder por defecto, prohibición de dejar líderes sin líder, pastor sin líder y reasignación al cambiar de pastor. Otra migración de datos corrige los líderes que hoy no tienen líder.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13 (Blade). Sin JavaScript nuevo.

**Dependencias Principales**: las ya instaladas (Bootstrap 5, AdminLTE 4, DataTables 2 vía la grilla estándar). No se agregan dependencias.

**Almacenamiento**: SQLite local y en pruebas, MySQL en producción. Cuatro migraciones (ver [data-model.md](data-model.md)):
- columnas `personas.celular_clave` y `users.revision_vinculo`;
- unificación de cuentas (datos);
- pastor como cabeza (datos);
- eliminación de `users.name` y `users.celular`, con `users.persona_id` `NOT NULL`, `UNIQUE` y `restrictOnDelete`.

**Pruebas**:
- PHPUnit 12, nueva carpeta `tests/Feature/CuentasDeMiembros/` y nuevas pruebas en `tests/Feature/Lideres/`. Se ajustan `UserFactory` y unos 30 usos de la fábrica en 22 archivos.
- Playwright (`npm run test:movil`) con los dos pasos de `users/create`.

**Plataforma de Destino**: aplicación web; uso principal en celular (principio VII).

**Tipo de Proyecto**: aplicación web monolítica Laravel con Blade.

**Metas de Rendimiento**:
- La búsqueda por celular es una consulta por igualdad sobre un índice: respuesta inmediata con miles de miembros por sede.
- Listados de usuarios sin consultas N+1 (`$with = ['persona']`).
- La unificación procesa las cuentas por lotes (`chunkById`).

**Restricciones**:
- Ninguna cuenta sin miembro y ninguna persona duplicada (RF-001, RF-002, CE-002).
- La búsqueda no revela datos de otra sede (principio II).
- Funciona sin JavaScript.
- Nunca `migrate:fresh` sobre la base local; las migraciones de datos son idempotentes y la eliminación de columnas se detiene si queda alguna cuenta sin miembro.

**Escala/Alcance**:
- 2 modelos y 4 servicios modificados, 1 servicio nuevo, 1 comando.
- 4 controladores tocados (usuarios, miembros, líderes, sedes), 2 grillas y unas 6 vistas.
- Decenas de cuentas y miles de miembros por sede.

## Verificación de la Constitución

Constitución vigente v1.1.0 (`.specify/memory/constitution.md`):

| Principio | Evaluación |
|-----------|------------|
| I. Artefactos en español | Cumple. |
| II. Aislamiento por sede y alcance por rol (no negociable) | **Cumple con una excepción menor declarada.** La búsqueda de miembros usa `AlcanceService::personasVisibles()` dentro de `SedeContext`. Para no duplicar a una persona de otra sede (RF-002), se comprueba la **existencia** del celular sin filtro de sede y solo se responde con un aviso genérico, sin nombre, sede ni ningún dato (research D8). Una prueba lo verifica. La relación `User::persona()` sin filtro de sede solo se usa para leer el miembro **de la propia cuenta**. |
| III. Grillas estándar | Cumple: `UsuariosGrilla` y `SolicitudesClaveGrilla` siguen siendo estándar. Cambian el orden y el filtro de «Nombre» (subconsulta sobre `personas`) y se agrega el filtro «Vínculo» (solo administrador). La exportación de usuarios sigue restringida al administrador. |
| IV. Pruebas primero | Cumple: cada regla (búsqueda, alta atómica, unicidad, unificación, pastor cabeza, cambio de pastor, traslado) tiene su prueba escrita antes. Hay pruebas de invariantes del árbol de liderazgo sobre los datos sembrados. |
| V. Mejora progresiva | Cumple: el flujo de dos pasos usa un formulario `GET` y validación completa en el servidor; no se agrega JavaScript. |
| VI. Simplicidad | Cumple: se reutilizan `PersonaRequest` (reglas extraídas), `LiderazgoService` (validación de ciclos e historial), `Historial`, `AlcanceService`, `SedeContext`, `Celular` y los componentes `x-form.*`. Solo se agregan un servicio (`CuentasDeMiembros`), un componente pequeño (`x-form.radio`) y un comando. |
| VII. Diseño primero para celular | Cumple: los dos pasos son de una columna, el campo celular usa `inputmode="tel"`, las tarjetas y los botones de opción tienen 44 px y las pantallas nuevas entran en `npm run test:movil` sin excepciones. |

Resultado: **APROBADO** con una excepción menor justificada (ver «Seguimiento de Complejidad»). Reevaluación tras el diseño (data-model y contratos): aprobada, sin cambios.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/016-usuario-es-miembro/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── rutas-y-reglas.md
├── checklists/requirements.md
└── tasks.md          # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio `laravel_app/`)

```text
app/
├── Models/
│   ├── User.php                      # − name/celular en Fillable; accesores name, celular; persona() sin filtro de sede; $with
│   └── Persona.php                   # + celular_clave (saving), cuenta(), esPastoral()
├── Services/
│   ├── CuentasDeMiembros.php         # NUEVO: buscarPorCelular, crear, vincular, unificar
│   ├── LiderazgoService.php          # + liderPorDefecto, reasignarDependientes; reglas de pastor y líder obligatorio
│   ├── SedeService.php               # crearCuentaPastoral/vincularPersona sobre CuentasDeMiembros; cambiarPastor (D10)
│   ├── TrasladoService.php           # mueve la cuenta al miembro nuevo (D13)
│   ├── OrganigramaService.php        # raíces = líderes bajo el pastor o la pastora (D12)
│   └── ClavesTemporales.php          # sin cambios funcionales (lee $usuario->name y ->celular por los accesores)
├── Support/Celular.php               # + clave()
├── Console/Commands/UnificarUsuarios.php   # NUEVO: miembros:unificar-usuarios
├── Grillas/
│   ├── UsuariosGrilla.php            # orden y filtro por miembro; filtro «Vínculo»
│   └── SolicitudesClaveGrilla.php    # orden y búsqueda por miembro
├── Http/
│   ├── Controllers/
│   │   ├── UsersController.php       # create en dos pasos, store con CuentasDeMiembros, edit/update solo cuenta
│   │   ├── PersonasController.php    # designar líder al crear/editar → pastor; borrar con cuenta prohibido
│   │   ├── LideresController.php     # mensaje con el pastor; error si la sede no tiene pastor
│   │   ├── SedesController.php, RolesController.php   # orderBy por nombre del miembro
│   │   ├── AuthController.php, CuentaController.php   # sin cambios funcionales (Session user_name vía accesor)
│   └── Requests/PersonaRequest.php   # reglas extraídas para reutilizar; líder obligatorio; celular único entre cuentas
├── Policies/PersonaPolicy.php        # no se elimina un miembro con cuenta
database/
├── migrations/
│   ├── 2026_11_01_000001_add_celular_clave_y_revision_vinculo.php
│   ├── 2026_11_01_000002_unificar_usuarios_con_miembros.php
│   ├── 2026_11_01_000003_pastor_cabeza_del_liderazgo.php
│   └── 2026_11_01_000004_users_persona_obligatoria.php
├── factories/UserFactory.php         # crea siempre su miembro; estado conMiembro([...])
├── factories/PersonaFactory.php      # estado pastorDe($sede)
└── seeders/DatabaseSeeder.php        # cuentas sembradas con su miembro; pastores como cabeza
resources/views/
├── users/create.blade.php            # paso 1 (celular) y paso 2 (según estado)
├── users/_form.blade.php             # solo campos de cuenta
├── users/edit.blade.php              # miembro vinculado + enlace; revisión de vínculo (administrador)
├── components/form/radio.blade.php   # NUEVO
└── personas/show.blade.php           # aviso «tiene cuenta» en lugar del botón eliminar
tests/
├── Feature/CuentasDeMiembros/
│   ├── BuscarMiembroPorCelularTest.php
│   ├── CrearUsuarioConMiembroTest.php
│   ├── UnaSolaPersonaTest.php
│   ├── UnificarUsuariosTest.php
│   ├── RevisionVinculoTest.php
│   ├── TrasladoConCuentaTest.php
│   └── CuentasDeMiembrosAislamientoTest.php
├── Feature/Lideres/
│   ├── PastorCabezaTest.php           # designar/crear → pastor; líder sin líder prohibido; pastor sin líder
│   ├── CambioDePastorTest.php
│   └── InvariantesLiderazgoTest.php
├── Unit/CelularClaveTest.php
└── movil/pantallas.mjs               # + users/create?celular=… (encontrado y nuevo)
```

**Decisión de estructura**: monolito Laravel existente. La lógica nueva va en un solo servicio (`CuentasDeMiembros`) y en `LiderazgoService`, que ya es dueño de las reglas de liderazgo. Los controladores solo orquestan.

## Riesgos y mitigación

| Riesgo | Mitigación |
|--------|------------|
| Eliminar `users.name` y `users.celular` rompe consultas SQL que los usan | Inventario hecho (grillas, `orderBy('name')` en Sedes y Roles). Una prueba busca `users.name` y `'name'` en consultas de `app/` y falla si quedan. Los accesores cubren las lecturas de PHP y Blade. |
| La migración destructiva corre con cuentas sin miembro | La migración 4 verifica que el conteo sea 0 y se detiene con un mensaje si no lo es. `down()` recrea las columnas desde el miembro. |
| Casos de unificación mal vinculados | Solo se vincula automáticamente con una coincidencia única y con alguna palabra del nombre en común. El resto queda «Por revisar», y desvincular un caso es reversible desde la edición. |
| Pruebas existentes que crean usuarios con `name` o `celular` | `UserFactory` traslada esos atributos al miembro (`conMiembro`). Se ajustan los 11 lugares con `'name' =>`. |
| Pastor de la sede no marcado como líder hoy | Lo corrige la migración 3, y `SedeService` lo mantiene en adelante. |

## Seguimiento de Complejidad

| Excepción | Por qué se necesita | Alternativa más simple descartada porque |
|-----------|---------------------|------------------------------------------|
| Comprobación de existencia de un celular fuera de la sede del actor (principio II) | Sin ella, un pastor crearía un duplicado de una persona registrada en otra sede, lo que contradice RF-002 y CE-002. | Ignorar las otras sedes duplica personas. Mostrar el miembro de la otra sede expone datos ajenos. El aviso genérico es lo mínimo. |
