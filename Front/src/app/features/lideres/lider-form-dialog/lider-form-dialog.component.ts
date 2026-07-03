import { Component, inject, signal, OnInit } from '@angular/core';
import { NgClass } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { DynamicDialogRef, DynamicDialogConfig } from 'primeng/dynamicdialog';
import { FloatLabelModule } from 'primeng/floatlabel';
import { InputTextModule } from 'primeng/inputtext';
import { TextareaModule } from 'primeng/textarea';
import { SelectModule } from 'primeng/select';
import { ButtonModule } from 'primeng/button';
import { DatePickerModule } from 'primeng/datepicker';
import { ProgressSpinnerModule } from 'primeng/progressspinner';
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
    NgClass, ReactiveFormsModule,
    FloatLabelModule, InputTextModule, TextareaModule, SelectModule,
    ButtonModule, DatePickerModule, ProgressSpinnerModule,
  ],
  template: `
    <h2>
      <i class="pi" [ngClass]="data.lider ? 'pi-pencil' : 'pi-user-plus'"></i>
      {{ data.lider ? 'Editar Líder' : 'Nuevo Líder' }}
    </h2>

    <div class="dialog-content">
      <form [formGroup]="form" class="form-grid">

        <div class="field">
          <p-floatlabel variant="on">
            <p-select inputId="nivel" formControlName="nivel" [options]="opcionesNivel" optionLabel="label" optionValue="value" (onChange)="onNivelChange()" />
            <label for="nivel">Nivel de liderazgo</label>
          </p-floatlabel>
        </div>

        @if (form.value.nivel && form.value.nivel !== 'Pastor') {
          <div class="field">
            <p-floatlabel variant="on">
              <p-select inputId="liderPadreId" formControlName="liderPadreId" [options]="opcionesPadres" optionLabel="label" optionValue="value" />
              <label for="liderPadreId">{{ form.value.nivel === 'LiderZona' ? 'Pastor a cargo' : 'Líder de zona' }}</label>
            </p-floatlabel>
          </div>
        }

        <div class="field">
          <p-floatlabel variant="on">
            <input pInputText id="zonaOCelula" formControlName="zonaOCelula" placeholder="Ej: Zona Norte / Célula Esperanza" />
            <label for="zonaOCelula">{{ nivelLabel }} (nombre)</label>
          </p-floatlabel>
        </div>

        @if (!data.lider) {
          <div class="field">
            <p-floatlabel variant="on">
              <p-select inputId="miembroId" formControlName="miembroId" [options]="opcionesMiembros" optionLabel="label" optionValue="value" />
              <label for="miembroId">Miembro a designar</label>
            </p-floatlabel>
          </div>
        }

        <div class="field">
          <p-floatlabel variant="on">
            <p-datepicker inputId="fechaAsignacion" formControlName="fechaAsignacion" dateFormat="yy-mm-dd" [showIcon]="true" />
            <label for="fechaAsignacion">Fecha de asignación</label>
          </p-floatlabel>
        </div>

        <div class="field full-width">
          <p-floatlabel variant="on">
            <textarea pTextarea id="motivoCambio" formControlName="motivoCambio" rows="2"
                      placeholder="Ej: Crecimiento de la célula, nueva zona, etc."></textarea>
            <label for="motivoCambio">{{ data.lider ? 'Motivo del cambio' : 'Motivo de designación' }}</label>
          </p-floatlabel>
        </div>

      </form>
    </div>

    <div class="dialog-actions">
      <button pButton [text]="true" (click)="ref.close()">Cancelar</button>
      <button pButton (click)="guardar()" [disabled]="form.invalid || saving()">
        @if (saving()) {
          <p-progressSpinner [style]="{ width: '18px', height: '18px' }" strokeWidth="6" />
        } @else {
          <i class="pi pi-save"></i>
        }
        {{ data.lider ? 'Guardar cambios' : 'Designar líder' }}
      </button>
    </div>
  `,
  styles: [`
    h2 { display: flex; align-items: center; gap: 8px; margin: 0 0 4px; }
    .dialog-content { padding-top: 16px; min-width: min(480px, 90vw); }
    .form-grid { display: flex; flex-direction: column; gap: 20px; }
    .field { width: 100%; }
    .field.full-width { width: 100%; }
    p-select, p-datepicker, input, textarea { width: 100%; }
    .dialog-actions { display: flex; justify-content: flex-end; gap: 8px; padding: 16px 0 0; }
  `],
})
export class LiderFormDialogComponent implements OnInit {
  private fb         = inject(FormBuilder);
  private svc        = inject(LideresService);
  private auth       = inject(AuthService);
  private miembroSvc = inject(MiembrosService);
  readonly ref = inject(DynamicDialogRef);
  readonly config = inject(DynamicDialogConfig<LiderFormData>);
  readonly data: LiderFormData = this.config.data!;

  saving   = signal(false);
  lideres  = signal<Lider[]>([]);
  miembrosDisponibles = signal<MiembroList[]>([]);

  opcionesNivel = [
    { label: 'Pastor', value: 'Pastor' },
    { label: 'Líder de Zona', value: 'LiderZona' },
    { label: 'Líder de Célula', value: 'LiderCelula' },
  ];

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

  get opcionesPadres() {
    return this.padresDisponibles().map(p => ({
      label: `${p.nombre} ${p.apellido} — ${p.zonaOCelula}`,
      value: p.liderId,
    }));
  }

  get opcionesMiembros() {
    return this.miembrosDisponibles().map(m => ({ label: `${m.nombre} ${m.apellido}`, value: m.miembroId }));
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
      next: (result) => { this.saving.set(false); this.ref.close(result); },
      error: () => this.saving.set(false),
    });
  }
}
