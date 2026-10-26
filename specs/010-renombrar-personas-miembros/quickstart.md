# Guía Rápida de Validación: Miembros

## Requisitos previos

- Copia de respaldo de `laravel_app/database/database.sqlite` (nunca `migrate:fresh` sobre la base local).
- Dependencias instaladas (`composer install`, `npm install`).

## 1. Antes del cambio: línea base

1. Anotar conteos: miembros, cambios, inscripciones a eventos, pertenencias a células/ministerios, usuarios con miembro vinculado, registros QR.
2. Guardar una captura del menú y del listado actual para comparar.

## 2. Después de cada etapa

Desde `laravel_app/`:

```text
php artisan test
npm test
npm run build
npm run test:movil
```

Resultado esperado: todo en verde (SC-004).

## 3. Etapa 3: migración de base de datos

1. Aplicar `php artisan migrate` sobre una copia de la base.
2. Verificar que los conteos coinciden con la línea base (SC-003).
3. Ejecutar `php artisan migrate:rollback --step=1` y luego `migrate` otra vez: mismo esquema y mismos conteos.

## 4. Recorrido manual (escritorio y celular)

Con un usuario de cada rol (SuperAdmin, Admin, Pastor, Líder):

1. El menú muestra «Miembros»; el listado, la ficha, el formulario y los mensajes dicen «miembro(s)» (SC-001, SC-006).
2. Crear, editar, inactivar, reactivar, trasladar y eliminar un miembro.
3. Inscribir un miembro a un evento y registrar un pago; añadirlo a una célula y a un ministerio; designar líder.
4. Exportar el listado: nombre y encabezados con «miembros».
5. Registro público por QR y recuperación de claves: textos con «miembro».
6. Un Líder solo ve los miembros de su alcance; otra sede no es accesible por ninguna dirección (principio II).

## 5. Direcciones antiguas

Abrir `/personas`, `/personas/{id}`, `/personas/exportar?q=ana`: redirigen a `/miembros…` conservando `?q=ana` (SC-002).

## 6. Búsqueda de rastros

Buscar «persona» en `resources/views`, `lang`, `config/menu.php`, mensajes de `app/` y `public/manifest.webmanifest`: solo deben quedar las excepciones declaradas en la prueba guardiana (p. ej. «datos personales»).

## 7. App instalada

Con la app instalada en el celular, abrirla con red tras el despliegue: aparece «Miembros» sin reinstalar (SC-005).
