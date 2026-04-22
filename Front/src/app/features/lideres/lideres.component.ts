import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { NgClass, DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatTabsModule } from '@angular/material/tabs';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTableModule } from '@angular/material/table';
import { MatChipsModule } from '@angular/material/chips';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatDialog } from '@angular/material/dialog';
import { MatDividerModule } from '@angular/material/divider';
import { Router } from '@angular/router';
import { LideresService } from '../../core/auth/lideres.service';
import { AuthService } from '../../core/auth/auth.service';
import { Lider, LiderNodo, HistorialLider, NivelLider } from '../../core/models/lider.models';
import { NodoAccion } from './nodo-arbol/nodo-arbol.component';
import { LiderFormDialogComponent } from './lider-form-dialog/lider-form-dialog.component';

@Component({
  selector: 'app-lideres',
  standalone: true,
  imports: [
    NgClass, DatePipe, FormsModule,
    MatTabsModule, MatCardModule, MatButtonModule, MatIconModule,
    MatTableModule, MatChipsModule, MatProgressSpinnerModule,
    MatTooltipModule, MatFormFieldModule, MatInputModule,
    MatSelectModule, MatDividerModule,
  ],
  templateUrl: './lideres.component.html',
  styleUrl: './lideres.component.css',
})
export class LideresComponent implements OnInit {
  private svc    = inject(LideresService);
  private auth   = inject(AuthService);
  private dialog = inject(MatDialog);
  private router = inject(Router);

  // ── Estado ────────────────────────────────────────────────────────────────────
  arbol = signal<LiderNodo | null>(null);
  lideres = signal<Lider[]>([]);
  historial = signal<HistorialLider[]>([]);
  cargando = signal(false);

  filtroNivel = signal<NivelLider | ''>('');
  filtroBusqueda = signal('');

  columnaHistorial = ['lider', 'nivel', 'territorio', 'inicio', 'fin', 'motivo', 'estado'];
  expandidoId = signal<number | null>(null);

  // ── Drill-down navegación ─────────────────────────────────────────────────────
  navPath = signal<LiderNodo[]>([]);

  viewNode = computed(() => {
    const path = this.navPath();
    return path.length > 0 ? path[path.length - 1] : this.arbol();
  });

  navTo(nodo: LiderNodo) {
    this.navPath.update(p => [...p, nodo]);
  }

  navBack(toIndex: number) {
    this.navPath.update(p => p.slice(0, toIndex));
  }

  // ── Computed ──────────────────────────────────────────────────────────────────
  lideresFiltrados = computed(() => {
    let lista = this.lideres();
    if (this.filtroNivel()) lista = lista.filter(l => l.nivel === this.filtroNivel());
    const q = this.filtroBusqueda().toLowerCase().trim();
    if (q) lista = lista.filter(l =>
      `${l.nombre} ${l.apellido}`.toLowerCase().includes(q) ||
      l.zonaOCelula.toLowerCase().includes(q)
    );
    return lista;
  });

  totalPastores   = computed(() => this.lideres().filter(l => l.nivel === 'Pastor').length);
  totalZonas      = computed(() => this.lideres().filter(l => l.nivel === 'LiderZona').length);
  totalCelulas    = computed(() => this.lideres().filter(l => l.nivel === 'LiderCelula').length);
  totalMiembros   = computed(() => this.lideres().filter(l => l.nivel === 'LiderCelula').reduce((s, l) => s + l.totalMiembros, 0));

  // ── Ciclo de vida ─────────────────────────────────────────────────────────────
  ngOnInit() {
    this.cargar();
  }

  cargar() {
    this.cargando.set(true);
    const sedeId = this.auth.currentUser()!.sedeId;

    this.svc.getArbol(sedeId).subscribe(a => this.arbol.set(a));
    this.svc.getAll(sedeId).subscribe(l => { this.lideres.set(l); this.cargando.set(false); });
    this.svc.getHistorial(sedeId).subscribe(h => this.historial.set(h));
  }

  // ── Acciones del árbol ────────────────────────────────────────────────────────
  onAccionArbol(evento: NodoAccion) {
    if (evento.tipo === 'editar') {
      const lider = this.lideres().find(l => l.liderId === evento.nodo.liderId);
      this.abrirForm(lider);
    }
    if (evento.tipo === 'agregar') {
      const nivelHijo: NivelLider = evento.nodo.nivel === 'Pastor' ? 'LiderZona' : 'LiderCelula';
      this.abrirForm(undefined, nivelHijo, evento.nodo.liderId);
    }
    if (evento.tipo === 'eliminar') {
      this.eliminar(evento.nodo.liderId);
    }
    if (evento.tipo === 'verDetalle') {
      this.router.navigate(['/miembros', evento.nodo.miembroId]);
    }
  }

  // ── CRUD ──────────────────────────────────────────────────────────────────────
  abrirForm(lider?: Lider, preNivel?: NivelLider, prePadreId?: number) {
    const ref = this.dialog.open(LiderFormDialogComponent, {
      data: { lider, preseleccionarNivel: preNivel, preseleccionarPadreId: prePadreId },
      width: '520px',
      disableClose: true,
    });
    ref.afterClosed().subscribe(result => {
      if (result) this.cargar();
    });
  }

  eliminar(id: number) {
    if (!confirm('¿Deseas remover a este líder? Se registrará en el historial.')) return;
    this.svc.delete(id).subscribe(() => this.cargar());
  }

  // ── Expand ────────────────────────────────────────────────────────────────────
  toggleExpand(l: Lider) {
    this.expandidoId.set(this.expandidoId() === l.liderId ? null : l.liderId);
  }

  discipulos(l: Lider): Lider[] {
    return this.lideres().filter(d => d.liderPadreId === l.liderId);
  }

  initials(nombre: string, apellido: string) {
    return `${nombre[0]}${apellido[0]}`.toUpperCase();
  }

  // ── Helpers de UI ─────────────────────────────────────────────────────────────
  nivelLabel(nivel: NivelLider): string {
    const map: Record<NivelLider, string> = { Pastor: 'Pastor', LiderZona: 'Líder Zona', LiderCelula: 'Líder Célula' };
    return map[nivel];
  }

  nivelClass(nivel: NivelLider): string {
    const map: Record<NivelLider, string> = { Pastor: 'pastor', LiderZona: 'zona', LiderCelula: 'celula' };
    return map[nivel];
  }

  nivelIcon(nivel: NivelLider): string {
    const map: Record<NivelLider, string> = { Pastor: 'church', LiderZona: 'groups', LiderCelula: 'group' };
    return map[nivel];
  }
}
