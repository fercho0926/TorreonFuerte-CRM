# Modelo de Datos: Organigrama interactivo por sede

**No hay cambios de base de datos.** El organigrama es una vista de solo lectura calculada desde tablas existentes. Lo único nuevo es la estructura en memoria que arma `OrganigramaService`.

## Fuentes existentes

| Entidad (tabla) | Campos usados | Papel en el organigrama |
|-----------------|---------------|-------------------------|
| `sedes` | `id`, `nombre`, `activa`, `pastor_persona_id`, `pastora_persona_id` | Cabeza del árbol; de aquí salen los pastores |
| `personas` | `id`, `sede_id`, `nombres`, `apellidos`, `es_lider`, `lider_id`, `estado` | Pastores, líderes y miembros |
| `celulas` | `id`, `lider_id`, `co_lider_id` | Vincula a un líder con los miembros de sus células |
| `celula_persona` | `celula_id`, `persona_id` | Miembros de cada célula |

Todas las consultas pasan por el filtro global `SedeScope` (aislamiento por sede) y, para el rol Líder, por `AlcanceService`.

## Estructura en memoria: `NodoOrganigrama`

Arreglo asociativo (o DTO simple) recursivo, sin persistencia:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `tipo` | `sede` \| `lider` \| `miembro` \| `sin_lider` | Nivel del nodo |
| `id` | int \| null | Id de la persona (null en `sede` y `sin_lider`) |
| `titulo` | string | Nombre completo, o nombre de la sede, o «Sin líder asignado» |
| `rol` | string | Etiqueta visible: «Sede», «Líder», «Miembro» |
| `pastores` | lista de `{id, nombre}` | Solo en el nodo `sede`: pastores/pastoras de la cabecera |
| `puedeVerFicha` | bool | Resultado de `AlcanceService::puedeVerPersona` (RF-014) |
| `hijos` | lista de nodos | Vacía en `miembro` |
| `totalHijos` | int | Hijos directos (contador visible, RF-006) |

### Relaciones

```text
sede (1)
 └── pastores (0..n, en la cabecera de la sede)
 └── lideres (0..n)                  ← personas.es_lider = true
      └── miembros (0..n)            ← personas.lider_id = líder
                                       ∪ celula_persona de sus células (sin duplicados)
 └── sin_lider (0..1)                ← Persona::sinLider(); solo roles de acceso total
```

### Reglas de validación y de armado

1. Se excluyen personas con `estado = 'inactivo'` y las eliminadas lógicamente (RF-013).
2. Un miembro aparece **una sola vez** bajo un líder, aunque lo vincule `lider_id` y también una célula; si dependiera de dos líderes distintos, aparece bajo cada uno (es información real, no se oculta).
3. Un líder nunca aparece como miembro de sí mismo.
4. Un líder que también es pastor aparece en la cabecera y como líder.
5. Sede sin pastores: la cabecera muestra la sede con un aviso (caso límite de la especificación).
6. Orden estable: líderes y miembros por apellidos y luego nombres.

### Alcance por rol (quién obtiene qué)

| Rol | Raíz del árbol | Incluye «Sin líder asignado» |
|-----|----------------|------------------------------|
| Admin con sede activa | Sede completa | Sí |
| Admin en «Todas las sedes» | Ninguna (se pide elegir sede) | No |
| Pastor | Sede completa | Sí |
| Líder de célula | Él mismo como líder, con sus miembros | No |
| Usuario | Sin acceso (403) | — |

### Transiciones de estado

No hay estado persistente. El estado «expandido/colapsado» de cada nodo vive solo en el navegador (atributo `open`) y no se guarda.
