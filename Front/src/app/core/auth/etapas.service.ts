import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { delay } from 'rxjs/operators';
import { Etapa, MiembroEnEtapa, AvanzarEtapaRequest } from '../models/etapa.models';
import { environment } from '../../../environments/environment';

// ── Mock data ──────────────────────────────────────────────────────────────────
const MOCK_ETAPAS: Etapa[] = [
  {
    etapaId: 1, sedeId: 1,
    nombre: 'Libres para Servir',
    descripcion: 'Primera etapa del camino. Los nuevos creyentes aprenden a conocer su libertad en Cristo, su identidad y su llamado al servicio en la iglesia.',
    orden: 1, icono: 'lock_open',
    color: '#f59e0b', colorClaro: '#fef3c7',
    totalMiembros: 12, activa: true,
  },
  {
    etapaId: 2, sedeId: 1,
    nombre: 'Avivando el Fuego',
    descripcion: 'Segunda etapa. Se profundiza en la oración, el ayuno y el estudio de la Palabra para avivar el fuego espiritual. Formación en dones.',
    orden: 2, icono: 'local_fire_department',
    color: '#ef4444', colorClaro: '#fee2e2',
    totalMiembros: 8, activa: true,
  },
  {
    etapaId: 3, sedeId: 1,
    nombre: 'Caminando en Santidad',
    descripcion: 'Tercera etapa. Formación profunda en carácter, santidad y discipulado. El miembro consolida su vida espiritual y comienza a mentorear a otros.',
    orden: 3, icono: 'auto_awesome',
    color: '#8b5cf6', colorClaro: '#ede9fe',
    totalMiembros: 5, activa: true,
  },
  {
    etapaId: 4, sedeId: 1,
    nombre: 'En Servicio Activo',
    descripcion: 'Etapa final. El miembro completó su formación integral y está activo sirviendo en algún ministerio, célula o liderazgo dentro de la iglesia.',
    orden: 4, icono: 'volunteer_activism',
    color: '#0284c7', colorClaro: '#e0f2fe',
    totalMiembros: 7, activa: true,
  },
];

