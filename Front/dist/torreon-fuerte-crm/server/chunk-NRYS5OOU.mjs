import './polyfills.server.mjs';
import {
  MatError,
  MatFormField,
  MatFormFieldModule,
  MatInput,
  MatInputModule,
  MatPrefix,
  MatSuffix
} from "./chunk-LQ4XYGPI.mjs";
import "./chunk-X3VHO37D.mjs";
import {
  MatButton,
  MatButtonModule,
  MatIconButton
} from "./chunk-FBNBSXHX.mjs";
import "./chunk-KEUUJZPF.mjs";
import {
  MatProgressSpinner,
  MatProgressSpinnerModule
} from "./chunk-XR745ZVT.mjs";
import {
  MatCardModule
} from "./chunk-2HWDCYPT.mjs";
import {
  DefaultValueAccessor,
  FormBuilder,
  FormControlName,
  FormGroupDirective,
  NgControlStatus,
  NgControlStatusGroup,
  ReactiveFormsModule,
  Validators,
  ɵNgNoValidate
} from "./chunk-22AJVEKS.mjs";
import {
  MatIcon,
  MatIconModule
} from "./chunk-BMEM75CN.mjs";
import {
  AuthService,
  Router
} from "./chunk-2GCZFSIW.mjs";
import "./chunk-HPMMNQDD.mjs";
import {
  Component,
  inject,
  setClassMetadata,
  signal,
  ɵsetClassDebugInfo,
  ɵɵadvance,
  ɵɵconditional,
  ɵɵconditionalCreate,
  ɵɵdefineComponent,
  ɵɵelement,
  ɵɵelementEnd,
  ɵɵelementStart,
  ɵɵlistener,
  ɵɵnextContext,
  ɵɵproperty,
  ɵɵtext,
  ɵɵtextInterpolate
} from "./chunk-UZ5PZMGS.mjs";
import "./chunk-T2KOBY73.mjs";

