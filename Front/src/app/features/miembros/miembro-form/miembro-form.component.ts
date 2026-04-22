import { Component, inject, signal, OnInit } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MiembrosService } from '../../../core/auth/miembros.service';
import { AuthService } from '../../../core/auth/auth.service';

@Component({
  selector: 'app-miembro-form',
  standalone: true,
  imports: [
    RouterLink, ReactiveFormsModule,
    MatCardModule, MatFormFieldModule, MatInputModule, MatSelectModule,
    MatButtonModule, MatIconModule, MatProgressSpinnerModule,
    MatDatepickerModule, MatNativeDateModule,
  ],
  templateUrl: './miembro-form.component.html',
  styleUrl: './miembro-form.component.css',
})
export class MiembroFormComponent implements OnInit {
  private svc = inject(MiembrosService);
  private auth = inject(AuthService);
  private router = inject(Router);
  private route = inject(ActivatedRoute);
  private fb = inject(FormBuilder);

  loading = signal(false);
  saving = signal(false);
  isEdit = signal(false);
  miembroId = signal<number | null>(null);

  estados = ['Visitante', 'Activo', 'Inactivo', 'Lider'];

  form = this.fb.group({
    nombre: ['', Validators.required],
    apellido: ['', Validators.required],
    cedula: [''],
    fechaNacimiento: [null as Date | null],
    telefono: [''],
    email: ['', Validators.email],
    direccion: [''],
    estado: ['Visitante', Validators.required],
    liderCelulaId: [null as number | null],
    notas: [''],
  });

  ngOnInit() {
    const id = this.route.snapshot.params['id'];
    if (id && !isNaN(+id)) {
      this.isEdit.set(true);
      this.miembroId.set(+id);
      this.loading.set(true);
      this.svc.getById(+id).subscribe({
        next: (m) => {
          this.form.patchValue({
            ...m,
            fechaNacimiento: m.fechaNacimiento ? new Date(m.fechaNacimiento) : null,
          });
          this.loading.set(false);
        },
        error: () => this.loading.set(false),
      });
    }
  }

  submit() {
    if (this.form.invalid) return;
    this.saving.set(true);
    const sedeId = this.auth.currentUser()!.sedeId;
    const value = this.form.value;
    const payload = {
      sedeId,
      nombre: value.nombre!,
      apellido: value.apellido!,
      cedula: value.cedula || undefined,
      fechaNacimiento: value.fechaNacimiento
        ? (value.fechaNacimiento as Date).toISOString().split('T')[0]
        : undefined,
      telefono: value.telefono || undefined,
      email: value.email || undefined,
      direccion: value.direccion || undefined,
      estado: value.estado as any,
      liderCelulaId: value.liderCelulaId || undefined,
      notas: value.notas || undefined,
    };

    const op = this.isEdit()
      ? this.svc.update(this.miembroId()!, payload)
      : this.svc.create(payload);

    op.subscribe({
      next: () => this.router.navigate(['/miembros']),
      error: () => this.saving.set(false),
    });
  }
}
