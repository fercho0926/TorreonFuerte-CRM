# Guía Rápida de Validación: Historial de cambios en toda la plataforma

**Funcionalidad**: `015-historial-cambios`

Guía para comprobar de punta a punta que la funcionalidad cumple la spec. Los detalles de datos y rutas están en [data-model.md](data-model.md), [contracts/rutas-y-permisos.md](contracts/rutas-y-permisos.md) y [contracts/servicio-historial.md](contracts/servicio-historial.md).

## Requisitos previos

- Desde `laravel_app/`: `composer install` y `npm install` hechos.
- Base local migrada **sin** `migrate:fresh` (constitución): `php artisan migrate`.
- Usuarios de ejemplo del seeder: administrador (`admin@example.com`), un pastor de una sede y un líder de célula.

## 1. Migración de los historiales existentes (CE-007)

```bash
php artisan test --filter=MigracionHistorialTest
```

Antes de migrar la base local, anota los conteos de `persona_cambios`, `sede_cambios`, `rol_cambios` y `nivel_cambios`. Después de `php artisan migrate`, la tabla `historial_cambios` debe tener al menos la suma de esos conteos, y las cuatro tablas viejas ya no deben existir. Abre la ficha de un miembro que tuviera historial: sus entradas antiguas aparecen en la pestaña «Histórico».

## 2. Histórico del miembro (Historia 1)

1. Inicia sesión como pastor. Edita un miembro y cambia su **dirección** y su **celular**.
2. Agrégalo a un encuentro y marca su asistencia. Inscríbelo en un evento y registra dos abonos. Agrégalo a una célula y luego a un ministerio.
3. Abre `personas/{id}?pestana=historico`.

**Esperado**: secciones «Cambios de datos», «Encuentros y retiros», «Células y ministerios», «Liderazgo», «Niveles» y «Sede», cada una con su contador.
- «Cambios de datos» muestra «Datos editados» con «Dirección: … → …» y «Celular: … → …», con la fecha, la hora y el nombre del pastor.
- «Encuentros y retiros» muestra «Inscrito al encuentro», «Asistió al encuentro» (con la fecha del encuentro y enlace), «Inscrito al evento» y los dos abonos con su valor.
- Las secciones vacías dicen «Sin movimientos».
- Filtra por un rango de fechas: el filtro se aplica a todas las secciones.

## 3. Registro en todos los módulos (Historia 2)

```bash
php artisan test --filter=CoberturaHistorialTest
```

Recorre todas las rutas de escritura y comprueba que cada una deja su entrada. Pruebas manuales rápidas:
- Edita un miembro sin cambiar nada y guarda: **no** aparece una entrada nueva.
- Envía un formulario con un error de validación: **no** aparece entrada.
- Regístrate por el QR público de un evento: la entrada dice «Registro público por QR · …».
- Marca la asistencia a un encuentro vinculado a un nivel: aparece «Nivel completado por encuentro» como «Automático · por *usuario*».
- Genera una clave para un usuario: aparece «Clave generada» y ningún valor de clave.

## 4. Pestaña en los demás módulos (Historia 3)

Abre `?pestana=historico` en una célula, un ministerio, un evento, un encuentro, una sede y un rol, y en `niveles/{id}/edit` y `users/{id}/edit`. En cada una, realiza un cambio y comprueba la entrada. En un rol, cambia el acceso de «Eventos» de lectura a escritura y verifica «Eventos: lectura → escritura».

## 5. Control de cambios (Historia 4)

1. Como administrador, abre «Control de cambios»: aparecen todas las entradas, de la más reciente a la más antigua.
2. Filtra por usuario, módulo y rango de fechas.
3. Elimina una célula y búscala por su nombre: aparece su eliminación con los datos que tenía.
4. Comprueba que **no** hay botón de exportar y que `/control-cambios/exportar` responde 404.
5. Como pastor (sin el módulo concedido): el menú no muestra «Control de cambios» y la ruta responde 403. Concede el módulo al rol pastor desde Roles: el pastor ve solo las entradas de su sede.

## 6. Inalterabilidad y traslado (Historia 5 y casos límite)

- Ninguna entrada tiene botones de editar ni borrar; `php artisan route:list --path=control-cambios` solo muestra rutas `GET`.
- Elimina un usuario que hizo cambios: sus entradas siguen mostrando su nombre.
- Traslada a un miembro a otra sede (como administrador) y entra con el pastor de la sede destino: ve **todo** el histórico anterior del miembro, con la sede de origen indicada y sin enlaces a las fichas de la otra sede.

## 7. Aislamiento, celular y suite completa

```bash
php artisan test --filter=Historial
php artisan test
npm test
npm run test:movil
npm run build
```

**Esperado**: todo en verde. La auditoría móvil incluye las pestañas `personas/1?pestana=historico`, `celulas/1?pestana=historico`, `eventos/1?pestana=historico` y `control-cambios`, sin excepciones nuevas en `tests/movil/excepciones.json`. Las pruebas de aislamiento confirman que ningún usuario ve entradas de otra sede, salvo la cadena del miembro trasladado (CE-006).

## Línea base (2026-10-23, antes de implementar)

| Comando | Resultado |
|---------|-----------|
| `php artisan test` | 858 pruebas: 857 pasan, 1 omitida, 1 riesgosa (6 007 aserciones) |
| `npm test` | 18 archivos, 114 pruebas en verde |
| `npm run build` | correcto |
| `npm run test:movil` | 401 pasan, 106 omitidas, **3 fallan ya antes de esta funcionalidad**: `carga.spec.js` (/personas con 4G lenta, movil-390), `auditoria.spec.js` (/cambiar-clave, movil-horizontal-740) y `tablero.spec.js` (accesos rápidos, movil-horizontal-740) |

Conteos de la base local: `persona_cambios` = 5, `sede_cambios` = 1, `rol_cambios` = 0, `nivel_cambios` = 1 (total 7).
