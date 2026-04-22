export type NivelLider = 'Pastor' | 'LiderZona' | 'LiderCelula';

export interface Lider {
  liderId: number;
  miembroId: number;
  nombre: string;
  apellido: string;
  telefono: string | null;
  email: string | null;
  nivel: NivelLider;
  liderPadreId: number | null;
  liderPadreNombre: string | null;
  zonaOCelula: string;
  totalMiembros: number;
  totalSubLideres: number;
  fechaAsignacion: string;
  activo: boolean;
}

export interface MiembroNodo {
  miembroId: number;
  nombre: string;
  apellido: string;
  estado: string;
  etapaActual: string | null;
}

export interface LiderNodo {
  liderId: number;
  miembroId: number;
  nombre: string;
  apellido: string;
  nivel: NivelLider;
  zonaOCelula: string;
  telefono: string | null;
  totalMiembros: number;
  fechaAsignacion: string;
  subLideres: LiderNodo[];
  miembros?: MiembroNodo[];
}

export interface HistorialLider {
  historialId: number;
  liderId: number;
  liderNombre: string;
  nivel: NivelLider;
  zonaOCelula: string;
  fechaInicio: string;
  fechaFin: string | null;
  motivoCambio: string | null;
  registradoPor: string;
  activo: boolean;
}

export interface CrearLiderRequest {
  sedeId: number;
  miembroId: number;
  nivel: NivelLider;
  liderPadreId: number | null;
  zonaOCelula: string;
  fechaAsignacion: string;
  motivoCambio?: string;
}

export interface EditarLiderRequest {
  nivel: NivelLider;
  liderPadreId: number | null;
  zonaOCelula: string;
  motivoCambio: string;
}
