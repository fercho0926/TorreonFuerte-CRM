# Investigación: Organigrama interactivo por sede

No quedaban marcadores `NEEDS CLARIFICATION`; estas son las decisiones técnicas tomadas.

## D1. Representación del organigrama: HTML anidado con `<details>`, no una librería de gráficos

- **Decisión**: árbol de `<ul>/<li>` renderizado en el servidor; cada nodo con hijos es un `<details>` cuyo `<summary>` es la tarjeta del nodo.
- **Justificación**: funciona sin JavaScript (principio V), es accesible por teclado y lector de pantalla de forma nativa, no agrega peso (el presupuesto de peso de `tests/movil/peso.spec.js` es estricto) y encaja con la simplicidad (VI).
- **Alternativas descartadas**: librería de diagramas (d3, OrgChart.js, GoJS): añade cientos de KB, exige JS y es difícil de volver usable en 320 px. Canvas/SVG propio: sin accesibilidad nativa y más código.

## D2. Responsive: un solo marcado, dos presentaciones por CSS

- **Decisión**: base móvil = árbol vertical con sangría y línea guía; desde 992 px = organigrama horizontal clásico (hijos en fila bajo el padre con conectores con pseudo-elementos). Si el ancho del árbol horizontal supera el contenedor, el desplazamiento horizontal queda **dentro** de un contenedor propio (`overflow-x: auto`), nunca en la página.
- **Justificación**: cumple RF-008 / CE-003 sin duplicar marcado; los miembros (tercer nivel) se mantienen siempre en lista vertical dentro del líder, porque decenas de miembros en fila no son legibles.
- **Alternativas descartadas**: dos vistas distintas según dispositivo (duplica pruebas y datos); zoom/pan táctil (complejo y choca con el desplazamiento nativo).

## D3. Fuente de datos: relaciones existentes, sin migraciones

- **Pastores**: `sedes.pastor_persona_id` y `sedes.pastora_persona_id` de la sede efectiva.
- **Líderes**: personas de la sede con `es_lider = true` y estado distinto de `inactivo`.
- **Miembros de un líder**: unión (sin duplicados) de las personas con `lider_id = líder` y de las que están en alguna célula que ese líder lidera o co-lidera (`celula_persona`), excluyendo inactivas y al propio líder.
- **Sin líder asignado**: `Persona::sinLider()` (ya excluye inactivos, líderes y «En célula»), solo visible para roles de acceso total.
- **Justificación**: es la misma noción de «miembros de un líder» que ya usa `AlcanceService::personasVisibles` para el rol Líder, por lo que el organigrama no se contradice con los listados.
- **Alternativa descartada**: tabla nueva de jerarquía (duplicaría datos y obligaría a sincronizarlos).

## D4. Alcance por rol y sede

- **Decisión**: el servicio toma la sede de `SedeContext` (filtro global `SedeScope` ya aplicado) y el usuario de `Controller::usuarioDeLaPeticion()`.
  - Admin con sede activa, Pastor: árbol completo de la sede.
  - Líder de célula: solo su rama (él como raíz con sus miembros); no ve pastores ni otros líderes.
  - Rol `user`: sin acceso (403), igual que otros módulos de gestión.
  - Admin en «Todas las sedes»: la pantalla pide elegir una sede y no muestra datos.
- **Justificación**: el selector de sede activa de la barra superior ya existe (`SedeActivaController`); reutilizarlo cumple RF-010 sin un segundo mecanismo. No se acepta `sede_id` por la URL, así que no hay vector para pedir una sede ajena (RF-012).
- **Alternativa descartada**: parámetro `?sede=` propio: duplicaría la validación de alcance.

## D5. Cantidad de datos y rendimiento

- **Decisión**: una consulta por nivel (pastores, líderes, miembros por pivote y por `lider_id`, sin líder) con solo las columnas necesarias (`id`, `nombres`, `apellidos`, `lider_id`, `estado`) y agrupación en memoria; sin N+1. Todo el árbol va en el HTML; los niveles inferiores están cerrados (`<details>` sin `open`).
- **Justificación**: una sede de 500 miembros son ~500 nodos pequeños, bien por debajo del umbral donde la carga diferida (AJAX por líder) compensa su complejidad y sus fallas sin JS.
- **Alternativa descartada**: carga perezosa por líder: más endpoints que proteger, no funciona sin JS.

## D6. «Expandir todo / Colapsar todo»

- **Decisión**: dos botones que alternan el atributo `open` de todos los `<details>` del árbol; el HTML los incluye ocultos (`hidden`) y el JS los muestra, de modo que sin JS no aparecen botones muertos. Cada nodo muestra el contador de hijos en el propio `<summary>`.
- **Justificación**: trivial de probar con Vitest/jsdom y sin dependencia de estado oculto.

## D7. Sin datos en el teléfono

- **Decisión**: `/organigrama` no se agrega al caché del service worker y la respuesta se sirve con `Cache-Control: no-store` (como el resto de pantallas con datos), cumpliendo RF-017 y el principio VII.

## D8. Terminología

- **Decisión**: los textos visibles dicen «miembros». Los identificadores de código siguen el estado actual del repositorio (`Persona`, `personas`) mientras la funcionalidad 010 no se aplique; si 010 se implementa primero, el plan de tareas debe usar los nombres que resulten de esa renombración.
