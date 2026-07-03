import { Component, inject, signal, OnInit } from '@angular/core';
import { NgClass, DatePipe } from '@angular/common';
import { DynamicDialogRef, DynamicDialogConfig } from 'primeng/dynamicdialog';
import { TabsModule } from 'primeng/tabs';
import { ButtonModule } from 'primeng/button';
import { ProgressSpinnerModule } from 'primeng/progressspinner';
import { MiembrosService } from '../../../core/auth/miembros.service';
import { MiembroList } from '../../../core/models/miembro.models';
import { Sede } from '../../../core/models/sede.models';

export interface SedeDetalleData { sede: Sede; }

interface CelulaSimulada {
  celulaId: number;
  nombre: string;
  lider: string;
  miembros: number;
  color: string;
}

@Component({
  selector: 'app-sede-detalle-dialog',
  standalone: true,
  imports: [
    NgClass, DatePipe,
    TabsModule, ButtonModule, ProgressSpinnerModule,
  ],
  template: `
    <!-- Header colorido -->
    <div class="det-header" [style.background]="sede.color">
      <div class="det-header-top">
        <span class="det-bandera">{{ sede.bandera }}</span>
        <div class="det-header-info">
          <span class="det-pais">{{ sede.ciudad }}, {{ sede.pais }}</span>
          <h2>{{ sede.nombre }}</h2>
        </div>
        <button pButton [text]="true" [rounded]="true" class="det-close" (click)="ref.close()"><i class="pi pi-times"></i></button>
      </div>
      <!-- KPIs rápidos -->
      <div class="det-kpis">
        <div class="det-kpi">
          <span class="det-kpi-n">{{ sede.totalMiembros }}</span>
          <span class="det-kpi-l">Miembros</span>
        </div>
        <div class="det-kpi">
          <span class="det-kpi-n">{{ sede.totalCelulas }}</span>
          <span class="det-kpi-l">Células</span>
        </div>
        <div class="det-kpi">
          <span class="det-kpi-n">{{ sede.totalLideres }}</span>
          <span class="det-kpi-l">Líderes</span>
        </div>
        <div class="det-kpi">
          <span class="det-kpi-n">{{ aniosFundacion() }}</span>
          <span class="det-kpi-l">Años</span>
        </div>
      </div>
    </div>

    <div class="dialog-content det-body">
      <p-tabs value="0">
        <p-tablist>
          <p-tab value="0"><i class="pi pi-info-circle tab-icon"></i> Información</p-tab>
          <p-tab value="1"><i class="pi pi-users tab-icon"></i> Miembros ({{ miembros().length }})</p-tab>
          <p-tab value="2"><i class="pi pi-sitemap tab-icon"></i> Células ({{ celulas().length }})</p-tab>
        </p-tablist>
        <p-tabpanels>

          <!-- ═══ TAB INFO ═══ -->
          <p-tabpanel value="0">
            <div class="info-grid">

              <div class="info-item">
                <i class="pi pi-building" [style.color]="sede.color"></i>
                <div>
                  <span class="info-label">Pastor(es)</span>
                  <span class="info-value">{{ sede.pastor }}</span>
                </div>
              </div>

              <div class="info-item">
                <i class="pi pi-map-marker" [style.color]="sede.color"></i>
                <div>
                  <span class="info-label">Dirección</span>
                  <span class="info-value">{{ sede.direccion }}</span>
                </div>
              </div>

              <div class="info-item">
                <i class="pi pi-map-marker" [style.color]="sede.color"></i>
                <div>
                  <span class="info-label">Ciudad / Departamento</span>
                  <span class="info-value">{{ sede.ciudad }}, {{ sede.departamento }}</span>
                </div>
              </div>

              <div class="info-item">
                <i class="pi pi-phone" [style.color]="sede.color"></i>
                <div>
                  <span class="info-label">Teléfono</span>
                  <span class="info-value">{{ sede.telefono }}</span>
                </div>
              </div>

              <div class="info-item">
                <i class="pi pi-envelope" [style.color]="sede.color"></i>
                <div>
                  <span class="info-label">Email</span>
                  <span class="info-value">{{ sede.email }}</span>
                </div>
              </div>

              <div class="info-item">
                <i class="pi pi-calendar" [style.color]="sede.color"></i>
                <div>
                  <span class="info-label">Fecha de fundación</span>
                  <span class="info-value">{{ sede.fechaFundacion | date:"d 'de' MMMM yyyy":'':'es' }}</span>
                </div>
              </div>

              <div class="info-item">
                <i class="pi pi-map" [style.color]="sede.color"></i>
                <div>
                  <span class="info-label">Coordenadas</span>
                  <span class="info-value">{{ sede.lat }}, {{ sede.lng }}</span>
                </div>
              </div>

            </div>
          </p-tabpanel>

          <!-- ═══ TAB MIEMBROS ═══ -->
          <p-tabpanel value="1">
            @if (cargandoMiembros()) {
              <div class="loading-row"><p-progressSpinner [style]="{ width: '36px', height: '36px' }" /></div>
            } @else if (miembros().length === 0) {
              <div class="empty-tab">
                <i class="pi pi-search"></i>
                <p>Sin miembros registrados en esta sede.</p>
              </div>
            } @else {
              <div class="estado-resumen">
                @for (grupo of estadoResumen(); track grupo.label) {
                  <div class="estado-pill" [style.background]="grupo.bg" [style.color]="grupo.color">
                    <strong>{{ grupo.count }}</strong> {{ grupo.label }}
                  </div>
                }
              </div>
              <div class="miembros-list">
                @for (m of miembros(); track m.miembroId) {
                  <div class="miembro-row">
                    <div class="m-avatar" [style.background]="sede.color + '22'" [style.color]="sede.color">
                      {{ m.nombre[0] }}{{ m.apellido[0] }}
                    </div>
                    <div class="m-info">
                      <span class="m-nombre">{{ m.nombre }} {{ m.apellido }}</span>
                      <span class="m-sub">
                        @if (m.liderNombre) { {{ m.liderNombre }} · }{{ m.etapaActual ?? 'Sin etapa' }}
                      </span>
                    </div>
                    <span class="estado-badge" [ngClass]="'est-' + m.estado.toLowerCase()">{{ m.estado }}</span>
                  </div>
                }
              </div>
            }
          </p-tabpanel>

          <!-- ═══ TAB CÉLULAS ═══ -->
          <p-tabpanel value="2">
            @if (celulas().length === 0) {
              <div class="empty-tab">
                <i class="pi pi-sitemap"></i>
                <p>Sin células registradas en esta sede.</p>
              </div>
            } @else {
              <div class="celulas-grid">
                @for (c of celulas(); track c.celulaId) {
                  <div class="celula-card" [style.border-left-color]="c.color">
                    <div class="cel-icon" [style.background]="c.color + '22'" [style.color]="c.color">
                      <i class="pi pi-users"></i>
                    </div>
                    <div class="cel-info">
                      <span class="cel-nombre">{{ c.nombre }}</span>
                      <span class="cel-lider">
                        <i class="pi pi-user"></i>{{ c.lider }}
                      </span>
                    </div>
                    <div class="cel-count">
                      <span class="cel-num" [style.color]="c.color">{{ c.miembros }}</span>
                      <span class="cel-lbl">miembros</span>
                    </div>
                  </div>
                }
              </div>
            }
          </p-tabpanel>

        </p-tabpanels>
      </p-tabs>
    </div>

    <div class="dialog-actions">
      <button pButton [text]="true" (click)="ref.close()">Cerrar</button>
    </div>
  `,
  styles: [`
    /* Header */
    .det-header { padding: 20px 20px 16px; color: white; }
    .det-header-top { display:flex; align-items:flex-start; gap:12px; margin-bottom:14px; }
    .det-bandera { font-size: 36px; line-height: 1; flex-shrink: 0; }
    .det-header-info { flex: 1; }
    .det-pais { display:block; font-size:12px; opacity:.75; margin-bottom:2px; }
    .det-header-info h2 { margin:0; font-size:20px; font-weight:700; }
    .det-close { color:rgba(255,255,255,.8) !important; }
    .det-kpis { display:flex; gap:24px; }
    .det-kpi { text-align:center; }
    .det-kpi-n { display:block; font-size:22px; font-weight:700; line-height:1; }
    .det-kpi-l { font-size:11px; opacity:.75; text-transform:uppercase; letter-spacing:.4px; }

    /* Body */
    .dialog-content { padding:0 !important; min-width:min(560px, 94vw); max-height:65vh; overflow-y:auto; }
    .tab-icon { font-size:16px!important; margin-right:4px; vertical-align:middle; }

    /* Info grid */
    .info-grid { display:flex; flex-direction:column; gap:2px; padding:12px 16px; }
    .info-item { display:flex; align-items:flex-start; gap:10px; padding:10px 0; border-bottom:1px solid #f1f5f9; }
    .info-item:last-child { border-bottom:none; }
    .info-item i { font-size:20px!important; flex-shrink:0; margin-top:2px; }
    .info-label { display:block; font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
    .info-value { font-size:13px; font-weight:600; color:#1e293b; }

    /* Miembros */
    .loading-row { display:flex; justify-content:center; padding:32px; }
    .empty-tab { display:flex; flex-direction:column; align-items:center; gap:8px; padding:40px; color:#94a3b8; font-size:13px; }
    .empty-tab i { font-size:40px!important; }

    .estado-resumen { display:flex; gap:8px; flex-wrap:wrap; padding:12px 16px 4px; }
    .estado-pill { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; }

    .miembros-list { display:flex; flex-direction:column; gap:2px; padding:8px 16px 16px; }
    .miembro-row { display:flex; align-items:center; gap:10px; padding:8px; border-radius:8px; transition:background .12s; }
    .miembro-row:hover { background:#f8fafc; }
    .m-avatar { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; }
    .m-info { flex:1; min-width:0; }
    .m-nombre { display:block; font-size:13px; font-weight:600; color:#1e293b; }
    .m-sub { font-size:11px; color:#94a3b8; }
    .estado-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; flex-shrink:0; }
    .est-activo    { background:#dcfce7; color:#15803d; }
    .est-lider     { background:#ede9fe; color:#7c3aed; }
    .est-visitante { background:#fef3c7; color:#92400e; }
    .est-inactivo  { background:#f1f5f9; color:#475569; }

    /* Células */
    .celulas-grid { display:flex; flex-direction:column; gap:8px; padding:12px 16px; }
    .celula-card {
      display:flex; align-items:center; gap:12px;
      background:#f8fafc; border-radius:10px;
      padding:12px 14px; border-left:4px solid transparent;
    }
    .cel-icon { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .cel-icon i { font-size:18px!important; }
    .cel-info { flex:1; min-width:0; }
    .cel-nombre { display:block; font-size:13px; font-weight:600; color:#1e293b; }
    .cel-lider { display:flex; align-items:center; gap:3px; font-size:11px; color:#94a3b8; }
    .cel-lider i { font-size:13px!important; }
    .cel-count { text-align:center; flex-shrink:0; }
    .cel-num { display:block; font-size:20px; font-weight:700; line-height:1; }
    .cel-lbl { font-size:10px; color:#94a3b8; text-transform:uppercase; }

    .dialog-actions { display:flex; justify-content:flex-end; padding:12px 20px !important; }
  `],
})
export class SedeDetalleDialogComponent implements OnInit {
  private miembroSvc = inject(MiembrosService);
  readonly ref = inject(DynamicDialogRef);
  readonly config = inject(DynamicDialogConfig<SedeDetalleData>);
  readonly data: SedeDetalleData = this.config.data;

