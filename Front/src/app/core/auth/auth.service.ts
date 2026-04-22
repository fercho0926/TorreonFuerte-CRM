import { Injectable, inject, signal, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { tap } from 'rxjs';
import { LoginRequest, LoginResponse, Usuario } from '../models/auth.models';
import { environment } from '../../../environments/environment';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);
  private isBrowser = isPlatformBrowser(inject(PLATFORM_ID));

  private readonly TOKEN_KEY = 'tf_token';
  private readonly USER_KEY = 'tf_user';

  currentUser = signal<Usuario | null>(this.loadUser());

  login(request: LoginRequest) {
    return this.http.post<LoginResponse>(`${environment.apiUrl}/auth/login`, request).pipe(
      tap((res) => {
        if (this.isBrowser) {
          localStorage.setItem(this.TOKEN_KEY, res.token);
          const user: Usuario = {
            email: res.email,
            nombre: res.nombre,
            apellido: res.apellido,
            rol: res.rol,
            sedeId: res.sedeId,
            sedeNombre: res.sedeNombre,
          };
          localStorage.setItem(this.USER_KEY, JSON.stringify(user));
          this.currentUser.set(user);
        }
      })
    );
  }

  logout() {
    if (this.isBrowser) {
      localStorage.removeItem(this.TOKEN_KEY);
      localStorage.removeItem(this.USER_KEY);
    }
    this.currentUser.set(null);
    this.router.navigate(['/login']);
  }

  getToken(): string | null {
    return this.isBrowser ? localStorage.getItem(this.TOKEN_KEY) : null;
  }

  isAuthenticated(): boolean {
    return !!this.getToken();
  }

  private loadUser(): Usuario | null {
    if (!this.isBrowser) return null;
    try {
      const raw = localStorage.getItem(this.USER_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch {
      return null;
    }
  }
}
