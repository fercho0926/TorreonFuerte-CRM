# Plan de Implementación: Claves de acceso generadas y entregadas por WhatsApp

**Rama**: `005-claves-por-whatsapp` | **Fecha**: 2026-10-19 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de la funcionalidad en `specs/005-claves-por-whatsapp/spec.md`

## Resumen

Dejar de asignar contraseñas a mano. Al crear un usuario (o al pedir una nueva clave) el sistema **genera una clave temporal aleatoria**, la deja como contraseña vigente de la cuenta marcada «debe cambiarla» y muestra **una sola vez** a quien administra un botón que abre WhatsApp (`wa.me`) con el mensaje ya redactado. La persona entra con esa clave, el sistema le exige elegir la suya y la temporal desaparece. En el login se añade «¿Olvidaste tu contraseña?», que registra una **solicitud** (respuesta siempre idéntica) que atiende un administrador o pastor de la sede desde un panel «Solicitudes de clave». Todo queda en un registro de claves sin guardar la clave. El envío es **asistido**, sin servicios externos ni costo.

Enfoque técnico (detalle en [research.md](research.md)):

- La clave temporal **es** la contraseña de la cuenta (hash en `users.password`, que ya existe) con `debe_cambiar_clave = true` y `clave_vence_en`; así el inicio de sesión actual la valida sin cambios y generar otra invalida la anterior.
- Dos tablas nuevas: `solicitudes_clave` (peticiones desde el login) y `registros_clave` (auditoría de cada generación, sin la clave), ambas con alcance por sede.
- Un servicio `ClavesTemporales` concentra generar, mensaje de WhatsApp, vigencia y registro; los controladores solo orquestan.
- La clave se muestra renderizando la respuesta del `POST` (nunca se guarda en sesión, base de datos ni logs) con `Cache-Control: no-store`.
- Un middleware `ExigeCambioDeClave` limita a quien tiene clave temporal a la pantalla «Elige tu contraseña» y a cerrar sesión.
- Se añade límite de intentos al inicio de sesión y a la recuperación (`RateLimiter`); hoy el login no lo tiene y es requisito de seguridad de FR-003/FR-006.
- No se toca el aislamiento por sede: se reutilizan `UserPolicy::gestionar`, el alcance de sede y `SedeContext`.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; Blade + AdminLTE 4 / Bootstrap 5 (spec 004); módulos JS con Vite

**Dependencias principales**: las existentes; sin paquetes nuevos (`RateLimiter` y `Hash` de Laravel, `random_int`)

**Almacenamiento**: SQLite en desarrollo, la base actual en producción; 2 tablas nuevas y 3 columnas en `users`; migraciones aditivas, sin tocar datos existentes

**Pruebas**: PHPUnit 12 (funcionalidad), Vitest 5 solo si se añade un módulo JS (botón «copiar mensaje»)

**Plataforma de destino**: aplicación web Laravel en hosting compartido (cPanel); sesión propia (`is_logged_in`, `user_id`), no `Auth` de Laravel

**Tipo de proyecto**: aplicación web monolítica

**Objetivos de rendimiento**: generar y mostrar la clave en < 1 s; el panel de solicitudes carga en < 1 s con 200 solicitudes (paginado)

**Restricciones**: no almacenar la clave en claro en ningún sitio (BD, sesión, logs, bitácora); respuesta de recuperación idéntica exista o no la cuenta; sin servicios externos de pago; textos en español; aislamiento por sede intacto

**Escala/Alcance**: decenas de sedes, cientos de usuarios; pocas solicitudes por día

## Verificación de la Constitución

`.specify/memory/constitution.md` sigue siendo la plantilla sin completar; se aplican los acuerdos vigentes del proyecto:

| Criterio | Estado |
|----------|--------|
| Artefactos de spec-kit en español | Cumple |
| Pruebas existentes siguen en verde (PHPUnit 254, Vitest 49), salvo las que escribían contraseñas en el formulario de usuarios, que se ajustan | Se verifica por fase |
| Aislamiento por sede y roles sin cambios | Cumple: se reutilizan políticas y alcance |
| Migraciones aditivas; nunca `migrate:fresh` sobre la base local | Cumple |
| No guardar secretos en claro | Cumple por diseño (ver research D2–D4) |

Reevaluación tras el diseño: sin violaciones.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/005-claves-por-whatsapp/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── rutas-y-pantallas.md
├── checklists/requirements.md
└── tasks.md              # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio)

```text
laravel_app/
├── app/
│   ├── Models/
│   │   ├── SolicitudClave.php            # NUEVO (alcance por sede)
│   │   ├── RegistroClave.php             # NUEVO (alcance por sede)
│   │   └── User.php                      # +celular, +debe_cambiar_clave, +clave_vence_en
│   ├── Services/ClavesTemporales.php     # NUEVO: generar, vigencia, mensaje, enlace wa.me, registro
│   ├── Support/Celular.php               # NUEVO: normaliza celular y número para wa.me (extrae de Persona)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── RecuperacionClaveController.php  # NUEVO: login → solicitud
│   │   │   ├── SolicitudesClaveController.php   # NUEVO: panel, atender, descartar
│   │   │   ├── ClavesUsuarioController.php      # NUEVO: generar clave y marcar «enviada»
│   │   │   ├── CuentaController.php             # NUEVO: «Elige tu contraseña» y «Mi cuenta»
│   │   │   ├── UsersController.php              # sin campos de contraseña; usa el servicio
│   │   │   └── AuthController.php               # límite de intentos; clave vencida
│   │   ├── Middleware/ExigeCambioDeClave.php    # NUEVO
│   │   └── Requests/                            # reglas de contraseña y celular
│   └── Policies/UserPolicy.php           # +gestionarClaves, +verSolicitudesClave
├── config/claves.php                     # NUEVO: vigencias, límites, longitud
├── database/migrations/                  # columnas de users + 2 tablas nuevas
├── resources/views/
│   ├── auth/{olvide-clave,cambiar-clave}.blade.php
│   ├── cuenta/mi-cuenta.blade.php
│   ├── claves/{solicitudes,mostrar}.blade.php
│   └── users/{_form,create,edit,index}.blade.php   # sin contraseñas; estado de clave
├── config/menu.php                       # opción «Solicitudes de clave» (con insignia)
├── routes/web.php
└── tests/Feature/Claves/                 # NUEVO
```

**Decisión de estructura**: monolito Laravel existente; se añaden un servicio, dos modelos, cuatro controladores pequeños, un middleware y vistas.

## Seguimiento de Complejidad

Sin violaciones que justificar.
