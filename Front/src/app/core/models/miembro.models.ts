export type EstadoMiembro = 'Visitante' | 'Activo' | 'Inactivo' | 'Lider';

export interface MiembroList {
  miembroId: number;
  nombre: string;
  apellido: string;
  telefono: string | null;
  email: string | null;
  estado: EstadoMiembro;
  fechaIngreso: string;
  liderNombre: string | null;
  etapaActual: string | null;
}

export interface MiembroDetalle {
  miembroId: number;
  sedeId: number;
  sedeNombre: string;
  nombre: string;
  apellido: string;
  cedula: string | null;
  fechaNacimiento: string | null;
  telefono: string | null;
  email: string | null;
  direccion: string | null;
  estado: EstadoMiembro;
  fechaIngreso: string;
  liderCelulaId: number | null;
  liderNombre: string | null;
  liderTelefono: string | null;
  redNombre: string | null;
  notas: string | null;
  etapaActualOrden: number | null;
  etapas: MiembroEtapaItem[];
  asistencias: EventoAsistenciaItem[];
  ministerios: MiembroMinisterioItem[];
}

export interface MiembroMinisterioItem {
  ministerioId: number;
  ministerioNombre: string;
  rol: string;
  color: string;
  activo: boolean;
}

export interface MiembroEtapaItem {
  etapaId: number;
  etapaNombre: string;
  fechaInicio: string;
  fechaFin: string | null;
  completada: boolean;
}

export interface EventoAsistenciaItem {
  eventoId: number;
  eventoNombre: string;
  fecha: string;
  inscrito: boolean;
  asistio: boolean | null;
}

export interface CrearMiembroRequest {
  sedeId: number;
  nombre: string;
  apellido: string;
  cedula?: string;
  fechaNacimiento?: string;
  telefono?: string;
  email?: string;
  direccion?: string;
  estado: EstadoMiembro;
  liderCelulaId?: number;
  notas?: string;
}
