import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { delay, map } from 'rxjs/operators';
import { Ministerio, IntegranteMinisterio, MinisterioTipo, MINISTERIO_TIPO_META } from '../models/ministerio.models';
import { environment } from '../../../environments/environment';

const MOCK_MINISTERIOS: Ministerio[] = [
  {
    ministerioId: 1, sedeId: 1, tipo: 'Alabanza', nombre: 'Equipo de Alabanza Central',
    descripcion: 'Lideramos la adoración en los cultos dominicales y eventos especiales.',
    icono: 'music_note', color: '#7c3aed', colorClaro: '#ede9fe',
    liderNombre: 'Carlos Ruiz', liderMiembroId: 2, totalIntegrantes: 8, activo: true,
    integrantes: [
      { integranteId: 1, miembroId: 2,  nombre: 'Carlos',    apellido: 'Ruiz',      rol: 'Director',        fechaIngreso: '2019-06-15', activo: true },
      { integranteId: 2, miembroId: 1,  nombre: 'Ana',       apellido: 'Gómez',     rol: 'Vocalista',       fechaIngreso: '2022-03-10', activo: true },
      { integranteId: 3, miembroId: 18, nombre: 'Mauricio',  apellido: 'Vélez',     rol: 'Guitarrista',     fechaIngreso: '2020-11-14', activo: true },
      { integranteId: 4, miembroId: 12, nombre: 'Felipe',    apellido: 'Ríos',      rol: 'Baterista',       fechaIngreso: '2021-05-01', activo: true },
      { integranteId: 5, miembroId: 8,  nombre: 'Andrés',    apellido: 'Mora',      rol: 'Bajista',         fechaIngreso: '2022-07-18', activo: true },
      { integranteId: 6, miembroId: 9,  nombre: 'Valentina', apellido: 'Torres',    rol: 'Vocalista',       fechaIngreso: '2021-05-22', activo: true },
      { integranteId: 7, miembroId: 19, nombre: 'Daniela',   apellido: 'Reyes',     rol: 'Teclados',        fechaIngreso: '2022-02-03', activo: true },
      { integranteId: 8, miembroId: 15, nombre: 'Camila',    apellido: 'Ospina',    rol: 'Vocalista',       fechaIngreso: '2023-01-10', activo: true },
    ],
  },
  {
    ministerioId: 2, sedeId: 1, tipo: 'Danza', nombre: 'Ministerio de Danza',
    descripcion: 'Expresamos la adoración a través del movimiento y la danza profética.',
    icono: 'directions_run', color: '#db2777', colorClaro: '#fce7f3',
    liderNombre: 'Laura Vargas', liderMiembroId: 5, totalIntegrantes: 6, activo: true,
    integrantes: [
      { integranteId: 9,  miembroId: 5,  nombre: 'Laura',     apellido: 'Vargas',    rol: 'Directora',       fechaIngreso: '2018-11-30', activo: true },
      { integranteId: 10, miembroId: 3,  nombre: 'María',     apellido: 'López',     rol: 'Bailarina',       fechaIngreso: '2021-03-15', activo: true },
      { integranteId: 11, miembroId: 13, nombre: 'Natalia',   apellido: 'Jiménez',   rol: 'Bailarina',       fechaIngreso: '2022-12-01', activo: true },
      { integranteId: 12, miembroId: 17, nombre: 'Isabela',   apellido: 'Arango',    rol: 'Bailarina',       fechaIngreso: '2023-06-30', activo: true },
      { integranteId: 13, miembroId: 20, nombre: 'Esteban',   apellido: 'Cardona',   rol: 'Bailarín',        fechaIngreso: '2022-09-25', activo: true },
      { integranteId: 14, miembroId: 7,  nombre: 'Sofía',     apellido: 'Castro',    rol: 'Bailarina',       fechaIngreso: '2024-04-01', activo: false },
    ],
  },
  {
    ministerioId: 3, sedeId: 1, tipo: 'Ujieres', nombre: 'Ministerio de Ujieres',
    descripcion: 'Servimos con excelencia recibiendo y acomodando a la congregación.',
    icono: 'door_front', color: '#0891b2', colorClaro: '#e0f2fe',
    liderNombre: 'Jorge Salazar', liderMiembroId: 10, totalIntegrantes: 7, activo: true,
    integrantes: [
      { integranteId: 15, miembroId: 10, nombre: 'Jorge',     apellido: 'Salazar',   rol: 'Coordinador',     fechaIngreso: '2017-08-09', activo: true },
      { integranteId: 16, miembroId: 11, nombre: 'Diana',     apellido: 'Patiño',    rol: 'Ujiera',          fechaIngreso: '2023-10-05', activo: true },
      { integranteId: 17, miembroId: 4,  nombre: 'Luis',      apellido: 'Martínez',  rol: 'Ujiero',          fechaIngreso: '2021-01-10', activo: true },
      { integranteId: 18, miembroId: 6,  nombre: 'Pedro',     apellido: 'Herrera',   rol: 'Ujiero',          fechaIngreso: '2023-02-14', activo: true },
      { integranteId: 19, miembroId: 16, nombre: 'Héctor',    apellido: 'Cano',      rol: 'Ujiero',          fechaIngreso: '2018-05-20', activo: true },
      { integranteId: 20, miembroId: 15, nombre: 'Camila',    apellido: 'Ospina',    rol: 'Ujiera',          fechaIngreso: '2022-07-01', activo: true },
      { integranteId: 21, miembroId: 8,  nombre: 'Andrés',    apellido: 'Mora',      rol: 'Ujiero',          fechaIngreso: '2023-03-15', activo: false },
    ],
  },
  {
    ministerioId: 4, sedeId: 1, tipo: 'Intercesion', nombre: 'Grupo de Intercesión',
    descripcion: 'Sostenemos la iglesia con oración e intercesión continua.',
    icono: 'volunteer_activism', color: '#d97706', colorClaro: '#fef3c7',
    liderNombre: 'Héctor Cano', liderMiembroId: 16, totalIntegrantes: 5, activo: true,
    integrantes: [
      { integranteId: 22, miembroId: 16, nombre: 'Héctor',    apellido: 'Cano',      rol: 'Líder',           fechaIngreso: '2016-02-20', activo: true },
      { integranteId: 23, miembroId: 3,  nombre: 'María',     apellido: 'López',     rol: 'Intercesora',     fechaIngreso: '2021-06-01', activo: true },
      { integranteId: 24, miembroId: 19, nombre: 'Daniela',   apellido: 'Reyes',     rol: 'Intercesora',     fechaIngreso: '2022-04-10', activo: true },
      { integranteId: 25, miembroId: 1,  nombre: 'Ana',       apellido: 'Gómez',     rol: 'Intercesora',     fechaIngreso: '2022-09-05', activo: true },
      { integranteId: 26, miembroId: 13, nombre: 'Natalia',   apellido: 'Jiménez',   rol: 'Intercesora',     fechaIngreso: '2023-01-20', activo: true },
    ],
  },
  {
    ministerioId: 5, sedeId: 1, tipo: 'Media', nombre: 'Ministerio de Medios',
    descripcion: 'Transmitimos y registramos los cultos y eventos para mayor alcance.',
    icono: 'videocam', color: '#16a34a', colorClaro: '#dcfce7',
    liderNombre: 'Felipe Ríos', liderMiembroId: 12, totalIntegrantes: 4, activo: true,
    integrantes: [
      { integranteId: 27, miembroId: 12, nombre: 'Felipe',    apellido: 'Ríos',      rol: 'Director',        fechaIngreso: '2021-08-01', activo: true },
      { integranteId: 28, miembroId: 20, nombre: 'Esteban',   apellido: 'Cardona',   rol: 'Camarógrafo',     fechaIngreso: '2022-01-15', activo: true },
      { integranteId: 29, miembroId: 6,  nombre: 'Pedro',     apellido: 'Herrera',   rol: 'Sonidista',       fechaIngreso: '2023-03-10', activo: true },
      { integranteId: 30, miembroId: 17, nombre: 'Isabela',   apellido: 'Arango',    rol: 'Diseñadora',      fechaIngreso: '2023-07-20', activo: true },
    ],
  },
];

