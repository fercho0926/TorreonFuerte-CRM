# Investigación: Niveles de formación de los miembros

## D1. Nombre del modelo y de las tablas
- **Decisión**: modelo `NivelFormacion` (tabla `niveles_formacion`), `AvanceNivel` (`avances_nivel`) y `NivelCambio` (`nivel_cambios`). El módulo de permisos se llama `niveles` y las rutas `niveles.*`.
- **Razón**: la clase `App\Support\Niveles` ya existe para los niveles de permiso (0 sin acceso, 1 lectura, 2 escritura); reutilizar el nombre confundiría. El nombre visible para las personas sigue siendo «Niveles».
- **Alternativa**: renombrar `Support\Niveles` (cambio ajeno a esta funcionalidad y con riesgo de regresión).

## D2. Plan común a todas las sedes
- **Decisión**: `niveles_formacion` no lleva `sede_id` ni el filtro global de sede; `avances_nivel` sí lleva `sede_id` (heredado del miembro) y el filtro global.
- **Razón**: aclaración de la especificación (un solo plan). El aislamiento se aplica a los datos de miembros.
- **Alternativa**: plan por sede (rechazada en la clarificación).

## D3. Un avance por miembro y nivel
- **Decisión**: `unique(persona_id, nivel_id)`. Una fila con `origen` (`encuentro` o `manual`), `fecha`, `encuentro_id` opcional, `registrado_por` y `nota`.
- **Razón**: el estado «completado» es binario; si existe un registro manual, la asistencia no lo duplica ni lo borra. Si solo existe el automático, la asistencia lo crea y lo retira.
- **Regla de recálculo** (`AvancesNivelService::recalcular(persona, nivel)`): si hay fila manual, no se toca; si no, se busca el **primer encuentro realizado de ese nivel al que el miembro asistió** (por fecha) y se crea o actualiza la fila automática con la fecha de ese encuentro; si no hay ninguno, se borra la fila automática.
- **Alternativa**: una fila por origen (permitiría duplicados y contradice «a lo sumo uno por nivel»).

## D4. Sincronía con encuentros
- **Decisión**: el controlador de encuentros llama al servicio tras guardar (alta, edición, asistencia) y antes de eliminar. El servicio recalcula solo los pares (miembro, nivel) afectados: los miembros del encuentro y el nivel anterior y el nuevo.
- **Razón**: la asistencia se actualiza con `updateExistingPivot`, que no dispara eventos de modelo; la llamada explícita es simple y comprobable.
- **Alternativa**: observadores o eventos de Eloquent (no se disparan con operaciones de tabla pivote).

## D5. Nivel actual y siguiente
- **Decisión**: para una persona, `CaminoService` arma las estaciones desde los niveles **activos** en orden y sus avances: `completado`, `siguiente` (primer pendiente) y `pendiente` (con indicador `saltado` si hay uno posterior completado). Nivel actual = el de mayor posición completado. Para listas, el alcance `Persona::enNivel($id)` usa una subconsulta agregada (máximo de posición entre avances de niveles activos) y `Persona::sinNivel()` la negación.
- **Razón**: una sola definición para pantalla y filtros, sin cargar todos los avances en memoria; compatible con SQLite, MySQL y SQL Server.

## D6. Orden de los niveles
- **Decisión**: posiciones enteras consecutivas (1..n) que incluyen los niveles desactivados; subir/bajar intercambia con el vecino dentro de una transacción. Alta al final.
- **Razón**: simple, sin arrastrar y soltar (que en celular es incómodo); sin dependencias.
- **Alternativa**: arrastrar y soltar con una librería (peso, accesibilidad y teclado difíciles).

## D7. Eliminar, desactivar y reactivar
- **Decisión**: se puede eliminar un nivel solo si no tiene avances ni encuentros vinculados; en otro caso se ofrece desactivar. Desactivar oculta el nivel del camino y de los conteos, conserva los avances y se puede revertir.
- **Razón**: RF-004 y casos límite; evita perder historia.

