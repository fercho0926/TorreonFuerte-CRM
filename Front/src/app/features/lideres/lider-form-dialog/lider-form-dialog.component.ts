import { Component, inject, signal, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { LideresService } from '../../../core/auth/lideres.service';
import { AuthService } from '../../../core/auth/auth.service';
import { MiembrosService } from '../../../core/auth/miembros.service';
import { MiembroList } from '../../../core/models/miembro.models';
import { Lider, NivelLider } from '../../../core/models/lider.models';

export interface LiderFormData {
  lider?: Lider;
  preseleccionarNivel?: NivelLider;
  preseleccionarPadreId?: number;
}

@Component({
  selector: 'app-lider-form-dialog',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatDialogModule, MatFormFieldModule, MatInputModule,
    MatSelectModule, MatButtonModule, MatIconModule,
    MatDatepickerModule, MatNativeDateModule, MatProgressSpinnerModule,
  ],
  template: `
    <h2 mat-dialog-title>
      <mat-icon>{{ data.lider ? 'edit' : 'person_add' }}</mat-icon>
      {{ data.lider ? 'Editar Líder' : 'Nuevo Líder' }}
    </h2>

    <mat-dialog-content>
      <form [formGroup]="form" class="form-grid">

        <mat-form-field appearance="outline">
          <mat-label>Nivel de liderazgo</mat-label>
          <mat-select formControlName="nivel" (selectionChange)="onNivelChange()">
            <mat-option value="Pastor">Pastor</mat-option>
            <mat-option value="LiderZona">Líder de Zona</mat-option>
            <mat-option value="LiderCelula">Líder de Célula</mat-option>
          </mat-select>
          <mat-icon matPrefix>military_tech</mat-icon>
        </mat-form-field>

        @if (form.value.nivel && form.value.nivel !== 'Pastor') {
          <mat-form-field appearance="outline">
            <mat-label>{{ form.value.nivel === 'LiderZona' ? 'Pastor a cargo' : 'Líder de zona' }}</mat-label>
            <mat-select formControlName="liderPadreId">
              @for (p of padresDisponibles(); track p.liderId) {
                <mat-option [value]="p.liderId">
                  {{ p.nombre }} {{ p.apellido }} — {{ p.zonaOCelula }}
                </mat-option>
              }
            </mat-select>
            <mat-icon matPrefix>supervisor_account</mat-icon>
          </mat-form-field>
        }

        <mat-form-field appearance="outline">
          <mat-label>{{ nivelLabel }} (nombre)</mat-label>
          <input matInput formControlName="zonaOCelula" placeholder="Ej: Zona Norte / Célula Esperanza">
          <mat-icon matPrefix>place</mat-icon>
        </mat-form-field>

        @if (!data.lider) {
          <mat-form-field appearance="outline">
            <mat-label>Miembro a designar</mat-label>
            <mat-select formControlName="miembroId">
              @for (m of miembrosDisponibles(); track m.miembroId) {
                <mat-option [value]="m.miembroId">{{ m.nombre }} {{ m.apellido }}</mat-option>
              }
            </mat-select>
            <mat-icon matPrefix>person</mat-icon>
          </mat-form-field>
        }

        <mat-form-field appearance="outline">
          <mat-label>Fecha de asignación</mat-label>
          <input matInput [matDatepicker]="picker" formControlName="fechaAsignacion">
          <mat-datepicker-toggle matSuffix [for]="picker" />
          <mat-datepicker #picker />
        </mat-form-field>

        <mat-form-field appearance="outline" class="full-width">
          <mat-label>{{ data.lider ? 'Motivo del cambio' : 'Motivo de designación' }}</mat-label>
          <textarea matInput formControlName="motivoCambio" rows="2"
                    placeholder="Ej: Crecimiento de la célula, nueva zona, etc."></textarea>
          <mat-icon matPrefix>note</mat-icon>
        </mat-form-field>

      </form>
    </mat-dialog-content>

    <mat-dialog-actions align="end">
      <button mat-button mat-dialog-close>Cancelar</button>
      <button mat-flat-button color="primary" (click)="guardar()" [disabled]="form.invalid || saving()">
        @if (saving()) {
          <mat-spinner diameter="18" />
        } @else {
          <mat-icon>save</mat-icon>
        }
        {{ data.lider ? 'Guardar cambios' : 'Designar líder' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    h2[mat-dialog-title] { display: flex; align-items: center; gap: 8px; }
    mat-dialog-content { padding-top: 16px !important; min-width: min(480px, 90vw); }
    .form-grid { display: flex; flex-direction: column; gap: 4px; }
    mat-form-field { width: 100%; }
    mat-dialog-actions { gap: 8px; padding: 16px !important; }
  `],
})
export class LiderFormDialogComponent implements OnInit {
  private fb         = inject(FormBuilder);
  private svc        = inject(LideresService);
  private auth       = inject(AuthService);
  private miembroSvc = inject(MiembrosService);
  readonly dialogRef = inject(MatDialogRef<LiderFormDialogComponent>);
  readonly data: LiderFormData = inject(MAT_DIALOG_DATA);

