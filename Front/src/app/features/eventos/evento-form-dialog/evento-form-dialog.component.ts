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
import { MatCheckboxModule } from '@angular/material/checkbox';
import { EventosService } from '../../../core/auth/eventos.service';
import { AuthService } from '../../../core/auth/auth.service';
import { MiembrosService } from '../../../core/auth/miembros.service';
import { MiembroList } from '../../../core/models/miembro.models';
import { Evento, EventoTipo, EVENTO_TIPO_META } from '../../../core/models/evento.models';

export interface EventoFormData { evento?: Evento; }

@Component({
  selector: 'app-evento-form-dialog',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatDialogModule, MatFormFieldModule, MatInputModule, MatSelectModule,
    MatButtonModule, MatIconModule, MatDatepickerModule, MatNativeDateModule,
    MatProgressSpinnerModule, MatCheckboxModule,
  ],
  template: `
    <h2 mat-dialog-title>
      <mat-icon>{{ data.evento ? 'edit_calendar' : 'event_available' }}</mat-icon>
      {{ data.evento ? 'Editar Evento' : 'Nuevo Evento' }}
    </h2>

    <mat-dialog-content>
      <form [formGroup]="form" class="form-body">

        <mat-form-field appearance="outline" class="full">
          <mat-label>Nombre del evento</mat-label>
          <input matInput formControlName="nombre" placeholder="Ej: Retiro de Hombres 2026">
          <mat-icon matPrefix>title</mat-icon>
        </mat-form-field>

        <div class="row-2">
          <mat-form-field appearance="outline">
            <mat-label>Tipo</mat-label>
            <mat-select formControlName="tipo">
              @for (t of tipos; track t.value) {
                <mat-option [value]="t.value">
                  <mat-icon>{{ t.icono }}</mat-icon> {{ t.label }}
                </mat-option>
              }
            </mat-select>
            <mat-icon matPrefix>category</mat-icon>
          </mat-form-field>

          <mat-form-field appearance="outline">
            <mat-label>Responsable</mat-label>
            <mat-select formControlName="responsable">
              @for (m of miembros(); track m.miembroId) {
                <mat-option [value]="m.nombre + ' ' + m.apellido">
                  {{ m.nombre }} {{ m.apellido }}
                  <span style="font-size:11px;color:#94a3b8"> · {{ m.estado }}</span>
                </mat-option>
              }
            </mat-select>
            <mat-icon matPrefix>person</mat-icon>
          </mat-form-field>
        </div>

        <div class="row-2">
          <mat-form-field appearance="outline">
            <mat-label>Fecha de inicio</mat-label>
            <input matInput [matDatepicker]="dpInicio" formControlName="fecha">
            <mat-datepicker-toggle matSuffix [for]="dpInicio" />
            <mat-datepicker #dpInicio />
          </mat-form-field>

          <mat-form-field appearance="outline">
            <mat-label>Fecha de fin (opcional)</mat-label>
            <input matInput [matDatepicker]="dpFin" formControlName="fechaFin">
            <mat-datepicker-toggle matSuffix [for]="dpFin" />
            <mat-datepicker #dpFin />
          </mat-form-field>
        </div>

        <div class="row-2">
          <mat-form-field appearance="outline">
            <mat-label>Hora de inicio</mat-label>
            <input matInput formControlName="horaInicio" type="time">
            <mat-icon matPrefix>schedule</mat-icon>
          </mat-form-field>

          <mat-form-field appearance="outline">
            <mat-label>Hora de fin</mat-label>
            <input matInput formControlName="horaFin" type="time">
            <mat-icon matPrefix>schedule</mat-icon>
          </mat-form-field>
        </div>

        <mat-form-field appearance="outline" class="full">
          <mat-label>Lugar</mat-label>
          <input matInput formControlName="lugar" placeholder="Nombre del lugar y ciudad">
          <mat-icon matPrefix>place</mat-icon>
        </mat-form-field>

        <mat-form-field appearance="outline" class="full">
          <mat-label>Cupo máximo (opcional)</mat-label>
          <input matInput formControlName="maxInscritos" type="number" min="1"
                 placeholder="Dejar vacío si no hay límite">
          <mat-icon matPrefix>groups</mat-icon>
        </mat-form-field>

        <mat-form-field appearance="outline" class="full">
          <mat-label>Descripción</mat-label>
          <textarea matInput formControlName="descripcion" rows="3"
                    placeholder="Detalle del evento..."></textarea>
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
        {{ data.evento ? 'Guardar cambios' : 'Crear evento' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    h2[mat-dialog-title] { display:flex; align-items:center; gap:8px; }
    mat-dialog-content   { padding-top:16px !important; min-width: min(560px, 92vw); }
    .form-body           { display:flex; flex-direction:column; gap:6px; }
    .full                { width:100%; }
    .row-2               { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    mat-form-field       { width:100%; }
    mat-dialog-actions   { gap:8px; padding:16px !important; }
    @media(max-width:520px){ .row-2{ grid-template-columns:1fr; } }
  `],
})
export class EventoFormDialogComponent implements OnInit {
  private fb        = inject(FormBuilder);
  private svc       = inject(EventosService);
  private auth      = inject(AuthService);
  private miembroSvc = inject(MiembrosService);
  readonly dialogRef = inject(MatDialogRef<EventoFormDialogComponent>);
  readonly data: EventoFormData = inject(MAT_DIALOG_DATA);

