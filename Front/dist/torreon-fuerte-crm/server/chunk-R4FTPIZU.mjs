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

// src/app/features/sedes/sedes.component.ts
var SedesComponent = class _SedesComponent {
  static \u0275fac = function SedesComponent_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _SedesComponent)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({ type: _SedesComponent, selectors: [["app-sedes"]], decls: 8, vars: 0, consts: [[1, "placeholder"]], template: function SedesComponent_Template(rf, ctx) {
    if (rf & 1) {
      \u0275\u0275elementStart(0, "h2");
      \u0275\u0275text(1, "Gesti\xF3n de Sedes");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(2, "mat-card")(3, "mat-card-content", 0)(4, "mat-icon");
      \u0275\u0275text(5, "church");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(6, "p");
      \u0275\u0275text(7, "M\xF3dulo de sedes \u2014 pr\xF3ximamente");
      \u0275\u0275elementEnd()()();
    }
  }, dependencies: [MatCardModule, MatCard, MatCardContent, MatIconModule, MatIcon], styles: ["\n\n.placeholder[_ngcontent-%COMP%] {\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  padding: 48px;\n  color: #999;\n}\nmat-icon[_ngcontent-%COMP%] {\n  font-size: 48px;\n  width: 48px;\n  height: 48px;\n}\n/*# sourceMappingURL=sedes.component.css.map */"] });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(SedesComponent, [{
    type: Component,
    args: [{ selector: "app-sedes", standalone: true, imports: [MatCardModule, MatIconModule], template: `
    <h2>Gesti\xF3n de Sedes</h2>
    <mat-card>
      <mat-card-content class="placeholder">
        <mat-icon>church</mat-icon>
        <p>M\xF3dulo de sedes \u2014 pr\xF3ximamente</p>
      </mat-card-content>
    </mat-card>
  `, styles: ["/* angular:styles/component:css;9e215009cb5622055a29db7c50b9fa7ba2b61c0dd388f2a5668fa50b7d4309d0;c:/WorkSpace/TorreonFuerte-CRM/src/app/features/sedes/sedes.component.ts */\n.placeholder {\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  padding: 48px;\n  color: #999;\n}\nmat-icon {\n  font-size: 48px;\n  width: 48px;\n  height: 48px;\n}\n/*# sourceMappingURL=sedes.component.css.map */\n"] }]
  }], null, null);
})();
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && \u0275setClassDebugInfo(SedesComponent, { className: "SedesComponent", filePath: "src/app/features/sedes/sedes.component.ts", lineNumber: 20 });
})();
export {
  SedesComponent
};
//# sourceMappingURL=chunk-R4FTPIZU.mjs.map
