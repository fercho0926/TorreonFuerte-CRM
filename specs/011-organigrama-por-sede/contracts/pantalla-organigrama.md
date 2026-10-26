# Contrato de Interfaz: Pantalla «Organigrama»

La funcionalidad expone una única interfaz de usuario web; no hay API JSON nueva.

## Ruta

| Método | Ruta | Nombre | Autorización | Respuesta |
|--------|------|--------|--------------|-----------|
| GET | `/organigrama` | `organigrama.index` | Sesión iniciada + habilidad `verOrganigrama` (admin, pastor, lider_celula) | HTML (200); redirección al inicio de sesión si no hay sesión; 403 para el rol `user` |

- No recibe parámetros. La sede sale del contexto de sede (usuario o sede activa del admin); no existe `?sede=`.
- Cabecera de respuesta: `Cache-Control: no-store` y ruta fuera del caché del service worker.
- Debe registrarse en `MatrizRutasTest` con su acceso por rol.

## Menú

Opción «Organigrama» (icono `fa-sitemap`) en la sección «Congregación», visible solo si el usuario cumple `verOrganigrama`; activa en `organigrama.*`.

## Estados de la pantalla

| Estado | Condición | Contenido |
|--------|-----------|-----------|
| Árbol | Hay al menos un nodo en el alcance | Cabecera de sede con pastores, líderes colapsados |
| Elegir sede | Admin en «Todas las sedes» | Mensaje que indica elegir una sede en el selector de la barra superior; sin datos |
| Vacío | Sede sin pastores, líderes ni miembros | Mensaje de estado vacío |
| Sin pastores | Sede con líderes pero sin pastor asignado | Cabecera con aviso «Sin pastor asignado» |

## Marcado del árbol (contrato para CSS, JS y pruebas)

```text
<section class="organigrama" data-organigrama>
  <div class="organigrama-acciones" hidden>   ← el JS lo muestra
    <button type="button" data-organigrama-accion="expandir">Expandir todo</button>
    <button type="button" data-organigrama-accion="colapsar">Colapsar todo</button>
  </div>
  <ul class="organigrama-arbol">
    <li class="organigrama-nodo" data-tipo="sede|lider|miembro|sin_lider">
      <!-- con hijos: -->
      <details>
        <summary>  nombre · rol · contador de hijos  </summary>
        <ul> …nodos hijos… </ul>
      </details>
      <!-- sin hijos: -->
      <div class="organigrama-tarjeta"> nombre · rol </div>
    </li>
  </ul>
</section>
```

Reglas del contrato:

- Ningún `<details>` lleva `open` al cargar (RF-003).
- El `<summary>` y todo control miden al menos 44 × 44 px y son operables con teclado (Enter/Espacio).
- El contador de hijos es texto visible dentro del `<summary>` («3 líderes», «5 miembros»); no depende de color.
- El nombre enlaza a la ficha (`personas.show`) solo cuando `puedeVerFicha` es verdadero; si no, es texto plano.
- Nombres largos: se truncan visualmente con CSS, con el nombre completo en el atributo `title`.
- Sin JavaScript: el árbol se expande y colapsa con `<details>` nativo; los botones de acciones permanecen ocultos.

## Eventos de JavaScript

| Control | Efecto |
|---------|--------|
| `[data-organigrama-accion="expandir"]` | Pone `open` en todos los `<details>` del árbol |
| `[data-organigrama-accion="colapsar"]` | Quita `open` de todos los `<details>` del árbol |

## Errores

Los errores de acceso usan las páginas de error existentes (`resources/views/errors`); la ruta no devuelve JSON.