  saving   = signal(false);
  lideres  = signal<Lider[]>([]);
  miembrosDisponibles = signal<MiembroList[]>([]);

  form = this.fb.group({
    nivel: [this.data.preseleccionarNivel ?? this.data.lider?.nivel ?? 'LiderCelula' as NivelLider, Validators.required],
    liderPadreId: [this.data.preseleccionarPadreId ?? this.data.lider?.liderPadreId ?? null as number | null],
    zonaOCelula: [this.data.lider?.zonaOCelula ?? '', Validators.required],
    miembroId: [null as number | null],
    fechaAsignacion: [this.data.lider ? new Date(this.data.lider.fechaAsignacion) : new Date(), Validators.required],
    motivoCambio: ['', Validators.required],
  });

  ngOnInit() {
    const sedeId = this.auth.currentUser()!.sedeId;
    this.svc.getAll(sedeId).subscribe(l => this.lideres.set(l));
    this.miembroSvc.getMock().subscribe(m => this.miembrosDisponibles.set(m));
    if (!this.data.lider) this.form.get('miembroId')!.setValidators(Validators.required);
    this.onNivelChange();
  }

  padresDisponibles() {
    const nivel = this.form.value.nivel;
    if (nivel === 'LiderZona') return this.lideres().filter(l => l.nivel === 'Pastor');
    if (nivel === 'LiderCelula') return this.lideres().filter(l => l.nivel === 'LiderZona');
    return [];
  }

  get nivelLabel(): string {
    const nivel = this.form.value.nivel;
    if (nivel === 'Pastor') return 'Sede';
    if (nivel === 'LiderZona') return 'Zona';
    return 'Célula';
  }

  onNivelChange() {
    const nivel = this.form.value.nivel;
    const padreCtrl = this.form.get('liderPadreId')!;
    if (nivel === 'Pastor') {
      padreCtrl.clearValidators();
      padreCtrl.setValue(null);
    } else {
      padreCtrl.setValidators(Validators.required);
    }
    padreCtrl.updateValueAndValidity();
  }

  guardar() {
    if (this.form.invalid) return;
    this.saving.set(true);
    const v = this.form.value;
    const sedeId = this.auth.currentUser()!.sedeId;
    const fecha = (v.fechaAsignacion as Date).toISOString().split('T')[0];

    const op = this.data.lider
      ? this.svc.update(this.data.lider.liderId, {
          nivel: v.nivel as NivelLider,
          liderPadreId: v.liderPadreId ?? null,
          zonaOCelula: v.zonaOCelula!,
          motivoCambio: v.motivoCambio!,
        })
      : this.svc.create({
          sedeId,
          miembroId: v.miembroId!,
          nivel: v.nivel as NivelLider,
          liderPadreId: v.liderPadreId ?? null,
          zonaOCelula: v.zonaOCelula!,
          fechaAsignacion: fecha,
          motivoCambio: v.motivoCambio ?? undefined,
        });

    op.subscribe({
      next: (result) => { this.saving.set(false); this.dialogRef.close(result); },
      error: () => this.saving.set(false),
    });
  }
}
