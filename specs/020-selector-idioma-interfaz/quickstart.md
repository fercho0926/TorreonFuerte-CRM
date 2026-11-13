# Guía de Validación: Selector de idioma en la interfaz (español/inglés)

## Requisitos previos

- Entorno local del proyecto funcionando (`laravel_app/`), base de datos de pruebas disponible.
- Migraciones al día: `php artisan migrate`.
- Un usuario de prueba con sesión iniciable (cualquier rol: administrador, pastor, líder o usuario).

## Preparación

```powershell
cd laravel_app
php artisan migrate
npm run build
```

## Escenarios de validación

### 1. Cambiar el idioma de toda la interfaz (Historia de Usuario 1 / RF-001, RF-002)

1. Iniciar sesión con cualquier usuario.
2. En el header, pulsar el control de idioma correspondiente a inglés.
3. **Esperado**: la pantalla se recarga y el menú, los botones, las etiquetas y los mensajes de validación aparecen en inglés. Ver el contrato de la petición en [`contracts/idioma-route.md`](./contracts/idioma-route.md).
4. Pulsar el control correspondiente a español.
5. **Esperado**: la interfaz vuelve a mostrarse en español, con el mismo texto que tenía antes de esta funcionalidad.

### 2. Persistencia de la preferencia entre sesiones (Historia de Usuario 2 / RF-003, RF-004)

1. Con el idioma en inglés (paso anterior), cerrar sesión.
2. Volver a iniciar sesión con el mismo usuario.
3. **Esperado**: la interfaz aparece en inglés sin tener que volver a elegirlo. Ver el campo `idioma` en [`data-model.md`](./data-model.md).
4. Iniciar sesión con un usuario que nunca ha cambiado su idioma.
5. **Esperado**: la interfaz aparece en español de forma predeterminada.

### 3. Respaldo a español cuando falta traducción (Historia de Usuario 3 / RF-006)

1. Con el idioma en inglés, visitar una pantalla cuya clave de traducción no exista todavía en `lang/en/interfaz.php`.
2. **Esperado**: el texto aparece en español (no vacío, no una clave cruda tipo `interfaz.alguna_clave`).

### 4. Grillas estándar en el idioma activo (Principio III / RF-002)

1. Con el idioma en inglés, abrir cualquier pantalla con una grilla estándar (`<x-ui.grilla>`).
2. **Esperado**: los textos fijos de la grilla (buscar, filas por página, paginador) también están en inglés, no solo el contenido de las filas.

### 5. Funciona sin JavaScript (RF-007)

1. Deshabilitar JavaScript en el navegador (o revisar el HTML generado).
2. Enviar el formulario de cambio de idioma.
3. **Esperado**: el idioma cambia igual, porque es un `POST` normal con un botón de envío, sin depender de `onchange` ni de peticiones asíncronas.

### 6. Tamaño táctil en celular (RF-008)

```powershell
npm run test:movil
```

**Esperado**: la prueba pasa sin necesidad de agregar una excepción en `tests/movil/excepciones.json` para el nuevo control.

## Pruebas automáticas

```powershell
php artisan test --filter=Interfaz
php artisan test
npm test
```

**Esperado**: todas en verde, sin regresiones en las pruebas existentes (CE-004).
