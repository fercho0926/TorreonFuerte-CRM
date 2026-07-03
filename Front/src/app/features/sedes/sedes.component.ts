import {
  Component, inject, signal, computed, OnInit, OnDestroy,
  PLATFORM_ID, ViewChild, ElementRef, AfterViewInit,
} from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { CardModule } from 'primeng/card';
import { ButtonModule } from 'primeng/button';
import { TooltipModule } from 'primeng/tooltip';
import { ProgressSpinnerModule } from 'primeng/progressspinner';
import { DividerModule } from 'primeng/divider';
import { DialogService } from 'primeng/dynamicdialog';
import { SedesService } from '../../core/auth/sedes.service';
import { Sede, SedeStats } from '../../core/models/sede.models';
import { SedeDetalleDialogComponent } from './sede-detalle-dialog/sede-detalle-dialog.component';

@Component({
  selector: 'app-sedes',
  standalone: true,
  imports: [
    CardModule, ButtonModule, TooltipModule, ProgressSpinnerModule, DividerModule,
  ],
  templateUrl: './sedes.component.html',
  styleUrl: './sedes.component.css',
})
export class SedesComponent implements OnInit, AfterViewInit, OnDestroy {
  private svc        = inject(SedesService);
  private dialogService = inject(DialogService);
  private platformId = inject(PLATFORM_ID);

  @ViewChild('mapEl') mapEl!: ElementRef<HTMLDivElement>;

  sedes            = signal<Sede[]>([]);
  stats            = signal<SedeStats | null>(null);
  sedeSeleccionada = signal<Sede | null>(null);
  cargando         = signal(true);
  mapListo         = signal(false);

  // Leaflet instance (runtime-only — not typed at module level for SSR safety)
  private map: any = null;
  private markers: any[] = [];

  // Computed helpers
  sedesColombia = computed(() => this.sedes().filter(s => s.pais === 'Colombia'));
  sedesUSA      = computed(() => this.sedes().filter(s => s.pais === 'Estados Unidos'));

  ngOnInit() {
    this.svc.getAll().subscribe(s => { this.sedes.set(s); this.cargando.set(false); });
    this.svc.getStats().subscribe(s => this.stats.set(s));
  }

  ngAfterViewInit() {
    if (isPlatformBrowser(this.platformId)) {
      // Slight delay so the DOM is fully painted before Leaflet measures the container
      setTimeout(() => this.initMap(), 80);
    }
  }

  ngOnDestroy() {
    this.map?.remove();
  }

  // ── Map initialization ───────────────────────────────────────────────────────
  private async initMap() {
    const L = (await import('leaflet')).default;

    this.map = L.map(this.mapEl.nativeElement, {
      center: [20, -90],
      zoom: 4,
      minZoom: 3,
      maxZoom: 16,
      zoomControl: false,
      attributionControl: true,
    });

    L.control.zoom({ position: 'bottomright' }).addTo(this.map);

    L.tileLayer(
      'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
      { attribution: '&copy; <a href="https://carto.com/">CARTO</a>', subdomains: 'abcd', maxZoom: 19 }
    ).addTo(this.map);

    const tryPlaceMarkers = () => {
      const sedes = this.sedes();
      if (sedes.length > 0) {
        this.placeMarkers(L, sedes);
        this.mapListo.set(true);
      } else {
        setTimeout(tryPlaceMarkers, 100);
      }
    };
    tryPlaceMarkers();
  }

