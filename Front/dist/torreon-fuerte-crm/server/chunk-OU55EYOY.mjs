import './polyfills.server.mjs';
import {
  MatList,
  MatListItem,
  MatListItemIcon,
  MatListItemLine,
  MatListItemTitle,
  MatListModule
} from "./chunk-R6VNOXRZ.mjs";
import "./chunk-TIGWNZFF.mjs";
import {
  MatProgressSpinner,
  MatProgressSpinnerModule
} from "./chunk-XR745ZVT.mjs";
import {
  MatCard,
  MatCardContent,
  MatCardHeader,
  MatCardModule,
  MatCardTitle
} from "./chunk-2HWDCYPT.mjs";
import "./chunk-N2W2VB4R.mjs";
import "./chunk-22AJVEKS.mjs";
import {
  MatIcon,
  MatIconModule
} from "./chunk-BMEM75CN.mjs";
import {
  AuthService,
  environment
} from "./chunk-2GCZFSIW.mjs";
import {
  DatePipe,
  HttpClient,
  HttpParams
} from "./chunk-HPMMNQDD.mjs";
import {
  Component,
  Injectable,
  inject,
  setClassMetadata,
  signal,
  ɵsetClassDebugInfo,
  ɵɵadvance,
  ɵɵconditional,
  ɵɵconditionalCreate,
  ɵɵdefineComponent,
  ɵɵdefineInjectable,
  ɵɵelement,
  ɵɵelementEnd,
  ɵɵelementStart,
  ɵɵnextContext,
  ɵɵpipe,
  ɵɵpipeBind2,
  ɵɵrepeater,
  ɵɵrepeaterCreate,
  ɵɵstyleProp,
  ɵɵtext,
  ɵɵtextInterpolate,
  ɵɵtextInterpolate1,
  ɵɵtextInterpolate2
} from "./chunk-UZ5PZMGS.mjs";
import "./chunk-T2KOBY73.mjs";

// src/app/core/auth/dashboard.service.ts
var DashboardService = class _DashboardService {
  http = inject(HttpClient);
  get(sedeId) {
    const params = new HttpParams().set("sedeId", sedeId);
    return this.http.get(`${environment.apiUrl}/dashboard`, { params });
  }
  static \u0275fac = function DashboardService_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _DashboardService)();
  };
  static \u0275prov = /* @__PURE__ */ \u0275\u0275defineInjectable({ token: _DashboardService, factory: _DashboardService.\u0275fac, providedIn: "root" });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DashboardService, [{
    type: Injectable,
    args: [{ providedIn: "root" }]
  }], null, null);
})();