@Injectable({ providedIn: 'root' })
export class MinisteriosService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/ministerios`;

  getAll(sedeId: number): Observable<Ministerio[]> {
    // return this.http.get<Ministerio[]>(this.base, { params: { sedeId } });
    return of(MOCK_MINISTERIOS.filter(m => m.sedeId === sedeId)).pipe(delay(300));
  }

  getById(id: number): Observable<Ministerio> {
    // return this.http.get<Ministerio>(`${this.base}/${id}`);
    return of(MOCK_MINISTERIOS.find(m => m.ministerioId === id)!).pipe(delay(200));
  }

  create(data: { sedeId: number; tipo: MinisterioTipo; nombre: string; descripcion: string; liderMiembroId: number; liderNombre: string }): Observable<Ministerio> {
    // return this.http.post<Ministerio>(this.base, data);
    const meta = MINISTERIO_TIPO_META[data.tipo];
    const nuevo: Ministerio = {
      ministerioId: Math.max(0, ...MOCK_MINISTERIOS.map(m => m.ministerioId)) + 1,
      sedeId: data.sedeId, tipo: data.tipo, nombre: data.nombre,
      descripcion: data.descripcion, icono: meta.icono,
      color: meta.color, colorClaro: meta.colorClaro,
      liderNombre: data.liderNombre, liderMiembroId: data.liderMiembroId,
      totalIntegrantes: 0, activo: true, integrantes: [],
    };
    MOCK_MINISTERIOS.push(nuevo);
    return of(nuevo).pipe(delay(300));
  }

  update(id: number, data: { nombre: string; descripcion: string; liderMiembroId: number; liderNombre: string }): Observable<Ministerio> {
    // return this.http.put<Ministerio>(`${this.base}/${id}`, data);
    const min = MOCK_MINISTERIOS.find(m => m.ministerioId === id);
    if (min) { Object.assign(min, data); }
    return of(min!).pipe(delay(250));
  }

  delete(id: number): Observable<void> {
    // return this.http.delete<void>(`${this.base}/${id}`);
    const idx = MOCK_MINISTERIOS.findIndex(m => m.ministerioId === id);
    if (idx >= 0) MOCK_MINISTERIOS.splice(idx, 1);
    return of(undefined).pipe(delay(200));
  }

  agregarIntegrante(ministerioId: number, miembroId: number, nombre: string, apellido: string, rol: string): Observable<void> {
    // return this.http.post<void>(`${this.base}/${ministerioId}/integrantes`, { miembroId, rol });
    const min = MOCK_MINISTERIOS.find(m => m.ministerioId === ministerioId);
    if (min) {
      const id = Math.max(0, ...(min.integrantes ?? []).map(i => i.integranteId)) + 1;
      min.integrantes = [...(min.integrantes ?? []), {
        integranteId: id, miembroId, nombre, apellido, rol,
        fechaIngreso: new Date().toISOString().split('T')[0], activo: true,
      }];
      min.totalIntegrantes = min.integrantes.filter(i => i.activo).length;
    }
    return of(undefined).pipe(delay(200));
  }

  removerIntegrante(ministerioId: number, integranteId: number): Observable<void> {
    // return this.http.delete<void>(`${this.base}/${ministerioId}/integrantes/${integranteId}`);
    const min = MOCK_MINISTERIOS.find(m => m.ministerioId === ministerioId);
    if (min?.integrantes) {
      min.integrantes = min.integrantes.filter(i => i.integranteId !== integranteId);
      min.totalIntegrantes = min.integrantes.filter(i => i.activo).length;
    }
    return of(undefined).pipe(delay(150));
  }

  toggleActivo(ministerioId: number, integranteId: number, activo: boolean): Observable<void> {
    // return this.http.patch<void>(`${this.base}/${ministerioId}/integrantes/${integranteId}`, { activo });
    const min = MOCK_MINISTERIOS.find(m => m.ministerioId === ministerioId);
    const int = min?.integrantes?.find(i => i.integranteId === integranteId);
    if (int) { int.activo = activo; if (min) min.totalIntegrantes = min.integrantes!.filter(i => i.activo).length; }
    return of(undefined).pipe(delay(150));
  }
}
