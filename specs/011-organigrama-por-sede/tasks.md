---

description: "Lista de tareas para implementar el organigrama interactivo por sede"
---

# Tareas: Organigrama interactivo por sede

**Entrada**: Documentos de diseño en `/specs/011-organigrama-por-sede/`

**Prerrequisitos**: plan.md, spec.md, research.md, data-model.md, contracts/pantalla-organigrama.md, quickstart.md

**Pruebas**: Obligatorias por el principio IV de la constitución (PHPUnit, Vitest y `npm run test:movil`); se escriben antes o junto con el código y deben fallar antes de implementar.

**Organización**: Las tareas se agrupan por historia de usuario para poder implementarlas y probarlas de forma independiente.

## Formato: `[ID] [P?] [Historia] Descripción`

- **[P]**: Se puede ejecutar en paralelo (archivos distintos, sin dependencias pendientes)
- **[HU]**: Historia de usuario a la que pertenece (HU1…HU5)
- Todas las rutas son relativas a la raíz del repositorio; el código vive en `laravel_app/`

## Convenciones de ruta

- PHP: `laravel_app/app/...`, pruebas en `laravel_app/tests/Feature/Organigrama/`
- Vistas: `laravel_app/resources/views/organigrama/`
- JS y CSS: `laravel_app/resources/js/organigrama/`, `laravel_app/resources/css/organigrama.css`
- Nota: mientras la funcionalidad 010 no se aplique, los modelos se llaman `Persona`/`personas`; los textos visibles dicen «miembros».

---

## Fase 1: Preparación

**Propósito**: Estructura mínima y verificación del punto de partida.

- [X] T001 Verificar la línea base ejecutando `php artisan test` y `npm test` en `laravel_app/` y anotar si hay pruebas en rojo previas a este trabajo.
- [X] T002 [P] Crear los directorios vacíos `laravel_app/resources/views/organigrama/`, `laravel_app/resources/js/organigrama/` y `laravel_app/tests/Feature/Organigrama/`.

---

## Fase 2: Fundamentos (bloquea todas las historias)

**Propósito**: Ruta, permiso, menú y esqueleto de controlador/servicio que todas las historias necesitan.

**⚠️ CRÍTICO**: Ninguna historia puede empezar hasta terminar esta fase.

- [X] T003 Agregar la habilidad `verOrganigrama(User $user): bool` en `laravel_app/app/Policies/PersonaPolicy.php`, verdadera solo para los roles `admin`, `pastor` y `lider_celula` (falsa para `user`).
- [X] T004 Agregar la ruta `GET /organigrama` con nombre `organigrama.index` apuntando a `OrganigramaController@index` en `laravel_app/routes/web.php` (junto a las rutas de Congregación) e importar el controlador.
- [X] T005 Crear `laravel_app/app/Services/OrganigramaService.php` con el método público `construir(User $user): array` que, por ahora, devuelva `['estado' => 'vacio', 'raiz' => null]`; documentar en la clase la estructura de nodo de `data-model.md` (`tipo`, `id`, `titulo`, `rol`, `pastores`, `puedeVerFicha`, `hijos`, `totalHijos`).
- [X] T006 Crear `laravel_app/app/Http/Controllers/OrganigramaController.php` con `index()`: `ensureLoggedIn()`, `autorizar('verOrganigrama')`, llamar al servicio y devolver `view('organigrama.index', ...)` con la cabecera `Cache-Control: no-store`; seguir el estilo de `SinLiderController`.
- [X] T007 Agregar la opción «Organigrama» (icono `fa-sitemap`, ruta `organigrama.index`, `activa_en` `['organigrama.*']`, `permiso` `['verOrganigrama', Persona::class]`) a la sección «Congregación» de `laravel_app/config/menu.php`.
- [X] T008 Crear `laravel_app/resources/views/organigrama/index.blade.php` mínima (layout de administración, título «Organigrama», contenedor `<section class="organigrama" data-organigrama>`), siguiendo el marco de página de las otras pantallas.
- [X] T009 [P] Registrar `organigrama` en la lista de pantallas con sesión de `laravel_app/tests/movil/pantallas.mjs`.
- [X] T010 [P] Registrar la ruta `organigrama.index` y su acceso por rol (admin, pastor, líder: sí; usuario: 403) en `laravel_app/tests/Feature/Aislamiento/MatrizRutasTest.php`.

