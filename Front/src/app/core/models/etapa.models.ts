export interface Etapa {
  etapaId: number;
  sedeId: number;
  nombre: string;
  descripcion: string;
  orden: number;
  icono: string;
  color: string;
  colorClaro: string;
  totalMiembros: number;
  activa: boolean;
}

export interface MiembroEnEtapa {
  miembroId: number;
  nombre: string;
  apellido: string;
  telefono: string | null;
  liderNombre: string | null;
  fechaInicioEtapa: string;
  diasEnEtapa: number;
  etapaId: number;
}

export interface AvanzarEtapaRequest {
  miembroId: number;
  etapaIdActual: number;
  etapaIdNueva: number | null;
  nota?: string;
}

export interface NodePosition {
  x: number;
  y: number;
  xPct: number;
}
