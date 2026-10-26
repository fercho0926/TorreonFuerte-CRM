# Contrato: camino de niveles y componentes

## `CaminoService`

```text
camino(Persona $miembro): array
  → [
      'estaciones' => [ ['nivel' => NivelFormacion, 'numero' => 1, 'estado' => 'completado|siguiente|pendiente',
                         'saltado' => bool, 'fecha' => Carbon|null, 'origen' => 'encuentro|manual|null',
                         'encuentro' => Encuentro|null, 'registrado_por' => User|null, 'avance' => AvanceNivel|null], … ],
      'actual' => NivelFormacion|null,      // el de mayor posición completado
      'siguiente' => NivelFormacion|null,   // primer activo sin completar
      'completados' => int, 'total' => int,
      'terminado' => bool,                  // todos completados
      'sin_iniciar' => bool,
    ]

geometria(int $estaciones): array
  → ['alto' => int, 'puntos' => [ ['x' => 0..100, 'y' => 0..alto], … ], 'sendero' => 'M … C …'] // trazado SVG
```

Reglas: usa solo niveles activos en orden; la «siguiente» es la primera estación sin completar (aunque haya una completada posterior, caso saltado); con 0 estaciones devuelve una estructura vacía sin error. Una sola consulta de avances del miembro más una de niveles activos.

## Componente `<x-niveles.camino :miembro="$persona" />`

Salida:

```html
<section class="camino" aria-labelledby="camino-titulo" data-estaciones="4">
  <h3 id="camino-titulo">Camino de niveles</h3>
  <p class="camino-resumen">2 de 4 niveles · Nivel actual: Aliviando el fuego</p>
  <div class="camino-mapa" style="--alto: 520px">
    <svg class="camino-sendero" viewBox="0 0 100 520" preserveAspectRatio="none" aria-hidden="true">…</svg>
    <ol class="camino-estaciones">
      <li class="estacion estacion--completado" style="--x: 20%; --y: 40px">
        <span class="estacion-circulo" aria-hidden="true">★</span>
        <div class="estacion-texto">
          <strong>1 · Libres para servir</strong>
          <span>Completado el 12 de marzo de 2026</span>
          <details> … fecha, origen (encuentro o registro manual), quién lo registró, [Quitar] </details>
        </div>
      </li>
      …
    </ol>
  </div>
  @puedeModulo('niveles','escritura') <details class="camino-registrar"> formulario: nivel, fecha, nota, «Marcar completado» </details> @endpuedeModulo
</section>
```

Estados y señales (nunca solo color): **completado** = círculo relleno, marca/estrella y fecha en texto; **siguiente** = círculo resaltado con borde grueso, texto «Siguiente» y pulso suave; **pendiente** = círculo gris con candado y texto «Pendiente» (o «Pendiente (saltado)»).

Tamaños: círculo de 64 px (44 px como mínimo en pantallas de 320 px); botones del detalle de 44 px; texto de formulario de 16 px.

Sin JavaScript: todo el contenido es legible y los formularios funcionan; los `<details>` son nativos.

Movimiento: `@media (prefers-reduced-motion: no-preference)` aplica el pulso y el brillo; con `reduce` no hay animación.

Estados vacíos: sin niveles activos → «El plan de niveles aún no tiene niveles» (con enlace al módulo si hay escritura); sin avances → «Aún no inicia su camino»; todos completados → «¡Camino terminado!».

## Componente `<x-niveles.puntos :miembro="$persona" />`

Fila de puntos (uno por nivel activo; completados rellenos) con `role="img"` y `aria-label="2 de 4 niveles; actual: Aliviando el fuego"`, más el nombre del nivel actual en texto (o «Sin iniciar»). Usa avances precargados (`Persona::with('avances')`) para no hacer consultas por fila.

## Grilla `NivelesMiembrosGrilla` (`id` = `niveles.miembros`)

| Columna | Orden | Filtro | Prioridad en teléfono |
|---------|-------|--------|-----------------------|
| Nombre | apellidos, nombres | texto | 1 |
| Nivel | posición del nivel actual | lista (niveles y «Sin iniciar») | 2 |
| Celular y contacto | celular | texto | 3 |
| Completó el nivel el | fecha del avance del nivel elegido | — | 4 |
| Sede (solo administrador en todas las sedes) | nombre de sede | — | 50 |
| Acciones | — | — | 1 (ver ficha; «Llamar» y «WhatsApp») |

Base: miembros visibles (`AlcanceService::personasVisibles`), activos salvo `inactivos`. CSV: nombre, celular, correo, nivel actual, fecha del último nivel completado, sede.

## Cambios en la grilla de miembros

Columna «Nivel» (`x-niveles.puntos`, prioridad 3) y filtro de lista con los niveles activos y «Sin iniciar» (parámetro `nivel`). Orden por posición del nivel actual.

## Cambios en encuentros

Formulario: selector opcional «Nivel del plan». Listado y detalle: columna y línea «Nivel». Cambiar el nivel o la asistencia recalcula los avances automáticos afectados.

## Tablero

Tarjeta «Miembros por nivel»: por cada nivel activo, su nombre, conteo de miembros con ese nivel actual y enlace a `niveles.miembros.index?nivel=…`, más «Sin iniciar». Solo con lectura en `niveles`; los conteos respetan el alcance del usuario.
