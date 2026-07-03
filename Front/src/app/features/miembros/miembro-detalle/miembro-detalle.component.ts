import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { NgClass, DatePipe } from '@angular/common';
import { ButtonModule } from 'primeng/button';
import { ProgressSpinnerModule } from 'primeng/progressspinner';
import { TooltipModule } from 'primeng/tooltip';
import { MiembrosService } from '../../../core/auth/miembros.service';
import { MiembroDetalle } from '../../../core/models/miembro.models';

interface PrepStep {
  orden: number;
  nombre: string;
  icono: string;
  color: string;
  colorClaro: string;
}

const PREP_PATH: PrepStep[] = [
  { orden: 1, nombre: 'Encuentro con Dios',     icono: 'pi-heart-fill', color: '#f43f5e', colorClaro: '#fce7f3' },
  { orden: 2, nombre: 'Libres para Servir',     icono: 'pi-lock-open',  color: '#f97316', colorClaro: '#ffedd5' },
  { orden: 3, nombre: 'Avivando el Fuego',      icono: 'pi-bolt',       color: '#eab308', colorClaro: '#fef9c3' },
  { orden: 4, nombre: 'Caminando en Santidad',  icono: 'pi-directions', color: '#22c55e', colorClaro: '#dcfce7' },
  { orden: 5, nombre: 'En Servicio Activo',     icono: 'pi-heart',      color: '#6366f1', colorClaro: '#ede9fe' },
];

type StepState = 'done' | 'active' | 'locked';

interface EtapaNode extends PrepStep {
  estado: StepState;
  fechaInicio: string | null;
  fechaFin: string | null;
}

@Component({
  selector: 'app-miembro-detalle',
  standalone: true,
  imports: [
    RouterLink, NgClass, DatePipe,
    ButtonModule, ProgressSpinnerModule, TooltipModule,
  ],
  templateUrl: './miembro-detalle.component.html',
  styleUrl: './miembro-detalle.component.css',
})
export class MiembroDetalleComponent implements OnInit {
  private svc   = inject(MiembrosService);
  private route = inject(ActivatedRoute);

  miembro  = signal<MiembroDetalle | null>(null);
  loading  = signal(true);
  error    = signal(false);

  etapaPath = computed((): EtapaNode[] => {
    const m = this.miembro();
    const actualOrden = m?.etapaActualOrden ?? null;

    return PREP_PATH.map(step => {
      let estado: StepState;
      if (actualOrden === null) {
        estado = 'locked';
      } else if (step.orden < actualOrden) {
        estado = 'done';
      } else if (step.orden === actualOrden) {
        estado = 'active';
      } else {
        estado = 'locked';
      }

      const etapa = m?.etapas.find(e => e.etapaNombre === step.nombre);
      return {
        ...step,
        estado,
        fechaInicio: etapa?.fechaInicio ?? null,
        fechaFin:    etapa?.fechaFin    ?? null,
      };
    });
  });

  progresoPorc = computed(() => {
    const orden = this.miembro()?.etapaActualOrden;
    if (!orden) return 0;
    return ((orden - 1) / (PREP_PATH.length - 1)) * 100;
  });

  edad = computed(() => {
    const fn = this.miembro()?.fechaNacimiento;
    if (!fn) return null;
    const diff = Date.now() - new Date(fn).getTime();
    return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
  });

  tiempoEnIglesia = computed(() => {
    const fi = this.miembro()?.fechaIngreso;
    if (!fi) return null;
    const meses = Math.floor((Date.now() - new Date(fi).getTime()) / (1000 * 60 * 60 * 24 * 30.44));
    if (meses < 12) return `${meses} mes${meses === 1 ? '' : 'es'}`;
    const años = Math.floor(meses / 12);
    const resto = meses % 12;
    return resto > 0 ? `${años} año${años > 1 ? 's' : ''} y ${resto} mes${resto === 1 ? '' : 'es'}` : `${años} año${años > 1 ? 's' : ''}`;
  });

  ngOnInit() {
    const id = +this.route.snapshot.params['id'];
    this.svc.getById(id).subscribe({
      next: (m) => { this.miembro.set(m); this.loading.set(false); },
      error: () => { this.error.set(true); this.loading.set(false); },
    });
  }

  estadoClass(estado: string) {
    return `chip-${estado.toLowerCase()}`;
  }

  initials(nombre: string, apellido: string) {
    return `${nombre[0]}${apellido[0]}`.toUpperCase();
  }
}
