# Guía Rápida de Validación: Todo usuario es un miembro y el pastor encabeza el liderazgo

**Funcionalidad**: `016-usuario-es-miembro`

Guía para comprobar de punta a punta que la funcionalidad cumple la spec. Los detalles están en [data-model.md](data-model.md) y [contracts/rutas-y-reglas.md](contracts/rutas-y-reglas.md).

## Requisitos previos

- Desde `laravel_app/`: `composer install` y `npm install` hechos.
- Base local con datos reales, migrada **sin** `migrate:fresh` (constitución).
- Antes de migrar, anota cuántas cuentas hay y cuántas tienen `persona_id` nulo:
  ```bash
  php artisan tinker --execute="echo App\Models\User::withoutGlobalScopes()->count().' / '.App\Models\User::withoutGlobalScopes()->whereNull('persona_id')->count();"
  ```

## 1. Unificación de cuentas existentes (Historia 4, CE-001)

```bash
php artisan test --filter=UnificarUsuariosTest
php artisan migrate
php artisan miembros:unificar-usuarios
```

**Esperado**:
- El comando informa cuántas cuentas se vincularon, cuántos miembros se crearon y cuántas cuentas quedan por revisar. Al repetirlo, no hay cambios nuevos.
- Ninguna cuenta queda sin miembro.
- En «Usuarios», el filtro «Vínculo: Por revisar» (como administrador) lista solo los casos dudosos. Al editar uno y elegir un candidato, la cuenta queda vinculada a ese miembro, y el miembro creado automáticamente desaparece si no tenía datos.

## 2. Crear usuario a partir de un miembro existente (Historia 1)

1. Como pastor, abre «Nuevo usuario» y escribe el celular de un miembro sin cuenta, con espacios y con «+57».
2. **Esperado**: se muestra la tarjeta con su nombre. Completa el correo y el rol, y guarda.
3. **Esperado**: se muestra la clave temporal; en la grilla aparece el nombre del miembro y no hay un miembro nuevo en «Miembros».
4. Repite con el mismo celular. **Esperado**: «… ya tiene una cuenta», sin formulario.

## 3. Crear usuario y miembro a la vez (Historia 2)

1. Escribe un celular que no exista. **Esperado**: campos del miembro y de la cuenta.
2. Deja vacío «Apellidos» y guarda. **Esperado**: error en ese campo; no se crea ni miembro ni cuenta.
3. Completa y guarda. **Esperado**: miembro nuevo en «Miembros» y cuenta vinculada.
4. Como administrador, repite eligiendo una sede. **Esperado**: el miembro queda en esa sede.

## 4. Una sola persona (Historia 3)

1. Edita el miembro de una cuenta y cambia su nombre y su celular.
2. **Esperado**: la grilla de usuarios, el encabezado de su sesión y el mensaje de la clave por WhatsApp usan los datos nuevos.
3. Abre la edición de la cuenta. **Esperado**: no hay campos de nombre ni de celular, solo un enlace «Editar datos personales».
4. Pon a otro miembro con cuenta el mismo celular. **Esperado**: error «Ese celular ya es el de la cuenta de otro miembro.»
5. Intenta eliminar un miembro con cuenta. **Esperado**: no hay botón; la ficha explica por qué.

## 5. El pastor encabeza el liderazgo (Historias 5 y 6)

1. En una sede con pastor, designa como líder a un miembro que tenía otro líder. **Esperado**: su líder ahora es el pastor, y la pestaña «Histórico» muestra «Designado líder» y el cambio de líder.
2. Edita a ese líder y quita su líder. **Esperado**: error «Todo líder depende del pastor o de otro líder.»
3. Edita al pastor e intenta ponerle líder. **Esperado**: error.
4. En una sede sin pastor, intenta designar un líder. **Esperado**: «Primero asigna el pastor de la sede», y nada cambia.
5. Como administrador, cambia el pastor de la sede. **Esperado**: los líderes que estaban bajo el pastor anterior quedan bajo el nuevo; el nuevo pastor no tiene líder y el anterior queda como líder bajo el nuevo.
6. Abre el organigrama. **Esperado**: una sola cabeza (pastor y pastora en la cabecera) y ningún líder suelto.

## 6. Aislamiento (principio II)

```bash
php artisan test --filter=CuentasDeMiembrosAislamientoTest
```

**Esperado**: un pastor de la sede A que busca el celular de un miembro de la sede B recibe solo el aviso genérico de traslado: ningún nombre ni dato aparece en la respuesta.

## 7. Cierre

```bash
php artisan test
npm test
npm run build
npm run test:movil
```

Todo en verde. Las pantallas «Nuevo usuario» (los dos pasos) y «Editar usuario» están en `tests/movil/pantallas.mjs`, sin excepciones.

## Línea base (2026-10-23, antes de implementar)

- `php artisan test`: 1 043 pruebas, 1 040 pasan, 1 falla, 2 omitidas.
  - El fallo ya existía y es ajeno a esta spec: `PresentacionPersonasTest::test_la_ficha_muestra_enlaces_de_llamada_y_whatsapp_con_celular`, porque la ficha no tiene el enlace `tel:`.
- `npm test` (Vitest): 18 archivos, 114 pruebas en verde.
- Base local:
  - 9 cuentas, 4 de ellas sin miembro;
  - 4 líderes sin líder;
  - 2 sedes, 1 de ellas sin pastor.
