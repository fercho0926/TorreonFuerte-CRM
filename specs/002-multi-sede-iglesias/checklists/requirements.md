# Checklist de calidad de la especificación: Multi-sede (varias iglesias en un mismo CRM)

**Propósito**: Validar la completitud y calidad de la especificación antes de pasar a planificación
**Creada**: 2026-10-19
**Funcionalidad**: [spec.md](../spec.md)

## Calidad del contenido

- [x] Sin detalles de implementación (lenguajes, frameworks, APIs)
- [x] Enfocada en el valor para el usuario y las necesidades del negocio
- [x] Redactada para interesados no técnicos
- [x] Todas las secciones obligatorias completadas

## Completitud de requisitos

- [x] No quedan marcadores [NEEDS CLARIFICATION]
- [x] Los requisitos son comprobables y no ambiguos
- [x] Los criterios de éxito son medibles
- [x] Los criterios de éxito son independientes de la tecnología
- [x] Todos los escenarios de aceptación están definidos
- [x] Se identifican los casos límite
- [x] El alcance está claramente acotado
- [x] Se identifican dependencias y supuestos

## Preparación de la funcionalidad

- [x] Todos los requisitos funcionales tienen criterios de aceptación claros
- [x] Los escenarios de usuario cubren los flujos principales
- [x] La funcionalidad cumple los resultados medibles definidos en los criterios de éxito
- [x] Ningún detalle de implementación se filtra en la especificación

## Notas

- Los supuestos sobre traslados de personas, permisos idénticos de pastor y pastora, catálogos por sede y migración a una primera sede son decisiones razonables por defecto; se recomienda confirmarlos en `/speckit-clarify`.
- Cambio importante frente al comportamiento actual: el rol pastor deja de ver todo el sistema y queda limitado a su sede.