  private placeMarkers(L: any, sedes: Sede[]) {
    const bounds: [number, number][] = [];

    sedes.forEach((sede, i) => {
      const icon = L.divIcon({
        className: '',
        html: this.buildMarkerHtml(sede),
        iconSize: [20, 20],
        iconAnchor: [10, 10],
        popupAnchor: [0, -14],
      });

      const marker = L.marker([sede.lat, sede.lng], { icon })
        .addTo(this.map)
        .bindPopup(this.buildPopupHtml(sede), { maxWidth: 280, className: 'sede-popup' });

      // Animar el div interno — NO el elemento raíz que Leaflet usa para translate3d
      setTimeout(() => {
        const inner = marker.getElement()?.querySelector('.map-dot') as HTMLElement | null;
        if (!inner) return;
        inner.style.opacity = '0';
        inner.style.transform = 'scale(0.3)';
        inner.style.transition = 'opacity 0.4s ease, transform 0.4s cubic-bezier(.34,1.56,.64,1)';
        requestAnimationFrame(() => requestAnimationFrame(() => {
          inner.style.opacity = '1';
          inner.style.transform = 'scale(1)';
        }));
      }, 50 + i * 120);

      marker.on('click', () => {
        this.sedeSeleccionada.set(sede);
        this.highlightMarker(marker, sede, L);
      });

      this.markers.push({ marker, sede });
      bounds.push([sede.lat, sede.lng]);
    });

    // Defer fitBounds: espera a que el mapa calcule su tamaño real
    if (bounds.length > 0) {
      setTimeout(() => {
        this.map.invalidateSize();
        this.map.fitBounds(bounds, { padding: [60, 60], maxZoom: 5 });
      }, 200);
    }
  }

  private buildMarkerHtml(sede: Sede): string {
    return `<div class="map-dot" data-sede="${sede.sedeId}">
      <div class="dot-pulse"></div>
      <svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <circle cx="10" cy="10" r="7" fill="#e53935" stroke="white" stroke-width="2.5"/>
        <circle cx="10" cy="10" r="3" fill="white"/>
      </svg>
    </div>`;
  }

  private buildPopupHtml(sede: Sede): string {
    return `
      <div class="popup-content">
        <div class="popup-header" style="border-left:4px solid ${sede.color}">
          <span class="popup-flag">${sede.bandera}</span>
          <div>
            <strong class="popup-name">${sede.nombre}</strong>
            <span class="popup-lugar">${sede.ciudad}, ${sede.pais}</span>
          </div>
        </div>
        <div class="popup-stats">
          <div class="pstat"><span class="pnum">${sede.totalMiembros}</span><span class="plbl">miembros</span></div>
          <div class="pstat"><span class="pnum">${sede.totalCelulas}</span><span class="plbl">células</span></div>
          <div class="pstat"><span class="pnum">${sede.totalLideres}</span><span class="plbl">líderes</span></div>
        </div>
        <div class="popup-pastor">
          <span>&#127979;</span> ${sede.pastor}
        </div>
      </div>`;
  }

  private highlightMarker(clickedMarker: any, _sede: Sede | null, _L: any) {
    this.markers.forEach(({ marker }) => {
      const el = marker.getElement();
      if (el) el.classList.remove('marker-selected');
    });
    const el = clickedMarker.getElement();
    if (el) el.classList.add('marker-selected');
  }

  // ── Card interactions ────────────────────────────────────────────────────────
  flyTo(sede: Sede) {
    this.sedeSeleccionada.set(sede);
    if (this.map) {
      this.map.flyTo([sede.lat, sede.lng], 13, { duration: 1.4 });
      // Open popup for this sede
      const entry = this.markers.find(m => m.sede.sedeId === sede.sedeId);
      if (entry) {
        setTimeout(() => entry.marker.openPopup(), 1200);
        this.highlightMarker(entry.marker, null, null);
      }
    }
  }

  resetView() {
    this.sedeSeleccionada.set(null);
    if (this.map && this.sedes().length > 1) {
      const bounds = this.sedes().map(s => [s.lat, s.lng] as [number, number]);
      this.map.flyToBounds(bounds, { padding: [80, 80], maxZoom: 6, duration: 1.2 });
    }
    this.markers.forEach(({ marker }) => {
      const el = marker.getElement();
      if (el) el.classList.remove('marker-selected');
    });
  }

  abrirDetalle(sede: Sede, event: Event) {
    event.stopPropagation();
    this.dialogService.open(SedeDetalleDialogComponent, {
      data: { sede }, width: '600px', showHeader: false, dismissableMask: true,
    });
  }

  diasDesde(fecha: string): string {
    const d = Math.floor((Date.now() - new Date(fecha).getTime()) / 86400000);
    const y = Math.floor(d / 365);
    const m = Math.floor((d % 365) / 30);
    return y > 0 ? `${y} año${y > 1 ? 's' : ''} ${m > 0 ? m + 'm' : ''}` : `${m} mes${m > 1 ? 'es' : ''}`;
  }
}
