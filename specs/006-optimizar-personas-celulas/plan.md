# Plan de Implementación: Optimización de Personas, Líderes, Células y Ministerios

**Rama**: `006-optimizar-personas-celulas` | **Fecha**: 2026-10-19 | **Especificación**: [spec.md](spec.md)

**Entrada**: Especificación de la funcionalidad en `specs/006-optimizar-personas-celulas/spec.md`

## Resumen

Siete mejoras que comparten una pieza común: un **buscador de personas con autocompletado** (búsqueda en el servidor, con paginación) usado en designar líder, en los integrantes de células y en los de ministerios.

1. **Personas**: una sola tarjeta «Datos personales» (celular, correo, estado, tipo y número de documento).
2. **Red** se elimina por completo (módulo, campo, filtros, tablero, datos), con el mismo criterio que se usó para Zona.
3. **Designar líder**: un campo con autocompletado de personas **no inactivas** que aún no son líderes, sin botón «Buscar».
4. **Ficha del líder**: grilla compacta «Personas a su cargo» con célula de cada una.
5. **Células**: registrar integrantes con multiselección autocompletada y **crear personas nuevas desde la célula**; la pertenencia deja de depender del líder directo (decisión B).
6. **Estado «En célula»** para quienes aún no son parte de la iglesia.
7. **Ministerios** pasa a «Congregación» y usa la misma selección de integrantes.

Enfoque técnico (detalle en [research.md](research.md)):

- Un único endpoint de búsqueda `GET /personas/buscar` (JSON paginado, respeta alcance por sede y rol) y un endpoint de alta rápida `POST /personas/rapida`; ambos reutilizan `AlcanceService`, `PersonaRequest` y las reglas de duplicados existentes.
- `x-form.select` y `x-form.multiselect` ganan un modo «remoto» (atributos `data-fuente`, `data-crear`) sobre Select2, que ya está instalado; sin JavaScript siguen funcionando con las opciones ya cargadas.
- Migración aditiva y reversible que elimina `redes` y `personas.red_id`; estado nuevo `celula` sin cambios de esquema (el estado ya es texto).
- Se elimina la regla de coincidencia de líder en `CelulasController::agregarMiembro` y los integrantes se sincronizan desde el formulario de la célula.
- Sin paquetes nuevos y sin cambios en permisos ni aislamiento por sede.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.3, Laravel 13; Blade + AdminLTE 4 / Bootstrap 5; módulos JS con Vite

**Dependencias principales**: las existentes (Select2 4, SweetAlert2, Vitest); sin paquetes nuevos

**Almacenamiento**: SQLite en desarrollo / base actual en producción; 1 migración (retira `redes` y `personas.red_id`)

**Pruebas**: PHPUnit 12 (funcionalidad y aislamiento), Vitest 5 + jsdom (módulo de búsqueda remota y alta rápida)

**Plataforma de destino**: aplicación web Laravel en hosting compartido (cPanel)

**Tipo de proyecto**: aplicación web monolítica

**Objetivos de rendimiento**: sugerencias en < 1 s con 500 personas (SC-003, SC-006); la búsqueda pagina de 20 en 20 y usa índices existentes (`apellidos`, `nombres`, `celular`)

**Restricciones**: aislamiento por sede intacto (FR-013); no se pierde ninguna persona, líder, célula ni ministerio (FR-014); textos en español; sin servicios externos

**Escala/Alcance**: decenas de sedes, cientos de personas por sede; ~12 vistas, 5 controladores y 1 servicio tocados

## Verificación de la Constitución

`.specify/memory/constitution.md` sigue siendo la plantilla sin completar; se aplican los acuerdos vigentes del proyecto:

| Criterio | Estado |
|----------|--------|
| Artefactos de spec-kit en español | Cumple |
| Pruebas existentes siguen en verde (PHPUnit 325, Vitest 53), salvo las que dependían de redes o de las tarjetas eliminadas, que se ajustan | Se verifica por fase |
| Aislamiento por sede y roles sin cambios | Cumple: la búsqueda usa `AlcanceService` |
| Migraciones aditivas y reversibles; nunca `migrate:fresh` sobre la base local | Cumple (solo retira el dato de red) |
| No perder datos (FR-014) | Cumple: el conteo de personas, líderes, células y ministerios no cambia |

Reevaluación tras el diseño: sin violaciones.

## Estructura del Proyecto

### Documentación (esta funcionalidad)

```text
specs/006-optimizar-personas-celulas/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── rutas-y-componentes.md
├── checklists/requirements.md
└── tasks.md              # lo genera /speckit-tasks
```

### Código fuente (raíz del repositorio)

```text
laravel_app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PersonasController.php        # + buscar(), + rapida(); sin red
│   │   │   ├── LideresController.php         # designar con autocompletado; ficha con grilla; sin red
│   │   │   ├── CelulasController.php         # integrantes (sync) y alta rápida; sin regla de líder directo
│   │   │   ├── MinisteriosController.php     # selección con búsqueda remota
│   │   │   ├── SinLiderController.php        # excluye «En célula»
│   │   │   └── RedesController.php           # ELIMINADO
│   │   └── Requests/PersonaRequest.php       # estado «celula»; sin red
│   ├── Models/
│   │   ├── Persona.php                       # ESTADOS += celula; sin red_id/redLider; scope sinLider
│   │   └── Red.php                           # ELIMINADO
│   ├── Services/
│   │   ├── BuscadorPersonas.php              # NUEVO: consulta paginada, filtros y formato de resultado
│   │   ├── AlcanceService.php                # sin redesVisibles
│   │   ├── LiderazgoService.php              # designarLider sin red
│   │   └── SedeService.php                   # sin Red en tieneDatos/eliminar/resumen
│   └── Support/{Estados,MigracionSedes,MigracionRedesZonas}.php
├── config/menu.php                           # sin Redes; Ministerios en Congregación
├── database/
│   ├── migrations/2026_10_25_000001_drop_redes.php   # NUEVO
│   ├── factories/PersonaFactory.php          # lider() sin red
│   └── seeders/DatabaseSeeder.php            # sin redes; personas «En célula» de ejemplo
├── resources/
│   ├── js/forms/
│   │   ├── buscador-remoto.js                # NUEVO: Select2 con fuente remota y paginación (+ prueba)
│   │   ├── alta-rapida.js                    # NUEVO: diálogo «Crear persona nueva» (+ prueba)
│   │   └── selectores.js                     # usa el modo remoto cuando hay data-fuente
│   └── views/
│       ├── components/form/{select,multiselect}.blade.php   # props fuente y crear
│       ├── personas/{_form,show,index}.blade.php            # una tarjeta; sin red
│       ├── lideres/{designar,show,index}.blade.php          # autocompletado; grilla; sin red
│       ├── celulas/{_form,show}.blade.php                   # integrantes con multiselect y alta rápida
│       ├── ministerios/_form.blade.php                      # búsqueda remota
│       ├── dashboard.blade.php                              # sin «Personas por red»
│       └── redes/                                           # ELIMINADO
├── routes/web.php
└── tests/Feature/{Personas,Celulas,Lideres,Ministerios}/    # NUEVOS y ajustes
```

**Decisión de estructura**: monolito Laravel existente; se añaden un servicio de búsqueda, dos rutas JSON y dos módulos JS pequeños; el resto son ajustes y eliminaciones.

## Seguimiento de Complejidad

Sin violaciones que justificar.
