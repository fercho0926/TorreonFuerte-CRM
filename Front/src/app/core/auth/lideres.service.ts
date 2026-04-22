import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { delay } from 'rxjs/operators';
import {
  Lider, LiderNodo, HistorialLider,
  CrearLiderRequest, EditarLiderRequest,
} from '../models/lider.models';
import { environment } from '../../../environments/environment';

// ── Mock data ──────────────────────────────────────────────────────────────────
// Swap these out for real HTTP calls once el backend esté listo.
// Cada método ya tiene la firma que usará la API (mismos params/tipos).

const MOCK_ARBOL: LiderNodo = {
  liderId: 1, miembroId: 1,
  nombre: 'Roberto', apellido: 'Martínez',
  nivel: 'Pastor', zonaOCelula: 'Sede Principal',
  telefono: '664-100-0001', totalMiembros: 30,
  fechaAsignacion: '2020-01-15',
  subLideres: [
    {
      liderId: 2, miembroId: 2,
      nombre: 'Carlos', apellido: 'García',
      nivel: 'LiderZona', zonaOCelula: 'Zona Norte',
      telefono: '664-100-0002', totalMiembros: 19,
      fechaAsignacion: '2020-03-10',
      subLideres: [
        {
          liderId: 4, miembroId: 4, nombre: 'Ana', apellido: 'López',
          nivel: 'LiderCelula', zonaOCelula: 'Célula Esperanza',
          telefono: '664-100-0004', totalMiembros: 8, fechaAsignacion: '2020-06-01', subLideres: [],
          miembros: [
            { miembroId: 101, nombre: 'Juan',   apellido: 'Díaz',    estado: 'Activo',    etapaActual: 'Avivando el Fuego' },
            { miembroId: 102, nombre: 'Rosa',   apellido: 'Sánchez', estado: 'Activo',    etapaActual: 'Caminando en Santidad' },
            { miembroId: 103, nombre: 'Pedro',  apellido: 'Vega',    estado: 'Activo',    etapaActual: 'Libres para Servir' },
            { miembroId: 104, nombre: 'Carmen', apellido: 'Ortiz',   estado: 'Activo',    etapaActual: 'Avivando el Fuego' },
            { miembroId: 105, nombre: 'Luis',   apellido: 'Fuentes', estado: 'Activo',    etapaActual: 'Encuentro con Dios' },
            { miembroId: 106, nombre: 'María',  apellido: 'Blanco',  estado: 'Visitante', etapaActual: null },
            { miembroId: 107, nombre: 'Carlos', apellido: 'Nieto',   estado: 'Activo',    etapaActual: 'Caminando en Santidad' },
            { miembroId: 108, nombre: 'Sofía',  apellido: 'Prada',   estado: 'Activo',    etapaActual: 'Libres para Servir' },
          ],
        },
        {
          liderId: 5, miembroId: 5, nombre: 'Miguel', apellido: 'Torres',
          nivel: 'LiderCelula', zonaOCelula: 'Célula Fe',
          telefono: '664-100-0005', totalMiembros: 6, fechaAsignacion: '2021-01-15', subLideres: [],
          miembros: [
            { miembroId: 109, nombre: 'Andrés',  apellido: 'Lima',    estado: 'Activo',    etapaActual: 'Avivando el Fuego' },
            { miembroId: 110, nombre: 'Paola',   apellido: 'Ríos',    estado: 'Activo',    etapaActual: 'Caminando en Santidad' },
            { miembroId: 111, nombre: 'Diego',   apellido: 'Mora',    estado: 'Activo',    etapaActual: 'Libres para Servir' },
            { miembroId: 112, nombre: 'Elena',   apellido: 'Castro',  estado: 'Visitante', etapaActual: null },
            { miembroId: 113, nombre: 'Jorge',   apellido: 'Reyes',   estado: 'Activo',    etapaActual: 'Encuentro con Dios' },
            { miembroId: 114, nombre: 'Natalia', apellido: 'Soto',    estado: 'Activo',    etapaActual: 'Avivando el Fuego' },
          ],
        },
        {
          liderId: 6, miembroId: 6, nombre: 'Diana', apellido: 'Morales',
          nivel: 'LiderCelula', zonaOCelula: 'Célula Paz',
          telefono: '664-100-0006', totalMiembros: 5, fechaAsignacion: '2021-08-20', subLideres: [],
          miembros: [
            { miembroId: 115, nombre: 'Fernando', apellido: 'Leal',    estado: 'Activo',    etapaActual: 'Caminando en Santidad' },
            { miembroId: 116, nombre: 'Isabel',   apellido: 'Mendoza', estado: 'Activo',    etapaActual: 'Avivando el Fuego' },
            { miembroId: 117, nombre: 'Marco',    apellido: 'Vargas',  estado: 'Activo',    etapaActual: 'Libres para Servir' },
            { miembroId: 118, nombre: 'Lucía',    apellido: 'Paredes', estado: 'Visitante', etapaActual: null },
            { miembroId: 119, nombre: 'Tomás',    apellido: 'Guerrero',estado: 'Activo',    etapaActual: 'Encuentro con Dios' },
          ],
        },
      ],
    },
    {
      liderId: 3, miembroId: 3,
      nombre: 'Patricia', apellido: 'Herrera',
      nivel: 'LiderZona', zonaOCelula: 'Zona Sur',
      telefono: '664-100-0003', totalMiembros: 11,
      fechaAsignacion: '2020-03-10',
      subLideres: [
        {
          liderId: 7, miembroId: 7, nombre: 'José', apellido: 'Ramírez',
          nivel: 'LiderCelula', zonaOCelula: 'Célula Gracia',
          telefono: '664-100-0007', totalMiembros: 7, fechaAsignacion: '2020-09-15', subLideres: [],
          miembros: [
            { miembroId: 120, nombre: 'Adriana',     apellido: 'Cruz',    estado: 'Activo',    etapaActual: 'Caminando en Santidad' },
            { miembroId: 121, nombre: 'Roberto',     apellido: 'Ávila',   estado: 'Activo',    etapaActual: 'Avivando el Fuego' },
            { miembroId: 122, nombre: 'Daniela',     apellido: 'Flores',  estado: 'Activo',    etapaActual: 'Libres para Servir' },
            { miembroId: 123, nombre: 'Miguel Ángel',apellido: 'León',    estado: 'Activo',    etapaActual: 'Avivando el Fuego' },
            { miembroId: 124, nombre: 'Fernanda',    apellido: 'Rueda',   estado: 'Visitante', etapaActual: null },
            { miembroId: 125, nombre: 'Alejandro',   apellido: 'Mora',    estado: 'Activo',    etapaActual: 'Encuentro con Dios' },
            { miembroId: 126, nombre: 'Valeria',     apellido: 'Santos',  estado: 'Activo',    etapaActual: 'Caminando en Santidad' },
          ],
        },
        {
          liderId: 8, miembroId: 8, nombre: 'Sandra', apellido: 'Pérez',
          nivel: 'LiderCelula', zonaOCelula: 'Célula Amor',
          telefono: '664-100-0008', totalMiembros: 4, fechaAsignacion: '2022-02-28', subLideres: [],
          miembros: [
            { miembroId: 127, nombre: 'Emilio',   apellido: 'Trujillo', estado: 'Activo',    etapaActual: 'Avivando el Fuego' },
            { miembroId: 128, nombre: 'Camila',   apellido: 'Vera',     estado: 'Activo',    etapaActual: 'Libres para Servir' },
            { miembroId: 129, nombre: 'Sebastián',apellido: 'Gil',      estado: 'Activo',    etapaActual: 'Encuentro con Dios' },
            { miembroId: 130, nombre: 'Mariana',  apellido: 'López',    estado: 'Visitante', etapaActual: null },
          ],
        },
      ],
    },
  ],
};

