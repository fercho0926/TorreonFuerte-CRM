import { Routes } from '@angular/router';
import { authGuard } from './core/auth/auth.guard';

export const routes: Routes = [
  {
    path: 'login',
    loadComponent: () =>
      import('./features/auth/login/login.component').then((m) => m.LoginComponent),
  },
  {
    path: '',
    loadComponent: () =>
      import('./shared/components/layout/layout.component').then((m) => m.LayoutComponent),
    canActivate: [authGuard],
    children: [
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
      {
        path: 'dashboard',
        loadComponent: () =>
          import('./features/dashboard/dashboard.component').then((m) => m.DashboardComponent),
      },
      {
        path: 'miembros',
        children: [
          {
            path: '',
            loadComponent: () =>
              import('./features/miembros/miembros-list/miembros-list.component').then(
                (m) => m.MiembrosListComponent
              ),
          },
          {
            path: 'nuevo',
            loadComponent: () =>
              import('./features/miembros/miembro-form/miembro-form.component').then(
                (m) => m.MiembroFormComponent
              ),
          },
          {
            path: ':id',
            loadComponent: () =>
              import('./features/miembros/miembro-detalle/miembro-detalle.component').then(
                (m) => m.MiembroDetalleComponent
              ),
          },
          {
            path: ':id/editar',
            loadComponent: () =>
              import('./features/miembros/miembro-form/miembro-form.component').then(
                (m) => m.MiembroFormComponent
              ),
          },
        ],
      },
      { path: 'lideres', loadComponent: () => import('./features/lideres/lideres.component').then(m => m.LideresComponent) },
      { path: 'etapas', loadComponent: () => import('./features/etapas/etapas.component').then(m => m.EtapasComponent) },
      { path: 'eventos', loadComponent: () => import('./features/eventos/eventos.component').then(m => m.EventosComponent) },
      { path: 'ministerios', loadComponent: () => import('./features/ministerios/ministerios.component').then(m => m.MinisteriosComponent) },
      { path: 'sedes', loadComponent: () => import('./features/sedes/sedes.component').then(m => m.SedesComponent) },
      { path: 'configuracion', loadComponent: () => import('./features/configuracion/configuracion.component').then(m => m.ConfiguracionComponent) },
    ],
  },
  { path: '**', redirectTo: '' },
];