const MOCK_MIEMBROS: Record<number, MiembroEnEtapa[]> = {
  1: [
    { miembroId: 20, nombre: 'Laura', apellido: 'Castro', telefono: '664-200-0001', liderNombre: 'Ana López', fechaInicioEtapa: '2025-11-10', diasEnEtapa: 161, etapaId: 1 },
    { miembroId: 21, nombre: 'Daniel', apellido: 'Fuentes', telefono: '664-200-0002', liderNombre: 'Ana López', fechaInicioEtapa: '2025-12-05', diasEnEtapa: 136, etapaId: 1 },
    { miembroId: 22, nombre: 'Sofía', apellido: 'Mendoza', telefono: '664-200-0003', liderNombre: 'Miguel Torres', fechaInicioEtapa: '2026-01-20', diasEnEtapa: 90, etapaId: 1 },
    { miembroId: 23, nombre: 'Rafael', apellido: 'Ibáñez', telefono: null, liderNombre: 'Miguel Torres', fechaInicioEtapa: '2026-02-01', diasEnEtapa: 78, etapaId: 1 },
    { miembroId: 24, nombre: 'Gabriela', apellido: 'Ríos', telefono: '664-200-0005', liderNombre: 'Diana Morales', fechaInicioEtapa: '2026-02-14', diasEnEtapa: 65, etapaId: 1 },
    { miembroId: 25, nombre: 'Héctor', apellido: 'Salinas', telefono: '664-200-0006', liderNombre: 'Diana Morales', fechaInicioEtapa: '2026-02-28', diasEnEtapa: 51, etapaId: 1 },
    { miembroId: 26, nombre: 'Valeria', apellido: 'Trejo', telefono: '664-200-0007', liderNombre: 'José Ramírez', fechaInicioEtapa: '2026-03-10', diasEnEtapa: 41, etapaId: 1 },
    { miembroId: 27, nombre: 'Eduardo', apellido: 'Nava', telefono: '664-200-0008', liderNombre: 'José Ramírez', fechaInicioEtapa: '2026-03-15', diasEnEtapa: 36, etapaId: 1 },
    { miembroId: 28, nombre: 'Mariana', apellido: 'Espinoza', telefono: '664-200-0009', liderNombre: 'Sandra Pérez', fechaInicioEtapa: '2026-04-01', diasEnEtapa: 19, etapaId: 1 },
    { miembroId: 29, nombre: 'Rodrigo', apellido: 'Ávila', telefono: '664-200-0010', liderNombre: 'Sandra Pérez', fechaInicioEtapa: '2026-04-05', diasEnEtapa: 15, etapaId: 1 },
    { miembroId: 30, nombre: 'Claudia', apellido: 'Reyes', telefono: '664-200-0011', liderNombre: 'Ana López', fechaInicioEtapa: '2026-04-10', diasEnEtapa: 10, etapaId: 1 },
    { miembroId: 31, nombre: 'Iván', apellido: 'Cisneros', telefono: '664-200-0012', liderNombre: 'Miguel Torres', fechaInicioEtapa: '2026-04-15', diasEnEtapa: 5, etapaId: 1 },
  ],
  2: [
    { miembroId: 40, nombre: 'Fernanda', apellido: 'Gutiérrez', telefono: '664-300-0001', liderNombre: 'Ana López', fechaInicioEtapa: '2025-07-01', diasEnEtapa: 293, etapaId: 2 },
    { miembroId: 41, nombre: 'Armando', apellido: 'Vásquez', telefono: '664-300-0002', liderNombre: 'Miguel Torres', fechaInicioEtapa: '2025-08-15', diasEnEtapa: 248, etapaId: 2 },
    { miembroId: 42, nombre: 'Norma', apellido: 'Pacheco', telefono: '664-300-0003', liderNombre: 'Diana Morales', fechaInicioEtapa: '2025-09-20', diasEnEtapa: 212, etapaId: 2 },
    { miembroId: 43, nombre: 'Jesús', apellido: 'Domínguez', telefono: null, liderNombre: 'José Ramírez', fechaInicioEtapa: '2025-10-10', diasEnEtapa: 192, etapaId: 2 },
    { miembroId: 44, nombre: 'Patricia', apellido: 'Lozano', telefono: '664-300-0005', liderNombre: 'Sandra Pérez', fechaInicioEtapa: '2025-11-01', diasEnEtapa: 170, etapaId: 2 },
    { miembroId: 45, nombre: 'Omar', apellido: 'Vargas', telefono: '664-300-0006', liderNombre: 'Ana López', fechaInicioEtapa: '2025-12-15', diasEnEtapa: 126, etapaId: 2 },
    { miembroId: 46, nombre: 'Leticia', apellido: 'Bravo', telefono: '664-300-0007', liderNombre: 'Miguel Torres', fechaInicioEtapa: '2026-01-08', diasEnEtapa: 102, etapaId: 2 },
    { miembroId: 47, nombre: 'Andrés', apellido: 'Montes', telefono: '664-300-0008', liderNombre: 'Diana Morales', fechaInicioEtapa: '2026-02-20', diasEnEtapa: 59, etapaId: 2 },
  ],
  3: [
    { miembroId: 50, nombre: 'Gloria', apellido: 'Sandoval', telefono: '664-400-0001', liderNombre: 'Carlos García', fechaInicioEtapa: '2024-09-01', diasEnEtapa: 596, etapaId: 3 },
    { miembroId: 51, nombre: 'Ramón', apellido: 'Alvarado', telefono: '664-400-0002', liderNombre: 'Carlos García', fechaInicioEtapa: '2024-10-15', diasEnEtapa: 552, etapaId: 3 },
    { miembroId: 52, nombre: 'Beatriz', apellido: 'Orozco', telefono: '664-400-0003', liderNombre: 'Patricia Herrera', fechaInicioEtapa: '2025-01-10', diasEnEtapa: 465, etapaId: 3 },
    { miembroId: 53, nombre: 'Tomás', apellido: 'Figueroa', telefono: null, liderNombre: 'Patricia Herrera', fechaInicioEtapa: '2025-03-20', diasEnEtapa: 396, etapaId: 3 },
    { miembroId: 54, nombre: 'Yolanda', apellido: 'Cervantes', telefono: '664-400-0005', liderNombre: 'Carlos García', fechaInicioEtapa: '2025-06-01', diasEnEtapa: 323, etapaId: 3 },
  ],
  4: [
    { miembroId: 60, nombre: 'Ernesto', apellido: 'Palomino', telefono: '664-500-0001', liderNombre: 'Roberto Martínez', fechaInicioEtapa: '2023-03-15', diasEnEtapa: 1131, etapaId: 4 },
    { miembroId: 61, nombre: 'Marisol', apellido: 'Acosta', telefono: '664-500-0002', liderNombre: 'Roberto Martínez', fechaInicioEtapa: '2023-07-01', diasEnEtapa: 1023, etapaId: 4 },
    { miembroId: 62, nombre: 'Felipe', apellido: 'Guerrero', telefono: '664-500-0003', liderNombre: 'Roberto Martínez', fechaInicioEtapa: '2024-01-10', diasEnEtapa: 831, etapaId: 4 },
    { miembroId: 63, nombre: 'Consuelo', apellido: 'Ramos', telefono: '664-500-0004', liderNombre: 'Roberto Martínez', fechaInicioEtapa: '2024-06-20', diasEnEtapa: 669, etapaId: 4 },
    { miembroId: 64, nombre: 'Salvador', apellido: 'Medina', telefono: '664-500-0005', liderNombre: 'Roberto Martínez', fechaInicioEtapa: '2024-11-01', diasEnEtapa: 535, etapaId: 4 },
    { miembroId: 65, nombre: 'Teresa', apellido: 'Aguilera', telefono: '664-500-0006', liderNombre: 'Roberto Martínez', fechaInicioEtapa: '2025-04-05', diasEnEtapa: 380, etapaId: 4 },
    { miembroId: 66, nombre: 'Hugo', apellido: 'Bustamante', telefono: '664-500-0007', liderNombre: 'Roberto Martínez', fechaInicioEtapa: '2025-09-15', diasEnEtapa: 217, etapaId: 4 },
  ],
};
// ── End mock data ──────────────────────────────────────────────────────────────

