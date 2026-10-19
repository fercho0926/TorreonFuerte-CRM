# Plan de implementación: Multi-sede (varias iglesias en un mismo CRM)

**Rama**: `002-multi-sede-iglesias` | **Fecha**: 2026-10-19 | **Spec**: [spec.md](spec.md)

**Entrada**: Especificación de la funcionalidad en `/specs/002-multi-sede-iglesias/spec.md`

## Resumen

Se convierte la aplicación Laravel existente (`laravel_app/`) en un sistema **multi-sede en una sola base de datos**:

- Nueva entidad **Sede**; cada tabla de datos de la iglesia lleva su `sede_id` y **un filtro global automático** hace que toda consulta, selector, búsqueda y enlace directo quede limitado a la sede del usuario (falla cerrado: sin sede no se ve nada).
- **Dos niveles de acceso**: el admin ve todo (con "sede activa" o "Todas las sedes" solo para consultar); pastor, pastora, líderes y usuarios pertenecen a una sola sede.
- **Módulo exclusivo del admin** para crear sedes (con pastor y pastora en una sola operación atómica), editarlas, cambiar pastores y desactivarlas; con bitácora y resumen global.
- **Migración** de los datos actuales a una primera sede sin pérdida, conservando credenciales.
- **Traslado de personas** entre sedes sin cruzar historial: se crea el registro en la sede nueva y el de origen queda inactivo con su historia.
- **Pruebas de aislamiento sistemáticas** (por módulo y una matriz que recorre todas las rutas) para garantizar que ninguna pantalla filtre datos entre sedes.

## Contexto técnico

**Lenguaje/Versión**: PHP ^8.3

**Dependencias principales**: Laravel ^13.8, Blade + AdminLTE (sin dependencias nuevas)

**Almacenamiento**: Una sola base (SQLite en desarrollo, MySQL en cPanel) con columna `sede_id` en cada tabla de datos; migraciones compatibles con ambos motores.

**Pruebas**: PHPUnit ^12.5; hoy 54 pruebas (módulo Personas y Líderes). Se añaden pruebas de aislamiento, de roles y de migración.

**Plataforma objetivo**: Aplicación web server-rendered en hosting compartido (cPanel); sin subdominios por sede (fuera de alcance).

**Tipo de proyecto**: Aplicación web monolítica Laravel (sesión propia, no `Auth`).

**Objetivos de rendimiento**: listados en menos de 2 s con 1.000 personas por sede y con 20+ sedes en la misma base (índices por `sede_id`).

**Restricciones**: sin pérdida de datos al migrar; ningún modelo de tenant sin filtro global; las migraciones deben poder correr sobre la base actual; el correo de inicio de sesión sigue siendo único en todo el sistema.

**Escala/Alcance**: 3 sedes iniciales, decenas esperadas; 9 tablas de datos con `sede_id`; ~10 controladores a revisar; 1 módulo nuevo (Sedes) y 1 cambio en Usuarios.

## Verificación contra la constitución

`.specify/memory/constitution.md` sigue siendo la plantilla sin completar: **no hay principios que evaluar ni puertas que fallar**. Se aplican como principios de trabajo los de la spec: aislamiento que falla cerrado, cero pérdida de datos y simplicidad. Recomendación: ejecutar `/speckit-constitution` y fijar como principio "toda tabla de la iglesia lleva `sede_id` y filtro global".

Re-evaluación tras el diseño: sin cambios; el diseño no introduce complejidad que requiera justificación (se evita una base por sede justamente por simplicidad operativa).

## Estructura del proyecto

### Documentación (esta funcionalidad)

```text
specs/002-multi-sede-iglesias/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── rutas-ui.md
└── tasks.md             # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio)

```text
laravel_app/
├── app/
│   ├── Models/
│   │   ├── Sede.php                      # nuevo (sin filtro global)
│   │   ├── SedeCambio.php                # nuevo (bitácora de sedes)
│   │   ├── Concerns/PerteneceASede.php   # nuevo: trait con filtro global y asignación automática
│   │   ├── Scopes/SedeScope.php          # nuevo: filtro global (falla cerrado)
│   │   └── (Persona, Red, Zona, Celula, CelulaReunion, Encuentro, Evento, EventoPersona,
│   │        EventoPago, Ministerio, PersonaCambio, User usan el trait)
│   ├── Services/
│   │   ├── SedeContext.php               # nuevo: sede efectiva del usuario / sede activa del admin
│   │   ├── SedeService.php               # nuevo: alta atómica, cambio de pastor/pastora, desactivar
│   │   └── TrasladoService.php           # nuevo: traslado de personas entre sedes
│   ├── Policies/
│   │   ├── SedePolicy.php                # nuevo: solo admin
│   │   └── PersonaPolicy.php             # + trasladar; (gestión de usuarios en UserPolicy)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── SedesController.php       # nuevo: módulo solo admin
│   │   │   ├── SedeActivaController.php  # nuevo: cambiar sede activa del admin
│   │   │   ├── TrasladosController.php   # nuevo: trasladar persona
│   │   │   ├── UsersController.php       # sede por usuario, pastor gestiona su sede
│   │   │   ├── AuthController.php        # bloquea sedes inactivas; dashboard por sede / global
│   │   │   └── (resto de controladores sin lógica de tenant: lo hace el filtro global)
│   │   ├── Middleware/
│   │   │   └── ResuelveSede.php          # nuevo: valida sede activa/inactiva y bloquea escrituras sin sede
│   │   └── Requests/SedeRequest.php      # nuevo
├── database/
│   ├── migrations/                       # sedes + sede_id en 12 tablas + índices + migración de datos
│   └── seeders/DatabaseSeeder.php        # 3 sedes de ejemplo
├── resources/views/
│   ├── sedes/ (index, create, edit, show, pastores)
│   ├── traslados/create.blade.php
│   ├── dashboard.blade.php               # resumen global por sede (admin)
│   └── layouts/admin.blade.php           # nombre de sede + selector de sede activa
├── routes/web.php
└── tests/
    ├── Feature/
    │   ├── Sedes/ (SedesAdminTest, AltaSedeAtomicaTest, PastoresSedeTest, DesactivarSedeTest)
    │   ├── Aislamiento/ (AislamientoPersonasTest, AislamientoCelulasTest, AislamientoEventosTest,
    │   │                 AislamientoMinisteriosTest, AislamientoUsuariosTest, MatrizRutasTest)
    │   ├── SedeActivaAdminTest.php, TrasladoPersonaTest.php, MigracionSedesTest.php
    └── Unit/ (SedeScopeTest, ModelosConSedeTest)
```

**Decisión de estructura**: se mantiene el monolito. El aislamiento vive en **un solo lugar** (trait + filtro global + middleware) en lugar de repetir condiciones en cada controlador; así un módulo nuevo o futuro queda aislado con solo usar el trait, y una prueba automática falla si una tabla con `sede_id` no lo usa.

## Seguimiento de complejidad

Sin violaciones de la constitución que justificar.