const MOCK_LIDERES: Lider[] = [
  { liderId: 1, miembroId: 1, nombre: 'Roberto', apellido: 'Martínez', telefono: '664-100-0001', email: 'roberto@iglesia.com', nivel: 'Pastor', liderPadreId: null, liderPadreNombre: null, zonaOCelula: 'Sede Principal', totalMiembros: 30, totalSubLideres: 2, fechaAsignacion: '2020-01-15', activo: true },
  { liderId: 2, miembroId: 2, nombre: 'Carlos', apellido: 'García', telefono: '664-100-0002', email: 'carlos@iglesia.com', nivel: 'LiderZona', liderPadreId: 1, liderPadreNombre: 'Roberto Martínez', zonaOCelula: 'Zona Norte', totalMiembros: 19, totalSubLideres: 3, fechaAsignacion: '2020-03-10', activo: true },
  { liderId: 3, miembroId: 3, nombre: 'Patricia', apellido: 'Herrera', telefono: '664-100-0003', email: 'patricia@iglesia.com', nivel: 'LiderZona', liderPadreId: 1, liderPadreNombre: 'Roberto Martínez', zonaOCelula: 'Zona Sur', totalMiembros: 11, totalSubLideres: 2, fechaAsignacion: '2020-03-10', activo: true },
  { liderId: 4, miembroId: 4, nombre: 'Ana', apellido: 'López', telefono: '664-100-0004', email: null, nivel: 'LiderCelula', liderPadreId: 2, liderPadreNombre: 'Carlos García', zonaOCelula: 'Célula Esperanza', totalMiembros: 8, totalSubLideres: 0, fechaAsignacion: '2020-06-01', activo: true },
  { liderId: 5, miembroId: 5, nombre: 'Miguel', apellido: 'Torres', telefono: '664-100-0005', email: null, nivel: 'LiderCelula', liderPadreId: 2, liderPadreNombre: 'Carlos García', zonaOCelula: 'Célula Fe', totalMiembros: 6, totalSubLideres: 0, fechaAsignacion: '2021-01-15', activo: true },
  { liderId: 6, miembroId: 6, nombre: 'Diana', apellido: 'Morales', telefono: '664-100-0006', email: null, nivel: 'LiderCelula', liderPadreId: 2, liderPadreNombre: 'Carlos García', zonaOCelula: 'Célula Paz', totalMiembros: 5, totalSubLideres: 0, fechaAsignacion: '2021-08-20', activo: true },
  { liderId: 7, miembroId: 7, nombre: 'José', apellido: 'Ramírez', telefono: '664-100-0007', email: null, nivel: 'LiderCelula', liderPadreId: 3, liderPadreNombre: 'Patricia Herrera', zonaOCelula: 'Célula Gracia', totalMiembros: 7, totalSubLideres: 0, fechaAsignacion: '2020-09-15', activo: true },
  { liderId: 8, miembroId: 8, nombre: 'Sandra', apellido: 'Pérez', telefono: '664-100-0008', email: null, nivel: 'LiderCelula', liderPadreId: 3, liderPadreNombre: 'Patricia Herrera', zonaOCelula: 'Célula Amor', totalMiembros: 4, totalSubLideres: 0, fechaAsignacion: '2022-02-28', activo: true },
];

