import { Component, inject, signal, OnInit } from '@angular/core';
import { NgClass } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { DynamicDialogRef, DynamicDialogConfig } from 'primeng/dynamicdialog';
import { FloatLabelModule } from 'primeng/floatlabel';
import { InputTextModule } from 'primeng/inputtext';
import { TextareaModule } from 'primeng/textarea';
import { SelectModule } from 'primeng/select';
import { ButtonModule } from 'primeng/button';
import { ProgressSpinnerModule } from 'primeng/progressspinner';
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
    ReactiveFormsModule, NgClass,
    FloatLabelModule, InputTextModule, TextareaModule, SelectModule,
    ButtonModule, ProgressSpinnerModule,
  ],
  template: `
    <h2>
      <i class="pi" [class.pi-pencil]="data.ministerio" [class.pi-plus-circle]="!data.ministerio"></i>
      {{ data.ministerio ? 'Editar ministerio' : 'Nuevo ministerio' }}
    </h2>

    <div class="dialog-content">
      <form [formGroup]="form" class="form-body">

        <div class="field">
          <p-floatlabel variant="on">
            <p-select inputId="tipo" formControlName="tipo" [options]="tipos" optionLabel="label" optionValue="value">
              <ng-template #selectedItem let-selected>
                @if (selected) {
                  <span class="select-item"><i class="pi" [ngClass]="selected.icono"></i> {{ selected.label }}</span>
                }
              </ng-template>
              <ng-template #item let-t>
                <span class="select-item"><i class="pi" [ngClass]="t.icono"></i> {{ t.label }}</span>
              </ng-template>
            </p-select>
            <label for="tipo">Tipo de ministerio</label>
          </p-floatlabel>
        </div>

        <div class="field">
          <p-floatlabel variant="on">
            <input pInputText id="nombre" formControlName="nombre" placeholder="Ej: Equipo de Alabanza Central" />
            <label for="nombre">Nombre del ministerio</label>
          </p-floatlabel>
        </div>

        <div class="field">
          <p-floatlabel variant="on">
            <p-select inputId="liderMiembroId" formControlName="liderMiembroId" [options]="miembros()"
                      optionLabel="nombre" optionValue="miembroId">
              <ng-template #selectedItem let-selected>
                @if (selected) {
                  <span>{{ selected.nombre }} {{ selected.apellido }}</span>
                }
              </ng-template>
              <ng-template #item let-m>
                <span>{{ m.nombre }} {{ m.apellido }} <span class="item-sub"> · {{ m.estado }}</span></span>
              </ng-template>
            </p-select>
            <label for="liderMiembroId">Líder del ministerio</label>
          </p-floatlabel>
        </div>

        <div class="field">
          <p-floatlabel variant="on">
            <textarea pTextarea id="descripcion" formControlName="descripcion" rows="3"
                      placeholder="¿Cuál es el propósito de este ministerio?"></textarea>
            <label for="descripcion">Descripción</label>
          </p-floatlabel>
        </div>

      </form>
    </div>

    <div class="dialog-actions">
      <button pButton [text]="true" (click)="ref.close()">Cancelar</button>
      <button pButton (click)="guardar()" [disabled]="form.invalid || saving()">
        @if (saving()) { <p-progressSpinner [style]="{ width: '18px', height: '18px' }" strokeWidth="6" /> }
        @else { <i class="pi pi-save"></i> }
        {{ data.ministerio ? 'Guardar cambios' : 'Crear ministerio' }}
      </button>
    </div>
  `,
  styles: [`
    h2 { display:flex; align-items:center; gap:8px; margin:0; padding:20px 20px 0; font-size:18px; font-weight:700; color:#0f172a; }
    .dialog-content { padding:16px 20px 0; min-width:min(480px,92vw); }
    .form-body { display:flex; flex-direction:column; gap:16px; }
    .field { display:flex; flex-direction:column; }
    .field, p-select, p-floatlabel { width:100%; }
    .select-item { display:flex; align-items:center; gap:8px; }
    .item-sub { font-size:11px; color:#94a3b8; }
    .dialog-actions { display:flex; justify-content:flex-end; gap:8px; padding:16px 20px 20px; }
  `],
})
export class MinisterioFormDialogComponent implements OnInit {
  private fb        = inject(FormBuilder);
  private svc       = inject(MinisteriosService);
  private miembroSvc = inject(MiembrosService);
  private auth      = inject(AuthService);
  readonly ref = inject(DynamicDialogRef);
  readonly config = inject(DynamicDialogConfig<MinisterioFormData>);
  readonly data: MinisterioFormData = this.config.data;

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
      next: r  => { this.saving.set(false); this.ref.close(r); },
      error: () => this.saving.set(false),
    });
  }
}
