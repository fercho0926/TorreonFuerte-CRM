export interface DashboardData {
  totalMiembros: number;
  totalVisitantes: number;
  totalActivos: number;
  totalInactivos: number;
  totalLideres: number;
  totalCelulas: number;
  totalSedes: number;
  totalMinisterios: number;
  miembrosPorEtapa: EtapaConteo[];
  proximosEventos: EventoProximo[];
  ministeriosResumen: MinisterioResumen[];
}

export interface EtapaConteo {
  etapa: string;
  conteo: number;
  color: string;
}

export interface EventoProximo {
  eventoId: number;
  nombre: string;
  fecha: string;
  tipo: string;
  color: string;
  icono: string;
  totalInscritos: number;
}

export interface MinisterioResumen {
  ministerioId: number;
  nombre: string;
  tipo: string;
  icono: string;
  color: string;
  colorClaro: string;
  totalIntegrantes: number;
  liderNombre: string;
}