const MOCK_HISTORIAL: HistorialLider[] = [
  { historialId: 1, liderId: 1, liderNombre: 'Roberto Martínez', nivel: 'Pastor', zonaOCelula: 'Sede Principal', fechaInicio: '2020-01-15', fechaFin: null, motivoCambio: 'Fundación de la sede', registradoPor: 'Sistema', activo: true },
  { historialId: 2, liderId: 2, liderNombre: 'Carlos García', nivel: 'LiderZona', zonaOCelula: 'Zona Norte', fechaInicio: '2020-03-10', fechaFin: null, motivoCambio: 'Designación inicial', registradoPor: 'Roberto Martínez', activo: true },
  { historialId: 3, liderId: 3, liderNombre: 'Patricia Herrera', nivel: 'LiderZona', zonaOCelula: 'Zona Sur', fechaInicio: '2020-03-10', fechaFin: null, motivoCambio: 'Designación inicial', registradoPor: 'Roberto Martínez', activo: true },
  { historialId: 4, liderId: 4, liderNombre: 'Ana López', nivel: 'LiderCelula', zonaOCelula: 'Célula Esperanza', fechaInicio: '2020-06-01', fechaFin: null, motivoCambio: 'Apertura de célula', registradoPor: 'Carlos García', activo: true },
  { historialId: 5, liderId: 99, liderNombre: 'Francisco Valdez', nivel: 'LiderCelula', zonaOCelula: 'Célula Fe', fechaInicio: '2020-06-01', fechaFin: '2021-01-10', motivoCambio: 'Cambio de sede', registradoPor: 'Carlos García', activo: false },
  { historialId: 6, liderId: 5, liderNombre: 'Miguel Torres', nivel: 'LiderCelula', zonaOCelula: 'Célula Fe', fechaInicio: '2021-01-15', fechaFin: null, motivoCambio: 'Reemplazo de Francisco Valdez', registradoPor: 'Carlos García', activo: true },
  { historialId: 7, liderId: 6, liderNombre: 'Diana Morales', nivel: 'LiderCelula', zonaOCelula: 'Célula Paz', fechaInicio: '2021-08-20', fechaFin: null, motivoCambio: 'Nueva célula de crecimiento', registradoPor: 'Carlos García', activo: true },
  { historialId: 8, liderId: 7, liderNombre: 'José Ramírez', nivel: 'LiderCelula', zonaOCelula: 'Célula Gracia', fechaInicio: '2020-09-15', fechaFin: null, motivoCambio: 'Apertura de célula', registradoPor: 'Patricia Herrera', activo: true },
  { historialId: 9, liderId: 8, liderNombre: 'Sandra Pérez', nivel: 'LiderCelula', zonaOCelula: 'Célula Amor', fechaInicio: '2022-02-28', fechaFin: null, motivoCambio: 'Nueva célula por crecimiento', registradoPor: 'Patricia Herrera', activo: true },
];
// ── End mock data ──────────────────────────────────────────────────────────────

