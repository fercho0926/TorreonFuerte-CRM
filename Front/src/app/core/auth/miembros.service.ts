import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { delay } from 'rxjs/operators';
import { CrearMiembroRequest, MiembroDetalle, MiembroList } from '../models/miembro.models';
import { environment } from '../../../environments/environment';

export const MOCK_MIEMBROS: MiembroList[] = [
  { miembroId: 1,  nombre: 'Ana',       apellido: 'Gómez',      telefono: '+57 300 111 0001', email: 'ana.gomez@mail.com',        estado: 'Activo',    fechaIngreso: '2022-03-10', liderNombre: 'Carlos Ruiz',   etapaActual: 'Avivando el Fuego' },
  { miembroId: 2,  nombre: 'Carlos',    apellido: 'Ruiz',       telefono: '+57 300 111 0002', email: 'carlos.ruiz@mail.com',      estado: 'Lider',     fechaIngreso: '2019-06-15', liderNombre: null,            etapaActual: 'En Servicio Activo' },
  { miembroId: 3,  nombre: 'María',     apellido: 'López',      telefono: '+57 300 111 0003', email: 'maria.lopez@mail.com',      estado: 'Activo',    fechaIngreso: '2021-01-20', liderNombre: 'Laura Vargas',  etapaActual: 'Caminando en Santidad' },
  { miembroId: 4,  nombre: 'Luis',      apellido: 'Martínez',   telefono: '+57 300 111 0004', email: 'luis.martinez@mail.com',    estado: 'Activo',    fechaIngreso: '2020-09-05', liderNombre: 'Carlos Ruiz',   etapaActual: 'Libres para Servir' },
  { miembroId: 5,  nombre: 'Laura',     apellido: 'Vargas',     telefono: '+57 300 111 0005', email: 'laura.vargas@mail.com',     estado: 'Lider',     fechaIngreso: '2018-11-30', liderNombre: null,            etapaActual: 'En Servicio Activo' },
  { miembroId: 6,  nombre: 'Pedro',     apellido: 'Herrera',    telefono: '+57 300 111 0006', email: 'pedro.herrera@mail.com',    estado: 'Activo',    fechaIngreso: '2023-02-14', liderNombre: 'Laura Vargas',  etapaActual: 'Libres para Servir' },
  { miembroId: 7,  nombre: 'Sofía',     apellido: 'Castro',     telefono: '+57 300 111 0007', email: 'sofia.castro@mail.com',     estado: 'Visitante', fechaIngreso: '2024-04-01', liderNombre: null,            etapaActual: null },
  { miembroId: 8,  nombre: 'Andrés',    apellido: 'Mora',       telefono: '+57 300 111 0008', email: 'andres.mora@mail.com',      estado: 'Activo',    fechaIngreso: '2022-07-18', liderNombre: 'Carlos Ruiz',   etapaActual: 'Avivando el Fuego' },
  { miembroId: 9,  nombre: 'Valentina', apellido: 'Torres',     telefono: '+57 300 111 0009', email: 'valentina.torres@mail.com', estado: 'Activo',    fechaIngreso: '2021-05-22', liderNombre: 'Laura Vargas',  etapaActual: 'Caminando en Santidad' },
  { miembroId: 10, nombre: 'Jorge',     apellido: 'Salazar',    telefono: '+57 300 111 0010', email: 'jorge.salazar@mail.com',    estado: 'Lider',     fechaIngreso: '2017-08-09', liderNombre: null,            etapaActual: 'En Servicio Activo' },
  { miembroId: 11, nombre: 'Diana',     apellido: 'Patiño',     telefono: '+57 300 111 0011', email: 'diana.patino@mail.com',     estado: 'Activo',    fechaIngreso: '2023-10-05', liderNombre: 'Jorge Salazar', etapaActual: 'Encuentro con Dios' },
  { miembroId: 12, nombre: 'Felipe',    apellido: 'Ríos',       telefono: '+57 300 111 0012', email: 'felipe.rios@mail.com',      estado: 'Activo',    fechaIngreso: '2020-03-17', liderNombre: 'Carlos Ruiz',   etapaActual: 'Avivando el Fuego' },
  { miembroId: 13, nombre: 'Natalia',   apellido: 'Jiménez',    telefono: '+57 300 111 0013', email: 'natalia.jimenez@mail.com',  estado: 'Activo',    fechaIngreso: '2022-12-01', liderNombre: 'Laura Vargas',  etapaActual: 'Caminando en Santidad' },
  { miembroId: 14, nombre: 'Santiago',  apellido: 'Medina',     telefono: '+57 300 111 0014', email: 'santi.medina@mail.com',     estado: 'Inactivo',  fechaIngreso: '2019-04-28', liderNombre: 'Jorge Salazar', etapaActual: null },
  { miembroId: 15, nombre: 'Camila',    apellido: 'Ospina',     telefono: '+57 300 111 0015', email: 'camila.ospina@mail.com',    estado: 'Activo',    fechaIngreso: '2021-08-11', liderNombre: 'Jorge Salazar', etapaActual: 'Avivando el Fuego' },
];