  saving   = signal(false);
  miembros = signal<MiembroList[]>([]);

  tipos = Object.entries(EVENTO_TIPO_META).map(([value, m]) => ({
    value: value as EventoTipo, label: m.label, icono: m.icono,
  }));

  form = this.fb.group({
    nombre:       [this.data.evento?.nombre       ?? '', Validators.required],
    tipo:         [this.data.evento?.tipo          ?? 'Encuentro' as EventoTipo, Validators.required],
    responsable:  [this.data.evento?.responsable   ?? '', Validators.required],
    fecha:        [this.data.evento ? new Date(this.data.evento.fecha) : null as Date | null, Validators.required],
    fechaFin:     [this.data.evento?.fechaFin ? new Date(this.data.evento.fechaFin) : null as Date | null],
    horaInicio:   [this.data.evento?.horaInicio    ?? '09:00', Validators.required],
    horaFin:      [this.data.evento?.horaFin       ?? '17:00', Validators.required],
    lugar:        [this.data.evento?.lugar         ?? '', Validators.required],
    maxInscritos: [this.data.evento?.maxInscritos  ?? null as number | null],
    descripcion:  [this.data.evento?.descripcion   ?? '', Validators.required],
  });

  ngOnInit() {
    this.miembroSvc.getMock().subscribe(m => this.miembros.set(m));
  }

  guardar() {
    if (this.form.invalid) return;
    this.saving.set(true);
    const v = this.form.value;
    const sedeId = this.auth.currentUser()!.sedeId;
    const toDate = (d: Date | null | undefined) => d?.toISOString().split('T')[0];

    const payload = {
      sedeId,
      nombre:       v.nombre!,
      tipo:         v.tipo as EventoTipo,
      descripcion:  v.descripcion!,
      fecha:        toDate(v.fecha as Date)!,
      fechaFin:     toDate(v.fechaFin as Date | null) ?? undefined,
      horaInicio:   v.horaInicio!,
      horaFin:      v.horaFin!,
      lugar:        v.lugar!,
      maxInscritos: v.maxInscritos ?? undefined,
      responsable:  v.responsable!,
    };

    const op = this.data.evento
      ? this.svc.update(this.data.evento.eventoId, payload)
      : this.svc.create(payload);

    op.subscribe({
      next: r  => { this.saving.set(false); this.dialogRef.close(r); },
      error: () => this.saving.set(false),
    });
  }
}
