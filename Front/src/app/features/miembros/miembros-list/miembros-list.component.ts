import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { NgClass, DatePipe } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatMenuModule } from '@angular/material/menu';
import { MiembrosService } from '../../../core/auth/miembros.service';
import { AuthService } from '../../../core/auth/auth.service';
import { MiembroList, EstadoMiembro } from '../../../core/models/miembro.models';

const ETAPA_ORDEN: Record<string, number> = {
  'Encuentro con Dios': 1,
  'Libres para Servir': 2,
  'Avivando el Fuego': 3,
  'Caminando en Santidad': 4,
  'En Servicio Activo': 5,
};

const ETAPA_COLOR: Record<string, string> = {
  'Encuentro con Dios': '#f43f5e',
  'Libres para Servir': '#f97316',
  'Avivando el Fuego': '#eab308',
  'Caminando en Santidad': '#22c55e',
  'En Servicio Activo': '#6366f1',
};

type FiltroEstado = 'Todos' | EstadoMiembro;

@Component({
  selector: 'app-miembros-list',
  standalone: true,
  imports: [
    RouterLink, FormsModule, NgClass, DatePipe,
    MatButtonModule, MatIconModule,
    MatProgressSpinnerModule, MatTooltipModule, MatMenuModule,
  ],
  templateUrl: './miembros-list.component.html',
  styleUrl: './miembros-list.component.css',
})
export class MiembrosListComponent implements OnInit {
  private svc  = inject(MiembrosService);
  private auth = inject(AuthService);

  miembros = signal<MiembroList[]>([]);
  loading  = signal(false);
  busqueda = signal('');
  filtro   = signal<FiltroEstado>('Todos');

  filtros: FiltroEstado[] = ['Todos', 'Activo', 'Lider', 'Visitante', 'Inactivo'];
  expandidoId = signal<number | null>(null);

  totalActivos    = computed(() => this.miembros().filter(m => m.estado === 'Activo').length);
  totalLideres    = computed(() => this.miembros().filter(m => m.estado === 'Lider').length);
  totalVisitantes = computed(() => this.miembros().filter(m => m.estado === 'Visitante').length);
  totalInactivos  = computed(() => this.miembros().filter(m => m.estado === 'Inactivo').length);

  filtrados = computed(() => {
    const q = this.busqueda().toLowerCase().trim();
    const f = this.filtro();
    return this.miembros().filter(m => {
      const matchEstado = f === 'Todos' || m.estado === f;
      const matchQ = !q || `${m.nombre} ${m.apellido} ${m.email ?? ''} ${m.telefono ?? ''}`.toLowerCase().includes(q);
      return matchEstado && matchQ;
    });
  });

  ngOnInit() {
    this.cargar();
  }

  cargar() {
    this.loading.set(true);
    const sedeId = this.auth.currentUser()!.sedeId;
    this.svc.getMockBySede(sedeId).subscribe({
      next: (data) => { this.miembros.set(data); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  eliminar(m: MiembroList) {
    if (!confirm(`¿Eliminar a ${m.nombre} ${m.apellido}?`)) return;
    this.svc.delete(m.miembroId).subscribe(() => this.cargar());
  }

  estadoClass(estado: EstadoMiembro) {
    return `chip-${estado.toLowerCase()}`;
  }

  initials(nombre: string, apellido: string) {
    return `${nombre[0]}${apellido[0]}`.toUpperCase();
  }

  etapaOrden(etapa: string | null): number {
    return etapa ? (ETAPA_ORDEN[etapa] ?? 0) : 0;
  }

  etapaColor(etapa: string | null): string {
    return etapa ? (ETAPA_COLOR[etapa] ?? '#cbd5e1') : '#cbd5e1';
  }

  etapaPorc(etapa: string | null): number {
    const orden = this.etapaOrden(etapa);
    return orden === 0 ? 0 : ((orden - 1) / 4) * 100;
  }

  toggleExpand(m: MiembroList) {
    this.expandidoId.set(this.expandidoId() === m.miembroId ? null : m.miembroId);
  }

  miembrosACargo(lider: MiembroList): MiembroList[] {
    const nombre = `${lider.nombre} ${lider.apellido}`;
    return this.miembros().filter(m => m.liderNombre === nombre);
  }

  diasIngreso(fecha: string): string {
    const dias = Math.floor((Date.now() - new Date(fecha).getTime()) / 86400000);
    if (dias < 30)  return `${dias}d`;
    if (dias < 365) return `${Math.floor(dias / 30)}m`;
    const años = Math.floor(dias / 365);
    return `${años}a`;
  }
}
