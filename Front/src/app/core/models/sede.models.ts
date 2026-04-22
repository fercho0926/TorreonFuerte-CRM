export interface Sede {
  sedeId: number;
  nombre: string;
  ciudad: string;
  departamento: string;
  pais: string;
  bandera: string;
  lat: number;
  lng: number;
  color: string;
  totalMiembros: number;
  totalCelulas: number;
  totalLideres: number;
  pastor: string;
  telefono: string | null;
  email: string | null;
  direccion: string | null;
  activa: boolean;
  fechaFundacion: string;
}

export interface SedeStats {
  totalSedes: number;
  totalMiembros: number;
  totalPaises: number;
  totalContinentes: number;
}
