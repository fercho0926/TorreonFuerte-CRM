import { Component, inject, signal, OnInit, computed } from '@angular/core';
import { NgClass, DatePipe, PercentPipe } from '@angular/common';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatDividerModule } from '@angular/material/divider';
import { MatChipsModule } from '@angular/material/chips';
import { MatTooltipModule } from '@angular/material/tooltip';
import { EventosService } from '../../../core/auth/eventos.service';
import { Evento, EventoInscrito, EVENTO_TIPO_META } from '../../../core/models/evento.models';

export interface EventoDetalleData { evento: Evento; }

@Component({
  selector: 'app-evento-detalle-dialog',
  standalone: true,
  imports: [
    NgClass, DatePipe, PercentPipe,
    MatDialogModule, MatButtonModule, MatIconModule,
    MatProgressBarModule, MatSlideToggleModule,
    MatProgressSpinnerModule, MatDividerModule,
    MatChipsModule, MatTooltipModule,
  ],
  template: `
    <!-- Header con color del tipo -->
    <div class="det-header" [style.background]="evento.color">
      <div class="det-header-top">
        <mat-icon class="det-tipo-icon">{{ tipoMeta.icono }}</mat-icon>
        <div class="det-header-info">
          <span class="det-tipo-badge">{{ tipoMeta.label }}</span>
          <h2>{{ evento.nombre }}</h2>
        </div>
        <button mat-icon-button class="det-close" mat-dialog-close>
          <mat-icon>close</mat-icon>
        </button>
      </div>

      <!-- Chips de info rápida -->
      <div class="det-chips">
        <span class="dchip"><mat-icon>calendar_today</mat-icon>
          {{ evento.fecha | date:'EEE dd MMM':'':'es' }}
          @if (evento.fechaFin) { — {{ evento.fechaFin | date:'dd MMM':'':'es' }} }
        </span>
        <span class="dchip"><mat-icon>schedule</mat-icon>{{ evento.horaInicio }} – {{ evento.horaFin }}</span>
        <span class="dchip"><mat-icon>place</mat-icon>{{ evento.lugar }}</span>
        <span class="dchip"><mat-icon>person</mat-icon>{{ evento.responsable }}</span>
      </div>
    </div>

    <mat-dialog-content class="det-body">

      <!-- Descripción -->
      <p class="det-desc">{{ evento.descripcion }}</p>

      <mat-divider />

      <!-- Stats row -->
      <div class="det-stats">
        <div class="dstat">
          <span class="dstat-n" [style.color]="evento.color">{{ evento.totalInscritos }}</span>
          <span class="dstat-l">Inscritos</span>
        </div>
        @if (evento.maxInscritos) {
          <div class="dstat">
            <span class="dstat-n">{{ evento.maxInscritos }}</span>
            <span class="dstat-l">Cupo máximo</span>
          </div>
        }
        @if (evento.estado === 'Finalizado') {
          <div class="dstat">
            <span class="dstat-n" [style.color]="evento.color">{{ evento.totalAsistentes }}</span>
            <span class="dstat-l">Asistentes</span>
          </div>
          <div class="dstat">
            <span class="dstat-n" [style.color]="tasaColor()">{{ tasaAsistencia() | percent }}</span>
            <span class="dstat-l">Asistencia</span>
          </div>
        }
      </div>

      <!-- Barra de inscripción -->
      @if (evento.maxInscritos) {
        <div class="progress-wrap">
          <div class="progress-labels">
            <span>Inscripciones</span>
            <span>{{ evento.totalInscritos }} / {{ evento.maxInscritos }}</span>
          </div>
          <mat-progress-bar mode="determinate"
            [value]="(evento.totalInscritos / evento.maxInscritos) * 100"
            [color]="evento.totalInscritos >= evento.maxInscritos ? 'warn' : 'primary'" />
        </div>
      }

      <mat-divider />

      <!-- Lista de inscritos -->
      <div class="inscritos-section">
        <div class="inscritos-header">
          <h3>
            <mat-icon [style.color]="evento.color">people</mat-icon>
            Lista de inscritos
          </h3>
          @if (evento.estado === 'Finalizado' || evento.estado === 'EnCurso') {
            <span class="asistencia-hint">
              <mat-icon>touch_app</mat-icon> Registra la asistencia
            </span>
          }
        </div>

        @if (cargandoInscritos()) {
          <div class="loading-row"><mat-spinner diameter="32" /></div>
        } @else if (inscritos().length === 0) {
          <div class="empty-inscritos">
            <mat-icon>person_search</mat-icon><p>Aún no hay inscritos.</p>
          </div>
        } @else {
          <div class="inscritos-list">
            @for (m of inscritos(); track m.inscripcionId) {
              <div class="inscrito-row" [ngClass]="{ 'row-asistio': m.asistio === true, 'row-falto': m.asistio === false }">

                <div class="inscrito-avatar" [style.background]="evento.colorClaro" [style.color]="evento.color">
                  {{ m.nombre[0] }}{{ m.apellido[0] }}
                </div>

                <div class="inscrito-info">
                  <span class="inscrito-nombre">{{ m.nombre }} {{ m.apellido }}</span>
                  @if (m.liderNombre) {
                    <span class="inscrito-lider">{{ m.liderNombre }}</span>
                  }
                </div>

                <!-- Asistencia toggle (solo para eventos activos o finalizados) -->
                @if (evento.estado === 'Finalizado' || evento.estado === 'EnCurso') {
                  <mat-slide-toggle
                    [checked]="m.asistio === true"
                    [color]="'primary'"
                    [matTooltip]="m.asistio === true ? 'Asistió' : m.asistio === false ? 'No asistió' : 'Sin registrar'"
                    (change)="toggleAsistencia(m, $event.checked)">
                  </mat-slide-toggle>
                } @else {
                  <span class="inscrito-fecha">{{ m.inscritoEn | date:'dd/MM':'':'es' }}</span>
                }

              </div>
            }
          </div>

          <!-- Resumen de asistencia -->
          @if (evento.estado === 'Finalizado' || evento.estado === 'EnCurso') {
            <div class="asistencia-resumen" [style.background]="evento.colorClaro">
              <mat-icon [style.color]="evento.color">check_circle</mat-icon>
              <span>
                <strong [style.color]="evento.color">{{ conteoAsistieron() }}</strong>
                de {{ inscritos().length }} asistieron
                ({{ tasaAsistenciaLocal() | percent }})
              </span>
            </div>
          }
        }
      </div>

    </mat-dialog-content>

    <mat-dialog-actions align="end">
      <button mat-button mat-dialog-close>Cerrar</button>
    </mat-dialog-actions>
  `,
  styles: [`
    /* Header */
    .det-header { padding: 20px 20px 16px; color: white; border-radius: 0; }
    .det-header-top { display:flex; align-items:flex-start; gap:12px; margin-bottom:12px; }
    .det-tipo-icon { font-size:32px; width:32px; height:32px; opacity:.9; flex-shrink:0; margin-top:2px; }
    .det-header-info { flex:1; }
    .det-tipo-badge {
      display:inline-block; font-size:10px; font-weight:700; letter-spacing:.6px;
      text-transform:uppercase; background:rgba(255,255,255,.25);
      padding:2px 8px; border-radius:20px; margin-bottom:4px;
    }
    .det-header-info h2 { margin:0; font-size:18px; font-weight:700; line-height:1.3; }
    .det-close { color:rgba(255,255,255,.8) !important; margin-top:-4px; }
    .det-chips { display:flex; flex-wrap:wrap; gap:8px; }
    .dchip {
      display:inline-flex; align-items:center; gap:4px;
      font-size:12px; background:rgba(255,255,255,.2);
      padding:3px 10px; border-radius:20px;
    }
    .dchip mat-icon { font-size:14px!important; width:14px!important; height:14px!important; }

    /* Body */
    mat-dialog-content { padding: 16px 20px !important; min-width: min(540px, 92vw); max-height: 65vh !important; }
    .det-desc { margin:0 0 16px; font-size:13px; color:#475569; line-height:1.6; }

    /* Stats */
    .det-stats { display:flex; gap:24px; padding:16px 0; flex-wrap:wrap; }
    .dstat { text-align:center; }
    .dstat-n { display:block; font-size:24px; font-weight:700; color:#0f172a; line-height:1; }
    .dstat-l { font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:.4px; }

    /* Progress */
    .progress-wrap { margin-bottom:16px; }
    .progress-labels { display:flex; justify-content:space-between; font-size:12px; color:#64748b; margin-bottom:4px; }

    /* Inscritos */
    .inscritos-section { padding-top:16px; }
    .inscritos-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:8px; }
    .inscritos-header h3 { margin:0; font-size:14px; font-weight:600; display:flex; align-items:center; gap:6px; }
    .inscritos-header h3 mat-icon { font-size:18px!important; width:18px!important; height:18px!important; }
    .asistencia-hint { display:flex; align-items:center; gap:4px; font-size:12px; color:#94a3b8; }
    .asistencia-hint mat-icon { font-size:14px!important; width:14px!important; height:14px!important; }

    .loading-row { display:flex; justify-content:center; padding:24px; }
    .empty-inscritos { display:flex; align-items:center; gap:8px; color:#94a3b8; padding:24px; justify-content:center; flex-direction:column; }

    .inscritos-list { display:flex; flex-direction:column; gap:4px; }
    .inscrito-row {
      display:flex; align-items:center; gap:10px;
      padding:8px 10px; border-radius:8px; background:#f8fafc;
      transition: background .15s;
    }
    .inscrito-row:hover  { background:#f1f5f9; }
    .row-asistio         { background:#f0fdf4 !important; }
    .row-falto           { background:#fef2f2 !important; opacity:.7; }

    .inscrito-avatar {
      width:34px; height:34px; border-radius:50%;
      display:flex; align-items:center; justify-content:center;
      font-size:12px; font-weight:700; flex-shrink:0;
    }
    .inscrito-info { flex:1; min-width:0; }
    .inscrito-nombre { display:block; font-size:13px; font-weight:600; color:#1e293b; }
    .inscrito-lider  { display:block; font-size:11px; color:#94a3b8; }
    .inscrito-fecha  { font-size:11px; color:#94a3b8; flex-shrink:0; }

    .asistencia-resumen {
      display:flex; align-items:center; gap:8px;
      margin-top:12px; border-radius:8px; padding:10px 14px;
      font-size:13px; color:#475569;
    }

    mat-dialog-actions { gap:8px; padding:12px 20px !important; }
  `],
})
export class EventoDetalleDialogComponent implements OnInit {
  private svc = inject(EventosService);
  readonly dialogRef = inject(MatDialogRef<EventoDetalleDialogComponent>);
  readonly data: EventoDetalleData = inject(MAT_DIALOG_DATA);

