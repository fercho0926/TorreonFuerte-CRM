# Torreón Fuerte CRM — Roadmap de Desarrollo

> **Última revisión:** Abril 2026  
> **Stack:** Angular 20 (standalone, signals, zoneless) · .NET 8 · SQL Server · Angular Material v20

---

## Estado actual del sistema

| Módulo | Ruta | Estado | Datos |
|--------|------|--------|-------|
| Dashboard | `/dashboard` | ✅ Completo | Mock agregado |
| Miembros (lista) | `/miembros` | ⚠️ UI lista, falla sin API | HTTP real |
| Miembros (detalle) | `/miembros/:id` | ⚠️ UI lista, falla sin API | HTTP real |
| Miembros (form) | `/miembros/nuevo` | ⚠️ UI lista, falla sin API | HTTP real |
| Liderazgo | `/lideres` | ✅ Completo | Mock |
| Preparación | `/etapas` | ✅ Completo | Mock |
| Eventos | `/eventos` | ✅ Completo + tab Ministerios | Mock |
| Sedes | `/sedes` | ✅ Mapa + cards (sin detalle click) | Mock |
| Ministerios | — | 🔴 Sin ruta ni página propia | Mock (service existe) |
| Reportes | — | 🔴 No existe | — |
| Seguimiento pastoral | — | 🔴 No existe | — |
| Asistencia dominical | — | 🔴 No existe | — |
| Comunicaciones | — | 🔴 No existe | — |
| Finanzas / Ofrendas | — | 🔴 No existe | — |
| Configuración | — | 🔴 No existe | — |

---

## Prioridad 1 — Crítico (bloquea uso real)

### 1.1 Miembros: conectar mock mientras no hay backend
**Problema:** `MiembrosService.getAll()`, `getById()` y `create()` hacen HTTP real → error 500 sin API.  
**Solución:** Usar `getMock()` igual que el resto de módulos. Las llamadas HTTP ya están comentadas encima, listas para descomentar.

**Archivos:**
- `src/app/features/miembros/miembros-list/miembros-list.component.ts` — cambiar `getAll()` → `getMock()`
- `src/app/features/miembros/miembro-detalle/miembro-detalle.component.ts` — mock por id
- `src/app/features/miembros/miembro-form/miembro-form.component.ts` — mock create/update

---

## Prioridad 2 — Alta (funcionalidad CRM core)

### 2.1 Ministerios: página CRUD completa ✅ EN PROGRESO
**Incluye:**
- Grid de cards por ministerio con color por tipo
- CRUD (crear, editar, eliminar ministerio)
- Gestión de integrantes: agregar desde lista de miembros, toggle activo/inactivo
- Ruta `/ministerios` + ítem en navegación
- Stats: total integrantes, ministerios activos

**Archivos:**
- `src/app/features/ministerios/ministerios.component.ts/html/css`
- `src/app/features/ministerios/ministerio-form-dialog/ministerio-form-dialog.component.ts`
- `src/app/app.routes.ts` — nueva ruta
- `src/app/shared/components/layout/layout.component.ts` — nav ítem

### 2.2 Sedes: detalle al hacer clic en card ✅ EN PROGRESO
**Incluye:**
- Dialog con info completa de la sede
- Tab "Miembros" — lista filtrada por sede
- Tab "Células" — células de esa sede con líderes
- Tab "Info" — dirección, pastor, fecha fundación, estadísticas

**Archivos:**
- `src/app/features/sedes/sede-detalle-dialog/sede-detalle-dialog.component.ts`
- `src/app/features/sedes/sedes.component.ts` — abrir dialog al hacer clic
- `src/app/core/auth/miembros.service.ts` — `getMockBySede(sedeId)`

---

## Prioridad 3 — Media (diferenciador pastoral)

### 3.1 Seguimiento pastoral
Módulo para registrar visitas, llamadas y notas por miembro. El corazón del pastoreo personalizado.

**Entidades:**  
`VisitaPastoral { visitaId, miembroId, responsableId, tipo, fecha, nota, resultado }`  
`TipoVisita = 'Domicilio' | 'Telefónica' | 'Presencial' | 'Digital'`