const MOCK_DETALLE: MiembroDetalle[] = [
  {
    miembroId: 1, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Ana', apellido: 'Gómez', cedula: '1020304050',
    fechaNacimiento: '1995-07-14', telefono: '+57 300 111 0001', email: 'ana.gomez@mail.com',
    direccion: 'Cra 43A #18-100, El Poblado', estado: 'Activo', fechaIngreso: '2022-03-10',
    liderCelulaId: 2, liderNombre: 'Carlos Ruiz', liderTelefono: '+57 300 111 0002',
    redNombre: 'Red Los Renovados', notas: 'Sirve en equipo de alabanza los domingos.',
    etapaActualOrden: 3,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios',     fechaInicio: '2022-03-15', fechaFin: '2022-04-10', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir',     fechaInicio: '2022-04-15', fechaFin: '2022-06-20', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',      fechaInicio: '2022-07-01', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 1, eventoNombre: 'Retiro Mujer Virtuosa', fecha: '2023-03-05', inscrito: true,  asistio: true },
      { eventoId: 2, eventoNombre: 'Congreso Central 2023', fecha: '2023-09-15', inscrito: true,  asistio: true },
      { eventoId: 3, eventoNombre: 'Campaña Avivamiento',   fecha: '2024-01-20', inscrito: true,  asistio: false },
    ],
    ministerios: [
      { ministerioId: 1, ministerioNombre: 'Alabanza y Adoración', rol: 'Vocalista', color: '#7c3aed', activo: true },
      { ministerioId: 3, ministerioNombre: 'Bienvenida',           rol: 'Coordinadora', color: '#0284c7', activo: true },
    ],
  },
  {
    miembroId: 2, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Carlos', apellido: 'Ruiz', cedula: '70123456',
    fechaNacimiento: '1985-02-28', telefono: '+57 300 111 0002', email: 'carlos.ruiz@mail.com',
    direccion: 'Cll 10 #32-50, Laureles', estado: 'Lider', fechaIngreso: '2019-06-15',
    liderCelulaId: null, liderNombre: null, liderTelefono: null,
    redNombre: 'Red Los Renovados', notas: 'Líder de red. Dirige célula los martes.',
    etapaActualOrden: 5,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios',      fechaInicio: '2019-06-20', fechaFin: '2019-07-15', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir',      fechaInicio: '2019-08-01', fechaFin: '2019-10-30', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',       fechaInicio: '2019-11-01', fechaFin: '2020-02-28', completada: true },
      { etapaId: 4, etapaNombre: 'Caminando en Santidad',   fechaInicio: '2020-03-01', fechaFin: '2020-08-31', completada: true },
      { etapaId: 5, etapaNombre: 'En Servicio Activo',      fechaInicio: '2020-09-01', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 1, eventoNombre: 'Retiro Líderes 2022',   fecha: '2022-05-20', inscrito: true, asistio: true },
      { eventoId: 2, eventoNombre: 'Congreso Central 2023', fecha: '2023-09-15', inscrito: true, asistio: true },
      { eventoId: 3, eventoNombre: 'Campaña Avivamiento',   fecha: '2024-01-20', inscrito: true, asistio: true },
    ],
    ministerios: [
      { ministerioId: 2, ministerioNombre: 'Células',         rol: 'Líder de Célula', color: '#16a34a', activo: true },
      { ministerioId: 4, ministerioNombre: 'Discipulado',     rol: 'Instructor',      color: '#d97706', activo: true },
    ],
  },
  {
    miembroId: 3, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'María', apellido: 'López', cedula: '43210987',
    fechaNacimiento: '1993-11-05', telefono: '+57 300 111 0003', email: 'maria.lopez@mail.com',
    direccion: 'Av. El Robledo #120-45, Robledo', estado: 'Activo', fechaIngreso: '2021-01-20',
    liderCelulaId: 5, liderNombre: 'Laura Vargas', liderTelefono: '+57 300 111 0005',
    redNombre: 'Red Guerreros de Fe', notas: 'Próxima a comenzar liderazgo.',
    etapaActualOrden: 4,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios',    fechaInicio: '2021-01-25', fechaFin: '2021-02-28', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir',    fechaInicio: '2021-03-10', fechaFin: '2021-06-15', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',     fechaInicio: '2021-07-01', fechaFin: '2022-01-31', completada: true },
      { etapaId: 4, etapaNombre: 'Caminando en Santidad', fechaInicio: '2022-02-01', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 2, eventoNombre: 'Congreso Central 2023', fecha: '2023-09-15', inscrito: true,  asistio: true },
      { eventoId: 4, eventoNombre: 'Retiro Mujeres 2024',   fecha: '2024-03-08', inscrito: true,  asistio: true },
    ],
    ministerios: [
      { ministerioId: 1, ministerioNombre: 'Alabanza y Adoración', rol: 'Corista',     color: '#7c3aed', activo: true },
      { ministerioId: 5, ministerioNombre: 'Intercesión',          rol: 'Intercesora', color: '#dc2626', activo: true },
    ],
  },
  {
    miembroId: 4, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Luis', apellido: 'Martínez', cedula: '1015678901',
    fechaNacimiento: '1998-04-17', telefono: '+57 300 111 0004', email: 'luis.martinez@mail.com',
    direccion: 'Cll 50 #65-20, Belén', estado: 'Activo', fechaIngreso: '2020-09-05',
    liderCelulaId: 2, liderNombre: 'Carlos Ruiz', liderTelefono: '+57 300 111 0002',
    redNombre: 'Red Los Renovados', notas: 'Apoya en producción audiovisual.',
    etapaActualOrden: 2,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios',  fechaInicio: '2020-09-10', fechaFin: '2020-10-20', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir',  fechaInicio: '2020-11-01', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 3, eventoNombre: 'Campaña Avivamiento', fecha: '2024-01-20', inscrito: true, asistio: true },
    ],
    ministerios: [
      { ministerioId: 6, ministerioNombre: 'Medios Audiovisuales', rol: 'Técnico', color: '#0891b2', activo: true },
    ],
  },
  {
    miembroId: 5, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Laura', apellido: 'Vargas', cedula: '32765432',
    fechaNacimiento: '1983-09-22', telefono: '+57 300 111 0005', email: 'laura.vargas@mail.com',
    direccion: 'Cra 80 #33-10, Estadio', estado: 'Lider', fechaIngreso: '2018-11-30',
    liderCelulaId: null, liderNombre: null, liderTelefono: null,
    redNombre: 'Red Guerreros de Fe', notas: 'Líder de red. Enfoque en familias jóvenes.',
    etapaActualOrden: 5,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios',    fechaInicio: '2018-12-01', fechaFin: '2019-01-10', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir',    fechaInicio: '2019-01-20', fechaFin: '2019-04-30', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',     fechaInicio: '2019-05-01', fechaFin: '2019-09-30', completada: true },
      { etapaId: 4, etapaNombre: 'Caminando en Santidad', fechaInicio: '2019-10-01', fechaFin: '2020-03-31', completada: true },
      { etapaId: 5, etapaNombre: 'En Servicio Activo',    fechaInicio: '2020-04-01', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 1, eventoNombre: 'Retiro Líderes 2022',   fecha: '2022-05-20', inscrito: true, asistio: true },
      { eventoId: 2, eventoNombre: 'Congreso Central 2023', fecha: '2023-09-15', inscrito: true, asistio: true },
    ],
    ministerios: [
      { ministerioId: 2, ministerioNombre: 'Células',     rol: 'Líder de Célula', color: '#16a34a', activo: true },
      { ministerioId: 5, ministerioNombre: 'Intercesión', rol: 'Coordinadora',    color: '#dc2626', activo: true },
    ],
  },
  {
    miembroId: 6, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Pedro', apellido: 'Herrera', cedula: '1014233110',
    fechaNacimiento: '2000-12-03', telefono: '+57 300 111 0006', email: 'pedro.herrera@mail.com',
    direccion: 'Cra 65 #48-30, Floresta', estado: 'Activo', fechaIngreso: '2023-02-14',
    liderCelulaId: 5, liderNombre: 'Laura Vargas', liderTelefono: '+57 300 111 0005',
    redNombre: 'Red Guerreros de Fe', notas: 'Nuevo en la iglesia, muy comprometido.',
    etapaActualOrden: 2,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios', fechaInicio: '2023-02-20', fechaFin: '2023-03-30', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir', fechaInicio: '2023-04-10', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 4, eventoNombre: 'Retiro Mujeres 2024', fecha: '2024-03-08', inscrito: false, asistio: null },
    ],
    ministerios: [
      { ministerioId: 7, ministerioNombre: 'Ushers', rol: 'Acomodador', color: '#ca8a04', activo: true },
    ],
  },
  {
    miembroId: 7, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Sofía', apellido: 'Castro', cedula: null,
    fechaNacimiento: '2002-06-18', telefono: '+57 300 111 0007', email: 'sofia.castro@mail.com',
    direccion: null, estado: 'Visitante', fechaIngreso: '2024-04-01',
    liderCelulaId: null, liderNombre: null, liderTelefono: null,
    redNombre: null, notas: 'Visitante recurrente. Invitada por Ana Gómez.',
    etapaActualOrden: null,
    etapas: [],
    asistencias: [
      { eventoId: 3, eventoNombre: 'Campaña Avivamiento', fecha: '2024-01-20', inscrito: true, asistio: true },
    ],
    ministerios: [],
  },
  {
    miembroId: 8, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Andrés', apellido: 'Mora', cedula: '1018555432',
    fechaNacimiento: '1991-03-29', telefono: '+57 300 111 0008', email: 'andres.mora@mail.com',
    direccion: 'Cra 73 #52-18, Calasanz', estado: 'Activo', fechaIngreso: '2022-07-18',
    liderCelulaId: 2, liderNombre: 'Carlos Ruiz', liderTelefono: '+57 300 111 0002',
    redNombre: 'Red Los Renovados', notas: 'Toca guitarra en el equipo de adoración.',
    etapaActualOrden: 3,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios', fechaInicio: '2022-07-25', fechaFin: '2022-09-10', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir', fechaInicio: '2022-09-20', fechaFin: '2023-01-15', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',  fechaInicio: '2023-01-20', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 2, eventoNombre: 'Congreso Central 2023', fecha: '2023-09-15', inscrito: true, asistio: true },
      { eventoId: 3, eventoNombre: 'Campaña Avivamiento',   fecha: '2024-01-20', inscrito: true, asistio: true },
    ],
    ministerios: [
      { ministerioId: 1, ministerioNombre: 'Alabanza y Adoración', rol: 'Guitarrista', color: '#7c3aed', activo: true },
    ],
  },
  {
    miembroId: 9, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Valentina', apellido: 'Torres', cedula: '1012876543',
    fechaNacimiento: '1997-08-14', telefono: '+57 300 111 0009', email: 'valentina.torres@mail.com',
    direccion: 'Cll 22 #55-40, Buenos Aires', estado: 'Activo', fechaIngreso: '2021-05-22',
    liderCelulaId: 5, liderNombre: 'Laura Vargas', liderTelefono: '+57 300 111 0005',
    redNombre: 'Red Guerreros de Fe', notas: 'Interesada en iniciar ministerio de danza.',
    etapaActualOrden: 4,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios',    fechaInicio: '2021-05-28', fechaFin: '2021-07-10', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir',    fechaInicio: '2021-07-20', fechaFin: '2021-12-01', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',     fechaInicio: '2021-12-10', fechaFin: '2022-07-30', completada: true },
      { etapaId: 4, etapaNombre: 'Caminando en Santidad', fechaInicio: '2022-08-01', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 2, eventoNombre: 'Congreso Central 2023', fecha: '2023-09-15', inscrito: true,  asistio: true },
      { eventoId: 4, eventoNombre: 'Retiro Mujeres 2024',   fecha: '2024-03-08', inscrito: true,  asistio: true },
    ],
    ministerios: [
      { ministerioId: 5, ministerioNombre: 'Intercesión', rol: 'Intercesora', color: '#dc2626', activo: true },
    ],
  },
  {
    miembroId: 10, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Jorge', apellido: 'Salazar', cedula: '98765432',
    fechaNacimiento: '1980-01-11', telefono: '+57 300 111 0010', email: 'jorge.salazar@mail.com',
    direccion: 'Cll 77 #80-22, Bello', estado: 'Lider', fechaIngreso: '2017-08-09',
    liderCelulaId: null, liderNombre: null, liderTelefono: null,
    redNombre: 'Red Nueva Generación', notas: 'Líder de red. Especialidad en jóvenes.',
    etapaActualOrden: 5,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios',    fechaInicio: '2017-08-15', fechaFin: '2017-09-30', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir',    fechaInicio: '2017-10-10', fechaFin: '2018-02-28', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',     fechaInicio: '2018-03-01', fechaFin: '2018-08-31', completada: true },
      { etapaId: 4, etapaNombre: 'Caminando en Santidad', fechaInicio: '2018-09-01', fechaFin: '2019-04-30', completada: true },
      { etapaId: 5, etapaNombre: 'En Servicio Activo',    fechaInicio: '2019-05-01', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 1, eventoNombre: 'Retiro Líderes 2022',   fecha: '2022-05-20', inscrito: true, asistio: true },
      { eventoId: 2, eventoNombre: 'Congreso Central 2023', fecha: '2023-09-15', inscrito: true, asistio: true },
    ],
    ministerios: [
      { ministerioId: 2, ministerioNombre: 'Células',      rol: 'Líder de Célula',  color: '#16a34a', activo: true },
      { ministerioId: 8, ministerioNombre: 'Jóvenes',      rol: 'Coordinador',      color: '#f59e0b', activo: true },
    ],
  },
  {
    miembroId: 11, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Diana', apellido: 'Patiño', cedula: '1001234567',
    fechaNacimiento: '2001-09-30', telefono: '+57 300 111 0011', email: 'diana.patino@mail.com',
    direccion: 'Cll 107 #51-20, Aranjuez', estado: 'Activo', fechaIngreso: '2023-10-05',
    liderCelulaId: 10, liderNombre: 'Jorge Salazar', liderTelefono: '+57 300 111 0010',
    redNombre: 'Red Nueva Generación', notas: 'Universitaria, sirve en ministerio de jóvenes.',
    etapaActualOrden: 1,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios', fechaInicio: '2023-10-15', fechaFin: null, completada: false },
    ],
    asistencias: [
      { eventoId: 3, eventoNombre: 'Campaña Avivamiento', fecha: '2024-01-20', inscrito: true, asistio: true },
    ],
    ministerios: [
      { ministerioId: 8, ministerioNombre: 'Jóvenes', rol: 'Voluntaria', color: '#f59e0b', activo: true },
    ],
  },
  {
    miembroId: 12, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Felipe', apellido: 'Ríos', cedula: '1016789012',
    fechaNacimiento: '1990-05-07', telefono: '+57 300 111 0012', email: 'felipe.rios@mail.com',
    direccion: 'Cra 48 #26-40, La América', estado: 'Activo', fechaIngreso: '2020-03-17',
    liderCelulaId: 2, liderNombre: 'Carlos Ruiz', liderTelefono: '+57 300 111 0002',
    redNombre: 'Red Los Renovados', notas: 'Diseñador gráfico, apoya en comunicaciones.',
    etapaActualOrden: 3,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios', fechaInicio: '2020-03-22', fechaFin: '2020-05-10', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir', fechaInicio: '2020-05-20', fechaFin: '2020-10-31', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',  fechaInicio: '2020-11-10', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 2, eventoNombre: 'Congreso Central 2023', fecha: '2023-09-15', inscrito: true, asistio: false },
      { eventoId: 3, eventoNombre: 'Campaña Avivamiento',   fecha: '2024-01-20', inscrito: true, asistio: true },
    ],
    ministerios: [
      { ministerioId: 9, ministerioNombre: 'Comunicaciones', rol: 'Diseñador', color: '#0284c7', activo: true },
    ],
  },
  {
    miembroId: 13, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Natalia', apellido: 'Jiménez', cedula: '32456789',
    fechaNacimiento: '1988-12-19', telefono: '+57 300 111 0013', email: 'natalia.jimenez@mail.com',
    direccion: 'Cll 33 #85-55, Belén Rosales', estado: 'Activo', fechaIngreso: '2022-12-01',
    liderCelulaId: 5, liderNombre: 'Laura Vargas', liderTelefono: '+57 300 111 0005',
    redNombre: 'Red Guerreros de Fe', notas: 'Maestra, imparte clases bíblicas a niños.',
    etapaActualOrden: 4,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios',    fechaInicio: '2022-12-10', fechaFin: '2023-01-20', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir',    fechaInicio: '2023-02-01', fechaFin: '2023-05-30', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',     fechaInicio: '2023-06-01', fechaFin: '2023-11-30', completada: true },
      { etapaId: 4, etapaNombre: 'Caminando en Santidad', fechaInicio: '2023-12-01', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 4, eventoNombre: 'Retiro Mujeres 2024', fecha: '2024-03-08', inscrito: true, asistio: true },
    ],
    ministerios: [
      { ministerioId: 10, ministerioNombre: 'Escuela Dominical', rol: 'Maestra', color: '#059669', activo: true },
    ],
  },
  {
    miembroId: 14, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Santiago', apellido: 'Medina', cedula: '80123456',
    fechaNacimiento: '1986-07-04', telefono: '+57 300 111 0014', email: 'santi.medina@mail.com',
    direccion: 'Cra 33 #13-80, Guayabal', estado: 'Inactivo', fechaIngreso: '2019-04-28',
    liderCelulaId: 10, liderNombre: 'Jorge Salazar', liderTelefono: '+57 300 111 0010',
    redNombre: 'Red Nueva Generación', notas: 'Inactivo por motivos de salud. En seguimiento pastoral.',
    etapaActualOrden: null,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios', fechaInicio: '2019-05-01', fechaFin: '2019-06-10', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir', fechaInicio: '2019-06-20', fechaFin: '2019-10-30', completada: true },
    ],
    asistencias: [],
    ministerios: [],
  },
  {
    miembroId: 15, sedeId: 1, sedeNombre: 'Central Medellín',
    nombre: 'Camila', apellido: 'Ospina', cedula: '1019876543',
    fechaNacimiento: '1996-02-25', telefono: '+57 300 111 0015', email: 'camila.ospina@mail.com',
    direccion: 'Cll 57 #43-70, Manila', estado: 'Activo', fechaIngreso: '2021-08-11',
    liderCelulaId: 10, liderNombre: 'Jorge Salazar', liderTelefono: '+57 300 111 0010',
    redNombre: 'Red Nueva Generación', notas: 'Excelente actitud de servicio.',
    etapaActualOrden: 3,
    etapas: [
      { etapaId: 1, etapaNombre: 'Encuentro con Dios', fechaInicio: '2021-08-18', fechaFin: '2021-10-01', completada: true },
      { etapaId: 2, etapaNombre: 'Libres para Servir', fechaInicio: '2021-10-15', fechaFin: '2022-03-31', completada: true },
      { etapaId: 3, etapaNombre: 'Avivando el Fuego',  fechaInicio: '2022-04-10', fechaFin: null,         completada: false },
    ],
    asistencias: [
      { eventoId: 2, eventoNombre: 'Congreso Central 2023', fecha: '2023-09-15', inscrito: true, asistio: true },
      { eventoId: 3, eventoNombre: 'Campaña Avivamiento',   fecha: '2024-01-20', inscrito: true, asistio: true },
    ],
    ministerios: [
      { ministerioId: 3, ministerioNombre: 'Bienvenida',           rol: 'Anfitriona', color: '#0284c7', activo: true },
      { ministerioId: 8, ministerioNombre: 'Jóvenes',              rol: 'Voluntaria',  color: '#f59e0b', activo: true },
    ],
  },
];

