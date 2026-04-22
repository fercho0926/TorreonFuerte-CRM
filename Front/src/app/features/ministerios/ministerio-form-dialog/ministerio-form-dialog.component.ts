import { Component, inject, signal, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MinisteriosService } from '../../../core/auth/ministerios.service';
import { MiembrosService } from '../../../core/auth/miembros.service';
import { AuthService } from '../../../core/auth/auth.service';
import { MiembroList } from '../../../core/models/miembro.models';
import { Ministerio, MinisterioTipo, MINISTERIO_TIPO_META } from '../../../core/models/ministerio.models';

export interface MinisterioFormData { ministerio?: Ministerio; }

@Component({
  selector: 'app-ministerio-form-dialog',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatDialogModule, MatFormFieldModule, MatInputModule,
    MatSelectModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule,
  ],
  template: `
    <h2 mat-dialog-title>
      <mat-icon>{{ data.ministerio ? 'edit' : 'add_circle' }}</mat-icon>
      {{ data.ministerio ? 'Editar ministerio' : 'Nuevo ministerio' }}
    </h2>

    <mat-dialog-content>
      <form [formGroup]="form" class="form-body">

        <mat-form-field appearance="outline" class="full">
          <mat-label>Tipo de ministerio</mat-label>
          <mat-select formControlName="tipo">
            @for (t of tipos; track t.value) {
              <mat-option [value]="t.value">
                <mat-icon>{{ t.icono }}</mat-icon> {{ t.label }}
              </mat-option>
            }
          </mat-select>
          <mat-icon matPrefix>category</mat-icon>
        </mat-form-field>

        <mat-form-field appearance="outline" class="full">
          <mat-label>Nombre del ministerio</mat-label>
          <input matInput formControlName="nombre" placeholder="Ej: Equipo de Alabanza Central">
          <mat-icon matPrefix>title</mat-icon>
        </mat-form-field>

        <mat-form-field appearance="outline" class="full">
          <mat-label>Líder del ministerio</mat-label>
          <mat-select formControlName="liderMiembroId">
            @for (m of miembros(); track m.miembroId) {
              <mat-option [value]="m.miembroId">
                {{ m.nombre }} {{ m.apellido }}
                <span style="font-size:11px;color:#94a3b8"> · {{ m.estado }}</span>
              </mat-option>
            }
          </mat-select>
          <mat-icon matPrefix>person</mat-icon>
        </mat-form-field>

        <mat-form-field appearance="outline" class="full">
          <mat-label>Descripción</mat-label>
          <textarea matInput formControlName="descripcion" rows="3"
                    placeholder="¿Cuál es el propósito de este ministerio?"></textarea>
          <mat-icon matPrefix>notes</mat-icon>
        </mat-form-field>

      </form>
    </mat-dialog-content>

    <mat-dialog-actions align="end">
      <button mat-button mat-dialog-close>Cancelar</button>
      <button mat-flat-button color="primary" (click)="guardar()"
              [disabled]="form.invalid || saving()">
        @if (saving()) { <mat-spinner diameter="18" /> }
        @else { <mat-icon>save</mat-icon> }
        {{ data.ministerio ? 'Guardar cambios' : 'Crear ministerio' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    h2[mat-dialog-title] { display:flex; align-items:center; gap:8px; }
    mat-dialog-content { padding-top:16px !important; min-width:min(480px,92vw); }
    .form-body { display:flex; flex-direction:column; gap:6px; }
    .full { width:100%; }
    mat-form-field { width:100%; }
    mat-dialog-actions { gap:8px; padding:16px !important; }
  `],
})
export class MinisterioFormDialogComponent implements OnInit {
  private fb        = inject(FormBuilder);
  private svc       = inject(MinisteriosService);
  private miembroSvc = inject(MiembrosService);
  private auth      = inject(AuthService);
  readonly dialogRef = inject(MatDialogRef<MinisterioFormDialogComponent>);
  readonly data: MinisterioFormData = inject(MAT_DIALOG_DATA);

  saving   = signal(false);
  miembros = signal<MiembroList[]>([]);

  tipos = Object.entries(MINISTERIO_TIPO_META).map(([value, m]) => ({
    value: value as MinisterioTipo, label: m.label, icono: m.icono,
  }));

  form = this.fb.group({
    tipo:           [this.data.ministerio?.tipo ?? 'Alabanza' as MinisterioTipo, Validators.required],
    nombre:         [this.data.ministerio?.nombre ?? '', Validators.required],
    liderMiembroId: [this.data.ministerio?.liderMiembroId ?? null as number | null, Validators.required],
    descripcion:    [this.data.ministerio?.descripcion ?? '', Validators.required],
  });

  ngOnInit() {
    this.miembroSvc.getMock().subscribe(m => this.miembros.set(m));
  }

  guardar() {
    if (this.form.invalid) return;
    this.saving.set(true);
    const v = this.form.value;
    const lider = this.miembros().find(m => m.miembroId === v.liderMiembroId);
    const liderNombre = lider ? `${lider.nombre} ${lider.apellido}` : '';
    const sedeId = this.auth.currentUser()!.sedeId;

    const op = this.data.ministerio
      ? this.svc.update(this.data.ministerio.ministerioId, {
          nombre: v.nombre!, descripcion: v.descripcion!,
          liderMiembroId: v.liderMiembroId!, liderNombre,
        })
      : this.svc.create({
          sedeId, tipo: v.tipo as MinisterioTipo,
          nombre: v.nombre!, descripcion: v.descripcion!,
          liderMiembroId: v.liderMiembroId!, liderNombre,
        });

    op.subscribe({
      next: r  => { this.saving.set(false); this.dialogRef.close(r); },
      error: () => this.saving.set(false),
    });
  }
}
