# Plan de Implementación: Registro por código QR a eventos, encuentros y células

**Rama**: `modulos` (directorio `009-registro-qr-eventos`) | **Fecha**: 2026-10-22 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de `/specs/009-registro-qr-eventos/spec.md`

## Resumen

Cada evento, encuentro y célula recibe un enlace de registro no adivinable (`/registro/{token}`) y su QR. Ese enlace abre un formulario **público y sin sesión**, pensado primero para celular: la persona escribe su celular (y opcionalmente su cédula), el sistema la busca en Personas de la sede del destino, precarga sus datos o pide los obligatorios (nombres, apellidos, celular, fecha de nacimiento) y la inscribe sin crear duplicados.

Enfoque técnico (ver [research.md](research.md)):
- El destino se resuelve por token con `SedeContext::sinFiltro()` y todo el trabajo posterior corre dentro de `SedeContext::conSede()` de la sede del destino, así el aislamiento por sede sigue aplicándose en la consulta (principio II).
- Una persona **de otra sede** nunca se toca: se guarda solo un registro en una tabla nueva `registros_qr` (nombre y celular escritos), visible para la sede del destino como «de otra sede».
- La inscripción real reutiliza las tablas existentes (`evento_persona`, `encuentro_persona`, `celula_persona`), y `registros_qr` deja la traza del origen QR, la fecha y hora y las correcciones como nota.
- QR generado en el servidor como SVG con una librería PHP pura (sin extensiones del hosting).

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13

**Dependencias principales**: Blade, AdminLTE 4/Bootstrap 5, Vite; nueva: `bacon/bacon-qr-code` (SVG en PHP puro)

**Almacenamiento**: base de datos relacional existente; 1 tabla nueva (`registros_qr`) y 3 columnas nuevas en `eventos`, `encuentros` y `celulas`

**Pruebas**: PHPUnit (aislamiento, flujo público, límite de frecuencia), Vitest (precarga por cédula), `npm run test:movil`

**Plataforma de destino**: web móvil primero; despliegue en cPanel (vendor se sube o se instala con `composer install`)

**Tipo de proyecto**: aplicación web monolítica Laravel (`laravel_app/`)

**Metas de rendimiento**: registro de persona nueva en < 60 s y de persona existente en < 30 s (SC-001, SC-002); consulta de búsqueda por cédula con respuesta imperceptible (< 1 s)

**Restricciones**: sin sesión en el formulario público; sin datos de personas en caché del teléfono; funciona sin JavaScript; respuestas JSON con error en JSON (la ruta de precarga no es `api/*`)

**Escala/Alcance**: 1 formulario público, 1 endpoint de precarga, 1 panel «Registrados por QR» en 3 fichas, ~3 migraciones

## Verificación de la Constitución

| Principio | Cumplimiento |
|---|---|
| I. Artefactos en español | Sí. |
| II. Aislamiento por sede y rol | Sí, con cuidado: la ruta pública no tiene usuario, así que se trabaja con `conSede()` y el token resuelve la sede. Prueba obligatoria: la persona de otra sede no se precarga, no se modifica y no aparece con enlace. La matriz de rutas (`MatrizRutasTest`) debe cubrir las rutas nuevas. |
| III. Grillas estándar | El panel «Registrados por QR» es una tabla pequeña dentro de una ficha (no es listado principal); no se crea listado nuevo. |
| IV. Pruebas primero | Se planifican pruebas PHPUnit y Vitest antes de la implementación. |
| V. Mejora progresiva | Formulario en dos pasos del servidor (celular/cédula → datos) que funciona sin JS; el JS solo precarga sin recargar. |
| VI. Simplicidad | Se reutilizan `x-form.*`, `Celular`, `SedeContext`, `Permisos`. Una sola tabla nueva. |
| VII. Celular primero | Controles de 44 px, letra de 16 px, `inputmode="numeric"`; el service worker no guarda `/registro/*`. |

Resultado: **sin violaciones** que requieran justificación.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/009-registro-qr-eventos/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── rutas-registro.md
└── tasks.md          # lo genera /speckit-tasks
```

### Código fuente (`laravel_app/`)

```text
app/
├── Http/Controllers/
│   ├── RegistroPublicoController.php     # formulario público, precarga, envío
│   └── RegistroQrController.php          # admin: ver/descargar QR, abrir/cerrar, regenerar
├── Models/
│   ├── RegistroQr.php                    # nuevo
│   └── Concerns/TieneRegistroQr.php      # token, estaAbierto(), regenerar() (Evento, Encuentro, Celula)
├── Services/
│   ├── RegistroPorQr.php                 # resuelve destino, busca persona, inscribe
│   └── GeneradorQr.php                   # SVG del enlace
├── Policies/ (o Gates existentes)        # habilidad `gestionarRegistroQr`
database/migrations/
├── ..._add_registro_qr_a_eventos_encuentros_celulas.php
└── ..._create_registros_qr_table.php
resources/
├── views/registro/{formulario,confirmacion,cerrado}.blade.php
├── views/partials/panel-registro-qr.blade.php   # QR + lista en las 3 fichas
├── js/forms/registro-qr.js                       # precarga por cédula/celular
└── css/forms.css                                 # ajustes del formulario público
routes/web.php                                    # grupo público throttled + rutas admin
tests/
├── Feature/RegistroQr/{Flujo,Aislamiento,Limites,Panel}Test.php
└── (Vitest) resources/js/forms/registro-qr.test.js
```

**Decisión de estructura**: se mantiene el monolito Laravel; sin proyectos nuevos.

## Seguimiento de Complejidad

Sin violaciones de la constitución; no aplica.
