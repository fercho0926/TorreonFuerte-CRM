# Contrato de Interfaz: Pestaña «Asistencia» en la ficha de un evento

La funcionalidad reutiliza rutas e interfaces web existentes; no se agrega ninguna ruta, endpoint JSON ni parámetro nuevo salvo el valor `asistencia` de `?pestana=`.

## Ruta de la ficha

| Método | Ruta | Nombre | Autorización | Respuesta |
|--------|------|--------|---------------|-----------|
| GET | `/eventos/{evento}` | `eventos.show` | Sesión iniciada + alcance de sede/rol ya existente | HTML (200); 404 si el evento no es visible para el usuario |

- `?pestana=` acepta `datos` (por defecto), `asistencia` o `historico`; cualquier otro valor cae a `datos`, igual que antes.
- No hay cambio de autorización de la ruta: quien ya podía ver la ficha, ve las tres pestañas (con el contenido de cada una ajustado a su permiso, ver más abajo).

## Pestañas (marcado y orden)

```text
<ul class="nav nav-tabs mb-3 historial-pestanas">
  <li><a href="?">Datos</a></li>                        ← pestana=datos (o sin parámetro)
  <li><a href="?pestana=asistencia">Asistencia</a></li>  ← nuevo
  <li><a href="?pestana=historico"><i class="fas fa-history"></i> Histórico</a></li>
</ul>
```

Generado por `@include('historial._pestanas', ['url' => route('eventos.show', $evento), 'pestana' => $pestana, 'otras' => ['asistencia' => ['Asistencia', 'fa-user-check']]])`. El orden Datos → Asistencia → Histórico es el orden de inserción del arreglo `$todas` dentro del partial; no requiere ningún cambio en `historial._pestanas.blade.php`.

> **Nota de alcance (implementación, 2026-11-13)**: el panel «Registro por código QR» se quedó en **Datos** (a pedido explícito del usuario), no se trasladó a «Asistencia» como describía la tabla original de este contrato. Además, los miembros inscritos se muestran en una grilla estándar (`App\Grillas\InscripcionesEventoGrilla`, rutas `eventos.inscripciones.{index,datos,exportar}`) en vez de tarjetas; cada fila tiene un botón «Registrar abono» que abre un panel `<details>` con el historial de abonos y el alta de uno nuevo. Ver spec.md § Clarifications.

## Contenido por pestaña

| Pestaña | Contenido | Quién lo ve |
|---------|-----------|-------------|
| **Datos** | Nombre, tipo, fecha, lugar, costo por miembro, estado, descripción, total esperado, total recaudado | Cualquiera que vea la ficha |
| **Asistencia** | Formulario «Inscribir miembro»; panel «Registro por código QR» (QR, enlace, descarga, abrir/cerrar, regenerar, lista de registrados); contador «Miembros inscritos (N)»; una tarjeta por miembro inscrito con costo aplicable, total pagado, saldo pendiente, tabla de abonos y acción «Quitar» | El formulario de inscripción y las acciones (quitar, registrar abono, eliminar abono) solo con permiso de **escritura** en Eventos; el panel QR solo con esa misma escritura (regla `gestionarRegistroQr`, preexistente); la lista de tarjetas (sin botones de acción) se ve también con solo **lectura** |
| **Histórico** | Sin cambios respecto al comportamiento actual | Quien ya podía ver la ficha (RF-021 de spec 015) |

## Redirecciones tras una acción (cambia el destino, no la autorización)

| Acción | Ruta | Antes de este cambio | Después de este cambio |
|--------|------|------------------------|---------------------------|
| Inscribir miembro | `POST /eventos/{evento}/personas` (`eventos.personas.store`) | `eventos.show` (pestaña por defecto) | `eventos.show` con `?pestana=asistencia` |
| Quitar miembro | `DELETE /eventos/{evento}/personas/{inscripcion}` (`eventos.personas.destroy`) | ídem | ídem |
| Registrar abono | `POST /eventos/{evento}/personas/{inscripcion}/pagos` (`eventos.pagos.store`) | ídem | ídem |
| Eliminar abono | `DELETE /eventos/{evento}/personas/{inscripcion}/pagos/{pago}` (`eventos.pagos.destroy`) | ídem | ídem |

Ninguna de estas rutas, sus nombres, verbos ni reglas de validación cambian; solo el parámetro `pestana` del `redirect()->route(...)`.

## Compatibilidad con enlaces existentes

- `?pestana=datos` (o sin parámetro) y `?pestana=historico` siguen mostrando exactamente lo que mostraban antes de esta funcionalidad, salvo que «Datos» ya no incluye el formulario de inscripción, el panel QR ni las tarjetas de miembros (ese es el cambio pedido).
- Cualquier enlace o marcador guardado con esos dos valores sigue funcionando sin romperse.
