import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { NgClass, DatePipe, PercentPipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { TabsModule } from 'primeng/tabs';
import { ButtonModule } from 'primeng/button';
import { ProgressBarModule } from 'primeng/progressbar';
import { ProgressSpinnerModule } from 'primeng/progressspinner';
import { TooltipModule } from 'primeng/tooltip';
import { MenuModule } from 'primeng/menu';
import { ToggleSwitchModule } from 'primeng/toggleswitch';
import { DialogService } from 'primeng/dynamicdialog';
import type { MenuItem } from 'primeng/api';
import { EventosService } from '../../core/auth/eventos.service';
import { AuthService } from '../../core/auth/auth.service';
import { MinisteriosService } from '../../core/auth/ministerios.service';
import {
  Evento, EventoEstado, EventoTipo, EVENTO_TIPO_META,
} from '../../core/models/evento.models';
import { Ministerio, MINISTERIO_TIPO_META } from '../../core/models/ministerio.models';
import {
  EventoFormDialogComponent,
} from './evento-form-dialog/evento-form-dialog.component';
import {
  EventoDetalleDialogComponent,
} from './evento-detalle-dialog/evento-detalle-dialog.component';

interface DiaCal {
  fecha: Date;
  esMesActual: boolean;
  eventos: Evento[];
}

@Component({
  selector: 'app-eventos',
  standalone: true,
  imports: [
    NgClass, DatePipe, PercentPipe, FormsModule,
    TabsModule, ButtonModule,
    ProgressBarModule, ProgressSpinnerModule,
    TooltipModule, MenuModule, ToggleSwitchModule,
  ],
  templateUrl: './eventos.component.html',
  styleUrl:    './eventos.component.css',
})
export class EventosComponent implements OnInit {
  private svc    = inject(EventosService);
  private minSvc = inject(MinisteriosService);
  private auth   = inject(AuthService);
  private dialogService = inject(DialogService);

  eventos      = signal<Evento[]>([]);
  ministerios  = signal<Ministerio[]>([]);
  cargando     = signal(true);
  cargandoMin  = signal(true);

  ministerioSeleccionado = signal<Ministerio | null>(null);

  MINISTERIO_TIPO_META = MINISTERIO_TIPO_META;

  // ── Calendario ────────────────────────────────────────────────────────────────
  mesActual       = signal(new Date(new Date().getFullYear(), new Date().getMonth(), 1));
  diaSeleccionado = signal<Date | null>(null);

  mesLabel = computed(() =>
    this.mesActual().toLocaleDateString('es', { month: 'long', year: 'numeric' })
  );

  diasCalendario = computed((): DiaCal[] => {
    const base      = this.mesActual();
    const año       = base.getFullYear();
    const mes       = base.getMonth();
    const totalDias = new Date(año, mes + 1, 0).getDate();
    const primerDow = new Date(año, mes, 1).getDay(); // 0=Dom
    const offset    = primerDow === 0 ? 6 : primerDow - 1; // lunes=0

    const dias: DiaCal[] = [];

    for (let i = offset - 1; i >= 0; i--)
      dias.push({ fecha: new Date(año, mes, -i), esMesActual: false, eventos: [] });

    for (let d = 1; d <= totalDias; d++) {
      const fecha = new Date(año, mes, d);
      dias.push({ fecha, esMesActual: true, eventos: this.eventosEnDia(fecha) });
    }

    let next = 1;
    while (dias.length < 42)
      dias.push({ fecha: new Date(año, mes + 1, next++), esMesActual: false, eventos: [] });

    return dias;
  });

  eventosDelDiaSeleccionado = computed(() => {
    const d = this.diaSeleccionado();
    return d ? this.eventosEnDia(d) : [];
  });

  // ── Derived ───────────────────────────────────────────────────────────────────
  proximos  = computed(() => this.eventos().filter(e => e.estado === 'Proximo' || e.estado === 'EnCurso')
                                           .sort((a, b) => a.fecha.localeCompare(b.fecha)));
  historial = computed(() => this.eventos().filter(e => e.estado === 'Finalizado' || e.estado === 'Cancelado')
                                           .sort((a, b) => b.fecha.localeCompare(a.fecha)));

  totalInscritos  = computed(() => this.proximos().reduce((s, e) => s + e.totalInscritos, 0));
  totalEstesMes   = computed(() => {
    const hoy = new Date();
    return this.proximos().filter(e => {
      const f = new Date(e.fecha);
      return f.getFullYear() === hoy.getFullYear() && f.getMonth() === hoy.getMonth();
    }).length;
  });
  promedioAsistencia = computed(() => {
    const fin = this.historial().filter(e => e.totalAsistentes !== null && e.totalInscritos > 0);
    if (fin.length === 0) return 0;
    return fin.reduce((s, e) => s + (e.totalAsistentes! / e.totalInscritos), 0) / fin.length;
  });

  ngOnInit() {
    this.cargar();
    this.cargarMinisterios();
  }

  cargar() {
    this.cargando.set(true);
    const sedeId = this.auth.currentUser()!.sedeId;
    this.svc.getAll(sedeId).subscribe(e => { this.eventos.set(e); this.cargando.set(false); });
  }

  cargarMinisterios() {
    this.cargandoMin.set(true);
    const sedeId = this.auth.currentUser()!.sedeId;
    this.minSvc.getAll(sedeId).subscribe(m => { this.ministerios.set(m); this.cargandoMin.set(false); });
  }

  seleccionarMinisterio(m: Ministerio) {
    this.ministerioSeleccionado.set(this.ministerioSeleccionado()?.ministerioId === m.ministerioId ? null : m);
  }

  toggleIntegrante(ministerioId: number, integranteId: number, activo: boolean) {
    this.minSvc.toggleActivo(ministerioId, integranteId, activo).subscribe(() => this.cargarMinisterios());
  }

  ministerioTipoMeta(m: Ministerio) { return MINISTERIO_TIPO_META[m.tipo]; }

  // ── Menús de acciones ─────────────────────────────────────────────────────────
  menuItemsEvento(e: Evento): MenuItem[] {
    return [
      { label: 'Editar', icon: 'pi pi-pencil', command: () => this.abrirForm(e) },
      { label: 'Marcar En Curso', icon: 'pi pi-play-circle', command: () => this.cambiarEstado(e, 'EnCurso') },
      { label: 'Marcar Finalizado', icon: 'pi pi-check-circle', command: () => this.cambiarEstado(e, 'Finalizado') },
      { label: 'Cancelar evento', icon: 'pi pi-times-circle', command: () => this.cambiarEstado(e, 'Cancelado') },
      { label: 'Eliminar', icon: 'pi pi-trash', styleClass: 'menu-danger', command: () => this.eliminar(e) },
    ];
  }

  menuItemsHist(e: Evento): MenuItem[] {
    return [
      { label: 'Editar', icon: 'pi pi-pencil', command: () => this.abrirForm(e) },
      { label: 'Eliminar', icon: 'pi pi-trash', styleClass: 'menu-danger', command: () => this.eliminar(e) },
    ];
  }

  // ── Dialogs ───────────────────────────────────────────────────────────────────
  abrirForm(evento?: Evento) {
    this.dialogService.open(EventoFormDialogComponent, {
      data: { evento }, width: '600px', showHeader: false, closeOnEscape: false,
    })!.onClose.subscribe(r => { if (r) this.cargar(); });
  }

  abrirDetalle(evento: Evento) {
    this.dialogService.open(EventoDetalleDialogComponent, {
      data: { evento }, width: '580px', showHeader: false, dismissableMask: true,
    })!.onClose.subscribe(() => this.cargar());
  }

  eliminar(evento: Evento) {
    if (!confirm(`¿Eliminar "${evento.nombre}"?`)) return;
    this.svc.delete(evento.eventoId).subscribe(() => this.cargar());
  }

  cambiarEstado(evento: Evento, estado: EventoEstado) {
    this.svc.cambiarEstado(evento.eventoId, estado).subscribe(() => this.cargar());
  }

  // ── Calendario: helpers ───────────────────────────────────────────────────────
  private toIso(d: Date): string {
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
  }

  private eventosEnDia(fecha: Date): Evento[] {
    const iso = this.toIso(fecha);
    return this.eventos().filter(e =>
      e.fechaFin && e.fechaFin !== e.fecha
        ? iso >= e.fecha && iso <= e.fechaFin
        : e.fecha === iso
    );
  }

  mesAnterior() {
    const m = this.mesActual();
    this.mesActual.set(new Date(m.getFullYear(), m.getMonth() - 1, 1));
    this.diaSeleccionado.set(null);
  }

  mesSiguiente() {
    const m = this.mesActual();
    this.mesActual.set(new Date(m.getFullYear(), m.getMonth() + 1, 1));
    this.diaSeleccionado.set(null);
  }

  irAHoy() {
    this.mesActual.set(new Date(new Date().getFullYear(), new Date().getMonth(), 1));
    this.diaSeleccionado.set(new Date());
  }

  seleccionarDia(dia: DiaCal) {
    if (!dia.esMesActual) return;
    const sel = this.diaSeleccionado();
    this.diaSeleccionado.set(sel && this.toIso(sel) === this.toIso(dia.fecha) ? null : dia.fecha);
  }

  esDiaSeleccionado(fecha: Date): boolean {
    const s = this.diaSeleccionado();
    return !!s && this.toIso(s) === this.toIso(fecha);
  }

  esHoy(fecha: Date): boolean {
    return this.toIso(fecha) === this.toIso(new Date());
  }

  calDiaLabel(fecha: Date): string {
    return fecha.toLocaleDateString('es', { weekday: 'long', day: 'numeric', month: 'long' });
  }

  // ── Helpers UI ────────────────────────────────────────────────────────────────
  tipoMeta(tipo: EventoTipo) { return EVENTO_TIPO_META[tipo]; }

  estadoLabel(estado: EventoEstado): string {
    const m: Record<EventoEstado, string> = {
      Proximo: 'Próximo', EnCurso: 'En curso',
      Finalizado: 'Finalizado', Cancelado: 'Cancelado',
    };
    return m[estado];
  }

  estadoClass(estado: EventoEstado): string {
    const m: Record<EventoEstado, string> = {
      Proximo: 'est-proximo', EnCurso: 'est-encurso',
      Finalizado: 'est-finalizado', Cancelado: 'est-cancelado',
    };
    return m[estado];
  }

  diasRestantes(fecha: string): number {
    return Math.ceil((new Date(fecha).getTime() - Date.now()) / 86400000);
  }

  diasRestantesLabel(fecha: string): string {
    const d = this.diasRestantes(fecha);
    if (d < 0)  return 'Pasado';
    if (d === 0) return 'Hoy';
    if (d === 1) return 'Mañana';
    return `En ${d} días`;
  }

  diasClass(fecha: string): string {
    const d = this.diasRestantes(fecha);
    if (d <= 3)  return 'dias-urgente';
    if (d <= 14) return 'dias-pronto';
    return 'dias-normal';
  }

  tasaAsistencia(e: Evento): number {
    if (!e.totalAsistentes || e.totalInscritos === 0) return 0;
    return e.totalAsistentes / e.totalInscritos;
  }

  tasaColor(e: Evento): string {
    const t = this.tasaAsistencia(e);
    if (t >= 0.9) return '#16a34a';
    if (t >= 0.7) return '#d97706';
    return '#dc2626';
  }

  esDuracionMultiple(e: Evento): boolean {
    return !!e.fechaFin && e.fechaFin !== e.fecha;
  }

  Math = Math;
}