**UI propuesta:**
- Tab "Seguimiento" en ficha de miembro
- Lista de interacciones con timeline vertical
- Botón "Registrar visita" → dialog rápido
- Dashboard: miembros sin contacto en los últimos 30/60/90 días

**Ruta:** `/seguimiento`

### 3.2 Asistencia dominical
Los cultos semanales son distintos a "Eventos" — necesitan registro masivo rápido (check-in).

**Flujo:**
1. Pastor abre "Culto del domingo"
2. Aparece lista de miembros activos con checkbox
3. Al guardar se registra asistencia en batch
4. Dashboard muestra tendencia de asistencia por mes

**Entidades:**  
`CultoSemanal { cultoId, sedeId, fecha, totalAsistentes }`  
`AsistenciaCulto { cultoId, miembroId, asistio }`

**Ruta:** `/cultos`

---

## Prioridad 4 — Media (herramientas de gestión)

### 4.1 Reportes y exportación
Sin CSV/Excel la coordinación administrativa es imposible.

**Reportes propuestos:**
| Reporte | Descripción |
|---------|-------------|
| Lista de miembros | Filtrable por estado, sede, etapa, líder |
| Asistencia por evento | Inscritos vs asistentes |
| Avance en preparación | Cuántos en cada etapa por sede |
| Líderes y células | Árbol jerárquico exportable |
| Crecimiento mensual | Nuevos ingresos y bajas por mes |

**Tech:** `angular-csv` o exportación nativa con `Blob` + `URL.createObjectURL`.

**Ruta:** `/reportes`

### 4.2 Búsqueda global
Barra de búsqueda en el header que permite encontrar miembros, eventos y ministerios desde cualquier pantalla.

**UI:** `Ctrl+K` abre un panel de búsqueda rápida con resultados agrupados por tipo.

---

## Prioridad 5 — Baja (crecimiento futuro)

### 5.1 Comunicaciones
Envío de recordatorios y anuncios a grupos específicos.

**Canales:** WhatsApp (API Business), Email (SendGrid), Notificación push (FCM)  
**Segmentación:** Por sede, etapa, ministerio, célula, estado

### 5.2 Finanzas / Ofrendas
Registro de diezmos y ofrendas por sede.

**Entidades:**  
`Ofrenda { ofrendaId, sedeId, miembroId, tipo, monto, fecha, nota }`  
`TipoOfrenda = 'Diezmo' | 'Ofrenda' | 'Mision' | 'Construccion' | 'Otro'`

### 5.3 Configuración del sistema
- Gestión de usuarios y roles (`Admin`, `Pastor`, `Lider`, `SuperAdmin`)
- Cambio de contraseña y foto de perfil
- Configuración por sede (horarios, colores, logo)

---

## Deuda técnica identificada

| Item | Impacto | Esfuerzo |
|------|---------|----------|
| Miembros sin paginación real | Medio | Bajo |
| Miembro-detalle sin datos de etapas/asistencias mock | Medio | Bajo |
| Sin manejo de errores global (interceptor HTTP) | Alto | Medio |
| Sin refresh token / expiración de sesión | Alto | Medio |
| Sin tests unitarios | Medio | Alto |
| Sin lazy-loading de imágenes en mapa | Bajo | Bajo |

---

## Arquitectura de datos (referencia)

```
Sede (1) ──< Miembro (N)
Miembro (1) ──< MiembroEtapa (N)
Miembro (N) >──< EventoInscrito (N) >──< Evento (1)
Miembro (N) >──< IntegranteMinisterio (N) >──< Ministerio (1)
Lider (1) ──< Lider (N)  [autorreferencial: Pastor > Zona > Célula]
Ministerio (1) ──< IntegranteMinisterio (N)
Evento (1) ──< EventoInscrito (N)
```

---

## Convenciones del proyecto

- **Control de flujo:** `@if`, `@for`, `@switch` — nunca `*ngIf`/`*ngFor`
- **Estado:** `signal()` + `computed()` — no hay Zone.js
- **Mock:** `of(MOCK_DATA).pipe(delay(N))` con llamada HTTP comentada encima
- **Standalone:** todos los componentes tienen `standalone: true`
- **Imports:** solo importar módulos que se usan en el template
- **SSR:** cualquier API de browser (Leaflet, localStorage) dentro de `isPlatformBrowser()`