// src/app/features/dashboard/dashboard.component.ts
var _forTrack0 = ($index, $item) => $item.etapa;
var _forTrack1 = ($index, $item) => $item.eventoId;
function DashboardComponent_Conditional_2_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "div", 0);
    \u0275\u0275element(1, "mat-spinner", 1);
    \u0275\u0275elementEnd();
  }
}
function DashboardComponent_Conditional_3_Conditional_39_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "p", 10);
    \u0275\u0275text(1, "Sin datos de etapas");
    \u0275\u0275elementEnd();
  }
}
function DashboardComponent_Conditional_3_Conditional_40_For_1_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "div", 11)(1, "span");
    \u0275\u0275text(2);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(3, "div", 12);
    \u0275\u0275element(4, "div", 13);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(5, "span", 14);
    \u0275\u0275text(6);
    \u0275\u0275elementEnd()();
  }
  if (rf & 2) {
    const e_r1 = ctx.$implicit;
    const d_r2 = \u0275\u0275nextContext(2);
    \u0275\u0275advance(2);
    \u0275\u0275textInterpolate(e_r1.etapa);
    \u0275\u0275advance(2);
    \u0275\u0275styleProp("width", e_r1.conteo / d_r2.totalMiembros * 100, "%");
    \u0275\u0275advance(2);
    \u0275\u0275textInterpolate(e_r1.conteo);
  }
}
function DashboardComponent_Conditional_3_Conditional_40_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275repeaterCreate(0, DashboardComponent_Conditional_3_Conditional_40_For_1_Template, 7, 4, "div", 11, _forTrack0);
  }
  if (rf & 2) {
    const d_r2 = \u0275\u0275nextContext();
    \u0275\u0275repeater(d_r2.miembrosPorEtapa);
  }
}
function DashboardComponent_Conditional_3_Conditional_46_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "p", 10);
    \u0275\u0275text(1, "Sin eventos pr\xF3ximos");
    \u0275\u0275elementEnd();
  }
}
function DashboardComponent_Conditional_3_Conditional_47_For_2_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "mat-list-item")(1, "mat-icon", 15);
    \u0275\u0275text(2, "event");
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(3, "span", 16);
    \u0275\u0275text(4);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(5, "span", 17);
    \u0275\u0275text(6);
    \u0275\u0275pipe(7, "date");
    \u0275\u0275elementEnd()();
  }
  if (rf & 2) {
    const ev_r3 = ctx.$implicit;
    \u0275\u0275advance(4);
    \u0275\u0275textInterpolate(ev_r3.nombre);
    \u0275\u0275advance(2);
    \u0275\u0275textInterpolate2("", \u0275\u0275pipeBind2(7, 3, ev_r3.fecha, "dd MMM yyyy"), " \xB7 ", ev_r3.tipo);
  }
}
function DashboardComponent_Conditional_3_Conditional_47_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "mat-list");
    \u0275\u0275repeaterCreate(1, DashboardComponent_Conditional_3_Conditional_47_For_2_Template, 8, 6, "mat-list-item", null, _forTrack1);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const d_r2 = \u0275\u0275nextContext();
    \u0275\u0275advance();
    \u0275\u0275repeater(d_r2.proximosEventos);
  }
}
function DashboardComponent_Conditional_3_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "div", 2)(1, "mat-card", 3)(2, "mat-card-content")(3, "mat-icon");
    \u0275\u0275text(4, "people");
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(5, "div", 4);
    \u0275\u0275text(6);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(7, "div", 5);
    \u0275\u0275text(8, "Total miembros");
    \u0275\u0275elementEnd()()();
    \u0275\u0275elementStart(9, "mat-card", 6)(10, "mat-card-content")(11, "mat-icon");
    \u0275\u0275text(12, "how_to_reg");
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(13, "div", 4);
    \u0275\u0275text(14);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(15, "div", 5);
    \u0275\u0275text(16, "Activos");
    \u0275\u0275elementEnd()()();
    \u0275\u0275elementStart(17, "mat-card", 7)(18, "mat-card-content")(19, "mat-icon");
    \u0275\u0275text(20, "person_add");
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(21, "div", 4);
    \u0275\u0275text(22);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(23, "div", 5);
    \u0275\u0275text(24, "Visitantes");
    \u0275\u0275elementEnd()()();
    \u0275\u0275elementStart(25, "mat-card", 8)(26, "mat-card-content")(27, "mat-icon");
    \u0275\u0275text(28, "supervisor_account");
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(29, "div", 4);
    \u0275\u0275text(30);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(31, "div", 5);
    \u0275\u0275text(32, "L\xEDderes");
    \u0275\u0275elementEnd()()()();
    \u0275\u0275elementStart(33, "div", 9)(34, "mat-card")(35, "mat-card-header")(36, "mat-card-title");
    \u0275\u0275text(37, "Embudo de preparaci\xF3n");
    \u0275\u0275elementEnd()();
    \u0275\u0275elementStart(38, "mat-card-content");
    \u0275\u0275conditionalCreate(39, DashboardComponent_Conditional_3_Conditional_39_Template, 2, 0, "p", 10)(40, DashboardComponent_Conditional_3_Conditional_40_Template, 2, 0);
    \u0275\u0275elementEnd()();
    \u0275\u0275elementStart(41, "mat-card")(42, "mat-card-header")(43, "mat-card-title");
    \u0275\u0275text(44, "Pr\xF3ximos eventos");
    \u0275\u0275elementEnd()();
    \u0275\u0275elementStart(45, "mat-card-content");
    \u0275\u0275conditionalCreate(46, DashboardComponent_Conditional_3_Conditional_46_Template, 2, 0, "p", 10)(47, DashboardComponent_Conditional_3_Conditional_47_Template, 3, 0, "mat-list");
    \u0275\u0275elementEnd()()();
  }
  if (rf & 2) {
    const d_r2 = ctx;
    \u0275\u0275advance(6);
    \u0275\u0275textInterpolate(d_r2.totalMiembros);
    \u0275\u0275advance(8);
    \u0275\u0275textInterpolate(d_r2.totalActivos);
    \u0275\u0275advance(8);
    \u0275\u0275textInterpolate(d_r2.totalVisitantes);
    \u0275\u0275advance(8);
    \u0275\u0275textInterpolate(d_r2.totalLideres);
    \u0275\u0275advance(9);
    \u0275\u0275conditional(d_r2.miembrosPorEtapa.length === 0 ? 39 : 40);
    \u0275\u0275advance(7);
    \u0275\u0275conditional(d_r2.proximosEventos.length === 0 ? 46 : 47);
  }
}
var DashboardComponent = class _DashboardComponent {
  svc = inject(DashboardService);
  auth = inject(AuthService);
  data = signal(null, ...ngDevMode ? [{ debugName: "data" }] : []);
  loading = signal(true, ...ngDevMode ? [{ debugName: "loading" }] : []);
  ngOnInit() {
    const sedeId = this.auth.currentUser().sedeId;
    this.svc.get(sedeId).subscribe({
      next: (d) => {
        this.data.set(d);
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }
  static \u0275fac = function DashboardComponent_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _DashboardComponent)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({ type: _DashboardComponent, selectors: [["app-dashboard"]], decls: 4, vars: 2, consts: [[1, "loading-center"], ["diameter", "40"], [1, "stats-grid"], [1, "stat-card", "stat-total"], [1, "stat-value"], [1, "stat-label"], [1, "stat-card", "stat-activos"], [1, "stat-card", "stat-visitantes"], [1, "stat-card", "stat-lideres"], [1, "bottom-grid"], [1, "empty-msg"], [1, "etapa-row"], [1, "etapa-bar-wrap"], [1, "etapa-bar"], [1, "etapa-num"], ["matListItemIcon", ""], ["matListItemTitle", ""], ["matListItemLine", ""]], template: function DashboardComponent_Template(rf, ctx) {
    if (rf & 1) {
      \u0275\u0275elementStart(0, "h2");
      \u0275\u0275text(1);
      \u0275\u0275elementEnd();
      \u0275\u0275conditionalCreate(2, DashboardComponent_Conditional_2_Template, 2, 0, "div", 0)(3, DashboardComponent_Conditional_3_Template, 48, 6);
    }
    if (rf & 2) {
      let tmp_0_0;
      let tmp_1_0;
      \u0275\u0275advance();
      \u0275\u0275textInterpolate1("Dashboard \u2014 ", (tmp_0_0 = ctx.auth.currentUser()) == null ? null : tmp_0_0.sedeNombre);
      \u0275\u0275advance();
      \u0275\u0275conditional(ctx.loading() ? 2 : (tmp_1_0 = ctx.data()) ? 3 : -1, tmp_1_0);
    }
  }, dependencies: [MatCardModule, MatCard, MatCardContent, MatCardHeader, MatCardTitle, MatIconModule, MatIcon, MatProgressSpinnerModule, MatProgressSpinner, MatListModule, MatList, MatListItem, MatListItemIcon, MatListItemLine, MatListItemTitle, DatePipe], styles: ["\n\nh2[_ngcontent-%COMP%] {\n  margin: 0 0 20px;\n  font-size: 22px;\n  font-weight: 500;\n}\n.stats-grid[_ngcontent-%COMP%] {\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));\n  gap: 16px;\n  margin-bottom: 24px;\n}\n.stat-card[_ngcontent-%COMP%]   mat-card-content[_ngcontent-%COMP%] {\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  padding: 20px !important;\n  text-align: center;\n}\n.stat-card[_ngcontent-%COMP%]   mat-icon[_ngcontent-%COMP%] {\n  font-size: 36px;\n  width: 36px;\n  height: 36px;\n  margin-bottom: 8px;\n}\n.stat-value[_ngcontent-%COMP%] {\n  font-size: 36px;\n  font-weight: 700;\n  line-height: 1;\n}\n.stat-label[_ngcontent-%COMP%] {\n  font-size: 13px;\n  color: #666;\n  margin-top: 4px;\n}\n.stat-total[_ngcontent-%COMP%] {\n  border-left: 4px solid #1a237e;\n}\n.stat-activos[_ngcontent-%COMP%] {\n  border-left: 4px solid #2e7d32;\n}\n.stat-visitantes[_ngcontent-%COMP%] {\n  border-left: 4px solid #1565c0;\n}\n.stat-lideres[_ngcontent-%COMP%] {\n  border-left: 4px solid #e65100;\n}\n.bottom-grid[_ngcontent-%COMP%] {\n  display: grid;\n  grid-template-columns: 1fr 1fr;\n  gap: 16px;\n}\n.etapa-row[_ngcontent-%COMP%] {\n  display: flex;\n  align-items: center;\n  gap: 12px;\n  margin-bottom: 10px;\n  font-size: 14px;\n}\n.etapa-row[_ngcontent-%COMP%]   span[_ngcontent-%COMP%]:first-child {\n  width: 120px;\n  flex-shrink: 0;\n}\n.etapa-bar-wrap[_ngcontent-%COMP%] {\n  flex: 1;\n  height: 10px;\n  background: #e0e0e0;\n  border-radius: 5px;\n  overflow: hidden;\n}\n.etapa-bar[_ngcontent-%COMP%] {\n  height: 100%;\n  background: #1a237e;\n  border-radius: 5px;\n  transition: width 0.6s ease;\n}\n.etapa-num[_ngcontent-%COMP%] {\n  width: 30px;\n  text-align: right;\n  font-weight: 500;\n}\n.empty-msg[_ngcontent-%COMP%] {\n  color: #999;\n  text-align: center;\n  padding: 20px 0;\n}\n.loading-center[_ngcontent-%COMP%] {\n  display: flex;\n  justify-content: center;\n  padding: 48px;\n}\n@media (max-width: 768px) {\n  .bottom-grid[_ngcontent-%COMP%] {\n    grid-template-columns: 1fr;\n  }\n}\n/*# sourceMappingURL=dashboard.component.css.map */"] });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DashboardComponent, [{
    type: Component,
    args: [{ selector: "app-dashboard", standalone: true, imports: [MatCardModule, MatIconModule, MatProgressSpinnerModule, MatListModule, DatePipe], template: `<h2>Dashboard \u2014 {{ auth.currentUser()?.sedeNombre }}</h2>

@if (loading()) {
  <div class="loading-center"><mat-spinner diameter="40" /></div>
} @else if (data(); as d) {
  <div class="stats-grid">
    <mat-card class="stat-card stat-total">
      <mat-card-content>
        <mat-icon>people</mat-icon>
        <div class="stat-value">{{ d.totalMiembros }}</div>
        <div class="stat-label">Total miembros</div>
      </mat-card-content>
    </mat-card>

    <mat-card class="stat-card stat-activos">
      <mat-card-content>
        <mat-icon>how_to_reg</mat-icon>
        <div class="stat-value">{{ d.totalActivos }}</div>
        <div class="stat-label">Activos</div>
      </mat-card-content>
    </mat-card>

    <mat-card class="stat-card stat-visitantes">
      <mat-card-content>
        <mat-icon>person_add</mat-icon>
        <div class="stat-value">{{ d.totalVisitantes }}</div>
        <div class="stat-label">Visitantes</div>
      </mat-card-content>
    </mat-card>

    <mat-card class="stat-card stat-lideres">
      <mat-card-content>
        <mat-icon>supervisor_account</mat-icon>
        <div class="stat-value">{{ d.totalLideres }}</div>
        <div class="stat-label">L\xEDderes</div>
      </mat-card-content>
    </mat-card>
  </div>

  <div class="bottom-grid">
    <mat-card>
      <mat-card-header>
        <mat-card-title>Embudo de preparaci\xF3n</mat-card-title>
      </mat-card-header>
      <mat-card-content>
        @if (d.miembrosPorEtapa.length === 0) {
          <p class="empty-msg">Sin datos de etapas</p>
        } @else {
          @for (e of d.miembrosPorEtapa; track e.etapa) {
            <div class="etapa-row">
              <span>{{ e.etapa }}</span>
              <div class="etapa-bar-wrap">
                <div class="etapa-bar" [style.width.%]="(e.conteo / d.totalMiembros) * 100"></div>
              </div>
              <span class="etapa-num">{{ e.conteo }}</span>
            </div>
          }
        }
      </mat-card-content>
    </mat-card>

    <mat-card>
      <mat-card-header>
        <mat-card-title>Pr\xF3ximos eventos</mat-card-title>
      </mat-card-header>
      <mat-card-content>
        @if (d.proximosEventos.length === 0) {
          <p class="empty-msg">Sin eventos pr\xF3ximos</p>
        } @else {
          <mat-list>
            @for (ev of d.proximosEventos; track ev.eventoId) {
              <mat-list-item>
                <mat-icon matListItemIcon>event</mat-icon>
                <span matListItemTitle>{{ ev.nombre }}</span>
                <span matListItemLine>{{ ev.fecha | date: 'dd MMM yyyy' }} \xB7 {{ ev.tipo }}</span>
              </mat-list-item>
            }
          </mat-list>
        }
      </mat-card-content>
    </mat-card>
  </div>
}
`, styles: ["/* src/app/features/dashboard/dashboard.component.css */\nh2 {\n  margin: 0 0 20px;\n  font-size: 22px;\n  font-weight: 500;\n}\n.stats-grid {\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));\n  gap: 16px;\n  margin-bottom: 24px;\n}\n.stat-card mat-card-content {\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  padding: 20px !important;\n  text-align: center;\n}\n.stat-card mat-icon {\n  font-size: 36px;\n  width: 36px;\n  height: 36px;\n  margin-bottom: 8px;\n}\n.stat-value {\n  font-size: 36px;\n  font-weight: 700;\n  line-height: 1;\n}\n.stat-label {\n  font-size: 13px;\n  color: #666;\n  margin-top: 4px;\n}\n.stat-total {\n  border-left: 4px solid #1a237e;\n}\n.stat-activos {\n  border-left: 4px solid #2e7d32;\n}\n.stat-visitantes {\n  border-left: 4px solid #1565c0;\n}\n.stat-lideres {\n  border-left: 4px solid #e65100;\n}\n.bottom-grid {\n  display: grid;\n  grid-template-columns: 1fr 1fr;\n  gap: 16px;\n}\n.etapa-row {\n  display: flex;\n  align-items: center;\n  gap: 12px;\n  margin-bottom: 10px;\n  font-size: 14px;\n}\n.etapa-row span:first-child {\n  width: 120px;\n  flex-shrink: 0;\n}\n.etapa-bar-wrap {\n  flex: 1;\n  height: 10px;\n  background: #e0e0e0;\n  border-radius: 5px;\n  overflow: hidden;\n}\n.etapa-bar {\n  height: 100%;\n  background: #1a237e;\n  border-radius: 5px;\n  transition: width 0.6s ease;\n}\n.etapa-num {\n  width: 30px;\n  text-align: right;\n  font-weight: 500;\n}\n.empty-msg {\n  color: #999;\n  text-align: center;\n  padding: 20px 0;\n}\n.loading-center {\n  display: flex;\n  justify-content: center;\n  padding: 48px;\n}\n@media (max-width: 768px) {\n  .bottom-grid {\n    grid-template-columns: 1fr;\n  }\n}\n/*# sourceMappingURL=dashboard.component.css.map */\n"] }]
  }], null, null);
})();
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && \u0275setClassDebugInfo(DashboardComponent, { className: "DashboardComponent", filePath: "src/app/features/dashboard/dashboard.component.ts", lineNumber: 18 });
})();
export {
  DashboardComponent
};
//# sourceMappingURL=chunk-OU55EYOY.mjs.map
