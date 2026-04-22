export type MinisterioTipo = 'Alabanza' | 'Danza' | 'Ujieres' | 'Intercesion' | 'Media' | 'Otro';

export interface IntegranteMinisterio {
  integranteId: number;
  miembroId: number;
  nombre: string;
  apellido: string;
  rol: string;
  fechaIngreso: string;
  activo: boolean;
}

export interface Ministerio {
  ministerioId: number;
  sedeId: number;
  tipo: MinisterioTipo;
  nombre: string;
  descripcion: string;
  icono: string;
  color: string;
  colorClaro: string;
  liderNombre: string;
  liderMiembroId: number;
  totalIntegrantes: number;
  activo: boolean;
  integrantes?: IntegranteMinisterio[];
}

export const MINISTERIO_TIPO_META: Record<MinisterioTipo, { label: string; icono: string; color: string; colorClaro: string }> = {
  Alabanza:   { label: 'Alabanza',    icono: 'music_note',      color: '#7c3aed', colorClaro: '#ede9fe' },
  Danza:      { label: 'Danza',       icono: 'directions_run',  color: '#db2777', colorClaro: '#fce7f3' },
  Ujieres:    { label: 'Ujieres',     icono: 'door_front',      color: '#0891b2', colorClaro: '#e0f2fe' },
  Intercesion:{ label: 'Intercesión', icono: 'volunteer_activism', color: '#d97706', colorClaro: '#fef3c7' },
  Media:      { label: 'Media',       icono: 'videocam',        color: '#16a34a', colorClaro: '#dcfce7' },
  Otro:       { label: 'Otro',        icono: 'groups',          color: '#64748b', colorClaro: '#f1f5f9' },
};
