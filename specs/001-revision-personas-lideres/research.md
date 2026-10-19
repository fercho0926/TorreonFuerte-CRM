# Investigación (Fase 0): Personas y Líderes

No quedaban marcadores `NEEDS CLARIFICATION` en el contexto técnico; este documento registra las decisiones de diseño tomadas a partir de la revisión del código actual y de la spec.

## D1. Un solo rol "Líder"; red y zona como catálogos

- **Decisión**: mantener la bandera `es_lider` en `personas` y añadir `red_id` y `zona_id` (solo con sentido cuando `es_lider`). Crear tablas `redes` y `zonas`. El campo de texto `red` se migra y se elimina.
- **Justificación**: la spec (Q1) fija un solo nivel de líder; una bandera + catálogos es lo más simple y evita una tabla `lideres` que duplicaría datos de la persona.
- **Alternativas**: tabla `lideres` separada (rechazada: reintroduce la duplicación persona/líder que hoy confunde); enum fijo de redes (rechazado: la iglesia debe poder administrarlas).

## D2. Zona sin supervisor (decisión revisada)

- **Decisión**: la zona es un catálogo geográfico opcional (`zonas`: nombre, activa). Se elimina el concepto de líder de zona: no hay `supervisor_id`, ni rol de usuario `lider_zona`, ni ámbito por zona. Los usuarios que tenían ese rol pasan a `lider_celula` en la migración de datos.
- **Justificación**: el usuario indicó que el líder de zona no es necesario; mantenerlo agregaba un nivel de supervisión que la iglesia no usa.
- **Alternativas**: eliminar también la zona (descartada por ahora: sigue sirviendo para filtrar; queda pendiente de confirmar).

## D3. Líder directo como fuente de verdad (Q3) y células

- **Decisión**: `personas.lider_id` manda. `celula_persona` solo admite personas cuyo `lider_id` es el líder o el co-líder de la célula. Al cambiar el líder, `LiderazgoService` retira a la persona de las células del líder anterior (o exige elegir otra célula del nuevo líder). Hoy `CelulasController` ya sincroniza `lider_id` al agregar miembros; se invierte la dirección de validación.
- **Justificación**: elimina las contradicciones del hallazgo 2 con el menor cambio.
- **Alternativas**: célula manda (descartada por el usuario); ambos independientes (descartada).

## D4. Personas sin líder (Q4)

- **Decisión**: `lider_id` nullable sigue siendo válido; vista `/sin-lider` para pastor y administrador.
- **Zona**: `personas.zona_id` solo se usa para líderes (opcional); una persona sin líder no tiene zona.
- **Alternativas**: líder temporal automático (descartada por el usuario).

## D5. Alcance por rol: consultas, no listas de IDs

- **Decisión**: `AlcanceService::personasVisibles(User)` devuelve un `Builder` (scope `visiblesPara`) con subconsultas; reemplaza `personasVisiblesIds()`/`celulasVisiblesIds()` del `Controller` base.
- **Justificación**: hoy se cargan todas las células y miembros en memoria y se pasan listas de IDs a `whereIn`, lo cual no escala con paginación y duplica reglas. Un scope se aplica igual a listados, selectores y validación de destino (corrige hallazgos 16–17).
- **Reglas**: admin/pastor: todo. Líder (rol `lider_celula`): personas con `lider_id` = su persona, miembros de sus células y él mismo. `user`: solo su registro.
- **Alternativas**: paquete de permisos (spatie): rechazado por peso y por tener solo 5 roles fijos.

## D6. Políticas y botones

- **Decisión**: `PersonaPolicy` consultada con `Gate::forUser($usuarioActual)` (la app usa sesión propia, no `Auth`). Las vistas usan `@can`-equivalente vía helper; el controlador devuelve 403 con mensaje claro en lugar de redirección silenciosa.
- **Alternativas**: migrar a `Auth` de Laravel: fuera de alcance.

## D7. Estado, documento opcional e inactivación

- **Decisión**: `personas.estado` (`visitante`, `nuevo_creyente`, `miembro_activo`, `inactivo`), `numero_documento` y `tipo_documento` nullable; el documento es obligatorio al pasar a `miembro_activo` o ser líder (validación condicional). Inactivar cambia el estado; el borrado definitivo usa soft delete, solo admin y solo sin historial.
- **Compatibilidad**: la restricción única de `numero_documento` admite varios NULL en SQLite y MySQL.
- **Alternativas**: solo soft delete (descartada: "inactivo" es un estado visible y reversible, no un borrado).

## D8. Duplicados

- **Decisión**: advertencia no bloqueante (mismo nombre+apellido y celular) con enlace a la ficha existente y casilla "guardar de todos modos"; el documento sigue siendo único.
- **Justificación**: familiares comparten celular (caso límite de la spec).

## D9. Búsqueda y paginación

- **Decisión**: parámetro `q` con `LIKE` sobre nombres, apellidos, documento y celular (normalizando espacios); paginación de 25; filtros combinables (`estado`, `red_id`, `zona_id`, `lider_id`). Índices en `apellidos, nombres`, `celular`, `estado`, `lider_id`.
- **Alternativas**: búsqueda de texto completo (rechazada: sobredimensionada para esta escala y poco portable entre SQLite y MySQL).

## D10. Interfaz móvil

- **Decisión**: en pantallas pequeñas el listado se muestra como tarjetas (partial `persona-tarjeta`) y la tabla solo desde `md`; ficha con botones `tel:` y `https://wa.me/`; fechas con Carbon `locale('es')` y edad calculada.
- **Alternativas**: DataTables (rechazada: dependencia extra y problemas en móvil).

## D11. Bitácora

- **Decisión**: tabla `persona_cambios` (persona, usuario, acción, antes/después en JSON, fecha), escrita desde `LiderazgoService` y el controlador de personas; visible a pastor y administrador en la ficha.
- **Alternativas**: paquete de auditoría (rechazado por dependencia).

## D12. Migración de datos

- **Decisión**: migraciones en dos fases dentro de la misma versión: (1) crear tablas/columnas nuevas; (2) migrar: normalizar `red` (trim, minúsculas, sin tildes) → crear `redes` y asignar `red_id`; los usuarios con rol `lider_zona` pasan a `lider_celula`; asignar `estado = miembro_activo` a las personas existentes; completar `lider_id` de miembros de célula con el líder de la célula cuando falte. Reversible (`down`) hasta eliminar la columna `red` en una migración posterior separada.
- **Riesgos**: redes casi iguales con otra escritura (typo): se generan sin fusionar y el administrador las consolida desde el catálogo (acción "fusionar").

## D13. Vocabulario

- **Decisión**: etiquetas de rol de usuario: `lider_celula` → "Líder"; `user` → "Usuario"; el rol `lider_zona` desaparece. No se renombran los valores en base de datos. Partial `ayuda-terminos` con definiciones breves de Líder, Red y Zona.
