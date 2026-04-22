import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { delay } from 'rxjs/operators';
import {
  Evento, EventoInscrito, CrearEventoRequest,
  EventoTipo, EventoEstado, EVENTO_TIPO_META,
} from '../models/evento.models';
import { environment } from '../../../environments/environment';

// ── Helpers ────────────────────────────────────────────────────────────────────
function meta(tipo: EventoTipo) { return EVENTO_TIPO_META[tipo]; }

// ── Mock data ──────────────────────────────────────────────────────────────────
let MOCK_EVENTOS: Evento[] = [
  // ── Próximos ────────────────────────────────────────────────────────────────
  {
    eventoId: 1, sedeId: 1,
    nombre: 'Retiro de Hombres 2026',
    tipo: 'Retiro',
    descripcion: 'Tres días de encuentro, oración y comunidad para hombres de todas las células. Predicación, adoración y actividades al aire libre.',
    fecha: '2026-05-09', fechaFin: '2026-05-11',
    horaInicio: '16:00', horaFin: '14:00',
    lugar: 'Finca La Esperanza, Guatapé',
    maxInscritos: 60, totalInscritos: 45, totalAsistentes: null,
    estado: 'Proximo', responsable: 'Carlos García',
    ...meta('Retiro'),
  },
  {
    eventoId: 2, sedeId: 1,
    nombre: 'Conferencia de Mujeres — Llamadas a Reinar',
    tipo: 'Conferencia',
    descripcion: 'Conferencia anual de mujeres con oradora invitada. Adoración, enseñanza y activación espiritual.',
    fecha: '2026-05-23', fechaFin: null,
    horaInicio: '09:00', horaFin: '17:00',
    lugar: 'Templo Central Medellín',
    maxInscritos: 120, totalInscritos: 87, totalAsistentes: null,
    estado: 'Proximo', responsable: 'Patricia Herrera',
    ...meta('Conferencia'),
  },
  {
    eventoId: 3, sedeId: 1,
    nombre: 'Encuentro de Jóvenes — Fuego Nuevo',
    tipo: 'Encuentro',
    descripcion: 'Encuentro intensivo de formación espiritual para jóvenes de 15 a 30 años. Campamento con talleres, deportes y adoración nocturna.',
    fecha: '2026-06-06', fechaFin: '2026-06-08',
    horaInicio: '14:00', horaFin: '12:00',
    lugar: 'Campamento Monte Sion, Santa Elena',
    maxInscritos: 80, totalInscritos: 54, totalAsistentes: null,
    estado: 'Proximo', responsable: 'José Ramírez',
    ...meta('Encuentro'),
  },
  {
    eventoId: 4, sedeId: 1,
    nombre: 'Capacitación de Líderes Q2',
    tipo: 'Capacitacion',
    descripcion: 'Sesión trimestral de formación para líderes de célula y zona. Temas: multiplicación de células, discipulado y manejo de conflictos.',
    fecha: '2026-05-17', fechaFin: null,
    horaInicio: '08:00', horaFin: '13:00',
    lugar: 'Sala de Conferencias — Sede Central',
    maxInscritos: null, totalInscritos: 22, totalAsistentes: null,
    estado: 'Proximo', responsable: 'Roberto Martínez',
    ...meta('Capacitacion'),
  },
  {
    eventoId: 5, sedeId: 1,
    nombre: 'Campaña Evangelismo Barrio Enciso',
    tipo: 'Campana',
    descripcion: 'Campaña de evangelismo en las calles del barrio Enciso. Actividades para niños, música y predicación al aire libre.',
    fecha: '2026-05-30', fechaFin: '2026-06-01',
    horaInicio: '17:00', horaFin: '21:00',
    lugar: 'Parque Principal Enciso, Medellín',
    maxInscritos: null, totalInscritos: 38, totalAsistentes: null,
    estado: 'Proximo', responsable: 'Carmen Zapata',
    ...meta('Campana'),
  },
  // ── Historial ────────────────────────────────────────────────────────────────
  {
    eventoId: 6, sedeId: 1,
    nombre: 'Encuentro de Parejas — Amor que Transforma',
    tipo: 'Encuentro',
    descripcion: 'Encuentro de fin de semana para parejas. Consejería, testimonios y renovación de votos.',
    fecha: '2026-04-05', fechaFin: '2026-04-06',
    horaInicio: '15:00', horaFin: '13:00',
    lugar: 'Hotel Dann Carlton, Medellín',
    maxInscritos: 80, totalInscritos: 74, totalAsistentes: 68,
    estado: 'Finalizado', responsable: 'Roberto Martínez',
    ...meta('Encuentro'),
  },
  {
    eventoId: 7, sedeId: 1,
    nombre: 'Culto Especial de Resurrección',
    tipo: 'CultoEspecial',
    descripcion: 'Culto de Semana Santa con servicio especial de comunión y adoración multitudinaria.',
    fecha: '2026-03-29', fechaFin: null,
    horaInicio: '10:00', horaFin: '13:00',
    lugar: 'Templo Central Medellín',
    maxInscritos: null, totalInscritos: 162, totalAsistentes: 154,
    estado: 'Finalizado', responsable: 'Roberto Martínez',
    ...meta('CultoEspecial'),
  },
  {
    eventoId: 8, sedeId: 1,
    nombre: 'Campaña de Evangelismo Q1',
    tipo: 'Campana',
    descripcion: 'Campaña evangelística en varios puntos de la ciudad. Testimonios y predicación abierta.',
    fecha: '2026-03-07', fechaFin: '2026-03-09',
    horaInicio: '17:00', horaFin: '21:00',
    lugar: 'Varios puntos — Medellín',
    maxInscritos: null, totalInscritos: 55, totalAsistentes: 51,
    estado: 'Finalizado', responsable: 'Patricia Herrera',
    ...meta('Campana'),
  },
  {
    eventoId: 9, sedeId: 1,
    nombre: 'Retiro de Damas — Esencia',
    tipo: 'Retiro',
    descripcion: 'Retiro espiritual para mujeres. Talleres de identidad, sanidad interior y propósito.',
    fecha: '2026-02-14', fechaFin: '2026-02-16',
    horaInicio: '16:00', horaFin: '14:00',
    lugar: 'Casa de Retiros Santa María, Rionegro',
    maxInscritos: 40, totalInscritos: 37, totalAsistentes: 33,
    estado: 'Finalizado', responsable: 'Patricia Herrera',
    ...meta('Retiro'),
  },
  {
    eventoId: 10, sedeId: 1,
    nombre: 'Capacitación de Líderes Q1',
    tipo: 'Capacitacion',
    descripcion: 'Primera capacitación del año para líderes. Visión 2026 y estrategias de crecimiento.',
    fecha: '2026-01-18', fechaFin: null,
    horaInicio: '08:00', horaFin: '13:00',
    lugar: 'Sala de Conferencias — Sede Central',
    maxInscritos: null, totalInscritos: 24, totalAsistentes: 22,
    estado: 'Finalizado', responsable: 'Roberto Martínez',
    ...meta('Capacitacion'),
  },
];