**Punto de control**: `/organigrama` responde 200 para admin/pastor/líder, 403 para `user`, aparece en el menú y `MatrizRutasTest` pasa.

---

## Fase 3: Historia de Usuario 1 - Ver el organigrama de una sede (Prioridad: P1) 🎯 MVP

**Meta**: Mostrar la jerarquía sede → pastores → líderes → miembros con los niveles inferiores colapsados.

**Prueba Independiente**: Con una sede con pastor, dos líderes y miembros, abrir `/organigrama` y comprobar la cabeza con pastores y cada nivel según los datos reales.

### Pruebas de HU1 (escribir primero; deben fallar)

- [X] T011 [P] [HU1] Crear `laravel_app/tests/Feature/Organigrama/OrganigramaEstructuraTest.php` con casos sobre `OrganigramaService`: pastores y pastora de la sede en la cabecera; líderes (`es_lider`) ordenados por apellidos y nombres; miembros de un líder por `lider_id` y por células que lidera o co-lidera sin duplicados; el líder no aparece como su propio miembro; `totalHijos` correcto.
- [X] T012 [P] [HU1] En el mismo archivo `OrganigramaEstructuraTest.php` agregar casos límite: miembros con `estado = 'inactivo'` y personas eliminadas lógicamente no aparecen (RF-013); sede sin pastores marca el aviso «Sin pastor asignado»; líder sin miembros tiene `totalHijos = 0`; sede vacía devuelve estado `vacio`; personas con `Persona::sinLider()` se agrupan en un nodo `sin_lider`.
- [X] T013 [P] [HU1] Crear `laravel_app/tests/Feature/Organigrama/OrganigramaPantallaTest.php` con casos de la vista: el HTML contiene `data-organigrama`, ningún `<details>` trae `open`, cada nodo con hijos muestra su contador de hijos como texto, el estado vacío muestra un mensaje, y la opción «Organigrama» está en el menú.

### Implementación de HU1

- [X] T014 [HU1] Implementar en `laravel_app/app/Services/OrganigramaService.php` la consulta de pastores (`sedes.pastor_persona_id` y `pastora_persona_id` de la sede de `SedeContext`) y de líderes (`es_lider = true`, estado distinto de `inactivo`), seleccionando solo `id`, `nombres`, `apellidos`, `lider_id`, `estado`; sin `withoutGlobalScopes()`.
- [X] T015 [HU1] Implementar en `OrganigramaService.php` la carga de miembros por líder en consultas agrupadas (sin N+1): unión de `personas.lider_id = líder` y de `celula_persona` de las células donde el líder es `lider_id` o `co_lider_id`, sin duplicados, sin inactivos y sin el propio líder.
- [X] T016 [HU1] Implementar en `OrganigramaService.php` el nodo «Sin líder asignado» con `Persona::sinLider()` y el armado final del árbol (`tipo`, `titulo`, `rol` «Sede»/«Líder»/«Miembro», `hijos`, `totalHijos`, aviso de sede sin pastores y estado `arbol`/`vacio`).
- [X] T017 [HU1] Crear el parcial recursivo `laravel_app/resources/views/organigrama/_nodo.blade.php` según el contrato: `<li class="organigrama-nodo" data-tipo=…>`, con hijos un `<details>` sin `open` y `<summary>` (nombre, rol y contador de hijos como texto, p. ej. «5 miembros»), sin hijos un `<div class="organigrama-tarjeta">`; nombre largo truncado por CSS con el completo en `title`.
- [X] T018 [HU1] Completar `laravel_app/resources/views/organigrama/index.blade.php`: cabecera de sede con la lista de pastores o el aviso «Sin pastor asignado», árbol con `@include('organigrama._nodo')` y estado vacío con mensaje claro (RF-015); todos los textos en español con «miembros».
- [X] T019 [HU1] Ejecutar `php artisan test --filter=Organigrama` y dejar T011–T013 en verde.

**Punto de control**: el organigrama de una sede se ve completo y correcto, con niveles inferiores cerrados, usando solo HTML nativo.

---

## Fase 4: Historia de Usuario 2 - Expandir y colapsar para explorar (Prioridad: P1)

**Meta**: Expandir/colapsar nodos con toque o clic y botones «Expandir todo» / «Colapsar todo».

