# Modelo de Datos: Registro por código QR

## Cambios en entidades existentes

Se aplican a **Evento**, **Encuentro** y **Célula** (`eventos`, `encuentros`, `celulas`):

| Campo | Tipo | Reglas |
|---|---|---|
| `registro_token` | cadena de 32, única, no nula tras la migración | Aleatoria; se genera al crear; se rellena para filas existentes; regenerarla invalida el enlace anterior |
| `registro_abierto` | booleano, por defecto verdadero | Interruptor del administrador |

Comportamiento derivado: `estaAbierto()` = `registro_abierto` y estado ni cancelado ni realizado (célula: activa).

## Entidad nueva: Registro QR (`registros_qr`)

Una fila por registro aceptado desde el formulario público.

| Campo | Tipo | Reglas |
|---|---|---|
| `id` | entero | clave primaria |
| `sede_id` | entero | sede del destino; filtro por sede (`PerteneceASede`) |
| `registrable_type` / `registrable_id` | polimórfico | Evento, Encuentro o Célula |
| `persona_id` | entero, nulo | persona de la sede del destino; **nulo** si es de otra sede o está pendiente |
| `nombres`, `apellidos` | cadena | lo escrito por la persona |
| `celular` | cadena | normalizado (solo dígitos) |
| `fecha_nacimiento` | fecha | obligatoria |
| `tipo_documento`, `numero_documento` | cadena, nulos | opcional; el tipo es obligatorio si hay número |
| `resultado` | enumeración | `nueva`, `existente`, `de_otra_sede`, `pendiente` (persona inactiva o eliminada) |
| `nota_correccion` | texto, nulo | diferencias con los datos de la persona existente |
| `created_at` | fecha y hora | momento del registro (FR-011) |

**Unicidad**: (`registrable_type`, `registrable_id`, `celular`) único → impide duplicados (FR-008).

**Relaciones**: pertenece a una sede; pertenece a una persona (opcional); pertenece a su destino (polimórfico).

## Reglas de validación

- `celular`: `Celular::esValido` (10 a 15 dígitos), normalizado antes de comparar.
- `nombres`, `apellidos`: obligatorios, máximo 100.
- `fecha_nacimiento`: fecha pasada, no anterior a 1900.
- `numero_documento`: si se escribe, `tipo_documento` obligatorio y dentro de `Persona::TIPOS_DOCUMENTO`.

## Flujo de estados de un registro

```text
formulario → (celular/cédula) → búsqueda en la sede del destino
   0 coincidencias → crea Persona (estado Visitante, o «En célula») + inscripción → resultado `nueva`
   1 coincidencia activa → inscripción de esa persona → resultado `existente`
   coincidencia inactiva o eliminada → solo registro → resultado `pendiente`
   existe solo en otra sede → solo registro → resultado `de_otra_sede`
   cédula y celular de personas distintas, o celular repetido → rechazo, no se guarda
```

## Efectos sobre tablas existentes

- `evento_persona`: fila nueva (sin costo individual).
- `encuentro_persona`: fila nueva (`asistio` falso).
- `celula_persona`: fila nueva (`fecha_ingreso` hoy); la persona «Visitante» pasa a «En célula».
- `personas`: solo se **crean** filas (persona nueva); nunca se actualizan las existentes.