@Injectable({ providedIn: 'root' })
export class LideresService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/lideres`;

  // ── Árbol jerárquico ─────────────────────────────────────────────────────────

  getArbol(sedeId: number): Observable<LiderNodo> {
    // return this.http.get<LiderNodo>(`${this.base}/arbol`, { params: new HttpParams().set('sedeId', sedeId) });
    return of(MOCK_ARBOL).pipe(delay(400));
  }

  // ── CRUD ─────────────────────────────────────────────────────────────────────

  getAll(sedeId: number): Observable<Lider[]> {
    // return this.http.get<Lider[]>(this.base, { params: new HttpParams().set('sedeId', sedeId) });
    return of(MOCK_LIDERES).pipe(delay(300));
  }

  getById(id: number): Observable<Lider> {
    // return this.http.get<Lider>(`${this.base}/${id}`);
    return of(MOCK_LIDERES.find(l => l.liderId === id)!).pipe(delay(200));
  }

  create(data: CrearLiderRequest): Observable<Lider> {
    // return this.http.post<Lider>(this.base, data);
    const nuevo: Lider = {
      liderId: Math.max(...MOCK_LIDERES.map(l => l.liderId)) + 1,
      miembroId: data.miembroId,
      nombre: 'Nuevo', apellido: 'Líder',
      telefono: null, email: null,
      nivel: data.nivel,
      liderPadreId: data.liderPadreId,
      liderPadreNombre: null,
      zonaOCelula: data.zonaOCelula,
      totalMiembros: 0, totalSubLideres: 0,
      fechaAsignacion: data.fechaAsignacion,
      activo: true,
    };
    MOCK_LIDERES.push(nuevo);
    return of(nuevo).pipe(delay(300));
  }

  update(id: number, data: EditarLiderRequest): Observable<Lider> {
    // return this.http.put<Lider>(`${this.base}/${id}`, data);
    const idx = MOCK_LIDERES.findIndex(l => l.liderId === id);
    if (idx >= 0) {
      MOCK_LIDERES[idx] = { ...MOCK_LIDERES[idx], nivel: data.nivel, liderPadreId: data.liderPadreId, zonaOCelula: data.zonaOCelula };
    }
    return of(MOCK_LIDERES[idx]).pipe(delay(300));
  }

  delete(id: number): Observable<void> {
    // return this.http.delete<void>(`${this.base}/${id}`);
    const idx = MOCK_LIDERES.findIndex(l => l.liderId === id);
    if (idx >= 0) MOCK_LIDERES.splice(idx, 1);
    return of(void 0).pipe(delay(200));
  }

  // ── Historial ────────────────────────────────────────────────────────────────

  getHistorial(sedeId: number): Observable<HistorialLider[]> {
    // return this.http.get<HistorialLider[]>(`${this.base}/historial`, { params: new HttpParams().set('sedeId', sedeId) });
    return of([...MOCK_HISTORIAL].sort((a, b) => b.fechaInicio.localeCompare(a.fechaInicio))).pipe(delay(300));
  }
}
