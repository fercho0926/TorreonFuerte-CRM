import './polyfills.server.mjs';
import {
  MatCard,
  MatCardContent,
  MatCardModule
} from "./chunk-2HWDCYPT.mjs";
import {
  MatIcon,
  MatIconModule
} from "./chunk-BMEM75CN.mjs";
import "./chunk-HPMMNQDD.mjs";
import {
  Component,
  setClassMetadata,
  ɵsetClassDebugInfo,
  ɵɵdefineComponent,
  ɵɵelementEnd,
  ɵɵelementStart,
  ɵɵtext
} from "./chunk-UZ5PZMGS.mjs";
import "./chunk-T2KOBY73.mjs";

// src/app/features/etapas/etapas.component.ts
var EtapasComponent = class _EtapasComponent {
  static \u0275fac = function EtapasComponent_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _EtapasComponent)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({ type: _EtapasComponent, selectors: [["app-etapas"]], decls: 8, vars: 0, consts: [[1, "placeholder"]], template: function EtapasComponent_Template(rf, ctx) {
    if (rf & 1) {
      \u0275\u0275elementStart(0, "h2");
      \u0275\u0275text(1, "Plan de Preparaci\xF3n");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(2, "mat-card")(3, "mat-card-content", 0)(4, "mat-icon");
      \u0275\u0275text(5, "stairs");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(6, "p");
      \u0275\u0275text(7, "M\xF3dulo de etapas \u2014 pr\xF3ximamente");
      \u0275\u0275elementEnd()()();
    }
  }, dependencies: [MatCardModule, MatCard, MatCardContent, MatIconModule, MatIcon], styles: ["\n\n.placeholder[_ngcontent-%COMP%] {\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  padding: 48px;\n  color: #999;\n}\nmat-icon[_ngcontent-%COMP%] {\n  font-size: 48px;\n  width: 48px;\n  height: 48px;\n}\n/*# sourceMappingURL=etapas.component.css.map */"] });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(EtapasComponent, [{
    type: Component,
    args: [{ selector: "app-etapas", standalone: true, imports: [MatCardModule, MatIconModule], template: `
    <h2>Plan de Preparaci\xF3n</h2>
    <mat-card>
      <mat-card-content class="placeholder">
        <mat-icon>stairs</mat-icon>
        <p>M\xF3dulo de etapas \u2014 pr\xF3ximamente</p>
      </mat-card-content>
    </mat-card>
  `, styles: ["/* angular:styles/component:css;9e215009cb5622055a29db7c50b9fa7ba2b61c0dd388f2a5668fa50b7d4309d0;c:/WorkSpace/TorreonFuerte-CRM/src/app/features/etapas/etapas.component.ts */\n.placeholder {\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  padding: 48px;\n  color: #999;\n}\nmat-icon {\n  font-size: 48px;\n  width: 48px;\n  height: 48px;\n}\n/*# sourceMappingURL=etapas.component.css.map */\n"] }]
  }], null, null);
})();
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && \u0275setClassDebugInfo(EtapasComponent, { className: "EtapasComponent", filePath: "src/app/features/etapas/etapas.component.ts", lineNumber: 20 });
})();
export {
  EtapasComponent
};
//# sourceMappingURL=chunk-NVCMGY3P.mjs.map