// src/app/features/auth/login/login.component.ts
function LoginComponent_Conditional_70_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "mat-error");
    \u0275\u0275text(1, "El correo es requerido");
    \u0275\u0275elementEnd();
  }
}
function LoginComponent_Conditional_71_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "mat-error");
    \u0275\u0275text(1, "Formato de correo inv\xE1lido");
    \u0275\u0275elementEnd();
  }
}
function LoginComponent_Conditional_82_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "mat-error");
    \u0275\u0275text(1, "La contrase\xF1a es requerida");
    \u0275\u0275elementEnd();
  }
}
function LoginComponent_Conditional_83_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "div", 28)(1, "mat-icon");
    \u0275\u0275text(2, "error_outline");
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(3, "span");
    \u0275\u0275text(4);
    \u0275\u0275elementEnd()();
  }
  if (rf & 2) {
    const ctx_r0 = \u0275\u0275nextContext();
    \u0275\u0275advance(4);
    \u0275\u0275textInterpolate(ctx_r0.error());
  }
}
function LoginComponent_Conditional_86_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275element(0, "mat-spinner", 33);
    \u0275\u0275text(1, " Verificando... ");
  }
}
function LoginComponent_Conditional_87_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "mat-icon");
    \u0275\u0275text(1, "login");
    \u0275\u0275elementEnd();
    \u0275\u0275text(2, " Ingresar al sistema ");
  }
}
var LoginComponent = class _LoginComponent {
  auth = inject(AuthService);
  router = inject(Router);
  fb = inject(FormBuilder);
  loading = signal(false, ...ngDevMode ? [{ debugName: "loading" }] : []);
  error = signal("", ...ngDevMode ? [{ debugName: "error" }] : []);
  hidePassword = signal(true, ...ngDevMode ? [{ debugName: "hidePassword" }] : []);
  form = this.fb.group({
    email: ["", [Validators.required, Validators.email]],
    password: ["", [Validators.required, Validators.minLength(6)]]
  });
  submit() {
    if (this.form.invalid)
      return;
    this.loading.set(true);
    this.error.set("");
    this.auth.login(this.form.value).subscribe({
      next: () => this.router.navigate(["/dashboard"]),
      error: (err) => {
        this.error.set(err?.error?.message ?? "Error al iniciar sesi\xF3n");
        this.loading.set(false);
      }
    });
  }
  static \u0275fac = function LoginComponent_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _LoginComponent)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({ type: _LoginComponent, selectors: [["app-login"]], decls: 92, vars: 9, consts: [[1, "login-wrapper"], [1, "brand-panel"], [1, "brand-top"], [1, "logo-card"], ["src", "https://www.torreonfuerte.org.co/wp-content/uploads/2023/10/LOGO-TORREON-FUERTE.webp", "alt", "Torre\xF3n Fuerte", 1, "logo-img"], [1, "tagline"], [1, "brand-mid"], [1, "accent"], [1, "brand-desc"], [1, "feature-list"], [1, "feature-item"], [1, "feat-icon"], [1, "feat-title"], [1, "feat-sub"], [1, "brand-bottom"], [1, "divider-line"], [1, "form-panel"], [1, "form-card"], [1, "form-header"], [1, "welcome-badge"], ["novalidate", "", 1, "login-form", 3, "ngSubmit", "formGroup"], [1, "input-group"], [1, "input-label"], ["appearance", "outline", 1, "field"], ["matInput", "", "type", "email", "formControlName", "email", "autocomplete", "email", "placeholder", "usuario@iglesia.com"], ["matPrefix", ""], ["matInput", "", "formControlName", "password", "autocomplete", "current-password", 3, "type"], ["mat-icon-button", "", "matSuffix", "", "type", "button", 3, "click"], [1, "error-alert"], ["mat-raised-button", "", "type", "submit", 1, "submit-btn", 3, "disabled"], [1, "btn-inner"], [1, "form-footer"], [1, "sec-icon"], ["diameter", "20", 1, "spin-white"]], template: function LoginComponent_Template(rf, ctx) {
    if (rf & 1) {
      \u0275\u0275elementStart(0, "div", 0)(1, "div", 1)(2, "div", 2)(3, "div", 3);
      \u0275\u0275element(4, "img", 4);
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(5, "p", 5);
      \u0275\u0275text(6, "Restaurar las Familias de la Tierra");
      \u0275\u0275elementEnd()();
      \u0275\u0275elementStart(7, "div", 6)(8, "h1");
      \u0275\u0275text(9, "Sistema CRM");
      \u0275\u0275element(10, "br");
      \u0275\u0275elementStart(11, "span", 7);
      \u0275\u0275text(12, "Eclesi\xE1stico");
      \u0275\u0275elementEnd()();
      \u0275\u0275elementStart(13, "p", 8);
      \u0275\u0275text(14, " Gestiona tu congregaci\xF3n, liderazgo, planes de preparaci\xF3n y eventos desde un solo lugar. ");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(15, "div", 9)(16, "div", 10)(17, "div", 11);
      \u0275\u0275text(18, "\u{1F3DB}\uFE0F");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(19, "div")(20, "div", 12);
      \u0275\u0275text(21, "M\xFAltiples Sedes");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(22, "div", 13);
      \u0275\u0275text(23, "Vista consolidada desde la sede central");
      \u0275\u0275elementEnd()()();
      \u0275\u0275elementStart(24, "div", 10)(25, "div", 11);
      \u0275\u0275text(26, "\u{1F333}");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(27, "div")(28, "div", 12);
      \u0275\u0275text(29, "\xC1rbol de Liderazgo");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(30, "div", 13);
      \u0275\u0275text(31, "Pastor \u2192 Zona \u2192 C\xE9lula \u2192 Miembros");
      \u0275\u0275elementEnd()()();
      \u0275\u0275elementStart(32, "div", 10)(33, "div", 11);
      \u0275\u0275text(34, "\u{1F4CB}");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(35, "div")(36, "div", 12);
      \u0275\u0275text(37, "Plan de Preparaci\xF3n");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(38, "div", 13);
      \u0275\u0275text(39, "Etapas, avances y alertas autom\xE1ticas");
      \u0275\u0275elementEnd()()();
      \u0275\u0275elementStart(40, "div", 10)(41, "div", 11);
      \u0275\u0275text(42, "\u{1F4C5}");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(43, "div")(44, "div", 12);
      \u0275\u0275text(45, "Eventos y Asistencia");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(46, "div", 13);
      \u0275\u0275text(47, "Encuentros, retiros y conferencias");
      \u0275\u0275elementEnd()()()()();
      \u0275\u0275elementStart(48, "div", 14);
      \u0275\u0275element(49, "div", 15);
      \u0275\u0275elementStart(50, "span");
      \u0275\u0275text(51, "Pastores Walter & Alba P\xE9rez \xB7 desde 1991 \xB7 Medell\xEDn");
      \u0275\u0275elementEnd()()();
      \u0275\u0275elementStart(52, "div", 16)(53, "div", 17)(54, "div", 18)(55, "div", 19)(56, "span");
      \u0275\u0275text(57, "\xA1Es todo un placer!");
      \u0275\u0275elementEnd()();
      \u0275\u0275elementStart(58, "h2");
      \u0275\u0275text(59, "Bienvenido de vuelta");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(60, "p");
      \u0275\u0275text(61, "Ingresa tus credenciales para acceder al sistema");
      \u0275\u0275elementEnd()();
      \u0275\u0275elementStart(62, "form", 20);
      \u0275\u0275listener("ngSubmit", function LoginComponent_Template_form_ngSubmit_62_listener() {
        return ctx.submit();
      });
      \u0275\u0275elementStart(63, "div", 21)(64, "label", 22);
      \u0275\u0275text(65, "Correo electr\xF3nico");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(66, "mat-form-field", 23);
      \u0275\u0275element(67, "input", 24);
      \u0275\u0275elementStart(68, "mat-icon", 25);
      \u0275\u0275text(69, "mail_outline");
      \u0275\u0275elementEnd();
      \u0275\u0275conditionalCreate(70, LoginComponent_Conditional_70_Template, 2, 0, "mat-error");
      \u0275\u0275conditionalCreate(71, LoginComponent_Conditional_71_Template, 2, 0, "mat-error");
      \u0275\u0275elementEnd()();
      \u0275\u0275elementStart(72, "div", 21)(73, "label", 22);
      \u0275\u0275text(74, "Contrase\xF1a");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(75, "mat-form-field", 23);
      \u0275\u0275element(76, "input", 26);
      \u0275\u0275elementStart(77, "mat-icon", 25);
      \u0275\u0275text(78, "lock_outline");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(79, "button", 27);
      \u0275\u0275listener("click", function LoginComponent_Template_button_click_79_listener() {
        return ctx.hidePassword.set(!ctx.hidePassword());
      });
      \u0275\u0275elementStart(80, "mat-icon");
      \u0275\u0275text(81);
      \u0275\u0275elementEnd()();
      \u0275\u0275conditionalCreate(82, LoginComponent_Conditional_82_Template, 2, 0, "mat-error");
      \u0275\u0275elementEnd()();
      \u0275\u0275conditionalCreate(83, LoginComponent_Conditional_83_Template, 5, 1, "div", 28);
      \u0275\u0275elementStart(84, "button", 29)(85, "span", 30);
      \u0275\u0275conditionalCreate(86, LoginComponent_Conditional_86_Template, 2, 0)(87, LoginComponent_Conditional_87_Template, 3, 0);
      \u0275\u0275elementEnd()()();
      \u0275\u0275elementStart(88, "div", 31)(89, "mat-icon", 32);
      \u0275\u0275text(90, "verified_user");
      \u0275\u0275elementEnd();
      \u0275\u0275text(91, " Acceso seguro \xB7 Autenticaci\xF3n JWT ");
      \u0275\u0275elementEnd()()()();
    }
    if (rf & 2) {
      let tmp_1_0;
      let tmp_2_0;
      let tmp_5_0;
      \u0275\u0275advance(62);
      \u0275\u0275property("formGroup", ctx.form);
      \u0275\u0275advance(8);
      \u0275\u0275conditional(((tmp_1_0 = ctx.form.get("email")) == null ? null : tmp_1_0.touched) && ((tmp_1_0 = ctx.form.get("email")) == null ? null : tmp_1_0.hasError("required")) ? 70 : -1);
      \u0275\u0275advance();
      \u0275\u0275conditional(((tmp_2_0 = ctx.form.get("email")) == null ? null : tmp_2_0.touched) && ((tmp_2_0 = ctx.form.get("email")) == null ? null : tmp_2_0.hasError("email")) ? 71 : -1);
      \u0275\u0275advance(5);
      \u0275\u0275property("type", ctx.hidePassword() ? "password" : "text");
      \u0275\u0275advance(5);
      \u0275\u0275textInterpolate(ctx.hidePassword() ? "visibility_off" : "visibility");
      \u0275\u0275advance();
      \u0275\u0275conditional(((tmp_5_0 = ctx.form.get("password")) == null ? null : tmp_5_0.touched) && ((tmp_5_0 = ctx.form.get("password")) == null ? null : tmp_5_0.hasError("required")) ? 82 : -1);
      \u0275\u0275advance();
      \u0275\u0275conditional(ctx.error() ? 83 : -1);
      \u0275\u0275advance();
      \u0275\u0275property("disabled", ctx.loading());
      \u0275\u0275advance(2);
      \u0275\u0275conditional(ctx.loading() ? 86 : 87);
    }
  }, dependencies: [ReactiveFormsModule, \u0275NgNoValidate, DefaultValueAccessor, NgControlStatus, NgControlStatusGroup, FormGroupDirective, FormControlName, MatCardModule, MatFormFieldModule, MatFormField, MatError, MatPrefix, MatSuffix, MatInputModule, MatInput, MatButtonModule, MatButton, MatIconButton, MatIconModule, MatIcon, MatProgressSpinnerModule, MatProgressSpinner], styles: ['\n\n.login-wrapper[_ngcontent-%COMP%] {\n  display: flex;\n  min-height: 100vh;\n}\n.brand-panel[_ngcontent-%COMP%] {\n  flex: 1;\n  background:\n    linear-gradient(\n      170deg,\n      #00385a 0%,\n      #005a8e 40%,\n      #0077bd 100%);\n  display: flex;\n  flex-direction: column;\n  justify-content: space-between;\n  padding: 48px 44px;\n  color: white;\n  position: relative;\n  overflow: hidden;\n}\n.brand-panel[_ngcontent-%COMP%]::before {\n  content: "";\n  position: absolute;\n  top: -100px;\n  right: -100px;\n  width: 400px;\n  height: 400px;\n  border-radius: 50%;\n  background:\n    radial-gradient(\n      circle,\n      rgba(248, 195, 0, .14) 0%,\n      transparent 70%);\n  pointer-events: none;\n}\n.brand-panel[_ngcontent-%COMP%]::after {\n  content: "";\n  position: absolute;\n  bottom: -80px;\n  left: -80px;\n  width: 300px;\n  height: 300px;\n  border-radius: 50%;\n  background:\n    radial-gradient(\n      circle,\n      rgba(255, 255, 255, .06) 0%,\n      transparent 70%);\n  pointer-events: none;\n}\n.brand-top[_ngcontent-%COMP%] {\n  position: relative;\n  z-index: 1;\n}\n.logo-card[_ngcontent-%COMP%] {\n  display: inline-flex;\n  background: white;\n  border-radius: 16px;\n  padding: 14px 20px;\n  box-shadow: 0 8px 32px rgba(0, 0, 0, .25);\n  margin-bottom: 20px;\n}\n.logo-img[_ngcontent-%COMP%] {\n  height: 56px;\n  width: auto;\n  display: block;\n}\n.tagline[_ngcontent-%COMP%] {\n  font-family: "Cormorant Infant", serif;\n  font-size: 15px;\n  font-style: italic;\n  color: rgba(255, 255, 255, .7);\n  margin: 0;\n  letter-spacing: 0.3px;\n}\n.brand-mid[_ngcontent-%COMP%] {\n  position: relative;\n  z-index: 1;\n}\n.brand-mid[_ngcontent-%COMP%]   h1[_ngcontent-%COMP%] {\n  font-family: "Cormorant Infant", serif;\n  font-size: 42px;\n  font-weight: 700;\n  line-height: 1.15;\n  margin: 0 0 14px;\n  letter-spacing: -0.5px;\n}\n.accent[_ngcontent-%COMP%] {\n  color: #f8c300;\n}\n.brand-desc[_ngcontent-%COMP%] {\n  font-family: "Karla", sans-serif;\n  font-size: 14.5px;\n  color: rgba(255, 255, 255, .7);\n  line-height: 1.65;\n  margin: 0 0 36px;\n  max-width: 340px;\n}\n.feature-list[_ngcontent-%COMP%] {\n  display: flex;\n  flex-direction: column;\n  gap: 18px;\n}\n.feature-item[_ngcontent-%COMP%] {\n  display: flex;\n  align-items: flex-start;\n  gap: 14px;\n}\n.feat-icon[_ngcontent-%COMP%] {\n  width: 42px;\n  height: 42px;\n  background: rgba(248, 195, 0, .15);\n  border: 1px solid rgba(248, 195, 0, .25);\n  border-radius: 12px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  font-size: 18px;\n  flex-shrink: 0;\n}\n.feat-title[_ngcontent-%COMP%] {\n  font-family: "Karla", sans-serif;\n  font-weight: 600;\n  font-size: 14px;\n  color: white;\n  margin-bottom: 2px;\n}\n.feat-sub[_ngcontent-%COMP%] {\n  font-family: "Karla", sans-serif;\n  font-size: 12px;\n  color: rgba(255, 255, 255, .55);\n}\n.brand-bottom[_ngcontent-%COMP%] {\n  position: relative;\n  z-index: 1;\n}\n.divider-line[_ngcontent-%COMP%] {\n  height: 1px;\n  background: rgba(255, 255, 255, .15);\n  margin-bottom: 14px;\n}\n.brand-bottom[_ngcontent-%COMP%]   span[_ngcontent-%COMP%] {\n  font-size: 12px;\n  color: rgba(255, 255, 255, .4);\n  font-family: "Karla", sans-serif;\n}\n.form-panel[_ngcontent-%COMP%] {\n  width: 500px;\n  flex-shrink: 0;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  background: #f4f6fa;\n  padding: 40px 32px;\n}\n.form-card[_ngcontent-%COMP%] {\n  width: 100%;\n  max-width: 400px;\n  background: white;\n  border-radius: 20px;\n  padding: 40px 36px;\n  box-shadow: 0 4px 24px rgba(0, 0, 0, .07), 0 1px 4px rgba(0, 0, 0, .05);\n}\n.form-header[_ngcontent-%COMP%] {\n  margin-bottom: 28px;\n}\n.welcome-badge[_ngcontent-%COMP%] {\n  display: inline-flex;\n  align-items: center;\n  background: #fff8e1;\n  border: 1px solid #f8c300;\n  border-radius: 20px;\n  padding: 4px 14px;\n  margin-bottom: 14px;\n}\n.welcome-badge[_ngcontent-%COMP%]   span[_ngcontent-%COMP%] {\n  font-family: "Karla", sans-serif;\n  font-size: 12.5px;\n  font-weight: 600;\n  color: #a16207;\n  letter-spacing: 0.3px;\n}\n.form-header[_ngcontent-%COMP%]   h2[_ngcontent-%COMP%] {\n  font-family: "Cormorant Infant", serif;\n  font-size: 28px;\n  font-weight: 700;\n  color: #201c15;\n  margin: 0 0 6px;\n  line-height: 1.2;\n}\n.form-header[_ngcontent-%COMP%]   p[_ngcontent-%COMP%] {\n  font-family: "Karla", sans-serif;\n  font-size: 14px;\n  color: #6b7280;\n  margin: 0;\n}\n.login-form[_ngcontent-%COMP%] {\n  display: flex;\n  flex-direction: column;\n  gap: 2px;\n}\n.input-group[_ngcontent-%COMP%] {\n  display: flex;\n  flex-direction: column;\n}\n.input-label[_ngcontent-%COMP%] {\n  font-family: "Karla", sans-serif;\n  font-size: 13px;\n  font-weight: 600;\n  color: #374151;\n  margin-bottom: 4px;\n}\n.field[_ngcontent-%COMP%] {\n  width: 100%;\n}\n.field[_ngcontent-%COMP%]     .mdc-text-field--outlined .mdc-notched-outline__leading, \n.field[_ngcontent-%COMP%]     .mdc-text-field--outlined .mdc-notched-outline__notch, \n.field[_ngcontent-%COMP%]     .mdc-text-field--outlined .mdc-notched-outline__trailing {\n  border-color: #e5e7eb;\n  border-radius: 10px;\n}\n.field[_ngcontent-%COMP%]     .mdc-text-field--outlined.mdc-text-field--focused .mdc-notched-outline__leading, \n.field[_ngcontent-%COMP%]     .mdc-text-field--outlined.mdc-text-field--focused .mdc-notched-outline__notch, \n.field[_ngcontent-%COMP%]     .mdc-text-field--outlined.mdc-text-field--focused .mdc-notched-outline__trailing {\n  border-color: #0077bd !important;\n  border-width: 2px !important;\n}\n.field[_ngcontent-%COMP%]     .mdc-floating-label--float-above {\n  color: #0077bd !important;\n}\n.field[_ngcontent-%COMP%]     .mat-mdc-form-field-icon-prefix mat-icon {\n  color: #9ca3af;\n  font-size: 20px;\n}\n.error-alert[_ngcontent-%COMP%] {\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  padding: 12px 16px;\n  background: #fef2f2;\n  border: 1px solid #fecaca;\n  border-radius: 10px;\n  color: #dc2626;\n  font-size: 13.5px;\n  font-family: "Karla", sans-serif;\n  margin: 4px 0;\n}\n.error-alert[_ngcontent-%COMP%]   mat-icon[_ngcontent-%COMP%] {\n  font-size: 18px;\n  width: 18px;\n  height: 18px;\n  flex-shrink: 0;\n}\n.submit-btn[_ngcontent-%COMP%] {\n  width: 100%;\n  height: 52px !important;\n  border-radius: 12px !important;\n  background:\n    linear-gradient(\n      135deg,\n      #0063a3 0%,\n      #0077bd 60%,\n      #009ae0 100%) !important;\n  color: white !important;\n  font-family: "Karla", sans-serif !important;\n  font-size: 15px !important;\n  font-weight: 600 !important;\n  letter-spacing: 0.4px;\n  margin-top: 12px;\n  box-shadow: 0 4px 16px rgba(0, 119, 189, .35) !important;\n  transition: box-shadow .2s, transform .1s !important;\n  border: none !important;\n}\n.submit-btn[_ngcontent-%COMP%]:hover:not(:disabled) {\n  box-shadow: 0 8px 24px rgba(0, 119, 189, .5) !important;\n  transform: translateY(-1px);\n}\n.submit-btn[_ngcontent-%COMP%]:active:not(:disabled) {\n  transform: translateY(0);\n}\n.submit-btn[_ngcontent-%COMP%]:disabled {\n  background:\n    linear-gradient(\n      135deg,\n      #93c5fd,\n      #7dd3fc) !important;\n  box-shadow: none !important;\n}\n.btn-inner[_ngcontent-%COMP%] {\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  gap: 8px;\n}\n.btn-inner[_ngcontent-%COMP%]   mat-icon[_ngcontent-%COMP%] {\n  font-size: 20px;\n  width: 20px;\n  height: 20px;\n}\n.spin-white[_ngcontent-%COMP%]     circle {\n  stroke: white !important;\n}\n.form-footer[_ngcontent-%COMP%] {\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  gap: 6px;\n  margin-top: 24px;\n  font-size: 12px;\n  font-family: "Karla", sans-serif;\n  color: #9ca3af;\n}\n.sec-icon[_ngcontent-%COMP%] {\n  font-size: 14px;\n  width: 14px;\n  height: 14px;\n  color: #10b981;\n}\n@media (max-width: 900px) {\n  .brand-panel[_ngcontent-%COMP%] {\n    display: none;\n  }\n  .form-panel[_ngcontent-%COMP%] {\n    width: 100%;\n    background: white;\n  }\n  .form-card[_ngcontent-%COMP%] {\n    box-shadow: none;\n    max-width: 440px;\n  }\n}\n@media (max-width: 480px) {\n  .form-panel[_ngcontent-%COMP%] {\n    padding: 24px 16px;\n  }\n  .form-card[_ngcontent-%COMP%] {\n    padding: 28px 20px;\n  }\n}\n/*# sourceMappingURL=login.component.css.map */'] });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(LoginComponent, [{
    type: Component,
    args: [{ selector: "app-login", standalone: true, imports: [
      ReactiveFormsModule,
      MatCardModule,
      MatFormFieldModule,
      MatInputModule,
      MatButtonModule,
      MatIconModule,
      MatProgressSpinnerModule
    ], template: `<div class="login-wrapper">

  <!-- \u2550\u2550 Panel izquierdo \u2014 branding \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550 -->
  <div class="brand-panel">

    <div class="brand-top">
      <div class="logo-card">
        <img
          src="https://www.torreonfuerte.org.co/wp-content/uploads/2023/10/LOGO-TORREON-FUERTE.webp"
          alt="Torre\xF3n Fuerte"
          class="logo-img"
        />
      </div>
      <p class="tagline">Restaurar las Familias de la Tierra</p>
    </div>

    <div class="brand-mid">
      <h1>Sistema CRM<br><span class="accent">Eclesi\xE1stico</span></h1>
      <p class="brand-desc">
        Gestiona tu congregaci\xF3n, liderazgo, planes de preparaci\xF3n y eventos desde un solo lugar.
      </p>

      <div class="feature-list">
        <div class="feature-item">
          <div class="feat-icon">\u{1F3DB}\uFE0F</div>
          <div>
            <div class="feat-title">M\xFAltiples Sedes</div>
            <div class="feat-sub">Vista consolidada desde la sede central</div>
          </div>
        </div>
        <div class="feature-item">
          <div class="feat-icon">\u{1F333}</div>
          <div>
            <div class="feat-title">\xC1rbol de Liderazgo</div>
            <div class="feat-sub">Pastor \u2192 Zona \u2192 C\xE9lula \u2192 Miembros</div>
          </div>
        </div>
        <div class="feature-item">
          <div class="feat-icon">\u{1F4CB}</div>
          <div>
            <div class="feat-title">Plan de Preparaci\xF3n</div>
            <div class="feat-sub">Etapas, avances y alertas autom\xE1ticas</div>
          </div>
        </div>
        <div class="feature-item">
          <div class="feat-icon">\u{1F4C5}</div>
          <div>
            <div class="feat-title">Eventos y Asistencia</div>
            <div class="feat-sub">Encuentros, retiros y conferencias</div>
          </div>
        </div>
      </div>
    </div>

    <div class="brand-bottom">
      <div class="divider-line"></div>
      <span>Pastores Walter &amp; Alba P\xE9rez \xB7 desde 1991 \xB7 Medell\xEDn</span>
    </div>
  </div>

  <!-- \u2550\u2550 Panel derecho \u2014 formulario \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550 -->
  <div class="form-panel">
    <div class="form-card">

      <div class="form-header">
        <div class="welcome-badge">
          <span>\xA1Es todo un placer!</span>
        </div>
        <h2>Bienvenido de vuelta</h2>
        <p>Ingresa tus credenciales para acceder al sistema</p>
      </div>

      <form [formGroup]="form" (ngSubmit)="submit()" class="login-form" novalidate>

        <div class="input-group">
          <label class="input-label">Correo electr\xF3nico</label>
          <mat-form-field appearance="outline" class="field">
            <input matInput type="email" formControlName="email"
                   autocomplete="email" placeholder="usuario@iglesia.com" />
            <mat-icon matPrefix>mail_outline</mat-icon>
            @if (form.get('email')?.touched && form.get('email')?.hasError('required')) {
              <mat-error>El correo es requerido</mat-error>
            }
            @if (form.get('email')?.touched && form.get('email')?.hasError('email')) {
              <mat-error>Formato de correo inv\xE1lido</mat-error>
            }
          </mat-form-field>
        </div>

        <div class="input-group">
          <label class="input-label">Contrase\xF1a</label>
          <mat-form-field appearance="outline" class="field">
            <input matInput [type]="hidePassword() ? 'password' : 'text'"
                   formControlName="password" autocomplete="current-password" />
            <mat-icon matPrefix>lock_outline</mat-icon>
            <button mat-icon-button matSuffix type="button"
                    (click)="hidePassword.set(!hidePassword())">
              <mat-icon>{{ hidePassword() ? 'visibility_off' : 'visibility' }}</mat-icon>
            </button>
            @if (form.get('password')?.touched && form.get('password')?.hasError('required')) {
              <mat-error>La contrase\xF1a es requerida</mat-error>
            }
          </mat-form-field>
        </div>

        @if (error()) {
          <div class="error-alert">
            <mat-icon>error_outline</mat-icon>
            <span>{{ error() }}</span>
          </div>
        }

        <button mat-raised-button type="submit"
                class="submit-btn" [disabled]="loading()">
          <span class="btn-inner">
            @if (loading()) {
              <mat-spinner diameter="20" class="spin-white" />
              Verificando...
            } @else {
              <mat-icon>login</mat-icon>
              Ingresar al sistema
            }
          </span>
        </button>

      </form>

      <div class="form-footer">
        <mat-icon class="sec-icon">verified_user</mat-icon>
        Acceso seguro \xB7 Autenticaci\xF3n JWT
      </div>

    </div>
  </div>

</div>
`, styles: ['/* src/app/features/auth/login/login.component.css */\n.login-wrapper {\n  display: flex;\n  min-height: 100vh;\n}\n.brand-panel {\n  flex: 1;\n  background:\n    linear-gradient(\n      170deg,\n      #00385a 0%,\n      #005a8e 40%,\n      #0077bd 100%);\n  display: flex;\n  flex-direction: column;\n  justify-content: space-between;\n  padding: 48px 44px;\n  color: white;\n  position: relative;\n  overflow: hidden;\n}\n.brand-panel::before {\n  content: "";\n  position: absolute;\n  top: -100px;\n  right: -100px;\n  width: 400px;\n  height: 400px;\n  border-radius: 50%;\n  background:\n    radial-gradient(\n      circle,\n      rgba(248, 195, 0, .14) 0%,\n      transparent 70%);\n  pointer-events: none;\n}\n.brand-panel::after {\n  content: "";\n  position: absolute;\n  bottom: -80px;\n  left: -80px;\n  width: 300px;\n  height: 300px;\n  border-radius: 50%;\n  background:\n    radial-gradient(\n      circle,\n      rgba(255, 255, 255, .06) 0%,\n      transparent 70%);\n  pointer-events: none;\n}\n.brand-top {\n  position: relative;\n  z-index: 1;\n}\n.logo-card {\n  display: inline-flex;\n  background: white;\n  border-radius: 16px;\n  padding: 14px 20px;\n  box-shadow: 0 8px 32px rgba(0, 0, 0, .25);\n  margin-bottom: 20px;\n}\n.logo-img {\n  height: 56px;\n  width: auto;\n  display: block;\n}\n.tagline {\n  font-family: "Cormorant Infant", serif;\n  font-size: 15px;\n  font-style: italic;\n  color: rgba(255, 255, 255, .7);\n  margin: 0;\n  letter-spacing: 0.3px;\n}\n.brand-mid {\n  position: relative;\n  z-index: 1;\n}\n.brand-mid h1 {\n  font-family: "Cormorant Infant", serif;\n  font-size: 42px;\n  font-weight: 700;\n  line-height: 1.15;\n  margin: 0 0 14px;\n  letter-spacing: -0.5px;\n}\n.accent {\n  color: #f8c300;\n}\n.brand-desc {\n  font-family: "Karla", sans-serif;\n  font-size: 14.5px;\n  color: rgba(255, 255, 255, .7);\n  line-height: 1.65;\n  margin: 0 0 36px;\n  max-width: 340px;\n}\n.feature-list {\n  display: flex;\n  flex-direction: column;\n  gap: 18px;\n}\n.feature-item {\n  display: flex;\n  align-items: flex-start;\n  gap: 14px;\n}\n.feat-icon {\n  width: 42px;\n  height: 42px;\n  background: rgba(248, 195, 0, .15);\n  border: 1px solid rgba(248, 195, 0, .25);\n  border-radius: 12px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  font-size: 18px;\n  flex-shrink: 0;\n}\n.feat-title {\n  font-family: "Karla", sans-serif;\n  font-weight: 600;\n  font-size: 14px;\n  color: white;\n  margin-bottom: 2px;\n}\n.feat-sub {\n  font-family: "Karla", sans-serif;\n  font-size: 12px;\n  color: rgba(255, 255, 255, .55);\n}\n.brand-bottom {\n  position: relative;\n  z-index: 1;\n}\n.divider-line {\n  height: 1px;\n  background: rgba(255, 255, 255, .15);\n  margin-bottom: 14px;\n}\n.brand-bottom span {\n  font-size: 12px;\n  color: rgba(255, 255, 255, .4);\n  font-family: "Karla", sans-serif;\n}\n.form-panel {\n  width: 500px;\n  flex-shrink: 0;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  background: #f4f6fa;\n  padding: 40px 32px;\n}\n.form-card {\n  width: 100%;\n  max-width: 400px;\n  background: white;\n  border-radius: 20px;\n  padding: 40px 36px;\n  box-shadow: 0 4px 24px rgba(0, 0, 0, .07), 0 1px 4px rgba(0, 0, 0, .05);\n}\n.form-header {\n  margin-bottom: 28px;\n}\n.welcome-badge {\n  display: inline-flex;\n  align-items: center;\n  background: #fff8e1;\n  border: 1px solid #f8c300;\n  border-radius: 20px;\n  padding: 4px 14px;\n  margin-bottom: 14px;\n}\n.welcome-badge span {\n  font-family: "Karla", sans-serif;\n  font-size: 12.5px;\n  font-weight: 600;\n  color: #a16207;\n  letter-spacing: 0.3px;\n}\n.form-header h2 {\n  font-family: "Cormorant Infant", serif;\n  font-size: 28px;\n  font-weight: 700;\n  color: #201c15;\n  margin: 0 0 6px;\n  line-height: 1.2;\n}\n.form-header p {\n  font-family: "Karla", sans-serif;\n  font-size: 14px;\n  color: #6b7280;\n  margin: 0;\n}\n.login-form {\n  display: flex;\n  flex-direction: column;\n  gap: 2px;\n}\n.input-group {\n  display: flex;\n  flex-direction: column;\n}\n.input-label {\n  font-family: "Karla", sans-serif;\n  font-size: 13px;\n  font-weight: 600;\n  color: #374151;\n  margin-bottom: 4px;\n}\n.field {\n  width: 100%;\n}\n.field ::ng-deep .mdc-text-field--outlined .mdc-notched-outline__leading,\n.field ::ng-deep .mdc-text-field--outlined .mdc-notched-outline__notch,\n.field ::ng-deep .mdc-text-field--outlined .mdc-notched-outline__trailing {\n  border-color: #e5e7eb;\n  border-radius: 10px;\n}\n.field ::ng-deep .mdc-text-field--outlined.mdc-text-field--focused .mdc-notched-outline__leading,\n.field ::ng-deep .mdc-text-field--outlined.mdc-text-field--focused .mdc-notched-outline__notch,\n.field ::ng-deep .mdc-text-field--outlined.mdc-text-field--focused .mdc-notched-outline__trailing {\n  border-color: #0077bd !important;\n  border-width: 2px !important;\n}\n.field ::ng-deep .mdc-floating-label--float-above {\n  color: #0077bd !important;\n}\n.field ::ng-deep .mat-mdc-form-field-icon-prefix mat-icon {\n  color: #9ca3af;\n  font-size: 20px;\n}\n.error-alert {\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  padding: 12px 16px;\n  background: #fef2f2;\n  border: 1px solid #fecaca;\n  border-radius: 10px;\n  color: #dc2626;\n  font-size: 13.5px;\n  font-family: "Karla", sans-serif;\n  margin: 4px 0;\n}\n.error-alert mat-icon {\n  font-size: 18px;\n  width: 18px;\n  height: 18px;\n  flex-shrink: 0;\n}\n.submit-btn {\n  width: 100%;\n  height: 52px !important;\n  border-radius: 12px !important;\n  background:\n    linear-gradient(\n      135deg,\n      #0063a3 0%,\n      #0077bd 60%,\n      #009ae0 100%) !important;\n  color: white !important;\n  font-family: "Karla", sans-serif !important;\n  font-size: 15px !important;\n  font-weight: 600 !important;\n  letter-spacing: 0.4px;\n  margin-top: 12px;\n  box-shadow: 0 4px 16px rgba(0, 119, 189, .35) !important;\n  transition: box-shadow .2s, transform .1s !important;\n  border: none !important;\n}\n.submit-btn:hover:not(:disabled) {\n  box-shadow: 0 8px 24px rgba(0, 119, 189, .5) !important;\n  transform: translateY(-1px);\n}\n.submit-btn:active:not(:disabled) {\n  transform: translateY(0);\n}\n.submit-btn:disabled {\n  background:\n    linear-gradient(\n      135deg,\n      #93c5fd,\n      #7dd3fc) !important;\n  box-shadow: none !important;\n}\n.btn-inner {\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  gap: 8px;\n}\n.btn-inner mat-icon {\n  font-size: 20px;\n  width: 20px;\n  height: 20px;\n}\n.spin-white ::ng-deep circle {\n  stroke: white !important;\n}\n.form-footer {\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  gap: 6px;\n  margin-top: 24px;\n  font-size: 12px;\n  font-family: "Karla", sans-serif;\n  color: #9ca3af;\n}\n.sec-icon {\n  font-size: 14px;\n  width: 14px;\n  height: 14px;\n  color: #10b981;\n}\n@media (max-width: 900px) {\n  .brand-panel {\n    display: none;\n  }\n  .form-panel {\n    width: 100%;\n    background: white;\n  }\n  .form-card {\n    box-shadow: none;\n    max-width: 440px;\n  }\n}\n@media (max-width: 480px) {\n  .form-panel {\n    padding: 24px 16px;\n  }\n  .form-card {\n    padding: 28px 20px;\n  }\n}\n/*# sourceMappingURL=login.component.css.map */\n'] }]
  }], null, null);
})();
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && \u0275setClassDebugInfo(LoginComponent, { className: "LoginComponent", filePath: "src/app/features/auth/login/login.component.ts", lineNumber: 23 });
})();
export {
  LoginComponent
};
//# sourceMappingURL=chunk-NRYS5OOU.mjs.map