**Prueba Independiente**: Abrir y cerrar un pastor y un líder, y usar los dos botones; el contenido visible cambia sin recargar.

### Pruebas de HU2 (escribir primero; deben fallar)

- [X] T020 [P] [HU2] Crear `laravel_app/resources/js/organigrama/organigrama.test.js` (Vitest + jsdom): `expandir` pone `open` en todos los `<details>` del contenedor `[data-organigrama]`; `colapsar` los quita; el contenedor de acciones deja de estar `hidden` al iniciar; sin contenedor `[data-organigrama]` no hace nada ni lanza errores.
- [X] T021 [P] [HU2] Agregar a `OrganigramaPantallaTest.php` la comprobación de que el HTML incluye los botones con `data-organigrama-accion="expandir"` y `"colapsar"` dentro de un contenedor con el atributo `hidden`.

### Implementación de HU2

- [X] T022 [HU2] Crear `laravel_app/resources/js/organigrama/organigrama.js` exportando `iniciarOrganigrama()`: muestra el contenedor de acciones, y con delegación de eventos aplica expandir/colapsar sobre todos los `<details>` del árbol.
- [X] T023 [HU2] Cargar el módulo solo en esta pantalla en `laravel_app/resources/js/app.js` con `if (document.querySelector('[data-organigrama]')) import('./organigrama/organigrama.js').then(({ iniciarOrganigrama }) => iniciarOrganigrama());` (mismo patrón que las grillas) y revisar que `laravel_app/tests/Feature/Interfaz/OrdenDeCargaJsTest.php` siga en verde.
- [X] T024 [HU2] Agregar al `index.blade.php` la barra `.organigrama-acciones` (con `hidden`) con los botones «Expandir todo» y «Colapsar todo» como `<button type="button">` con los atributos del contrato.
- [X] T025 [HU2] Ejecutar `npm test` y `php artisan test --filter=Organigrama`; dejar T020–T021 en verde.

**Punto de control**: se puede recorrer el árbol nivel por nivel y todo a la vez; sin JS el árbol sigue abriéndose con `<details>` y los botones no aparecen.

---

## Fase 5: Historia de Usuario 3 - Usarlo cómodamente desde el celular (Prioridad: P1)

**Meta**: Presentación vertical con sangría en pantallas estrechas y horizontal tipo organigrama desde 992 px, sin desplazamiento horizontal de la página y con controles de 44 px.

**Prueba Independiente**: Abrir a 320, 768 y 1920 px; sin barra horizontal de la página, controles ≥ 44 × 44 px, expandir/colapsar funciona.

### Pruebas de HU3 (escribir primero; deben fallar)

- [ ] T026 [HU3] Revisar `laravel_app/tests/movil/reglas.mjs` y `laravel_app/tests/movil/auditoria.spec.js` para confirmar que `organigrama` (T009) entra en la auditoría de 320–430 px y en la de peso; ejecutar `npm run test:movil` y registrar los fallos iniciales de la pantalla.
- [ ] T027 [P] [HU3] Crear `laravel_app/tests/movil/organigrama.spec.js` (Playwright, mismo estilo que `tablero.spec.js`): a 320, 768 y 1920 px no hay desplazamiento horizontal de la página; los `<summary>` y los botones miden ≥ 44 × 44 px; al tocar un líder aparecen sus miembros; a 1920 px el árbol se dispone en horizontal.

### Implementación de HU3

- [X] T028 [HU3] Crear `laravel_app/resources/css/organigrama.css` con el diseño base móvil: árbol vertical con sangría y línea guía, tarjetas de nodo de al menos 44 px de alto, letra ≥ 16 px, indicador de expandido/colapsado (icono que gira, no solo color), truncado de nombres con `text-overflow: ellipsis` y foco visible.
- [X] T029 [HU3] Agregar en `organigrama.css` la presentación horizontal con `@media (min-width: 992px)`: hijos en fila bajo el padre con conectores por pseudo-elementos, contenedor `.organigrama` con `overflow-x: auto` propio (el desplazamiento nunca recae en la página) y los miembros siempre en lista vertical dentro de su líder.
- [X] T030 [HU3] Importar `../css/organigrama.css` en `laravel_app/resources/css/app.css` (o en la entrada que use el layout, siguiendo cómo se cargan los demás estilos) y confirmar que `npm run build` compila.
- [X] T031 [HU3] Verificar que el service worker (`laravel_app/public/` y `laravel_app/resources/js/pwa/`) no guarda `/organigrama` ni sus respuestas, y que la respuesta lleva `Cache-Control: no-store` (RF-017); agregar la comprobación a `laravel_app/tests/Feature/Organigrama/OrganigramaPantallaTest.php`.
- [ ] T032 [HU3] Ejecutar `npm run build` y `npm run test:movil`; corregir hasta que pasen sin agregar nada a `laravel_app/tests/movil/excepciones.json` (debe seguir vacío).