  evento = this.data.evento;
  tipoMeta = EVENTO_TIPO_META[this.evento.tipo];

  inscritos         = signal<EventoInscrito[]>([]);
  cargandoInscritos = signal(true);

  conteoAsistieron = computed(() => this.inscritos().filter(m => m.asistio === true).length);

  tasaAsistencia = () =>
    this.evento.totalInscritos > 0
      ? (this.evento.totalAsistentes ?? 0) / this.evento.totalInscritos
      : 0;

  tasaAsistenciaLocal = computed(() =>
    this.inscritos().length > 0
      ? this.conteoAsistieron() / this.inscritos().length
      : 0
  );

  tasaColor = () => {
    const t = this.tasaAsistencia();
    if (t >= 0.9) return '#16a34a';
    if (t >= 0.7) return '#d97706';
    return '#dc2626';
  };

  ngOnInit() {
    this.svc.getInscritos(this.evento.eventoId).subscribe(i => {
      this.inscritos.set(i);
      this.cargandoInscritos.set(false);
    });
  }

  toggleAsistencia(m: EventoInscrito, asistio: boolean) {
    const prev = m.asistio;
    m.asistio = asistio;
    this.inscritos.update(list => [...list]);
    this.svc.registrarAsistencia(this.evento.eventoId, m.miembroId, asistio).subscribe({
      error: () => { m.asistio = prev; this.inscritos.update(l => [...l]); },
    });
  }
}
