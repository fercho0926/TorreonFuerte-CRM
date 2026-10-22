# Contrato: CSS y componentes móviles

## Reglas globales (`resources/css/movil.css`)

Activas con `@media (max-width: 767.98px), (pointer: coarse)`:

| Selector | Regla |
|----------|-------|
| `.btn`, `.nav-link`, `.page-link`, `.dropdown-item`, `a.btn-sm`, `.form-select`, `.form-control` | `min-height: 44px` |
| `.btn`, `.nav-link`, `.page-link`, botones de icono | `min-width: 44px` |
| `.form-check-input`, `.form-switch .form-check-input` | área de pulsación ≥ 44 px (relleno en la etiqueta) |
| `input:not([type=checkbox]):not([type=radio]), select, textarea, .select2-selection, .dt-container select` | `font-size: 16px` |
| `.app-header .nav-link` | 44 × 44 px |
| `#sede-activa` | alto 44 px, ancho máx. calculado, texto con `text-overflow: ellipsis` |

En escritorio (`min-width: 768px` y puntero fino) no se aplica ninguna.

## Barra superior (`layouts/admin.blade.php`)

- Botón de menú, selector de sede y menú de usuario con la regla anterior.
- Aviso «Todas las sedes: solo consulta»: en teléfono se muestra en una franja bajo la barra (`.aviso-sede-movil`), en escritorio en la barra.
- Margen superior/inferior con `env(safe-area-inset-top|bottom)`.

## Grilla estándar en teléfono (`<x-ui.grilla>`, `grilla.js`, `grilla.css`)

| Elemento | Comportamiento por debajo de 768 px |
|----------|-------------------------------------|
| `table.grilla` | `display:block`; cada `tr` es una tarjeta; cada `td` muestra `data-etiqueta` |
| Columnas de prioridad ≤ 3 y acciones | visibles |
| Columnas de prioridad > 3 | clase `.grilla-sec`, ocultas hasta pulsar `.grilla-mas` de la tarjeta |
| `tr.grilla-filtros` | oculta; se muestra dentro de `.grilla-panel-filtros` al pulsar «Filtros (n)» |
| Botón `[data-grilla-filtros]` | en la barra de la grilla; `n` = filtros activos |
| Extensión Responsive | desactivada por debajo de 768 px |
| Formulario de búsqueda | una franja: búsqueda, interruptor, «Limpiar» con icono |

Sin JavaScript: se ve la tabla del servidor ya apilada por CSS (sin panel ni «Ver más»: se muestran todas las columnas).

## Tabla apilable (`.tabla-apilable`)

```html
<table class="table tabla-apilable">
  <thead>…</thead>
  <tbody>
    <tr><td data-etiqueta="Sede" data-primaria>Centro</td><td data-etiqueta="Personas">5</td></tr>
  </tbody>
</table>
```

Por debajo de 768 px: encabezado oculto accesiblemente, filas como tarjetas, etiqueta antepuesta a cada valor. Por encima: tabla normal.

## Componente de entrada (`<x-form.input>`)

Nuevos tipos que fijan teclado y autocompletado: `celular` → `type=tel inputmode=numeric autocomplete=tel`; `correo` → `type=email inputmode=email autocomplete=email`; `numero` → `inputmode=numeric`; `moneda` → `inputmode=decimal`. Sin cambios para el resto.
