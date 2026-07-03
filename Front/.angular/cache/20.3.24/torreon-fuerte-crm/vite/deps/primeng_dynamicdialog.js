import {
  Button
} from "./chunk-D7NNDZQB.js";
import "./chunk-BASEVNZ5.js";
import "./chunk-GF2QDUOO.js";
import {
  TimesIcon,
  WindowMaximizeIcon,
  WindowMinimizeIcon
} from "./chunk-RS2T5OLM.js";
import {
  zindexutils
} from "./chunk-KVRA5NT4.js";
import "./chunk-SGAREDHC.js";
import {
  animate,
  animation,
  style,
  transition,
  trigger,
  useAnimation
} from "./chunk-42VGZS34.js";
import "./chunk-BVLLAPFU.js";
import {
  DomHandler,
  blockBodyScroll,
  unblockBodyScroll
} from "./chunk-4W4LVZ4Z.js";
import "./chunk-CEQOIPCD.js";
import {
  BaseComponent,
  PARENT_INSTANCE
} from "./chunk-ZUHKIO6D.js";
import {
  Bind,
  BindModule
} from "./chunk-XKYBTGJK.js";
import {
  BaseStyle
} from "./chunk-MBNUBWL2.js";
import {
  PrimeTemplate,
  SharedModule,
  TranslationKeys
} from "./chunk-PX2FD2VV.js";
import {
  C,
  Lt,
  P,
  R,
  U,
  W,
  _t,
  bt,
  h,
  s3 as s,
  ut,
  v,
  vt
} from "./chunk-TIK557PQ.js";
import {
  CommonModule,
  NgClass,
  NgComponentOutlet,
  NgIf,
  NgStyle,
  NgTemplateOutlet,
  isPlatformBrowser
} from "./chunk-CSO4LQUX.js";
import "./chunk-U44TYWUZ.js";
import {
  ApplicationRef,
  ChangeDetectionStrategy,
  Component,
  ContentChild,
  ContentChildren,
  Directive,
  EventEmitter,
  Inject,
  Injectable,
  Input,
  NgModule,
  NgZone,
  Output,
  PLATFORM_ID,
  ViewChild,
  ViewContainerRef,
  ViewEncapsulation,
  booleanAttribute,
  createComponent,
  input,
  numberAttribute,
  setClassMetadata,
  ɵɵHostDirectivesFeature,
  ɵɵInheritDefinitionFeature,
  ɵɵProvidersFeature,
  ɵɵadvance,
  ɵɵattribute,
  ɵɵclassMap,
  ɵɵcontentQuery,
  ɵɵdefineComponent,
  ɵɵdefineDirective,
  ɵɵdefineNgModule,
  ɵɵdirectiveInject,
  ɵɵelement,
  ɵɵelementContainer,
  ɵɵelementContainerEnd,
  ɵɵelementContainerStart,
  ɵɵelementEnd,
  ɵɵelementStart,
  ɵɵgetCurrentView,
  ɵɵgetInheritedFactory,
  ɵɵlistener,
  ɵɵloadQuery,
  ɵɵnextContext,
  ɵɵprojection,
  ɵɵprojectionDef,
  ɵɵproperty,
  ɵɵpureFunction0,
  ɵɵpureFunction1,
  ɵɵpureFunction2,
  ɵɵqueryRefresh,
  ɵɵreference,
  ɵɵstyleMap,
  ɵɵstyleProp,
  ɵɵtemplate,
  ɵɵtemplateRefExtractor,
  ɵɵtext,
  ɵɵtextInterpolate,
  ɵɵtwoWayBindingSet,
  ɵɵtwoWayListener,
  ɵɵtwoWayProperty,
  ɵɵviewQuery
} from "./chunk-QAPJPPDN.js";
import {
  DOCUMENT,
  InjectionToken,
  Injector,
  computed,
  inject,
  ɵɵdefineInjectable,
  ɵɵdefineInjector,
  ɵɵinject,
  ɵɵnamespaceSVG,
  ɵɵresetView,
  ɵɵrestoreView
} from "./chunk-7VU5RGLF.js";
import "./chunk-JRFR6BLO.js";
import "./chunk-HWYXSU2G.js";
import {
  Subject
} from "./chunk-MARUHEWW.js";
import {
  __spreadValues
} from "./chunk-H2SRQSE4.js";

// node_modules/primeng/fesm2022/primeng-focustrap.mjs
var FocusTrap = class _FocusTrap extends BaseComponent {
  /**
   * When set as true, focus wouldn't be managed.
   * @group Props
   */
  pFocusTrapDisabled = false;
  platformId = inject(PLATFORM_ID);
  document = inject(DOCUMENT);
  firstHiddenFocusableElement;
  lastHiddenFocusableElement;
  onInit() {
    if (isPlatformBrowser(this.platformId) && !this.pFocusTrapDisabled) {
      !this.firstHiddenFocusableElement && !this.lastHiddenFocusableElement && this.createHiddenFocusableElements();
    }
  }
  onChanges(changes) {
    if (changes.pFocusTrapDisabled && isPlatformBrowser(this.platformId)) {
      if (changes.pFocusTrapDisabled.currentValue) {
        this.removeHiddenFocusableElements();
      } else {
        this.createHiddenFocusableElements();
      }
    }
  }
  removeHiddenFocusableElements() {
    if (this.firstHiddenFocusableElement && this.firstHiddenFocusableElement.parentNode) {
      this.firstHiddenFocusableElement.parentNode.removeChild(this.firstHiddenFocusableElement);
    }
    if (this.lastHiddenFocusableElement && this.lastHiddenFocusableElement.parentNode) {
      this.lastHiddenFocusableElement.parentNode.removeChild(this.lastHiddenFocusableElement);
    }
  }
  getComputedSelector(selector) {
    return `:not(.p-hidden-focusable):not([data-p-hidden-focusable="true"])${selector ?? ""}`;
  }
  createHiddenFocusableElements() {
    const tabindex = "0";
    const createFocusableElement = (onFocus) => {
      return U("span", {
        class: "p-hidden-accessible p-hidden-focusable",
        tabindex,
        role: "presentation",
        "aria-hidden": true,
        "data-p-hidden-accessible": true,
        "data-p-hidden-focusable": true,
        onFocus: onFocus?.bind(this)
      });
    };
    this.firstHiddenFocusableElement = createFocusableElement(this.onFirstHiddenElementFocus);
    this.lastHiddenFocusableElement = createFocusableElement(this.onLastHiddenElementFocus);
    this.firstHiddenFocusableElement.setAttribute("data-pc-section", "firstfocusableelement");
    this.lastHiddenFocusableElement.setAttribute("data-pc-section", "lastfocusableelement");
    this.el.nativeElement.prepend(this.firstHiddenFocusableElement);
    this.el.nativeElement.append(this.lastHiddenFocusableElement);
  }
  onFirstHiddenElementFocus(event) {
    const {
      currentTarget,
      relatedTarget
    } = event;
    const focusableElement = relatedTarget === this.lastHiddenFocusableElement || !this.el.nativeElement?.contains(relatedTarget) ? vt(currentTarget.parentElement, ":not(.p-hidden-focusable)") : this.lastHiddenFocusableElement;
    bt(focusableElement);
  }
  onLastHiddenElementFocus(event) {
    const {
      currentTarget,
      relatedTarget
    } = event;
    const focusableElement = relatedTarget === this.firstHiddenFocusableElement || !this.el.nativeElement?.contains(relatedTarget) ? Lt(currentTarget.parentElement, ":not(.p-hidden-focusable)") : this.firstHiddenFocusableElement;
    bt(focusableElement);
  }
  static ɵfac = /* @__PURE__ */ (() => {
    let ɵFocusTrap_BaseFactory;
    return function FocusTrap_Factory(__ngFactoryType__) {
      return (ɵFocusTrap_BaseFactory || (ɵFocusTrap_BaseFactory = ɵɵgetInheritedFactory(_FocusTrap)))(__ngFactoryType__ || _FocusTrap);
    };
  })();
  static ɵdir = ɵɵdefineDirective({
    type: _FocusTrap,
    selectors: [["", "pFocusTrap", ""]],
    inputs: {
      pFocusTrapDisabled: [2, "pFocusTrapDisabled", "pFocusTrapDisabled", booleanAttribute]
    },
    features: [ɵɵInheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(FocusTrap, [{
    type: Directive,
    args: [{
      selector: "[pFocusTrap]",
      standalone: true
    }]
  }], null, {
    pFocusTrapDisabled: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }]
  });
})();
var FocusTrapModule = class _FocusTrapModule {
  static ɵfac = function FocusTrapModule_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _FocusTrapModule)();
  };
  static ɵmod = ɵɵdefineNgModule({
    type: _FocusTrapModule,
    imports: [FocusTrap],
    exports: [FocusTrap]
  });
  static ɵinj = ɵɵdefineInjector({});
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(FocusTrapModule, [{
    type: NgModule,
    args: [{
      imports: [FocusTrap],
      exports: [FocusTrap]
    }]
  }], null, null);
})();

// node_modules/@primeuix/styles/dist/dialog/index.mjs
var style2 = "\n    .p-dialog {\n        max-height: 90%;\n        transform: scale(1);\n        border-radius: dt('dialog.border.radius');\n        box-shadow: dt('dialog.shadow');\n        background: dt('dialog.background');\n        border: 1px solid dt('dialog.border.color');\n        color: dt('dialog.color');\n    }\n\n    .p-dialog-content {\n        overflow-y: auto;\n        padding: dt('dialog.content.padding');\n    }\n\n    .p-dialog-header {\n        display: flex;\n        align-items: center;\n        justify-content: space-between;\n        flex-shrink: 0;\n        padding: dt('dialog.header.padding');\n    }\n\n    .p-dialog-title {\n        font-weight: dt('dialog.title.font.weight');\n        font-size: dt('dialog.title.font.size');\n    }\n\n    .p-dialog-footer {\n        flex-shrink: 0;\n        padding: dt('dialog.footer.padding');\n        display: flex;\n        justify-content: flex-end;\n        gap: dt('dialog.footer.gap');\n    }\n\n    .p-dialog-header-actions {\n        display: flex;\n        align-items: center;\n        gap: dt('dialog.header.gap');\n    }\n\n    .p-dialog-enter-active {\n        transition: all 150ms cubic-bezier(0, 0, 0.2, 1);\n    }\n\n    .p-dialog-leave-active {\n        transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);\n    }\n\n    .p-dialog-enter-from,\n    .p-dialog-leave-to {\n        opacity: 0;\n        transform: scale(0.7);\n    }\n\n    .p-dialog-top .p-dialog,\n    .p-dialog-bottom .p-dialog,\n    .p-dialog-left .p-dialog,\n    .p-dialog-right .p-dialog,\n    .p-dialog-topleft .p-dialog,\n    .p-dialog-topright .p-dialog,\n    .p-dialog-bottomleft .p-dialog,\n    .p-dialog-bottomright .p-dialog {\n        margin: 0.75rem;\n        transform: translate3d(0px, 0px, 0px);\n    }\n\n    .p-dialog-top .p-dialog-enter-active,\n    .p-dialog-top .p-dialog-leave-active,\n    .p-dialog-bottom .p-dialog-enter-active,\n    .p-dialog-bottom .p-dialog-leave-active,\n    .p-dialog-left .p-dialog-enter-active,\n    .p-dialog-left .p-dialog-leave-active,\n    .p-dialog-right .p-dialog-enter-active,\n    .p-dialog-right .p-dialog-leave-active,\n    .p-dialog-topleft .p-dialog-enter-active,\n    .p-dialog-topleft .p-dialog-leave-active,\n    .p-dialog-topright .p-dialog-enter-active,\n    .p-dialog-topright .p-dialog-leave-active,\n    .p-dialog-bottomleft .p-dialog-enter-active,\n    .p-dialog-bottomleft .p-dialog-leave-active,\n    .p-dialog-bottomright .p-dialog-enter-active,\n    .p-dialog-bottomright .p-dialog-leave-active {\n        transition: all 0.3s ease-out;\n    }\n\n    .p-dialog-top .p-dialog-enter-from,\n    .p-dialog-top .p-dialog-leave-to {\n        transform: translate3d(0px, -100%, 0px);\n    }\n\n    .p-dialog-bottom .p-dialog-enter-from,\n    .p-dialog-bottom .p-dialog-leave-to {\n        transform: translate3d(0px, 100%, 0px);\n    }\n\n    .p-dialog-left .p-dialog-enter-from,\n    .p-dialog-left .p-dialog-leave-to,\n    .p-dialog-topleft .p-dialog-enter-from,\n    .p-dialog-topleft .p-dialog-leave-to,\n    .p-dialog-bottomleft .p-dialog-enter-from,\n    .p-dialog-bottomleft .p-dialog-leave-to {\n        transform: translate3d(-100%, 0px, 0px);\n    }\n\n    .p-dialog-right .p-dialog-enter-from,\n    .p-dialog-right .p-dialog-leave-to,\n    .p-dialog-topright .p-dialog-enter-from,\n    .p-dialog-topright .p-dialog-leave-to,\n    .p-dialog-bottomright .p-dialog-enter-from,\n    .p-dialog-bottomright .p-dialog-leave-to {\n        transform: translate3d(100%, 0px, 0px);\n    }\n\n    .p-dialog-left:dir(rtl) .p-dialog-enter-from,\n    .p-dialog-left:dir(rtl) .p-dialog-leave-to,\n    .p-dialog-topleft:dir(rtl) .p-dialog-enter-from,\n    .p-dialog-topleft:dir(rtl) .p-dialog-leave-to,\n    .p-dialog-bottomleft:dir(rtl) .p-dialog-enter-from,\n    .p-dialog-bottomleft:dir(rtl) .p-dialog-leave-to {\n        transform: translate3d(100%, 0px, 0px);\n    }\n\n    .p-dialog-right:dir(rtl) .p-dialog-enter-from,\n    .p-dialog-right:dir(rtl) .p-dialog-leave-to,\n    .p-dialog-topright:dir(rtl) .p-dialog-enter-from,\n    .p-dialog-topright:dir(rtl) .p-dialog-leave-to,\n    .p-dialog-bottomright:dir(rtl) .p-dialog-enter-from,\n    .p-dialog-bottomright:dir(rtl) .p-dialog-leave-to {\n        transform: translate3d(-100%, 0px, 0px);\n    }\n\n    .p-dialog-maximized {\n        width: 100vw !important;\n        height: 100vh !important;\n        top: 0px !important;\n        left: 0px !important;\n        max-height: 100%;\n        height: 100%;\n        border-radius: 0;\n    }\n\n    .p-dialog-maximized .p-dialog-content {\n        flex-grow: 1;\n    }\n\n    .p-dialog .p-resizable-handle {\n        position: absolute;\n        font-size: 0.1px;\n        display: block;\n        cursor: se-resize;\n        width: 12px;\n        height: 12px;\n        right: 1px;\n        bottom: 1px;\n    }\n";

