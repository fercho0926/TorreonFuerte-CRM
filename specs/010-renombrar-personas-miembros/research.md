# Investigación: Renombrar "Personas" a "Miembros"

No quedan puntos «NEEDS CLARIFICATION». Estas son las decisiones tomadas.

## D1. Alcance del renombrado interno

- **Decisión**: renombrar también el código y la base de datos (modelo, tablas, columnas, rutas con nombre, vistas, pruebas), no solo el texto visible.
- **Justificación**: la spec pide «todo» (FR-012) y evitar dos vocabularios. Dejar `Persona` en el código con «Miembros» en pantalla obliga a traducir mentalmente en cada cambio futuro y genera errores de concordancia.
- **Alternativas**: (a) solo texto visible: más barato pero deja deuda y contradice FR-012; (b) renombrar código pero no tablas: deja `personas` en la base, aceptable como plan B si el hosting no soporta el renombrado (ver D3).

## D2. Orden en etapas

- **Decisión**: etapa 1 texto y URLs, etapa 2 código, etapa 3 base de datos. Cada etapa termina con `php artisan test`, `npm test`, `npm run build` y `npm run test:movil` en verde.
- **Justificación**: la etapa 1 ya entrega el valor al usuario y es reversible; separar el renombrado del código del de la base permite aislar fallos.

## D3. Base de datos

- **Decisión**: una migración nueva con `Schema::rename` y `renameColumn` (y renombrado de índices con nombre), reversible (`down` revierte), idempotente ante tablas ya renombradas. Tablas: `personas`→`miembros`, `persona_cambios`→`miembro_cambios`, `evento_persona`→`evento_miembro`, `encuentro_persona`→`encuentro_miembro`, `celula_persona`→`celula_miembro`, `ministerio_persona`→`ministerio_miembro`. Columnas: `persona_id`→`miembro_id` (en `users`, pivotes, cambios, registros QR, etc.), `pastor_persona_id`→`pastor_miembro_id`, `pastora_persona_id`→`pastora_miembro_id`, `evento_persona_id`→`evento_miembro_id`.
- **Justificación**: las migraciones históricas ya corrieron en producción y no se editan. Renombrar es una operación de metadatos y conserva filas y relaciones (FR-008, SC-003). Laravel 13 soporta `renameColumn` nativo en SQLite y los motores de producción.
- **Riesgos y mitigación**: claves foráneas e índices con nombre autogenerado conservan el nombre antiguo (cosmético) o requieren renombrado explícito; se hace copia de la base antes de aplicar en producción y la prueba `MigracionDatosTest` verifica conteos antes/después. Si el renombrado fallara en el hosting, plan B: dejar tablas con nombre antiguo y apuntar el modelo con `$table`.
- **Alternativas**: vistas SQL de compatibilidad (descartado: complejidad); copiar tablas (descartado: riesgo de pérdida).

## D4. Direcciones web

- **Decisión**: rutas nuevas `/miembros/*` con nombres `miembros.*`; grupo de rutas `/personas/{cualquiera}` que redirige con 301 al equivalente nuevo conservando la consulta (`?q=`, filtros) y para todos los métodos GET. Los métodos no-GET antiguos (POST/PUT/DELETE) responden 308 para conservar el método y los datos en formularios abiertos antes del despliegue.
- **Justificación**: FR-007; los marcadores, enlaces de WhatsApp y accesos de la app instalada siguen funcionando. La autorización la decide la ruta nueva, por lo que la redirección no filtra información (principio II).
- **Alternativas**: dejar solo las antiguas (incumple FR-006); redirección 302 (no comunica el cambio permanente).

## D5. Colisiones de nombres

- **Decisión**: `Persona::miembros()` (hasMany: miembros a cargo de un líder) → `Miembro::liderados()`; `Celula::miembros()` se conserva. En `LideresGrilla` la columna `personas` pasa a `miembros` con etiqueta «Miembros». El estado almacenado `miembro_activo` y su etiqueta «Miembro activo» se conservan.
- **Justificación**: evita `Miembro::miembros()` ambiguo y no cambia datos almacenados.

## D6. Concordancia gramatical

- **Decisión**: no usar reemplazo automático ciego. Se revisa cada frase con una tabla de transformaciones: persona→miembro (m.), personas→miembros, «una persona»→«un miembro», «la/las persona(s)»→«el/los miembro(s)», «ninguna persona»→«ningún miembro», «inactiva(s)»→«inactivo(s)», «nueva»→«nuevo», «esta persona»→«este miembro», «otra persona»→«otro miembro», «sin asignar», etc. Se agrega una prueba guardiana que busca en vistas, `lang` y mensajes la palabra «persona(s)» con lista de excepciones declaradas (p. ej. «datos personales»).
- **Justificación**: SC-006; el género cambia de femenino a masculino y afecta artículos, adjetivos y participios.

## D7. Caché y app instalada

- **Decisión**: subir la versión de caché del service worker (`public/sw.js`) y actualizar `manifest.webmanifest`; el service worker no guarda datos de miembros (principio VII), por lo que solo cambian los archivos de interfaz.
- **Justificación**: SC-005; el usuario ve el nuevo vocabulario sin reinstalar.

## D8. Historial y datos de usuario

- **Decisión**: no se reescribe contenido escrito por usuarios (FR-010). Para el historial de cambios (`persona_cambios`), se renombra la tabla; los textos ya guardados se muestran tal cual salvo etiquetas generadas por el sistema, que se sustituyen en la capa de presentación. Si algún campo guarda etiquetas del sistema con «persona», se normaliza con una migración de datos acotada y reversible.
- **Justificación**: coherente con los casos límite de la spec y con «mensajes ya emitidos no se modifican».

## D9. Documentación

- **Decisión**: actualizar README, constitución (enmienda 1.1.1), memoria del proyecto y guías. Las specs 001–009 se conservan como histórico y no se reescriben; se agrega una nota de glosario en el README («Persona» histórico = «Miembro»).
- **Justificación**: FR-012 sin falsear el historial de decisiones.
