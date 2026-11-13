# Investigación: Selector de idioma en la interfaz (español/inglés)

No quedan marcadores [NEEDS CLARIFICATION] en la especificación (las 3 preguntas se resolvieron durante `/speckit-specify`). Esta fase documenta las decisiones técnicas tomadas para el Contexto Técnico del plan.

## 1. Mecanismo de localización

**Decisión**: Usar el sistema de localización nativo de Laravel (`lang/{locale}/*.php`, helper `__()`, `Illuminate\Support\Facades\App::setLocale()`), en vez de un paquete externo de traducción automática o un servicio de traducción en tiempo real.

**Justificación**: El proyecto ya tiene `APP_LOCALE` y `APP_FALLBACK_LOCALE` configurados en `config/app.php` pero sin usar. Es la forma soportada y documentada por el framework, resuelve el respaldo a español de forma automática con `fallback_locale` (RF-006) y no agrega una dependencia nueva ni un costo recurrente de servicio externo. Es coherente con el principio de Simplicidad (VI).

**Alternativas consideradas**:
- `mcamara/laravel-localization`: resuelve URLs por idioma (`/en/...`, `/es/...`) y detección automática por navegador, pero no traduce contenido por sí solo y está orientado a rutas públicas, que quedan fuera de alcance (RF-009). Se descarta para esta funcionalidad.
- Traductor JS en vivo (tipo Google Translate embebido): descartado ya en la fase de especificación por mala calidad en términos del dominio (sedes, niveles, roles) y porque no funcionaría sin JavaScript (RF-007).

## 2. Extracción del texto existente sin reescritura manual

**Decisión**: Un script de migración de código, ejecutado una sola vez (no una dependencia de producción), que recorre `resources/views/**/*.blade.php`, detecta el texto literal en español fuera de código PHP/atributos técnicos, lo mueve a `lang/es/interfaz.php` **con el texto exacto que tiene hoy** bajo una clave legible, reemplaza ese texto en la vista por `__('interfaz.clave')`, y genera `lang/en/interfaz.php` con la traducción al inglés de cada clave.

**Justificación**: Cumple RF-005 (el español existente no se reescribe a mano: se mueve tal cual) y permite que el inglés se añada de forma incremental. El resultado visual en español es idéntico al actual.

**Alternativas consideradas**:
- Traducir vista por vista a mano: es exactamente el trabajo que el usuario pidió evitar.
- Envolver solo los textos de los controladores/mensajes flash: insuficiente, la gran mayoría del texto vive directamente en los Blade (139 archivos, 0 usan `__()` hoy).

## 3. Persistencia de la preferencia de idioma

**Decisión**: Columna `idioma` (string corto, valores `es` o `en`, por defecto `es`) en la tabla `users`, leída por un middleware nuevo `EstableceIdioma` que llama a `App::setLocale()` al inicio de cada petición web, agregado a la pila `web` en `bootstrap/app.php`.

**Justificación**: Ya resuelto en la especificación (RF-003: perfil del usuario, no cookie, para que el idioma sea el mismo en cualquier dispositivo). Sigue el mismo patrón que `ResuelveSede`, que ya se agrega como middleware `web` y lee datos del usuario autenticado en cada petición.

**Alternativas consideradas**:
- Cookie o sesión por navegador: descartado por la especificación (RF-003 exige persistencia en el perfil).
- Paquete de terceros para "locale por usuario": innecesario, Laravel ya expone `App::setLocale()` de forma nativa.

## 4. Control de idioma en la interfaz

**Decisión**: Dos botones de envío reales (bandera o texto "ES"/"EN") en el header de `layouts/admin.blade.php`, cada uno dentro de un `<form method="POST">` a una ruta `idioma.update`, sin depender de `onchange` + JavaScript.

**Justificación**: Cumple RF-001 (control visible e interactivo), RF-007 (debe funcionar sin JavaScript) y RF-008 (tamaño táctil ≥44×44 px). El selector de sede activa existente usa `<select onchange="this.form.submit()">`, que requiere JavaScript; para el idioma se evita ese precedente y se usa un botón de envío normal, que funciona con o sin JavaScript.

**Alternativas consideradas**:
- Replicar el `<select onchange=...>` del selector de sede: descartado porque no cumple RF-007 de forma estricta sin JavaScript.
- Menú desplegable que dependa de JavaScript para abrirse: descartado por la misma razón.

## 5. Textos fijos de las grillas estándar (DataTables)

**Decisión**: Los textos fijos de DataTables (buscar, filas por página, "Mostrando X de Y", paginador) se configuran según el idioma activo de la sesión, usando el soporte de idioma que DataTables ya trae (arreglo de idioma embebido o `language.url` según el idioma actual), en vez de dejarlos siempre en español.

**Justificación**: El principio III exige que toda grilla estándar siga cumpliendo su contrato (incluye estos textos). Sin esta tarea, una pantalla con grilla quedaría con su contenido en inglés pero los controles de la grilla en español, incumpliendo RF-002 ("todos los textos de la interfaz").

**Alternativas consideradas**:
- Dejar los textos de DataTables siempre en español: descartado, viola RF-002.

## 6. Estrategia de pruebas

**Decisión**: Pruebas PHPUnit nuevas en `tests/Feature/Interfaz/` que cubran: (a) el cambio de idioma vía `idioma.update` persiste en el usuario y afecta la siguiente petición; (b) una clave sin traducción al inglés cae de vuelta al español (`fallback_locale`) en lugar de mostrar la clave cruda o un texto vacío; (c) la ruta `idioma.update` responde correctamente a una petición normal sin cabeceras de JavaScript/AJAX. Se valida además con `npm run test:movil` que el nuevo control cumple el tamaño táctil mínimo.

**Justificación**: Principio IV (pruebas primero) y VII (diseño primero para celular).

**Alternativas consideradas**: Ninguna; es la aplicación directa de los principios ya establecidos en el proyecto.
