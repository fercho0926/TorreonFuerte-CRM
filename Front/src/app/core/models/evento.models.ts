export type EventoTipo =
  | 'Encuentro'
  | 'Retiro'
  | 'CultoEspecial'
  | 'Conferencia'
  | 'Campana'
  | 'Capacitacion'
  | 'Otro';

export type EventoEstado = 'Proximo' | 'EnCurso' | 'Finalizado' | 'Cancelado';

export interface Evento {
  eventoId: number;
  sedeId: number;
  nombre: string;
  tipo: EventoTipo;
  descripcion: string;
  fecha: string;
  fechaFin: string | null;
  horaInicio: string;
  horaFin: string;
  lugar: string;
  maxInscritos: number | null;
  totalInscritos: number;
  totalAsistentes: number | null;
  estado: EventoEstado;
  responsable: string;
  color: string;
  colorClaro: string;
}

export interface EventoInscrito {
  inscripcionId: number;
  eventoId: number;
  miembroId: number;
  nombre: string;
  apellido: string;
  telefono: string | null;
  liderNombre: string | null;
  inscritoEn: string;
  asistio: boolean | null;
}

export interface CrearEventoRequest {
  sedeId: number;
  nombre: string;
  tipo: EventoTipo;
  descripcion: string;
  fecha: string;
  fechaFin?: string;
  horaInicio: string;
  horaFin: string;
  lugar: string;
  maxInscritos?: number;
  responsable: string;
}

export const EVENTO_TIPO_META: Record<EventoTipo, { label: string; icono: string; color: string; colorClaro: string }> = {
  Encuentro:    { label: 'Encuentro',      icono: 'pi-users',              color: '#7c3aed', colorClaro: '#ede9fe' },
  Retiro:       { label: 'Retiro',         icono: 'pi-map',                color: '#059669', colorClaro: '#d1fae5' },
  CultoEspecial:{ label: 'Culto Especial', icono: 'pi-building-columns',   color: '#0284c7', colorClaro: '#e0f2fe' },
  Conferencia:  { label: 'Conferencia',    icono: 'pi-microphone',         color: '#d97706', colorClaro: '#fef3c7' },
  Campana:      { label: 'Campaña',        icono: 'pi-megaphone',          color: '#dc2626', colorClaro: '#fee2e2' },
  Capacitacion: { label: 'Capacitación',   icono: 'pi-graduation-cap',     color: '#0891b2', colorClaro: '#cffafe' },
  Otro:         { label: 'Otro',           icono: 'pi-calendar',           color: '#64748b', colorClaro: '#f1f5f9' },
};
