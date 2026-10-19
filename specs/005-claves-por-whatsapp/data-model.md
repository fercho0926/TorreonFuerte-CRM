# Modelo de datos: Claves por WhatsApp

Cambios aditivos; no se modifican ni borran datos existentes.

## Usuario (`users`) — columnas nuevas

| Campo | Tipo | Reglas |
|-------|------|--------|
| `celular` | texto de hasta 20, nulo | Mismo formato de celular que `personas.celular`; obligatorio al crear un usuario; nulo en cuentas antiguas hasta completarlo |
| `debe_cambiar_clave` | booleano, por defecto falso | Verdadero mientras la contraseña vigente sea una clave temporal |
| `clave_vence_en` | fecha y hora, nulo | Solo con clave temporal; pasado ese momento la cuenta no inicia sesión con ella |

Reglas: `password` conserva su hash y cast actual. Con `debe_cambiar_clave = true` y `clave_vence_en` pasado, el inicio de sesión se rechaza con mensaje de clave vencida. `debe_cambiar_clave` nunca es verdadero con `password` nulo.

## Solicitud de clave (`solicitudes_clave`)

| Campo | Tipo | Reglas |
|-------|------|--------|
| `id` | entero | |
| `user_id` | referencia a `users`, obligatoria | Solo se crean para cuentas existentes y habilitadas |
| `sede_id` | referencia a `sedes`, nula | Sede de la cuenta; nula para administradores del sistema |
| `estado` | texto | `pendiente`, `atendida`, `descartada` (`vencida` se calcula: pendiente con más de 48 h) |
| `atendida_por` | referencia a `users`, nula | Quien la atendió o descartó |
| `atendida_en` | fecha y hora, nula | |
| `ultima_solicitud_en` | fecha y hora | Se actualiza si la persona insiste mientras sigue pendiente |
| `origen_hash` | texto de hasta 64, nulo | Huella no reversible del origen, solo para el límite de intentos y diagnóstico |
| `created_at`, `updated_at` | marcas de tiempo | |

Reglas: como máximo **una** solicitud `pendiente` por `user_id`. Índices: (`sede_id`, `estado`), (`user_id`, `estado`). Alcance por sede igual que el resto de tablas de la iglesia.

## Registro de claves (`registros_clave`)

| Campo | Tipo | Reglas |
|-------|------|--------|
| `id` | entero | |
| `user_id` | referencia a `users`, obligatoria | Cuenta afectada |
| `sede_id` | referencia a `sedes`, nula | De la cuenta |
| `solicitud_id` | referencia a `solicitudes_clave`, nula | Si nació de una solicitud |
| `motivo` | texto | `alta`, `recuperacion`, `reenvio` |
| `solicitada_por` | referencia a `users`, nula | Nulo = «la propia persona» |
| `preparada_por` | referencia a `users`, obligatoria | Quien generó la clave |
| `estado` | texto | `pendiente_envio`, `enviada`, `usada`, `reemplazada`, `vencida` |
| `vence_en` | fecha y hora | Copia de la vigencia de la clave |
| `enviada_en`, `usada_en` | fecha y hora, nulas | |
| `created_at`, `updated_at` | marcas de tiempo | |

Reglas: **no contiene la clave ni su hash**. Índices: (`user_id`, `created_at`), (`sede_id`, `estado`).

## Transiciones de estado

Registro de claves:

```text
pendiente_envio ──(se marca «enviada»)──▶ enviada ──(la persona elige su contraseña)──▶ usada
      │                                      │
      ├──(se genera otra clave)──────────────┴──▶ reemplazada
      └──(pasa vence_en sin uso)─────────────────▶ vencida
```

Solicitud de clave:

```text
pendiente ──(se genera y envía la clave)──▶ atendida
    │
    └──(un pastor o administrador la descarta)──▶ descartada
(pendiente > 48 h se muestra «vencida» y puede atenderse o descartarse)
```

## Relaciones

- Usuario 1 — N Registros de clave; Usuario 1 — N Solicitudes de clave (una pendiente a la vez).
- Solicitud 1 — 0..N Registros de clave.
- Sede 1 — N Solicitudes y Registros (nula para administradores del sistema).

## Configuración (`config/claves.php`)

| Clave | Valor por defecto |
|-------|-------------------|
| `vigencia_horas` | 24 |
| `longitud` | 10 |
| `solicitudes_por_cuenta_hora` | 3 |
| `solicitudes_por_origen_hora` | 10 |
| `login_fallos_por_minuto` | 5 |
| `solicitud_vence_horas` | 48 |
| `contrasena_minimo` | 8 |