// node_modules/primeng/fesm2022/primeng-dialog.mjs
var _c0 = ["header"];
var _c1 = ["content"];
var _c2 = ["footer"];
var _c3 = ["closeicon"];
var _c4 = ["maximizeicon"];
var _c5 = ["minimizeicon"];
var _c6 = ["headless"];
var _c7 = ["titlebar"];
var _c8 = ["*", [["p-footer"]]];
var _c9 = ["*", "p-footer"];
var _c10 = (a0, a1) => ({
  transform: a0,
  transition: a1
});
var _c11 = (a0) => ({
  value: "visible",
  params: a0
});
function Dialog_div_0_div_1_ng_container_2_ng_container_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function Dialog_div_0_div_1_ng_container_2_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainerStart(0);
    ɵɵtemplate(1, Dialog_div_0_div_1_ng_container_2_ng_container_1_Template, 1, 0, "ng-container", 11);
    ɵɵelementContainerEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(3);
    ɵɵadvance();
    ɵɵproperty("ngTemplateOutlet", ctx_r1._headlessTemplate || ctx_r1.headlessTemplate || ctx_r1.headlessT);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_0_Template(rf, ctx) {
  if (rf & 1) {
    const _r3 = ɵɵgetCurrentView();
    ɵɵelementStart(0, "div", 15);
    ɵɵlistener("mousedown", function Dialog_div_0_div_1_ng_template_3_div_0_Template_div_mousedown_0_listener($event) {
      ɵɵrestoreView(_r3);
      const ctx_r1 = ɵɵnextContext(4);
      return ɵɵresetView(ctx_r1.initResize($event));
    });
    ɵɵelementEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(4);
    ɵɵclassMap(ctx_r1.cx("resizeHandle"));
    ɵɵstyleProp("z-index", 90);
    ɵɵproperty("pBind", ctx_r1.ptm("resizeHandle"));
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_span_2_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementStart(0, "span", 19);
    ɵɵtext(1);
    ɵɵelementEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(5);
    ɵɵclassMap(ctx_r1.cx("title"));
    ɵɵproperty("id", ctx_r1.ariaLabelledBy)("pBind", ctx_r1.ptm("title"));
    ɵɵadvance();
    ɵɵtextInterpolate(ctx_r1.header);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_ng_container_3_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_span_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelement(0, "span", 23);
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(7);
    ɵɵproperty("ngClass", ctx_r1.maximized ? ctx_r1.minimizeIcon : ctx_r1.maximizeIcon);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_1__svg_svg_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵnamespaceSVG();
    ɵɵelement(0, "svg", 26);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_1__svg_svg_2_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵnamespaceSVG();
    ɵɵelement(0, "svg", 27);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainerStart(0);
    ɵɵtemplate(1, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_1__svg_svg_1_Template, 1, 0, "svg", 24)(2, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_1__svg_svg_2_Template, 1, 0, "svg", 25);
    ɵɵelementContainerEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(7);
    ɵɵadvance();
    ɵɵproperty("ngIf", !ctx_r1.maximized && !ctx_r1._maximizeiconTemplate && !ctx_r1.maximizeIconTemplate && !ctx_r1.maximizeIconT);
    ɵɵadvance();
    ɵɵproperty("ngIf", ctx_r1.maximized && !ctx_r1._minimizeiconTemplate && !ctx_r1.minimizeIconTemplate && !ctx_r1.minimizeIconT);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_2_1_ng_template_0_Template(rf, ctx) {
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_2_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_2_1_ng_template_0_Template, 0, 0, "ng-template");
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_2_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainerStart(0);
    ɵɵtemplate(1, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_2_1_Template, 1, 0, null, 11);
    ɵɵelementContainerEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(7);
    ɵɵadvance();
    ɵɵproperty("ngTemplateOutlet", ctx_r1._maximizeiconTemplate || ctx_r1.maximizeIconTemplate || ctx_r1.maximizeIconT);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_3_1_ng_template_0_Template(rf, ctx) {
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_3_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_3_1_ng_template_0_Template, 0, 0, "ng-template");
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_3_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainerStart(0);
    ɵɵtemplate(1, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_3_1_Template, 1, 0, null, 11);
    ɵɵelementContainerEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(7);
    ɵɵadvance();
    ɵɵproperty("ngTemplateOutlet", ctx_r1._minimizeiconTemplate || ctx_r1.minimizeIconTemplate || ctx_r1.minimizeIconT);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_span_0_Template, 1, 1, "span", 21)(1, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_1_Template, 3, 2, "ng-container", 22)(2, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_2_Template, 2, 1, "ng-container", 22)(3, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_ng_container_3_Template, 2, 1, "ng-container", 22);
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(6);
    ɵɵproperty("ngIf", ctx_r1.maximizeIcon && !ctx_r1._maximizeiconTemplate && !ctx_r1._minimizeiconTemplate);
    ɵɵadvance();
    ɵɵproperty("ngIf", !ctx_r1.maximizeIcon && !(ctx_r1.maximizeButtonProps == null ? null : ctx_r1.maximizeButtonProps.icon));
    ɵɵadvance();
    ɵɵproperty("ngIf", !ctx_r1.maximized);
    ɵɵadvance();
    ɵɵproperty("ngIf", ctx_r1.maximized);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_Template(rf, ctx) {
  if (rf & 1) {
    const _r5 = ɵɵgetCurrentView();
    ɵɵelementStart(0, "p-button", 20);
    ɵɵlistener("onClick", function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_Template_p_button_onClick_0_listener() {
      ɵɵrestoreView(_r5);
      const ctx_r1 = ɵɵnextContext(5);
      return ɵɵresetView(ctx_r1.maximize());
    })("keydown.enter", function Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_Template_p_button_keydown_enter_0_listener() {
      ɵɵrestoreView(_r5);
      const ctx_r1 = ɵɵnextContext(5);
      return ɵɵresetView(ctx_r1.maximize());
    });
    ɵɵtemplate(1, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_ng_template_1_Template, 4, 4, "ng-template", null, 4, ɵɵtemplateRefExtractor);
    ɵɵelementEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(5);
    ɵɵproperty("pt", ctx_r1.ptm("pcMaximizeButton"))("styleClass", ctx_r1.cx("pcMaximizeButton"))("ariaLabel", ctx_r1.maximized ? ctx_r1.minimizeLabel : ctx_r1.maximizeLabel)("tabindex", ctx_r1.maximizable ? "0" : "-1")("buttonProps", ctx_r1.maximizeButtonProps);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_ng_container_0_span_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelement(0, "span");
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(8);
    ɵɵclassMap(ctx_r1.closeIcon);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_ng_container_0__svg_svg_2_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵnamespaceSVG();
    ɵɵelement(0, "svg", 30);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_ng_container_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainerStart(0);
    ɵɵtemplate(1, Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_ng_container_0_span_1_Template, 1, 2, "span", 28)(2, Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_ng_container_0__svg_svg_2_Template, 1, 0, "svg", 29);
    ɵɵelementContainerEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(7);
    ɵɵadvance();
    ɵɵproperty("ngIf", ctx_r1.closeIcon);
    ɵɵadvance();
    ɵɵproperty("ngIf", !ctx_r1.closeIcon);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_span_1_1_ng_template_0_Template(rf, ctx) {
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_span_1_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_span_1_1_ng_template_0_Template, 0, 0, "ng-template");
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_span_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementStart(0, "span");
    ɵɵtemplate(1, Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_span_1_1_Template, 1, 0, null, 11);
    ɵɵelementEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(7);
    ɵɵadvance();
    ɵɵproperty("ngTemplateOutlet", ctx_r1._closeiconTemplate || ctx_r1.closeIconTemplate || ctx_r1.closeIconT);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_ng_container_0_Template, 3, 2, "ng-container", 22)(1, Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_span_1_Template, 2, 1, "span", 22);
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(6);
    ɵɵproperty("ngIf", !ctx_r1._closeiconTemplate && !ctx_r1.closeIconTemplate && !ctx_r1.closeIconT && !(ctx_r1.closeButtonProps == null ? null : ctx_r1.closeButtonProps.icon));
    ɵɵadvance();
    ɵɵproperty("ngIf", ctx_r1._closeiconTemplate || ctx_r1.closeIconTemplate || ctx_r1.closeIconT);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_Template(rf, ctx) {
  if (rf & 1) {
    const _r6 = ɵɵgetCurrentView();
    ɵɵelementStart(0, "p-button", 20);
    ɵɵlistener("onClick", function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_Template_p_button_onClick_0_listener($event) {
      ɵɵrestoreView(_r6);
      const ctx_r1 = ɵɵnextContext(5);
      return ɵɵresetView(ctx_r1.close($event));
    })("keydown.enter", function Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_Template_p_button_keydown_enter_0_listener($event) {
      ɵɵrestoreView(_r6);
      const ctx_r1 = ɵɵnextContext(5);
      return ɵɵresetView(ctx_r1.close($event));
    });
    ɵɵtemplate(1, Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_ng_template_1_Template, 2, 2, "ng-template", null, 4, ɵɵtemplateRefExtractor);
    ɵɵelementEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(5);
    ɵɵproperty("pt", ctx_r1.ptm("pcCloseButton"))("styleClass", ctx_r1.cx("pcCloseButton"))("ariaLabel", ctx_r1.closeAriaLabel)("tabindex", ctx_r1.closeTabindex)("buttonProps", ctx_r1.closeButtonProps);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_1_Template(rf, ctx) {
  if (rf & 1) {
    const _r4 = ɵɵgetCurrentView();
    ɵɵelementStart(0, "div", 15, 3);
    ɵɵlistener("mousedown", function Dialog_div_0_div_1_ng_template_3_div_1_Template_div_mousedown_0_listener($event) {
      ɵɵrestoreView(_r4);
      const ctx_r1 = ɵɵnextContext(4);
      return ɵɵresetView(ctx_r1.initDrag($event));
    });
    ɵɵtemplate(2, Dialog_div_0_div_1_ng_template_3_div_1_span_2_Template, 2, 5, "span", 16)(3, Dialog_div_0_div_1_ng_template_3_div_1_ng_container_3_Template, 1, 0, "ng-container", 11);
    ɵɵelementStart(4, "div", 17);
    ɵɵtemplate(5, Dialog_div_0_div_1_ng_template_3_div_1_p_button_5_Template, 3, 5, "p-button", 18)(6, Dialog_div_0_div_1_ng_template_3_div_1_p_button_6_Template, 3, 5, "p-button", 18);
    ɵɵelementEnd()();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(4);
    ɵɵclassMap(ctx_r1.cx("header"));
    ɵɵproperty("pBind", ctx_r1.ptm("header"));
    ɵɵadvance(2);
    ɵɵproperty("ngIf", !ctx_r1._headerTemplate && !ctx_r1.headerTemplate && !ctx_r1.headerT);
    ɵɵadvance();
    ɵɵproperty("ngTemplateOutlet", ctx_r1._headerTemplate || ctx_r1.headerTemplate || ctx_r1.headerT);
    ɵɵadvance();
    ɵɵclassMap(ctx_r1.cx("headerActions"));
    ɵɵproperty("pBind", ctx_r1.ptm("headerActions"));
    ɵɵadvance();
    ɵɵproperty("ngIf", ctx_r1.maximizable);
    ɵɵadvance();
    ɵɵproperty("ngIf", ctx_r1.closable);
  }
}
function Dialog_div_0_div_1_ng_template_3_ng_container_5_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_6_ng_container_3_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function Dialog_div_0_div_1_ng_template_3_div_6_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementStart(0, "div", 17, 5);
    ɵɵprojection(2, 1);
    ɵɵtemplate(3, Dialog_div_0_div_1_ng_template_3_div_6_ng_container_3_Template, 1, 0, "ng-container", 11);
    ɵɵelementEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(4);
    ɵɵclassMap(ctx_r1.cx("footer"));
    ɵɵproperty("pBind", ctx_r1.ptm("footer"));
    ɵɵadvance(3);
    ɵɵproperty("ngTemplateOutlet", ctx_r1._footerTemplate || ctx_r1.footerTemplate || ctx_r1.footerT);
  }
}
function Dialog_div_0_div_1_ng_template_3_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, Dialog_div_0_div_1_ng_template_3_div_0_Template, 1, 5, "div", 12)(1, Dialog_div_0_div_1_ng_template_3_div_1_Template, 7, 10, "div", 13);
    ɵɵelementStart(2, "div", 7, 2);
    ɵɵprojection(4);
    ɵɵtemplate(5, Dialog_div_0_div_1_ng_template_3_ng_container_5_Template, 1, 0, "ng-container", 11);
    ɵɵelementEnd();
    ɵɵtemplate(6, Dialog_div_0_div_1_ng_template_3_div_6_Template, 4, 4, "div", 14);
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext(3);
    ɵɵproperty("ngIf", ctx_r1.resizable);
    ɵɵadvance();
    ɵɵproperty("ngIf", ctx_r1.showHeader);
    ɵɵadvance();
    ɵɵclassMap(ctx_r1.cn(ctx_r1.cx("content"), ctx_r1.contentStyleClass));
    ɵɵproperty("ngStyle", ctx_r1.contentStyle)("pBind", ctx_r1.ptm("content"));
    ɵɵadvance(3);
    ɵɵproperty("ngTemplateOutlet", ctx_r1._contentTemplate || ctx_r1.contentTemplate || ctx_r1.contentT);
    ɵɵadvance();
    ɵɵproperty("ngIf", ctx_r1._footerTemplate || ctx_r1.footerTemplate || ctx_r1.footerT);
  }
}
function Dialog_div_0_div_1_Template(rf, ctx) {
  if (rf & 1) {
    const _r1 = ɵɵgetCurrentView();
    ɵɵelementStart(0, "div", 9, 0);
    ɵɵlistener("@animation.start", function Dialog_div_0_div_1_Template_div_animation_animation_start_0_listener($event) {
      ɵɵrestoreView(_r1);
      const ctx_r1 = ɵɵnextContext(2);
      return ɵɵresetView(ctx_r1.onAnimationStart($event));
    })("@animation.done", function Dialog_div_0_div_1_Template_div_animation_animation_done_0_listener($event) {
      ɵɵrestoreView(_r1);
      const ctx_r1 = ɵɵnextContext(2);
      return ɵɵresetView(ctx_r1.onAnimationEnd($event));
    });
    ɵɵtemplate(2, Dialog_div_0_div_1_ng_container_2_Template, 2, 1, "ng-container", 10)(3, Dialog_div_0_div_1_ng_template_3_Template, 7, 8, "ng-template", null, 1, ɵɵtemplateRefExtractor);
    ɵɵelementEnd();
  }
  if (rf & 2) {
    const notHeadless_r7 = ɵɵreference(4);
    const ctx_r1 = ɵɵnextContext(2);
    ɵɵstyleMap(ctx_r1.sx("root"));
    ɵɵclassMap(ctx_r1.cn(ctx_r1.cx("root"), ctx_r1.styleClass));
    ɵɵproperty("ngStyle", ctx_r1.style)("pBind", ctx_r1.ptm("root"))("pFocusTrapDisabled", ctx_r1.focusTrap === false)("@animation", ɵɵpureFunction1(16, _c11, ɵɵpureFunction2(13, _c10, ctx_r1.transformOptions, ctx_r1.transitionOptions)));
    ɵɵattribute("role", ctx_r1.role)("aria-labelledby", ctx_r1.ariaLabelledBy)("aria-modal", true);
    ɵɵadvance(2);
    ɵɵproperty("ngIf", ctx_r1._headlessTemplate || ctx_r1.headlessTemplate || ctx_r1.headlessT)("ngIfElse", notHeadless_r7);
  }
}
function Dialog_div_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementStart(0, "div", 7);
    ɵɵtemplate(1, Dialog_div_0_div_1_Template, 5, 18, "div", 8);
    ɵɵelementEnd();
  }
  if (rf & 2) {
    const ctx_r1 = ɵɵnextContext();
    ɵɵstyleMap(ctx_r1.sx("mask"));
    ɵɵclassMap(ctx_r1.cn(ctx_r1.cx("mask"), ctx_r1.maskStyleClass));
    ɵɵproperty("ngStyle", ctx_r1.maskStyle)("pBind", ctx_r1.ptm("mask"));
    ɵɵadvance();
    ɵɵproperty("ngIf", ctx_r1.visible);
  }
}
var inlineStyles = {
  mask: ({
    instance
  }) => ({
    position: "fixed",
    height: "100%",
    width: "100%",
    left: 0,
    top: 0,
    display: "flex",
    justifyContent: instance.position === "left" || instance.position === "topleft" || instance.position === "bottomleft" ? "flex-start" : instance.position === "right" || instance.position === "topright" || instance.position === "bottomright" ? "flex-end" : "center",
    alignItems: instance.position === "top" || instance.position === "topleft" || instance.position === "topright" ? "flex-start" : instance.position === "bottom" || instance.position === "bottomleft" || instance.position === "bottomright" ? "flex-end" : "center",
    pointerEvents: instance.modal ? "auto" : "none"
  }),
  root: {
    display: "flex",
    flexDirection: "column",
    pointerEvents: "auto"
  }
};
var classes = {
  mask: ({
    instance
  }) => {
    const positions = ["left", "right", "top", "topleft", "topright", "bottom", "bottomleft", "bottomright"];
    const pos = positions.find((item) => item === instance.position);
    return ["p-dialog-mask", {
      "p-overlay-mask p-overlay-mask-enter": instance.modal
    }, pos ? `p-dialog-${pos}` : ""];
  },
  root: ({
    instance
  }) => ["p-dialog p-component", {
    "p-dialog-maximized": instance.maximizable && instance.maximized
  }],
  header: "p-dialog-header",
  title: "p-dialog-title",
  resizeHandle: "p-resizable-handle",
  headerActions: "p-dialog-header-actions",
  pcMaximizeButton: "p-dialog-maximize-button",
  pcCloseButton: "p-dialog-close-button",
  content: () => ["p-dialog-content"],
  footer: "p-dialog-footer"
};
var DialogStyle = class _DialogStyle extends BaseStyle {
  name = "dialog";
  style = style2;
  classes = classes;
  inlineStyles = inlineStyles;
  static ɵfac = /* @__PURE__ */ (() => {
    let ɵDialogStyle_BaseFactory;
    return function DialogStyle_Factory(__ngFactoryType__) {
      return (ɵDialogStyle_BaseFactory || (ɵDialogStyle_BaseFactory = ɵɵgetInheritedFactory(_DialogStyle)))(__ngFactoryType__ || _DialogStyle);
    };
  })();
  static ɵprov = ɵɵdefineInjectable({
    token: _DialogStyle,
    factory: _DialogStyle.ɵfac
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DialogStyle, [{
    type: Injectable
  }], null, null);
})();
var DialogClasses;
(function(DialogClasses2) {
  DialogClasses2["mask"] = "p-dialog-mask";
  DialogClasses2["root"] = "p-dialog";
  DialogClasses2["header"] = "p-dialog-header";
  DialogClasses2["title"] = "p-dialog-title";
  DialogClasses2["headerActions"] = "p-dialog-header-actions";
  DialogClasses2["pcMaximizeButton"] = "p-dialog-maximize-button";
  DialogClasses2["pcCloseButton"] = "p-dialog-close-button";
  DialogClasses2["content"] = "p-dialog-content";
  DialogClasses2["footer"] = "p-dialog-footer";
})(DialogClasses || (DialogClasses = {}));
var DIALOG_INSTANCE = new InjectionToken("DIALOG_INSTANCE");
var showAnimation = animation([style({
  transform: "{{transform}}",
  opacity: 0
}), animate("{{transition}}")]);
var hideAnimation = animation([animate("{{transition}}", style({
  transform: "{{transform}}",
  opacity: 0
}))]);
var Dialog = class _Dialog extends BaseComponent {
  hostName = "";
  $pcDialog = inject(DIALOG_INSTANCE, {
    optional: true,
    skipSelf: true
  }) ?? void 0;
  bindDirectiveInstance = inject(Bind, {
    self: true
  });
  onAfterViewChecked() {
    this.bindDirectiveInstance.setAttrs(this.ptm("host"));
  }
  /**
   * Title text of the dialog.
   * @group Props
   */
  header;
  /**
   * Enables dragging to change the position using header.
   * @group Props
   */
  draggable = true;
  /**
   * Enables resizing of the content.
   * @group Props
   */
  resizable = true;
  /**
   * Style of the content section.
   * @group Props
   */
  contentStyle;
  /**
   * Style class of the content.
   * @group Props
   */
  contentStyleClass;
  /**
   * Defines if background should be blocked when dialog is displayed.
   * @group Props
   */
  modal = false;
  /**
   * Specifies if pressing escape key should hide the dialog.
   * @group Props
   */
  closeOnEscape = true;
  /**
   * Specifies if clicking the modal background should hide the dialog.
   * @group Props
   */
  dismissableMask = false;
  /**
   * When enabled dialog is displayed in RTL direction.
   * @group Props
   */
  rtl = false;
  /**
   * Adds a close icon to the header to hide the dialog.
   * @group Props
   */
  closable = true;
  /**
   * Object literal to define widths per screen size.
   * @group Props
   */
  breakpoints;
  /**
   * Style class of the component.
   * @group Props
   */
  styleClass;
  /**
   * Style class of the mask.
   * @group Props
   */
  maskStyleClass;
  /**
   * Style of the mask.
   * @group Props
   */
  maskStyle;
  /**
   * Whether to show the header or not.
   * @group Props
   */
  showHeader = true;
  /**
   * Whether background scroll should be blocked when dialog is visible.
   * @group Props
   */
  blockScroll = false;
  /**
   * Whether to automatically manage layering.
   * @group Props
   */
  autoZIndex = true;
  /**
   * Base zIndex value to use in layering.
   * @group Props
   */
  baseZIndex = 0;
  /**
   * Minimum value for the left coordinate of dialog in dragging.
   * @group Props
   */
  minX = 0;
  /**
   * Minimum value for the top coordinate of dialog in dragging.
   * @group Props
   */
  minY = 0;
  /**
   * When enabled, first focusable element receives focus on show.
   * @group Props
   */
  focusOnShow = true;
  /**
   * Whether the dialog can be displayed full screen.
   * @group Props
   */
  maximizable = false;
  /**
   * Keeps dialog in the viewport.
   * @group Props
   */
  keepInViewport = true;
  /**
   * When enabled, can only focus on elements inside the dialog.
   * @group Props
   */
  focusTrap = true;
  /**
   * Transition options of the animation.
   * @group Props
   */
  transitionOptions = "150ms cubic-bezier(0, 0, 0.2, 1)";
  /**
   * Name of the close icon.
   * @group Props
   */
  closeIcon;
  /**
   * Defines a string that labels the close button for accessibility.
   * @group Props
   */
  closeAriaLabel;
  /**
   * Index of the close button in tabbing order.
   * @group Props
   */
  closeTabindex = "0";
  /**
   * Name of the minimize icon.
   * @group Props
   */
  minimizeIcon;
  /**
   * Name of the maximize icon.
   * @group Props
   */
  maximizeIcon;
  /**
   * Used to pass all properties of the ButtonProps to the Button component.
   * @group Props
   */
  closeButtonProps = {
    severity: "secondary",
    variant: "text",
    rounded: true
  };
  /**
   * Used to pass all properties of the ButtonProps to the Button component.
   * @group Props
   */
  maximizeButtonProps = {
    severity: "secondary",
    variant: "text",
    rounded: true
  };
  /**
   * Specifies the visibility of the dialog.
   * @group Props
   */
  get visible() {
    return this._visible;
  }
  set visible(value) {
    this._visible = value;
    if (this._visible && !this.maskVisible) {
      this.maskVisible = true;
    }
  }
  /**
   * Inline style of the component.
   * @group Props
   */
  get style() {
    return this._style;
  }
  set style(value) {
    if (value) {
      this._style = __spreadValues({}, value);
      this.originalStyle = value;
    }
  }
  /**
   * Position of the dialog.
   * @group Props
   */
  get position() {
    return this._position;
  }
  set position(value) {
    this._position = value;
    switch (value) {
      case "topleft":
      case "bottomleft":
      case "left":
        this.transformOptions = "translate3d(-100%, 0px, 0px)";
        break;
      case "topright":
      case "bottomright":
      case "right":
        this.transformOptions = "translate3d(100%, 0px, 0px)";
        break;
      case "bottom":
        this.transformOptions = "translate3d(0px, 100%, 0px)";
        break;
      case "top":
        this.transformOptions = "translate3d(0px, -100%, 0px)";
        break;
      default:
        this.transformOptions = "scale(0.7)";
        break;
    }
  }
  /**
   * Role attribute of html element.
   * @group Emits
   */
  role = "dialog";
  /**
   * Target element to attach the overlay, valid values are "body" or a local ng-template variable of another element (note: use binding with brackets for template variables, e.g. [appendTo]="mydiv" for a div element having #mydiv as variable name).
   * @defaultValue 'self'
   * @group Props
   */
  appendTo = input(void 0, ...ngDevMode ? [{
    debugName: "appendTo"
  }] : []);
  /**
   * Callback to invoke when dialog is shown.
   * @group Emits
   */
  onShow = new EventEmitter();
  /**
   * Callback to invoke when dialog is hidden.
   * @group Emits
   */
  onHide = new EventEmitter();
  /**
   * This EventEmitter is used to notify changes in the visibility state of a component.
   * @param {boolean} value - New value.
   * @group Emits
   */
  visibleChange = new EventEmitter();
  /**
   * Callback to invoke when dialog resizing is initiated.
   * @param {MouseEvent} event - Mouse event.
   * @group Emits
   */
  onResizeInit = new EventEmitter();
  /**
   * Callback to invoke when dialog resizing is completed.
   * @param {MouseEvent} event - Mouse event.
   * @group Emits
   */
  onResizeEnd = new EventEmitter();
  /**
   * Callback to invoke when dialog dragging is completed.
   * @param {DragEvent} event - Drag event.
   * @group Emits
   */
  onDragEnd = new EventEmitter();
  /**
   * Callback to invoke when dialog maximized or unmaximized.
   * @group Emits
   */
  onMaximize = new EventEmitter();
  headerViewChild;
  contentViewChild;
  footerViewChild;
  /**
   * Header template.
   * @group Props
   */
  headerTemplate;
  /**
   * Content template.
   * @group Props
   */
  contentTemplate;
  /**
   * Footer template.
   * @group Props
   */
  footerTemplate;
  /**
   * Close icon template.
   * @group Props
   */
  closeIconTemplate;
  /**
   * Maximize icon template.
   * @group Props
   */
  maximizeIconTemplate;
  /**
   * Minimize icon template.
   * @group Props
   */
  minimizeIconTemplate;
  /**
   * Headless template.
   * @group Props
   */
  headlessTemplate;
  _headerTemplate;
  _contentTemplate;
  _footerTemplate;
  _closeiconTemplate;
  _maximizeiconTemplate;
  _minimizeiconTemplate;
  _headlessTemplate;
  $appendTo = computed(() => this.appendTo() || this.config.overlayAppendTo(), ...ngDevMode ? [{
    debugName: "$appendTo"
  }] : []);
  _visible = false;
  maskVisible;
  container;
  wrapper;
  dragging;
  ariaLabelledBy = this.getAriaLabelledBy();
  documentDragListener;
  documentDragEndListener;
  resizing;
  documentResizeListener;
  documentResizeEndListener;
  documentEscapeListener;
  maskClickListener;
  lastPageX;
  lastPageY;
  preventVisibleChangePropagation;
  maximized;
  preMaximizeContentHeight;
  preMaximizeContainerWidth;
  preMaximizeContainerHeight;
  preMaximizePageX;
  preMaximizePageY;
  id = s("pn_id_");
  _style = {};
  _position = "center";
  originalStyle;
  transformOptions = "scale(0.7)";
  styleElement;
  window;
  _componentStyle = inject(DialogStyle);
  headerT;
  contentT;
  footerT;
  closeIconT;
  maximizeIconT;
  minimizeIconT;
  headlessT;
  zIndexForLayering;
  get maximizeLabel() {
    return this.config.getTranslation(TranslationKeys.ARIA)["maximizeLabel"];
  }
  get minimizeLabel() {
    return this.config.getTranslation(TranslationKeys.ARIA)["minimizeLabel"];
  }
  zone = inject(NgZone);
  get maskClass() {
    const positions = ["left", "right", "top", "topleft", "topright", "bottom", "bottomleft", "bottomright"];
    const pos = positions.find((item) => item === this.position);
    return {
      "p-dialog-mask": true,
      "p-overlay-mask p-overlay-mask-enter": this.modal || this.dismissableMask,
      [`p-dialog-${pos}`]: pos
    };
  }
  onInit() {
    if (this.breakpoints) {
      this.createStyle();
    }
  }
  templates;
  onAfterContentInit() {
    this.templates?.forEach((item) => {
      switch (item.getType()) {
        case "header":
          this.headerT = item.template;
          break;
        case "content":
          this.contentT = item.template;
          break;
        case "footer":
          this.footerT = item.template;
          break;
        case "closeicon":
          this.closeIconT = item.template;
          break;
        case "maximizeicon":
          this.maximizeIconT = item.template;
          break;
        case "minimizeicon":
          this.minimizeIconT = item.template;
          break;
        case "headless":
          this.headlessT = item.template;
          break;
        default:
          this.contentT = item.template;
          break;
      }
    });
  }
  getAriaLabelledBy() {
    return this.header !== null ? s("pn_id_") + "_header" : null;
  }
  parseDurationToMilliseconds(durationString) {
    const transitionTimeRegex = /([\d\.]+)(ms|s)\b/g;
    let totalMilliseconds = 0;
    let match;
    while ((match = transitionTimeRegex.exec(durationString)) !== null) {
      const value = parseFloat(match[1]);
      const unit = match[2];
      if (unit === "ms") {
        totalMilliseconds += value;
      } else if (unit === "s") {
        totalMilliseconds += value * 1e3;
      }
    }
    if (totalMilliseconds === 0) {
      return void 0;
    }
    return totalMilliseconds;
  }
  _focus(focusParentElement) {
    if (focusParentElement) {
      const timeoutDuration = this.parseDurationToMilliseconds(this.transitionOptions);
      let _focusableElements = DomHandler.getFocusableElements(focusParentElement);
      if (_focusableElements && _focusableElements.length > 0) {
        this.zone.runOutsideAngular(() => {
          setTimeout(() => _focusableElements[0].focus(), timeoutDuration || 5);
        });
        return true;
      }
    }
    return false;
  }
  focus(focusParentElement = this.contentViewChild?.nativeElement) {
    let focused = this._focus(focusParentElement);
    if (!focused) {
      focused = this._focus(this.footerViewChild?.nativeElement);
      if (!focused) {
        focused = this._focus(this.headerViewChild?.nativeElement);
        if (!focused) {
          this._focus(this.contentViewChild?.nativeElement);
        }
      }
    }
  }
  close(event) {
    this.visibleChange.emit(false);
    event.preventDefault();
  }
  enableModality() {
    if (this.closable && this.dismissableMask) {
      this.maskClickListener = this.renderer.listen(this.wrapper, "mousedown", (event) => {
        if (this.wrapper && this.wrapper.isSameNode(event.target)) {
          this.close(event);
        }
      });
    }
    if (this.modal) {
      blockBodyScroll();
    }
  }
  disableModality() {
    if (this.wrapper) {
      if (this.dismissableMask) {
        this.unbindMaskClickListener();
      }
      const scrollBlockers = document.querySelectorAll(".p-dialog-mask-scrollblocker");
      if (this.modal && scrollBlockers && scrollBlockers.length == 1) {
        unblockBodyScroll();
      }
      if (!this.cd.destroyed) {
        this.cd.detectChanges();
      }
    }
  }
  maximize() {
    this.maximized = !this.maximized;
    if (!this.modal && !this.blockScroll) {
      if (this.maximized) {
        blockBodyScroll();
      } else {
        unblockBodyScroll();
      }
    }
    this.onMaximize.emit({
      maximized: this.maximized
    });
  }
  unbindMaskClickListener() {
    if (this.maskClickListener) {
      this.maskClickListener();
      this.maskClickListener = null;
    }
  }
  moveOnTop() {
    if (this.autoZIndex) {
      zindexutils.set("modal", this.container, this.baseZIndex + this.config.zIndex.modal);
      this.wrapper.style.zIndex = String(parseInt(this.container.style.zIndex, 10) - 1);
    } else {
      this.zIndexForLayering = zindexutils.generateZIndex("modal", (this.baseZIndex ?? 0) + this.config.zIndex.modal);
    }
  }
  createStyle() {
    if (isPlatformBrowser(this.platformId)) {
      if (!this.styleElement) {
        this.styleElement = this.renderer.createElement("style");
        this.styleElement.type = "text/css";
        _t(this.styleElement, "nonce", this.config?.csp()?.nonce);
        this.renderer.appendChild(this.document.head, this.styleElement);
        let innerHTML = "";
        for (let breakpoint in this.breakpoints) {
          innerHTML += `
                        @media screen and (max-width: ${breakpoint}) {
                            .p-dialog[${this.id}]:not(.p-dialog-maximized) {
                                width: ${this.breakpoints[breakpoint]} !important;
                            }
                        }
                    `;
        }
        this.renderer.setProperty(this.styleElement, "innerHTML", innerHTML);
        _t(this.styleElement, "nonce", this.config?.csp()?.nonce);
      }
    }
  }
  initDrag(event) {
    if (R(event.target, "p-dialog-maximize-icon") || R(event.target, "p-dialog-header-close-icon") || R(event.target?.parentElement, "p-dialog-header-icon")) {
      return;
    }
    if (this.draggable) {
      this.dragging = true;
      this.lastPageX = event.pageX;
      this.lastPageY = event.pageY;
      this.container.style.margin = "0";
      W(this.document.body, "p-unselectable-text");
    }
  }
  onDrag(event) {
    if (this.dragging && this.container) {
      const containerWidth = v(this.container);
      const containerHeight = C(this.container);
      const deltaX = event.pageX - this.lastPageX;
      const deltaY = event.pageY - this.lastPageY;
      const offset = this.container.getBoundingClientRect();
      const containerComputedStyle = getComputedStyle(this.container);
      const leftMargin = parseFloat(containerComputedStyle.marginLeft);
      const topMargin = parseFloat(containerComputedStyle.marginTop);
      const leftPos = offset.left + deltaX - leftMargin;
      const topPos = offset.top + deltaY - topMargin;
      const viewport = h();
      this.container.style.position = "fixed";
      if (this.keepInViewport) {
        if (leftPos >= this.minX && leftPos + containerWidth < viewport.width) {
          this._style.left = `${leftPos}px`;
          this.lastPageX = event.pageX;
          this.container.style.left = `${leftPos}px`;
        }
        if (topPos >= this.minY && topPos + containerHeight < viewport.height) {
          this._style.top = `${topPos}px`;
          this.lastPageY = event.pageY;
          this.container.style.top = `${topPos}px`;
        }
      } else {
        this.lastPageX = event.pageX;
        this.container.style.left = `${leftPos}px`;
        this.lastPageY = event.pageY;
        this.container.style.top = `${topPos}px`;
      }
    }
  }
  endDrag(event) {
    if (this.dragging) {
      this.dragging = false;
      P(this.document.body, "p-unselectable-text");
      this.cd.detectChanges();
      this.onDragEnd.emit(event);
    }
  }
  resetPosition() {
    this.container.style.position = "";
    this.container.style.left = "";
    this.container.style.top = "";
    this.container.style.margin = "";
  }
  //backward compatibility
  center() {
    this.resetPosition();
  }
  initResize(event) {
    if (this.resizable) {
      this.resizing = true;
      this.lastPageX = event.pageX;
      this.lastPageY = event.pageY;
      W(this.document.body, "p-unselectable-text");
      this.onResizeInit.emit(event);
    }
  }
  onResize(event) {
    if (this.resizing) {
      let deltaX = event.pageX - this.lastPageX;
      let deltaY = event.pageY - this.lastPageY;
      let containerWidth = v(this.container);
      let containerHeight = C(this.container);
      let contentHeight = C(this.contentViewChild?.nativeElement);
      let newWidth = containerWidth + deltaX;
      let newHeight = containerHeight + deltaY;
      let minWidth = this.container.style.minWidth;
      let minHeight = this.container.style.minHeight;
      let offset = this.container.getBoundingClientRect();
      let viewport = h();
      let hasBeenDragged = !parseInt(this.container.style.top) || !parseInt(this.container.style.left);
      if (hasBeenDragged) {
        newWidth += deltaX;
        newHeight += deltaY;
      }
      if ((!minWidth || newWidth > parseInt(minWidth)) && offset.left + newWidth < viewport.width) {
        this._style.width = newWidth + "px";
        this.container.style.width = this._style.width;
      }
      if ((!minHeight || newHeight > parseInt(minHeight)) && offset.top + newHeight < viewport.height) {
        this.contentViewChild.nativeElement.style.height = contentHeight + newHeight - containerHeight + "px";
        if (this._style.height) {
          this._style.height = newHeight + "px";
          this.container.style.height = this._style.height;
        }
      }
      this.lastPageX = event.pageX;
      this.lastPageY = event.pageY;
    }
  }
  resizeEnd(event) {
    if (this.resizing) {
      this.resizing = false;
      P(this.document.body, "p-unselectable-text");
      this.onResizeEnd.emit(event);
    }
  }
  bindGlobalListeners() {
    if (this.draggable) {
      this.bindDocumentDragListener();
      this.bindDocumentDragEndListener();
    }
    if (this.resizable) {
      this.bindDocumentResizeListeners();
    }
    if (this.closeOnEscape && this.closable) {
      this.bindDocumentEscapeListener();
    }
  }
  unbindGlobalListeners() {
    this.unbindDocumentDragListener();
    this.unbindDocumentDragEndListener();
    this.unbindDocumentResizeListeners();
    this.unbindDocumentEscapeListener();
  }
  bindDocumentDragListener() {
    if (!this.documentDragListener) {
      this.zone.runOutsideAngular(() => {
        this.documentDragListener = this.renderer.listen(this.document.defaultView, "mousemove", this.onDrag.bind(this));
      });
    }
  }
  unbindDocumentDragListener() {
    if (this.documentDragListener) {
      this.documentDragListener();
      this.documentDragListener = null;
    }
  }
  bindDocumentDragEndListener() {
    if (!this.documentDragEndListener) {
      this.zone.runOutsideAngular(() => {
        this.documentDragEndListener = this.renderer.listen(this.document.defaultView, "mouseup", this.endDrag.bind(this));
      });
    }
  }
  unbindDocumentDragEndListener() {
    if (this.documentDragEndListener) {
      this.documentDragEndListener();
      this.documentDragEndListener = null;
    }
  }
  bindDocumentResizeListeners() {
    if (!this.documentResizeListener && !this.documentResizeEndListener) {
      this.zone.runOutsideAngular(() => {
        this.documentResizeListener = this.renderer.listen(this.document.defaultView, "mousemove", this.onResize.bind(this));
        this.documentResizeEndListener = this.renderer.listen(this.document.defaultView, "mouseup", this.resizeEnd.bind(this));
      });
    }
  }
  unbindDocumentResizeListeners() {
    if (this.documentResizeListener && this.documentResizeEndListener) {
      this.documentResizeListener();
      this.documentResizeEndListener();
      this.documentResizeListener = null;
      this.documentResizeEndListener = null;
    }
  }
  bindDocumentEscapeListener() {
    const documentTarget = this.el ? this.el.nativeElement.ownerDocument : "document";
    this.documentEscapeListener = this.renderer.listen(documentTarget, "keydown", (event) => {
      if (event.key == "Escape") {
        const currentZIndex = zindexutils.getCurrent();
        if (parseInt(this.container.style.zIndex) == currentZIndex || this.zIndexForLayering == currentZIndex) {
          this.close(event);
        }
      }
    });
  }
  unbindDocumentEscapeListener() {
    if (this.documentEscapeListener) {
      this.documentEscapeListener();
      this.documentEscapeListener = null;
    }
  }
  appendContainer() {
    if (this.$appendTo() && this.$appendTo() !== "self") {
      if (this.$appendTo() === "body") this.renderer.appendChild(this.document.body, this.wrapper);
      else ut(this.$appendTo(), this.wrapper);
    }
  }
  restoreAppend() {
    if (this.container && this.$appendTo() !== "self") {
      this.renderer.appendChild(this.el.nativeElement, this.wrapper);
    }
  }
  onAnimationStart(event) {
    switch (event.toState) {
      case "visible":
        this.container = event.element;
        this.wrapper = this.container?.parentElement;
        this.$attrSelector && this.container?.setAttribute(this.$attrSelector, "");
        this.appendContainer();
        this.moveOnTop();
        this.bindGlobalListeners();
        this.container?.setAttribute(this.id, "");
        if (this.modal) {
          this.enableModality();
        }
        if (this.focusOnShow) {
          this.focus();
        }
        break;
      case "void":
        if (this.wrapper && this.modal) {
          W(this.wrapper, "p-overlay-mask-leave");
        }
        break;
    }
  }
  onAnimationEnd(event) {
    switch (event.toState) {
      case "void":
        this.onContainerDestroy();
        this.onHide.emit({});
        this.cd.markForCheck();
        if (this.maskVisible !== this.visible) {
          this.maskVisible = this.visible;
        }
        break;
      case "visible":
        this.onShow.emit({});
        break;
    }
  }
  onContainerDestroy() {
    this.unbindGlobalListeners();
    this.dragging = false;
    this.maskVisible = false;
    if (this.maximized) {
      this.document.body.style.removeProperty("--scrollbar;-width");
      this.maximized = false;
    }
    if (this.modal) {
      this.disableModality();
    }
    if (R(this.document.body, "p-overflow-hidden")) {
      P(this.document.body, "p-overflow-hidden");
    }
    if (this.container && this.autoZIndex) {
      zindexutils.clear(this.container);
    }
    if (this.zIndexForLayering) {
      zindexutils.revertZIndex(this.zIndexForLayering);
    }
    this.container = null;
    this.wrapper = null;
    this._style = this.originalStyle ? __spreadValues({}, this.originalStyle) : {};
  }
  destroyStyle() {
    if (this.styleElement) {
      this.renderer.removeChild(this.document.head, this.styleElement);
      this.styleElement = null;
    }
  }
  onDestroy() {
    if (this.container) {
      this.restoreAppend();
      this.onContainerDestroy();
    }
    this.destroyStyle();
  }
  static ɵfac = /* @__PURE__ */ (() => {
    let ɵDialog_BaseFactory;
    return function Dialog_Factory(__ngFactoryType__) {
      return (ɵDialog_BaseFactory || (ɵDialog_BaseFactory = ɵɵgetInheritedFactory(_Dialog)))(__ngFactoryType__ || _Dialog);
    };
  })();
  static ɵcmp = ɵɵdefineComponent({
    type: _Dialog,
    selectors: [["p-dialog"]],
    contentQueries: function Dialog_ContentQueries(rf, ctx, dirIndex) {
      if (rf & 1) {
        ɵɵcontentQuery(dirIndex, _c0, 4);
        ɵɵcontentQuery(dirIndex, _c1, 4);
        ɵɵcontentQuery(dirIndex, _c2, 4);
        ɵɵcontentQuery(dirIndex, _c3, 4);
        ɵɵcontentQuery(dirIndex, _c4, 4);
        ɵɵcontentQuery(dirIndex, _c5, 4);
        ɵɵcontentQuery(dirIndex, _c6, 4);
        ɵɵcontentQuery(dirIndex, PrimeTemplate, 4);
      }
      if (rf & 2) {
        let _t2;
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx._headerTemplate = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx._contentTemplate = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx._footerTemplate = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx._closeiconTemplate = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx._maximizeiconTemplate = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx._minimizeiconTemplate = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx._headlessTemplate = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx.templates = _t2);
      }
    },
    viewQuery: function Dialog_Query(rf, ctx) {
      if (rf & 1) {
        ɵɵviewQuery(_c7, 5);
        ɵɵviewQuery(_c1, 5);
        ɵɵviewQuery(_c2, 5);
      }
      if (rf & 2) {
        let _t2;
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx.headerViewChild = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx.contentViewChild = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx.footerViewChild = _t2.first);
      }
    },
    inputs: {
      hostName: "hostName",
      header: "header",
      draggable: [2, "draggable", "draggable", booleanAttribute],
      resizable: [2, "resizable", "resizable", booleanAttribute],
      contentStyle: "contentStyle",
      contentStyleClass: "contentStyleClass",
      modal: [2, "modal", "modal", booleanAttribute],
      closeOnEscape: [2, "closeOnEscape", "closeOnEscape", booleanAttribute],
      dismissableMask: [2, "dismissableMask", "dismissableMask", booleanAttribute],
      rtl: [2, "rtl", "rtl", booleanAttribute],
      closable: [2, "closable", "closable", booleanAttribute],
      breakpoints: "breakpoints",
      styleClass: "styleClass",
      maskStyleClass: "maskStyleClass",
      maskStyle: "maskStyle",
      showHeader: [2, "showHeader", "showHeader", booleanAttribute],
      blockScroll: [2, "blockScroll", "blockScroll", booleanAttribute],
      autoZIndex: [2, "autoZIndex", "autoZIndex", booleanAttribute],
      baseZIndex: [2, "baseZIndex", "baseZIndex", numberAttribute],
      minX: [2, "minX", "minX", numberAttribute],
      minY: [2, "minY", "minY", numberAttribute],
      focusOnShow: [2, "focusOnShow", "focusOnShow", booleanAttribute],
      maximizable: [2, "maximizable", "maximizable", booleanAttribute],
      keepInViewport: [2, "keepInViewport", "keepInViewport", booleanAttribute],
      focusTrap: [2, "focusTrap", "focusTrap", booleanAttribute],
      transitionOptions: "transitionOptions",
      closeIcon: "closeIcon",
      closeAriaLabel: "closeAriaLabel",
      closeTabindex: "closeTabindex",
      minimizeIcon: "minimizeIcon",
      maximizeIcon: "maximizeIcon",
      closeButtonProps: "closeButtonProps",
      maximizeButtonProps: "maximizeButtonProps",
      visible: "visible",
      style: "style",
      position: "position",
      role: "role",
      appendTo: [1, "appendTo"],
      headerTemplate: [0, "content", "headerTemplate"],
      contentTemplate: "contentTemplate",
      footerTemplate: "footerTemplate",
      closeIconTemplate: "closeIconTemplate",
      maximizeIconTemplate: "maximizeIconTemplate",
      minimizeIconTemplate: "minimizeIconTemplate",
      headlessTemplate: "headlessTemplate"
    },
    outputs: {
      onShow: "onShow",
      onHide: "onHide",
      visibleChange: "visibleChange",
      onResizeInit: "onResizeInit",
      onResizeEnd: "onResizeEnd",
      onDragEnd: "onDragEnd",
      onMaximize: "onMaximize"
    },
    features: [ɵɵProvidersFeature([DialogStyle, {
      provide: DIALOG_INSTANCE,
      useExisting: _Dialog
    }, {
      provide: PARENT_INSTANCE,
      useExisting: _Dialog
    }]), ɵɵHostDirectivesFeature([Bind]), ɵɵInheritDefinitionFeature],
    ngContentSelectors: _c9,
    decls: 1,
    vars: 1,
    consts: [["container", ""], ["notHeadless", ""], ["content", ""], ["titlebar", ""], ["icon", ""], ["footer", ""], [3, "class", "style", "ngStyle", "pBind", 4, "ngIf"], [3, "ngStyle", "pBind"], ["pFocusTrap", "", 3, "class", "style", "ngStyle", "pBind", "pFocusTrapDisabled", 4, "ngIf"], ["pFocusTrap", "", 3, "ngStyle", "pBind", "pFocusTrapDisabled"], [4, "ngIf", "ngIfElse"], [4, "ngTemplateOutlet"], [3, "class", "pBind", "z-index", "mousedown", 4, "ngIf"], [3, "class", "pBind", "mousedown", 4, "ngIf"], [3, "class", "pBind", 4, "ngIf"], [3, "mousedown", "pBind"], [3, "id", "class", "pBind", 4, "ngIf"], [3, "pBind"], [3, "pt", "styleClass", "ariaLabel", "tabindex", "buttonProps", "onClick", "keydown.enter", 4, "ngIf"], [3, "id", "pBind"], [3, "onClick", "keydown.enter", "pt", "styleClass", "ariaLabel", "tabindex", "buttonProps"], [3, "ngClass", 4, "ngIf"], [4, "ngIf"], [3, "ngClass"], ["data-p-icon", "window-maximize", 4, "ngIf"], ["data-p-icon", "window-minimize", 4, "ngIf"], ["data-p-icon", "window-maximize"], ["data-p-icon", "window-minimize"], [3, "class", 4, "ngIf"], ["data-p-icon", "times", 4, "ngIf"], ["data-p-icon", "times"]],
    template: function Dialog_Template(rf, ctx) {
      if (rf & 1) {
        ɵɵprojectionDef(_c8);
        ɵɵtemplate(0, Dialog_div_0_Template, 2, 7, "div", 6);
      }
      if (rf & 2) {
        ɵɵproperty("ngIf", ctx.maskVisible);
      }
    },
    dependencies: [CommonModule, NgClass, NgIf, NgTemplateOutlet, NgStyle, Button, FocusTrap, TimesIcon, WindowMaximizeIcon, WindowMinimizeIcon, SharedModule, Bind],
    encapsulation: 2,
    data: {
      animation: [trigger("animation", [transition("void => visible", [useAnimation(showAnimation)]), transition("visible => void", [useAnimation(hideAnimation)])])]
    },
    changeDetection: 0
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(Dialog, [{
    type: Component,
    args: [{
      selector: "p-dialog",
      standalone: true,
      imports: [CommonModule, Button, FocusTrap, TimesIcon, WindowMaximizeIcon, WindowMinimizeIcon, SharedModule, Bind],
      template: `
        <div *ngIf="maskVisible" [class]="cn(cx('mask'), maskStyleClass)" [style]="sx('mask')" [ngStyle]="maskStyle" [pBind]="ptm('mask')">
            <div
                *ngIf="visible"
                #container
                [class]="cn(cx('root'), styleClass)"
                [style]="sx('root')"
                [ngStyle]="style"
                [pBind]="ptm('root')"
                pFocusTrap
                [pFocusTrapDisabled]="focusTrap === false"
                [@animation]="{
                    value: 'visible',
                    params: { transform: transformOptions, transition: transitionOptions }
                }"
                (@animation.start)="onAnimationStart($event)"
                (@animation.done)="onAnimationEnd($event)"
                [attr.role]="role"
                [attr.aria-labelledby]="ariaLabelledBy"
                [attr.aria-modal]="true"
            >
                <ng-container *ngIf="_headlessTemplate || headlessTemplate || headlessT; else notHeadless">
                    <ng-container *ngTemplateOutlet="_headlessTemplate || headlessTemplate || headlessT"></ng-container>
                </ng-container>

                <ng-template #notHeadless>
                    <div *ngIf="resizable" [class]="cx('resizeHandle')" [pBind]="ptm('resizeHandle')" [style.z-index]="90" (mousedown)="initResize($event)"></div>
                    <div #titlebar [class]="cx('header')" [pBind]="ptm('header')" (mousedown)="initDrag($event)" *ngIf="showHeader">
                        <span [id]="ariaLabelledBy" [class]="cx('title')" [pBind]="ptm('title')" *ngIf="!_headerTemplate && !headerTemplate && !headerT">{{ header }}</span>
                        <ng-container *ngTemplateOutlet="_headerTemplate || headerTemplate || headerT"></ng-container>
                        <div [class]="cx('headerActions')" [pBind]="ptm('headerActions')">
                            <p-button
                                [pt]="ptm('pcMaximizeButton')"
                                *ngIf="maximizable"
                                [styleClass]="cx('pcMaximizeButton')"
                                [ariaLabel]="maximized ? minimizeLabel : maximizeLabel"
                                (onClick)="maximize()"
                                (keydown.enter)="maximize()"
                                [tabindex]="maximizable ? '0' : '-1'"
                                [buttonProps]="maximizeButtonProps"
                            >
                                <ng-template #icon>
                                    <span *ngIf="maximizeIcon && !_maximizeiconTemplate && !_minimizeiconTemplate" [ngClass]="maximized ? minimizeIcon : maximizeIcon"></span>
                                    <ng-container *ngIf="!maximizeIcon && !maximizeButtonProps?.icon">
                                        <svg data-p-icon="window-maximize" *ngIf="!maximized && !_maximizeiconTemplate && !maximizeIconTemplate && !maximizeIconT" />
                                        <svg data-p-icon="window-minimize" *ngIf="maximized && !_minimizeiconTemplate && !minimizeIconTemplate && !minimizeIconT" />
                                    </ng-container>
                                    <ng-container *ngIf="!maximized">
                                        <ng-template *ngTemplateOutlet="_maximizeiconTemplate || maximizeIconTemplate || maximizeIconT"></ng-template>
                                    </ng-container>
                                    <ng-container *ngIf="maximized">
                                        <ng-template *ngTemplateOutlet="_minimizeiconTemplate || minimizeIconTemplate || minimizeIconT"></ng-template>
                                    </ng-container>
                                </ng-template>
                            </p-button>
                            <p-button
                                [pt]="ptm('pcCloseButton')"
                                *ngIf="closable"
                                [styleClass]="cx('pcCloseButton')"
                                [ariaLabel]="closeAriaLabel"
                                (onClick)="close($event)"
                                (keydown.enter)="close($event)"
                                [tabindex]="closeTabindex"
                                [buttonProps]="closeButtonProps"
                            >
                                <ng-template #icon>
                                    <ng-container *ngIf="!_closeiconTemplate && !closeIconTemplate && !closeIconT && !closeButtonProps?.icon">
                                        <span *ngIf="closeIcon" [class]="closeIcon"></span>
                                        <svg data-p-icon="times" *ngIf="!closeIcon" />
                                    </ng-container>
                                    <span *ngIf="_closeiconTemplate || closeIconTemplate || closeIconT">
                                        <ng-template *ngTemplateOutlet="_closeiconTemplate || closeIconTemplate || closeIconT"></ng-template>
                                    </span>
                                </ng-template>
                            </p-button>
                        </div>
                    </div>
                    <div #content [class]="cn(cx('content'), contentStyleClass)" [ngStyle]="contentStyle" [pBind]="ptm('content')">
                        <ng-content></ng-content>
                        <ng-container *ngTemplateOutlet="_contentTemplate || contentTemplate || contentT"></ng-container>
                    </div>
                    <div #footer [class]="cx('footer')" [pBind]="ptm('footer')" *ngIf="_footerTemplate || footerTemplate || footerT">
                        <ng-content select="p-footer"></ng-content>
                        <ng-container *ngTemplateOutlet="_footerTemplate || footerTemplate || footerT"></ng-container>
                    </div>
                </ng-template>
            </div>
        </div>
    `,
      animations: [trigger("animation", [transition("void => visible", [useAnimation(showAnimation)]), transition("visible => void", [useAnimation(hideAnimation)])])],
      changeDetection: ChangeDetectionStrategy.OnPush,
      encapsulation: ViewEncapsulation.None,
      providers: [DialogStyle, {
        provide: DIALOG_INSTANCE,
        useExisting: Dialog
      }, {
        provide: PARENT_INSTANCE,
        useExisting: Dialog
      }],
      hostDirectives: [Bind]
    }]
  }], null, {
    hostName: [{
      type: Input
    }],
    header: [{
      type: Input
    }],
    draggable: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    resizable: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    contentStyle: [{
      type: Input
    }],
    contentStyleClass: [{
      type: Input
    }],
    modal: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    closeOnEscape: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    dismissableMask: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    rtl: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    closable: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    breakpoints: [{
      type: Input
    }],
    styleClass: [{
      type: Input
    }],
    maskStyleClass: [{
      type: Input
    }],
    maskStyle: [{
      type: Input
    }],
    showHeader: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    blockScroll: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    autoZIndex: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    baseZIndex: [{
      type: Input,
      args: [{
        transform: numberAttribute
      }]
    }],
    minX: [{
      type: Input,
      args: [{
        transform: numberAttribute
      }]
    }],
    minY: [{
      type: Input,
      args: [{
        transform: numberAttribute
      }]
    }],
    focusOnShow: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    maximizable: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    keepInViewport: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    focusTrap: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    transitionOptions: [{
      type: Input
    }],
    closeIcon: [{
      type: Input
    }],
    closeAriaLabel: [{
      type: Input
    }],
    closeTabindex: [{
      type: Input
    }],
    minimizeIcon: [{
      type: Input
    }],
    maximizeIcon: [{
      type: Input
    }],
    closeButtonProps: [{
      type: Input
    }],
    maximizeButtonProps: [{
      type: Input
    }],
    visible: [{
      type: Input
    }],
    style: [{
      type: Input
    }],
    position: [{
      type: Input
    }],
    role: [{
      type: Input
    }],
    appendTo: [{
      type: Input,
      args: [{
        isSignal: true,
        alias: "appendTo",
        required: false
      }]
    }],
    onShow: [{
      type: Output
    }],
    onHide: [{
      type: Output
    }],
    visibleChange: [{
      type: Output
    }],
    onResizeInit: [{
      type: Output
    }],
    onResizeEnd: [{
      type: Output
    }],
    onDragEnd: [{
      type: Output
    }],
    onMaximize: [{
      type: Output
    }],
    headerViewChild: [{
      type: ViewChild,
      args: ["titlebar"]
    }],
    contentViewChild: [{
      type: ViewChild,
      args: ["content"]
    }],
    footerViewChild: [{
      type: ViewChild,
      args: ["footer"]
    }],
    headerTemplate: [{
      type: Input,
      args: ["content"]
    }],
    contentTemplate: [{
      type: Input
    }],
    footerTemplate: [{
      type: Input
    }],
    closeIconTemplate: [{
      type: Input
    }],
    maximizeIconTemplate: [{
      type: Input
    }],
    minimizeIconTemplate: [{
      type: Input
    }],
    headlessTemplate: [{
      type: Input
    }],
    _headerTemplate: [{
      type: ContentChild,
      args: ["header", {
        descendants: false
      }]
    }],
    _contentTemplate: [{
      type: ContentChild,
      args: ["content", {
        descendants: false
      }]
    }],
    _footerTemplate: [{
      type: ContentChild,
      args: ["footer", {
        descendants: false
      }]
    }],
    _closeiconTemplate: [{
      type: ContentChild,
      args: ["closeicon", {
        descendants: false
      }]
    }],
    _maximizeiconTemplate: [{
      type: ContentChild,
      args: ["maximizeicon", {
        descendants: false
      }]
    }],
    _minimizeiconTemplate: [{
      type: ContentChild,
      args: ["minimizeicon", {
        descendants: false
      }]
    }],
    _headlessTemplate: [{
      type: ContentChild,
      args: ["headless", {
        descendants: false
      }]
    }],
    templates: [{
      type: ContentChildren,
      args: [PrimeTemplate]
    }]
  });
})();
var DialogModule = class _DialogModule {
  static ɵfac = function DialogModule_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _DialogModule)();
  };
  static ɵmod = ɵɵdefineNgModule({
    type: _DialogModule,
    imports: [Dialog, SharedModule],
    exports: [Dialog, SharedModule]
  });
  static ɵinj = ɵɵdefineInjector({
    imports: [Dialog, SharedModule, SharedModule]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DialogModule, [{
    type: NgModule,
    args: [{
      imports: [Dialog, SharedModule],
      exports: [Dialog, SharedModule]
    }]
  }], null, null);
})();

