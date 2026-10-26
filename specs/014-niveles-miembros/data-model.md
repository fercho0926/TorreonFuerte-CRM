# Modelo de Datos: Niveles de formación de los miembros

## NivelFormacion (`niveles_formacion`) — común a todas las sedes

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | entero | clave primaria |
| nombre | texto (máx. 80) | obligatorio, **único en todo el plan** (sin distinguir mayúsculas) |
| descripcion | texto (máx. 500) | opcional |
| posicion | entero positivo | obligatorio; consecutivas 1..n incluyendo desactivados; única |
| activo | booleano | por defecto verdadero |
| created_at, updated_at | fechas | |

Reglas: se crea al final (`max(posicion)+1`); subir o bajar intercambia con el vecino; se elimina solo si no tiene avances ni encuentros; desactivar lo oculta del camino y de los conteos.

Datos iniciales: 1 «Libres para servir», 2 «Aliviando el fuego», 3 «Caminando en santidad», 4 «Viviendo en el espíritu».

## AvanceNivel (`avances_nivel`) — aislado por sede

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | entero | clave primaria |
| sede_id | clave foránea a sedes | heredada del miembro; filtro global de sede |
| persona_id | clave foránea a personas (miembros) | obligatorio; borrado en cascada con el miembro |
| nivel_id | clave foránea a niveles_formacion | obligatorio |
| fecha | fecha | obligatoria; no puede ser futura |
| origen | `encuentro` \| `manual` | obligatorio |
| encuentro_id | clave foránea a encuentros | solo si origen = `encuentro`; se anula/borra con el encuentro |
| registrado_por | clave foránea a users | opcional (nulo si es automático) |
| nota | texto (máx. 255) | opcional |
| created_at, updated_at | fechas | |

Restricción única: `(persona_id, nivel_id)`. Índices: `(sede_id, nivel_id)`, `(persona_id)`.

Estados derivados por miembro y nivel activo: **completado** (existe fila), **siguiente** (primer activo sin fila), **pendiente** (sin fila; `saltado` si hay un nivel posterior completado). Nivel actual = el de mayor `posicion` completado entre los niveles activos; «sin iniciar» si ninguno.

## NivelCambio (`nivel_cambios`) — historial del plan

| Campo | Tipo |
|-------|------|
| id | entero |
| nivel_id | clave foránea (nula si se eliminó) |
| user_id | clave foránea a users (nula) |
| accion | `creado`, `editado`, `reordenado`, `desactivado`, `reactivado`, `eliminado` |
| nombre | texto (nombre del nivel al momento) |
| antes, despues | JSON |
| created_at | fecha |

## Encuentro (`encuentros`) — columna nueva

| Campo | Tipo | Restricciones |
|-------|------|---------------|
| nivel_id | clave foránea a niveles_formacion | opcional; sin acción en cascada (se impide eliminar un nivel con encuentros) |

## PersonaCambio — acciones nuevas

`nivel_registrado` («Nivel completado»), `nivel_quitado` («Nivel retirado»), `nivel_automatico` («Nivel completado por encuentro»), con `despues` = nombre del nivel, fecha y origen.

## Permisos (`rol_permisos`)

Módulo `niveles`: administrador 2, pastor 2, líder 1, usuario 1; roles personalizados 0.

## Relaciones

- NivelFormacion 1—N AvanceNivel; NivelFormacion 1—N Encuentro.
- Persona 1—N AvanceNivel; Encuentro 1—N AvanceNivel (solo avances automáticos).
- Persona N—M NivelFormacion a través de AvanceNivel.
