import { Component, Input, Output, EventEmitter, signal, OnInit } from '@angular/core';
import { NgClass } from '@angular/common';
import { ButtonModule } from 'primeng/button';
import { TooltipModule } from 'primeng/tooltip';
import { LiderNodo, MiembroNodo } from '../../../core/models/lider.models';

export interface NodoAccion {
  tipo: 'editar' | 'agregar' | 'eliminar' | 'verDetalle';
  nodo: LiderNodo;
}

@Component({
  selector: 'app-nodo-arbol',
  standalone: true,
  imports: [NgClass, ButtonModule, TooltipModule, NodoArbolComponent],
  template: `
    <div class="tree-wrap">

      <!-- ── Fila del nodo ── -->
      <div class="node-row" [ngClass]="cls">
        <!-- Indent -->
        <span class="indent" [style.width.px]="depth * 28"></span>

        <!-- Toggle -->
        <button class="toggle-btn"
                [class.hidden]="!hasChildren"
                (click)="expandido.set(!expandido())">
          <i class="pi" [ngClass]="expandido() ? 'pi-chevron-down' : 'pi-chevron-right'"></i>
        </button>

        <!-- Avatar -->
        <div class="node-avatar" [ngClass]="cls">{{ iniciales }}</div>

        <!-- Info -->
        <div class="node-info">
          <span class="node-nombre">{{ nodo.nombre }} {{ nodo.apellido }}</span>
          <span class="node-zona">{{ nodo.zonaOCelula }}</span>
        </div>

        <!-- Nivel badge — oculto en mobile -->
        <span class="nivel-badge" [ngClass]="cls">{{ nivelLabel }}</span>

        <!-- Conteo -->
        <span class="count-pill">
          <i class="pi pi-users"></i>{{ nodo.totalMiembros }}
        </span>

        <!-- Acciones -->
        <div class="node-actions">
          <button pButton [text]="true" [rounded]="true" pTooltip="Ver ficha" (click)="emitir('verDetalle')"><i class="pi pi-user"></i></button>
          @if (nodo.nivel !== 'LiderCelula') {
            <button pButton [text]="true" [rounded]="true" pTooltip="Agregar sub-líder" (click)="emitir('agregar')"><i class="pi pi-user-plus"></i></button>
          }
          <button pButton [text]="true" [rounded]="true" pTooltip="Editar" (click)="emitir('editar')"><i class="pi pi-pencil"></i></button>
          @if (nodo.nivel !== 'Pastor') {
            <button pButton [text]="true" [rounded]="true" pTooltip="Eliminar" (click)="emitir('eliminar')" class="btn-del"><i class="pi pi-trash"></i></button>
          }
        </div>
      </div>

      <!-- ── Hijos ── -->
      @if (expandido()) {
        @for (hijo of nodo.subLideres; track hijo.liderId) {
          <app-nodo-arbol [nodo]="hijo" [depth]="depth + 1" (accion)="accion.emit($event)" />
        }
        @for (m of nodo.miembros ?? []; track m.miembroId) {
          <div class="member-row">
            <span class="indent" [style.width.px]="(depth + 1) * 28"></span>
            <span class="member-dot"></span>
            <div class="member-avatar" [ngClass]="miembroEstadoCls(m.estado)">
              {{ miembroIniciales(m) }}
            </div>
            <div class="member-info">
              <span class="member-nombre">{{ m.nombre }} {{ m.apellido }}</span>
              @if (m.etapaActual) {
                <span class="member-etapa">{{ m.etapaActual }}</span>
              }
            </div>
            <span class="member-estado" [ngClass]="miembroEstadoCls(m.estado)">
              {{ m.estado }}
            </span>
          </div>
        }
      }

    </div>
  `,
  styles: [`
    /* ── Wrap ──────────────────────────────────────────────────── */
    .tree-wrap { display: flex; flex-direction: column; }

    /* ── Node row ───────────────────────────────────────────────── */
    .node-row {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 7px 10px 7px 0;
      border-radius: 8px;
      transition: background 0.12s;
      min-height: 52px;
      cursor: default;
      border-left: 3px solid transparent;
      position: relative;
    }

    .node-row:hover { background: #f0f9ff; }

    .node-row.pastor { border-left-color: #7c3aed; }
    .node-row.zona   { border-left-color: #1199c5; }
    .node-row.celula { border-left-color: #059669; }

    .indent { flex-shrink: 0; }

    /* ── Toggle ─────────────────────────────────────────────────── */
    .toggle-btn {
      width: 28px; height: 28px;
      border-radius: 6px;
      border: none;
      background: transparent;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      color: #94a3b8;
      flex-shrink: 0;
      transition: background 0.12s, color 0.12s;
      padding: 0;
    }
    .toggle-btn:hover { background: #e0f2fe; color: #0284c7; }
    .toggle-btn i { font-size: 18px; width: 18px; height: 18px; }
    .toggle-btn.hidden { visibility: hidden; pointer-events: none; }

    /* ── Avatar ─────────────────────────────────────────────────── */
    .node-avatar {
      width: 36px; height: 36px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.76rem; font-weight: 800;
      flex-shrink: 0;
    }
    .node-avatar.pastor { background: #ede9fe; color: #7c3aed; }
    .node-avatar.zona   { background: #e0f2fe; color: #0284c7; }
    .node-avatar.celula { background: #d1fae5; color: #059669; }

    /* ── Info ───────────────────────────────────────────────────── */
    .node-info {
      flex: 1; min-width: 0;
      display: flex; flex-direction: column; gap: 1px;
    }
    .node-nombre {
      font-size: 0.86rem; font-weight: 700; color: #0f172a;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .node-zona {
      font-size: 0.72rem; color: #64748b;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* ── Nivel badge ────────────────────────────────────────────── */
    .nivel-badge {
      font-size: 0.6rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.04em;
      padding: 2px 8px; border-radius: 20px;
      white-space: nowrap; flex-shrink: 0;
    }
    .nivel-badge.pastor { background: #ede9fe; color: #7c3aed; }
    .nivel-badge.zona   { background: #e0f2fe; color: #0284c7; }
    .nivel-badge.celula { background: #d1fae5; color: #059669; }

    /* ── Count pill ─────────────────────────────────────────────── */
    .count-pill {
      display: inline-flex; align-items: center; gap: 3px;
      font-size: 0.75rem; font-weight: 600; color: #475569;
      background: #f1f5f9; padding: 3px 9px; border-radius: 20px;
      flex-shrink: 0;
    }
    .count-pill i { font-size: 13px; width: 13px; height: 13px; }

    /* ── Actions ────────────────────────────────────────────────── */
    .node-actions {
      display: flex; align-items: center; gap: 0;
      opacity: 0; transition: opacity 0.15s;
      flex-shrink: 0;
    }
    .node-row:hover .node-actions { opacity: 1; }

    .node-actions button {
      width: 30px !important; height: 30px !important;
      color: #94a3b8 !important;
    }
    .node-actions button:hover { color: #475569 !important; }
    .btn-del { color: #ef4444 !important; }

    /* ── Member row ─────────────────────────────────────────────── */
    .member-row {
      display: flex; align-items: center; gap: 8px;
      padding: 5px 10px 5px 0;
      border-radius: 8px;
      transition: background 0.12s;
      min-height: 38px;
    }
    .member-row:hover { background: #f0fdf4; }

    .member-dot {
      width: 5px; height: 5px; border-radius: 50%;
      background: #d1fae5; flex-shrink: 0;
    }

    .member-avatar {
      width: 28px; height: 28px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.62rem; font-weight: 800; flex-shrink: 0;
    }
    .mc-activo    { background: #d1fae5; color: #059669; }
    .mc-visitante { background: #fef3c7; color: #d97706; }
    .mc-inactivo  { background: #fee2e2; color: #dc2626; }

    .member-info {
      flex: 1; min-width: 0;
      display: flex; align-items: center; gap: 8px;
    }
    .member-nombre {
      font-size: 0.8rem; font-weight: 600; color: #374151;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .member-etapa {
      font-size: 0.66rem; color: #94a3b8;
      white-space: nowrap; flex-shrink: 0;
    }

    .member-estado {
      font-size: 0.6rem; font-weight: 700; text-transform: uppercase;
      padding: 2px 7px; border-radius: 20px; white-space: nowrap; flex-shrink: 0;
    }

    /* ── Mobile ─────────────────────────────────────────────────── */
    @media (max-width: 700px) {
      .nivel-badge { display: none; }
      .node-actions { opacity: 1; }
      .node-actions button { width: 26px !important; height: 26px !important; }
      .count-pill { display: none; }
    }
  `],
})
export class NodoArbolComponent implements OnInit {
  @Input() nodo!: LiderNodo;
  @Input() depth = 0;
  @Output() accion = new EventEmitter<NodoAccion>();

  expandido = signal(false);

  ngOnInit() {
    this.expandido.set(this.depth === 0);
  }

  get cls(): string {
    return { Pastor: 'pastor', LiderZona: 'zona', LiderCelula: 'celula' }[this.nodo.nivel] ?? 'celula';
  }

  get iniciales(): string {
    return `${this.nodo.nombre[0]}${this.nodo.apellido[0]}`.toUpperCase();
  }

  get nivelLabel(): string {
    return { Pastor: 'Pastor', LiderZona: 'Líder Zona', LiderCelula: 'Líder Célula' }[this.nodo.nivel] ?? this.nodo.nivel;
  }

  get hasChildren(): boolean {
    return this.nodo.subLideres.length > 0 || (this.nodo.miembros?.length ?? 0) > 0;
  }

  miembroIniciales(m: MiembroNodo): string {
    return `${m.nombre[0]}${m.apellido[0]}`.toUpperCase();
  }

  miembroEstadoCls(estado: string): string {
    return { Activo: 'mc-activo', Visitante: 'mc-visitante', Inactivo: 'mc-inactivo' }[estado] ?? 'mc-activo';
  }

  emitir(tipo: NodoAccion['tipo']) {
    this.accion.emit({ tipo, nodo: this.nodo });
  }
}