  sede = this.data.sede;

  miembros         = signal<MiembroList[]>([]);
  cargandoMiembros = signal(true);

  celulas = signal<CelulaSimulada[]>([]);

  ngOnInit() {
    this.miembroSvc.getMockBySede(this.sede.sedeId).subscribe(m => {
      this.miembros.set(m);
      this.cargandoMiembros.set(false);
    });
    this.celulas.set(this.generarCelulas());
  }

  aniosFundacion(): number {
    return Math.floor((Date.now() - new Date(this.sede.fechaFundacion).getTime()) / (365.25 * 86400000));
  }

  estadoResumen() {
    const m = this.miembros();
    const count = (estado: string) => m.filter(x => x.estado === estado).length;
    return [
      { label: 'Activos',    count: count('Activo'),    bg: '#dcfce7', color: '#15803d' },
      { label: 'Líderes',    count: count('Lider'),     bg: '#ede9fe', color: '#7c3aed' },
      { label: 'Visitantes', count: count('Visitante'), bg: '#fef3c7', color: '#92400e' },
      { label: 'Inactivos',  count: count('Inactivo'),  bg: '#f1f5f9', color: '#475569' },
    ].filter(g => g.count > 0);
  }

  private generarCelulas(): CelulaSimulada[] {
    const colores = ['#7c3aed', '#059669', '#0284c7', '#d97706', '#ef4444', '#0891b2', '#16a34a', '#db2777'];
    const nombres = ['Célula Esperanza', 'Célula Fe Viva', 'Célula Nueva Vida', 'Célula Gracia', 'Célula Amor', 'Célula Luz', 'Célula Fuego', 'Célula Paz'];
    const lideres = ['Carlos Ruiz', 'Laura Vargas', 'Jorge Salazar', 'Héctor Cano', 'Ana Gómez', 'Felipe Ríos', 'Daniela Reyes', 'Andrés Mora'];
    return Array.from({ length: this.sede.totalCelulas }, (_, i) => ({
      celulaId: i + 1,
      nombre: nombres[i % nombres.length],
      lider: lideres[i % lideres.length],
      miembros: Math.max(4, Math.floor(this.sede.totalMiembros / this.sede.totalCelulas) + (i % 3 - 1)),
      color: colores[i % colores.length],
    }));
  }
}
