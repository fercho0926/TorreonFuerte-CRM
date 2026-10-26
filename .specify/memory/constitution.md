# Constitución de Torreón Fuerte CRM

## Principios Fundamentales

### I. Todo artefacto de especificación en español
Todo el contenido generado con spec-kit (especificación, plan, tareas, investigación, modelo de datos, contratos, guías rápidas, listas de verificación y esta constitución) se redacta en español. Se conservan sin traducir los nombres de archivos, rutas, ramas, identificadores de código, comandos y fragmentos técnicos. Las palabras normativas se escriben DEBE / DEBERÍA / PUEDE.

### II. Aislamiento por sede y alcance por rol (NO NEGOCIABLE)
Ninguna pantalla, respuesta JSON, exportación o búsqueda puede mostrar información fuera del alcance del usuario (su sede y su rol: administrador, pastor, líder de célula, usuario). El alcance se aplica en la consulta, no solo en la vista. Toda funcionalidad nueva DEBE probar el aislamiento entre sedes y entre roles.

### III. Grillas estándar para todo listado principal
Todo listado principal DEBE usar la grilla estándar (`<x-ui.grilla>` con una definición `App\Grillas\Grilla`): ordenar por columna sobre todo el conjunto, filtro por columna, búsqueda general cuando aplique, 10/25/50/100 filas por página, paginador, adaptación a teléfono y exportación a CSV con los filtros y el orden actuales. Una prueba automática DEBE fallar si un listado principal usa una tabla simple. La exportación de datos de acceso (usuarios, solicitudes de clave) DEBE restringirse a administradores. Las tablas pequeñas dentro de una ficha no son listados principales. La guía está en el README, sección «Grillas».

### IV. Pruebas primero
Cada funcionalidad se acompaña de pruebas automáticas (PHPUnit para el servidor, Vitest para JavaScript) escritas antes o junto con el código. Los cambios no se dan por terminados con pruebas en rojo ni con una compilación (`npm run build`) fallida.

### V. Mejora progresiva
Las pantallas DEBEN seguir funcionando sin JavaScript (formularios, filtros, paginación del servidor); el JavaScript solo las mejora. Los endpoints JSON responden errores en JSON, porque el manejador global solo lo hace para `api/*`.

### VI. Simplicidad
Se prefiere reutilizar componentes y servicios existentes (componentes `x-form.*` y `x-ui.*`, `AlcanceService`, `Estados`, `Permisos`) antes que crear abstracciones nuevas. Ningún `<form>` se escribe a mano fuera de los componentes de formulario.

### VII. Diseño primero para celular
La mayor parte del uso es desde el celular, así que toda pantalla se diseña primero para teléfono: sin desplazamiento horizontal de la página entre 320 y 1920 px, todo control tocable de al menos 44 × 44 px, campos de texto con letra de al menos 16 px, tablas que se apilan como tarjetas por debajo de 768 px (clase `tabla-apilable` o grilla estándar) y teclado adecuado por tipo de dato. La aplicación se puede instalar en la pantalla de inicio y **no guarda datos de personas, líderes, células, eventos ni usuarios en el teléfono** (solo archivos de la interfaz y la pantalla «Sin conexión»). Una funcionalidad no se da por terminada si `npm run test:movil` falla; las excepciones se declaran con motivo en `tests/movil/excepciones.json`, que debe permanecer vacía salvo causa justificada.

## Restricciones Técnicas

- PHP 8.3 y Laravel 13; vistas Blade; AdminLTE 4 sobre Bootstrap 5; Vite; DataTables 2 para las grillas.
- Los activos compilados (`public/build`) forman parte del despliegue al hosting.
- Nunca se ejecuta `migrate:fresh` sobre la base local; las pruebas usan bases temporales.

## Flujo de Desarrollo

- Cada funcionalidad sigue el ciclo de spec-kit: especificar, clarificar, planificar, generar tareas e implementar.
- Antes de cerrar una funcionalidad: `php artisan test`, `npm test` y `npm run build` en verde, y verificación en el navegador de los flujos principales.
- Los commits se hacen solo cuando el responsable del proyecto lo pide.

## Gobernanza

Esta constitución prevalece sobre otras prácticas del proyecto. Las enmiendas se documentan aquí con su fecha y motivo, y toda revisión de código verifica el cumplimiento de los principios II, III y VII.

### Excepciones vigentes

Decididas por el responsable del proyecto; cada una tiene su prueba y no se amplía sin una enmienda nueva.

- **Principio III — «Control de cambios» no se exporta** (spec 015, 2026-10-23): la grilla `control-cambios` cumple el resto de la grilla estándar pero no tiene exportación a CSV, porque el historial reúne datos personales y de acceso de toda la plataforma. Pruebas: `GuardaGrillaEstandarTest::SIN_EXPORTACION` y `ContratoGrillaTest`.
- **Principio II — histórico del miembro trasladado** (spec 015, 2026-10-23): la sede actual de un miembro trasladado ve todo su histórico de las sedes anteriores (la cadena `personas.trasladada_desde_persona_id`); las fichas de la otra sede siguen sin enlace y ningún otro dato cruza de sede. Pruebas: `TrasladoHistorialTest` y `AislamientoHistorialTest`.

**Versión**: 1.2.0 | **Ratificada**: 2026-10-19 | **Última enmienda**: 2026-10-23 (excepciones declaradas de la spec 015)
