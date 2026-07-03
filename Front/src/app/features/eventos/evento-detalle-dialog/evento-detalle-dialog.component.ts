import { Component, inject, signal, OnInit, computed } from '@angular/core';
import { NgClass, DatePipe, PercentPipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DynamicDialogRef, DynamicDialogConfig } from 'primeng/dynamicdialog';
import { ButtonModule } from 'primeng/button';
import { ProgressBarModule } from 'primeng/progressbar';
import { ToggleSwitchModule } from 'primeng/toggleswitch';
import { ProgressSpinnerModule } from 'primeng/progressspinner';
import { DividerModule } from 'primeng/divider';
import { TooltipModule } from 'primeng/tooltip';
import { EventosService } from '../../../core/auth/eventos.service';
import { Evento, EventoInscrito, EVENTO_TIPO_META } from '../../../core/models/evento.models';

export interface EventoDetalleData { evento: Evento; }

@Component({
  selector: 'app-evento-detalle-dialog',
  standalone: true,
  imports: [
    NgClass, DatePipe, PercentPipe, FormsModule,
    ButtonModule, ProgressBarModule, ToggleSwitchModule,
    ProgressSpinnerModule, DividerModule, TooltipModule,
  ],
  template: `
    <!-- Header con color del tipo -->
    <div class="det-header" [style.background]="evento.color">
      <div class="det-header-top">
        <i class="pi det-tipo-icon" [ngClass]="tipoMeta.icono"></i>
        <div class="det-header-info">
          <span class="det-tipo-badge">{{ tipoMeta.label }}</span>
          <h2>{{ evento.nombre }}</h2>
        </div>
        <button pButton [text]="true" [rounded]="true" class="det-close" (click)="ref.close()"><i class="pi pi-times"></i></button>
      </div>

      <!-- Chips de info rápida -->
      <div class="det-chips">
        <span class="dchip"><i class="pi pi-calendar"></i>
          {{ evento.fecha | date:'EEE dd MMM':'':'es' }}
          @if (evento.fechaFin) { — {{ evento.fechaFin | date:'dd MMM':'':'es' }} }
        </span>
        <span class="dchip"><i class="pi pi-clock"></i>{{ evento.horaInicio }} – {{ evento.horaFin }}</span>
        <span class="dchip"><i class="pi pi-map-marker"></i>{{ evento.lugar }}</span>
        <span class="dchip"><i class="pi pi-user"></i>{{ evento.responsable }}</span>
      </div>
    </div>

    <div class="dialog-content det-body">

      <!-- Descripción -->
      <p class="det-desc">{{ evento.descripcion }}</p>

      <p-divider />

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
          <p-progressBar [value]="(evento.totalInscritos / evento.maxInscritos) * 100"
            [style]="{ height: '6px' }" [showValue]="false" />
        </div>
      }

      <p-divider />

      <!-- Lista de inscritos -->
      <div class="inscritos-section">
        <div class="inscritos-header">
          <h3>
            <i class="pi pi-users" [style.color]="evento.color"></i>
            Lista de inscritos
          </h3>
          @if (evento.estado === 'Finalizado' || evento.estado === 'EnCurso') {
            <span class="asistencia-hint">
              <i class="pi pi-info-circle"></i> Registra la asistencia
            </span>
          }
        </div>

        @if (cargandoInscritos()) {
          <div class="loading-row"><p-progressSpinner [style]="{ width: '32px', height: '32px' }" /></div>
        } @else if (inscritos().length === 0) {
          <div class="empty-inscritos">
            <i class="pi pi-search"></i><p>Aún no hay inscritos.</p>
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
                  <div class="asistencia-toggle-wrap"
                       [pTooltip]="m.asistio === true ? 'Asistió' : m.asistio === false ? 'No asistió' : 'Sin registrar'">
                    <p-toggleswitch
                      [ngModel]="m.asistio === true"
                      (onChange)="toggleAsistencia(m, $event.checked)" />
                  </div>
                } @else {
                  <span class="inscrito-fecha">{{ m.inscritoEn | date:'dd/MM':'':'es' }}</span>
                }

              </div>
            }
          </div>

          <!-- Resumen de asistencia -->
          @if (evento.estado === 'Finalizado' || evento.estado === 'EnCurso') {
            <div class="asistencia-resumen" [style.background]="evento.colorClaro">
              <i class="pi pi-check-circle" [style.color]="evento.color"></i>
              <span>
                <strong [style.color]="evento.color">{{ conteoAsistieron() }}</strong>
                de {{ inscritos().length }} asistieron
                ({{ tasaAsistenciaLocal() | percent }})
              </span>
            </div>
          }
        }
      </div>

    </div>

    <div class="dialog-actions">
      <button pButton [text]="true" (click)="ref.close()">Cerrar</button>
    </div>
  `,
  styles: [`
    /* Header */
    .det-header { padding: 20px 20px 16px; color: white; border-radius: 0; }
    .det-header-top { display:flex; align-items:flex-start; gap:12px; margin-bottom:12px; }
    .det-tipo-icon { font-size:32px; opacity:.9; flex-shrink:0; margin-top:2px; }
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
    .dchip i { font-size:14px; }

    /* Body */
    .dialog-content.det-body { padding: 16px 20px !important; min-width: min(540px, 92vw); max-height: 65vh; overflow-y: auto; }
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
    .inscritos-header h3 i { font-size:18px; }
    .asistencia-hint { display:flex; align-items:center; gap:4px; font-size:12px; color:#94a3b8; }
    .asistencia-hint i { font-size:14px; }

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

    .dialog-actions { display:flex; justify-content:flex-end; gap:8px; padding:12px 20px; }
  `],
})
export class EventoDetalleDialogComponent implements OnInit {
  private svc = inject(EventosService);
  readonly ref    = inject(DynamicDialogRef);
  readonly config = inject(DynamicDialogConfig<EventoDetalleData>);
  readonly data: EventoDetalleData = this.config.data;

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
