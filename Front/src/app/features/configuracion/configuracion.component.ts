import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatTabsModule } from '@angular/material/tabs';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatTableModule } from '@angular/material/table';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatDividerModule } from '@angular/material/divider';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatChipsModule } from '@angular/material/chips';
import { of } from 'rxjs';
import { delay } from 'rxjs/operators';
import { AuthService } from '../../core/auth/auth.service';

export interface UsuarioAdmin {
  id: number;
  nombre: string;
  apellido: string;
  email: string;
  rol: 'SuperAdmin' | 'Admin' | 'Pastor' | 'Lider';
  sedeId: number;
  sedeNombre: string;
  activo: boolean;
  ultimoAcceso: string;
}

const MOCK_USUARIOS: UsuarioAdmin[] = [
  { id: 1, nombre: 'Walter',  apellido: 'Pérez',   email: 'walter@torreonFuerte.com',  rol: 'SuperAdmin', sedeId: 1, sedeNombre: 'Central Medellín', activo: true,  ultimoAcceso: '2026-04-20T10:30:00' },
  { id: 2, nombre: 'Alba',    apellido: 'Pérez',   email: 'alba@torreonFuerte.com',     rol: 'Admin',      sedeId: 1, sedeNombre: 'Central Medellín', activo: true,  ultimoAcceso: '2026-04-19T15:45:00' },
  { id: 3, nombre: 'Camilo',  apellido: 'Pérez',   email: 'camilo@torreonFuerte.com',   rol: 'Pastor',     sedeId: 5, sedeNombre: 'Las Vegas',         activo: true,  ultimoAcceso: '2026-04-18T09:00:00' },
  { id: 4, nombre: 'Samuel',  apellido: 'Giraldo', email: 'samuel@torreonFuerte.com',   rol: 'Pastor',     sedeId: 7, sedeNombre: 'New York',           activo: true,  ultimoAcceso: '2026-04-17T14:20:00' },
  { id: 5, nombre: 'María',   apellido: 'Henao',   email: 'maria@torreonFuerte.com',    rol: 'Lider',      sedeId: 6, sedeNombre: 'Maine',              activo: false, ultimoAcceso: '2026-03-15T11:00:00' },
  { id: 6, nombre: 'Abel',    apellido: 'Sinitiva',email: 'abel@torreonFuerte.com',     rol: 'Pastor',     sedeId: 2, sedeNombre: 'Carambolas',         activo: true,  ultimoAcceso: '2026-04-16T08:30:00' },
  { id: 7, nombre: 'Julio',   apellido: 'Paniagua',email: 'julio@torreonFuerte.com',    rol: 'Pastor',     sedeId: 3, sedeNombre: 'Cocorná',            activo: true,  ultimoAcceso: '2026-04-15T16:00:00' },
];

@Component({
  selector: 'app-configuracion',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatTabsModule, MatCardModule, MatFormFieldModule, MatInputModule,
    MatButtonModule, MatIconModule, MatSelectModule, MatSlideToggleModule,
    MatTableModule, MatTooltipModule, MatDividerModule, MatProgressSpinnerModule,
    MatChipsModule,
  ],
  templateUrl: './configuracion.component.html',
  styleUrl: './configuracion.component.css',
})
export class ConfiguracionComponent implements OnInit {
  auth    = inject(AuthService);
  private fb    = inject(FormBuilder);
  private snack = inject(MatSnackBar);

  usuario = this.auth.currentUser;

  // ── Perfil ──────────────────────────────────────────────────────────────────
  guardandoPerfil   = signal(false);
  guardandoPassword = signal(false);

  perfilForm = this.fb.group({
    nombre:   [this.usuario()?.nombre   ?? '', Validators.required],
    apellido: [this.usuario()?.apellido ?? '', Validators.required],
  });

  passwordForm = this.fb.group({
    actual:    ['', Validators.required],
    nueva:     ['', [Validators.required, Validators.minLength(8)]],
    confirmar: ['', Validators.required],
  });

  // ── Usuarios ─────────────────────────────────────────────────────────────────
  usuarios          = signal<UsuarioAdmin[]>([]);
  cargandoUsuarios  = signal(true);
  columnas          = ['nombre', 'rol', 'sede', 'ultimoAcceso', 'activo', 'acciones'];
  roles: UsuarioAdmin['rol'][] = ['SuperAdmin', 'Admin', 'Pastor', 'Lider'];