**Punto de control**: la pantalla es utilizable de 320 a 1920 px, cumple las reglas móviles y no guarda datos en el teléfono.

---

## Fase 6: Historia de Usuario 4 - Elegir la sede y respetar el alcance por rol (Prioridad: P2)

**Meta**: El SuperAdmin elige sede con el selector existente; Admin/Pastor ven su sede; el Líder solo su rama; el rol `user` queda fuera; nada se filtra entre sedes.

**Prueba Independiente**: Con usuarios de distintas sedes y roles, cada uno ve solo lo suyo; sin parámetro para pedir otra sede.

### Pruebas de HU4 (escribir primero; deben fallar)

- [X] T033 [P] [HU4] Crear `laravel_app/tests/Feature/Organigrama/OrganigramaAlcanceTest.php` con aislamiento entre sedes: un pastor de la sede A nunca ve personas de la sede B en el HTML ni en el resultado del servicio, aun enviando `?sede=` o `?sede_id=` en la URL (se ignoran).
- [X] T034 [P] [HU4] En el mismo archivo agregar alcance por rol: admin con sede activa ve la sede completa; admin en «Todas las sedes» recibe el estado `elegir_sede` sin datos de personas; pastor ve su sede completa con «Sin líder asignado»; líder ve solo su rama (él y sus miembros, sin pastores, otros líderes ni «Sin líder asignado»); rol `user` recibe 403; usuario sin sede no ve nada.

### Implementación de HU4

- [X] T035 [HU4] Extender `OrganigramaService::construir()` en `laravel_app/app/Services/OrganigramaService.php`: devolver estado `elegir_sede` cuando `SedeContext::estaEnTodas()`; para el rol Líder devolver como raíz solo su nodo de líder con sus miembros, filtrando con `AlcanceService::personasVisibles`; para admin/pastor incluir «Sin líder asignado»; un usuario sin sede obtiene `vacio` (falla cerrado).
- [X] T036 [HU4] Mostrar en `laravel_app/resources/views/organigrama/index.blade.php` el estado `elegir_sede` con un mensaje que indique elegir una sede en el selector de la barra superior, sin ningún dato de personas.
- [X] T037 [HU4] Ejecutar `php artisan test --filter=Organigrama` y `php artisan test --filter=Aislamiento`; dejar T033–T034 en verde.

**Punto de control**: el alcance por rol y por sede queda comprobado por pruebas automáticas (principio II).

---

## Fase 7: Historia de Usuario 5 - Ir de un nodo a su ficha (Prioridad: P3)

**Meta**: Abrir la ficha de un pastor, líder o miembro desde el organigrama cuando el usuario tiene permiso.

**Prueba Independiente**: Tocar un nombre abre la ficha correcta; sin permiso no hay enlace.

### Pruebas de HU5 (escribir primero; deben fallar)

- [X] T038 [P] [HU5] Agregar a `laravel_app/tests/Feature/Organigrama/OrganigramaPantallaTest.php`: un nombre con permiso contiene un enlace a `personas.show` de esa persona; sin permiso se muestra texto plano sin enlace; el enlace dentro de `<summary>` no impide que el resto del `<summary>` expanda el nodo.

### Implementación de HU5

- [X] T039 [HU5] Calcular `puedeVerFicha` en `OrganigramaService.php` con `AlcanceService::puedeVerPersona` (usando una sola consulta de ids visibles, sin N+1) para pastores, líderes y miembros.
- [X] T040 [HU5] En `_nodo.blade.php` y la cabecera de `index.blade.php` renderizar el nombre como `<a href="{{ route('personas.show', $id) }}">` solo si `puedeVerFicha`; asegurar un área táctil de al menos 44 × 44 px y que el enlace tenga foco visible, ajustando `organigrama.css`.
- [X] T041 [HU5] Ejecutar `php artisan test --filter=Organigrama` y dejar T038 en verde.

