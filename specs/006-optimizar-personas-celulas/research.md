# Investigación (Fase 0): Personas, Líderes, Células y Ministerios

No quedan incógnitas ni marcadores `NEEDS CLARIFICATION`.

## D1. Autocompletado con búsqueda en el servidor

- **Decisión**: un endpoint `GET /personas/buscar` devuelve JSON paginado (`resultados[]` con `id`, `texto`, `apoyo`, `sinDocumento`, `estado`, y `mas`). Select2 lo consulta con retardo de 250 ms, mínimo 2 letras y 20 resultados por página. Ordena por apellido y nombre y busca con `Persona::buscar()` (ya normaliza nombres, apellidos, documento y celular por palabras).
- **Justificación**: SC-003 y SC-006 piden sugerencias en < 1 s con 500 personas; cargar todas las personas en cada formulario no escala y la búsqueda del servidor reutiliza la lógica y los índices actuales.
- **Alternativas**: cargar todas las opciones en la página y filtrar en el navegador (descartada: pesa y no escala; hoy el multiselect solo ofrece búsqueda desde 10 opciones y se vuelve lento); un paquete de autocompletado nuevo (descartada: Select2 ya está).

## D2. Un solo componente para designar líder, células y ministerios

- **Decisión**: `x-form.select` y `x-form.multiselect` aceptan `fuente` (URL de búsqueda) y `filtros` (`activas`, `sin_lideres`, `excluir`). El módulo `buscador-remoto.js` activa Select2 en modo remoto; las opciones ya seleccionadas se cargan en el HTML para que se vean al editar. Sin JavaScript el `<select>` nativo muestra solo lo ya seleccionado y el formulario sigue enviándose.
- **Justificación**: FR-012 pide el mismo componente en células y ministerios; evita tres implementaciones.
- **Alternativas**: componentes distintos por pantalla (descartada por duplicación).

## D3. Alta rápida de personas desde la célula

- **Decisión**: el multiselect de células muestra un botón «Crear persona nueva» que abre un diálogo (SweetAlert2) con nombres, apellidos, celular y correo; `POST /personas/rapida` valida con las reglas de `PersonaRequest` (celular o correo obligatorio), fuerza el estado `celula`, asigna el líder directo solo si quien crea es un líder (como hoy en `store`), devuelve `{id, texto, apoyo}` y el componente lo añade seleccionado. Si hay posible duplicado responde 409 con la persona parecida y el diálogo pide confirmar (`confirmar_duplicado`), igual que el formulario normal.
- **Justificación**: FR-009 y la historia 5; reutiliza el aviso de duplicados y las reglas existentes sin salir de la célula.
- **Alternativas**: abrir el formulario completo en otra pestaña (descartada: rompe el flujo); crear sin aviso de duplicados (descartada).

## D4. La pertenencia a una célula no depende del líder directo

- **Decisión**: se elimina la comprobación de `lider_id` en `CelulasController::agregarMiembro` y se sustituye por la sincronización de `integrantes[]` desde el formulario (`store`/`update`) y desde la ficha (`celulas.miembros.store` acepta `personas[]`). Añadir a una célula nunca cambia `lider_id`. Para una persona ya integrante se conserva `fecha_ingreso`; las nuevas reciben la fecha de hoy. Solo se aceptan personas visibles para quien guarda y no inactivas; las inactivas que ya eran integrantes se conservan.
- **Justificación**: decisión B de la clarificación; cubre FR-008, FR-010 y FR-014.
- **Alternativas**: asignar el líder directo al añadir (descartada por el usuario).

## D5. Estado «En célula»

- **Decisión**: nuevo valor `celula` (etiqueta «En célula») en `Persona::ESTADOS`; no exige documento; cuenta como activo; se añade a `Estados` con color propio (`dark`) y al filtro de estados. `Persona::scopeSinLider` lo excluye para que no aparezca en «Sin líder asignado». No hay migración de esquema: el estado ya es texto.
- **Justificación**: FR-006 y supuestos.
- **Alternativas**: una bandera aparte `en_celula` (descartada: duplica el concepto de estado).

