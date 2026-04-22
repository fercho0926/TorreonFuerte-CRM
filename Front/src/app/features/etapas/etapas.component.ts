import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { NgClass, DatePipe } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatTableModule } from '@angular/material/table';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatChipsModule } from '@angular/material/chips';
import { MatDividerModule } from '@angular/material/divider';
import { FormsModule } from '@angular/forms';
import { EtapasService } from '../../core/auth/etapas.service';
import { MiembrosService } from '../../core/auth/miembros.service';
import { MiembroList } from '../../core/models/miembro.models';
import { AuthService } from '../../core/auth/auth.service';
import { Etapa, MiembroEnEtapa, NodePosition } from '../../core/models/etapa.models';

const SVG_W = 1000;
const SVG_H = 260;
const TOP_Y  = 75;
const BOT_Y  = 185;

@Component({
  selector: 'app-etapas',
  standalone: true,
  imports: [
    NgClass, DatePipe, FormsModule,
    MatIconModule, MatButtonModule, MatCardModule,
    MatTableModule, MatTooltipModule, MatProgressSpinnerModule,
    MatChipsModule, MatDividerModule,
  ],
  templateUrl: './etapas.component.html',
  styleUrl: './etapas.component.css',
})
export class EtapasComponent implements OnInit {
  private svc        = inject(EtapasService);
  private miembroSvc = inject(MiembrosService);
  private auth       = inject(AuthService);

  etapas            = signal<Etapa[]>([]);
  etapaSeleccionada = signal<Etapa | null>(null);
  miembros          = signal<MiembroEnEtapa[]>([]);
  cargando          = signal(false);
  cargandoMiembros  = signal(false);
  avanzando         = signal<number | null>(null);

  mostrarPicker     = signal(false);
  busquedaPicker    = signal('');
  todosMiembros     = signal<MiembroList[]>([]);

  miembrosFiltrados = computed(() => {
    const q = this.busquedaPicker().toLowerCase().trim();
    return this.todosMiembros().filter(m =>
      !q || `${m.nombre} ${m.apellido}`.toLowerCase().includes(q)
    );
  });

  columnas = ['nombre', 'lider', 'dias', 'acciones'];

  // ── Posicionamiento del path ─────────────────────────────────────────────────
  nodePositions = computed<NodePosition[]>(() => this.computePositions(this.etapas()));
  pathD         = computed(() => this.generatePath(this.nodePositions()));
  svgW = SVG_W;
  svgH = SVG_H;

  // ── Computed stats ────────────────────────────────────────────────────────────
  totalEnFormacion = computed(() => this.etapas().reduce((s, e) => s + e.totalMiembros, 0));
  etapaMasPoblada  = computed(() => [...this.etapas()].sort((a, b) => b.totalMiembros - a.totalMiembros)[0] ?? null);
  etapaSiguiente   = computed(() => {
    const sel = this.etapaSeleccionada();
    if (!sel) return null;
    return this.etapas().find(e => e.orden === sel.orden + 1) ?? null;
  });

  ngOnInit() {
    this.cargar();
    this.miembroSvc.getMock().subscribe(m => this.todosMiembros.set(m));
  }

  cargar() {
    this.cargando.set(true);
    const sedeId = this.auth.currentUser()!.sedeId;
    this.svc.getEtapas(sedeId).subscribe(etapas => {
      this.etapas.set(etapas);
      this.cargando.set(false);
      if (etapas.length > 0) this.seleccionar(etapas[0]);
    });
  }

  seleccionar(etapa: Etapa) {
    this.etapaSeleccionada.set(etapa);
    this.cargandoMiembros.set(true);
    this.svc.getMiembrosEnEtapa(etapa.etapaId).subscribe(m => {
      this.miembros.set(m);
      this.cargandoMiembros.set(false);
    });
  }

  avanzar(miembro: MiembroEnEtapa) {
    const sel = this.etapaSeleccionada();
    const sig = this.etapaSiguiente();
    if (!sel) return;
    const confirmMsg = sig
      ? `¿Avanzar a ${miembro.nombre} ${miembro.apellido} a "${sig.nombre}"?`
      : `¿Marcar a ${miembro.nombre} ${miembro.apellido} como completado (sin etapa siguiente)?`;
    if (!confirm(confirmMsg)) return;

    this.avanzando.set(miembro.miembroId);
    this.svc.avanzarEtapa({
      miembroId: miembro.miembroId,
      etapaIdActual: sel.etapaId,
      etapaIdNueva: sig?.etapaId ?? null,
    }).subscribe(() => {
      this.avanzando.set(null);
      this.cargar();
    });
  }

  agregarMiembro(miembro: MiembroList) {
    const etapa = this.etapaSeleccionada();
    if (!etapa) return;
    this.mostrarPicker.set(false);
    this.busquedaPicker.set('');
    this.svc.avanzarEtapa({ miembroId: miembro.miembroId, etapaIdActual: 0, etapaIdNueva: etapa.etapaId })
      .subscribe(() => this.seleccionar(etapa));
  }

  diasLabel(dias: number): string {
    if (dias < 30)  return `${dias}d`;
    if (dias < 365) return `${Math.floor(dias / 30)}m`;
    return `${Math.floor(dias / 365)}a ${Math.floor((dias % 365) / 30)}m`;
  }

  diasClass(dias: number): string {
    if (dias > 365) return 'dias-alerta';
    if (dias > 180) return 'dias-warn';
    return 'dias-ok';
  }

  // ── Path math ─────────────────────────────────────────────────────────────────
  private computePositions(etapas: Etapa[]): NodePosition[] {
    const n = etapas.length;
    if (n === 0) return [];
    if (n === 1) return [{ x: SVG_W / 2, y: (TOP_Y + BOT_Y) / 2, xPct: 50 }];
    const margin = 110;
    const usable = SVG_W - 2 * margin;
    return etapas.map((_, i) => {
      const x = margin + (i / (n - 1)) * usable;
      const y = i % 2 === 0 ? TOP_Y : BOT_Y;
      return { x, y, xPct: (x / SVG_W) * 100 };
    });
  }

  private generatePath(positions: NodePosition[]): string {
    if (positions.length === 0) return '';
    if (positions.length === 1) return `M ${positions[0].x},${positions[0].y}`;
    let d = `M ${positions[0].x},${positions[0].y}`;
    for (let i = 1; i < positions.length; i++) {
      const p = positions[i - 1];
      const c = positions[i];
      const mx = (p.x + c.x) / 2;
      d += ` C ${mx},${p.y} ${mx},${c.y} ${c.x},${c.y}`;
    }
    return d;
  }
}