**Punto de control**: las cinco historias funcionan de forma independiente.

---

## Fase 8: Pulido y asuntos transversales

**Propósito**: Cierre según el flujo de desarrollo de la constitución.

- [ ] T042 [P] Documentar la pantalla en `laravel_app/README.md` (sección breve «Organigrama»: qué muestra, alcance por rol, cómo elegir sede).
- [X] T043 [P] Prueba de rendimiento en `laravel_app/tests/Feature/Organigrama/OrganigramaEstructuraTest.php`: con una sede sembrada de 500 miembros, el servicio ejecuta un número acotado de consultas (sin N+1) y la respuesta se genera en tiempo razonable (CE-004).
- [ ] T044 Ejecutar la suite completa: `php artisan test`, `npm test`, `npm run build` y `npm run test:movil`, todos en verde y con `tests/movil/excepciones.json` vacío.
- [ ] T045 Recorrer manualmente `specs/011-organigrama-por-sede/quickstart.md` en el navegador (escritorio y 320 px, con y sin JavaScript) y corregir lo que falle.

---

## Dependencias y orden de ejecución

### Dependencias entre fases

- **Preparación (Fase 1)**: sin dependencias.
- **Fundamentos (Fase 2)**: depende de la Fase 1; bloquea todas las historias.
- **HU1 (Fase 3)**: depende de la Fase 2. Es el MVP.
- **HU2 (Fase 4)**: depende de HU1 (necesita el árbol renderizado).
- **HU3 (Fase 5)**: depende de HU1; puede avanzar en paralelo con HU2 (T028–T029 tocan solo CSS), pero T032 espera a ambas.
- **HU4 (Fase 6)**: depende de HU1 (extiende el servicio); independiente de HU2 y HU3.
- **HU5 (Fase 7)**: depende de HU1; ajusta `_nodo.blade.php` y el CSS de HU3 (hacerla después de HU3).
- **Pulido (Fase 8)**: depende de las historias que se incluyan.

### Dentro de cada historia

Las pruebas se escriben primero y deben fallar; luego servicio, vista y estilos; al final se ejecuta la verificación de la historia.

### Oportunidades de paralelismo

- Fase 2: T009 y T010 en paralelo entre sí y con T003–T008.
- HU1: T011, T012 y T013 en paralelo (T011 y T012 son el mismo archivo: hacerlas seguidas por una misma persona o agente).
- HU2 con HU3: T020/T021 (JS y marcado) en paralelo con T027–T029 (Playwright y CSS).
- HU4 puede desarrollarse en paralelo con HU2/HU3 por otra persona (archivos distintos salvo `index.blade.php`).

### Ejemplo de paralelismo (HU1)

```text
Tarea: "Pruebas de estructura del servicio" (T011/T012)
Tarea: "Pruebas de la pantalla" (T013)
```

---

## Estrategia de implementación

### MVP primero (solo HU1)

1. Completar Fase 1 y Fase 2.
2. Completar HU1 (Fase 3).
3. **Parar y validar**: ver el organigrama de una sede con pastores, líderes y miembros.

### Entrega incremental

1. Fundamentos + HU1 → organigrama estático correcto (MVP).
2. + HU2 → interactivo con «Expandir/Colapsar todo».
3. + HU3 → responsive completo y auditoría móvil en verde.
4. + HU4 → alcance por rol y selección de sede comprobados.
5. + HU5 → enlaces a las fichas.
6. Pulido y verificación final.

> Nota: aunque HU4 es P2, la restricción mínima de acceso (403 para `user`, filtro por sede) ya queda activa desde la Fase 2 y HU1; HU4 completa el rol Líder y el estado «elegir sede». Antes de publicar a producción deben estar al menos HU1–HU4.

---

## Notas

- `[P]` = archivos distintos, sin dependencias pendientes.
- La etiqueta [HU] relaciona cada tarea con su historia para dar trazabilidad.
- Nunca se ejecuta `migrate:fresh` sobre la base local; las pruebas usan bases temporales.
- Los commits solo se hacen cuando el responsable del proyecto lo pida.