@Injectable({ providedIn: 'root' })
export class MiembrosService {
  private http = inject(HttpClient);
  private base = `${environment.apiUrl}/miembros`;

  getMock(): Observable<MiembroList[]> {
    return of(MOCK_MIEMBROS).pipe(delay(200));
  }

  getMockBySede(sedeId: number): Observable<MiembroList[]> {
    const lista = sedeId === 1 ? MOCK_MIEMBROS : [];
    return of(lista).pipe(delay(200));
  }

  getMockById(id: number): Observable<MiembroDetalle> {
    const detalle = MOCK_DETALLE.find(d => d.miembroId === id) ?? MOCK_DETALLE[0];
    return of(detalle).pipe(delay(300));
  }

  getAll(sedeId: number, busqueda?: string) {
    let params = new HttpParams().set('sedeId', sedeId);
    if (busqueda) params = params.set('busqueda', busqueda);
    return this.http.get<MiembroList[]>(this.base, { params });
  }

  getById(id: number) {
    return this.http.get<MiembroDetalle>(`${this.base}/${id}`);
  }

  create(data: CrearMiembroRequest) {
    return this.http.post<MiembroDetalle>(this.base, data);
  }

  update(id: number, data: CrearMiembroRequest) {
    return this.http.put<MiembroDetalle>(`${this.base}/${id}`, data);
  }

  delete(id: number) {
    return this.http.delete(`${this.base}/${id}`);
  }
}