@Injectable({ providedIn: 'root' })
export class EtapasService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/etapas`;

  getEtapas(sedeId: number): Observable<Etapa[]> {
    // return this.http.get<Etapa[]>(this.base, { params: new HttpParams().set('sedeId', sedeId) });
    return of([...MOCK_ETAPAS]).pipe(delay(300));
  }

  getMiembrosEnEtapa(etapaId: number): Observable<MiembroEnEtapa[]> {
    // return this.http.get<MiembroEnEtapa[]>(`${this.base}/${etapaId}/miembros`);
    return of(MOCK_MIEMBROS[etapaId] ?? []).pipe(delay(200));
  }

  avanzarEtapa(req: AvanzarEtapaRequest): Observable<void> {
    // return this.http.post<void>(`${this.base}/avanzar`, req);
    const lista = MOCK_MIEMBROS[req.etapaIdActual];
    if (lista) {
      const idx = lista.findIndex(m => m.miembroId === req.miembroId);
      if (idx >= 0) lista.splice(idx, 1);
      MOCK_ETAPAS[req.etapaIdActual - 1].totalMiembros = lista.length;
    }
    if (req.etapaIdNueva) {
      const destino = MOCK_MIEMBROS[req.etapaIdNueva];
      if (destino) {
        const origen = MOCK_MIEMBROS[req.etapaIdActual]?.find(m => m.miembroId === req.miembroId);
        if (origen) destino.push({ ...origen, etapaId: req.etapaIdNueva, fechaInicioEtapa: new Date().toISOString().split('T')[0], diasEnEtapa: 0 });
        MOCK_ETAPAS[req.etapaIdNueva - 1].totalMiembros = destino.length;
      }
    }
    return of(void 0).pipe(delay(300));
  }
}