const INSCRITOS_BASE: Omit<EventoInscrito, 'inscripcionId' | 'eventoId'>[] = [
  { miembroId: 20, nombre: 'Laura',    apellido: 'Castro',    telefono: '664-200-0001', liderNombre: 'Ana López',      inscritoEn: '2026-04-01', asistio: null },
  { miembroId: 21, nombre: 'Daniel',   apellido: 'Fuentes',   telefono: '664-200-0002', liderNombre: 'Ana López',      inscritoEn: '2026-04-02', asistio: null },
  { miembroId: 22, nombre: 'Sofía',    apellido: 'Mendoza',   telefono: '664-200-0003', liderNombre: 'Miguel Torres',  inscritoEn: '2026-04-03', asistio: null },
  { miembroId: 23, nombre: 'Rafael',   apellido: 'Ibáñez',    telefono: null,           liderNombre: 'Miguel Torres',  inscritoEn: '2026-04-04', asistio: null },
  { miembroId: 24, nombre: 'Gabriela', apellido: 'Ríos',      telefono: '664-200-0005', liderNombre: 'Diana Morales',  inscritoEn: '2026-04-05', asistio: null },
  { miembroId: 25, nombre: 'Héctor',   apellido: 'Salinas',   telefono: '664-200-0006', liderNombre: 'Diana Morales',  inscritoEn: '2026-04-06', asistio: null },
  { miembroId: 26, nombre: 'Valeria',  apellido: 'Trejo',     telefono: '664-200-0007', liderNombre: 'José Ramírez',   inscritoEn: '2026-04-07', asistio: null },
  { miembroId: 27, nombre: 'Eduardo',  apellido: 'Nava',      telefono: '664-200-0008', liderNombre: 'José Ramírez',   inscritoEn: '2026-04-08', asistio: null },
  { miembroId: 28, nombre: 'Mariana',  apellido: 'Espinoza',  telefono: '664-200-0009', liderNombre: 'Sandra Pérez',   inscritoEn: '2026-04-09', asistio: null },
  { miembroId: 29, nombre: 'Rodrigo',  apellido: 'Ávila',     telefono: '664-200-0010', liderNombre: 'Sandra Pérez',   inscritoEn: '2026-04-10', asistio: null },
  { miembroId: 40, nombre: 'Fernanda', apellido: 'Gutiérrez', telefono: '664-300-0001', liderNombre: 'Ana López',      inscritoEn: '2026-04-11', asistio: null },
  { miembroId: 41, nombre: 'Armando',  apellido: 'Vásquez',   telefono: '664-300-0002', liderNombre: 'Miguel Torres',  inscritoEn: '2026-04-12', asistio: null },
];

