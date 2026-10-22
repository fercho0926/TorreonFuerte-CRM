# Modelo de Datos: Experiencia móvil

No hay cambios de esquema ni de datos persistentes. Los conceptos son de presentación y de configuración de la aplicación instalable.

## Regla táctil (valores de diseño)

| Valor | Descripción |
|-------|-------------|
| `--tactil-min` | 44 px: alto y ancho mínimos de todo control tocable en teléfono |
| `--campo-letra` | 16 px: tamaño mínimo de letra de campos de texto, selectores y buscadores en teléfono |
| `--quiebre-movil` | 768 px: por debajo se usan tarjetas y paneles |
| `--seguro-*` | márgenes de área segura del teléfono (muesca, barra inferior) |

## Columna de grilla (extensión de la spec 007)

La prioridad de columna ya existe. En teléfono: **prioridad 1 a 3 y acciones = visibles en la tarjeta**; **prioridad mayor a 3 = bajo «Ver más»**. Se agrega a cada celda la etiqueta de su columna (`data-etiqueta`) para mostrarla en la tarjeta.

## Tabla apilable

| Atributo | Descripción |
|----------|-------------|
| clase `tabla-apilable` | marca una tabla que en teléfono se apila como tarjetas |
| `data-etiqueta` por celda | texto de la columna que se antepone al valor |
| `data-primaria` (opcional) | celda que hace de título de la tarjeta |

## Manifiesto de la aplicación

| Campo | Valor |
|-------|-------|
| `name` | «CRM Torreón Fuerte» (tomado de `APP_SISTEMA`) |
| `short_name` | «Torreón» |
| `start_url` | `/dashboard` |
| `display` | `standalone` |
| `theme_color` / `background_color` | color de la marca / blanco |
| `lang` | `es` |
| `icons` | 192, 512 y maskable 512 (PNG) |

## Política de la caché del dispositivo

| Recurso | Estrategia | Se guarda |
|---------|------------|-----------|
| `/build/assets/*`, `/iconos/*` | caché primero (nombres con huella) | Sí (solo interfaz) |
| `offline.html` | se guarda al instalar | Sí |
| Navegaciones (HTML) | red primero; si falla, `offline.html` | **No** |
| `*/datos`, `*/exportar`, `*/buscar`, formularios, cualquier JSON/CSV | solo red | **No** |

Regla absoluta (RF-013b): ninguna respuesta que contenga datos de personas, líderes, células, eventos o usuarios se almacena en el dispositivo.

## Respaldo de formulario (navegador)

| Campo | Descripción |
|-------|-------------|
| clave | `crm.borrador.<ruta>` en `sessionStorage` |
| contenido | valores de campos de texto, selectores y casillas; **excluye** contraseñas, tokens y archivos |
| ciclo de vida | se guarda al escribir (con retardo), se restaura al volver a la pantalla tras un fallo, se borra al enviar con éxito o al cerrar la pestaña |
