import { Component, inject, signal, computed, OnInit } from '@angular/core';
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
    ReactiveFormsModule, NgClass,
    FloatLabelModule, InputTextModule, TextareaModule, SelectModule,
    ButtonModule, DatePickerModule, ProgressSpinnerModule,
  ],
  template: `
    <h2>
      <i class="pi" [ngClass]="data.evento ? 'pi-calendar' : 'pi-calendar-plus'"></i>
      {{ data.evento ? 'Editar Evento' : 'Nuevo Evento' }}
    </h2>

    <div class="dialog-content">
      <form [formGroup]="form" class="form-body">

        <div class="field full">
          <p-floatlabel variant="on">
            <input pInputText id="nombre" formControlName="nombre" />
            <label for="nombre">Nombre del evento</label>
          </p-floatlabel>
        </div>

        <div class="row-2">
          <div class="field">
            <p-floatlabel variant="on">
              <p-select inputId="tipo" formControlName="tipo" [options]="tipos"
                        optionLabel="label" optionValue="value" />
              <label for="tipo">Tipo</label>
            </p-floatlabel>
          </div>

          <div class="field">
            <p-floatlabel variant="on">
              <p-select inputId="responsable" formControlName="responsable" [options]="responsables()"
                        optionLabel="label" optionValue="value" />
              <label for="responsable">Responsable</label>
            </p-floatlabel>
          </div>
        </div>

        <div class="row-2">
          <div class="field">
            <p-floatlabel variant="on">
              <p-datepicker inputId="fecha" formControlName="fecha" dateFormat="yy-mm-dd" [showIcon]="true" />
              <label for="fecha">Fecha de inicio</label>
            </p-floatlabel>
          </div>

          <div class="field">
            <p-floatlabel variant="on">
              <p-datepicker inputId="fechaFin" formControlName="fechaFin" dateFormat="yy-mm-dd" [showIcon]="true" />
              <label for="fechaFin">Fecha de fin (opcional)</label>
            </p-floatlabel>
          </div>
        </div>

        <div class="row-2">
          <div class="field">
            <p-floatlabel variant="on">
              <input pInputText id="horaInicio" formControlName="horaInicio" type="time" />
              <label for="horaInicio">Hora de inicio</label>
            </p-floatlabel>
          </div>

          <div class="field">
            <p-floatlabel variant="on">
              <input pInputText id="horaFin" formControlName="horaFin" type="time" />
              <label for="horaFin">Hora de fin</label>
            </p-floatlabel>
          </div>
        </div>

        <div class="field full">
          <p-floatlabel variant="on">
            <input pInputText id="lugar" formControlName="lugar" />
            <label for="lugar">Lugar</label>
          </p-floatlabel>
        </div>

        <div class="field full">
          <p-floatlabel variant="on">
            <input pInputText id="maxInscritos" formControlName="maxInscritos" type="number" min="1" />
            <label for="maxInscritos">Cupo máximo (opcional)</label>
          </p-floatlabel>
        </div>

        <div class="field full">
          <p-floatlabel variant="on">
            <textarea pTextarea id="descripcion" formControlName="descripcion" rows="3"></textarea>
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
        {{ data.evento ? 'Guardar cambios' : 'Crear evento' }}
      </button>
    </div>
  `,
  styles: [`
    h2 { display:flex; align-items:center; gap:8px; margin: 0; padding: 20px 20px 0; font-size: 18px; font-weight: 700; color: #0f172a; }
    .dialog-content { padding: 16px 20px 0; min-width: min(560px, 92vw); }
    .form-body       { display:flex; flex-direction:column; gap:16px; }
    .field           { display: flex; flex-direction: column; }
    .field.full      { width:100%; }
    .field p-floatlabel, .field p-select, .field input, .field textarea { width: 100%; }
    .row-2           { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .dialog-actions  { display:flex; justify-content:flex-end; gap:8px; padding:16px 20px; }
    @media(max-width:520px){ .row-2{ grid-template-columns:1fr; } }
  `],
})
export class EventoFormDialogComponent implements OnInit {
  private fb        = inject(FormBuilder);
  private svc       = inject(EventosService);
  private auth      = inject(AuthService);
  private miembroSvc = inject(MiembrosService);
  readonly ref    = inject(DynamicDialogRef);
  readonly config = inject(DynamicDialogConfig<EventoFormData>);
  readonly data: EventoFormData = this.config.data;

  saving   = signal(false);
  miembros = signal<MiembroList[]>([]);

  tipos = Object.entries(EVENTO_TIPO_META).map(([value, m]) => ({
    value: value as EventoTipo, label: m.label, icono: m.icono,
  }));

  responsables = computed(() =>
    this.miembros().map(m => ({
      value: `${m.nombre} ${m.apellido}`,
      label: `${m.nombre} ${m.apellido} · ${m.estado}`,
    }))
  );

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
      next: r  => { this.saving.set(false); this.ref.close(r); },
      error: () => this.saving.set(false),
    });
  }
}
