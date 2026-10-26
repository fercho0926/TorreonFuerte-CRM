# Modelo de Datos: Todo usuario es un miembro y el pastor encabeza el liderazgo

**Funcionalidad**: `016-usuario-es-miembro` | **Fecha**: 2026-10-23

## Entidades

### Persona (miembro), tabla `personas` (cambia)

| Campo | Tipo | Cambio | Regla |
|-------|------|--------|-------|
| `celular` | string, nulo | sin cambio | Se muestra tal como se escribió. |
| `celular_clave` | string(15), nulo, **índice** | **NUEVO** | `Celular::clave(celular)`; se calcula en `saving`. Lo usa la búsqueda de «Nuevo usuario» (research D3). |
| `email` | string, nulo | sin cambio | Al crear el miembro desde «Nuevo usuario» toma el correo de la cuenta (D2). |
| `lider_id` | FK `personas`, nulo | regla nueva | **Obligatorio si `es_lider` y la persona no es pastor ni pastora de su sede. Nulo si es pastor o pastora.** |
| `es_lider` | bool | regla nueva | Pastor y pastora: siempre `true`. |

**Relaciones nuevas**:
- `cuenta(): HasOne<User>`: la cuenta de acceso del miembro, si tiene.
- `esPastoral(): bool`: verdadero si es `pastor_persona_id` o `pastora_persona_id` de su sede.

**Validaciones nuevas**:
- Si el miembro tiene cuenta, `celular` es obligatorio y su `celular_clave` no se repite en otro miembro con cuenta (RF-009).
- No se puede eliminar un miembro con cuenta (RF-010).
- No se le puede asignar líder al pastor ni a la pastora (RF-015).
- No se puede dejar sin líder a un líder que no es pastor ni pastora (RF-014).

### Usuario (cuenta de acceso), tabla `users` (cambia)

| Campo | Tipo | Cambio | Regla |
|-------|------|--------|-------|
| `name` | string | **SE ELIMINA** | Pasa a ser el atributo de solo lectura `name` = `persona->nombre_completo`. |
| `celular` | string(20), nulo | **SE ELIMINA** | Pasa a ser el atributo de solo lectura `celular` = `persona->celular`. |
| `persona_id` | FK `personas` | **NOT NULL, UNIQUE, `restrictOnDelete`** (antes nulo con `nullOnDelete`) | Una cuenta por miembro (RF-001, RF-002). |
| `revision_vinculo` | json, nulo | **NUEVO** | `{"candidatos": [persona_id, …], "motivo": "varios"\|"nombre_distinto", "creado_automatico": persona_id}`; nulo cuando no hay nada que revisar (D7). |
| `email` | string, único | sin cambio | Sigue siendo la credencial de ingreso (D2). |
| `sede_id` | FK `sedes`, nulo | regla nueva | Cuentas que no son de administrador: igual a `persona.sede_id`. Administrador: nulo (acceso global); su miembro pertenece a una sede concreta (RF-012a). |
| `role`, `password`, `debe_cambiar_clave`, `clave_vence_en` | — | sin cambio | Lo propio de la cuenta. |

**Relación**: `persona(): BelongsTo<Persona>` sin filtro global de sede (`withoutGlobalScopes`), con carga anticipada por defecto (`$with = ['persona']`) para no multiplicar consultas en listados y en el encabezado.

### Sede, tabla `sedes` (sin cambios de esquema)

- `pastor_persona_id` es la cabeza del árbol de liderazgo de la sede. Para crear o designar líderes la sede DEBE tener pastor (RF-016).
- `pastora_persona_id` es cabeza junto con el pastor: no tiene líder y no recibe automáticamente a los líderes nuevos.

## Relación de liderazgo (árbol por sede)

```text
Sede
 └─ Pastor (lider_id = null, es_lider = true)   ← única raíz
     ├─ Pastora (lider_id = null, es_lider = true; se muestra junto al pastor)
     ├─ Líder A (lider_id = pastor)
     │   ├─ Líder A1 (lider_id = A)
     │   └─ Miembro (lider_id = A)
     └─ Líder B (lider_id = pastor)
```

**Invariantes** (se prueban todas):
1. Todo `es_lider = true` que no es pastor ni pastora tiene `lider_id`.
2. El pastor y la pastora tienen `lider_id = null`.
3. No hay ciclos (regla existente de `validarLider()`).
4. El `lider_id` apunta a un líder de la misma sede (regla existente de `SedeContext::existe()`).

## Transiciones

### Miembro → líder (designar, crear con `es_lider`, editar)

```text
es_lider: false → true
lider_id: (cualquiera) → pastor de la sede      [historial: rol_lider + lider]
Si la sede no tiene pastor → error, nada cambia.
Si es el pastor o la pastora → lider_id = null.
```

### Cambio de pastor de la sede

```text
nuevo pastor:     lider_id → null, es_lider → true
dependientes del pastor anterior: lider_id → nuevo pastor   (sin retirarlos de sus células)
pastor anterior:  lider_id → nuevo pastor, es_lider → true, cuenta: role pastor → lider_celula (sin cambio respecto a hoy)
```

### Cuenta: alta

```text
celular escrito → celular_clave
├─ 1 miembro visible, sin cuenta    → cuenta.persona_id = miembro
├─ varios miembros visibles         → el responsable elige uno
├─ 0 miembros visibles              → se crea el miembro y la cuenta (transacción única)
├─ miembro con cuenta               → error «ya tiene cuenta»
├─ miembro inactivo                 → aviso y enlace para reactivarlo; no se crea nada
└─ solo existe en otra sede         → aviso genérico de traslado; no se crea nada
```

### Cuenta: revisión de vínculo (datos migrados)

```text
revision_vinculo != null → el administrador elige un candidato
  → persona_id = candidato; revision_vinculo = null
  → el miembro creado automáticamente: borrado lógico si no tiene datos asociados; si no, se conserva y se avisa
```

### Traslado de un miembro con cuenta

```text
persona origen → persona nueva en la sede destino
cuenta.persona_id → persona nueva; cuenta.sede_id → sede destino   (misma transacción)
```

## Migraciones (en orden)

1. `add_celular_clave_y_revision_vinculo`: agrega `personas.celular_clave` (con índice) y `users.revision_vinculo`, y rellena `celular_clave`.
2. `unificar_usuarios_con_miembros`: datos; ejecuta `CuentasDeMiembros::unificar()` (D7).
3. `pastor_cabeza_del_liderazgo`: datos (D11).
4. `users_persona_obligatoria`: elimina `users.name` y `users.celular`; `persona_id` pasa a `NOT NULL` y `UNIQUE`, con la FK en `restrictOnDelete`. Se detiene con un error claro si queda alguna cuenta sin miembro.

`down()`: la 4 recrea `name` y `celular` y los rellena desde el miembro; las de datos no se revierten (son idempotentes y no destruyen nada).
