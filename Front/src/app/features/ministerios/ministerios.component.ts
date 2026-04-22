import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatChipsModule } from '@angular/material/chips';
import { MatDialog } from '@angular/material/dialog';
import { MinisteriosService } from '../../core/auth/ministerios.service';
import { MiembrosService } from '../../core/auth/miembros.service';
import { AuthService } from '../../core/auth/auth.service';
import { Ministerio, IntegranteMinisterio, MINISTERIO_TIPO_META } from '../../core/models/ministerio.models';
import { MiembroList } from '../../core/models/miembro.models';
import { MinisterioFormDialogComponent } from './ministerio-form-dialog/ministerio-form-dialog.component';

@Component({
  selector: 'app-ministerios',
  standalone: true,
  imports: [
    MatIconModule, MatButtonModule, MatProgressSpinnerModule,
    MatTooltipModule, MatSlideToggleModule, MatChipsModule,
  ],
  templateUrl: './ministerios.component.html',
  styleUrl: './ministerios.component.css',
})
export class MinisteriosComponent implements OnInit {
  private svc        = inject(MinisteriosService);
  private miembroSvc = inject(MiembrosService);
  private auth       = inject(AuthService);
  private dialog     = inject(MatDialog);

  ministerios  = signal<Ministerio[]>([]);
  miembros     = signal<MiembroList[]>([]);
  cargando     = signal(true);
  expandido    = signal<number | null>(null);   // ministerioId expandido
  mostrarPicker = signal<number | null>(null);  // ministerioId con picker abierto
  busqueda     = signal('');

  totalIntegrantes = computed(() =>
    this.ministerios().reduce((s, m) => s + m.totalIntegrantes, 0)
  );
  ministeriosActivos = computed(() =>
    this.ministerios().filter(m => m.activo).length
  );
  totalTipos = computed(() =>
    new Set(this.ministerios().map(m => m.tipo)).size
  );

  miembrosFiltrados = computed(() => {
    const q = this.busqueda().toLowerCase().trim();
    return this.miembros().filter(m =>
      !q || `${m.nombre} ${m.apellido}`.toLowerCase().includes(q)
    );
  });

  MINISTERIO_TIPO_META = MINISTERIO_TIPO_META;

  ngOnInit() {
    this.cargar();
    this.miembroSvc.getMock().subscribe(m => this.miembros.set(m));
  }

  cargar() {
    this.cargando.set(true);
    const sedeId = this.auth.currentUser()!.sedeId;
    this.svc.getAll(sedeId).subscribe(m => {
      this.ministerios.set(m);
      this.cargando.set(false);
    });
  }

  toggleExpand(id: number) {
    this.expandido.set(this.expandido() === id ? null : id);
    this.mostrarPicker.set(null);
    this.busqueda.set('');
  }

  abrirForm(ministerio?: Ministerio) {
    this.dialog.open(MinisterioFormDialogComponent, {
      data: { ministerio },
      width: '500px',
      disableClose: true,
    }).afterClosed().subscribe(r => { if (r) this.cargar(); });
  }

  eliminar(m: Ministerio) {
    if (!confirm(`¿Eliminar el ministerio "${m.nombre}"?`)) return;
    this.svc.delete(m.ministerioId).subscribe(() => this.cargar());
  }

  togglePicker(ministerioId: number) {
    this.mostrarPicker.set(this.mostrarPicker() === ministerioId ? null : ministerioId);
    this.busqueda.set('');
  }

  agregarIntegrante(ministerio: Ministerio, miembro: MiembroList) {
    this.mostrarPicker.set(null);
    this.busqueda.set('');
    this.svc.agregarIntegrante(
      ministerio.ministerioId, miembro.miembroId,
      miembro.nombre, miembro.apellido, 'Integrante'
    ).subscribe(() => this.cargar());
  }

  removerIntegrante(ministerioId: number, integrante: IntegranteMinisterio) {
    if (!confirm(`¿Remover a ${integrante.nombre} ${integrante.apellido}?`)) return;
    this.svc.removerIntegrante(ministerioId, integrante.integranteId)
      .subscribe(() => this.cargar());
  }

  toggleIntegrante(ministerioId: number, integrante: IntegranteMinisterio, activo: boolean) {
    this.svc.toggleActivo(ministerioId, integrante.integranteId, activo)
      .subscribe(() => this.cargar());
  }

  tipoMeta(m: Ministerio) { return MINISTERIO_TIPO_META[m.tipo]; }

  integrantesYaMiembro(ministerio: Ministerio): Set<number> {
    return new Set(ministerio.integrantes?.map(i => i.miembroId) ?? []);
  }
}