## D6. Eliminar «Red» como se hizo con «Zona»

- **Decisión**: migración `2026_10_25_000001_drop_redes` que quita la clave foránea y la columna `personas.red_id` y la tabla `redes` (con `down()` que las recrea vacías); se eliminan `Red`, `RedesController`, la vista, las rutas, la opción de menú, los filtros, la ayuda de términos, `AlcanceService::redesVisibles`, `designarLider(…, Red)`, la fábrica y el sembrado, el bloque «Personas por red» del tablero y `redes` de `MigracionSedes::TABLAS`. `MigracionRedesZonas::clave()` se conserva si algo más la usa; en caso contrario se retira con su clase.
- **Justificación**: FR-004; mismo camino ya probado con Zona (migración aditiva + limpieza de código y pruebas).
- **Alternativas**: conservar el dato sin mostrarlo (descartada por el usuario: «quitarlo de todos lados»).

## D7. Formulario de personas en una sola tarjeta

- **Decisión**: se fusionan las secciones «Contacto» y «Estado y documento» dentro de «Datos personales» en `personas/_form.blade.php`, conservando atributos de `data-*`, máscaras, ayudas y reglas de dependencia (documento obligatorio según estado). La ficha (`show`) agrupa esos datos en el mismo bloque.
- **Justificación**: FR-001 a FR-003; cambio de vista sin tocar validaciones.
- **Alternativas**: dejar las tarjetas colapsadas (descartada).

## D8. Grilla «Personas a su cargo»

- **Decisión**: en la ficha del líder, tabla compacta (nombre con enlace, estado con `x-ui.estado`, celular, célula) dentro de una tarjeta con altura máxima y desplazamiento, ordenada por apellido; carga con `with('celulas')` para evitar consultas repetidas y respeta `AlcanceService`. Solo lista a quienes tienen al líder como líder directo.
- **Justificación**: FR-007 y el caso límite acordado.
- **Alternativas**: paginar la grilla (descartada: una altura limitada basta con cientos de personas y evita otro recorrido).

## D9. Menú

- **Decisión**: en `config/menu.php` «Ministerios» pasa a la sección Congregación (después de Células) y se retira «Redes». Las pruebas de menú y de marco ya leen el archivo.
- **Justificación**: FR-011.
- **Alternativas**: ninguna razonable.

## D10. Estrategia de verificación

- **Decisión**: PHPUnit para el buscador (alcance por sede, filtros, paginación, personas inactivas y líderes excluidos), el alta rápida (duplicados, estado, alcance del líder), las células (sincronizar integrantes sin tocar `lider_id`), la migración (sin pérdida de datos), el formulario de personas (una tarjeta), el menú y la ausencia de «red» (prueba de guardia que busca el texto en vistas y rutas); Vitest para `buscador-remoto.js` y `alta-rapida.js`; recorrido en navegador con 500 personas sembradas.
- **Justificación**: SC-001 a SC-008.
- **Alternativas**: solo pruebas manuales (descartada).

## Riesgos

| Riesgo | Mitigación |
|--------|-----------|
| La búsqueda remota rompe selectores existentes | El modo remoto solo se activa con `data-fuente`; las pruebas de 003 se mantienen |
| Un líder crea personas fuera de su ámbito | El alta rápida asigna el líder directo como hoy y la búsqueda usa `AlcanceService` |
| Pérdida de datos al retirar redes | Prueba de conteos antes y después; migración probada sobre copia de la base |
| Duplicados al crear personas desde la célula | Reutiliza el aviso de duplicados existente |
| Regresión de pruebas que usan `lider(red)` | Se ajusta la fábrica y se actualizan las pruebas en la misma fase |
