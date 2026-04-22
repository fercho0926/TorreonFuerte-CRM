import { Component, inject, signal, OnInit, computed } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { DatePipe, DecimalPipe, PercentPipe } from '@angular/common';
import { DashboardService } from '../../core/auth/dashboard.service';
import { AuthService } from '../../core/auth/auth.service';
import { DashboardData } from '../../core/models/dashboard.models';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [MatIconModule, MatProgressSpinnerModule, DatePipe, DecimalPipe, PercentPipe, RouterLink],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css',
})
export class DashboardComponent implements OnInit {
  private svc = inject(DashboardService);
  auth = inject(AuthService);

  data    = signal<DashboardData | null>(null);
  loading = signal(true);
  today   = new Date();

  greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return 'Buenos días';
    if (h < 18) return 'Buenas tardes';
    return 'Buenas noches';
  });

  donutStyle = computed(() => {
    const d = this.data();
    if (!d || d.totalMiembros === 0) return '';
    const total  = d.totalMiembros;
    const actPct = (d.totalActivos    / total) * 360;
    const visPct = (d.totalVisitantes / total) * 360;
    const lidPct = (d.totalLideres    / total) * 360;
    const a2 = actPct;
    const v2 = a2 + visPct;
    const l2 = v2 + lidPct;
    return `conic-gradient(#10b981 0deg ${a2}deg,#f8c300 ${a2}deg ${v2}deg,#0077bd ${v2}deg ${l2}deg,#e5e7eb ${l2}deg 360deg)`;
  });

  maxEtapa = computed(() => {
    const d = this.data();
    if (!d || d.miembrosPorEtapa.length === 0) return 1;
    return Math.max(...d.miembrosPorEtapa.map(e => e.conteo));
  });

  ngOnInit() {
    const sedeId = this.auth.currentUser()!.sedeId;
    this.svc.get(sedeId).subscribe({
      next:  d  => { this.data.set(d); this.loading.set(false); },
      error: () => this.loading.set(false),
    });
  }

  daysUntil(dateStr: string): number {
    return Math.ceil((new Date(dateStr).getTime() - Date.now()) / 86400000);
  }
}
