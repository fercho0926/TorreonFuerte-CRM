# Contrato: rutas, formularios y reglas

**Funcionalidad**: `016-usuario-es-miembro`

No se agregan rutas. Cambia el comportamiento de las rutas existentes que se listan abajo. Todas siguen funcionando sin JavaScript y responden HTML. Los permisos (`UserPolicy`, `PersonaPolicy`, módulos) no cambian salvo donde se indica.

## Usuarios

### `GET /users/create` (`users.create`)

| Parámetro (query) | Obligatorio | Descripción |
|-------------------|-------------|-------------|
| `celular` | no | Si no viene: solo se muestra el paso 1 (celular y, para el administrador, la sede del miembro). Si viene: se busca por `Celular::clave()`. |
| `sede_id` | solo si el actor es administrador | Sede donde se busca y, si hace falta, se crea el miembro (RF-012a). |

**Resultado del paso 2** (variable `$busqueda['estado']` de la vista):

| Estado | Qué muestra |
|--------|-------------|
| `encontrado` | Tarjeta del miembro (nombre, celular legible, estado), `persona_id` oculto y campos de cuenta: `email` y `role`. |
| `varios` | Lista de miembros como `x-form.radio` (componente **nuevo**, D4) con `persona_id` y los campos de cuenta. |
| `nuevo` | Campos obligatorios del miembro (`miembro[nombres]`, `miembro[apellidos]` y los demás obligatorios de `PersonaRequest`) y los campos de cuenta. El celular queda fijo con el valor buscado. |
| `con_cuenta` | Aviso «{nombre} ya tiene una cuenta» con enlace a `users.edit`. Sin formulario. |
| `inactivo` | Aviso con enlace a la ficha del miembro para reactivarlo. Sin formulario. |
| `otra_sede` | Aviso «Ese celular pertenece a un miembro de otra sede; solicita su traslado». **Sin nombre, sede ni ningún dato del miembro.** |
| `invalido` | Error de validación del celular (`Celular::regla()`). |

### `POST /users` (`users.store`)

| Campo | Regla |
|-------|-------|
| `celular` | obligatorio, `Celular::regla()` |
| `persona_id` | obligatorio si no viene `miembro`; debe estar en `AlcanceService::personasVisibles(actor)`, sin cuenta, no inactivo y con `celular_clave` = `Celular::clave(celular)` |
| `miembro[...]` | obligatorio si no viene `persona_id`; reglas de alta de `PersonaRequest`; el celular se toma de `celular` |
| `email` | obligatorio, correo, único en `users` |
| `role` | obligatorio, en `UserPolicy::rolesAsignables(actor)` |
| `sede_id` | administrador: obligatorio (sede del miembro); otros actores: prohibido (se usa la sede efectiva) |
| `name` | **ya no se acepta** (se ignora) |

**Efecto**: `CuentasDeMiembros::crear()` en una transacción. Si `persona_id` no cumple las condiciones, se devuelve un error de validación en `persona_id` y no se crea nada. Después, como hoy, se muestra la clave temporal (`MuestraClave`).

### `GET /users/{user}/edit` y `PUT /users/{user}`

- Campos editables: `email` y `role` (con las reglas actuales, incluida la del único administrador).
- Se eliminan `name`, `celular` y `persona_id` del formulario y de la validación.
- La vista muestra el miembro vinculado con un enlace a `personas.show` («Editar datos personales»).
- **Solo el administrador, y solo si `revision_vinculo` no es nulo**: el campo `vincular_a` (`x-form.radio` con los candidatos) llama a `CuentasDeMiembros::vincular()`.

### `GET /users` (grilla `UsuariosGrilla`)

- La columna «Nombre» ordena con una subconsulta sobre `personas` (`apellidos`, `nombres`) y filtra con `whereHas('persona')`.
- La búsqueda general busca en el nombre del miembro y en `users.email`.
- Nuevo filtro de la columna «Vínculo» (solo administrador): `Todos | Por revisar`.
- La exportación CSV lleva los mismos datos, con el nombre tomado del miembro.

## Miembros

### `POST /personas`, `PUT /personas/{persona}` (`PersonaRequest`)

| Regla nueva | Mensaje |
|-------------|---------|
| `es_lider = true` y no es pastor ni pastora: `lider_id` se fuerza al pastor de la sede al **pasar a líder**. Si ya era líder, `lider_id` es obligatorio. | «Todo líder depende del pastor o de otro líder.» |
| La sede no tiene pastor y se marca `es_lider` | «Primero asigna el pastor de la sede.» |
| Es pastor o pastora y viene `lider_id` | «El pastor es la cabeza de la sede y no tiene líder.» |
| El miembro tiene cuenta: `celular` obligatorio y su `celular_clave` no se repite en otro miembro con cuenta | «Ese celular ya es el de la cuenta de otro miembro.» |

### `DELETE /personas/{persona}`

- `PersonaPolicy` niega el borrado si el miembro tiene cuenta. La ficha oculta el botón y explica: «Tiene cuenta de usuario; elimina primero la cuenta.»

## Líderes

### `POST /lideres/designar` (`lideres.designar.store`)

- Después de `designarLider()`, el líder queda bajo el pastor de la sede (aunque antes tuviera otro líder).
- El mensaje de éxito dice: «{nombre} ahora es líder, bajo el pastor {pastor}.»
- Si la sede no tiene pastor: error de validación en `persona_id` y nada cambia.

### `DELETE /lideres/{lider}` (quitar rol)

- Sin cambios en la reasignación de sus miembros. La persona conserva su líder actual.

## Sedes

### `PUT /sedes/{sede}/pastores` (`sedes.pastores.update`)

- Además de lo actual: el nuevo pastor o pastora queda sin líder y como líder. Al cambiar el **pastor**, los dependientes directos del anterior pasan al nuevo, y el pastor anterior queda como líder bajo el nuevo.

## Servicios (contrato interno)

```php
// App\Services\CuentasDeMiembros
public function buscarPorCelular(string $celular, User $actor, ?int $sedeId = null): array; // ['estado' => ..., 'personas' => Collection]
public function crear(array $cuenta, ?Persona $persona, ?array $miembro, User $actor): User;  // transacción única
public function vincular(User $usuario, Persona $persona, ?User $por = null): User;          // revisión manual y traslados
public function unificar(): array;                                                          // ['vinculadas' => n, 'creadas' => n, 'por_revisar' => n, 'sedes_sin_pastor' => [...]]

// App\Services\LiderazgoService (nuevos)
public function liderPorDefecto(Persona $persona): Persona;                                  // pastor de la sede o ValidationException
public function reasignarDependientes(Persona $de, Persona $a, ?User $por = null): int;      // sin retirar de células

// App\Support\Celular
public static function clave(?string $celular): ?string;                                      // dígitos; 12 con «57» al inicio → últimos 10
```

## Comando

```text
php artisan miembros:unificar-usuarios
```

Es idempotente. Imprime el resumen de `unificar()` y la tabla de cuentas por revisar (correo, sede, motivo y número de candidatos).
