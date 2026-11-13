# Plan de Implementación: Selector de idioma en la interfaz (español/inglés)

**Rama**: `020-selector-idioma-interfaz` | **Fecha**: 2026-11-13 | **Spec**: [spec.md](./spec.md)

**Entrada**: Especificación de funcionalidad desde `specs/020-selector-idioma-interfaz/spec.md`

## Resumen

El CRM debe ofrecer un control interactivo (por ejemplo una bandera) para que cada usuario elija entre español e inglés en las pantallas internas del sistema, sin reescribir a mano el texto existente. El enfoque técnico aprovecha el sistema de localización nativo de Laravel (`lang/{locale}/*.php`, `__()`, `App::setLocale()`), que el proyecto ya tiene configurado (`APP_LOCALE`, `APP_FALLBACK_LOCALE`) pero sin usar: hoy ninguna de las 139 vistas Blade usa `__()` y el texto en español está escrito directamente en ellas. Un script de extracción (ejecutado una sola vez, no una dependencia nueva en producción) moverá ese texto a `lang/es/interfaz.php` conservando el contenido exacto, generará `lang/en/interfaz.php` con la traducción, y reemplazará el texto literal por `__('interfaz.clave')`. La preferencia de idioma se guarda en el perfil del usuario (columna `idioma` en `users`) y un middleware nuevo, siguiendo el mismo patrón que `ResuelveSede`, aplica `App::setLocale()` en cada petición.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13

**Dependencias Principales**: Ninguna dependencia nueva en producción. Se usa el sistema de localización nativo de Laravel (`Illuminate\Support\Facades\Lang`, `App::setLocale()`, helper `__()`); Blade; AdminLTE 4 sobre Bootstrap 5; DataTables 2 (para traducir los textos fijos de las grillas estándar, principio III).

**Almacenamiento**: Igual que el resto del proyecto (MySQL/MariaDB en producción, SQLite en pruebas, vía Eloquent). Se agrega la columna `idioma` a la tabla `users` existente.

**Pruebas**: PHPUnit (`php artisan test`) para el servidor; `tests/movil` (Vitest/`npm run test:movil`) para verificar el tamaño táctil del nuevo control en celular.

**Plataforma Objetivo**: Navegador web (escritorio y celular), mismo hosting cPanel actual.

**Tipo de Proyecto**: Aplicación web monolítica Laravel + Blade (sin frontend separado).

**Metas de Rendimiento**: El cambio de idioma no debe añadir una recarga perceptible adicional respecto a cualquier otra navegación normal del sitio (sigue el patrón ya existente del selector de sede activa).

**Restricciones**: DEBE funcionar sin JavaScript (principio V); el control DEBE cumplir el tamaño táctil mínimo de 44×44 px (principio VII); el texto en español existente NO se reescribe a mano (RF-005); toda pantalla sin traducción al inglés DEBE mostrar el español como respaldo (RF-006), aprovechando `fallback_locale` de Laravel.

**Escala/Alcance**: 139 vistas Blade (uso interno: administrador, pastor, líder de célula, usuario). Fuera de alcance: pantallas públicas de registro/asistencia y mensajes generados fuera de pantalla (WhatsApp, notificaciones, exportaciones), según RF-009 y RF-010.

## Verificación de la Constitución

*GATE: Debe aprobarse antes de la investigación de la Fase 0. Se vuelve a revisar después del diseño de la Fase 1.*

| Principio | Evaluación |
|---|---|
| I. Artefactos en español | Cumple: este plan y el resto de los artefactos de spec-kit están en español. |
| II. Aislamiento por sede y alcance por rol | No aplica un riesgo nuevo: el idioma es una preferencia propia de cada usuario, no expone ni cruza datos entre sedes o roles. |
| III. Grillas estándar | Cumple con una tarea explícita: los textos fijos de DataTables (buscar, filas por página, paginador) deben traducirse según el idioma activo para que la grilla siga mostrando el 100% de su texto en el idioma elegido (RF-002). |
| IV. Pruebas primero | Cumple: se agregan pruebas PHPUnit en `tests/Feature/Interfaz/` antes de dar por cerrada la funcionalidad (cambio de idioma, persistencia, respaldo a español). |
| V. Mejora progresiva | Cumple de forma más estricta que el precedente: el selector de sede activa depende de `onchange` + JavaScript; el nuevo control de idioma se implementa con botones de envío reales (`<button type="submit">`) para funcionar también sin JavaScript (RF-007). |
| VI. Simplicidad | Cumple: se reutiliza el mecanismo nativo de Laravel y el mismo patrón de middleware que `ResuelveSede`, sin crear un servicio nuevo tipo `SedeContext`. El mini-formulario de cambio de idioma sigue el mismo precedente ya aceptado del selector de sede activa (control de navegación, no un formulario de datos de los componentes `x-form.*`). |
| VII. Diseño primero para celular | Cumple: el control se diseña con tamaño táctil ≥44×44 px y se valida con `npm run test:movil`; no se guarda en el teléfono ningún dato nuevo de personas/eventos (el idioma es una preferencia de interfaz, no un dato sensible). |

No hay violaciones que requieran justificación en Seguimiento de Complejidad.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/020-selector-idioma-interfaz/
├── plan.md              # Este archivo (salida de /speckit-plan)
├── research.md           # Salida de la Fase 0
├── data-model.md         # Salida de la Fase 1
├── quickstart.md         # Salida de la Fase 1
├── contracts/            # Salida de la Fase 1
│   └── idioma-route.md
└── tasks.md              # Salida de /speckit-tasks (no se crea en este comando)
```

### Código Fuente (raíz del repositorio)

```text
laravel_app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── IdiomaController.php         # Nuevo: guarda la preferencia de idioma del usuario
│   │   └── Middleware/
│   │       └── EstableceIdioma.php          # Nuevo: aplica App::setLocale() en cada petición web
│   └── Models/
│       └── User.php                          # Modificado: nuevo campo `idioma` (historial, fillable)
├── bootstrap/app.php                          # Modificado: registra EstableceIdioma en la pila `web`
├── database/migrations/
│   └── 2026_11_13_000000_add_idioma_a_users_table.php   # Nuevo
├── lang/
│   ├── es/
│   │   └── interfaz.php                       # Nuevo: textos extraídos de las vistas, en español (sin cambios)
│   └── en/
│       └── interfaz.php                       # Nuevo: traducción al inglés de las mismas claves
├── resources/views/
│   ├── layouts/admin.blade.php                # Modificado: agrega el control de idioma en el header
│   └── **/*.blade.php                         # Modificadas (139 archivos): texto literal → __('interfaz.clave')
├── routes/web.php                             # Modificado: nueva ruta `idioma.update`
└── tests/
    └── Feature/Interfaz/
        └── SelectorIdiomaTest.php              # Nuevo
```

**Decisión de Estructura**: Proyecto único (Laravel monolítico con Blade), sin frontend separado. No se crean módulos ni servicios nuevos aparte del middleware y el controlador indicados; se reutiliza el mecanismo de localización que Laravel ya trae integrado.

## Seguimiento de Complejidad

No aplica: la Verificación de la Constitución no encontró violaciones que requieran justificación.
