import { Component, inject, signal, computed, HostListener, OnInit, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser, NgClass } from '@angular/common';
import { RouterOutlet, RouterLink, RouterLinkActive, Router, NavigationEnd } from '@angular/router';
import { filter, map } from 'rxjs/operators';
import { toSignal } from '@angular/core/rxjs-interop';
import { TooltipModule } from 'primeng/tooltip';
import { ToastModule } from 'primeng/toast';
import { AuthService } from '../../../core/auth/auth.service';

interface NavItem {
  label: string;
  icon: string;
  route: string;
  roles?: string[];
}

@Component({
  selector: 'app-layout',
  standalone: true,
  imports: [
    RouterOutlet, RouterLink, RouterLinkActive, NgClass,
    TooltipModule, ToastModule,
  ],
  templateUrl: './layout.component.html',
  styleUrl: './layout.component.css',
})
export class LayoutComponent implements OnInit {
  auth       = inject(AuthService);
  private router     = inject(Router);
  private platformId = inject(PLATFORM_ID);

  navItems: NavItem[] = [
    { label: 'Dashboard',     icon: 'pi-th-large', route: '/dashboard' },
    { label: 'Miembros',      icon: 'pi-users',     route: '/miembros' },
    { label: 'Liderazgo',     icon: 'pi-sitemap',   route: '/lideres' },
    { label: 'Preparación',   icon: 'pi-sort-alt',  route: '/etapas' },
    { label: 'Eventos',       icon: 'pi-calendar',  route: '/eventos' },
    { label: 'Ministerios',   icon: 'pi-heart',     route: '/ministerios' },
    { label: 'Sedes',         icon: 'pi-building',  route: '/sedes',        roles: ['SuperAdmin'] },
    { label: 'Configuración', icon: 'pi-cog',       route: '/configuracion' },
  ];

  isMobile         = signal(false);
  sidenavOpen      = signal(true);
  sidenavCollapsed = signal(false);

  private currentUrl = toSignal(
    this.router.events.pipe(
      filter(e => e instanceof NavigationEnd),
      map((e: any) => e.urlAfterRedirects as string)
    ),
    { initialValue: this.router.url }
  );

  currentPageTitle = computed(() => {
    const url = this.currentUrl() ?? '';
    return this.navItems.find(n => url.startsWith(n.route))?.label ?? 'Torreón Fuerte';
  });

  currentPageIcon = computed(() => {
    const url = this.currentUrl() ?? '';
    return this.navItems.find(n => url.startsWith(n.route))?.icon ?? 'pi-shield';
  });

  get visibleItems() {
    const rol = this.auth.currentUser()?.rol;
    return this.navItems.filter(i => !i.roles || i.roles.includes(rol ?? ''));
  }

  get userInitials(): string {
    const u = this.auth.currentUser();
    return ((u?.nombre?.[0] ?? '') + (u?.apellido?.[0] ?? '')).toUpperCase();
  }

  ngOnInit() {
    if (isPlatformBrowser(this.platformId)) this.checkBreakpoint();
  }

  @HostListener('window:resize')
  onResize() { this.checkBreakpoint(); }

  private checkBreakpoint() {
    const mobile = window.innerWidth < 768;
    this.isMobile.set(mobile);
    if (mobile) {
      this.sidenavOpen.set(false);
      this.sidenavCollapsed.set(false);
    } else {
      this.sidenavOpen.set(true);
    }
  }

  toggleSidenav() {
    if (this.isMobile()) {
      this.sidenavOpen.set(!this.sidenavOpen());
    } else {
      this.sidenavCollapsed.set(!this.sidenavCollapsed());
    }
  }

  closeOnMobile() {
    if (this.isMobile()) this.sidenavOpen.set(false);
  }
}
