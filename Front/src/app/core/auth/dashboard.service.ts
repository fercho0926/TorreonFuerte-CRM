import { Injectable, inject } from '@angular/core';
import { forkJoin, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { DashboardData } from '../models/dashboard.models';
import { MiembrosService } from './miembros.service';
import { SedesService } from './sedes.service';
import { EventosService } from './eventos.service';
import { MinisteriosService } from './ministerios.service';
import { EtapasService } from './etapas.service';
import { EVENTO_TIPO_META } from '../models/evento.models';
import { MINISTERIO_TIPO_META } from '../models/ministerio.models';

@Injectable({ providedIn: 'root' })
export class DashboardService {
  private miembros    = inject(MiembrosService);
  private sedes       = inject(SedesService);
  private eventos     = inject(EventosService);
  private ministerios = inject(MinisteriosService);
  private etapas      = inject(EtapasService);

  get(sedeId: number): Observable<DashboardData> {
    return forkJoin({
      miembros:    this.miembros.getMock(),
      sedes:       this.sedes.getAll(),
      eventos:     this.eventos.getAll(sedeId),
      ministerios: this.ministerios.getAll(sedeId),
      etapas:      this.etapas.getEtapas(sedeId),
    }).pipe(map(({ miembros, sedes, eventos, ministerios, etapas }) => {
      const activos    = miembros.filter(m => m.estado === 'Activo').length;
      const visitantes = miembros.filter(m => m.estado === 'Visitante').length;
      const inactivos  = miembros.filter(m => m.estado === 'Inactivo').length;
      const lideres    = miembros.filter(m => m.estado === 'Lider').length;

      const miembrosPorEtapa = etapas.map(e => ({
        etapa: e.nombre,
        conteo: e.totalMiembros,
        color: e.color,
      }));

      const now = new Date().toISOString().substring(0, 10);
      const proximosEventos = eventos
        .filter(e => (e.estado === 'Proximo' || e.estado === 'EnCurso') && e.fecha >= now)
        .sort((a, b) => a.fecha.localeCompare(b.fecha))
        .slice(0, 4)
        .map(e => {
          const meta = EVENTO_TIPO_META[e.tipo];
          return {
            eventoId: e.eventoId,
            nombre: e.nombre,
            fecha: e.fecha,
            tipo: e.tipo,
            color: meta.color,
            icono: meta.icono,
            totalInscritos: e.totalInscritos,
          };
        });

      const ministeriosResumen = ministerios.map(m => {
        const meta = MINISTERIO_TIPO_META[m.tipo];
        return {
          ministerioId: m.ministerioId,
          nombre: m.nombre,
          tipo: m.tipo,
          icono: meta.icono,
          color: meta.color,
          colorClaro: meta.colorClaro,
          totalIntegrantes: m.totalIntegrantes,
          liderNombre: m.liderNombre,
        };
      });

      const totalCelulas = sedes.reduce((s, x) => s + x.totalCelulas, 0);

      return {
        totalMiembros: miembros.length,
        totalActivos: activos,
        totalVisitantes: visitantes,
        totalInactivos: inactivos,
        totalLideres: lideres,
        totalCelulas,
        totalSedes: sedes.length,
        totalMinisterios: ministerios.length,
        miembrosPorEtapa,
        proximosEventos,
        ministeriosResumen,
      };
    }));
  }
}