const MOCK_INSCRITOS: Record<number, EventoInscrito[]> = {};
MOCK_EVENTOS.forEach(e => {
  MOCK_INSCRITOS[e.eventoId] = INSCRITOS_BASE.slice(0, e.totalInscritos).map((m, i) => ({
    ...m,
    inscripcionId: e.eventoId * 100 + i,
    eventoId: e.eventoId,
    asistio: e.estado === 'Finalizado'
      ? (i < (e.totalAsistentes ?? 0) ? true : false)
      : null,
  }));
});
// ── End mock data ──────────────────────────────────────────────────────────────

@Injectable({ providedIn: 'root' })
export class EventosService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/eventos`;

  getAll(sedeId: number): Observable<Evento[]> {
    // return this.http.get<Evento[]>(this.base, { params: { sedeId } });
    return of([...MOCK_EVENTOS]).pipe(delay(300));
  }

  getById(id: number): Observable<Evento> {
    // return this.http.get<Evento>(`${this.base}/${id}`);
    return of(MOCK_EVENTOS.find(e => e.eventoId === id)!).pipe(delay(150));
  }

  getInscritos(eventoId: number): Observable<EventoInscrito[]> {
    // return this.http.get<EventoInscrito[]>(`${this.base}/${eventoId}/inscritos`);
    return of(MOCK_INSCRITOS[eventoId] ?? []).pipe(delay(200));
  }

  create(data: CrearEventoRequest): Observable<Evento> {
    // return this.http.post<Evento>(this.base, data);
    const tipo = data.tipo;
    const nuevo: Evento = {
      eventoId: Math.max(...MOCK_EVENTOS.map(e => e.eventoId)) + 1,
      sedeId: data.sedeId,
      nombre: data.nombre,
      tipo,
      descripcion: data.descripcion,
      fecha: data.fecha,
      fechaFin: data.fechaFin ?? null,
      horaInicio: data.horaInicio,
      horaFin: data.horaFin,
      lugar: data.lugar,
      maxInscritos: data.maxInscritos ?? null,
      totalInscritos: 0,
      totalAsistentes: null,
      estado: 'Proximo',
      responsable: data.responsable,
      color: EVENTO_TIPO_META[tipo].color,
      colorClaro: EVENTO_TIPO_META[tipo].colorClaro,
    };
    MOCK_EVENTOS.unshift(nuevo);
    MOCK_INSCRITOS[nuevo.eventoId] = [];
    return of(nuevo).pipe(delay(300));
  }

  update(id: number, data: Partial<CrearEventoRequest>): Observable<Evento> {
    // return this.http.put<Evento>(`${this.base}/${id}`, data);
    const idx = MOCK_EVENTOS.findIndex(e => e.eventoId === id);
    if (idx >= 0) MOCK_EVENTOS[idx] = { ...MOCK_EVENTOS[idx], ...data };
    return of(MOCK_EVENTOS[idx]).pipe(delay(300));
  }

  delete(id: number): Observable<void> {
    // return this.http.delete<void>(`${this.base}/${id}`);
    MOCK_EVENTOS = MOCK_EVENTOS.filter(e => e.eventoId !== id);
    return of(void 0).pipe(delay(200));
  }

  registrarAsistencia(eventoId: number, miembroId: number, asistio: boolean): Observable<void> {
    // return this.http.patch<void>(`${this.base}/${eventoId}/asistencia/${miembroId}`, { asistio });
    const lista = MOCK_INSCRITOS[eventoId];
    if (lista) {
      const m = lista.find(x => x.miembroId === miembroId);
      if (m) m.asistio = asistio;
      const e = MOCK_EVENTOS.find(x => x.eventoId === eventoId);
      if (e) e.totalAsistentes = lista.filter(x => x.asistio === true).length;
    }
    return of(void 0).pipe(delay(100));
  }

  cambiarEstado(id: number, estado: EventoEstado): Observable<Evento> {
    // return this.http.patch<Evento>(`${this.base}/${id}/estado`, { estado });
    const idx = MOCK_EVENTOS.findIndex(e => e.eventoId === id);
    if (idx >= 0) MOCK_EVENTOS[idx].estado = estado;
    return of(MOCK_EVENTOS[idx]).pipe(delay(200));
  }
}
