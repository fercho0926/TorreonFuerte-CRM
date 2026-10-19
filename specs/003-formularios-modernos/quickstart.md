# Guía de validación (quickstart): Formularios modernos

Escenarios para comprobar que la funcionalidad cumple la [spec](spec.md). Los componentes y atributos están en [contracts/componentes-formulario.md](contracts/componentes-formulario.md); los conceptos de interfaz en [data-model.md](data-model.md).

## Requisitos previos

- PHP ^8.3, Composer y Node 22 / npm 10.
- Funcionalidades 001 y 002 implementadas (personas, líderes, sedes).
- **Base de datos de prueba**: no uses `migrate:fresh` contra la base del `.env`. Para el recorrido manual usa una base temporal: `DB_DATABASE=<ruta>\prueba.sqlite php artisan migrate:fresh --seed` (crea 3 sedes con datos).

## Preparación

```powershell
cd laravel_app
composer install
npm install            # instala select2, flatpickr, sweetalert2, inputmask y vitest
npm run build          # compila public/build (en el servidor cPanel NO hay Node: subir public/build)
php artisan serve
```

Para trabajar con recarga en caliente: `npm run dev` en otra terminal.

## Pruebas automáticas

```powershell
php artisan test       # PHPUnit: negocio y permisos (157 previas) + estructura y accesibilidad de formularios
npm test               # Vitest: funciones puras de los módulos JS
```

Resultado esperado: todo en verde; la prueba de inventario confirma que cada formulario usa los componentes y que ninguno conserva `onclick="return confirm"`.

## Escenarios manuales (con la base temporal, sesión como pastor y luego como admin)

| # | Escenario | Pasos | Resultado esperado |
|---|-----------|-------|--------------------|
| 1 | Secciones y obligatorios (HU1, FR-001/002) | Personas → Crear | Tarjetas con título, ícono y descripción ("Datos personales", "Contacto", "Ubicación", "Liderazgo"); campos obligatorios con marca y leyenda; etiqueta encima, ícono a la izquierda |
| 2 | Botones siempre visibles (FR-003) | En Personas → Crear, desplazarse por todo el formulario | La barra con Guardar y Cancelar sigue visible; no tapa campos ni la marca de agua |
| 3 | Validación al salir (HU2) | Escribir "abc" en correo y salir del campo; dejar celular a medias | Mensajes debajo del campo y campo marcado en rojo; al corregir pasan a verde |
| 4 | Resumen y primer error | Dejar nombres vacío y pulsar Guardar | No envía; enfoca el primer campo con error y muestra un resumen con enlaces |
| 5 | Error del servidor conserva datos | Registrar un documento ya existente | Vuelve con todo lo escrito, campo marcado y foco en él |
| 6 | Selector con búsqueda (HU3) | Personas → Crear → Líder directo: escribir "ana" (con y sin tilde) | Lista filtrada al instante, con documento/celular junto al nombre |
| 7 | Selección múltiple | Eventos → Crear → elegir 10 personas escribiendo | Quedan como etiquetas removibles con contador; "Limpiar" las quita; se guardan correctamente |
| 8 | Campos dependientes (HU4) | Marcar "Esta persona es líder" y cambiar el estado a "miembro activo" | Aparecen Red y Zona con transición; el documento pasa a obligatorio de inmediato; al desmarcar se ocultan |
| 9 | Máscaras y calendario | Escribir un celular; elegir fecha de nacimiento; probar "Hoy" en una fecha; escribir un monto | Celular formateado y teclado numérico en teléfono; calendario con atajos y sin fechas futuras en nacimiento; monto con separador de miles; al guardar se envían los mismos valores de siempre |
| 10 | Envío único (HU5) | Llenar un formulario válido y hacer doble clic rápido en Guardar | Botón con progreso y deshabilitado; se crea un solo registro |
| 11 | Cambios sin guardar | Escribir algo e intentar cerrar o navegar; luego sin escribir nada | Pregunta solo cuando hay cambios; sin borradores guardados en el navegador |
| 12 | Confirmación propia (FR-011) | Quitar rol de líder; inactivar una persona; eliminar un abono | Diálogo propio con el detalle de las consecuencias; Cancelar no hace nada; ya no aparece el cuadro del navegador |
| 13 | Aviso de éxito | Guardar una persona | Mensaje discreto que desaparece solo en ~5 s |
| 14 | Asistencia en teléfono (clarificación Q3) | Célula → registrar reunión, en pantalla de 360 px | Lista de tarjetas con interruptor grande por persona; en computador, tabla |
| 15 | Móvil (FR-004, SC-007) | Abrir Persona, Evento y Sede en 360 px | Una columna, sin desplazamiento horizontal, controles cómodos al tacto |
| 16 | Solo teclado (SC-008) | Llenar y enviar Persona con Tab, Enter, flechas, Esc | Todo alcanzable y operable; foco visible; errores anunciados |
| 17 | Sin JavaScript (FR-020) | Desactivar JS en el navegador y registrar una persona | Controles nativos, envío correcto y errores del servidor visibles |
| 18 | Mismo estilo en todos (HU6, FR-017) | Recorrer persona, designar líder, célula, reunión, encuentro, evento, abono, ministerio, usuario, sede, pastores, traslado, redes, zonas, filtros y login | Mismas tarjetas, campos, ayudas, errores y botones |
| 19 | Marca de agua y solo consulta (FR-022) | Como admin en "Todas las sedes": abrir un formulario | La marca de agua no estorba; el botón principal está deshabilitado con aviso claro de solo consulta |
| 20 | Rendimiento de selectores (SC-004) | Sede con 1.000 personas: abrir Evento → Crear y filtrar | El filtro responde al instante; elegir 10 personas en menos de 60 s |
| 21 | Compilación (D13) | Revisar que `public/build` esté actualizado tras `npm run build` | La página carga el CSS/JS nuevo; sin él, los formularios seguirían funcionando con el estilo anterior |

## Criterio de finalización

Todos los escenarios pasan, `php artisan test` y `npm test` quedan en verde, y la prueba de inventario no encuentra formularios fuera de los componentes.