  esAdminOSuperAdmin = computed(() => {
    const r = this.usuario()?.rol;
    return r === 'Admin' || r === 'SuperAdmin';
  });

  // ── Sede ─────────────────────────────────────────────────────────────────────
  guardandoSede = signal(false);

  sedeForm = this.fb.group({
    horarioDomingoAM:  ['9:00 AM',  Validators.required],
    horarioDomingoPM:  ['6:00 PM',  Validators.required],
    horarioMiercoles:  ['7:00 PM'],
    telefono:          ['+57 604 100 0001'],
    email:             ['central@torreonFuerte.com', Validators.email],
  });

  // ── Sistema ──────────────────────────────────────────────────────────────────
  notificaciones   = signal(true);
  guardandoSistema = signal(false);

  // ── Lifecycle ────────────────────────────────────────────────────────────────
  ngOnInit() {
    of(MOCK_USUARIOS).pipe(delay(400)).subscribe(u => {
      this.usuarios.set(u);
      this.cargandoUsuarios.set(false);
    });
  }

  // ── Helpers ──────────────────────────────────────────────────────────────────
  iniciales(): string {
    const u = this.usuario();
    if (!u) return '?';
    return `${u.nombre[0]}${u.apellido[0]}`.toUpperCase();
  }

  rolColor(rol: string): string {
    const map: Record<string, string> = {
      SuperAdmin: '#7c3aed', Admin: '#0284c7', Pastor: '#d97706', Lider: '#059669',
    };
    return map[rol] ?? '#64748b';
  }

  formatFecha(fecha: string): string {
    return new Date(fecha).toLocaleDateString('es', {
      day: '2-digit', month: 'short', year: 'numeric',
    });
  }

  // ── Acciones: Perfil ─────────────────────────────────────────────────────────
  guardarPerfil() {
    if (this.perfilForm.invalid) return;
    this.guardandoPerfil.set(true);
    setTimeout(() => {
      this.guardandoPerfil.set(false);
      this.snack.open('Perfil actualizado correctamente', 'Cerrar', { duration: 3000 });
    }, 800);
  }

  cambiarPassword() {
    const v = this.passwordForm.value;
    if (this.passwordForm.invalid) return;
    if (v.nueva !== v.confirmar) {
      this.snack.open('Las contraseñas nuevas no coinciden', 'Cerrar', { duration: 3000, panelClass: 'snack-error' });
      return;
    }
    this.guardandoPassword.set(true);
    setTimeout(() => {
      this.guardandoPassword.set(false);
      this.passwordForm.reset();
      this.snack.open('Contraseña actualizada correctamente', 'Cerrar', { duration: 3000 });
    }, 800);
  }

  // ── Acciones: Usuarios ────────────────────────────────────────────────────────
  toggleActivo(u: UsuarioAdmin) {
    this.usuarios.update(list =>
      list.map(x => x.id === u.id ? { ...x, activo: !x.activo } : x)
    );
    const estado = !u.activo ? 'activado' : 'desactivado';
    this.snack.open(`Usuario ${u.nombre} ${estado}`, 'Cerrar', { duration: 2500 });
  }

  cambiarRol(u: UsuarioAdmin, rol: UsuarioAdmin['rol']) {
    this.usuarios.update(list =>
      list.map(x => x.id === u.id ? { ...x, rol } : x)
    );
    this.snack.open(`Rol de ${u.nombre} actualizado a ${rol}`, 'Cerrar', { duration: 2500 });
  }

  enviarAcceso(u: UsuarioAdmin) {
    this.snack.open(`Email de acceso enviado a ${u.email}`, 'Cerrar', { duration: 3000 });
  }

  // ── Acciones: Sede ────────────────────────────────────────────────────────────
  guardarSede() {
    if (this.sedeForm.invalid) return;
    this.guardandoSede.set(true);
    setTimeout(() => {
      this.guardandoSede.set(false);
      this.snack.open('Configuración de sede guardada', 'Cerrar', { duration: 3000 });
    }, 800);
  }

  // ── Acciones: Sistema ─────────────────────────────────────────────────────────
  guardarSistema() {
    this.guardandoSistema.set(true);
    setTimeout(() => {
      this.guardandoSistema.set(false);
      this.snack.open('Preferencias guardadas', 'Cerrar', { duration: 3000 });
    }, 600);
  }
}