// node_modules/primeng/fesm2022/primeng-dynamicdialog.mjs
var _c02 = () => ({
  severity: "secondary",
  variant: "text",
  rounded: true
});
function DynamicDialog_1_ng_template_0_ng_container_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function DynamicDialog_1_ng_template_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_1_ng_template_0_ng_container_0_Template, 1, 0, "ng-container", 8);
  }
  if (rf & 2) {
    const ctx_r0 = ɵɵnextContext(2);
    ɵɵproperty("ngComponentOutlet", ctx_r0.headerTemplate);
  }
}
function DynamicDialog_1_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_1_ng_template_0_Template, 1, 1, "ng-template", null, 0, ɵɵtemplateRefExtractor);
  }
}
function DynamicDialog_2_ng_template_0_ng_container_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function DynamicDialog_2_ng_template_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_2_ng_template_0_ng_container_0_Template, 1, 0, "ng-container", 8);
  }
  if (rf & 2) {
    const ctx_r0 = ɵɵnextContext(2);
    ɵɵproperty("ngComponentOutlet", ctx_r0.contentTemplate);
  }
}
function DynamicDialog_2_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_2_ng_template_0_Template, 1, 1, "ng-template", null, 1, ɵɵtemplateRefExtractor);
  }
}
function DynamicDialog_3_ng_template_0_ng_container_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function DynamicDialog_3_ng_template_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_3_ng_template_0_ng_container_0_Template, 1, 0, "ng-container", 8);
  }
  if (rf & 2) {
    const ctx_r0 = ɵɵnextContext(2);
    ɵɵproperty("ngComponentOutlet", ctx_r0.footerTemplate);
  }
}
function DynamicDialog_3_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_3_ng_template_0_Template, 1, 1, "ng-template", null, 2, ɵɵtemplateRefExtractor);
  }
}
function DynamicDialog_4_ng_template_0_ng_container_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function DynamicDialog_4_ng_template_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_4_ng_template_0_ng_container_0_Template, 1, 0, "ng-container", 8);
  }
  if (rf & 2) {
    const ctx_r0 = ɵɵnextContext(2);
    ɵɵproperty("ngComponentOutlet", ctx_r0.closeIconTemplate);
  }
}
function DynamicDialog_4_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_4_ng_template_0_Template, 1, 1, "ng-template", null, 3, ɵɵtemplateRefExtractor);
  }
}
function DynamicDialog_5_ng_template_0_ng_container_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function DynamicDialog_5_ng_template_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_5_ng_template_0_ng_container_0_Template, 1, 0, "ng-container", 8);
  }
  if (rf & 2) {
    const ctx_r0 = ɵɵnextContext(2);
    ɵɵproperty("ngComponentOutlet", ctx_r0.maximizeIconTemplate);
  }
}
function DynamicDialog_5_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_5_ng_template_0_Template, 1, 1, "ng-template", null, 4, ɵɵtemplateRefExtractor);
  }
}
function DynamicDialog_6_ng_template_0_ng_container_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementContainer(0);
  }
}
function DynamicDialog_6_ng_template_0_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_6_ng_template_0_ng_container_0_Template, 1, 0, "ng-container", 8);
  }
  if (rf & 2) {
    const ctx_r0 = ɵɵnextContext(2);
    ɵɵproperty("ngComponentOutlet", ctx_r0.minimizeIconTemplate);
  }
}
function DynamicDialog_6_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_6_ng_template_0_Template, 1, 1, "ng-template", null, 5, ɵɵtemplateRefExtractor);
  }
}
function DynamicDialog_7_ng_template_0_Template(rf, ctx) {
}
function DynamicDialog_7_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵtemplate(0, DynamicDialog_7_ng_template_0_Template, 0, 0, "ng-template", 9);
  }
}
function DynamicDialog_div_8_Template(rf, ctx) {
  if (rf & 1) {
    ɵɵelementStart(0, "div");
    ɵɵtext(1);
    ɵɵelementEnd();
  }
  if (rf & 2) {
    const ctx_r0 = ɵɵnextContext();
    ɵɵadvance();
    ɵɵtextInterpolate(ctx_r0.ddconfig.footer);
  }
}
var DynamicDialogContent = class _DynamicDialogContent {
  viewContainerRef;
  constructor(viewContainerRef) {
    this.viewContainerRef = viewContainerRef;
  }
  static ɵfac = function DynamicDialogContent_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _DynamicDialogContent)(ɵɵdirectiveInject(ViewContainerRef));
  };
  static ɵdir = ɵɵdefineDirective({
    type: _DynamicDialogContent,
    selectors: [["", "pDynamicDialogContent", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DynamicDialogContent, [{
    type: Directive,
    args: [{
      selector: "[pDynamicDialogContent]",
      standalone: true
    }]
  }], () => [{
    type: ViewContainerRef
  }], null);
})();
var DynamicDialogStyle = class _DynamicDialogStyle extends DialogStyle {
  name = "dialog";
  static ɵfac = /* @__PURE__ */ (() => {
    let ɵDynamicDialogStyle_BaseFactory;
    return function DynamicDialogStyle_Factory(__ngFactoryType__) {
      return (ɵDynamicDialogStyle_BaseFactory || (ɵDynamicDialogStyle_BaseFactory = ɵɵgetInheritedFactory(_DynamicDialogStyle)))(__ngFactoryType__ || _DynamicDialogStyle);
    };
  })();
  static ɵprov = ɵɵdefineInjectable({
    token: _DynamicDialogStyle,
    factory: _DynamicDialogStyle.ɵfac
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DynamicDialogStyle, [{
    type: Injectable
  }], null, null);
})();
var DynamicDialogClasses;
(function(DynamicDialogClasses2) {
  DynamicDialogClasses2["mask"] = "p-dialog-mask";
  DynamicDialogClasses2["root"] = "p-dialog";
  DynamicDialogClasses2["header"] = "p-dialog-header";
  DynamicDialogClasses2["title"] = "p-dialog-title";
  DynamicDialogClasses2["headerActions"] = "p-dialog-header-actions";
  DynamicDialogClasses2["pcMaximizeButton"] = "p-dialog-maximize-button";
  DynamicDialogClasses2["pcCloseButton"] = "p-dialog-close-button";
  DynamicDialogClasses2["content"] = "p-dialog-content";
  DynamicDialogClasses2["footer"] = "p-dialog-footer";
})(DynamicDialogClasses || (DynamicDialogClasses = {}));
var DynamicDialogConfig = class {
  /**
   * An object to pass to the component loaded inside the Dialog.
   * @group Props
   */
  data;
  /**
   * An object to pass to the component loaded inside the Dialog.
   * @group Props
   */
  inputValues;
  /**
   * Header text of the dialog.
   * @group Props
   */
  header;
  /**
   * Identifies the element (or elements) that labels the element it is applied to.
   * @group Props
   */
  ariaLabelledBy;
  /**
   * Footer text of the dialog.
   * @group Props
   */
  footer;
  /**
   * Width of the dialog.
   * @group Props
   */
  width;
  /**
   * Height of the dialog.
   * @group Props
   */
  height;
  /**
   * Specifies if pressing escape key should hide the dialog.
   * @group Props
   */
  closeOnEscape = false;
  /**
   * Specifies if autofocus should happen on show.
   * @group Props
   */
  focusOnShow = true;
  /**
   * When enabled, can only focus on elements inside the dialog.
   * @group Props
   */
  focusTrap = true;
  /**
   * Base zIndex value to use in layering.
   * @group Props
   */
  baseZIndex;
  /**
   * Whether to re-enforce layering through applying zIndex.
   * @group Props
   */
  autoZIndex = false;
  /**
   * Specifies if clicking the modal background should hide the dialog.
   * @group Props
   */
  dismissableMask = false;
  /**
   * Inline style of the component.
   * @group Props
   */
  rtl = false;
  /**
   * Inline style of the component.
   * @group Props
   */
  style;
  /**
   * Inline style of the content.
   * @group Props
   */
  contentStyle;
  /**
   * Style class of the component.
   * @group Props
   */
  styleClass;
  /**
   * Transition options of the animation.
   * @group Props
   */
  transitionOptions;
  /**
   * Adds a close icon to the header to hide the dialog.
   * @group Props
   */
  closable = false;
  /**
   * Whether to show the header or not.
   * @group Props
   */
  showHeader = false;
  /**
   * Defines if background should be blocked when dialog is displayed.
   * @group Props
   */
  modal = false;
  /**
   * Style class of the mask.
   * @group Props
   */
  maskStyleClass;
  /**
   * Enables resizing of the content.
   * @group Props
   */
  resizable = false;
  /**
   * Enables dragging to change the position using header.
   * @group Props
   */
  draggable = false;
  /**
   * Keeps dialog in the viewport.
   * @group Props
   */
  keepInViewport = false;
  /**
   * Minimum value for the left coordinate of dialog in dragging.
   * @group Props
   */
  minX;
  /**
   * Minimum value for the top coordinate of dialog in dragging.
   * @group Props
   */
  minY;
  /**
   * Whether the dialog can be displayed full screen.
   * @group Props
   */
  maximizable = false;
  /**
   * Name of the maximize icon.
   * @group Props
   */
  maximizeIcon;
  /**
   * Name of the minimize icon.
   * @group Props
   */
  minimizeIcon;
  /**
   * Position of the dialog, options are "center", "top", "bottom", "left", "right", "topleft", "topright", "bottomleft" or "bottomright".
   * @group Props
   */
  position;
  /**
   * Defines a string that labels the close button for accessibility.
   * @group Props
   */
  closeAriaLabel;
  /**
   * Target element to attach the overlay, valid values are "body" or a local ng-template variable of another element (note: use binding with brackets for template variables, e.g. [appendTo]="mydiv" for a div element having #mydiv as variable name).
   * @group Props
   */
  appendTo;
  /**
   * A boolean to determine if it can be duplicate.
   * @group Props
   */
  duplicate = false;
  /**
   * Object literal to define widths per screen size.
   * @group Props
   */
  breakpoints;
  /**
   * Dialog templates.
   * @group Props
   */
  templates;
};
var DynamicDialogRef = class {
  constructor() {
  }
  /**
   * Closes dialog.
   * @group Method
   */
  close(result) {
    this._onClose.next(result);
    setTimeout(() => {
      this._onClose.complete();
    }, 1e3);
  }
  /**
   * Destroys the dialog instance.
   * @group Method
   */
  destroy() {
    this._onDestroy.next(null);
  }
  /**
   * Callback to invoke on drag start.
   * @param {MouseEvent} event - Mouse event.
   * @group Method
   */
  dragStart(event) {
    this._onDragStart.next(event);
  }
  /**
   * Callback to invoke on drag end.
   * @param {MouseEvent} event - Mouse event.
   * @group Method
   */
  dragEnd(event) {
    this._onDragEnd.next(event);
  }
  /**
   * Callback to invoke on resize start.
   * @param {MouseEvent} event - Mouse event.
   * @group Method
   */
  resizeInit(event) {
    this._onResizeInit.next(event);
  }
  /**
   * Callback to invoke on resize start.
   * @param {MouseEvent} event - Mouse event.
   * @group Method
   */
  resizeEnd(event) {
    this._onResizeEnd.next(event);
  }
  /**
   * Callback to invoke on dialog is maximized.
   * @param {*} value - Size value.
   * @group Method
   */
  maximize(value) {
    this._onMaximize.next(value);
  }
  _onClose = new Subject();
  /**
   * Event triggered on dialog is closed.
   * @group Events
   */
  onClose = this._onClose.asObservable();
  _onDestroy = new Subject();
  /**
   * Event triggered on dialog instance is destroyed.
   * @group Events
   */
  onDestroy = this._onDestroy.asObservable();
  _onDragStart = new Subject();
  /**
   * Event triggered on drag start.
   * @param {MouseEvent} event - Mouse event.
   * @group Events
   */
  onDragStart = this._onDragStart.asObservable();
  _onDragEnd = new Subject();
  /**
   * Event triggered on drag end.
   * @param {MouseEvent} event - Mouse event.
   * @group Events
   */
  onDragEnd = this._onDragEnd.asObservable();
  _onResizeInit = new Subject();
  /**
   * Event triggered on resize start.
   * @param {MouseEvent} event - Mouse event.
   * @group Events
   */
  onResizeInit = this._onResizeInit.asObservable();
  _onResizeEnd = new Subject();
  /**
   * Event triggered on resize end.
   * @param {MouseEvent} event - Mouse event.
   * @group Events
   */
  onResizeEnd = this._onResizeEnd.asObservable();
  _onMaximize = new Subject();
  /**
   * Event triggered on dialog is maximized.
   * @param {*} value - Size value.
   * @group Events
   */
  onMaximize = this._onMaximize.asObservable();
  /**
   * Event triggered on child component load.
   * @param {*} value - Chi.
   * @group Events
   */
  onChildComponentLoaded = new Subject();
};
var DYNAMIC_DIALOG_INSTANCE = new InjectionToken("DYNAMIC_DIALOG_INSTANCE");
var DynamicDialog = class _DynamicDialog extends BaseComponent {
  ddconfig;
  dialogRef;
  _componentStyle = inject(DynamicDialogStyle);
  $pcDynamicDialog = inject(DYNAMIC_DIALOG_INSTANCE, {
    optional: true,
    skipSelf: true
  }) ?? void 0;
  bindDirectiveInstance = inject(Bind, {
    self: true
  });
  onAfterViewChecked() {
    this.bindDirectiveInstance.setAttrs(this.ptms(["host", "root"]));
  }
  visible = true;
  componentRef;
  id = s("pn_id_");
  insertionPoint;
  dialog;
  childComponentType;
  inputValues;
  get minX() {
    return this.ddconfig.minX ? this.ddconfig.minX : 0;
  }
  get minY() {
    return this.ddconfig.minY ? this.ddconfig.minY : 0;
  }
  get keepInViewport() {
    return this.ddconfig.keepInViewport;
  }
  get maximizable() {
    return this.ddconfig.maximizable;
  }
  get maximizeIcon() {
    return this.ddconfig.maximizeIcon;
  }
  get minimizeIcon() {
    return this.ddconfig.minimizeIcon;
  }
  get closable() {
    return this.ddconfig.closable;
  }
  get position() {
    return this.ddconfig.position;
  }
  get defaultCloseAriaLabel() {
    return this.config.getTranslation(TranslationKeys.ARIA)["close"];
  }
  get breakpoints() {
    return this.ddconfig.breakpoints;
  }
  get footerTemplate() {
    return this.ddconfig?.templates?.footer;
  }
  get headerTemplate() {
    return this.ddconfig?.templates?.header;
  }
  get contentTemplate() {
    return this.ddconfig?.templates?.content;
  }
  get minimizeIconTemplate() {
    return this.ddconfig?.templates?.minimizeicon;
  }
  get maximizeIconTemplate() {
    return this.ddconfig?.templates?.maximizeicon;
  }
  get closeIconTemplate() {
    return this.ddconfig?.templates?.closeicon;
  }
  get dialogStyle() {
    return __spreadValues(__spreadValues(__spreadValues({}, this.ddconfig?.style || {}), this.ddconfig?.width && {
      width: this.ddconfig.width
    }), this.ddconfig?.height && {
      height: this.ddconfig.height
    });
  }
  get header() {
    return this.ddconfig.header;
  }
  get data() {
    return this.ddconfig.data;
  }
  get dialogId() {
    return this.$attrSelector;
  }
  maximized;
  dragging;
  resizing;
  ariaLabelledBy;
  _style = {};
  styleElement;
  lastPageX;
  lastPageY;
  contentViewChild;
  footerViewChild;
  headerViewChild;
  maskViewChild;
  maskClickListener;
  documentDragListener;
  documentDragEndListener;
  documentResizeListener;
  documentResizeEndListener;
  documentEscapeListener;
  constructor(ddconfig, dialogRef) {
    super();
    this.ddconfig = ddconfig;
    this.dialogRef = dialogRef;
  }
  onAfterViewInit() {
    this.loadChildComponent(this.childComponentType);
    this.ariaLabelledBy = this.getAriaLabelledBy();
    this.cd.detectChanges();
  }
  getAriaLabelledBy() {
    const {
      header,
      showHeader
    } = this.ddconfig;
    if (header === null || showHeader === false) {
      return null;
    }
    return s("pn_id_") + "_header";
  }
  loadChildComponent(componentType) {
    let viewContainerRef = this.insertionPoint?.viewContainerRef;
    viewContainerRef?.clear();
    this.componentRef = viewContainerRef?.createComponent(componentType);
    if (this.inputValues && this.componentRef) {
      Object.entries(this.inputValues).forEach(([key, value]) => {
        this.componentRef.setInput(key, value);
      });
    }
    this.dialogRef.onChildComponentLoaded.next(this.componentRef.instance);
  }
  onDialogHide(event) {
    this.dialogRef.destroy();
  }
  onDialogMaximize(event) {
    this.maximized = event.maximized;
    this.dialogRef.maximize(event);
  }
  onDialogResizeInit(event) {
    this.resizing = true;
    this.dialogRef.resizeInit(event);
  }
  onDialogResizeEnd(event) {
    this.resizing = false;
    this.dialogRef.resizeEnd(event);
  }
  onDialogDragEnd(event) {
    this.dragging = false;
    this.dialogRef.dragEnd(event);
  }
  close() {
    this.visible = false;
    this.cd.markForCheck();
  }
  hide() {
    if (this.dialogRef) {
      this.dialogRef.close();
    }
  }
  get _parent() {
    const domElements = Array.from(this.document.getElementsByClassName("p-dialog"));
    if (domElements.length > 1) {
      return domElements.pop();
    }
  }
  get parentContent() {
    const domElements = Array.from(this.document.getElementsByClassName("p-dialog"));
    if (domElements.length > 0) {
      const contentElements = domElements[domElements.length - 1].querySelector(".p-dialog-content");
      if (contentElements) return Array.isArray(contentElements) ? contentElements[0] : contentElements;
    }
  }
  container;
  wrapper;
  unbindGlobalListeners() {
    this.unbindDocumentEscapeListener();
    this.unbindDocumentResizeListeners();
    this.unbindDocumentDragListener();
    this.unbindDocumentDragEndListener();
  }
  onAnimationStart(event) {
    if (event.toState === "visible") {
      if (this._parent) {
        this.unbindGlobalListeners();
      }
      if (this.ddconfig.modal) {
        this.enableModality();
      }
    }
  }
  onAnimationEnd(event) {
    if (event.toState === "void") {
      this.onContainerDestroy();
      this.dialogRef.destroy();
    }
  }
  onContainerDestroy() {
    this.unbindGlobalListeners();
    if (this.ddconfig.modal) {
      this.disableModality();
    }
    this.container = null;
  }
  bindDocumentDragListener() {
    if (!this.documentDragListener) {
      this.documentDragListener = this.renderer.listen(this.document.defaultView, "mousemove", (event) => {
        this.onDrag(event);
      });
    }
  }
  bindDocumentDragEndListener() {
    if (!this.documentDragEndListener) {
      this.documentDragEndListener = this.renderer.listen(this.document.defaultView, "mouseup", (event) => {
        this.endDrag(event);
      });
    }
  }
  unbindDocumentDragEndListener() {
    if (this.documentDragEndListener) {
      this.documentDragEndListener();
      this.documentDragEndListener = null;
    }
  }
  unbindDocumentDragListener() {
    if (this.documentDragListener) {
      this.documentDragListener();
      this.documentDragListener = null;
    }
  }
  initDrag(event) {
    if (event.target instanceof HTMLElement) {
      const target = event.target;
      if (target.closest(".p-dialog-header-icon") || target.closest(".p-dialog-header-icons")) {
        return;
      }
    }
    this.dragging = true;
    this.lastPageX = event.pageX;
    this.lastPageY = event.pageY;
    this.dialogRef.dragStart(event);
    this.bindDocumentDragListener();
    this.bindDocumentDragEndListener();
  }
  onDrag(event) {
    if (this.dragging) {
      this.lastPageX = event.pageX;
      this.lastPageY = event.pageY;
      if (this.ddconfig.keepInViewport && this.container) {
        this.container.style.position = "fixed";
      }
    }
  }
  endDrag(event) {
    if (this.dragging) {
      this.dragging = false;
      this.dialogRef.dragEnd(event);
      this.cd.detectChanges();
    }
  }
  resetPosition() {
    if (this.container) {
      this.container.style.position = "";
      this.container.style.left = "";
      this.container.style.top = "";
      this.container.style.margin = "";
    }
  }
  bindDocumentResizeListeners() {
    if (!this.documentResizeListener) {
      this.documentResizeListener = this.renderer.listen(this.document.defaultView, "mousemove", (event) => {
        this.onResize(event);
      });
    }
    if (!this.documentResizeEndListener) {
      this.documentResizeEndListener = this.renderer.listen(this.document.defaultView, "mouseup", (event) => {
        this.resizeEnd(event);
      });
    }
  }
  unbindDocumentResizeListeners() {
    if (this.documentResizeListener) {
      this.documentResizeListener();
      this.documentResizeListener = null;
    }
    if (this.documentResizeEndListener) {
      this.documentResizeEndListener();
      this.documentResizeEndListener = null;
    }
  }
  initResize(event) {
    this.resizing = true;
    this.lastPageX = event.pageX;
    this.lastPageY = event.pageY;
    this.dialogRef.resizeInit(event);
  }
  onResize(event) {
    if (this.resizing) {
      this.lastPageX = event.pageX;
      this.lastPageY = event.pageY;
    }
  }
  resizeEnd(event) {
    if (this.resizing) {
      this.resizing = false;
      this.dialogRef.resizeEnd(event);
    }
  }
  maximize() {
    this.maximized = !this.maximized;
    this.dialogRef.maximize({
      maximized: this.maximized
    });
  }
  enableModality() {
    if (this.ddconfig.dismissableMask && this.wrapper) {
      this.maskClickListener = this.renderer.listen(this.wrapper, "mousedown", (event) => {
        if (this.wrapper && this.wrapper.isSameNode(event.target)) {
          this.hide();
        }
      });
    }
  }
  disableModality() {
    this.unbindMaskClickListener();
    this.cd.detectChanges();
  }
  unbindMaskClickListener() {
    if (this.maskClickListener) {
      this.maskClickListener();
      this.maskClickListener = null;
    }
  }
  bindDocumentEscapeListener() {
    if (this.ddconfig.closeOnEscape) {
      this.documentEscapeListener = this.renderer.listen(this.document, "keydown", (event) => {
        if (event.key === "Escape" && this.container) {
          this.hide();
        }
      });
    }
  }
  unbindDocumentEscapeListener() {
    if (this.documentEscapeListener) {
      this.documentEscapeListener();
      this.documentEscapeListener = null;
    }
  }
  createStyle() {
    if (!this.styleElement && this.breakpoints) {
      this.styleElement = this.renderer.createElement("style");
      this.styleElement.type = "text/css";
      this.renderer.appendChild(this.document.head, this.styleElement);
      let innerHTML = "";
      for (let breakpoint in this.breakpoints) {
        innerHTML += `
                    @media screen and (max-width: ${breakpoint}) {
                        .p-dialog[${this.dialogId}] {
                            width: ${this.breakpoints[breakpoint]} !important;
                        }
                    }
                `;
      }
      this.renderer.setProperty(this.styleElement, "innerHTML", innerHTML);
    }
  }
  destroyStyle() {
    if (this.styleElement) {
      this.renderer.removeChild(this.document.head, this.styleElement);
      this.styleElement = null;
    }
  }
  onDestroy() {
    if (this.componentRef && typeof this.componentRef.destroy === "function") {
      this.componentRef.destroy();
    }
  }
  ngOnDestroy() {
    this.onContainerDestroy();
    if (this.componentRef && typeof this.componentRef.destroy === "function") {
      this.componentRef.destroy();
    }
    this.destroyStyle();
  }
  static ɵfac = function DynamicDialog_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _DynamicDialog)(ɵɵdirectiveInject(DynamicDialogConfig), ɵɵdirectiveInject(DynamicDialogRef));
  };
  static ɵcmp = ɵɵdefineComponent({
    type: _DynamicDialog,
    selectors: [["p-dynamicDialog"], ["p-dynamicdialog"], ["p-dynamic-dialog"]],
    viewQuery: function DynamicDialog_Query(rf, ctx) {
      if (rf & 1) {
        ɵɵviewQuery(DynamicDialogContent, 5);
        ɵɵviewQuery(Dialog, 5);
      }
      if (rf & 2) {
        let _t2;
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx.insertionPoint = _t2.first);
        ɵɵqueryRefresh(_t2 = ɵɵloadQuery()) && (ctx.dialog = _t2.first);
      }
    },
    features: [ɵɵProvidersFeature([DynamicDialogStyle, {
      provide: DYNAMIC_DIALOG_INSTANCE,
      useExisting: _DynamicDialog
    }, {
      provide: PARENT_INSTANCE,
      useExisting: _DynamicDialog
    }]), ɵɵHostDirectivesFeature([Bind]), ɵɵInheritDefinitionFeature],
    decls: 9,
    vars: 42,
    consts: [["header", ""], ["content", ""], ["footer", ""], ["closeicon", ""], ["maximizeicon", ""], ["minimizeicon", ""], ["hostName", "DynamicDialog", 3, "visibleChange", "onHide", "onMaximize", "onResizeInit", "onResizeEnd", "onDragEnd", "visible", "header", "draggable", "resizable", "contentStyle", "modal", "closeOnEscape", "dismissableMask", "rtl", "closable", "breakpoints", "styleClass", "maskStyleClass", "showHeader", "autoZIndex", "baseZIndex", "minX", "minY", "focusOnShow", "maximizable", "keepInViewport", "focusTrap", "transitionOptions", "closeAriaLabel", "minimizeIcon", "maximizeIcon", "closeButtonProps", "maximizeButtonProps", "position", "pt"], [4, "ngIf"], [4, "ngComponentOutlet"], ["pDynamicDialogContent", ""]],
    template: function DynamicDialog_Template(rf, ctx) {
      if (rf & 1) {
        ɵɵelementStart(0, "p-dialog", 6);
        ɵɵtwoWayListener("visibleChange", function DynamicDialog_Template_p_dialog_visibleChange_0_listener($event) {
          ɵɵtwoWayBindingSet(ctx.visible, $event) || (ctx.visible = $event);
          return $event;
        });
        ɵɵlistener("onHide", function DynamicDialog_Template_p_dialog_onHide_0_listener($event) {
          return ctx.onDialogHide($event);
        })("onMaximize", function DynamicDialog_Template_p_dialog_onMaximize_0_listener($event) {
          return ctx.onDialogMaximize($event);
        })("onResizeInit", function DynamicDialog_Template_p_dialog_onResizeInit_0_listener($event) {
          return ctx.onDialogResizeInit($event);
        })("onResizeEnd", function DynamicDialog_Template_p_dialog_onResizeEnd_0_listener($event) {
          return ctx.onDialogResizeEnd($event);
        })("onDragEnd", function DynamicDialog_Template_p_dialog_onDragEnd_0_listener($event) {
          return ctx.onDialogDragEnd($event);
        });
        ɵɵtemplate(1, DynamicDialog_1_Template, 2, 0, null, 7)(2, DynamicDialog_2_Template, 2, 0, null, 7)(3, DynamicDialog_3_Template, 2, 0, null, 7)(4, DynamicDialog_4_Template, 2, 0, null, 7)(5, DynamicDialog_5_Template, 2, 0, null, 7)(6, DynamicDialog_6_Template, 2, 0, null, 7)(7, DynamicDialog_7_Template, 1, 0, null, 7)(8, DynamicDialog_div_8_Template, 2, 1, "div", 7);
        ɵɵelementEnd();
      }
      if (rf & 2) {
        ɵɵstyleMap(ctx.dialogStyle);
        ɵɵtwoWayProperty("visible", ctx.visible);
        ɵɵproperty("header", ctx.ddconfig == null ? null : ctx.ddconfig.header)("draggable", (ctx.ddconfig == null ? null : ctx.ddconfig.draggable) !== false)("resizable", (ctx.ddconfig == null ? null : ctx.ddconfig.resizable) !== false)("contentStyle", ctx.ddconfig == null ? null : ctx.ddconfig.contentStyle)("modal", (ctx.ddconfig == null ? null : ctx.ddconfig.modal) !== false)("closeOnEscape", (ctx.ddconfig == null ? null : ctx.ddconfig.closeOnEscape) !== false)("dismissableMask", ctx.ddconfig == null ? null : ctx.ddconfig.dismissableMask)("rtl", ctx.ddconfig == null ? null : ctx.ddconfig.rtl)("closable", ctx.closable)("breakpoints", ctx.breakpoints)("styleClass", ctx.ddconfig == null ? null : ctx.ddconfig.styleClass)("maskStyleClass", ctx.ddconfig == null ? null : ctx.ddconfig.maskStyleClass)("showHeader", (ctx.ddconfig == null ? null : ctx.ddconfig.showHeader) !== false)("autoZIndex", (ctx.ddconfig == null ? null : ctx.ddconfig.autoZIndex) !== false)("baseZIndex", (ctx.ddconfig == null ? null : ctx.ddconfig.baseZIndex) || 0)("minX", ctx.minX)("minY", ctx.minY)("focusOnShow", (ctx.ddconfig == null ? null : ctx.ddconfig.focusOnShow) !== false)("maximizable", ctx.maximizable)("keepInViewport", ctx.keepInViewport)("focusTrap", (ctx.ddconfig == null ? null : ctx.ddconfig.focusTrap) !== false)("transitionOptions", (ctx.ddconfig == null ? null : ctx.ddconfig.transitionOptions) || "150ms cubic-bezier(0, 0, 0.2, 1)")("closeAriaLabel", (ctx.ddconfig == null ? null : ctx.ddconfig.closeAriaLabel) || ctx.defaultCloseAriaLabel)("minimizeIcon", ctx.minimizeIcon)("maximizeIcon", ctx.maximizeIcon)("closeButtonProps", ɵɵpureFunction0(40, _c02))("maximizeButtonProps", ɵɵpureFunction0(41, _c02))("position", ctx.position)("pt", ctx.ptm("pcDialog"));
        ɵɵadvance();
        ɵɵproperty("ngIf", ctx.headerTemplate);
        ɵɵadvance();
        ɵɵproperty("ngIf", ctx.contentTemplate);
        ɵɵadvance();
        ɵɵproperty("ngIf", ctx.footerTemplate);
        ɵɵadvance();
        ɵɵproperty("ngIf", ctx.closeIconTemplate);
        ɵɵadvance();
        ɵɵproperty("ngIf", ctx.maximizeIconTemplate);
        ɵɵadvance();
        ɵɵproperty("ngIf", ctx.minimizeIconTemplate);
        ɵɵadvance();
        ɵɵproperty("ngIf", !ctx.contentTemplate);
        ɵɵadvance();
        ɵɵproperty("ngIf", ctx.ddconfig.footer && !ctx.footerTemplate);
      }
    },
    dependencies: [CommonModule, NgComponentOutlet, NgIf, SharedModule, DynamicDialogContent, Dialog, BindModule],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DynamicDialog, [{
    type: Component,
    args: [{
      selector: "p-dynamicDialog, p-dynamicdialog, p-dynamic-dialog",
      standalone: true,
      imports: [CommonModule, SharedModule, DynamicDialogContent, Dialog, BindModule],
      template: `
        <p-dialog
            [(visible)]="visible"
            [header]="ddconfig?.header"
            [draggable]="ddconfig?.draggable !== false"
            [resizable]="ddconfig?.resizable !== false"
            [contentStyle]="ddconfig?.contentStyle"
            [modal]="ddconfig?.modal !== false"
            [closeOnEscape]="ddconfig?.closeOnEscape !== false"
            [dismissableMask]="ddconfig?.dismissableMask"
            [rtl]="ddconfig?.rtl"
            [closable]="closable"
            [breakpoints]="breakpoints"
            [styleClass]="ddconfig?.styleClass"
            [maskStyleClass]="ddconfig?.maskStyleClass"
            [showHeader]="ddconfig?.showHeader !== false"
            [autoZIndex]="ddconfig?.autoZIndex !== false"
            [baseZIndex]="ddconfig?.baseZIndex || 0"
            [minX]="minX"
            [minY]="minY"
            [focusOnShow]="ddconfig?.focusOnShow !== false"
            [maximizable]="maximizable"
            [keepInViewport]="keepInViewport"
            [focusTrap]="ddconfig?.focusTrap !== false"
            [transitionOptions]="ddconfig?.transitionOptions || '150ms cubic-bezier(0, 0, 0.2, 1)'"
            [closeAriaLabel]="ddconfig?.closeAriaLabel || defaultCloseAriaLabel"
            [minimizeIcon]="minimizeIcon"
            [maximizeIcon]="maximizeIcon"
            [closeButtonProps]="{ severity: 'secondary', variant: 'text', rounded: true }"
            [maximizeButtonProps]="{ severity: 'secondary', variant: 'text', rounded: true }"
            [style]="dialogStyle"
            [position]="position"
            (onHide)="onDialogHide($event)"
            (onMaximize)="onDialogMaximize($event)"
            (onResizeInit)="onDialogResizeInit($event)"
            (onResizeEnd)="onDialogResizeEnd($event)"
            (onDragEnd)="onDialogDragEnd($event)"
            [pt]="ptm('pcDialog')"
            hostName="DynamicDialog"
        >
            <ng-template #header *ngIf="headerTemplate">
                <ng-container *ngComponentOutlet="headerTemplate"></ng-container>
            </ng-template>
            <ng-template #content *ngIf="contentTemplate">
                <ng-container *ngComponentOutlet="contentTemplate"></ng-container>
            </ng-template>
            <ng-template #footer *ngIf="footerTemplate">
                <ng-container *ngComponentOutlet="footerTemplate"></ng-container>
            </ng-template>
            <ng-template #closeicon *ngIf="closeIconTemplate">
                <ng-container *ngComponentOutlet="closeIconTemplate"></ng-container>
            </ng-template>
            <ng-template #maximizeicon *ngIf="maximizeIconTemplate">
                <ng-container *ngComponentOutlet="maximizeIconTemplate"></ng-container>
            </ng-template>
            <ng-template #minimizeicon *ngIf="minimizeIconTemplate">
                <ng-container *ngComponentOutlet="minimizeIconTemplate"></ng-container>
            </ng-template>

            <ng-template pDynamicDialogContent *ngIf="!contentTemplate"></ng-template>
            <div *ngIf="ddconfig.footer && !footerTemplate">{{ ddconfig.footer }}</div>
        </p-dialog>
    `,
      changeDetection: ChangeDetectionStrategy.Default,
      encapsulation: ViewEncapsulation.None,
      providers: [DynamicDialogStyle, {
        provide: DYNAMIC_DIALOG_INSTANCE,
        useExisting: DynamicDialog
      }, {
        provide: PARENT_INSTANCE,
        useExisting: DynamicDialog
      }],
      hostDirectives: [Bind]
    }]
  }], () => [{
    type: DynamicDialogConfig
  }, {
    type: DynamicDialogRef
  }], {
    insertionPoint: [{
      type: ViewChild,
      args: [DynamicDialogContent]
    }],
    dialog: [{
      type: ViewChild,
      args: [Dialog]
    }]
  });
})();
var DynamicDialogModule = class _DynamicDialogModule {
  static ɵfac = function DynamicDialogModule_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _DynamicDialogModule)();
  };
  static ɵmod = ɵɵdefineNgModule({
    type: _DynamicDialogModule,
    imports: [DynamicDialog, SharedModule],
    exports: [DynamicDialog, SharedModule]
  });
  static ɵinj = ɵɵdefineInjector({
    imports: [DynamicDialog, SharedModule, SharedModule]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DynamicDialogModule, [{
    type: NgModule,
    args: [{
      imports: [DynamicDialog, SharedModule],
      exports: [DynamicDialog, SharedModule]
    }]
  }], null, null);
})();
var DynamicDialogInjector = class {
  _parentInjector;
  _additionalTokens;
  constructor(_parentInjector, _additionalTokens) {
    this._parentInjector = _parentInjector;
    this._additionalTokens = _additionalTokens;
  }
  get(token, notFoundValue, options) {
    const value = this._additionalTokens.get(token);
    if (value) return value;
    return this._parentInjector.get(token, notFoundValue);
  }
};
var DialogService = class _DialogService {
  appRef;
  injector;
  document;
  dialogComponentRefMap = /* @__PURE__ */ new Map();
  constructor(appRef, injector, document2) {
    this.appRef = appRef;
    this.injector = injector;
    this.document = document2;
  }
  /**
   * Displays the dialog using the dynamic dialog object options.
   * @param {*} componentType - Dynamic component for content template.
   * @param {DynamicDialogConfig} config - DynamicDialog object.
   * @returns {DynamicDialogRef} DynamicDialog instance.
   * @group Method
   */
  open(componentType, config) {
    if (!this.duplicationPermission(componentType, config)) {
      return null;
    }
    const dialogRef = this.appendDialogComponentToBody(config, componentType);
    const componentRefInstance = this.dialogComponentRefMap.get(dialogRef);
    if (componentRefInstance) {
      componentRefInstance.instance.childComponentType = componentType;
      componentRefInstance.instance.inputValues = config.inputValues || {};
    }
    return dialogRef;
  }
  /**
   * Returns the dynamic dialog component instance.
   * @param {DynamicDialogRef} ref - DynamicDialog instance.
   * @group Method
   */
  getInstance(ref) {
    return this.dialogComponentRefMap.get(ref)?.instance;
  }
  appendDialogComponentToBody(config, componentType) {
    const map = /* @__PURE__ */ new WeakMap();
    map.set(DynamicDialogConfig, config);
    const dialogRef = new DynamicDialogRef();
    map.set(DynamicDialogRef, dialogRef);
    const sub = dialogRef.onClose.subscribe(() => {
      this.dialogComponentRefMap.get(dialogRef)?.instance.close();
    });
    const destroySub = dialogRef.onDestroy.subscribe(() => {
      this.removeDialogComponentFromBody(dialogRef);
      destroySub.unsubscribe();
      sub.unsubscribe();
    });
    const componentRef = createComponent(DynamicDialog, {
      environmentInjector: this.appRef.injector,
      elementInjector: new DynamicDialogInjector(this.injector, map)
    });
    this.appRef.attachView(componentRef.hostView);
    const domElem = componentRef.hostView.rootNodes[0];
    if (!config.appendTo || config.appendTo === "body") {
      this.document.body.appendChild(domElem);
    } else {
      ut(config.appendTo, domElem);
    }
    this.dialogComponentRefMap.set(dialogRef, componentRef);
    return dialogRef;
  }
  removeDialogComponentFromBody(dialogRef) {
    if (!dialogRef || !this.dialogComponentRefMap.has(dialogRef)) {
      return;
    }
    const dialogComponentRef = this.dialogComponentRefMap.get(dialogRef);
    if (dialogComponentRef) {
      this.appRef.detachView(dialogComponentRef.hostView);
      dialogComponentRef.destroy();
      dialogComponentRef.changeDetectorRef.detectChanges();
    }
    this.dialogComponentRefMap.delete(dialogRef);
  }
  duplicationPermission(componentType, config) {
    if (config.duplicate) {
      return true;
    }
    let permission = true;
    for (const [key, value] of this.dialogComponentRefMap) {
      if (value.instance.childComponentType === componentType) {
        permission = false;
        break;
      }
    }
    return permission;
  }
  static ɵfac = function DialogService_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _DialogService)(ɵɵinject(ApplicationRef), ɵɵinject(Injector), ɵɵinject(DOCUMENT));
  };
  static ɵprov = ɵɵdefineInjectable({
    token: _DialogService,
    factory: _DialogService.ɵfac
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DialogService, [{
    type: Injectable
  }], () => [{
    type: ApplicationRef
  }, {
    type: Injector
  }, {
    type: Document,
    decorators: [{
      type: Inject,
      args: [DOCUMENT]
    }]
  }], null);
})();
export {
  DialogService,
  DynamicDialog,
  DynamicDialogClasses,
  DynamicDialogConfig,
  DynamicDialogInjector,
  DynamicDialogModule,
  DynamicDialogRef,
  DynamicDialogStyle
};
//# sourceMappingURL=primeng_dynamicdialog.js.map