## D8. El camino gráfico
- **Decisión**: componente Blade `x-niveles.camino` que recibe las estaciones y su geometría y dibuja un contenedor con un SVG de fondo (sendero con curvas suaves y línea punteada para lo pendiente) y las estaciones como elementos HTML posicionados en porcentajes. Patrón en zigzag de cuatro posiciones horizontales (izquierda, centro, derecha, centro) y espacio vertical fijo por estación. La geometría la calcula `CaminoService::geometria(n)` (testeable). Cada estación es un círculo de 64 px con número, nombre y estado en texto; completada: marca y estrella; siguiente: resaltada y con pulso suave; pendiente: gris con candado. El detalle (fecha, origen, quién registró y el registro manual) va en un `<details>` nativo.
- **Razón**: funciona sin JavaScript, sin librerías, escala de 320 px a escritorio y es accesible (lista ordenada con texto por estación).
- **Alternativas**: canvas o librería de gráficos (no accesibles, más peso, sin respaldo sin JS); imágenes (no se adaptan ni son accesibles).

## D9. Accesibilidad y movimiento
- **Decisión**: `<ol>` con `aria-label`; cada estación expone su estado como texto visible («Completado el 12 de marzo», «Siguiente», «Pendiente»); el estado nunca depende solo del color (forma e icono distintos); `@media (prefers-reduced-motion: reduce)` desactiva el pulso y el brillo; contraste suficiente del texto sobre cada estado.
- **Razón**: RF-006 y RF-008.

## D10. Versión resumida para listas
- **Decisión**: `x-niveles.puntos` pinta una fila de puntos (uno por nivel activo) con etiqueta accesible («2 de 4 niveles; actual: Aliviando el fuego») y se usa en la columna «Nivel» de la grilla de miembros y en las listas por nivel.
- **Razón**: RF-005/RF-017. Los datos de la celda se obtienen de una consulta con carga ansiosa de avances (una consulta extra por página).

## D11. Permisos y menú
- **Decisión**: nuevo módulo `niveles` en `Modulos::catalogo()` (etiqueta «Niveles», icono `fa-route`, no solo lectura, rutas `niveles.*`), grupo de rutas con `modulo:niveles` y opción en Congregación. Migración que agrega el permiso a los roles existentes: administrador y pastor escritura; líder lectura; usuario lectura; roles personalizados sin acceso. Registrar y quitar avances exige escritura en `niveles` **y** que el miembro esté dentro del alcance del usuario. Ver el camino en la ficha exige lectura en `niveles`.
- **Razón**: consistente con la spec 013 y RF-020.

## D12. Historial de cambios
- **Decisión**: los avances usan el historial de miembros existente (`PersonaCambio`, con acciones nuevas `nivel_registrado`, `nivel_quitado` y `nivel_automatico`); los cambios de niveles usan una tabla nueva `nivel_cambios` (patrón de `SedeCambio` y `RolCambio`) visible al final del módulo.
- **Razón**: RF-021, sin inventar un mecanismo nuevo.

## D13. Traslado de sede
- **Decisión**: el traslado crea un registro nuevo del miembro en la sede de destino; `TrasladoService` copia sus avances al nuevo registro (origen `manual` con nota «Trasladado desde <sede>» y la fecha original; se conserva el origen de encuentro como texto en la nota) para que el camino no se pierda.
- **Razón**: RF-022; el plan es común, así que no hay que traducir niveles.

## D14. Tablero
- **Decisión**: tarjeta «Miembros por nivel» en el tablero, alimentada por `NivelesService::resumen($usuario)` (una consulta agregada sobre miembros visibles), visible con lectura en `niveles`.
- **Razón**: RF-018 y la spec 013 (cada tarjeta respeta el permiso de su módulo).

## D15. Datos iniciales
- **Decisión**: la migración inserta los cuatro niveles (posiciones 1 a 4, activos) si la tabla está vacía; no hay datos por sede.
- **Razón**: RF-002; funciona en instalaciones nuevas y existentes.
