import './polyfills.server.mjs';
import {
  MAT_TOOLTIP_SCROLL_STRATEGY_FACTORY_PROVIDER,
  MatTooltip,
  MiembrosService,
  OverlayModule,
  TooltipComponent
} from "./chunk-FPUGQNMX.mjs";
import {
  MAT_FORM_FIELD,
  MatFormField,
  MatFormFieldControl,
  MatFormFieldModule,
  MatInput,
  MatInputModule,
  MatLabel,
  MatSuffix
} from "./chunk-LQ4XYGPI.mjs";
import {
  ErrorStateMatcher,
  _ErrorStateTracker
} from "./chunk-X3VHO37D.mjs";
import {
  CdkScrollableModule,
  ScrollingModule,
  ViewportRuler
} from "./chunk-M7FVHXRD.mjs";
import {
  MatButton,
  MatButtonModule,
  MatIconButton
} from "./chunk-FBNBSXHX.mjs";
import {
  MatRippleLoader
} from "./chunk-KEUUJZPF.mjs";
import {
  MatProgressSpinner,
  MatProgressSpinnerModule
} from "./chunk-XR745ZVT.mjs";
import {
  MatCard,
  MatCardModule
} from "./chunk-2HWDCYPT.mjs";
import {
  _DisposeViewRepeaterStrategy,
  _RecycleViewRepeaterStrategy,
  _VIEW_REPEATER_STRATEGY,
  _ViewRepeaterOperation,
  isDataSource
} from "./chunk-N2W2VB4R.mjs";
import {
  DefaultValueAccessor,
  FormGroupDirective,
  FormsModule,
  NG_VALUE_ACCESSOR,
  NgControl,
  NgControlStatus,
  NgForm,
  NgModel,
  Validators
} from "./chunk-22AJVEKS.mjs";
import {
  MatIcon,
  MatIconModule
} from "./chunk-BMEM75CN.mjs";
import {
  AuthService,
  MAT_RIPPLE_GLOBAL_OPTIONS,
  MatRippleModule,
  RouterLink,
  _StructuralStylesLoader,
  _animationsDisabled
} from "./chunk-2GCZFSIW.mjs";
import {
  A11yModule,
  BACKSPACE,
  DELETE,
  DOWN_ARROW,
  Directionality,
  ENTER,
  FocusKeyManager,
  FocusMonitor,
  MatCommonModule,
  Platform,
  SPACE,
  TAB,
  UP_ARROW,
  _CdkPrivateStyleLoader,
  _IdGenerator,
  _VisuallyHiddenLoader,
  hasModifierKey
} from "./chunk-HPMMNQDD.mjs";
import {
  BehaviorSubject,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ContentChild,
  ContentChildren,
  DOCUMENT,
  Directive,
  ElementRef,
  EventEmitter,
  HOST_TAG_NAME,
  HostAttributeToken,
  InjectionToken,
  Injector,
  Input,
  IterableDiffers,
  NgModule,
  NgZone,
  Output,
  QueryList,
  Subject,
  TemplateRef,
  ViewChild,
  ViewContainerRef,
  ViewEncapsulation,
  afterNextRender,
  booleanAttribute,
  forwardRef,
  inject,
  isObservable,
  merge,
  numberAttribute,
  of,
  setClassMetadata,
  signal,
  startWith,
  switchMap,
  takeUntil,
  ɵsetClassDebugInfo,
  ɵɵInheritDefinitionFeature,
  ɵɵNgOnChangesFeature,
  ɵɵProvidersFeature,
  ɵɵadvance,
  ɵɵattribute,
  ɵɵclassMap,
  ɵɵclassProp,
  ɵɵconditional,
  ɵɵconditionalCreate,
  ɵɵcontentQuery,
  ɵɵdefineComponent,
  ɵɵdefineDirective,
  ɵɵdefineInjector,
  ɵɵdefineNgModule,
  ɵɵdomElementEnd,
  ɵɵdomElementStart,
  ɵɵdomProperty,
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
  ɵɵnamespaceSVG,
  ɵɵnextContext,
  ɵɵprojection,
  ɵɵprojectionDef,
  ɵɵproperty,
  ɵɵpureFunction1,
  ɵɵqueryRefresh,
  ɵɵresetView,
  ɵɵrestoreView,
  ɵɵstyleProp,
  ɵɵtemplate,
  ɵɵtext,
  ɵɵtextInterpolate,
  ɵɵtextInterpolate1,
  ɵɵtextInterpolate2,
  ɵɵtwoWayBindingSet,
  ɵɵtwoWayListener,
  ɵɵtwoWayProperty,
  ɵɵviewQuery
} from "./chunk-UZ5PZMGS.mjs";
import "./chunk-T2KOBY73.mjs";

// node_modules/@angular/cdk/fesm2022/table.mjs
var _c0 = [[["caption"]], [["colgroup"], ["col"]], "*"];
var _c1 = ["caption", "colgroup, col", "*"];
function CdkTable_Conditional_2_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275projection(0, 2);
  }
}
function CdkTable_Conditional_3_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "thead", 0);
    \u0275\u0275elementContainer(1, 1);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(2, "tbody", 0);
    \u0275\u0275elementContainer(3, 2)(4, 3);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(5, "tfoot", 0);
    \u0275\u0275elementContainer(6, 4);
    \u0275\u0275elementEnd();
  }
}
function CdkTable_Conditional_4_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementContainer(0, 1)(1, 2)(2, 3)(3, 4);
  }
}
function CdkTextColumn_th_1_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "th", 3);
    \u0275\u0275text(1);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const ctx_r0 = \u0275\u0275nextContext();
    \u0275\u0275styleProp("text-align", ctx_r0.justify);
    \u0275\u0275advance();
    \u0275\u0275textInterpolate1(" ", ctx_r0.headerText, " ");
  }
}
function CdkTextColumn_td_2_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "td", 4);
    \u0275\u0275text(1);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const data_r2 = ctx.$implicit;
    const ctx_r0 = \u0275\u0275nextContext();
    \u0275\u0275styleProp("text-align", ctx_r0.justify);
    \u0275\u0275advance();
    \u0275\u0275textInterpolate1(" ", ctx_r0.dataAccessor(data_r2, ctx_r0.name), " ");
  }
}
var CDK_TABLE = new InjectionToken("CDK_TABLE");
var TEXT_COLUMN_OPTIONS = new InjectionToken("text-column-options");
var CdkCellDef = class _CdkCellDef {
  /** @docs-private */
  template = inject(TemplateRef);
  constructor() {
  }
  static \u0275fac = function CdkCellDef_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkCellDef)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkCellDef,
    selectors: [["", "cdkCellDef", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkCellDef, [{
    type: Directive,
    args: [{
      selector: "[cdkCellDef]"
    }]
  }], () => [], null);
})();
var CdkHeaderCellDef = class _CdkHeaderCellDef {
  /** @docs-private */
  template = inject(TemplateRef);
  constructor() {
  }
  static \u0275fac = function CdkHeaderCellDef_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkHeaderCellDef)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkHeaderCellDef,
    selectors: [["", "cdkHeaderCellDef", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkHeaderCellDef, [{
    type: Directive,
    args: [{
      selector: "[cdkHeaderCellDef]"
    }]
  }], () => [], null);
})();
var CdkFooterCellDef = class _CdkFooterCellDef {
  /** @docs-private */
  template = inject(TemplateRef);
  constructor() {
  }
  static \u0275fac = function CdkFooterCellDef_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkFooterCellDef)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkFooterCellDef,
    selectors: [["", "cdkFooterCellDef", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkFooterCellDef, [{
    type: Directive,
    args: [{
      selector: "[cdkFooterCellDef]"
    }]
  }], () => [], null);
})();
var CdkColumnDef = class _CdkColumnDef {
  _table = inject(CDK_TABLE, {
    optional: true
  });
  _hasStickyChanged = false;
  /** Unique name for this column. */
  get name() {
    return this._name;
  }
  set name(name) {
    this._setNameInput(name);
  }
  _name;
  /** Whether the cell is sticky. */
  get sticky() {
    return this._sticky;
  }
  set sticky(value) {
    if (value !== this._sticky) {
      this._sticky = value;
      this._hasStickyChanged = true;
    }
  }
  _sticky = false;
  /**
   * Whether this column should be sticky positioned on the end of the row. Should make sure
   * that it mimics the `CanStick` mixin such that `_hasStickyChanged` is set to true if the value
   * has been changed.
   */
  get stickyEnd() {
    return this._stickyEnd;
  }
  set stickyEnd(value) {
    if (value !== this._stickyEnd) {
      this._stickyEnd = value;
      this._hasStickyChanged = true;
    }
  }
  _stickyEnd = false;
  /** @docs-private */
  cell;
  /** @docs-private */
  headerCell;
  /** @docs-private */
  footerCell;
  /**
   * Transformed version of the column name that can be used as part of a CSS classname. Excludes
   * all non-alphanumeric characters and the special characters '-' and '_'. Any characters that
   * do not match are replaced by the '-' character.
   */
  cssClassFriendlyName;
  /**
   * Class name for cells in this column.
   * @docs-private
   */
  _columnCssClassName;
  constructor() {
  }
  /** Whether the sticky state has changed. */
  hasStickyChanged() {
    const hasStickyChanged = this._hasStickyChanged;
    this.resetStickyChanged();
    return hasStickyChanged;
  }
  /** Resets the sticky changed state. */
  resetStickyChanged() {
    this._hasStickyChanged = false;
  }
  /**
   * Overridable method that sets the css classes that will be added to every cell in this
   * column.
   * In the future, columnCssClassName will change from type string[] to string and this
   * will set a single string value.
   * @docs-private
   */
  _updateColumnCssClassName() {
    this._columnCssClassName = [`cdk-column-${this.cssClassFriendlyName}`];
  }
  /**
   * This has been extracted to a util because of TS 4 and VE.
   * View Engine doesn't support property rename inheritance.
   * TS 4.0 doesn't allow properties to override accessors or vice-versa.
   * @docs-private
   */
  _setNameInput(value) {
    if (value) {
      this._name = value;
      this.cssClassFriendlyName = value.replace(/[^a-z0-9_-]/gi, "-");
      this._updateColumnCssClassName();
    }
  }
  static \u0275fac = function CdkColumnDef_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkColumnDef)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkColumnDef,
    selectors: [["", "cdkColumnDef", ""]],
    contentQueries: function CdkColumnDef_ContentQueries(rf, ctx, dirIndex) {
      if (rf & 1) {
        \u0275\u0275contentQuery(dirIndex, CdkCellDef, 5);
        \u0275\u0275contentQuery(dirIndex, CdkHeaderCellDef, 5);
        \u0275\u0275contentQuery(dirIndex, CdkFooterCellDef, 5);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.cell = _t.first);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.headerCell = _t.first);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.footerCell = _t.first);
      }
    },
    inputs: {
      name: [0, "cdkColumnDef", "name"],
      sticky: [2, "sticky", "sticky", booleanAttribute],
      stickyEnd: [2, "stickyEnd", "stickyEnd", booleanAttribute]
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: "MAT_SORT_HEADER_COLUMN_DEF",
      useExisting: _CdkColumnDef
    }])]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkColumnDef, [{
    type: Directive,
    args: [{
      selector: "[cdkColumnDef]",
      providers: [{
        provide: "MAT_SORT_HEADER_COLUMN_DEF",
        useExisting: CdkColumnDef
      }]
    }]
  }], () => [], {
    name: [{
      type: Input,
      args: ["cdkColumnDef"]
    }],
    sticky: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    stickyEnd: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    cell: [{
      type: ContentChild,
      args: [CdkCellDef]
    }],
    headerCell: [{
      type: ContentChild,
      args: [CdkHeaderCellDef]
    }],
    footerCell: [{
      type: ContentChild,
      args: [CdkFooterCellDef]
    }]
  });
})();
var BaseCdkCell = class {
  constructor(columnDef, elementRef) {
    elementRef.nativeElement.classList.add(...columnDef._columnCssClassName);
  }
};
var CdkHeaderCell = class _CdkHeaderCell extends BaseCdkCell {
  constructor() {
    super(inject(CdkColumnDef), inject(ElementRef));
  }
  static \u0275fac = function CdkHeaderCell_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkHeaderCell)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkHeaderCell,
    selectors: [["cdk-header-cell"], ["th", "cdk-header-cell", ""]],
    hostAttrs: ["role", "columnheader", 1, "cdk-header-cell"],
    features: [\u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkHeaderCell, [{
    type: Directive,
    args: [{
      selector: "cdk-header-cell, th[cdk-header-cell]",
      host: {
        "class": "cdk-header-cell",
        "role": "columnheader"
      }
    }]
  }], () => [], null);
})();
var CdkFooterCell = class _CdkFooterCell extends BaseCdkCell {
  constructor() {
    const columnDef = inject(CdkColumnDef);
    const elementRef = inject(ElementRef);
    super(columnDef, elementRef);
    const role = columnDef._table?._getCellRole();
    if (role) {
      elementRef.nativeElement.setAttribute("role", role);
    }
  }
  static \u0275fac = function CdkFooterCell_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkFooterCell)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkFooterCell,
    selectors: [["cdk-footer-cell"], ["td", "cdk-footer-cell", ""]],
    hostAttrs: [1, "cdk-footer-cell"],
    features: [\u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkFooterCell, [{
    type: Directive,
    args: [{
      selector: "cdk-footer-cell, td[cdk-footer-cell]",
      host: {
        "class": "cdk-footer-cell"
      }
    }]
  }], () => [], null);
})();
var CdkCell = class _CdkCell extends BaseCdkCell {
  constructor() {
    const columnDef = inject(CdkColumnDef);
    const elementRef = inject(ElementRef);
    super(columnDef, elementRef);
    const role = columnDef._table?._getCellRole();
    if (role) {
      elementRef.nativeElement.setAttribute("role", role);
    }
  }
  static \u0275fac = function CdkCell_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkCell)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkCell,
    selectors: [["cdk-cell"], ["td", "cdk-cell", ""]],
    hostAttrs: [1, "cdk-cell"],
    features: [\u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkCell, [{
    type: Directive,
    args: [{
      selector: "cdk-cell, td[cdk-cell]",
      host: {
        "class": "cdk-cell"
      }
    }]
  }], () => [], null);
})();
var CDK_ROW_TEMPLATE = `<ng-container cdkCellOutlet></ng-container>`;
var BaseRowDef = class _BaseRowDef {
  template = inject(TemplateRef);
  _differs = inject(IterableDiffers);
  /** The columns to be displayed on this row. */
  columns;
  /** Differ used to check if any changes were made to the columns. */
  _columnsDiffer;
  constructor() {
  }
  ngOnChanges(changes) {
    if (!this._columnsDiffer) {
      const columns = changes["columns"] && changes["columns"].currentValue || [];
      this._columnsDiffer = this._differs.find(columns).create();
      this._columnsDiffer.diff(columns);
    }
  }
  /**
   * Returns the difference between the current columns and the columns from the last diff, or null
   * if there is no difference.
   */
  getColumnsDiff() {
    return this._columnsDiffer.diff(this.columns);
  }
  /** Gets this row def's relevant cell template from the provided column def. */
  extractCellTemplate(column) {
    if (this instanceof CdkHeaderRowDef) {
      return column.headerCell.template;
    }
    if (this instanceof CdkFooterRowDef) {
      return column.footerCell.template;
    } else {
      return column.cell.template;
    }
  }
  static \u0275fac = function BaseRowDef_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _BaseRowDef)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _BaseRowDef,
    features: [\u0275\u0275NgOnChangesFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(BaseRowDef, [{
    type: Directive
  }], () => [], null);
})();
var CdkHeaderRowDef = class _CdkHeaderRowDef extends BaseRowDef {
  _table = inject(CDK_TABLE, {
    optional: true
  });
  _hasStickyChanged = false;
  /** Whether the row is sticky. */
  get sticky() {
    return this._sticky;
  }
  set sticky(value) {
    if (value !== this._sticky) {
      this._sticky = value;
      this._hasStickyChanged = true;
    }
  }
  _sticky = false;
  constructor() {
    super(inject(TemplateRef), inject(IterableDiffers));
  }
  // Prerender fails to recognize that ngOnChanges in a part of this class through inheritance.
  // Explicitly define it so that the method is called as part of the Angular lifecycle.
  ngOnChanges(changes) {
    super.ngOnChanges(changes);
  }
  /** Whether the sticky state has changed. */
  hasStickyChanged() {
    const hasStickyChanged = this._hasStickyChanged;
    this.resetStickyChanged();
    return hasStickyChanged;
  }
  /** Resets the sticky changed state. */
  resetStickyChanged() {
    this._hasStickyChanged = false;
  }
  static \u0275fac = function CdkHeaderRowDef_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkHeaderRowDef)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkHeaderRowDef,
    selectors: [["", "cdkHeaderRowDef", ""]],
    inputs: {
      columns: [0, "cdkHeaderRowDef", "columns"],
      sticky: [2, "cdkHeaderRowDefSticky", "sticky", booleanAttribute]
    },
    features: [\u0275\u0275InheritDefinitionFeature, \u0275\u0275NgOnChangesFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkHeaderRowDef, [{
    type: Directive,
    args: [{
      selector: "[cdkHeaderRowDef]",
      inputs: [{
        name: "columns",
        alias: "cdkHeaderRowDef"
      }]
    }]
  }], () => [], {
    sticky: [{
      type: Input,
      args: [{
        alias: "cdkHeaderRowDefSticky",
        transform: booleanAttribute
      }]
    }]
  });
})();
var CdkFooterRowDef = class _CdkFooterRowDef extends BaseRowDef {
  _table = inject(CDK_TABLE, {
    optional: true
  });
  _hasStickyChanged = false;
  /** Whether the row is sticky. */
  get sticky() {
    return this._sticky;
  }
  set sticky(value) {
    if (value !== this._sticky) {
      this._sticky = value;
      this._hasStickyChanged = true;
    }
  }
  _sticky = false;
  constructor() {
    super(inject(TemplateRef), inject(IterableDiffers));
  }
  // Prerender fails to recognize that ngOnChanges in a part of this class through inheritance.
  // Explicitly define it so that the method is called as part of the Angular lifecycle.
  ngOnChanges(changes) {
    super.ngOnChanges(changes);
  }
  /** Whether the sticky state has changed. */
  hasStickyChanged() {
    const hasStickyChanged = this._hasStickyChanged;
    this.resetStickyChanged();
    return hasStickyChanged;
  }
  /** Resets the sticky changed state. */
  resetStickyChanged() {
    this._hasStickyChanged = false;
  }
  static \u0275fac = function CdkFooterRowDef_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkFooterRowDef)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkFooterRowDef,
    selectors: [["", "cdkFooterRowDef", ""]],
    inputs: {
      columns: [0, "cdkFooterRowDef", "columns"],
      sticky: [2, "cdkFooterRowDefSticky", "sticky", booleanAttribute]
    },
    features: [\u0275\u0275InheritDefinitionFeature, \u0275\u0275NgOnChangesFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkFooterRowDef, [{
    type: Directive,
    args: [{
      selector: "[cdkFooterRowDef]",
      inputs: [{
        name: "columns",
        alias: "cdkFooterRowDef"
      }]
    }]
  }], () => [], {
    sticky: [{
      type: Input,
      args: [{
        alias: "cdkFooterRowDefSticky",
        transform: booleanAttribute
      }]
    }]
  });
})();
var CdkRowDef = class _CdkRowDef extends BaseRowDef {
  _table = inject(CDK_TABLE, {
    optional: true
  });
  /**
   * Function that should return true if this row template should be used for the provided index
   * and row data. If left undefined, this row will be considered the default row template to use
   * when no other when functions return true for the data.
   * For every row, there must be at least one when function that passes or an undefined to default.
   */
  when;
  constructor() {
    super(inject(TemplateRef), inject(IterableDiffers));
  }
  static \u0275fac = function CdkRowDef_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkRowDef)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkRowDef,
    selectors: [["", "cdkRowDef", ""]],
    inputs: {
      columns: [0, "cdkRowDefColumns", "columns"],
      when: [0, "cdkRowDefWhen", "when"]
    },
    features: [\u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkRowDef, [{
    type: Directive,
    args: [{
      selector: "[cdkRowDef]",
      inputs: [{
        name: "columns",
        alias: "cdkRowDefColumns"
      }, {
        name: "when",
        alias: "cdkRowDefWhen"
      }]
    }]
  }], () => [], null);
})();
var CdkCellOutlet = class _CdkCellOutlet {
  _viewContainer = inject(ViewContainerRef);
  /** The ordered list of cells to render within this outlet's view container */
  cells;
  /** The data context to be provided to each cell */
  context;
  /**
   * Static property containing the latest constructed instance of this class.
   * Used by the CDK table when each CdkHeaderRow and CdkRow component is created using
   * createEmbeddedView. After one of these components are created, this property will provide
   * a handle to provide that component's cells and context. After init, the CdkCellOutlet will
   * construct the cells with the provided context.
   */
  static mostRecentCellOutlet = null;
  constructor() {
    _CdkCellOutlet.mostRecentCellOutlet = this;
  }
  ngOnDestroy() {
    if (_CdkCellOutlet.mostRecentCellOutlet === this) {
      _CdkCellOutlet.mostRecentCellOutlet = null;
    }
  }
  static \u0275fac = function CdkCellOutlet_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkCellOutlet)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkCellOutlet,
    selectors: [["", "cdkCellOutlet", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkCellOutlet, [{
    type: Directive,
    args: [{
      selector: "[cdkCellOutlet]"
    }]
  }], () => [], null);
})();
var CdkHeaderRow = class _CdkHeaderRow {
  static \u0275fac = function CdkHeaderRow_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkHeaderRow)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _CdkHeaderRow,
    selectors: [["cdk-header-row"], ["tr", "cdk-header-row", ""]],
    hostAttrs: ["role", "row", 1, "cdk-header-row"],
    decls: 1,
    vars: 0,
    consts: [["cdkCellOutlet", ""]],
    template: function CdkHeaderRow_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275elementContainer(0, 0);
      }
    },
    dependencies: [CdkCellOutlet],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkHeaderRow, [{
    type: Component,
    args: [{
      selector: "cdk-header-row, tr[cdk-header-row]",
      template: CDK_ROW_TEMPLATE,
      host: {
        "class": "cdk-header-row",
        "role": "row"
      },
      // See note on CdkTable for explanation on why this uses the default change detection strategy.
      // tslint:disable-next-line:validate-decorators
      changeDetection: ChangeDetectionStrategy.Default,
      encapsulation: ViewEncapsulation.None,
      imports: [CdkCellOutlet]
    }]
  }], null, null);
})();
var CdkFooterRow = class _CdkFooterRow {
  static \u0275fac = function CdkFooterRow_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkFooterRow)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _CdkFooterRow,
    selectors: [["cdk-footer-row"], ["tr", "cdk-footer-row", ""]],
    hostAttrs: ["role", "row", 1, "cdk-footer-row"],
    decls: 1,
    vars: 0,
    consts: [["cdkCellOutlet", ""]],
    template: function CdkFooterRow_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275elementContainer(0, 0);
      }
    },
    dependencies: [CdkCellOutlet],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkFooterRow, [{
    type: Component,
    args: [{
      selector: "cdk-footer-row, tr[cdk-footer-row]",
      template: CDK_ROW_TEMPLATE,
      host: {
        "class": "cdk-footer-row",
        "role": "row"
      },
      // See note on CdkTable for explanation on why this uses the default change detection strategy.
      // tslint:disable-next-line:validate-decorators
      changeDetection: ChangeDetectionStrategy.Default,
      encapsulation: ViewEncapsulation.None,
      imports: [CdkCellOutlet]
    }]
  }], null, null);
})();
var CdkRow = class _CdkRow {
  static \u0275fac = function CdkRow_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkRow)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _CdkRow,
    selectors: [["cdk-row"], ["tr", "cdk-row", ""]],
    hostAttrs: ["role", "row", 1, "cdk-row"],
    decls: 1,
    vars: 0,
    consts: [["cdkCellOutlet", ""]],
    template: function CdkRow_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275elementContainer(0, 0);
      }
    },
    dependencies: [CdkCellOutlet],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkRow, [{
    type: Component,
    args: [{
      selector: "cdk-row, tr[cdk-row]",
      template: CDK_ROW_TEMPLATE,
      host: {
        "class": "cdk-row",
        "role": "row"
      },
      // See note on CdkTable for explanation on why this uses the default change detection strategy.
      // tslint:disable-next-line:validate-decorators
      changeDetection: ChangeDetectionStrategy.Default,
      encapsulation: ViewEncapsulation.None,
      imports: [CdkCellOutlet]
    }]
  }], null, null);
})();
var CdkNoDataRow = class _CdkNoDataRow {
  templateRef = inject(TemplateRef);
  _contentClassNames = ["cdk-no-data-row", "cdk-row"];
  _cellClassNames = ["cdk-cell", "cdk-no-data-cell"];
  _cellSelector = "td, cdk-cell, [cdk-cell], .cdk-cell";
  constructor() {
  }
  static \u0275fac = function CdkNoDataRow_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkNoDataRow)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkNoDataRow,
    selectors: [["ng-template", "cdkNoDataRow", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkNoDataRow, [{
    type: Directive,
    args: [{
      selector: "ng-template[cdkNoDataRow]"
    }]
  }], () => [], null);
})();
var STICKY_DIRECTIONS = ["top", "bottom", "left", "right"];
var StickyStyler = class {
  _isNativeHtmlTable;
  _stickCellCss;
  _isBrowser;
  _needsPositionStickyOnElement;
  direction;
  _positionListener;
  _tableInjector;
  _elemSizeCache = /* @__PURE__ */ new WeakMap();
  _resizeObserver = globalThis?.ResizeObserver ? new globalThis.ResizeObserver((entries) => this._updateCachedSizes(entries)) : null;
  _updatedStickyColumnsParamsToReplay = [];
  _stickyColumnsReplayTimeout = null;
  _cachedCellWidths = [];
  _borderCellCss;
  _destroyed = false;
  /**
   * @param _isNativeHtmlTable Whether the sticky logic should be based on a table
   *     that uses the native `<table>` element.
   * @param _stickCellCss The CSS class that will be applied to every row/cell that has
   *     sticky positioning applied.
   * @param direction The directionality context of the table (ltr/rtl); affects column positioning
   *     by reversing left/right positions.
   * @param _isBrowser Whether the table is currently being rendered on the server or the client.
   * @param _needsPositionStickyOnElement Whether we need to specify position: sticky on cells
   *     using inline styles. If false, it is assumed that position: sticky is included in
   *     the component stylesheet for _stickCellCss.
   * @param _positionListener A listener that is notified of changes to sticky rows/columns
   *     and their dimensions.
   * @param _tableInjector The table's Injector.
   */
  constructor(_isNativeHtmlTable, _stickCellCss, _isBrowser = true, _needsPositionStickyOnElement = true, direction, _positionListener, _tableInjector) {
    this._isNativeHtmlTable = _isNativeHtmlTable;
    this._stickCellCss = _stickCellCss;
    this._isBrowser = _isBrowser;
    this._needsPositionStickyOnElement = _needsPositionStickyOnElement;
    this.direction = direction;
    this._positionListener = _positionListener;
    this._tableInjector = _tableInjector;
    this._borderCellCss = {
      "top": `${_stickCellCss}-border-elem-top`,
      "bottom": `${_stickCellCss}-border-elem-bottom`,
      "left": `${_stickCellCss}-border-elem-left`,
      "right": `${_stickCellCss}-border-elem-right`
    };
  }
  /**
   * Clears the sticky positioning styles from the row and its cells by resetting the `position`
   * style, setting the zIndex to 0, and unsetting each provided sticky direction.
   * @param rows The list of rows that should be cleared from sticking in the provided directions
   * @param stickyDirections The directions that should no longer be set as sticky on the rows.
   */
  clearStickyPositioning(rows, stickyDirections) {
    if (stickyDirections.includes("left") || stickyDirections.includes("right")) {
      this._removeFromStickyColumnReplayQueue(rows);
    }
    const elementsToClear = [];
    for (const row of rows) {
      if (row.nodeType !== row.ELEMENT_NODE) {
        continue;
      }
      elementsToClear.push(row, ...Array.from(row.children));
    }
    afterNextRender({
      write: () => {
        for (const element of elementsToClear) {
          this._removeStickyStyle(element, stickyDirections);
        }
      }
    }, {
      injector: this._tableInjector
    });
  }
  /**
   * Applies sticky left and right positions to the cells of each row according to the sticky
   * states of the rendered column definitions.
   * @param rows The rows that should have its set of cells stuck according to the sticky states.
   * @param stickyStartStates A list of boolean states where each state represents whether the cell
   *     in this index position should be stuck to the start of the row.
   * @param stickyEndStates A list of boolean states where each state represents whether the cell
   *     in this index position should be stuck to the end of the row.
   * @param recalculateCellWidths Whether the sticky styler should recalculate the width of each
   *     column cell. If `false` cached widths will be used instead.
   * @param replay Whether to enqueue this call for replay after a ResizeObserver update.
   */
  updateStickyColumns(rows, stickyStartStates, stickyEndStates, recalculateCellWidths = true, replay = true) {
    if (!rows.length || !this._isBrowser || !(stickyStartStates.some((state) => state) || stickyEndStates.some((state) => state))) {
      this._positionListener?.stickyColumnsUpdated({
        sizes: []
      });
      this._positionListener?.stickyEndColumnsUpdated({
        sizes: []
      });
      return;
    }
    const firstRow = rows[0];
    const numCells = firstRow.children.length;
    const isRtl = this.direction === "rtl";
    const start = isRtl ? "right" : "left";
    const end = isRtl ? "left" : "right";
    const lastStickyStart = stickyStartStates.lastIndexOf(true);
    const firstStickyEnd = stickyEndStates.indexOf(true);
    let cellWidths;
    let startPositions;
    let endPositions;
    if (replay) {
      this._updateStickyColumnReplayQueue({
        rows: [...rows],
        stickyStartStates: [...stickyStartStates],
        stickyEndStates: [...stickyEndStates]
      });
    }
    afterNextRender({
      earlyRead: () => {
        cellWidths = this._getCellWidths(firstRow, recalculateCellWidths);
        startPositions = this._getStickyStartColumnPositions(cellWidths, stickyStartStates);
        endPositions = this._getStickyEndColumnPositions(cellWidths, stickyEndStates);
      },
      write: () => {
        for (const row of rows) {
          for (let i = 0; i < numCells; i++) {
            const cell = row.children[i];
            if (stickyStartStates[i]) {
              this._addStickyStyle(cell, start, startPositions[i], i === lastStickyStart);
            }
            if (stickyEndStates[i]) {
              this._addStickyStyle(cell, end, endPositions[i], i === firstStickyEnd);
            }
          }
        }
        if (this._positionListener && cellWidths.some((w) => !!w)) {
          this._positionListener.stickyColumnsUpdated({
            sizes: lastStickyStart === -1 ? [] : cellWidths.slice(0, lastStickyStart + 1).map((width, index) => stickyStartStates[index] ? width : null)
          });
          this._positionListener.stickyEndColumnsUpdated({
            sizes: firstStickyEnd === -1 ? [] : cellWidths.slice(firstStickyEnd).map((width, index) => stickyEndStates[index + firstStickyEnd] ? width : null).reverse()
          });
        }
      }
    }, {
      injector: this._tableInjector
    });
  }
  /**
   * Applies sticky positioning to the row's cells if using the native table layout, and to the
   * row itself otherwise.
   * @param rowsToStick The list of rows that should be stuck according to their corresponding
   *     sticky state and to the provided top or bottom position.
   * @param stickyStates A list of boolean states where each state represents whether the row
   *     should be stuck in the particular top or bottom position.
   * @param position The position direction in which the row should be stuck if that row should be
   *     sticky.
   *
   */
  stickRows(rowsToStick, stickyStates, position) {
    if (!this._isBrowser) {
      return;
    }
    const rows = position === "bottom" ? rowsToStick.slice().reverse() : rowsToStick;
    const states = position === "bottom" ? stickyStates.slice().reverse() : stickyStates;
    const stickyOffsets = [];
    const stickyCellHeights = [];
    const elementsToStick = [];
    afterNextRender({
      earlyRead: () => {
        for (let rowIndex = 0, stickyOffset = 0; rowIndex < rows.length; rowIndex++) {
          if (!states[rowIndex]) {
            continue;
          }
          stickyOffsets[rowIndex] = stickyOffset;
          const row = rows[rowIndex];
          elementsToStick[rowIndex] = this._isNativeHtmlTable ? Array.from(row.children) : [row];
          const height = this._retrieveElementSize(row).height;
          stickyOffset += height;
          stickyCellHeights[rowIndex] = height;
        }
      },
      write: () => {
        const borderedRowIndex = states.lastIndexOf(true);
        for (let rowIndex = 0; rowIndex < rows.length; rowIndex++) {
          if (!states[rowIndex]) {
            continue;
          }
          const offset = stickyOffsets[rowIndex];
          const isBorderedRowIndex = rowIndex === borderedRowIndex;
          for (const element of elementsToStick[rowIndex]) {
            this._addStickyStyle(element, position, offset, isBorderedRowIndex);
          }
        }
        if (position === "top") {
          this._positionListener?.stickyHeaderRowsUpdated({
            sizes: stickyCellHeights,
            offsets: stickyOffsets,
            elements: elementsToStick
          });
        } else {
          this._positionListener?.stickyFooterRowsUpdated({
            sizes: stickyCellHeights,
            offsets: stickyOffsets,
            elements: elementsToStick
          });
        }
      }
    }, {
      injector: this._tableInjector
    });
  }
  /**
   * When using the native table in Safari, sticky footer cells do not stick. The only way to stick
   * footer rows is to apply sticky styling to the tfoot container. This should only be done if
   * all footer rows are sticky. If not all footer rows are sticky, remove sticky positioning from
   * the tfoot element.
   */
  updateStickyFooterContainer(tableElement, stickyStates) {
    if (!this._isNativeHtmlTable) {
      return;
    }
    afterNextRender({
      write: () => {
        const tfoot = tableElement.querySelector("tfoot");
        if (tfoot) {
          if (stickyStates.some((state) => !state)) {
            this._removeStickyStyle(tfoot, ["bottom"]);
          } else {
            this._addStickyStyle(tfoot, "bottom", 0, false);
          }
        }
      }
    }, {
      injector: this._tableInjector
    });
  }
  /** Triggered by the table's OnDestroy hook. */
  destroy() {
    if (this._stickyColumnsReplayTimeout) {
      clearTimeout(this._stickyColumnsReplayTimeout);
    }
    this._resizeObserver?.disconnect();
    this._destroyed = true;
  }
  /**
   * Removes the sticky style on the element by removing the sticky cell CSS class, re-evaluating
   * the zIndex, removing each of the provided sticky directions, and removing the
   * sticky position if there are no more directions.
   */
  _removeStickyStyle(element, stickyDirections) {
    if (!element.classList.contains(this._stickCellCss)) {
      return;
    }
    for (const dir of stickyDirections) {
      element.style[dir] = "";
      element.classList.remove(this._borderCellCss[dir]);
    }
    const hasDirection = STICKY_DIRECTIONS.some((dir) => stickyDirections.indexOf(dir) === -1 && element.style[dir]);
    if (hasDirection) {
      element.style.zIndex = this._getCalculatedZIndex(element);
    } else {
      element.style.zIndex = "";
      if (this._needsPositionStickyOnElement) {
        element.style.position = "";
      }
      element.classList.remove(this._stickCellCss);
    }
  }
  /**
   * Adds the sticky styling to the element by adding the sticky style class, changing position
   * to be sticky (and -webkit-sticky), setting the appropriate zIndex, and adding a sticky
   * direction and value.
   */
  _addStickyStyle(element, dir, dirValue, isBorderElement) {
    element.classList.add(this._stickCellCss);
    if (isBorderElement) {
      element.classList.add(this._borderCellCss[dir]);
    }
    element.style[dir] = `${dirValue}px`;
    element.style.zIndex = this._getCalculatedZIndex(element);
    if (this._needsPositionStickyOnElement) {
      element.style.cssText += "position: -webkit-sticky; position: sticky; ";
    }
  }
  /**
   * Calculate what the z-index should be for the element, depending on what directions (top,
   * bottom, left, right) have been set. It should be true that elements with a top direction
   * should have the highest index since these are elements like a table header. If any of those
   * elements are also sticky in another direction, then they should appear above other elements
   * that are only sticky top (e.g. a sticky column on a sticky header). Bottom-sticky elements
   * (e.g. footer rows) should then be next in the ordering such that they are below the header
   * but above any non-sticky elements. Finally, left/right sticky elements (e.g. sticky columns)
   * should minimally increment so that they are above non-sticky elements but below top and bottom
   * elements.
   */
  _getCalculatedZIndex(element) {
    const zIndexIncrements = {
      top: 100,
      bottom: 10,
      left: 1,
      right: 1
    };
    let zIndex = 0;
    for (const dir of STICKY_DIRECTIONS) {
      if (element.style[dir]) {
        zIndex += zIndexIncrements[dir];
      }
    }
    return zIndex ? `${zIndex}` : "";
  }
  /** Gets the widths for each cell in the provided row. */
  _getCellWidths(row, recalculateCellWidths = true) {
    if (!recalculateCellWidths && this._cachedCellWidths.length) {
      return this._cachedCellWidths;
    }
    const cellWidths = [];
    const firstRowCells = row.children;
    for (let i = 0; i < firstRowCells.length; i++) {
      const cell = firstRowCells[i];
      cellWidths.push(this._retrieveElementSize(cell).width);
    }
    this._cachedCellWidths = cellWidths;
    return cellWidths;
  }
  /**
   * Determines the left and right positions of each sticky column cell, which will be the
   * accumulation of all sticky column cell widths to the left and right, respectively.
   * Non-sticky cells do not need to have a value set since their positions will not be applied.
   */
  _getStickyStartColumnPositions(widths, stickyStates) {
    const positions = [];
    let nextPosition = 0;
    for (let i = 0; i < widths.length; i++) {
      if (stickyStates[i]) {
        positions[i] = nextPosition;
        nextPosition += widths[i];
      }
    }
    return positions;
  }
  /**
   * Determines the left and right positions of each sticky column cell, which will be the
   * accumulation of all sticky column cell widths to the left and right, respectively.
   * Non-sticky cells do not need to have a value set since their positions will not be applied.
   */
  _getStickyEndColumnPositions(widths, stickyStates) {
    const positions = [];
    let nextPosition = 0;
    for (let i = widths.length; i > 0; i--) {
      if (stickyStates[i]) {
        positions[i] = nextPosition;
        nextPosition += widths[i];
      }
    }
    return positions;
  }
  /**
   * Retreives the most recently observed size of the specified element from the cache, or
   * meaures it directly if not yet cached.
   */
  _retrieveElementSize(element) {
    const cachedSize = this._elemSizeCache.get(element);
    if (cachedSize) {
      return cachedSize;
    }
    const clientRect = element.getBoundingClientRect();
    const size = {
      width: clientRect.width,
      height: clientRect.height
    };
    if (!this._resizeObserver) {
      return size;
    }
    this._elemSizeCache.set(element, size);
    this._resizeObserver.observe(element, {
      box: "border-box"
    });
    return size;
  }
  /**
   * Conditionally enqueue the requested sticky update and clear previously queued updates
   * for the same rows.
   */
  _updateStickyColumnReplayQueue(params) {
    this._removeFromStickyColumnReplayQueue(params.rows);
    if (!this._stickyColumnsReplayTimeout) {
      this._updatedStickyColumnsParamsToReplay.push(params);
    }
  }
  /** Remove updates for the specified rows from the queue. */
  _removeFromStickyColumnReplayQueue(rows) {
    const rowsSet = new Set(rows);
    for (const update of this._updatedStickyColumnsParamsToReplay) {
      update.rows = update.rows.filter((row) => !rowsSet.has(row));
    }
    this._updatedStickyColumnsParamsToReplay = this._updatedStickyColumnsParamsToReplay.filter((update) => !!update.rows.length);
  }
  /** Update _elemSizeCache with the observed sizes. */
  _updateCachedSizes(entries) {
    let needsColumnUpdate = false;
    for (const entry of entries) {
      const newEntry = entry.borderBoxSize?.length ? {
        width: entry.borderBoxSize[0].inlineSize,
        height: entry.borderBoxSize[0].blockSize
      } : {
        width: entry.contentRect.width,
        height: entry.contentRect.height
      };
      if (newEntry.width !== this._elemSizeCache.get(entry.target)?.width && isCell(entry.target)) {
        needsColumnUpdate = true;
      }
      this._elemSizeCache.set(entry.target, newEntry);
    }
    if (needsColumnUpdate && this._updatedStickyColumnsParamsToReplay.length) {
      if (this._stickyColumnsReplayTimeout) {
        clearTimeout(this._stickyColumnsReplayTimeout);
      }
      this._stickyColumnsReplayTimeout = setTimeout(() => {
        if (this._destroyed) {
          return;
        }
        for (const update of this._updatedStickyColumnsParamsToReplay) {
          this.updateStickyColumns(update.rows, update.stickyStartStates, update.stickyEndStates, true, false);
        }
        this._updatedStickyColumnsParamsToReplay = [];
        this._stickyColumnsReplayTimeout = null;
      }, 0);
    }
  }
};
function isCell(element) {
  return ["cdk-cell", "cdk-header-cell", "cdk-footer-cell"].some((klass) => element.classList.contains(klass));
}
function getTableUnknownColumnError(id) {
  return Error(`Could not find column with id "${id}".`);
}
function getTableDuplicateColumnNameError(name) {
  return Error(`Duplicate column definition name provided: "${name}".`);
}
function getTableMultipleDefaultRowDefsError() {
  return Error(`There can only be one default row without a when predicate function. Or set \`multiTemplateDataRows\`.`);
}
function getTableMissingMatchingRowDefError(data) {
  return Error(`Could not find a matching row definition for the provided row data: ${JSON.stringify(data)}`);
}
function getTableMissingRowDefsError() {
  return Error("Missing definitions for header, footer, and row; cannot determine which columns should be rendered.");
}
function getTableUnknownDataSourceError() {
  return Error(`Provided data source did not match an array, Observable, or DataSource`);
}
function getTableTextColumnMissingParentTableError() {
  return Error(`Text column could not find a parent table for registration.`);
}
function getTableTextColumnMissingNameError() {
  return Error(`Table text column must have a name.`);
}
var STICKY_POSITIONING_LISTENER = new InjectionToken("CDK_SPL");
var CdkRecycleRows = class _CdkRecycleRows {
  static \u0275fac = function CdkRecycleRows_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkRecycleRows)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _CdkRecycleRows,
    selectors: [["cdk-table", "recycleRows", ""], ["table", "cdk-table", "", "recycleRows", ""]],
    features: [\u0275\u0275ProvidersFeature([{
      provide: _VIEW_REPEATER_STRATEGY,
      useClass: _RecycleViewRepeaterStrategy
    }])]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkRecycleRows, [{
    type: Directive,
    args: [{
      selector: "cdk-table[recycleRows], table[cdk-table][recycleRows]",
      providers: [{
        provide: _VIEW_REPEATER_STRATEGY,
        useClass: _RecycleViewRepeaterStrategy
      }]
    }]
  }], null, null);
})();
var DataRowOutlet = class _DataRowOutlet {
  viewContainer = inject(ViewContainerRef);
  elementRef = inject(ElementRef);
  constructor() {
    const table = inject(CDK_TABLE);
    table._rowOutlet = this;
    table._outletAssigned();
  }
  static \u0275fac = function DataRowOutlet_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _DataRowOutlet)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _DataRowOutlet,
    selectors: [["", "rowOutlet", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(DataRowOutlet, [{
    type: Directive,
    args: [{
      selector: "[rowOutlet]"
    }]
  }], () => [], null);
})();
var HeaderRowOutlet = class _HeaderRowOutlet {
  viewContainer = inject(ViewContainerRef);
  elementRef = inject(ElementRef);
  constructor() {
    const table = inject(CDK_TABLE);
    table._headerRowOutlet = this;
    table._outletAssigned();
  }
  static \u0275fac = function HeaderRowOutlet_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _HeaderRowOutlet)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _HeaderRowOutlet,
    selectors: [["", "headerRowOutlet", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(HeaderRowOutlet, [{
    type: Directive,
    args: [{
      selector: "[headerRowOutlet]"
    }]
  }], () => [], null);
})();
var FooterRowOutlet = class _FooterRowOutlet {
  viewContainer = inject(ViewContainerRef);
  elementRef = inject(ElementRef);
  constructor() {
    const table = inject(CDK_TABLE);
    table._footerRowOutlet = this;
    table._outletAssigned();
  }
  static \u0275fac = function FooterRowOutlet_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _FooterRowOutlet)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _FooterRowOutlet,
    selectors: [["", "footerRowOutlet", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(FooterRowOutlet, [{
    type: Directive,
    args: [{
      selector: "[footerRowOutlet]"
    }]
  }], () => [], null);
})();
var NoDataRowOutlet = class _NoDataRowOutlet {
  viewContainer = inject(ViewContainerRef);
  elementRef = inject(ElementRef);
  constructor() {
    const table = inject(CDK_TABLE);
    table._noDataRowOutlet = this;
    table._outletAssigned();
  }
  static \u0275fac = function NoDataRowOutlet_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _NoDataRowOutlet)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _NoDataRowOutlet,
    selectors: [["", "noDataRowOutlet", ""]]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(NoDataRowOutlet, [{
    type: Directive,
    args: [{
      selector: "[noDataRowOutlet]"
    }]
  }], () => [], null);
})();
var CdkTable = class _CdkTable {
  _differs = inject(IterableDiffers);
  _changeDetectorRef = inject(ChangeDetectorRef);
  _elementRef = inject(ElementRef);
  _dir = inject(Directionality, {
    optional: true
  });
  _platform = inject(Platform);
  _viewRepeater = inject(_VIEW_REPEATER_STRATEGY);
  _viewportRuler = inject(ViewportRuler);
  _stickyPositioningListener = inject(STICKY_POSITIONING_LISTENER, {
    optional: true,
    skipSelf: true
  });
  _document = inject(DOCUMENT);
  /** Latest data provided by the data source. */
  _data;
  /** Subject that emits when the component has been destroyed. */
  _onDestroy = new Subject();
  /** List of the rendered rows as identified by their `RenderRow` object. */
  _renderRows;
  /** Subscription that listens for the data provided by the data source. */
  _renderChangeSubscription;
  /**
   * Map of all the user's defined columns (header, data, and footer cell template) identified by
   * name. Collection populated by the column definitions gathered by `ContentChildren` as well as
   * any custom column definitions added to `_customColumnDefs`.
   */
  _columnDefsByName = /* @__PURE__ */ new Map();
  /**
   * Set of all row definitions that can be used by this table. Populated by the rows gathered by
   * using `ContentChildren` as well as any custom row definitions added to `_customRowDefs`.
   */
  _rowDefs;
  /**
   * Set of all header row definitions that can be used by this table. Populated by the rows
   * gathered by using `ContentChildren` as well as any custom row definitions added to
   * `_customHeaderRowDefs`.
   */
  _headerRowDefs;
  /**
   * Set of all row definitions that can be used by this table. Populated by the rows gathered by
   * using `ContentChildren` as well as any custom row definitions added to
   * `_customFooterRowDefs`.
   */
  _footerRowDefs;
  /** Differ used to find the changes in the data provided by the data source. */
  _dataDiffer;
  /** Stores the row definition that does not have a when predicate. */
  _defaultRowDef;
  /**
   * Column definitions that were defined outside of the direct content children of the table.
   * These will be defined when, e.g., creating a wrapper around the cdkTable that has
   * column definitions as *its* content child.
   */
  _customColumnDefs = /* @__PURE__ */ new Set();
  /**
   * Data row definitions that were defined outside of the direct content children of the table.
   * These will be defined when, e.g., creating a wrapper around the cdkTable that has
   * built-in data rows as *its* content child.
   */
  _customRowDefs = /* @__PURE__ */ new Set();
  /**
   * Header row definitions that were defined outside of the direct content children of the table.
   * These will be defined when, e.g., creating a wrapper around the cdkTable that has
   * built-in header rows as *its* content child.
   */
  _customHeaderRowDefs = /* @__PURE__ */ new Set();
  /**
   * Footer row definitions that were defined outside of the direct content children of the table.
   * These will be defined when, e.g., creating a wrapper around the cdkTable that has a
   * built-in footer row as *its* content child.
   */
  _customFooterRowDefs = /* @__PURE__ */ new Set();
  /** No data row that was defined outside of the direct content children of the table. */
  _customNoDataRow;
  /**
   * Whether the header row definition has been changed. Triggers an update to the header row after
   * content is checked. Initialized as true so that the table renders the initial set of rows.
   */
  _headerRowDefChanged = true;
  /**
   * Whether the footer row definition has been changed. Triggers an update to the footer row after
   * content is checked. Initialized as true so that the table renders the initial set of rows.
   */
  _footerRowDefChanged = true;
  /**
   * Whether the sticky column styles need to be updated. Set to `true` when the visible columns
   * change.
   */
  _stickyColumnStylesNeedReset = true;
  /**
   * Whether the sticky styler should recalculate cell widths when applying sticky styles. If
   * `false`, cached values will be used instead. This is only applicable to tables with
   * `_fixedLayout` enabled. For other tables, cell widths will always be recalculated.
   */
  _forceRecalculateCellWidths = true;
  /**
   * Cache of the latest rendered `RenderRow` objects as a map for easy retrieval when constructing
   * a new list of `RenderRow` objects for rendering rows. Since the new list is constructed with
   * the cached `RenderRow` objects when possible, the row identity is preserved when the data
   * and row template matches, which allows the `IterableDiffer` to check rows by reference
   * and understand which rows are added/moved/removed.
   *
   * Implemented as a map of maps where the first key is the `data: T` object and the second is the
   * `CdkRowDef<T>` object. With the two keys, the cache points to a `RenderRow<T>` object that
   * contains an array of created pairs. The array is necessary to handle cases where the data
   * array contains multiple duplicate data objects and each instantiated `RenderRow` must be
   * stored.
   */
  _cachedRenderRowsMap = /* @__PURE__ */ new Map();
  /** Whether the table is applied to a native `<table>`. */
  _isNativeHtmlTable;
  /**
   * Utility class that is responsible for applying the appropriate sticky positioning styles to
   * the table's rows and cells.
   */
  _stickyStyler;
  /**
   * CSS class added to any row or cell that has sticky positioning applied. May be overridden by
   * table subclasses.
   */
  stickyCssClass = "cdk-table-sticky";
  /**
   * Whether to manually add position: sticky to all sticky cell elements. Not needed if
   * the position is set in a selector associated with the value of stickyCssClass. May be
   * overridden by table subclasses
   */
  needsPositionStickyOnElement = true;
  /** Whether the component is being rendered on the server. */
  _isServer;
  /** Whether the no data row is currently showing anything. */
  _isShowingNoDataRow = false;
  /** Whether the table has rendered out all the outlets for the first time. */
  _hasAllOutlets = false;
  /** Whether the table is done initializing. */
  _hasInitialized = false;
  /** Aria role to apply to the table's cells based on the table's own role. */
  _getCellRole() {
    if (this._cellRoleInternal === void 0) {
      const tableRole = this._elementRef.nativeElement.getAttribute("role");
      return tableRole === "grid" || tableRole === "treegrid" ? "gridcell" : "cell";
    }
    return this._cellRoleInternal;
  }
  _cellRoleInternal = void 0;
  /**
   * Tracking function that will be used to check the differences in data changes. Used similarly
   * to `ngFor` `trackBy` function. Optimize row operations by identifying a row based on its data
   * relative to the function to know if a row should be added/removed/moved.
   * Accepts a function that takes two parameters, `index` and `item`.
   */
  get trackBy() {
    return this._trackByFn;
  }
  set trackBy(fn) {
    if ((typeof ngDevMode === "undefined" || ngDevMode) && fn != null && typeof fn !== "function") {
      console.warn(`trackBy must be a function, but received ${JSON.stringify(fn)}.`);
    }
    this._trackByFn = fn;
  }
  _trackByFn;
  /**
   * The table's source of data, which can be provided in three ways (in order of complexity):
   *   - Simple data array (each object represents one table row)
   *   - Stream that emits a data array each time the array changes
   *   - `DataSource` object that implements the connect/disconnect interface.
   *
   * If a data array is provided, the table must be notified when the array's objects are
   * added, removed, or moved. This can be done by calling the `renderRows()` function which will
   * render the diff since the last table render. If the data array reference is changed, the table
   * will automatically trigger an update to the rows.
   *
   * When providing an Observable stream, the table will trigger an update automatically when the
   * stream emits a new array of data.
   *
   * Finally, when providing a `DataSource` object, the table will use the Observable stream
   * provided by the connect function and trigger updates when that stream emits new data array
   * values. During the table's ngOnDestroy or when the data source is removed from the table, the
   * table will call the DataSource's `disconnect` function (may be useful for cleaning up any
   * subscriptions registered during the connect process).
   */
  get dataSource() {
    return this._dataSource;
  }
  set dataSource(dataSource) {
    if (this._dataSource !== dataSource) {
      this._switchDataSource(dataSource);
    }
  }
  _dataSource;
  /**
   * Whether to allow multiple rows per data object by evaluating which rows evaluate their 'when'
   * predicate to true. If `multiTemplateDataRows` is false, which is the default value, then each
   * dataobject will render the first row that evaluates its when predicate to true, in the order
   * defined in the table, or otherwise the default row which does not have a when predicate.
   */
  get multiTemplateDataRows() {
    return this._multiTemplateDataRows;
  }
  set multiTemplateDataRows(value) {
    this._multiTemplateDataRows = value;
    if (this._rowOutlet && this._rowOutlet.viewContainer.length) {
      this._forceRenderDataRows();
      this.updateStickyColumnStyles();
    }
  }
  _multiTemplateDataRows = false;
  /**
   * Whether to use a fixed table layout. Enabling this option will enforce consistent column widths
   * and optimize rendering sticky styles for native tables. No-op for flex tables.
   */
  get fixedLayout() {
    return this._fixedLayout;
  }
  set fixedLayout(value) {
    this._fixedLayout = value;
    this._forceRecalculateCellWidths = true;
    this._stickyColumnStylesNeedReset = true;
  }
  _fixedLayout = false;
  /**
   * Emits when the table completes rendering a set of data rows based on the latest data from the
   * data source, even if the set of rows is empty.
   */
  contentChanged = new EventEmitter();
  // TODO(andrewseguin): Remove max value as the end index
  //   and instead calculate the view on init and scroll.
  /**
   * Stream containing the latest information on what rows are being displayed on screen.
   * Can be used by the data source to as a heuristic of what data should be provided.
   *
   * @docs-private
   */
  viewChange = new BehaviorSubject({
    start: 0,
    end: Number.MAX_VALUE
  });
  // Outlets in the table's template where the header, data rows, and footer will be inserted.
  _rowOutlet;
  _headerRowOutlet;
  _footerRowOutlet;
  _noDataRowOutlet;
  /**
   * The column definitions provided by the user that contain what the header, data, and footer
   * cells should render for each column.
   */
  _contentColumnDefs;
  /** Set of data row definitions that were provided to the table as content children. */
  _contentRowDefs;
  /** Set of header row definitions that were provided to the table as content children. */
  _contentHeaderRowDefs;
  /** Set of footer row definitions that were provided to the table as content children. */
  _contentFooterRowDefs;
  /** Row definition that will only be rendered if there's no data in the table. */
  _noDataRow;
  _injector = inject(Injector);
  constructor() {
    const role = inject(new HostAttributeToken("role"), {
      optional: true
    });
    if (!role) {
      this._elementRef.nativeElement.setAttribute("role", "table");
    }
    this._isServer = !this._platform.isBrowser;
    this._isNativeHtmlTable = this._elementRef.nativeElement.nodeName === "TABLE";
    this._dataDiffer = this._differs.find([]).create((_i, dataRow) => {
      return this.trackBy ? this.trackBy(dataRow.dataIndex, dataRow.data) : dataRow;
    });
  }
  ngOnInit() {
    this._setupStickyStyler();
    this._viewportRuler.change().pipe(takeUntil(this._onDestroy)).subscribe(() => {
      this._forceRecalculateCellWidths = true;
    });
  }
  ngAfterContentInit() {
    this._hasInitialized = true;
  }
  ngAfterContentChecked() {
    if (this._canRender()) {
      this._render();
    }
  }
  ngOnDestroy() {
    this._stickyStyler?.destroy();
    [this._rowOutlet?.viewContainer, this._headerRowOutlet?.viewContainer, this._footerRowOutlet?.viewContainer, this._cachedRenderRowsMap, this._customColumnDefs, this._customRowDefs, this._customHeaderRowDefs, this._customFooterRowDefs, this._columnDefsByName].forEach((def) => {
      def?.clear();
    });
    this._headerRowDefs = [];
    this._footerRowDefs = [];
    this._defaultRowDef = null;
    this._onDestroy.next();
    this._onDestroy.complete();
    if (isDataSource(this.dataSource)) {
      this.dataSource.disconnect(this);
    }
  }
  /**
   * Renders rows based on the table's latest set of data, which was either provided directly as an
   * input or retrieved through an Observable stream (directly or from a DataSource).
   * Checks for differences in the data since the last diff to perform only the necessary
   * changes (add/remove/move rows).
   *
   * If the table's data source is a DataSource or Observable, this will be invoked automatically
   * each time the provided Observable stream emits a new data array. Otherwise if your data is
   * an array, this function will need to be called to render any changes.
   */
  renderRows() {
    this._renderRows = this._getAllRenderRows();
    const changes = this._dataDiffer.diff(this._renderRows);
    if (!changes) {
      this._updateNoDataRow();
      this.contentChanged.next();
      return;
    }
    const viewContainer = this._rowOutlet.viewContainer;
    this._viewRepeater.applyChanges(changes, viewContainer, (record, _adjustedPreviousIndex, currentIndex) => this._getEmbeddedViewArgs(record.item, currentIndex), (record) => record.item.data, (change) => {
      if (change.operation === _ViewRepeaterOperation.INSERTED && change.context) {
        this._renderCellTemplateForItem(change.record.item.rowDef, change.context);
      }
    });
    this._updateRowIndexContext();
    changes.forEachIdentityChange((record) => {
      const rowView = viewContainer.get(record.currentIndex);
      rowView.context.$implicit = record.item.data;
    });
    this._updateNoDataRow();
    this.contentChanged.next();
    this.updateStickyColumnStyles();
  }
  /** Adds a column definition that was not included as part of the content children. */
  addColumnDef(columnDef) {
    this._customColumnDefs.add(columnDef);
  }
  /** Removes a column definition that was not included as part of the content children. */
  removeColumnDef(columnDef) {
    this._customColumnDefs.delete(columnDef);
  }
  /** Adds a row definition that was not included as part of the content children. */
  addRowDef(rowDef) {
    this._customRowDefs.add(rowDef);
  }
  /** Removes a row definition that was not included as part of the content children. */
  removeRowDef(rowDef) {
    this._customRowDefs.delete(rowDef);
  }
  /** Adds a header row definition that was not included as part of the content children. */
  addHeaderRowDef(headerRowDef) {
    this._customHeaderRowDefs.add(headerRowDef);
    this._headerRowDefChanged = true;
  }
  /** Removes a header row definition that was not included as part of the content children. */
  removeHeaderRowDef(headerRowDef) {
    this._customHeaderRowDefs.delete(headerRowDef);
    this._headerRowDefChanged = true;
  }
  /** Adds a footer row definition that was not included as part of the content children. */
  addFooterRowDef(footerRowDef) {
    this._customFooterRowDefs.add(footerRowDef);
    this._footerRowDefChanged = true;
  }
  /** Removes a footer row definition that was not included as part of the content children. */
  removeFooterRowDef(footerRowDef) {
    this._customFooterRowDefs.delete(footerRowDef);
    this._footerRowDefChanged = true;
  }
  /** Sets a no data row definition that was not included as a part of the content children. */
  setNoDataRow(noDataRow) {
    this._customNoDataRow = noDataRow;
  }
  /**
   * Updates the header sticky styles. First resets all applied styles with respect to the cells
   * sticking to the top. Then, evaluating which cells need to be stuck to the top. This is
   * automatically called when the header row changes its displayed set of columns, or if its
   * sticky input changes. May be called manually for cases where the cell content changes outside
   * of these events.
   */
  updateStickyHeaderRowStyles() {
    const headerRows = this._getRenderedRows(this._headerRowOutlet);
    if (this._isNativeHtmlTable) {
      const thead = closestTableSection(this._headerRowOutlet, "thead");
      if (thead) {
        thead.style.display = headerRows.length ? "" : "none";
      }
    }
    const stickyStates = this._headerRowDefs.map((def) => def.sticky);
    this._stickyStyler.clearStickyPositioning(headerRows, ["top"]);
    this._stickyStyler.stickRows(headerRows, stickyStates, "top");
    this._headerRowDefs.forEach((def) => def.resetStickyChanged());
  }
  /**
   * Updates the footer sticky styles. First resets all applied styles with respect to the cells
   * sticking to the bottom. Then, evaluating which cells need to be stuck to the bottom. This is
   * automatically called when the footer row changes its displayed set of columns, or if its
   * sticky input changes. May be called manually for cases where the cell content changes outside
   * of these events.
   */
  updateStickyFooterRowStyles() {
    const footerRows = this._getRenderedRows(this._footerRowOutlet);
    if (this._isNativeHtmlTable) {
      const tfoot = closestTableSection(this._footerRowOutlet, "tfoot");
      if (tfoot) {
        tfoot.style.display = footerRows.length ? "" : "none";
      }
    }
    const stickyStates = this._footerRowDefs.map((def) => def.sticky);
    this._stickyStyler.clearStickyPositioning(footerRows, ["bottom"]);
    this._stickyStyler.stickRows(footerRows, stickyStates, "bottom");
    this._stickyStyler.updateStickyFooterContainer(this._elementRef.nativeElement, stickyStates);
    this._footerRowDefs.forEach((def) => def.resetStickyChanged());
  }
  /**
   * Updates the column sticky styles. First resets all applied styles with respect to the cells
   * sticking to the left and right. Then sticky styles are added for the left and right according
   * to the column definitions for each cell in each row. This is automatically called when
   * the data source provides a new set of data or when a column definition changes its sticky
   * input. May be called manually for cases where the cell content changes outside of these events.
   */
  updateStickyColumnStyles() {
    const headerRows = this._getRenderedRows(this._headerRowOutlet);
    const dataRows = this._getRenderedRows(this._rowOutlet);
    const footerRows = this._getRenderedRows(this._footerRowOutlet);
    if (this._isNativeHtmlTable && !this._fixedLayout || this._stickyColumnStylesNeedReset) {
      this._stickyStyler.clearStickyPositioning([...headerRows, ...dataRows, ...footerRows], ["left", "right"]);
      this._stickyColumnStylesNeedReset = false;
    }
    headerRows.forEach((headerRow, i) => {
      this._addStickyColumnStyles([headerRow], this._headerRowDefs[i]);
    });
    this._rowDefs.forEach((rowDef) => {
      const rows = [];
      for (let i = 0; i < dataRows.length; i++) {
        if (this._renderRows[i].rowDef === rowDef) {
          rows.push(dataRows[i]);
        }
      }
      this._addStickyColumnStyles(rows, rowDef);
    });
    footerRows.forEach((footerRow, i) => {
      this._addStickyColumnStyles([footerRow], this._footerRowDefs[i]);
    });
    Array.from(this._columnDefsByName.values()).forEach((def) => def.resetStickyChanged());
  }
  /** Invoked whenever an outlet is created and has been assigned to the table. */
  _outletAssigned() {
    if (!this._hasAllOutlets && this._rowOutlet && this._headerRowOutlet && this._footerRowOutlet && this._noDataRowOutlet) {
      this._hasAllOutlets = true;
      if (this._canRender()) {
        this._render();
      }
    }
  }
  /** Whether the table has all the information to start rendering. */
  _canRender() {
    return this._hasAllOutlets && this._hasInitialized;
  }
  /** Renders the table if its state has changed. */
  _render() {
    this._cacheRowDefs();
    this._cacheColumnDefs();
    if (!this._headerRowDefs.length && !this._footerRowDefs.length && !this._rowDefs.length && (typeof ngDevMode === "undefined" || ngDevMode)) {
      throw getTableMissingRowDefsError();
    }
    const columnsChanged = this._renderUpdatedColumns();
    const rowDefsChanged = columnsChanged || this._headerRowDefChanged || this._footerRowDefChanged;
    this._stickyColumnStylesNeedReset = this._stickyColumnStylesNeedReset || rowDefsChanged;
    this._forceRecalculateCellWidths = rowDefsChanged;
    if (this._headerRowDefChanged) {
      this._forceRenderHeaderRows();
      this._headerRowDefChanged = false;
    }
    if (this._footerRowDefChanged) {
      this._forceRenderFooterRows();
      this._footerRowDefChanged = false;
    }
    if (this.dataSource && this._rowDefs.length > 0 && !this._renderChangeSubscription) {
      this._observeRenderChanges();
    } else if (this._stickyColumnStylesNeedReset) {
      this.updateStickyColumnStyles();
    }
    this._checkStickyStates();
  }
  /**
   * Get the list of RenderRow objects to render according to the current list of data and defined
   * row definitions. If the previous list already contained a particular pair, it should be reused
   * so that the differ equates their references.
   */
  _getAllRenderRows() {
    const renderRows = [];
    const prevCachedRenderRows = this._cachedRenderRowsMap;
    this._cachedRenderRowsMap = /* @__PURE__ */ new Map();
    if (!this._data) {
      return renderRows;
    }
    for (let i = 0; i < this._data.length; i++) {
      let data = this._data[i];
      const renderRowsForData = this._getRenderRowsForData(data, i, prevCachedRenderRows.get(data));
      if (!this._cachedRenderRowsMap.has(data)) {
        this._cachedRenderRowsMap.set(data, /* @__PURE__ */ new WeakMap());
      }
      for (let j = 0; j < renderRowsForData.length; j++) {
        let renderRow = renderRowsForData[j];
        const cache = this._cachedRenderRowsMap.get(renderRow.data);
        if (cache.has(renderRow.rowDef)) {
          cache.get(renderRow.rowDef).push(renderRow);
        } else {
          cache.set(renderRow.rowDef, [renderRow]);
        }
        renderRows.push(renderRow);
      }
    }
    return renderRows;
  }
  /**
   * Gets a list of `RenderRow<T>` for the provided data object and any `CdkRowDef` objects that
   * should be rendered for this data. Reuses the cached RenderRow objects if they match the same
   * `(T, CdkRowDef)` pair.
   */
  _getRenderRowsForData(data, dataIndex, cache) {
    const rowDefs = this._getRowDefs(data, dataIndex);
    return rowDefs.map((rowDef) => {
      const cachedRenderRows = cache && cache.has(rowDef) ? cache.get(rowDef) : [];
      if (cachedRenderRows.length) {
        const dataRow = cachedRenderRows.shift();
        dataRow.dataIndex = dataIndex;
        return dataRow;
      } else {
        return {
          data,
          rowDef,
          dataIndex
        };
      }
    });
  }
  /** Update the map containing the content's column definitions. */
  _cacheColumnDefs() {
    this._columnDefsByName.clear();
    const columnDefs = mergeArrayAndSet(this._getOwnDefs(this._contentColumnDefs), this._customColumnDefs);
    columnDefs.forEach((columnDef) => {
      if (this._columnDefsByName.has(columnDef.name) && (typeof ngDevMode === "undefined" || ngDevMode)) {
        throw getTableDuplicateColumnNameError(columnDef.name);
      }
      this._columnDefsByName.set(columnDef.name, columnDef);
    });
  }
  /** Update the list of all available row definitions that can be used. */
  _cacheRowDefs() {
    this._headerRowDefs = mergeArrayAndSet(this._getOwnDefs(this._contentHeaderRowDefs), this._customHeaderRowDefs);
    this._footerRowDefs = mergeArrayAndSet(this._getOwnDefs(this._contentFooterRowDefs), this._customFooterRowDefs);
    this._rowDefs = mergeArrayAndSet(this._getOwnDefs(this._contentRowDefs), this._customRowDefs);
    const defaultRowDefs = this._rowDefs.filter((def) => !def.when);
    if (!this.multiTemplateDataRows && defaultRowDefs.length > 1 && (typeof ngDevMode === "undefined" || ngDevMode)) {
      throw getTableMultipleDefaultRowDefsError();
    }
    this._defaultRowDef = defaultRowDefs[0];
  }
  /**
   * Check if the header, data, or footer rows have changed what columns they want to display or
   * whether the sticky states have changed for the header or footer. If there is a diff, then
   * re-render that section.
   */
  _renderUpdatedColumns() {
    const columnsDiffReducer = (acc, def) => {
      const diff = !!def.getColumnsDiff();
      return acc || diff;
    };
    const dataColumnsChanged = this._rowDefs.reduce(columnsDiffReducer, false);
    if (dataColumnsChanged) {
      this._forceRenderDataRows();
    }
    const headerColumnsChanged = this._headerRowDefs.reduce(columnsDiffReducer, false);
    if (headerColumnsChanged) {
      this._forceRenderHeaderRows();
    }
    const footerColumnsChanged = this._footerRowDefs.reduce(columnsDiffReducer, false);
    if (footerColumnsChanged) {
      this._forceRenderFooterRows();
    }
    return dataColumnsChanged || headerColumnsChanged || footerColumnsChanged;
  }
  /**
   * Switch to the provided data source by resetting the data and unsubscribing from the current
   * render change subscription if one exists. If the data source is null, interpret this by
   * clearing the row outlet. Otherwise start listening for new data.
   */
  _switchDataSource(dataSource) {
    this._data = [];
    if (isDataSource(this.dataSource)) {
      this.dataSource.disconnect(this);
    }
    if (this._renderChangeSubscription) {
      this._renderChangeSubscription.unsubscribe();
      this._renderChangeSubscription = null;
    }
    if (!dataSource) {
      if (this._dataDiffer) {
        this._dataDiffer.diff([]);
      }
      if (this._rowOutlet) {
        this._rowOutlet.viewContainer.clear();
      }
    }
    this._dataSource = dataSource;
  }
  /** Set up a subscription for the data provided by the data source. */
  _observeRenderChanges() {
    if (!this.dataSource) {
      return;
    }
    let dataStream;
    if (isDataSource(this.dataSource)) {
      dataStream = this.dataSource.connect(this);
    } else if (isObservable(this.dataSource)) {
      dataStream = this.dataSource;
    } else if (Array.isArray(this.dataSource)) {
      dataStream = of(this.dataSource);
    }
    if (dataStream === void 0 && (typeof ngDevMode === "undefined" || ngDevMode)) {
      throw getTableUnknownDataSourceError();
    }
    this._renderChangeSubscription = dataStream.pipe(takeUntil(this._onDestroy)).subscribe((data) => {
      this._data = data || [];
      this.renderRows();
    });
  }
  /**
   * Clears any existing content in the header row outlet and creates a new embedded view
   * in the outlet using the header row definition.
   */
  _forceRenderHeaderRows() {
    if (this._headerRowOutlet.viewContainer.length > 0) {
      this._headerRowOutlet.viewContainer.clear();
    }
    this._headerRowDefs.forEach((def, i) => this._renderRow(this._headerRowOutlet, def, i));
    this.updateStickyHeaderRowStyles();
  }
  /**
   * Clears any existing content in the footer row outlet and creates a new embedded view
   * in the outlet using the footer row definition.
   */
  _forceRenderFooterRows() {
    if (this._footerRowOutlet.viewContainer.length > 0) {
      this._footerRowOutlet.viewContainer.clear();
    }
    this._footerRowDefs.forEach((def, i) => this._renderRow(this._footerRowOutlet, def, i));
    this.updateStickyFooterRowStyles();
  }
  /** Adds the sticky column styles for the rows according to the columns' stick states. */
  _addStickyColumnStyles(rows, rowDef) {
    const columnDefs = Array.from(rowDef?.columns || []).map((columnName) => {
      const columnDef = this._columnDefsByName.get(columnName);
      if (!columnDef && (typeof ngDevMode === "undefined" || ngDevMode)) {
        throw getTableUnknownColumnError(columnName);
      }
      return columnDef;
    });
    const stickyStartStates = columnDefs.map((columnDef) => columnDef.sticky);
    const stickyEndStates = columnDefs.map((columnDef) => columnDef.stickyEnd);
    this._stickyStyler.updateStickyColumns(rows, stickyStartStates, stickyEndStates, !this._fixedLayout || this._forceRecalculateCellWidths);
  }
  /** Gets the list of rows that have been rendered in the row outlet. */
  _getRenderedRows(rowOutlet) {
    const renderedRows = [];
    for (let i = 0; i < rowOutlet.viewContainer.length; i++) {
      const viewRef = rowOutlet.viewContainer.get(i);
      renderedRows.push(viewRef.rootNodes[0]);
    }
    return renderedRows;
  }
  /**
   * Get the matching row definitions that should be used for this row data. If there is only
   * one row definition, it is returned. Otherwise, find the row definitions that has a when
   * predicate that returns true with the data. If none return true, return the default row
   * definition.
   */
  _getRowDefs(data, dataIndex) {
    if (this._rowDefs.length == 1) {
      return [this._rowDefs[0]];
    }
    let rowDefs = [];
    if (this.multiTemplateDataRows) {
      rowDefs = this._rowDefs.filter((def) => !def.when || def.when(dataIndex, data));
    } else {
      let rowDef = this._rowDefs.find((def) => def.when && def.when(dataIndex, data)) || this._defaultRowDef;
      if (rowDef) {
        rowDefs.push(rowDef);
      }
    }
    if (!rowDefs.length && (typeof ngDevMode === "undefined" || ngDevMode)) {
      throw getTableMissingMatchingRowDefError(data);
    }
    return rowDefs;
  }
  _getEmbeddedViewArgs(renderRow, index) {
    const rowDef = renderRow.rowDef;
    const context = {
      $implicit: renderRow.data
    };
    return {
      templateRef: rowDef.template,
      context,
      index
    };
  }
  /**
   * Creates a new row template in the outlet and fills it with the set of cell templates.
   * Optionally takes a context to provide to the row and cells, as well as an optional index
   * of where to place the new row template in the outlet.
   */
  _renderRow(outlet, rowDef, index, context = {}) {
    const view = outlet.viewContainer.createEmbeddedView(rowDef.template, context, index);
    this._renderCellTemplateForItem(rowDef, context);
    return view;
  }
  _renderCellTemplateForItem(rowDef, context) {
    for (let cellTemplate of this._getCellTemplates(rowDef)) {
      if (CdkCellOutlet.mostRecentCellOutlet) {
        CdkCellOutlet.mostRecentCellOutlet._viewContainer.createEmbeddedView(cellTemplate, context);
      }
    }
    this._changeDetectorRef.markForCheck();
  }
  /**
   * Updates the index-related context for each row to reflect any changes in the index of the rows,
   * e.g. first/last/even/odd.
   */
  _updateRowIndexContext() {
    const viewContainer = this._rowOutlet.viewContainer;
    for (let renderIndex = 0, count = viewContainer.length; renderIndex < count; renderIndex++) {
      const viewRef = viewContainer.get(renderIndex);
      const context = viewRef.context;
      context.count = count;
      context.first = renderIndex === 0;
      context.last = renderIndex === count - 1;
      context.even = renderIndex % 2 === 0;
      context.odd = !context.even;
      if (this.multiTemplateDataRows) {
        context.dataIndex = this._renderRows[renderIndex].dataIndex;
        context.renderIndex = renderIndex;
      } else {
        context.index = this._renderRows[renderIndex].dataIndex;
      }
    }
  }
  /** Gets the column definitions for the provided row def. */
  _getCellTemplates(rowDef) {
    if (!rowDef || !rowDef.columns) {
      return [];
    }
    return Array.from(rowDef.columns, (columnId) => {
      const column = this._columnDefsByName.get(columnId);
      if (!column && (typeof ngDevMode === "undefined" || ngDevMode)) {
        throw getTableUnknownColumnError(columnId);
      }
      return rowDef.extractCellTemplate(column);
    });
  }
  /**
   * Forces a re-render of the data rows. Should be called in cases where there has been an input
   * change that affects the evaluation of which rows should be rendered, e.g. toggling
   * `multiTemplateDataRows` or adding/removing row definitions.
   */
  _forceRenderDataRows() {
    this._dataDiffer.diff([]);
    this._rowOutlet.viewContainer.clear();
    this.renderRows();
  }
  /**
   * Checks if there has been a change in sticky states since last check and applies the correct
   * sticky styles. Since checking resets the "dirty" state, this should only be performed once
   * during a change detection and after the inputs are settled (after content check).
   */
  _checkStickyStates() {
    const stickyCheckReducer = (acc, d) => {
      return acc || d.hasStickyChanged();
    };
    if (this._headerRowDefs.reduce(stickyCheckReducer, false)) {
      this.updateStickyHeaderRowStyles();
    }
    if (this._footerRowDefs.reduce(stickyCheckReducer, false)) {
      this.updateStickyFooterRowStyles();
    }
    if (Array.from(this._columnDefsByName.values()).reduce(stickyCheckReducer, false)) {
      this._stickyColumnStylesNeedReset = true;
      this.updateStickyColumnStyles();
    }
  }
  /**
   * Creates the sticky styler that will be used for sticky rows and columns. Listens
   * for directionality changes and provides the latest direction to the styler. Re-applies column
   * stickiness when directionality changes.
   */
  _setupStickyStyler() {
    const direction = this._dir ? this._dir.value : "ltr";
    this._stickyStyler = new StickyStyler(this._isNativeHtmlTable, this.stickyCssClass, this._platform.isBrowser, this.needsPositionStickyOnElement, direction, this._stickyPositioningListener, this._injector);
    (this._dir ? this._dir.change : of()).pipe(takeUntil(this._onDestroy)).subscribe((value) => {
      this._stickyStyler.direction = value;
      this.updateStickyColumnStyles();
    });
  }
  /** Filters definitions that belong to this table from a QueryList. */
  _getOwnDefs(items) {
    return items.filter((item) => !item._table || item._table === this);
  }
  /** Creates or removes the no data row, depending on whether any data is being shown. */
  _updateNoDataRow() {
    const noDataRow = this._customNoDataRow || this._noDataRow;
    if (!noDataRow) {
      return;
    }
    const shouldShow = this._rowOutlet.viewContainer.length === 0;
    if (shouldShow === this._isShowingNoDataRow) {
      return;
    }
    const container = this._noDataRowOutlet.viewContainer;
    if (shouldShow) {
      const view = container.createEmbeddedView(noDataRow.templateRef);
      const rootNode = view.rootNodes[0];
      if (view.rootNodes.length === 1 && rootNode?.nodeType === this._document.ELEMENT_NODE) {
        rootNode.setAttribute("role", "row");
        rootNode.classList.add(...noDataRow._contentClassNames);
        const cells = rootNode.querySelectorAll(noDataRow._cellSelector);
        for (let i = 0; i < cells.length; i++) {
          cells[i].classList.add(...noDataRow._cellClassNames);
        }
      }
    } else {
      container.clear();
    }
    this._isShowingNoDataRow = shouldShow;
    this._changeDetectorRef.markForCheck();
  }
  static \u0275fac = function CdkTable_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkTable)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _CdkTable,
    selectors: [["cdk-table"], ["table", "cdk-table", ""]],
    contentQueries: function CdkTable_ContentQueries(rf, ctx, dirIndex) {
      if (rf & 1) {
        \u0275\u0275contentQuery(dirIndex, CdkNoDataRow, 5);
        \u0275\u0275contentQuery(dirIndex, CdkColumnDef, 5);
        \u0275\u0275contentQuery(dirIndex, CdkRowDef, 5);
        \u0275\u0275contentQuery(dirIndex, CdkHeaderRowDef, 5);
        \u0275\u0275contentQuery(dirIndex, CdkFooterRowDef, 5);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._noDataRow = _t.first);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._contentColumnDefs = _t);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._contentRowDefs = _t);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._contentHeaderRowDefs = _t);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._contentFooterRowDefs = _t);
      }
    },
    hostAttrs: [1, "cdk-table"],
    hostVars: 2,
    hostBindings: function CdkTable_HostBindings(rf, ctx) {
      if (rf & 2) {
        \u0275\u0275classProp("cdk-table-fixed-layout", ctx.fixedLayout);
      }
    },
    inputs: {
      trackBy: "trackBy",
      dataSource: "dataSource",
      multiTemplateDataRows: [2, "multiTemplateDataRows", "multiTemplateDataRows", booleanAttribute],
      fixedLayout: [2, "fixedLayout", "fixedLayout", booleanAttribute]
    },
    outputs: {
      contentChanged: "contentChanged"
    },
    exportAs: ["cdkTable"],
    features: [\u0275\u0275ProvidersFeature([
      {
        provide: CDK_TABLE,
        useExisting: _CdkTable
      },
      {
        provide: _VIEW_REPEATER_STRATEGY,
        useClass: _DisposeViewRepeaterStrategy
      },
      // Prevent nested tables from seeing this table's StickyPositioningListener.
      {
        provide: STICKY_POSITIONING_LISTENER,
        useValue: null
      }
    ])],
    ngContentSelectors: _c1,
    decls: 5,
    vars: 2,
    consts: [["role", "rowgroup"], ["headerRowOutlet", ""], ["rowOutlet", ""], ["noDataRowOutlet", ""], ["footerRowOutlet", ""]],
    template: function CdkTable_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275projectionDef(_c0);
        \u0275\u0275projection(0);
        \u0275\u0275projection(1, 1);
        \u0275\u0275conditionalCreate(2, CdkTable_Conditional_2_Template, 1, 0);
        \u0275\u0275conditionalCreate(3, CdkTable_Conditional_3_Template, 7, 0)(4, CdkTable_Conditional_4_Template, 4, 0);
      }
      if (rf & 2) {
        \u0275\u0275advance(2);
        \u0275\u0275conditional(ctx._isServer ? 2 : -1);
        \u0275\u0275advance();
        \u0275\u0275conditional(ctx._isNativeHtmlTable ? 3 : 4);
      }
    },
    dependencies: [HeaderRowOutlet, DataRowOutlet, NoDataRowOutlet, FooterRowOutlet],
    styles: [".cdk-table-fixed-layout{table-layout:fixed}\n"],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkTable, [{
    type: Component,
    args: [{
      selector: "cdk-table, table[cdk-table]",
      exportAs: "cdkTable",
      template: `
    <ng-content select="caption"/>
    <ng-content select="colgroup, col"/>

    <!--
      Unprojected content throws a hydration error so we need this to capture it.
      It gets removed on the client so it doesn't affect the layout.
    -->
    @if (_isServer) {
      <ng-content/>
    }

    @if (_isNativeHtmlTable) {
      <thead role="rowgroup">
        <ng-container headerRowOutlet/>
      </thead>
      <tbody role="rowgroup">
        <ng-container rowOutlet/>
        <ng-container noDataRowOutlet/>
      </tbody>
      <tfoot role="rowgroup">
        <ng-container footerRowOutlet/>
      </tfoot>
    } @else {
      <ng-container headerRowOutlet/>
      <ng-container rowOutlet/>
      <ng-container noDataRowOutlet/>
      <ng-container footerRowOutlet/>
    }
  `,
      host: {
        "class": "cdk-table",
        "[class.cdk-table-fixed-layout]": "fixedLayout"
      },
      encapsulation: ViewEncapsulation.None,
      changeDetection: ChangeDetectionStrategy.Default,
      providers: [
        {
          provide: CDK_TABLE,
          useExisting: CdkTable
        },
        {
          provide: _VIEW_REPEATER_STRATEGY,
          useClass: _DisposeViewRepeaterStrategy
        },
        // Prevent nested tables from seeing this table's StickyPositioningListener.
        {
          provide: STICKY_POSITIONING_LISTENER,
          useValue: null
        }
      ],
      imports: [HeaderRowOutlet, DataRowOutlet, NoDataRowOutlet, FooterRowOutlet],
      styles: [".cdk-table-fixed-layout{table-layout:fixed}\n"]
    }]
  }], () => [], {
    trackBy: [{
      type: Input
    }],
    dataSource: [{
      type: Input
    }],
    multiTemplateDataRows: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    fixedLayout: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    contentChanged: [{
      type: Output
    }],
    _contentColumnDefs: [{
      type: ContentChildren,
      args: [CdkColumnDef, {
        descendants: true
      }]
    }],
    _contentRowDefs: [{
      type: ContentChildren,
      args: [CdkRowDef, {
        descendants: true
      }]
    }],
    _contentHeaderRowDefs: [{
      type: ContentChildren,
      args: [CdkHeaderRowDef, {
        descendants: true
      }]
    }],
    _contentFooterRowDefs: [{
      type: ContentChildren,
      args: [CdkFooterRowDef, {
        descendants: true
      }]
    }],
    _noDataRow: [{
      type: ContentChild,
      args: [CdkNoDataRow]
    }]
  });
})();
function mergeArrayAndSet(array, set) {
  return array.concat(Array.from(set));
}
function closestTableSection(outlet, section) {
  const uppercaseSection = section.toUpperCase();
  let current = outlet.viewContainer.element.nativeElement;
  while (current) {
    const nodeName = current.nodeType === 1 ? current.nodeName : null;
    if (nodeName === uppercaseSection) {
      return current;
    } else if (nodeName === "TABLE") {
      break;
    }
    current = current.parentNode;
  }
  return null;
}
var CdkTextColumn = class _CdkTextColumn {
  _table = inject(CdkTable, {
    optional: true
  });
  _options = inject(TEXT_COLUMN_OPTIONS, {
    optional: true
  });
  /** Column name that should be used to reference this column. */
  get name() {
    return this._name;
  }
  set name(name) {
    this._name = name;
    this._syncColumnDefName();
  }
  _name;
  /**
   * Text label that should be used for the column header. If this property is not
   * set, the header text will default to the column name with its first letter capitalized.
   */
  headerText;
  /**
   * Accessor function to retrieve the data rendered for each cell. If this
   * property is not set, the data cells will render the value found in the data's property matching
   * the column's name. For example, if the column is named `id`, then the rendered value will be
   * value defined by the data's `id` property.
   */
  dataAccessor;
  /** Alignment of the cell values. */
  justify = "start";
  /** @docs-private */
  columnDef;
  /**
   * The column cell is provided to the column during `ngOnInit` with a static query.
   * Normally, this will be retrieved by the column using `ContentChild`, but that assumes the
   * column definition was provided in the same view as the table, which is not the case with this
   * component.
   * @docs-private
   */
  cell;
  /**
   * The column headerCell is provided to the column during `ngOnInit` with a static query.
   * Normally, this will be retrieved by the column using `ContentChild`, but that assumes the
   * column definition was provided in the same view as the table, which is not the case with this
   * component.
   * @docs-private
   */
  headerCell;
  constructor() {
    this._options = this._options || {};
  }
  ngOnInit() {
    this._syncColumnDefName();
    if (this.headerText === void 0) {
      this.headerText = this._createDefaultHeaderText();
    }
    if (!this.dataAccessor) {
      this.dataAccessor = this._options.defaultDataAccessor || ((data, name) => data[name]);
    }
    if (this._table) {
      this.columnDef.cell = this.cell;
      this.columnDef.headerCell = this.headerCell;
      this._table.addColumnDef(this.columnDef);
    } else if (typeof ngDevMode === "undefined" || ngDevMode) {
      throw getTableTextColumnMissingParentTableError();
    }
  }
  ngOnDestroy() {
    if (this._table) {
      this._table.removeColumnDef(this.columnDef);
    }
  }
  /**
   * Creates a default header text. Use the options' header text transformation function if one
   * has been provided. Otherwise simply capitalize the column name.
   */
  _createDefaultHeaderText() {
    const name = this.name;
    if (!name && (typeof ngDevMode === "undefined" || ngDevMode)) {
      throw getTableTextColumnMissingNameError();
    }
    if (this._options && this._options.defaultHeaderTextTransform) {
      return this._options.defaultHeaderTextTransform(name);
    }
    return name[0].toUpperCase() + name.slice(1);
  }
  /** Synchronizes the column definition name with the text column name. */
  _syncColumnDefName() {
    if (this.columnDef) {
      this.columnDef.name = this.name;
    }
  }
  static \u0275fac = function CdkTextColumn_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkTextColumn)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _CdkTextColumn,
    selectors: [["cdk-text-column"]],
    viewQuery: function CdkTextColumn_Query(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275viewQuery(CdkColumnDef, 7);
        \u0275\u0275viewQuery(CdkCellDef, 7);
        \u0275\u0275viewQuery(CdkHeaderCellDef, 7);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.columnDef = _t.first);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.cell = _t.first);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.headerCell = _t.first);
      }
    },
    inputs: {
      name: "name",
      headerText: "headerText",
      dataAccessor: "dataAccessor",
      justify: "justify"
    },
    decls: 3,
    vars: 0,
    consts: [["cdkColumnDef", ""], ["cdk-header-cell", "", 3, "text-align", 4, "cdkHeaderCellDef"], ["cdk-cell", "", 3, "text-align", 4, "cdkCellDef"], ["cdk-header-cell", ""], ["cdk-cell", ""]],
    template: function CdkTextColumn_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275elementContainerStart(0, 0);
        \u0275\u0275template(1, CdkTextColumn_th_1_Template, 2, 3, "th", 1)(2, CdkTextColumn_td_2_Template, 2, 3, "td", 2);
        \u0275\u0275elementContainerEnd();
      }
    },
    dependencies: [CdkColumnDef, CdkHeaderCellDef, CdkHeaderCell, CdkCellDef, CdkCell],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkTextColumn, [{
    type: Component,
    args: [{
      selector: "cdk-text-column",
      template: `
    <ng-container cdkColumnDef>
      <th cdk-header-cell *cdkHeaderCellDef [style.text-align]="justify">
        {{headerText}}
      </th>
      <td cdk-cell *cdkCellDef="let data" [style.text-align]="justify">
        {{dataAccessor(data, name)}}
      </td>
    </ng-container>
  `,
      encapsulation: ViewEncapsulation.None,
      // Change detection is intentionally not set to OnPush. This component's template will be provided
      // to the table to be inserted into its view. This is problematic when change detection runs since
      // the bindings in this template will be evaluated _after_ the table's view is evaluated, which
      // mean's the template in the table's view will not have the updated value (and in fact will cause
      // an ExpressionChangedAfterItHasBeenCheckedError).
      // tslint:disable-next-line:validate-decorators
      changeDetection: ChangeDetectionStrategy.Default,
      imports: [CdkColumnDef, CdkHeaderCellDef, CdkHeaderCell, CdkCellDef, CdkCell]
    }]
  }], () => [], {
    name: [{
      type: Input
    }],
    headerText: [{
      type: Input
    }],
    dataAccessor: [{
      type: Input
    }],
    justify: [{
      type: Input
    }],
    columnDef: [{
      type: ViewChild,
      args: [CdkColumnDef, {
        static: true
      }]
    }],
    cell: [{
      type: ViewChild,
      args: [CdkCellDef, {
        static: true
      }]
    }],
    headerCell: [{
      type: ViewChild,
      args: [CdkHeaderCellDef, {
        static: true
      }]
    }]
  });
})();
var EXPORTED_DECLARATIONS = [CdkTable, CdkRowDef, CdkCellDef, CdkCellOutlet, CdkHeaderCellDef, CdkFooterCellDef, CdkColumnDef, CdkCell, CdkRow, CdkHeaderCell, CdkFooterCell, CdkHeaderRow, CdkHeaderRowDef, CdkFooterRow, CdkFooterRowDef, DataRowOutlet, HeaderRowOutlet, FooterRowOutlet, CdkTextColumn, CdkNoDataRow, CdkRecycleRows, NoDataRowOutlet];
var CdkTableModule = class _CdkTableModule {
  static \u0275fac = function CdkTableModule_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _CdkTableModule)();
  };
  static \u0275mod = /* @__PURE__ */ \u0275\u0275defineNgModule({
    type: _CdkTableModule,
    imports: [ScrollingModule, CdkTable, CdkRowDef, CdkCellDef, CdkCellOutlet, CdkHeaderCellDef, CdkFooterCellDef, CdkColumnDef, CdkCell, CdkRow, CdkHeaderCell, CdkFooterCell, CdkHeaderRow, CdkHeaderRowDef, CdkFooterRow, CdkFooterRowDef, DataRowOutlet, HeaderRowOutlet, FooterRowOutlet, CdkTextColumn, CdkNoDataRow, CdkRecycleRows, NoDataRowOutlet],
    exports: [CdkTable, CdkRowDef, CdkCellDef, CdkCellOutlet, CdkHeaderCellDef, CdkFooterCellDef, CdkColumnDef, CdkCell, CdkRow, CdkHeaderCell, CdkFooterCell, CdkHeaderRow, CdkHeaderRowDef, CdkFooterRow, CdkFooterRowDef, DataRowOutlet, HeaderRowOutlet, FooterRowOutlet, CdkTextColumn, CdkNoDataRow, CdkRecycleRows, NoDataRowOutlet]
  });
  static \u0275inj = /* @__PURE__ */ \u0275\u0275defineInjector({
    imports: [ScrollingModule]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(CdkTableModule, [{
    type: NgModule,
    args: [{
      exports: EXPORTED_DECLARATIONS,
      imports: [ScrollingModule, ...EXPORTED_DECLARATIONS]
    }]
  }], null, null);
})();

// node_modules/@angular/material/fesm2022/table.mjs
var _c02 = [[["caption"]], [["colgroup"], ["col"]], "*"];
var _c12 = ["caption", "colgroup, col", "*"];
function MatTable_Conditional_2_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275projection(0, 2);
  }
}
function MatTable_Conditional_3_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "thead", 0);
    \u0275\u0275elementContainer(1, 1);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(2, "tbody", 2);
    \u0275\u0275elementContainer(3, 3)(4, 4);
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(5, "tfoot", 0);
    \u0275\u0275elementContainer(6, 5);
    \u0275\u0275elementEnd();
  }
}
function MatTable_Conditional_4_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementContainer(0, 1)(1, 3)(2, 4)(3, 5);
  }
}
function MatTextColumn_th_1_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "th", 3);
    \u0275\u0275text(1);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const ctx_r0 = \u0275\u0275nextContext();
    \u0275\u0275styleProp("text-align", ctx_r0.justify);
    \u0275\u0275advance();
    \u0275\u0275textInterpolate1(" ", ctx_r0.headerText, " ");
  }
}
function MatTextColumn_td_2_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "td", 4);
    \u0275\u0275text(1);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const data_r2 = ctx.$implicit;
    const ctx_r0 = \u0275\u0275nextContext();
    \u0275\u0275styleProp("text-align", ctx_r0.justify);
    \u0275\u0275advance();
    \u0275\u0275textInterpolate1(" ", ctx_r0.dataAccessor(data_r2, ctx_r0.name), " ");
  }
}
var MatRecycleRows = class _MatRecycleRows {
  static \u0275fac = function MatRecycleRows_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatRecycleRows)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatRecycleRows,
    selectors: [["mat-table", "recycleRows", ""], ["table", "mat-table", "", "recycleRows", ""]],
    features: [\u0275\u0275ProvidersFeature([{
      provide: _VIEW_REPEATER_STRATEGY,
      useClass: _RecycleViewRepeaterStrategy
    }])]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatRecycleRows, [{
    type: Directive,
    args: [{
      selector: "mat-table[recycleRows], table[mat-table][recycleRows]",
      providers: [{
        provide: _VIEW_REPEATER_STRATEGY,
        useClass: _RecycleViewRepeaterStrategy
      }]
    }]
  }], null, null);
})();
var MatTable = class _MatTable extends CdkTable {
  /** Overrides the sticky CSS class set by the `CdkTable`. */
  stickyCssClass = "mat-mdc-table-sticky";
  /** Overrides the need to add position: sticky on every sticky cell element in `CdkTable`. */
  needsPositionStickyOnElement = false;
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatTable_BaseFactory;
    return function MatTable_Factory(__ngFactoryType__) {
      return (\u0275MatTable_BaseFactory || (\u0275MatTable_BaseFactory = \u0275\u0275getInheritedFactory(_MatTable)))(__ngFactoryType__ || _MatTable);
    };
  })();
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatTable,
    selectors: [["mat-table"], ["table", "mat-table", ""]],
    hostAttrs: [1, "mat-mdc-table", "mdc-data-table__table"],
    hostVars: 2,
    hostBindings: function MatTable_HostBindings(rf, ctx) {
      if (rf & 2) {
        \u0275\u0275classProp("mdc-table-fixed-layout", ctx.fixedLayout);
      }
    },
    exportAs: ["matTable"],
    features: [\u0275\u0275ProvidersFeature([
      {
        provide: CdkTable,
        useExisting: _MatTable
      },
      {
        provide: CDK_TABLE,
        useExisting: _MatTable
      },
      // TODO(michaeljamesparsons) Abstract the view repeater strategy to a directive API so this code
      //  is only included in the build if used.
      {
        provide: _VIEW_REPEATER_STRATEGY,
        useClass: _DisposeViewRepeaterStrategy
      },
      // Prevent nested tables from seeing this table's StickyPositioningListener.
      {
        provide: STICKY_POSITIONING_LISTENER,
        useValue: null
      }
    ]), \u0275\u0275InheritDefinitionFeature],
    ngContentSelectors: _c12,
    decls: 5,
    vars: 2,
    consts: [["role", "rowgroup"], ["headerRowOutlet", ""], ["role", "rowgroup", 1, "mdc-data-table__content"], ["rowOutlet", ""], ["noDataRowOutlet", ""], ["footerRowOutlet", ""]],
    template: function MatTable_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275projectionDef(_c02);
        \u0275\u0275projection(0);
        \u0275\u0275projection(1, 1);
        \u0275\u0275conditionalCreate(2, MatTable_Conditional_2_Template, 1, 0);
        \u0275\u0275conditionalCreate(3, MatTable_Conditional_3_Template, 7, 0)(4, MatTable_Conditional_4_Template, 4, 0);
      }
      if (rf & 2) {
        \u0275\u0275advance(2);
        \u0275\u0275conditional(ctx._isServer ? 2 : -1);
        \u0275\u0275advance();
        \u0275\u0275conditional(ctx._isNativeHtmlTable ? 3 : 4);
      }
    },
    dependencies: [HeaderRowOutlet, DataRowOutlet, NoDataRowOutlet, FooterRowOutlet],
    styles: [".mat-mdc-table-sticky{position:sticky !important}mat-table{display:block}mat-header-row{min-height:var(--mat-table-header-container-height, 56px)}mat-row{min-height:var(--mat-table-row-item-container-height, 52px)}mat-footer-row{min-height:var(--mat-table-footer-container-height, 52px)}mat-row,mat-header-row,mat-footer-row{display:flex;border-width:0;border-bottom-width:1px;border-style:solid;align-items:center;box-sizing:border-box}mat-cell:first-of-type,mat-header-cell:first-of-type,mat-footer-cell:first-of-type{padding-left:24px}[dir=rtl] mat-cell:first-of-type:not(:only-of-type),[dir=rtl] mat-header-cell:first-of-type:not(:only-of-type),[dir=rtl] mat-footer-cell:first-of-type:not(:only-of-type){padding-left:0;padding-right:24px}mat-cell:last-of-type,mat-header-cell:last-of-type,mat-footer-cell:last-of-type{padding-right:24px}[dir=rtl] mat-cell:last-of-type:not(:only-of-type),[dir=rtl] mat-header-cell:last-of-type:not(:only-of-type),[dir=rtl] mat-footer-cell:last-of-type:not(:only-of-type){padding-right:0;padding-left:24px}mat-cell,mat-header-cell,mat-footer-cell{flex:1;display:flex;align-items:center;overflow:hidden;word-wrap:break-word;min-height:inherit}.mat-mdc-table{min-width:100%;border:0;border-spacing:0;table-layout:auto;white-space:normal;background-color:var(--mat-table-background-color, var(--mat-sys-surface))}.mdc-data-table__cell{box-sizing:border-box;overflow:hidden;text-align:left;text-overflow:ellipsis}[dir=rtl] .mdc-data-table__cell{text-align:right}.mdc-data-table__cell,.mdc-data-table__header-cell{padding:0 16px}.mat-mdc-header-row{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;height:var(--mat-table-header-container-height, 56px);color:var(--mat-table-header-headline-color, var(--mat-sys-on-surface, rgba(0, 0, 0, 0.87)));font-family:var(--mat-table-header-headline-font, var(--mat-sys-title-small-font, Roboto, sans-serif));line-height:var(--mat-table-header-headline-line-height, var(--mat-sys-title-small-line-height));font-size:var(--mat-table-header-headline-size, var(--mat-sys-title-small-size, 14px));font-weight:var(--mat-table-header-headline-weight, var(--mat-sys-title-small-weight, 500))}.mat-mdc-row{height:var(--mat-table-row-item-container-height, 52px);color:var(--mat-table-row-item-label-text-color, var(--mat-sys-on-surface, rgba(0, 0, 0, 0.87)))}.mat-mdc-row,.mdc-data-table__content{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;font-family:var(--mat-table-row-item-label-text-font, var(--mat-sys-body-medium-font, Roboto, sans-serif));line-height:var(--mat-table-row-item-label-text-line-height, var(--mat-sys-body-medium-line-height));font-size:var(--mat-table-row-item-label-text-size, var(--mat-sys-body-medium-size, 14px));font-weight:var(--mat-table-row-item-label-text-weight, var(--mat-sys-body-medium-weight))}.mat-mdc-footer-row{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;height:var(--mat-table-footer-container-height, 52px);color:var(--mat-table-row-item-label-text-color, var(--mat-sys-on-surface, rgba(0, 0, 0, 0.87)));font-family:var(--mat-table-footer-supporting-text-font, var(--mat-sys-body-medium-font, Roboto, sans-serif));line-height:var(--mat-table-footer-supporting-text-line-height, var(--mat-sys-body-medium-line-height));font-size:var(--mat-table-footer-supporting-text-size, var(--mat-sys-body-medium-size, 14px));font-weight:var(--mat-table-footer-supporting-text-weight, var(--mat-sys-body-medium-weight));letter-spacing:var(--mat-table-footer-supporting-text-tracking, var(--mat-sys-body-medium-tracking))}.mat-mdc-header-cell{border-bottom-color:var(--mat-table-row-item-outline-color, var(--mat-sys-outline, rgba(0, 0, 0, 0.12)));border-bottom-width:var(--mat-table-row-item-outline-width, 1px);border-bottom-style:solid;letter-spacing:var(--mat-table-header-headline-tracking, var(--mat-sys-title-small-tracking));font-weight:inherit;line-height:inherit;box-sizing:border-box;text-overflow:ellipsis;overflow:hidden;outline:none;text-align:left}[dir=rtl] .mat-mdc-header-cell{text-align:right}.mdc-data-table__row:last-child>.mat-mdc-header-cell{border-bottom:none}.mat-mdc-cell{border-bottom-color:var(--mat-table-row-item-outline-color, var(--mat-sys-outline, rgba(0, 0, 0, 0.12)));border-bottom-width:var(--mat-table-row-item-outline-width, 1px);border-bottom-style:solid;letter-spacing:var(--mat-table-row-item-label-text-tracking, var(--mat-sys-body-medium-tracking));line-height:inherit}.mdc-data-table__row:last-child>.mat-mdc-cell{border-bottom:none}.mat-mdc-footer-cell{letter-spacing:var(--mat-table-row-item-label-text-tracking, var(--mat-sys-body-medium-tracking))}mat-row.mat-mdc-row,mat-header-row.mat-mdc-header-row,mat-footer-row.mat-mdc-footer-row{border-bottom:none}.mat-mdc-table tbody,.mat-mdc-table tfoot,.mat-mdc-table thead,.mat-mdc-cell,.mat-mdc-footer-cell,.mat-mdc-header-row,.mat-mdc-row,.mat-mdc-footer-row,.mat-mdc-table .mat-mdc-header-cell{background:inherit}.mat-mdc-table mat-header-row.mat-mdc-header-row,.mat-mdc-table mat-row.mat-mdc-row,.mat-mdc-table mat-footer-row.mat-mdc-footer-cell{height:unset}mat-header-cell.mat-mdc-header-cell,mat-cell.mat-mdc-cell,mat-footer-cell.mat-mdc-footer-cell{align-self:stretch}\n"],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatTable, [{
    type: Component,
    args: [{
      selector: "mat-table, table[mat-table]",
      exportAs: "matTable",
      template: `
    <ng-content select="caption"/>
    <ng-content select="colgroup, col"/>

    <!--
      Unprojected content throws a hydration error so we need this to capture it.
      It gets removed on the client so it doesn't affect the layout.
    -->
    @if (_isServer) {
      <ng-content/>
    }

    @if (_isNativeHtmlTable) {
      <thead role="rowgroup">
        <ng-container headerRowOutlet/>
      </thead>
      <tbody class="mdc-data-table__content" role="rowgroup">
        <ng-container rowOutlet/>
        <ng-container noDataRowOutlet/>
      </tbody>
      <tfoot role="rowgroup">
        <ng-container footerRowOutlet/>
      </tfoot>
    } @else {
      <ng-container headerRowOutlet/>
      <ng-container rowOutlet/>
      <ng-container noDataRowOutlet/>
      <ng-container footerRowOutlet/>
    }
  `,
      host: {
        "class": "mat-mdc-table mdc-data-table__table",
        "[class.mdc-table-fixed-layout]": "fixedLayout"
      },
      providers: [
        {
          provide: CdkTable,
          useExisting: MatTable
        },
        {
          provide: CDK_TABLE,
          useExisting: MatTable
        },
        // TODO(michaeljamesparsons) Abstract the view repeater strategy to a directive API so this code
        //  is only included in the build if used.
        {
          provide: _VIEW_REPEATER_STRATEGY,
          useClass: _DisposeViewRepeaterStrategy
        },
        // Prevent nested tables from seeing this table's StickyPositioningListener.
        {
          provide: STICKY_POSITIONING_LISTENER,
          useValue: null
        }
      ],
      encapsulation: ViewEncapsulation.None,
      changeDetection: ChangeDetectionStrategy.Default,
      imports: [HeaderRowOutlet, DataRowOutlet, NoDataRowOutlet, FooterRowOutlet],
      styles: [".mat-mdc-table-sticky{position:sticky !important}mat-table{display:block}mat-header-row{min-height:var(--mat-table-header-container-height, 56px)}mat-row{min-height:var(--mat-table-row-item-container-height, 52px)}mat-footer-row{min-height:var(--mat-table-footer-container-height, 52px)}mat-row,mat-header-row,mat-footer-row{display:flex;border-width:0;border-bottom-width:1px;border-style:solid;align-items:center;box-sizing:border-box}mat-cell:first-of-type,mat-header-cell:first-of-type,mat-footer-cell:first-of-type{padding-left:24px}[dir=rtl] mat-cell:first-of-type:not(:only-of-type),[dir=rtl] mat-header-cell:first-of-type:not(:only-of-type),[dir=rtl] mat-footer-cell:first-of-type:not(:only-of-type){padding-left:0;padding-right:24px}mat-cell:last-of-type,mat-header-cell:last-of-type,mat-footer-cell:last-of-type{padding-right:24px}[dir=rtl] mat-cell:last-of-type:not(:only-of-type),[dir=rtl] mat-header-cell:last-of-type:not(:only-of-type),[dir=rtl] mat-footer-cell:last-of-type:not(:only-of-type){padding-right:0;padding-left:24px}mat-cell,mat-header-cell,mat-footer-cell{flex:1;display:flex;align-items:center;overflow:hidden;word-wrap:break-word;min-height:inherit}.mat-mdc-table{min-width:100%;border:0;border-spacing:0;table-layout:auto;white-space:normal;background-color:var(--mat-table-background-color, var(--mat-sys-surface))}.mdc-data-table__cell{box-sizing:border-box;overflow:hidden;text-align:left;text-overflow:ellipsis}[dir=rtl] .mdc-data-table__cell{text-align:right}.mdc-data-table__cell,.mdc-data-table__header-cell{padding:0 16px}.mat-mdc-header-row{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;height:var(--mat-table-header-container-height, 56px);color:var(--mat-table-header-headline-color, var(--mat-sys-on-surface, rgba(0, 0, 0, 0.87)));font-family:var(--mat-table-header-headline-font, var(--mat-sys-title-small-font, Roboto, sans-serif));line-height:var(--mat-table-header-headline-line-height, var(--mat-sys-title-small-line-height));font-size:var(--mat-table-header-headline-size, var(--mat-sys-title-small-size, 14px));font-weight:var(--mat-table-header-headline-weight, var(--mat-sys-title-small-weight, 500))}.mat-mdc-row{height:var(--mat-table-row-item-container-height, 52px);color:var(--mat-table-row-item-label-text-color, var(--mat-sys-on-surface, rgba(0, 0, 0, 0.87)))}.mat-mdc-row,.mdc-data-table__content{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;font-family:var(--mat-table-row-item-label-text-font, var(--mat-sys-body-medium-font, Roboto, sans-serif));line-height:var(--mat-table-row-item-label-text-line-height, var(--mat-sys-body-medium-line-height));font-size:var(--mat-table-row-item-label-text-size, var(--mat-sys-body-medium-size, 14px));font-weight:var(--mat-table-row-item-label-text-weight, var(--mat-sys-body-medium-weight))}.mat-mdc-footer-row{-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;height:var(--mat-table-footer-container-height, 52px);color:var(--mat-table-row-item-label-text-color, var(--mat-sys-on-surface, rgba(0, 0, 0, 0.87)));font-family:var(--mat-table-footer-supporting-text-font, var(--mat-sys-body-medium-font, Roboto, sans-serif));line-height:var(--mat-table-footer-supporting-text-line-height, var(--mat-sys-body-medium-line-height));font-size:var(--mat-table-footer-supporting-text-size, var(--mat-sys-body-medium-size, 14px));font-weight:var(--mat-table-footer-supporting-text-weight, var(--mat-sys-body-medium-weight));letter-spacing:var(--mat-table-footer-supporting-text-tracking, var(--mat-sys-body-medium-tracking))}.mat-mdc-header-cell{border-bottom-color:var(--mat-table-row-item-outline-color, var(--mat-sys-outline, rgba(0, 0, 0, 0.12)));border-bottom-width:var(--mat-table-row-item-outline-width, 1px);border-bottom-style:solid;letter-spacing:var(--mat-table-header-headline-tracking, var(--mat-sys-title-small-tracking));font-weight:inherit;line-height:inherit;box-sizing:border-box;text-overflow:ellipsis;overflow:hidden;outline:none;text-align:left}[dir=rtl] .mat-mdc-header-cell{text-align:right}.mdc-data-table__row:last-child>.mat-mdc-header-cell{border-bottom:none}.mat-mdc-cell{border-bottom-color:var(--mat-table-row-item-outline-color, var(--mat-sys-outline, rgba(0, 0, 0, 0.12)));border-bottom-width:var(--mat-table-row-item-outline-width, 1px);border-bottom-style:solid;letter-spacing:var(--mat-table-row-item-label-text-tracking, var(--mat-sys-body-medium-tracking));line-height:inherit}.mdc-data-table__row:last-child>.mat-mdc-cell{border-bottom:none}.mat-mdc-footer-cell{letter-spacing:var(--mat-table-row-item-label-text-tracking, var(--mat-sys-body-medium-tracking))}mat-row.mat-mdc-row,mat-header-row.mat-mdc-header-row,mat-footer-row.mat-mdc-footer-row{border-bottom:none}.mat-mdc-table tbody,.mat-mdc-table tfoot,.mat-mdc-table thead,.mat-mdc-cell,.mat-mdc-footer-cell,.mat-mdc-header-row,.mat-mdc-row,.mat-mdc-footer-row,.mat-mdc-table .mat-mdc-header-cell{background:inherit}.mat-mdc-table mat-header-row.mat-mdc-header-row,.mat-mdc-table mat-row.mat-mdc-row,.mat-mdc-table mat-footer-row.mat-mdc-footer-cell{height:unset}mat-header-cell.mat-mdc-header-cell,mat-cell.mat-mdc-cell,mat-footer-cell.mat-mdc-footer-cell{align-self:stretch}\n"]
    }]
  }], null, null);
})();
var MatCellDef = class _MatCellDef extends CdkCellDef {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatCellDef_BaseFactory;
    return function MatCellDef_Factory(__ngFactoryType__) {
      return (\u0275MatCellDef_BaseFactory || (\u0275MatCellDef_BaseFactory = \u0275\u0275getInheritedFactory(_MatCellDef)))(__ngFactoryType__ || _MatCellDef);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatCellDef,
    selectors: [["", "matCellDef", ""]],
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkCellDef,
      useExisting: _MatCellDef
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatCellDef, [{
    type: Directive,
    args: [{
      selector: "[matCellDef]",
      providers: [{
        provide: CdkCellDef,
        useExisting: MatCellDef
      }]
    }]
  }], null, null);
})();
var MatHeaderCellDef = class _MatHeaderCellDef extends CdkHeaderCellDef {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatHeaderCellDef_BaseFactory;
    return function MatHeaderCellDef_Factory(__ngFactoryType__) {
      return (\u0275MatHeaderCellDef_BaseFactory || (\u0275MatHeaderCellDef_BaseFactory = \u0275\u0275getInheritedFactory(_MatHeaderCellDef)))(__ngFactoryType__ || _MatHeaderCellDef);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatHeaderCellDef,
    selectors: [["", "matHeaderCellDef", ""]],
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkHeaderCellDef,
      useExisting: _MatHeaderCellDef
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatHeaderCellDef, [{
    type: Directive,
    args: [{
      selector: "[matHeaderCellDef]",
      providers: [{
        provide: CdkHeaderCellDef,
        useExisting: MatHeaderCellDef
      }]
    }]
  }], null, null);
})();
var MatFooterCellDef = class _MatFooterCellDef extends CdkFooterCellDef {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatFooterCellDef_BaseFactory;
    return function MatFooterCellDef_Factory(__ngFactoryType__) {
      return (\u0275MatFooterCellDef_BaseFactory || (\u0275MatFooterCellDef_BaseFactory = \u0275\u0275getInheritedFactory(_MatFooterCellDef)))(__ngFactoryType__ || _MatFooterCellDef);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatFooterCellDef,
    selectors: [["", "matFooterCellDef", ""]],
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkFooterCellDef,
      useExisting: _MatFooterCellDef
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatFooterCellDef, [{
    type: Directive,
    args: [{
      selector: "[matFooterCellDef]",
      providers: [{
        provide: CdkFooterCellDef,
        useExisting: MatFooterCellDef
      }]
    }]
  }], null, null);
})();
var MatColumnDef = class _MatColumnDef extends CdkColumnDef {
  /** Unique name for this column. */
  get name() {
    return this._name;
  }
  set name(name) {
    this._setNameInput(name);
  }
  /**
   * Add "mat-column-" prefix in addition to "cdk-column-" prefix.
   * In the future, this will only add "mat-column-" and columnCssClassName
   * will change from type string[] to string.
   * @docs-private
   */
  _updateColumnCssClassName() {
    super._updateColumnCssClassName();
    this._columnCssClassName.push(`mat-column-${this.cssClassFriendlyName}`);
  }
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatColumnDef_BaseFactory;
    return function MatColumnDef_Factory(__ngFactoryType__) {
      return (\u0275MatColumnDef_BaseFactory || (\u0275MatColumnDef_BaseFactory = \u0275\u0275getInheritedFactory(_MatColumnDef)))(__ngFactoryType__ || _MatColumnDef);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatColumnDef,
    selectors: [["", "matColumnDef", ""]],
    inputs: {
      name: [0, "matColumnDef", "name"]
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkColumnDef,
      useExisting: _MatColumnDef
    }, {
      provide: "MAT_SORT_HEADER_COLUMN_DEF",
      useExisting: _MatColumnDef
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatColumnDef, [{
    type: Directive,
    args: [{
      selector: "[matColumnDef]",
      providers: [{
        provide: CdkColumnDef,
        useExisting: MatColumnDef
      }, {
        provide: "MAT_SORT_HEADER_COLUMN_DEF",
        useExisting: MatColumnDef
      }]
    }]
  }], null, {
    name: [{
      type: Input,
      args: ["matColumnDef"]
    }]
  });
})();
var MatHeaderCell = class _MatHeaderCell extends CdkHeaderCell {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatHeaderCell_BaseFactory;
    return function MatHeaderCell_Factory(__ngFactoryType__) {
      return (\u0275MatHeaderCell_BaseFactory || (\u0275MatHeaderCell_BaseFactory = \u0275\u0275getInheritedFactory(_MatHeaderCell)))(__ngFactoryType__ || _MatHeaderCell);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatHeaderCell,
    selectors: [["mat-header-cell"], ["th", "mat-header-cell", ""]],
    hostAttrs: ["role", "columnheader", 1, "mat-mdc-header-cell", "mdc-data-table__header-cell"],
    features: [\u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatHeaderCell, [{
    type: Directive,
    args: [{
      selector: "mat-header-cell, th[mat-header-cell]",
      host: {
        "class": "mat-mdc-header-cell mdc-data-table__header-cell",
        "role": "columnheader"
      }
    }]
  }], null, null);
})();
var MatFooterCell = class _MatFooterCell extends CdkFooterCell {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatFooterCell_BaseFactory;
    return function MatFooterCell_Factory(__ngFactoryType__) {
      return (\u0275MatFooterCell_BaseFactory || (\u0275MatFooterCell_BaseFactory = \u0275\u0275getInheritedFactory(_MatFooterCell)))(__ngFactoryType__ || _MatFooterCell);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatFooterCell,
    selectors: [["mat-footer-cell"], ["td", "mat-footer-cell", ""]],
    hostAttrs: [1, "mat-mdc-footer-cell", "mdc-data-table__cell"],
    features: [\u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatFooterCell, [{
    type: Directive,
    args: [{
      selector: "mat-footer-cell, td[mat-footer-cell]",
      host: {
        "class": "mat-mdc-footer-cell mdc-data-table__cell"
      }
    }]
  }], null, null);
})();
var MatCell = class _MatCell extends CdkCell {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatCell_BaseFactory;
    return function MatCell_Factory(__ngFactoryType__) {
      return (\u0275MatCell_BaseFactory || (\u0275MatCell_BaseFactory = \u0275\u0275getInheritedFactory(_MatCell)))(__ngFactoryType__ || _MatCell);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatCell,
    selectors: [["mat-cell"], ["td", "mat-cell", ""]],
    hostAttrs: [1, "mat-mdc-cell", "mdc-data-table__cell"],
    features: [\u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatCell, [{
    type: Directive,
    args: [{
      selector: "mat-cell, td[mat-cell]",
      host: {
        "class": "mat-mdc-cell mdc-data-table__cell"
      }
    }]
  }], null, null);
})();
var ROW_TEMPLATE = `<ng-container cdkCellOutlet></ng-container>`;
var MatHeaderRowDef = class _MatHeaderRowDef extends CdkHeaderRowDef {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatHeaderRowDef_BaseFactory;
    return function MatHeaderRowDef_Factory(__ngFactoryType__) {
      return (\u0275MatHeaderRowDef_BaseFactory || (\u0275MatHeaderRowDef_BaseFactory = \u0275\u0275getInheritedFactory(_MatHeaderRowDef)))(__ngFactoryType__ || _MatHeaderRowDef);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatHeaderRowDef,
    selectors: [["", "matHeaderRowDef", ""]],
    inputs: {
      columns: [0, "matHeaderRowDef", "columns"],
      sticky: [2, "matHeaderRowDefSticky", "sticky", booleanAttribute]
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkHeaderRowDef,
      useExisting: _MatHeaderRowDef
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatHeaderRowDef, [{
    type: Directive,
    args: [{
      selector: "[matHeaderRowDef]",
      providers: [{
        provide: CdkHeaderRowDef,
        useExisting: MatHeaderRowDef
      }],
      inputs: [{
        name: "columns",
        alias: "matHeaderRowDef"
      }, {
        name: "sticky",
        alias: "matHeaderRowDefSticky",
        transform: booleanAttribute
      }]
    }]
  }], null, null);
})();
var MatFooterRowDef = class _MatFooterRowDef extends CdkFooterRowDef {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatFooterRowDef_BaseFactory;
    return function MatFooterRowDef_Factory(__ngFactoryType__) {
      return (\u0275MatFooterRowDef_BaseFactory || (\u0275MatFooterRowDef_BaseFactory = \u0275\u0275getInheritedFactory(_MatFooterRowDef)))(__ngFactoryType__ || _MatFooterRowDef);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatFooterRowDef,
    selectors: [["", "matFooterRowDef", ""]],
    inputs: {
      columns: [0, "matFooterRowDef", "columns"],
      sticky: [2, "matFooterRowDefSticky", "sticky", booleanAttribute]
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkFooterRowDef,
      useExisting: _MatFooterRowDef
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatFooterRowDef, [{
    type: Directive,
    args: [{
      selector: "[matFooterRowDef]",
      providers: [{
        provide: CdkFooterRowDef,
        useExisting: MatFooterRowDef
      }],
      inputs: [{
        name: "columns",
        alias: "matFooterRowDef"
      }, {
        name: "sticky",
        alias: "matFooterRowDefSticky",
        transform: booleanAttribute
      }]
    }]
  }], null, null);
})();
var MatRowDef = class _MatRowDef extends CdkRowDef {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatRowDef_BaseFactory;
    return function MatRowDef_Factory(__ngFactoryType__) {
      return (\u0275MatRowDef_BaseFactory || (\u0275MatRowDef_BaseFactory = \u0275\u0275getInheritedFactory(_MatRowDef)))(__ngFactoryType__ || _MatRowDef);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatRowDef,
    selectors: [["", "matRowDef", ""]],
    inputs: {
      columns: [0, "matRowDefColumns", "columns"],
      when: [0, "matRowDefWhen", "when"]
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkRowDef,
      useExisting: _MatRowDef
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatRowDef, [{
    type: Directive,
    args: [{
      selector: "[matRowDef]",
      providers: [{
        provide: CdkRowDef,
        useExisting: MatRowDef
      }],
      inputs: [{
        name: "columns",
        alias: "matRowDefColumns"
      }, {
        name: "when",
        alias: "matRowDefWhen"
      }]
    }]
  }], null, null);
})();
var MatHeaderRow = class _MatHeaderRow extends CdkHeaderRow {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatHeaderRow_BaseFactory;
    return function MatHeaderRow_Factory(__ngFactoryType__) {
      return (\u0275MatHeaderRow_BaseFactory || (\u0275MatHeaderRow_BaseFactory = \u0275\u0275getInheritedFactory(_MatHeaderRow)))(__ngFactoryType__ || _MatHeaderRow);
    };
  })();
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatHeaderRow,
    selectors: [["mat-header-row"], ["tr", "mat-header-row", ""]],
    hostAttrs: ["role", "row", 1, "mat-mdc-header-row", "mdc-data-table__header-row"],
    exportAs: ["matHeaderRow"],
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkHeaderRow,
      useExisting: _MatHeaderRow
    }]), \u0275\u0275InheritDefinitionFeature],
    decls: 1,
    vars: 0,
    consts: [["cdkCellOutlet", ""]],
    template: function MatHeaderRow_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275elementContainer(0, 0);
      }
    },
    dependencies: [CdkCellOutlet],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatHeaderRow, [{
    type: Component,
    args: [{
      selector: "mat-header-row, tr[mat-header-row]",
      template: ROW_TEMPLATE,
      host: {
        "class": "mat-mdc-header-row mdc-data-table__header-row",
        "role": "row"
      },
      // See note on CdkTable for explanation on why this uses the default change detection strategy.
      // tslint:disable-next-line:validate-decorators
      changeDetection: ChangeDetectionStrategy.Default,
      encapsulation: ViewEncapsulation.None,
      exportAs: "matHeaderRow",
      providers: [{
        provide: CdkHeaderRow,
        useExisting: MatHeaderRow
      }],
      imports: [CdkCellOutlet]
    }]
  }], null, null);
})();
var MatFooterRow = class _MatFooterRow extends CdkFooterRow {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatFooterRow_BaseFactory;
    return function MatFooterRow_Factory(__ngFactoryType__) {
      return (\u0275MatFooterRow_BaseFactory || (\u0275MatFooterRow_BaseFactory = \u0275\u0275getInheritedFactory(_MatFooterRow)))(__ngFactoryType__ || _MatFooterRow);
    };
  })();
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatFooterRow,
    selectors: [["mat-footer-row"], ["tr", "mat-footer-row", ""]],
    hostAttrs: ["role", "row", 1, "mat-mdc-footer-row", "mdc-data-table__row"],
    exportAs: ["matFooterRow"],
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkFooterRow,
      useExisting: _MatFooterRow
    }]), \u0275\u0275InheritDefinitionFeature],
    decls: 1,
    vars: 0,
    consts: [["cdkCellOutlet", ""]],
    template: function MatFooterRow_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275elementContainer(0, 0);
      }
    },
    dependencies: [CdkCellOutlet],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatFooterRow, [{
    type: Component,
    args: [{
      selector: "mat-footer-row, tr[mat-footer-row]",
      template: ROW_TEMPLATE,
      host: {
        "class": "mat-mdc-footer-row mdc-data-table__row",
        "role": "row"
      },
      // See note on CdkTable for explanation on why this uses the default change detection strategy.
      // tslint:disable-next-line:validate-decorators
      changeDetection: ChangeDetectionStrategy.Default,
      encapsulation: ViewEncapsulation.None,
      exportAs: "matFooterRow",
      providers: [{
        provide: CdkFooterRow,
        useExisting: MatFooterRow
      }],
      imports: [CdkCellOutlet]
    }]
  }], null, null);
})();
var MatRow = class _MatRow extends CdkRow {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatRow_BaseFactory;
    return function MatRow_Factory(__ngFactoryType__) {
      return (\u0275MatRow_BaseFactory || (\u0275MatRow_BaseFactory = \u0275\u0275getInheritedFactory(_MatRow)))(__ngFactoryType__ || _MatRow);
    };
  })();
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatRow,
    selectors: [["mat-row"], ["tr", "mat-row", ""]],
    hostAttrs: ["role", "row", 1, "mat-mdc-row", "mdc-data-table__row"],
    exportAs: ["matRow"],
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkRow,
      useExisting: _MatRow
    }]), \u0275\u0275InheritDefinitionFeature],
    decls: 1,
    vars: 0,
    consts: [["cdkCellOutlet", ""]],
    template: function MatRow_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275elementContainer(0, 0);
      }
    },
    dependencies: [CdkCellOutlet],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatRow, [{
    type: Component,
    args: [{
      selector: "mat-row, tr[mat-row]",
      template: ROW_TEMPLATE,
      host: {
        "class": "mat-mdc-row mdc-data-table__row",
        "role": "row"
      },
      // See note on CdkTable for explanation on why this uses the default change detection strategy.
      // tslint:disable-next-line:validate-decorators
      changeDetection: ChangeDetectionStrategy.Default,
      encapsulation: ViewEncapsulation.None,
      exportAs: "matRow",
      providers: [{
        provide: CdkRow,
        useExisting: MatRow
      }],
      imports: [CdkCellOutlet]
    }]
  }], null, null);
})();
var MatNoDataRow = class _MatNoDataRow extends CdkNoDataRow {
  _cellSelector = "td, mat-cell, [mat-cell], .mat-cell";
  constructor() {
    super();
    this._contentClassNames.push("mat-mdc-no-data-row", "mat-mdc-row", "mdc-data-table__row");
    this._cellClassNames.push("mat-mdc-cell", "mdc-data-table__cell", "mat-no-data-cell");
  }
  static \u0275fac = function MatNoDataRow_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatNoDataRow)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatNoDataRow,
    selectors: [["ng-template", "matNoDataRow", ""]],
    features: [\u0275\u0275ProvidersFeature([{
      provide: CdkNoDataRow,
      useExisting: _MatNoDataRow
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatNoDataRow, [{
    type: Directive,
    args: [{
      selector: "ng-template[matNoDataRow]",
      providers: [{
        provide: CdkNoDataRow,
        useExisting: MatNoDataRow
      }]
    }]
  }], () => [], null);
})();
var MatTextColumn = class _MatTextColumn extends CdkTextColumn {
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatTextColumn_BaseFactory;
    return function MatTextColumn_Factory(__ngFactoryType__) {
      return (\u0275MatTextColumn_BaseFactory || (\u0275MatTextColumn_BaseFactory = \u0275\u0275getInheritedFactory(_MatTextColumn)))(__ngFactoryType__ || _MatTextColumn);
    };
  })();
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatTextColumn,
    selectors: [["mat-text-column"]],
    features: [\u0275\u0275InheritDefinitionFeature],
    decls: 3,
    vars: 0,
    consts: [["matColumnDef", ""], ["mat-header-cell", "", 3, "text-align", 4, "matHeaderCellDef"], ["mat-cell", "", 3, "text-align", 4, "matCellDef"], ["mat-header-cell", ""], ["mat-cell", ""]],
    template: function MatTextColumn_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275elementContainerStart(0, 0);
        \u0275\u0275template(1, MatTextColumn_th_1_Template, 2, 3, "th", 1)(2, MatTextColumn_td_2_Template, 2, 3, "td", 2);
        \u0275\u0275elementContainerEnd();
      }
    },
    dependencies: [MatColumnDef, MatHeaderCellDef, MatHeaderCell, MatCellDef, MatCell],
    encapsulation: 2
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatTextColumn, [{
    type: Component,
    args: [{
      selector: "mat-text-column",
      template: `
    <ng-container matColumnDef>
      <th mat-header-cell *matHeaderCellDef [style.text-align]="justify">
        {{headerText}}
      </th>
      <td mat-cell *matCellDef="let data" [style.text-align]="justify">
        {{dataAccessor(data, name)}}
      </td>
    </ng-container>
  `,
      encapsulation: ViewEncapsulation.None,
      // Change detection is intentionally not set to OnPush. This component's template will be provided
      // to the table to be inserted into its view. This is problematic when change detection runs since
      // the bindings in this template will be evaluated _after_ the table's view is evaluated, which
      // mean's the template in the table's view will not have the updated value (and in fact will cause
      // an ExpressionChangedAfterItHasBeenCheckedError).
      // tslint:disable-next-line:validate-decorators
      changeDetection: ChangeDetectionStrategy.Default,
      imports: [MatColumnDef, MatHeaderCellDef, MatHeaderCell, MatCellDef, MatCell]
    }]
  }], null, null);
})();
var EXPORTED_DECLARATIONS2 = [
  // Table
  MatTable,
  MatRecycleRows,
  // Template defs
  MatHeaderCellDef,
  MatHeaderRowDef,
  MatColumnDef,
  MatCellDef,
  MatRowDef,
  MatFooterCellDef,
  MatFooterRowDef,
  // Cell directives
  MatHeaderCell,
  MatCell,
  MatFooterCell,
  // Row directives
  MatHeaderRow,
  MatRow,
  MatFooterRow,
  MatNoDataRow,
  MatTextColumn
];
var MatTableModule = class _MatTableModule {
  static \u0275fac = function MatTableModule_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatTableModule)();
  };
  static \u0275mod = /* @__PURE__ */ \u0275\u0275defineNgModule({
    type: _MatTableModule,
    imports: [
      MatCommonModule,
      CdkTableModule,
      // Table
      MatTable,
      MatRecycleRows,
      // Template defs
      MatHeaderCellDef,
      MatHeaderRowDef,
      MatColumnDef,
      MatCellDef,
      MatRowDef,
      MatFooterCellDef,
      MatFooterRowDef,
      // Cell directives
      MatHeaderCell,
      MatCell,
      MatFooterCell,
      // Row directives
      MatHeaderRow,
      MatRow,
      MatFooterRow,
      MatNoDataRow,
      MatTextColumn
    ],
    exports: [
      MatCommonModule,
      // Table
      MatTable,
      MatRecycleRows,
      // Template defs
      MatHeaderCellDef,
      MatHeaderRowDef,
      MatColumnDef,
      MatCellDef,
      MatRowDef,
      MatFooterCellDef,
      MatFooterRowDef,
      // Cell directives
      MatHeaderCell,
      MatCell,
      MatFooterCell,
      // Row directives
      MatHeaderRow,
      MatRow,
      MatFooterRow,
      MatNoDataRow,
      MatTextColumn
    ]
  });
  static \u0275inj = /* @__PURE__ */ \u0275\u0275defineInjector({
    imports: [MatCommonModule, CdkTableModule, MatCommonModule]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatTableModule, [{
    type: NgModule,
    args: [{
      imports: [MatCommonModule, CdkTableModule, ...EXPORTED_DECLARATIONS2],
      exports: [MatCommonModule, EXPORTED_DECLARATIONS2]
    }]
  }], null, null);
})();

// node_modules/@angular/material/fesm2022/chips.mjs
var _c03 = ["*", [["mat-chip-avatar"], ["", "matChipAvatar", ""]], [["mat-chip-trailing-icon"], ["", "matChipRemove", ""], ["", "matChipTrailingIcon", ""]]];
var _c13 = ["*", "mat-chip-avatar, [matChipAvatar]", "mat-chip-trailing-icon,[matChipRemove],[matChipTrailingIcon]"];
function MatChip_Conditional_3_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "span", 3);
    \u0275\u0275projection(1, 1);
    \u0275\u0275elementEnd();
  }
}
function MatChip_Conditional_7_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "span", 6);
    \u0275\u0275projection(1, 2);
    \u0275\u0275elementEnd();
  }
}
function MatChipOption_Conditional_3_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "span", 3);
    \u0275\u0275projection(1, 1);
    \u0275\u0275elementStart(2, "span", 7);
    \u0275\u0275namespaceSVG();
    \u0275\u0275elementStart(3, "svg", 8);
    \u0275\u0275element(4, "path", 9);
    \u0275\u0275elementEnd()()();
  }
}
function MatChipOption_Conditional_7_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "span", 6);
    \u0275\u0275projection(1, 2);
    \u0275\u0275elementEnd();
  }
}
var _c2 = '.mdc-evolution-chip,.mdc-evolution-chip__cell,.mdc-evolution-chip__action{display:inline-flex;align-items:center}.mdc-evolution-chip{position:relative;max-width:100%}.mdc-evolution-chip__cell,.mdc-evolution-chip__action{height:100%}.mdc-evolution-chip__cell--primary{flex-basis:100%;overflow-x:hidden}.mdc-evolution-chip__cell--trailing{flex:1 0 auto}.mdc-evolution-chip__action{align-items:center;background:none;border:none;box-sizing:content-box;cursor:pointer;display:inline-flex;justify-content:center;outline:none;padding:0;text-decoration:none;color:inherit}.mdc-evolution-chip__action--presentational{cursor:auto}.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{pointer-events:none}@media(forced-colors: active){.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{forced-color-adjust:none}}.mdc-evolution-chip__action--primary{font:inherit;letter-spacing:inherit;white-space:inherit;overflow-x:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-outline-width, 1px);border-radius:var(--mat-chip-container-shape-radius, 8px);box-sizing:border-box;content:"";height:100%;left:0;position:absolute;pointer-events:none;top:0;width:100%;z-index:1;border-style:solid}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-outline-color, var(--mat-sys-outline))}.mdc-evolution-chip__action--primary:not(.mdc-evolution-chip__action--presentational):not(.mdc-ripple-upgraded):focus::before{border-color:var(--mat-chip-focus-outline-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-disabled-outline-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-basic-chip .mdc-evolution-chip__action--primary{font:inherit}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip__action--secondary{position:relative;overflow:visible}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-trailing-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip__text-label{-webkit-user-select:none;user-select:none;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__text-label{font-family:var(--mat-chip-label-text-font, var(--mat-sys-label-large-font));line-height:var(--mat-chip-label-text-line-height, var(--mat-sys-label-large-line-height));font-size:var(--mat-chip-label-text-size, var(--mat-sys-label-large-size));font-weight:var(--mat-chip-label-text-weight, var(--mat-sys-label-large-weight));letter-spacing:var(--mat-chip-label-text-tracking, var(--mat-sys-label-large-tracking))}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-label-text-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label,.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label{color:var(--mat-chip-disabled-label-text-color, color-mix(in srgb, var(--mat-sys-on-surface) 38%, transparent))}.mdc-evolution-chip__graphic{align-items:center;display:inline-flex;justify-content:center;overflow:hidden;pointer-events:none;position:relative;flex:1 0 auto}.mat-mdc-standard-chip .mdc-evolution-chip__graphic{width:var(--mat-chip-with-avatar-avatar-size, 24px);height:var(--mat-chip-with-avatar-avatar-size, 24px);font-size:var(--mat-chip-with-avatar-avatar-size, 24px)}.mdc-evolution-chip--selecting .mdc-evolution-chip__graphic{transition:width 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selectable:not(.mdc-evolution-chip--selected):not(.mdc-evolution-chip--with-primary-icon) .mdc-evolution-chip__graphic{width:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__graphic{padding-left:0}.mdc-evolution-chip__checkmark{position:absolute;opacity:0;top:50%;left:50%;height:20px;width:20px}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark{transition:transform 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1);transform:translate(-75%, -50%)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{transform:translate(-50%, -50%);opacity:1}.mdc-evolution-chip__checkmark-svg{display:block}.mdc-evolution-chip__checkmark-path{stroke-width:2px;stroke-dasharray:29.7833385;stroke-dashoffset:29.7833385;stroke:currentColor}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark-path{transition:stroke-dashoffset 150ms 45ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark-path{stroke-dashoffset:0}@media(forced-colors: active){.mdc-evolution-chip__checkmark-path{stroke:CanvasText !important}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--trailing{height:18px;width:18px;font-size:18px}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove{opacity:calc(var(--mat-chip-trailing-action-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove:focus{opacity:calc(var(--mat-chip-trailing-action-focus-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mat-mdc-standard-chip{border-radius:var(--mat-chip-container-shape-radius, 8px);height:var(--mat-chip-container-height, 32px)}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-container-color, transparent)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{background-color:var(--mat-chip-elevated-disabled-container-color)}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled{background-color:var(--mat-chip-flat-disabled-selected-container-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}@media(forced-colors: active){.mat-mdc-standard-chip{outline:solid 1px}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--primary{border-radius:var(--mat-chip-with-avatar-avatar-shape-radius, 24px);width:var(--mat-chip-with-icon-icon-size, 18px);height:var(--mat-chip-with-icon-icon-size, 18px);font-size:var(--mat-chip-with-icon-icon-size, 18px)}.mdc-evolution-chip--selected .mdc-evolution-chip__icon--primary{opacity:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-highlighted{--mat-chip-with-icon-icon-color: var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container));--mat-chip-elevated-container-color: var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container));--mat-chip-label-text-color: var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container));--mat-chip-outline-width: var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-selected .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-chip:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-hover-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-focus-overlay .mat-mdc-chip-selected:hover,.mat-mdc-chip-highlighted:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-hover-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected.cdk-focused .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mdc-evolution-chip--disabled:not(.mdc-evolution-chip--selected) .mat-mdc-chip-avatar{opacity:var(--mat-chip-with-avatar-disabled-avatar-opacity, 0.38)}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{opacity:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38)}.mdc-evolution-chip--disabled.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{opacity:var(--mat-chip-with-icon-disabled-icon-opacity, 0.38)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{opacity:var(--mat-chip-disabled-container-opacity, 1)}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-trailing-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-edit,.mat-mdc-chip-remove{opacity:var(--mat-chip-trailing-action-opacity, 1)}.mat-mdc-chip-edit:focus,.mat-mdc-chip-remove:focus{opacity:var(--mat-chip-trailing-action-focus-opacity, 1)}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{background-color:var(--mat-chip-trailing-action-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-edit:hover::after,.mat-mdc-chip-remove:hover::after{opacity:var(--mat-chip-trailing-action-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-edit:focus::after,.mat-mdc-chip-remove:focus::after{opacity:var(--mat-chip-trailing-action-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected .mat-mdc-chip-remove::after,.mat-mdc-chip-highlighted .mat-mdc-chip-remove::after{background-color:var(--mat-chip-selected-trailing-action-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip{-webkit-tap-highlight-color:rgba(0,0,0,0)}.mat-mdc-standard-chip .mdc-evolution-chip__cell--primary,.mat-mdc-standard-chip .mdc-evolution-chip__action--primary,.mat-mdc-standard-chip .mat-mdc-chip-action-label{overflow:visible}.mat-mdc-standard-chip .mat-mdc-chip-graphic,.mat-mdc-standard-chip .mat-mdc-chip-trailing-icon{box-sizing:content-box}.mat-mdc-standard-chip._mat-animation-noopable,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__graphic,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark-path{transition-duration:1ms;animation-duration:1ms}.mat-mdc-chip-focus-overlay{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;opacity:0;border-radius:inherit;transition:opacity 150ms linear}._mat-animation-noopable .mat-mdc-chip-focus-overlay{transition:none}.mat-mdc-basic-chip .mat-mdc-chip-focus-overlay{display:none}.mat-mdc-chip .mat-ripple.mat-mdc-chip-ripple{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;border-radius:inherit}.mat-mdc-chip-avatar{text-align:center;line-height:1;color:var(--mat-chip-with-icon-icon-color, currentColor)}.mat-mdc-chip{position:relative;z-index:0}.mat-mdc-chip-action-label{text-align:left;z-index:1}[dir=rtl] .mat-mdc-chip-action-label{text-align:right}.mat-mdc-chip.mdc-evolution-chip--with-trailing-action .mat-mdc-chip-action-label{position:relative}.mat-mdc-chip-action-label .mat-mdc-chip-primary-focus-indicator{position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none}.mat-mdc-chip-action-label .mat-focus-indicator::before{margin:calc(calc(var(--mat-focus-indicator-border-width, 3px) + 2px)*-1)}.mat-mdc-chip-edit::before,.mat-mdc-chip-remove::before{margin:calc(var(--mat-focus-indicator-border-width, 3px)*-1);left:8px;right:8px}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{content:"";display:block;opacity:0;position:absolute;top:-3px;bottom:-3px;left:5px;right:5px;border-radius:50%;box-sizing:border-box;padding:12px;margin:-12px;background-clip:content-box}.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{width:18px;height:18px;font-size:18px;box-sizing:content-box}.mat-chip-edit-input{cursor:text;display:inline-block;color:inherit;outline:0}@media(forced-colors: active){.mat-mdc-chip-selected:not(.mat-mdc-chip-multiple){outline-width:3px}}.mat-mdc-chip-action:focus .mat-focus-indicator::before{content:""}.mdc-evolution-chip__icon,.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{min-height:fit-content}img.mdc-evolution-chip__icon{min-height:0}\n';
var _c3 = [[["", "matChipEdit", ""]], [["mat-chip-avatar"], ["", "matChipAvatar", ""]], [["", "matChipEditInput", ""]], "*", [["mat-chip-trailing-icon"], ["", "matChipRemove", ""], ["", "matChipTrailingIcon", ""]]];
var _c4 = ["[matChipEdit]", "mat-chip-avatar, [matChipAvatar]", "[matChipEditInput]", "*", "mat-chip-trailing-icon,[matChipRemove],[matChipTrailingIcon]"];
function MatChipRow_Conditional_0_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275element(0, "span", 0);
  }
}
function MatChipRow_Conditional_1_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "span", 1);
    \u0275\u0275projection(1);
    \u0275\u0275elementEnd();
  }
}
function MatChipRow_Conditional_3_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "span", 3);
    \u0275\u0275projection(1, 1);
    \u0275\u0275elementEnd();
  }
}
function MatChipRow_Conditional_5_Conditional_0_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275projection(0, 2);
  }
}
function MatChipRow_Conditional_5_Conditional_1_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275element(0, "span", 7);
  }
}
function MatChipRow_Conditional_5_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275conditionalCreate(0, MatChipRow_Conditional_5_Conditional_0_Template, 1, 0)(1, MatChipRow_Conditional_5_Conditional_1_Template, 1, 0, "span", 7);
  }
  if (rf & 2) {
    const ctx_r0 = \u0275\u0275nextContext();
    \u0275\u0275conditional(ctx_r0.contentEditInput ? 0 : 1);
  }
}
function MatChipRow_Conditional_6_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275projection(0, 3);
  }
}
function MatChipRow_Conditional_8_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "span", 6);
    \u0275\u0275projection(1, 4);
    \u0275\u0275elementEnd();
  }
}
var _c5 = ["*"];
var _c6 = ".mat-mdc-chip-set{display:flex}.mat-mdc-chip-set:focus{outline:none}.mat-mdc-chip-set .mdc-evolution-chip-set__chips{min-width:100%;margin-left:-8px;margin-right:0}.mat-mdc-chip-set .mdc-evolution-chip{margin:4px 0 4px 8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip-set__chips{margin-left:0;margin-right:-8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip{margin-left:0;margin-right:8px}.mdc-evolution-chip-set__chips{display:flex;flex-flow:wrap;min-width:0}.mat-mdc-chip-set-stacked{flex-direction:column;align-items:flex-start}.mat-mdc-chip-set-stacked .mat-mdc-chip{width:100%}.mat-mdc-chip-set-stacked .mdc-evolution-chip__graphic{flex-grow:0}.mat-mdc-chip-set-stacked .mdc-evolution-chip__action--primary{flex-basis:100%;justify-content:start}input.mat-mdc-chip-input{flex:1 0 150px;margin-left:8px}[dir=rtl] input.mat-mdc-chip-input{margin-left:0;margin-right:8px}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-moz-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-webkit-input-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input:-ms-input-placeholder{opacity:1}.mat-mdc-chip-set+input.mat-mdc-chip-input{margin-left:0;margin-right:0}\n";
var MAT_CHIPS_DEFAULT_OPTIONS = new InjectionToken("mat-chips-default-options", {
  providedIn: "root",
  factory: () => ({
    separatorKeyCodes: [ENTER]
  })
});
var MAT_CHIP_AVATAR = new InjectionToken("MatChipAvatar");
var MAT_CHIP_TRAILING_ICON = new InjectionToken("MatChipTrailingIcon");
var MAT_CHIP_EDIT = new InjectionToken("MatChipEdit");
var MAT_CHIP_REMOVE = new InjectionToken("MatChipRemove");
var MAT_CHIP = new InjectionToken("MatChip");
var MatChipAction = class _MatChipAction {
  _elementRef = inject(ElementRef);
  _parentChip = inject(MAT_CHIP);
  /** Whether the action is interactive. */
  isInteractive = true;
  /** Whether this is the primary action in the chip. */
  _isPrimary = true;
  /** Whether this is the leading action in the chip. */
  _isLeading = false;
  // TODO(adolgachev): consolidate usage to secondary css class
  /** Whether the action is disabled. */
  get disabled() {
    return this._disabled || this._parentChip?.disabled || false;
  }
  set disabled(value) {
    this._disabled = value;
  }
  _disabled = false;
  /** Tab index of the action. */
  tabIndex = -1;
  /**
   * Private API to allow focusing this chip when it is disabled.
   */
  _allowFocusWhenDisabled = false;
  /**
   * Determine the value of the disabled attribute for this chip action.
   */
  _getDisabledAttribute() {
    return this.disabled && !this._allowFocusWhenDisabled ? "" : null;
  }
  /**
   * Determine the value of the tabindex attribute for this chip action.
   */
  _getTabindex() {
    return this.disabled && !this._allowFocusWhenDisabled || !this.isInteractive ? null : this.tabIndex.toString();
  }
  constructor() {
    inject(_CdkPrivateStyleLoader).load(_StructuralStylesLoader);
    if (this._elementRef.nativeElement.nodeName === "BUTTON") {
      this._elementRef.nativeElement.setAttribute("type", "button");
    }
  }
  focus() {
    this._elementRef.nativeElement.focus();
  }
  _handleClick(event) {
    if (!this.disabled && this.isInteractive && this._isPrimary) {
      event.preventDefault();
      this._parentChip._handlePrimaryActionInteraction();
    }
  }
  _handleKeydown(event) {
    if ((event.keyCode === ENTER || event.keyCode === SPACE) && !this.disabled && this.isInteractive && this._isPrimary && !this._parentChip._isEditing) {
      event.preventDefault();
      this._parentChip._handlePrimaryActionInteraction();
    }
  }
  static \u0275fac = function MatChipAction_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatChipAction)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatChipAction,
    selectors: [["", "matChipAction", ""]],
    hostAttrs: [1, "mdc-evolution-chip__action", "mat-mdc-chip-action"],
    hostVars: 11,
    hostBindings: function MatChipAction_HostBindings(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275listener("click", function MatChipAction_click_HostBindingHandler($event) {
          return ctx._handleClick($event);
        })("keydown", function MatChipAction_keydown_HostBindingHandler($event) {
          return ctx._handleKeydown($event);
        });
      }
      if (rf & 2) {
        \u0275\u0275attribute("tabindex", ctx._getTabindex())("disabled", ctx._getDisabledAttribute())("aria-disabled", ctx.disabled);
        \u0275\u0275classProp("mdc-evolution-chip__action--primary", ctx._isPrimary)("mdc-evolution-chip__action--presentational", !ctx.isInteractive)("mdc-evolution-chip__action--secondary", !ctx._isPrimary)("mdc-evolution-chip__action--trailing", !ctx._isPrimary && !ctx._isLeading);
      }
    },
    inputs: {
      isInteractive: "isInteractive",
      disabled: [2, "disabled", "disabled", booleanAttribute],
      tabIndex: [2, "tabIndex", "tabIndex", (value) => value == null ? -1 : numberAttribute(value)],
      _allowFocusWhenDisabled: "_allowFocusWhenDisabled"
    }
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipAction, [{
    type: Directive,
    args: [{
      selector: "[matChipAction]",
      host: {
        "class": "mdc-evolution-chip__action mat-mdc-chip-action",
        "[class.mdc-evolution-chip__action--primary]": "_isPrimary",
        "[class.mdc-evolution-chip__action--presentational]": "!isInteractive",
        "[class.mdc-evolution-chip__action--secondary]": "!_isPrimary",
        "[class.mdc-evolution-chip__action--trailing]": "!_isPrimary && !_isLeading",
        "[attr.tabindex]": "_getTabindex()",
        "[attr.disabled]": "_getDisabledAttribute()",
        "[attr.aria-disabled]": "disabled",
        "(click)": "_handleClick($event)",
        "(keydown)": "_handleKeydown($event)"
      }
    }]
  }], () => [], {
    isInteractive: [{
      type: Input
    }],
    disabled: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    tabIndex: [{
      type: Input,
      args: [{
        transform: (value) => value == null ? -1 : numberAttribute(value)
      }]
    }],
    _allowFocusWhenDisabled: [{
      type: Input
    }]
  });
})();
var MatChipAvatar = class _MatChipAvatar {
  static \u0275fac = function MatChipAvatar_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatChipAvatar)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatChipAvatar,
    selectors: [["mat-chip-avatar"], ["", "matChipAvatar", ""]],
    hostAttrs: ["role", "img", 1, "mat-mdc-chip-avatar", "mdc-evolution-chip__icon", "mdc-evolution-chip__icon--primary"],
    features: [\u0275\u0275ProvidersFeature([{
      provide: MAT_CHIP_AVATAR,
      useExisting: _MatChipAvatar
    }])]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipAvatar, [{
    type: Directive,
    args: [{
      selector: "mat-chip-avatar, [matChipAvatar]",
      host: {
        "class": "mat-mdc-chip-avatar mdc-evolution-chip__icon mdc-evolution-chip__icon--primary",
        "role": "img"
      },
      providers: [{
        provide: MAT_CHIP_AVATAR,
        useExisting: MatChipAvatar
      }]
    }]
  }], null, null);
})();
var MatChipTrailingIcon = class _MatChipTrailingIcon extends MatChipAction {
  /**
   * MDC considers all trailing actions as a remove icon,
   * but we support non-interactive trailing icons.
   */
  isInteractive = false;
  _isPrimary = false;
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatChipTrailingIcon_BaseFactory;
    return function MatChipTrailingIcon_Factory(__ngFactoryType__) {
      return (\u0275MatChipTrailingIcon_BaseFactory || (\u0275MatChipTrailingIcon_BaseFactory = \u0275\u0275getInheritedFactory(_MatChipTrailingIcon)))(__ngFactoryType__ || _MatChipTrailingIcon);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatChipTrailingIcon,
    selectors: [["mat-chip-trailing-icon"], ["", "matChipTrailingIcon", ""]],
    hostAttrs: ["aria-hidden", "true", 1, "mat-mdc-chip-trailing-icon", "mdc-evolution-chip__icon", "mdc-evolution-chip__icon--trailing"],
    features: [\u0275\u0275ProvidersFeature([{
      provide: MAT_CHIP_TRAILING_ICON,
      useExisting: _MatChipTrailingIcon
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipTrailingIcon, [{
    type: Directive,
    args: [{
      selector: "mat-chip-trailing-icon, [matChipTrailingIcon]",
      host: {
        "class": "mat-mdc-chip-trailing-icon mdc-evolution-chip__icon mdc-evolution-chip__icon--trailing",
        "aria-hidden": "true"
      },
      providers: [{
        provide: MAT_CHIP_TRAILING_ICON,
        useExisting: MatChipTrailingIcon
      }]
    }]
  }], null, null);
})();
var MatChipEdit = class _MatChipEdit extends MatChipAction {
  _isPrimary = false;
  _isLeading = true;
  _handleClick(event) {
    if (!this.disabled) {
      event.stopPropagation();
      event.preventDefault();
      this._parentChip._edit();
    }
  }
  _handleKeydown(event) {
    if ((event.keyCode === ENTER || event.keyCode === SPACE) && !this.disabled) {
      event.stopPropagation();
      event.preventDefault();
      this._parentChip._edit();
    }
  }
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatChipEdit_BaseFactory;
    return function MatChipEdit_Factory(__ngFactoryType__) {
      return (\u0275MatChipEdit_BaseFactory || (\u0275MatChipEdit_BaseFactory = \u0275\u0275getInheritedFactory(_MatChipEdit)))(__ngFactoryType__ || _MatChipEdit);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatChipEdit,
    selectors: [["", "matChipEdit", ""]],
    hostAttrs: ["role", "button", 1, "mat-mdc-chip-edit", "mat-mdc-chip-avatar", "mat-focus-indicator", "mdc-evolution-chip__icon", "mdc-evolution-chip__icon--primary"],
    hostVars: 1,
    hostBindings: function MatChipEdit_HostBindings(rf, ctx) {
      if (rf & 2) {
        \u0275\u0275attribute("aria-hidden", null);
      }
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: MAT_CHIP_EDIT,
      useExisting: _MatChipEdit
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipEdit, [{
    type: Directive,
    args: [{
      selector: "[matChipEdit]",
      host: {
        "class": "mat-mdc-chip-edit mat-mdc-chip-avatar mat-focus-indicator mdc-evolution-chip__icon mdc-evolution-chip__icon--primary",
        "role": "button",
        "[attr.aria-hidden]": "null"
      },
      providers: [{
        provide: MAT_CHIP_EDIT,
        useExisting: MatChipEdit
      }]
    }]
  }], null, null);
})();
var MatChipRemove = class _MatChipRemove extends MatChipAction {
  _isPrimary = false;
  _handleClick(event) {
    if (!this.disabled) {
      event.stopPropagation();
      event.preventDefault();
      this._parentChip.remove();
    }
  }
  _handleKeydown(event) {
    if ((event.keyCode === ENTER || event.keyCode === SPACE) && !this.disabled) {
      event.stopPropagation();
      event.preventDefault();
      this._parentChip.remove();
    }
  }
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatChipRemove_BaseFactory;
    return function MatChipRemove_Factory(__ngFactoryType__) {
      return (\u0275MatChipRemove_BaseFactory || (\u0275MatChipRemove_BaseFactory = \u0275\u0275getInheritedFactory(_MatChipRemove)))(__ngFactoryType__ || _MatChipRemove);
    };
  })();
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatChipRemove,
    selectors: [["", "matChipRemove", ""]],
    hostAttrs: ["role", "button", 1, "mat-mdc-chip-remove", "mat-mdc-chip-trailing-icon", "mat-focus-indicator", "mdc-evolution-chip__icon", "mdc-evolution-chip__icon--trailing"],
    hostVars: 1,
    hostBindings: function MatChipRemove_HostBindings(rf, ctx) {
      if (rf & 2) {
        \u0275\u0275attribute("aria-hidden", null);
      }
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: MAT_CHIP_REMOVE,
      useExisting: _MatChipRemove
    }]), \u0275\u0275InheritDefinitionFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipRemove, [{
    type: Directive,
    args: [{
      selector: "[matChipRemove]",
      host: {
        "class": "mat-mdc-chip-remove mat-mdc-chip-trailing-icon mat-focus-indicator mdc-evolution-chip__icon mdc-evolution-chip__icon--trailing",
        "role": "button",
        "[attr.aria-hidden]": "null"
      },
      providers: [{
        provide: MAT_CHIP_REMOVE,
        useExisting: MatChipRemove
      }]
    }]
  }], null, null);
})();
var MatChip = class _MatChip {
  _changeDetectorRef = inject(ChangeDetectorRef);
  _elementRef = inject(ElementRef);
  _tagName = inject(HOST_TAG_NAME);
  _ngZone = inject(NgZone);
  _focusMonitor = inject(FocusMonitor);
  _globalRippleOptions = inject(MAT_RIPPLE_GLOBAL_OPTIONS, {
    optional: true
  });
  _document = inject(DOCUMENT);
  /** Emits when the chip is focused. */
  _onFocus = new Subject();
  /** Emits when the chip is blurred. */
  _onBlur = new Subject();
  /** Whether this chip is a basic (unstyled) chip. */
  _isBasicChip;
  /** Role for the root of the chip. */
  role = null;
  /** Whether the chip has focus. */
  _hasFocusInternal = false;
  /** Whether moving focus into the chip is pending. */
  _pendingFocus;
  /** Subscription to changes in the chip's actions. */
  _actionChanges;
  /** Whether animations for the chip are enabled. */
  _animationsDisabled = _animationsDisabled();
  /** All avatars present in the chip. */
  _allLeadingIcons;
  /** All trailing icons present in the chip. */
  _allTrailingIcons;
  /** All edit icons present in the chip. */
  _allEditIcons;
  /** All remove icons present in the chip. */
  _allRemoveIcons;
  _hasFocus() {
    return this._hasFocusInternal;
  }
  /** A unique id for the chip. If none is supplied, it will be auto-generated. */
  id = inject(_IdGenerator).getId("mat-mdc-chip-");
  // TODO(#26104): Consider deprecating and using `_computeAriaAccessibleName` instead.
  // `ariaLabel` may be unnecessary, and `_computeAriaAccessibleName` only supports
  // datepicker's use case.
  /** ARIA label for the content of the chip. */
  ariaLabel = null;
  // TODO(#26104): Consider deprecating and using `_computeAriaAccessibleName` instead.
  // `ariaDescription` may be unnecessary, and `_computeAriaAccessibleName` only supports
  // datepicker's use case.
  /** ARIA description for the content of the chip. */
  ariaDescription = null;
  /** Whether the chip list is disabled. */
  _chipListDisabled = false;
  /** Whether the chip was focused when it was removed. */
  _hadFocusOnRemove = false;
  _textElement;
  /**
   * The value of the chip. Defaults to the content inside
   * the `mat-mdc-chip-action-label` element.
   */
  get value() {
    return this._value !== void 0 ? this._value : this._textElement.textContent.trim();
  }
  set value(value) {
    this._value = value;
  }
  _value;
  // TODO: should be typed as `ThemePalette` but internal apps pass in arbitrary strings.
  /**
   * Theme color of the chip. This API is supported in M2 themes only, it has no
   * effect in M3 themes. For color customization in M3, see https://material.angular.dev/components/chips/styling.
   *
   * For information on applying color variants in M3, see
   * https://material.angular.dev/guide/material-2-theming#optional-add-backwards-compatibility-styles-for-color-variants
   */
  color;
  /**
   * Determines whether or not the chip displays the remove styling and emits (removed) events.
   */
  removable = true;
  /**
   * Colors the chip for emphasis as if it were selected.
   */
  highlighted = false;
  /** Whether the ripple effect is disabled or not. */
  disableRipple = false;
  /** Whether the chip is disabled. */
  get disabled() {
    return this._disabled || this._chipListDisabled;
  }
  set disabled(value) {
    this._disabled = value;
  }
  _disabled = false;
  /** Emitted when a chip is to be removed. */
  removed = new EventEmitter();
  /** Emitted when the chip is destroyed. */
  destroyed = new EventEmitter();
  /** The unstyled chip selector for this component. */
  basicChipAttrName = "mat-basic-chip";
  /** The chip's leading icon. */
  leadingIcon;
  /** The chip's leading edit icon. */
  editIcon;
  /** The chip's trailing icon. */
  trailingIcon;
  /** The chip's trailing remove icon. */
  removeIcon;
  /** Action receiving the primary set of user interactions. */
  primaryAction;
  /**
   * Handles the lazy creation of the MatChip ripple.
   * Used to improve initial load time of large applications.
   */
  _rippleLoader = inject(MatRippleLoader);
  _injector = inject(Injector);
  constructor() {
    const styleLoader = inject(_CdkPrivateStyleLoader);
    styleLoader.load(_StructuralStylesLoader);
    styleLoader.load(_VisuallyHiddenLoader);
    this._monitorFocus();
    this._rippleLoader?.configureRipple(this._elementRef.nativeElement, {
      className: "mat-mdc-chip-ripple",
      disabled: this._isRippleDisabled()
    });
  }
  ngOnInit() {
    this._isBasicChip = this._elementRef.nativeElement.hasAttribute(this.basicChipAttrName) || this._tagName.toLowerCase() === this.basicChipAttrName;
  }
  ngAfterViewInit() {
    this._textElement = this._elementRef.nativeElement.querySelector(".mat-mdc-chip-action-label");
    if (this._pendingFocus) {
      this._pendingFocus = false;
      this.focus();
    }
  }
  ngAfterContentInit() {
    this._actionChanges = merge(this._allLeadingIcons.changes, this._allTrailingIcons.changes, this._allEditIcons.changes, this._allRemoveIcons.changes).subscribe(() => this._changeDetectorRef.markForCheck());
  }
  ngDoCheck() {
    this._rippleLoader.setDisabled(this._elementRef.nativeElement, this._isRippleDisabled());
  }
  ngOnDestroy() {
    this._focusMonitor.stopMonitoring(this._elementRef);
    this._rippleLoader?.destroyRipple(this._elementRef.nativeElement);
    this._actionChanges?.unsubscribe();
    this.destroyed.emit({
      chip: this
    });
    this.destroyed.complete();
  }
  /**
   * Allows for programmatic removal of the chip.
   *
   * Informs any listeners of the removal request. Does not remove the chip from the DOM.
   */
  remove() {
    if (this.removable) {
      this._hadFocusOnRemove = this._hasFocus();
      this.removed.emit({
        chip: this
      });
    }
  }
  /** Whether or not the ripple should be disabled. */
  _isRippleDisabled() {
    return this.disabled || this.disableRipple || this._animationsDisabled || this._isBasicChip || !this._hasInteractiveActions() || !!this._globalRippleOptions?.disabled;
  }
  /** Returns whether the chip has a trailing icon. */
  _hasTrailingIcon() {
    return !!(this.trailingIcon || this.removeIcon);
  }
  /** Handles keyboard events on the chip. */
  _handleKeydown(event) {
    if (event.keyCode === BACKSPACE && !event.repeat || event.keyCode === DELETE) {
      event.preventDefault();
      this.remove();
    }
  }
  /** Allows for programmatic focusing of the chip. */
  focus() {
    if (!this.disabled) {
      if (this.primaryAction) {
        this.primaryAction.focus();
      } else {
        this._pendingFocus = true;
      }
    }
  }
  /** Gets the action that contains a specific target node. */
  _getSourceAction(target) {
    return this._getActions().find((action) => {
      const element = action._elementRef.nativeElement;
      return element === target || element.contains(target);
    });
  }
  /** Gets all of the actions within the chip. */
  _getActions() {
    const result = [];
    if (this.editIcon) {
      result.push(this.editIcon);
    }
    if (this.primaryAction) {
      result.push(this.primaryAction);
    }
    if (this.removeIcon) {
      result.push(this.removeIcon);
    }
    if (this.trailingIcon) {
      result.push(this.trailingIcon);
    }
    return result;
  }
  /** Handles interactions with the primary action of the chip. */
  _handlePrimaryActionInteraction() {
  }
  /** Returns whether the chip has any interactive actions. */
  _hasInteractiveActions() {
    return this._getActions().some((a) => a.isInteractive);
  }
  /** Handles interactions with the edit action of the chip. */
  _edit(event) {
  }
  /** Starts the focus monitoring process on the chip. */
  _monitorFocus() {
    this._focusMonitor.monitor(this._elementRef, true).subscribe((origin) => {
      const hasFocus = origin !== null;
      if (hasFocus !== this._hasFocusInternal) {
        this._hasFocusInternal = hasFocus;
        if (hasFocus) {
          this._onFocus.next({
            chip: this
          });
        } else {
          this._changeDetectorRef.markForCheck();
          setTimeout(() => this._ngZone.run(() => this._onBlur.next({
            chip: this
          })));
        }
      }
    });
  }
  static \u0275fac = function MatChip_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatChip)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatChip,
    selectors: [["mat-basic-chip"], ["", "mat-basic-chip", ""], ["mat-chip"], ["", "mat-chip", ""]],
    contentQueries: function MatChip_ContentQueries(rf, ctx, dirIndex) {
      if (rf & 1) {
        \u0275\u0275contentQuery(dirIndex, MAT_CHIP_AVATAR, 5);
        \u0275\u0275contentQuery(dirIndex, MAT_CHIP_EDIT, 5);
        \u0275\u0275contentQuery(dirIndex, MAT_CHIP_TRAILING_ICON, 5);
        \u0275\u0275contentQuery(dirIndex, MAT_CHIP_REMOVE, 5);
        \u0275\u0275contentQuery(dirIndex, MAT_CHIP_AVATAR, 5);
        \u0275\u0275contentQuery(dirIndex, MAT_CHIP_TRAILING_ICON, 5);
        \u0275\u0275contentQuery(dirIndex, MAT_CHIP_EDIT, 5);
        \u0275\u0275contentQuery(dirIndex, MAT_CHIP_REMOVE, 5);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.leadingIcon = _t.first);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.editIcon = _t.first);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.trailingIcon = _t.first);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.removeIcon = _t.first);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._allLeadingIcons = _t);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._allTrailingIcons = _t);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._allEditIcons = _t);
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._allRemoveIcons = _t);
      }
    },
    viewQuery: function MatChip_Query(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275viewQuery(MatChipAction, 5);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.primaryAction = _t.first);
      }
    },
    hostAttrs: [1, "mat-mdc-chip"],
    hostVars: 31,
    hostBindings: function MatChip_HostBindings(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275listener("keydown", function MatChip_keydown_HostBindingHandler($event) {
          return ctx._handleKeydown($event);
        });
      }
      if (rf & 2) {
        \u0275\u0275domProperty("id", ctx.id);
        \u0275\u0275attribute("role", ctx.role)("aria-label", ctx.ariaLabel);
        \u0275\u0275classMap("mat-" + (ctx.color || "primary"));
        \u0275\u0275classProp("mdc-evolution-chip", !ctx._isBasicChip)("mdc-evolution-chip--disabled", ctx.disabled)("mdc-evolution-chip--with-trailing-action", ctx._hasTrailingIcon())("mdc-evolution-chip--with-primary-graphic", ctx.leadingIcon)("mdc-evolution-chip--with-primary-icon", ctx.leadingIcon)("mdc-evolution-chip--with-avatar", ctx.leadingIcon)("mat-mdc-chip-with-avatar", ctx.leadingIcon)("mat-mdc-chip-highlighted", ctx.highlighted)("mat-mdc-chip-disabled", ctx.disabled)("mat-mdc-basic-chip", ctx._isBasicChip)("mat-mdc-standard-chip", !ctx._isBasicChip)("mat-mdc-chip-with-trailing-icon", ctx._hasTrailingIcon())("_mat-animation-noopable", ctx._animationsDisabled);
      }
    },
    inputs: {
      role: "role",
      id: "id",
      ariaLabel: [0, "aria-label", "ariaLabel"],
      ariaDescription: [0, "aria-description", "ariaDescription"],
      value: "value",
      color: "color",
      removable: [2, "removable", "removable", booleanAttribute],
      highlighted: [2, "highlighted", "highlighted", booleanAttribute],
      disableRipple: [2, "disableRipple", "disableRipple", booleanAttribute],
      disabled: [2, "disabled", "disabled", booleanAttribute]
    },
    outputs: {
      removed: "removed",
      destroyed: "destroyed"
    },
    exportAs: ["matChip"],
    features: [\u0275\u0275ProvidersFeature([{
      provide: MAT_CHIP,
      useExisting: _MatChip
    }])],
    ngContentSelectors: _c13,
    decls: 8,
    vars: 3,
    consts: [[1, "mat-mdc-chip-focus-overlay"], [1, "mdc-evolution-chip__cell", "mdc-evolution-chip__cell--primary"], ["matChipAction", "", 3, "isInteractive"], [1, "mdc-evolution-chip__graphic", "mat-mdc-chip-graphic"], [1, "mdc-evolution-chip__text-label", "mat-mdc-chip-action-label"], [1, "mat-mdc-chip-primary-focus-indicator", "mat-focus-indicator"], [1, "mdc-evolution-chip__cell", "mdc-evolution-chip__cell--trailing"]],
    template: function MatChip_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275projectionDef(_c03);
        \u0275\u0275element(0, "span", 0);
        \u0275\u0275elementStart(1, "span", 1)(2, "span", 2);
        \u0275\u0275conditionalCreate(3, MatChip_Conditional_3_Template, 2, 0, "span", 3);
        \u0275\u0275elementStart(4, "span", 4);
        \u0275\u0275projection(5);
        \u0275\u0275element(6, "span", 5);
        \u0275\u0275elementEnd()()();
        \u0275\u0275conditionalCreate(7, MatChip_Conditional_7_Template, 2, 0, "span", 6);
      }
      if (rf & 2) {
        \u0275\u0275advance(2);
        \u0275\u0275property("isInteractive", false);
        \u0275\u0275advance();
        \u0275\u0275conditional(ctx.leadingIcon ? 3 : -1);
        \u0275\u0275advance(4);
        \u0275\u0275conditional(ctx._hasTrailingIcon() ? 7 : -1);
      }
    },
    dependencies: [MatChipAction],
    styles: ['.mdc-evolution-chip,.mdc-evolution-chip__cell,.mdc-evolution-chip__action{display:inline-flex;align-items:center}.mdc-evolution-chip{position:relative;max-width:100%}.mdc-evolution-chip__cell,.mdc-evolution-chip__action{height:100%}.mdc-evolution-chip__cell--primary{flex-basis:100%;overflow-x:hidden}.mdc-evolution-chip__cell--trailing{flex:1 0 auto}.mdc-evolution-chip__action{align-items:center;background:none;border:none;box-sizing:content-box;cursor:pointer;display:inline-flex;justify-content:center;outline:none;padding:0;text-decoration:none;color:inherit}.mdc-evolution-chip__action--presentational{cursor:auto}.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{pointer-events:none}@media(forced-colors: active){.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{forced-color-adjust:none}}.mdc-evolution-chip__action--primary{font:inherit;letter-spacing:inherit;white-space:inherit;overflow-x:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-outline-width, 1px);border-radius:var(--mat-chip-container-shape-radius, 8px);box-sizing:border-box;content:"";height:100%;left:0;position:absolute;pointer-events:none;top:0;width:100%;z-index:1;border-style:solid}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-outline-color, var(--mat-sys-outline))}.mdc-evolution-chip__action--primary:not(.mdc-evolution-chip__action--presentational):not(.mdc-ripple-upgraded):focus::before{border-color:var(--mat-chip-focus-outline-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-disabled-outline-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-basic-chip .mdc-evolution-chip__action--primary{font:inherit}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip__action--secondary{position:relative;overflow:visible}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-trailing-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip__text-label{-webkit-user-select:none;user-select:none;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__text-label{font-family:var(--mat-chip-label-text-font, var(--mat-sys-label-large-font));line-height:var(--mat-chip-label-text-line-height, var(--mat-sys-label-large-line-height));font-size:var(--mat-chip-label-text-size, var(--mat-sys-label-large-size));font-weight:var(--mat-chip-label-text-weight, var(--mat-sys-label-large-weight));letter-spacing:var(--mat-chip-label-text-tracking, var(--mat-sys-label-large-tracking))}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-label-text-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label,.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label{color:var(--mat-chip-disabled-label-text-color, color-mix(in srgb, var(--mat-sys-on-surface) 38%, transparent))}.mdc-evolution-chip__graphic{align-items:center;display:inline-flex;justify-content:center;overflow:hidden;pointer-events:none;position:relative;flex:1 0 auto}.mat-mdc-standard-chip .mdc-evolution-chip__graphic{width:var(--mat-chip-with-avatar-avatar-size, 24px);height:var(--mat-chip-with-avatar-avatar-size, 24px);font-size:var(--mat-chip-with-avatar-avatar-size, 24px)}.mdc-evolution-chip--selecting .mdc-evolution-chip__graphic{transition:width 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selectable:not(.mdc-evolution-chip--selected):not(.mdc-evolution-chip--with-primary-icon) .mdc-evolution-chip__graphic{width:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__graphic{padding-left:0}.mdc-evolution-chip__checkmark{position:absolute;opacity:0;top:50%;left:50%;height:20px;width:20px}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark{transition:transform 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1);transform:translate(-75%, -50%)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{transform:translate(-50%, -50%);opacity:1}.mdc-evolution-chip__checkmark-svg{display:block}.mdc-evolution-chip__checkmark-path{stroke-width:2px;stroke-dasharray:29.7833385;stroke-dashoffset:29.7833385;stroke:currentColor}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark-path{transition:stroke-dashoffset 150ms 45ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark-path{stroke-dashoffset:0}@media(forced-colors: active){.mdc-evolution-chip__checkmark-path{stroke:CanvasText !important}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--trailing{height:18px;width:18px;font-size:18px}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove{opacity:calc(var(--mat-chip-trailing-action-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove:focus{opacity:calc(var(--mat-chip-trailing-action-focus-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mat-mdc-standard-chip{border-radius:var(--mat-chip-container-shape-radius, 8px);height:var(--mat-chip-container-height, 32px)}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-container-color, transparent)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{background-color:var(--mat-chip-elevated-disabled-container-color)}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled{background-color:var(--mat-chip-flat-disabled-selected-container-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}@media(forced-colors: active){.mat-mdc-standard-chip{outline:solid 1px}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--primary{border-radius:var(--mat-chip-with-avatar-avatar-shape-radius, 24px);width:var(--mat-chip-with-icon-icon-size, 18px);height:var(--mat-chip-with-icon-icon-size, 18px);font-size:var(--mat-chip-with-icon-icon-size, 18px)}.mdc-evolution-chip--selected .mdc-evolution-chip__icon--primary{opacity:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-highlighted{--mat-chip-with-icon-icon-color: var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container));--mat-chip-elevated-container-color: var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container));--mat-chip-label-text-color: var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container));--mat-chip-outline-width: var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-selected .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-chip:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-hover-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-focus-overlay .mat-mdc-chip-selected:hover,.mat-mdc-chip-highlighted:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-hover-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected.cdk-focused .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mdc-evolution-chip--disabled:not(.mdc-evolution-chip--selected) .mat-mdc-chip-avatar{opacity:var(--mat-chip-with-avatar-disabled-avatar-opacity, 0.38)}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{opacity:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38)}.mdc-evolution-chip--disabled.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{opacity:var(--mat-chip-with-icon-disabled-icon-opacity, 0.38)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{opacity:var(--mat-chip-disabled-container-opacity, 1)}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-trailing-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-edit,.mat-mdc-chip-remove{opacity:var(--mat-chip-trailing-action-opacity, 1)}.mat-mdc-chip-edit:focus,.mat-mdc-chip-remove:focus{opacity:var(--mat-chip-trailing-action-focus-opacity, 1)}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{background-color:var(--mat-chip-trailing-action-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-edit:hover::after,.mat-mdc-chip-remove:hover::after{opacity:var(--mat-chip-trailing-action-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-edit:focus::after,.mat-mdc-chip-remove:focus::after{opacity:var(--mat-chip-trailing-action-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected .mat-mdc-chip-remove::after,.mat-mdc-chip-highlighted .mat-mdc-chip-remove::after{background-color:var(--mat-chip-selected-trailing-action-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip{-webkit-tap-highlight-color:rgba(0,0,0,0)}.mat-mdc-standard-chip .mdc-evolution-chip__cell--primary,.mat-mdc-standard-chip .mdc-evolution-chip__action--primary,.mat-mdc-standard-chip .mat-mdc-chip-action-label{overflow:visible}.mat-mdc-standard-chip .mat-mdc-chip-graphic,.mat-mdc-standard-chip .mat-mdc-chip-trailing-icon{box-sizing:content-box}.mat-mdc-standard-chip._mat-animation-noopable,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__graphic,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark-path{transition-duration:1ms;animation-duration:1ms}.mat-mdc-chip-focus-overlay{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;opacity:0;border-radius:inherit;transition:opacity 150ms linear}._mat-animation-noopable .mat-mdc-chip-focus-overlay{transition:none}.mat-mdc-basic-chip .mat-mdc-chip-focus-overlay{display:none}.mat-mdc-chip .mat-ripple.mat-mdc-chip-ripple{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;border-radius:inherit}.mat-mdc-chip-avatar{text-align:center;line-height:1;color:var(--mat-chip-with-icon-icon-color, currentColor)}.mat-mdc-chip{position:relative;z-index:0}.mat-mdc-chip-action-label{text-align:left;z-index:1}[dir=rtl] .mat-mdc-chip-action-label{text-align:right}.mat-mdc-chip.mdc-evolution-chip--with-trailing-action .mat-mdc-chip-action-label{position:relative}.mat-mdc-chip-action-label .mat-mdc-chip-primary-focus-indicator{position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none}.mat-mdc-chip-action-label .mat-focus-indicator::before{margin:calc(calc(var(--mat-focus-indicator-border-width, 3px) + 2px)*-1)}.mat-mdc-chip-edit::before,.mat-mdc-chip-remove::before{margin:calc(var(--mat-focus-indicator-border-width, 3px)*-1);left:8px;right:8px}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{content:"";display:block;opacity:0;position:absolute;top:-3px;bottom:-3px;left:5px;right:5px;border-radius:50%;box-sizing:border-box;padding:12px;margin:-12px;background-clip:content-box}.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{width:18px;height:18px;font-size:18px;box-sizing:content-box}.mat-chip-edit-input{cursor:text;display:inline-block;color:inherit;outline:0}@media(forced-colors: active){.mat-mdc-chip-selected:not(.mat-mdc-chip-multiple){outline-width:3px}}.mat-mdc-chip-action:focus .mat-focus-indicator::before{content:""}.mdc-evolution-chip__icon,.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{min-height:fit-content}img.mdc-evolution-chip__icon{min-height:0}\n'],
    encapsulation: 2,
    changeDetection: 0
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChip, [{
    type: Component,
    args: [{
      selector: "mat-basic-chip, [mat-basic-chip], mat-chip, [mat-chip]",
      exportAs: "matChip",
      host: {
        "class": "mat-mdc-chip",
        "[class]": '"mat-" + (color || "primary")',
        "[class.mdc-evolution-chip]": "!_isBasicChip",
        "[class.mdc-evolution-chip--disabled]": "disabled",
        "[class.mdc-evolution-chip--with-trailing-action]": "_hasTrailingIcon()",
        "[class.mdc-evolution-chip--with-primary-graphic]": "leadingIcon",
        "[class.mdc-evolution-chip--with-primary-icon]": "leadingIcon",
        "[class.mdc-evolution-chip--with-avatar]": "leadingIcon",
        "[class.mat-mdc-chip-with-avatar]": "leadingIcon",
        "[class.mat-mdc-chip-highlighted]": "highlighted",
        "[class.mat-mdc-chip-disabled]": "disabled",
        "[class.mat-mdc-basic-chip]": "_isBasicChip",
        "[class.mat-mdc-standard-chip]": "!_isBasicChip",
        "[class.mat-mdc-chip-with-trailing-icon]": "_hasTrailingIcon()",
        "[class._mat-animation-noopable]": "_animationsDisabled",
        "[id]": "id",
        "[attr.role]": "role",
        "[attr.aria-label]": "ariaLabel",
        "(keydown)": "_handleKeydown($event)"
      },
      encapsulation: ViewEncapsulation.None,
      changeDetection: ChangeDetectionStrategy.OnPush,
      providers: [{
        provide: MAT_CHIP,
        useExisting: MatChip
      }],
      imports: [MatChipAction],
      template: '<span class="mat-mdc-chip-focus-overlay"></span>\n\n<span class="mdc-evolution-chip__cell mdc-evolution-chip__cell--primary">\n  <span matChipAction [isInteractive]="false">\n    @if (leadingIcon) {\n      <span class="mdc-evolution-chip__graphic mat-mdc-chip-graphic">\n        <ng-content select="mat-chip-avatar, [matChipAvatar]"></ng-content>\n      </span>\n    }\n    <span class="mdc-evolution-chip__text-label mat-mdc-chip-action-label">\n      <ng-content></ng-content>\n      <span class="mat-mdc-chip-primary-focus-indicator mat-focus-indicator"></span>\n    </span>\n  </span>\n</span>\n\n@if (_hasTrailingIcon()) {\n  <span class="mdc-evolution-chip__cell mdc-evolution-chip__cell--trailing">\n    <ng-content select="mat-chip-trailing-icon,[matChipRemove],[matChipTrailingIcon]"></ng-content>\n  </span>\n}\n',
      styles: ['.mdc-evolution-chip,.mdc-evolution-chip__cell,.mdc-evolution-chip__action{display:inline-flex;align-items:center}.mdc-evolution-chip{position:relative;max-width:100%}.mdc-evolution-chip__cell,.mdc-evolution-chip__action{height:100%}.mdc-evolution-chip__cell--primary{flex-basis:100%;overflow-x:hidden}.mdc-evolution-chip__cell--trailing{flex:1 0 auto}.mdc-evolution-chip__action{align-items:center;background:none;border:none;box-sizing:content-box;cursor:pointer;display:inline-flex;justify-content:center;outline:none;padding:0;text-decoration:none;color:inherit}.mdc-evolution-chip__action--presentational{cursor:auto}.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{pointer-events:none}@media(forced-colors: active){.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{forced-color-adjust:none}}.mdc-evolution-chip__action--primary{font:inherit;letter-spacing:inherit;white-space:inherit;overflow-x:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-outline-width, 1px);border-radius:var(--mat-chip-container-shape-radius, 8px);box-sizing:border-box;content:"";height:100%;left:0;position:absolute;pointer-events:none;top:0;width:100%;z-index:1;border-style:solid}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-outline-color, var(--mat-sys-outline))}.mdc-evolution-chip__action--primary:not(.mdc-evolution-chip__action--presentational):not(.mdc-ripple-upgraded):focus::before{border-color:var(--mat-chip-focus-outline-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-disabled-outline-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-basic-chip .mdc-evolution-chip__action--primary{font:inherit}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip__action--secondary{position:relative;overflow:visible}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-trailing-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip__text-label{-webkit-user-select:none;user-select:none;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__text-label{font-family:var(--mat-chip-label-text-font, var(--mat-sys-label-large-font));line-height:var(--mat-chip-label-text-line-height, var(--mat-sys-label-large-line-height));font-size:var(--mat-chip-label-text-size, var(--mat-sys-label-large-size));font-weight:var(--mat-chip-label-text-weight, var(--mat-sys-label-large-weight));letter-spacing:var(--mat-chip-label-text-tracking, var(--mat-sys-label-large-tracking))}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-label-text-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label,.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label{color:var(--mat-chip-disabled-label-text-color, color-mix(in srgb, var(--mat-sys-on-surface) 38%, transparent))}.mdc-evolution-chip__graphic{align-items:center;display:inline-flex;justify-content:center;overflow:hidden;pointer-events:none;position:relative;flex:1 0 auto}.mat-mdc-standard-chip .mdc-evolution-chip__graphic{width:var(--mat-chip-with-avatar-avatar-size, 24px);height:var(--mat-chip-with-avatar-avatar-size, 24px);font-size:var(--mat-chip-with-avatar-avatar-size, 24px)}.mdc-evolution-chip--selecting .mdc-evolution-chip__graphic{transition:width 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selectable:not(.mdc-evolution-chip--selected):not(.mdc-evolution-chip--with-primary-icon) .mdc-evolution-chip__graphic{width:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__graphic{padding-left:0}.mdc-evolution-chip__checkmark{position:absolute;opacity:0;top:50%;left:50%;height:20px;width:20px}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark{transition:transform 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1);transform:translate(-75%, -50%)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{transform:translate(-50%, -50%);opacity:1}.mdc-evolution-chip__checkmark-svg{display:block}.mdc-evolution-chip__checkmark-path{stroke-width:2px;stroke-dasharray:29.7833385;stroke-dashoffset:29.7833385;stroke:currentColor}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark-path{transition:stroke-dashoffset 150ms 45ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark-path{stroke-dashoffset:0}@media(forced-colors: active){.mdc-evolution-chip__checkmark-path{stroke:CanvasText !important}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--trailing{height:18px;width:18px;font-size:18px}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove{opacity:calc(var(--mat-chip-trailing-action-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove:focus{opacity:calc(var(--mat-chip-trailing-action-focus-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mat-mdc-standard-chip{border-radius:var(--mat-chip-container-shape-radius, 8px);height:var(--mat-chip-container-height, 32px)}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-container-color, transparent)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{background-color:var(--mat-chip-elevated-disabled-container-color)}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled{background-color:var(--mat-chip-flat-disabled-selected-container-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}@media(forced-colors: active){.mat-mdc-standard-chip{outline:solid 1px}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--primary{border-radius:var(--mat-chip-with-avatar-avatar-shape-radius, 24px);width:var(--mat-chip-with-icon-icon-size, 18px);height:var(--mat-chip-with-icon-icon-size, 18px);font-size:var(--mat-chip-with-icon-icon-size, 18px)}.mdc-evolution-chip--selected .mdc-evolution-chip__icon--primary{opacity:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-highlighted{--mat-chip-with-icon-icon-color: var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container));--mat-chip-elevated-container-color: var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container));--mat-chip-label-text-color: var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container));--mat-chip-outline-width: var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-selected .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-chip:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-hover-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-focus-overlay .mat-mdc-chip-selected:hover,.mat-mdc-chip-highlighted:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-hover-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected.cdk-focused .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mdc-evolution-chip--disabled:not(.mdc-evolution-chip--selected) .mat-mdc-chip-avatar{opacity:var(--mat-chip-with-avatar-disabled-avatar-opacity, 0.38)}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{opacity:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38)}.mdc-evolution-chip--disabled.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{opacity:var(--mat-chip-with-icon-disabled-icon-opacity, 0.38)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{opacity:var(--mat-chip-disabled-container-opacity, 1)}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-trailing-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-edit,.mat-mdc-chip-remove{opacity:var(--mat-chip-trailing-action-opacity, 1)}.mat-mdc-chip-edit:focus,.mat-mdc-chip-remove:focus{opacity:var(--mat-chip-trailing-action-focus-opacity, 1)}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{background-color:var(--mat-chip-trailing-action-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-edit:hover::after,.mat-mdc-chip-remove:hover::after{opacity:var(--mat-chip-trailing-action-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-edit:focus::after,.mat-mdc-chip-remove:focus::after{opacity:var(--mat-chip-trailing-action-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected .mat-mdc-chip-remove::after,.mat-mdc-chip-highlighted .mat-mdc-chip-remove::after{background-color:var(--mat-chip-selected-trailing-action-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip{-webkit-tap-highlight-color:rgba(0,0,0,0)}.mat-mdc-standard-chip .mdc-evolution-chip__cell--primary,.mat-mdc-standard-chip .mdc-evolution-chip__action--primary,.mat-mdc-standard-chip .mat-mdc-chip-action-label{overflow:visible}.mat-mdc-standard-chip .mat-mdc-chip-graphic,.mat-mdc-standard-chip .mat-mdc-chip-trailing-icon{box-sizing:content-box}.mat-mdc-standard-chip._mat-animation-noopable,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__graphic,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark-path{transition-duration:1ms;animation-duration:1ms}.mat-mdc-chip-focus-overlay{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;opacity:0;border-radius:inherit;transition:opacity 150ms linear}._mat-animation-noopable .mat-mdc-chip-focus-overlay{transition:none}.mat-mdc-basic-chip .mat-mdc-chip-focus-overlay{display:none}.mat-mdc-chip .mat-ripple.mat-mdc-chip-ripple{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;border-radius:inherit}.mat-mdc-chip-avatar{text-align:center;line-height:1;color:var(--mat-chip-with-icon-icon-color, currentColor)}.mat-mdc-chip{position:relative;z-index:0}.mat-mdc-chip-action-label{text-align:left;z-index:1}[dir=rtl] .mat-mdc-chip-action-label{text-align:right}.mat-mdc-chip.mdc-evolution-chip--with-trailing-action .mat-mdc-chip-action-label{position:relative}.mat-mdc-chip-action-label .mat-mdc-chip-primary-focus-indicator{position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none}.mat-mdc-chip-action-label .mat-focus-indicator::before{margin:calc(calc(var(--mat-focus-indicator-border-width, 3px) + 2px)*-1)}.mat-mdc-chip-edit::before,.mat-mdc-chip-remove::before{margin:calc(var(--mat-focus-indicator-border-width, 3px)*-1);left:8px;right:8px}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{content:"";display:block;opacity:0;position:absolute;top:-3px;bottom:-3px;left:5px;right:5px;border-radius:50%;box-sizing:border-box;padding:12px;margin:-12px;background-clip:content-box}.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{width:18px;height:18px;font-size:18px;box-sizing:content-box}.mat-chip-edit-input{cursor:text;display:inline-block;color:inherit;outline:0}@media(forced-colors: active){.mat-mdc-chip-selected:not(.mat-mdc-chip-multiple){outline-width:3px}}.mat-mdc-chip-action:focus .mat-focus-indicator::before{content:""}.mdc-evolution-chip__icon,.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{min-height:fit-content}img.mdc-evolution-chip__icon{min-height:0}\n']
    }]
  }], () => [], {
    role: [{
      type: Input
    }],
    _allLeadingIcons: [{
      type: ContentChildren,
      args: [MAT_CHIP_AVATAR, {
        descendants: true
      }]
    }],
    _allTrailingIcons: [{
      type: ContentChildren,
      args: [MAT_CHIP_TRAILING_ICON, {
        descendants: true
      }]
    }],
    _allEditIcons: [{
      type: ContentChildren,
      args: [MAT_CHIP_EDIT, {
        descendants: true
      }]
    }],
    _allRemoveIcons: [{
      type: ContentChildren,
      args: [MAT_CHIP_REMOVE, {
        descendants: true
      }]
    }],
    id: [{
      type: Input
    }],
    ariaLabel: [{
      type: Input,
      args: ["aria-label"]
    }],
    ariaDescription: [{
      type: Input,
      args: ["aria-description"]
    }],
    value: [{
      type: Input
    }],
    color: [{
      type: Input
    }],
    removable: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    highlighted: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    disableRipple: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    disabled: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    removed: [{
      type: Output
    }],
    destroyed: [{
      type: Output
    }],
    leadingIcon: [{
      type: ContentChild,
      args: [MAT_CHIP_AVATAR]
    }],
    editIcon: [{
      type: ContentChild,
      args: [MAT_CHIP_EDIT]
    }],
    trailingIcon: [{
      type: ContentChild,
      args: [MAT_CHIP_TRAILING_ICON]
    }],
    removeIcon: [{
      type: ContentChild,
      args: [MAT_CHIP_REMOVE]
    }],
    primaryAction: [{
      type: ViewChild,
      args: [MatChipAction]
    }]
  });
})();
var MatChipOption = class _MatChipOption extends MatChip {
  /** Default chip options. */
  _defaultOptions = inject(MAT_CHIPS_DEFAULT_OPTIONS, {
    optional: true
  });
  /** Whether the chip list is selectable. */
  chipListSelectable = true;
  /** Whether the chip list is in multi-selection mode. */
  _chipListMultiple = false;
  /** Whether the chip list hides single-selection indicator. */
  _chipListHideSingleSelectionIndicator = this._defaultOptions?.hideSingleSelectionIndicator ?? false;
  /**
   * Whether or not the chip is selectable.
   *
   * When a chip is not selectable, changes to its selected state are always
   * ignored. By default an option chip is selectable, and it becomes
   * non-selectable if its parent chip list is not selectable.
   */
  get selectable() {
    return this._selectable && this.chipListSelectable;
  }
  set selectable(value) {
    this._selectable = value;
    this._changeDetectorRef.markForCheck();
  }
  _selectable = true;
  /** Whether the chip is selected. */
  get selected() {
    return this._selected;
  }
  set selected(value) {
    this._setSelectedState(value, false, true);
  }
  _selected = false;
  /**
   * The ARIA selected applied to the chip. Conforms to WAI ARIA best practices for listbox
   * interaction patterns.
   *
   * From [WAI ARIA Listbox authoring practices guide](
   * https://www.w3.org/WAI/ARIA/apg/patterns/listbox/):
   *  "If any options are selected, each selected option has either aria-selected or aria-checked
   *  set to true. All options that are selectable but not selected have either aria-selected or
   *  aria-checked set to false."
   *
   * Set `aria-selected="false"` on not-selected listbox options that are selectable to fix
   * VoiceOver reading every option as "selected" (#25736).
   */
  get ariaSelected() {
    return this.selectable ? this.selected.toString() : null;
  }
  /** The unstyled chip selector for this component. */
  basicChipAttrName = "mat-basic-chip-option";
  /** Emitted when the chip is selected or deselected. */
  selectionChange = new EventEmitter();
  ngOnInit() {
    super.ngOnInit();
    this.role = "presentation";
  }
  /** Selects the chip. */
  select() {
    this._setSelectedState(true, false, true);
  }
  /** Deselects the chip. */
  deselect() {
    this._setSelectedState(false, false, true);
  }
  /** Selects this chip and emits userInputSelection event */
  selectViaInteraction() {
    this._setSelectedState(true, true, true);
  }
  /** Toggles the current selected state of this chip. */
  toggleSelected(isUserInput = false) {
    this._setSelectedState(!this.selected, isUserInput, true);
    return this.selected;
  }
  _handlePrimaryActionInteraction() {
    if (!this.disabled) {
      this.focus();
      if (this.selectable) {
        this.toggleSelected(true);
      }
    }
  }
  _hasLeadingGraphic() {
    if (this.leadingIcon) {
      return true;
    }
    return !this._chipListHideSingleSelectionIndicator || this._chipListMultiple;
  }
  _setSelectedState(isSelected, isUserInput, emitEvent) {
    if (isSelected !== this.selected) {
      this._selected = isSelected;
      if (emitEvent) {
        this.selectionChange.emit({
          source: this,
          isUserInput,
          selected: this.selected
        });
      }
      this._changeDetectorRef.markForCheck();
    }
  }
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatChipOption_BaseFactory;
    return function MatChipOption_Factory(__ngFactoryType__) {
      return (\u0275MatChipOption_BaseFactory || (\u0275MatChipOption_BaseFactory = \u0275\u0275getInheritedFactory(_MatChipOption)))(__ngFactoryType__ || _MatChipOption);
    };
  })();
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatChipOption,
    selectors: [["mat-basic-chip-option"], ["", "mat-basic-chip-option", ""], ["mat-chip-option"], ["", "mat-chip-option", ""]],
    hostAttrs: [1, "mat-mdc-chip", "mat-mdc-chip-option"],
    hostVars: 37,
    hostBindings: function MatChipOption_HostBindings(rf, ctx) {
      if (rf & 2) {
        \u0275\u0275domProperty("id", ctx.id);
        \u0275\u0275attribute("tabindex", null)("aria-label", null)("aria-description", null)("role", ctx.role);
        \u0275\u0275classProp("mdc-evolution-chip", !ctx._isBasicChip)("mdc-evolution-chip--filter", !ctx._isBasicChip)("mdc-evolution-chip--selectable", !ctx._isBasicChip)("mat-mdc-chip-selected", ctx.selected)("mat-mdc-chip-multiple", ctx._chipListMultiple)("mat-mdc-chip-disabled", ctx.disabled)("mat-mdc-chip-with-avatar", ctx.leadingIcon)("mdc-evolution-chip--disabled", ctx.disabled)("mdc-evolution-chip--selected", ctx.selected)("mdc-evolution-chip--selecting", !ctx._animationsDisabled)("mdc-evolution-chip--with-trailing-action", ctx._hasTrailingIcon())("mdc-evolution-chip--with-primary-icon", ctx.leadingIcon)("mdc-evolution-chip--with-primary-graphic", ctx._hasLeadingGraphic())("mdc-evolution-chip--with-avatar", ctx.leadingIcon)("mat-mdc-chip-highlighted", ctx.highlighted)("mat-mdc-chip-with-trailing-icon", ctx._hasTrailingIcon());
      }
    },
    inputs: {
      selectable: [2, "selectable", "selectable", booleanAttribute],
      selected: [2, "selected", "selected", booleanAttribute]
    },
    outputs: {
      selectionChange: "selectionChange"
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: MatChip,
      useExisting: _MatChipOption
    }, {
      provide: MAT_CHIP,
      useExisting: _MatChipOption
    }]), \u0275\u0275InheritDefinitionFeature],
    ngContentSelectors: _c13,
    decls: 8,
    vars: 6,
    consts: [[1, "mat-mdc-chip-focus-overlay"], [1, "mdc-evolution-chip__cell", "mdc-evolution-chip__cell--primary"], ["matChipAction", "", "role", "option", 3, "_allowFocusWhenDisabled"], [1, "mdc-evolution-chip__graphic", "mat-mdc-chip-graphic"], [1, "mdc-evolution-chip__text-label", "mat-mdc-chip-action-label"], [1, "mat-mdc-chip-primary-focus-indicator", "mat-focus-indicator"], [1, "mdc-evolution-chip__cell", "mdc-evolution-chip__cell--trailing"], [1, "mdc-evolution-chip__checkmark"], ["viewBox", "-2 -3 30 30", "focusable", "false", "aria-hidden", "true", 1, "mdc-evolution-chip__checkmark-svg"], ["fill", "none", "stroke", "currentColor", "d", "M1.73,12.91 8.1,19.28 22.79,4.59", 1, "mdc-evolution-chip__checkmark-path"]],
    template: function MatChipOption_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275projectionDef(_c03);
        \u0275\u0275element(0, "span", 0);
        \u0275\u0275elementStart(1, "span", 1)(2, "button", 2);
        \u0275\u0275conditionalCreate(3, MatChipOption_Conditional_3_Template, 5, 0, "span", 3);
        \u0275\u0275elementStart(4, "span", 4);
        \u0275\u0275projection(5);
        \u0275\u0275element(6, "span", 5);
        \u0275\u0275elementEnd()()();
        \u0275\u0275conditionalCreate(7, MatChipOption_Conditional_7_Template, 2, 0, "span", 6);
      }
      if (rf & 2) {
        \u0275\u0275advance(2);
        \u0275\u0275property("_allowFocusWhenDisabled", true);
        \u0275\u0275attribute("aria-description", ctx.ariaDescription)("aria-label", ctx.ariaLabel)("aria-selected", ctx.ariaSelected);
        \u0275\u0275advance();
        \u0275\u0275conditional(ctx._hasLeadingGraphic() ? 3 : -1);
        \u0275\u0275advance(4);
        \u0275\u0275conditional(ctx._hasTrailingIcon() ? 7 : -1);
      }
    },
    dependencies: [MatChipAction],
    styles: [_c2],
    encapsulation: 2,
    changeDetection: 0
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipOption, [{
    type: Component,
    args: [{
      selector: "mat-basic-chip-option, [mat-basic-chip-option], mat-chip-option, [mat-chip-option]",
      host: {
        "class": "mat-mdc-chip mat-mdc-chip-option",
        "[class.mdc-evolution-chip]": "!_isBasicChip",
        "[class.mdc-evolution-chip--filter]": "!_isBasicChip",
        "[class.mdc-evolution-chip--selectable]": "!_isBasicChip",
        "[class.mat-mdc-chip-selected]": "selected",
        "[class.mat-mdc-chip-multiple]": "_chipListMultiple",
        "[class.mat-mdc-chip-disabled]": "disabled",
        "[class.mat-mdc-chip-with-avatar]": "leadingIcon",
        "[class.mdc-evolution-chip--disabled]": "disabled",
        "[class.mdc-evolution-chip--selected]": "selected",
        // This class enables the transition on the checkmark. Usually MDC adds it when selection
        // starts and removes it once the animation is finished. We don't need to go through all
        // the trouble, because we only care about the selection animation. MDC needs to do it,
        // because they also have an exit animation that we don't care about.
        "[class.mdc-evolution-chip--selecting]": "!_animationsDisabled",
        "[class.mdc-evolution-chip--with-trailing-action]": "_hasTrailingIcon()",
        "[class.mdc-evolution-chip--with-primary-icon]": "leadingIcon",
        "[class.mdc-evolution-chip--with-primary-graphic]": "_hasLeadingGraphic()",
        "[class.mdc-evolution-chip--with-avatar]": "leadingIcon",
        "[class.mat-mdc-chip-highlighted]": "highlighted",
        "[class.mat-mdc-chip-with-trailing-icon]": "_hasTrailingIcon()",
        "[attr.tabindex]": "null",
        "[attr.aria-label]": "null",
        "[attr.aria-description]": "null",
        "[attr.role]": "role",
        "[id]": "id"
      },
      providers: [{
        provide: MatChip,
        useExisting: MatChipOption
      }, {
        provide: MAT_CHIP,
        useExisting: MatChipOption
      }],
      encapsulation: ViewEncapsulation.None,
      changeDetection: ChangeDetectionStrategy.OnPush,
      imports: [MatChipAction],
      template: '<span class="mat-mdc-chip-focus-overlay"></span>\n\n<span class="mdc-evolution-chip__cell mdc-evolution-chip__cell--primary">\n  <button\n    matChipAction\n    [_allowFocusWhenDisabled]="true"\n    [attr.aria-description]="ariaDescription"\n    [attr.aria-label]="ariaLabel"\n    [attr.aria-selected]="ariaSelected"\n    role="option">\n    @if (_hasLeadingGraphic()) {\n      <span class="mdc-evolution-chip__graphic mat-mdc-chip-graphic">\n        <ng-content select="mat-chip-avatar, [matChipAvatar]"></ng-content>\n        <span class="mdc-evolution-chip__checkmark">\n          <svg\n            class="mdc-evolution-chip__checkmark-svg"\n            viewBox="-2 -3 30 30"\n            focusable="false"\n            aria-hidden="true">\n            <path class="mdc-evolution-chip__checkmark-path"\n                  fill="none" stroke="currentColor" d="M1.73,12.91 8.1,19.28 22.79,4.59" />\n          </svg>\n        </span>\n      </span>\n    }\n    <span class="mdc-evolution-chip__text-label mat-mdc-chip-action-label">\n      <ng-content></ng-content>\n      <span class="mat-mdc-chip-primary-focus-indicator mat-focus-indicator"></span>\n    </span>\n  </button>\n</span>\n\n@if (_hasTrailingIcon()) {\n  <span class="mdc-evolution-chip__cell mdc-evolution-chip__cell--trailing">\n    <ng-content select="mat-chip-trailing-icon,[matChipRemove],[matChipTrailingIcon]"></ng-content>\n  </span>\n}\n',
      styles: ['.mdc-evolution-chip,.mdc-evolution-chip__cell,.mdc-evolution-chip__action{display:inline-flex;align-items:center}.mdc-evolution-chip{position:relative;max-width:100%}.mdc-evolution-chip__cell,.mdc-evolution-chip__action{height:100%}.mdc-evolution-chip__cell--primary{flex-basis:100%;overflow-x:hidden}.mdc-evolution-chip__cell--trailing{flex:1 0 auto}.mdc-evolution-chip__action{align-items:center;background:none;border:none;box-sizing:content-box;cursor:pointer;display:inline-flex;justify-content:center;outline:none;padding:0;text-decoration:none;color:inherit}.mdc-evolution-chip__action--presentational{cursor:auto}.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{pointer-events:none}@media(forced-colors: active){.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{forced-color-adjust:none}}.mdc-evolution-chip__action--primary{font:inherit;letter-spacing:inherit;white-space:inherit;overflow-x:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-outline-width, 1px);border-radius:var(--mat-chip-container-shape-radius, 8px);box-sizing:border-box;content:"";height:100%;left:0;position:absolute;pointer-events:none;top:0;width:100%;z-index:1;border-style:solid}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-outline-color, var(--mat-sys-outline))}.mdc-evolution-chip__action--primary:not(.mdc-evolution-chip__action--presentational):not(.mdc-ripple-upgraded):focus::before{border-color:var(--mat-chip-focus-outline-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-disabled-outline-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-basic-chip .mdc-evolution-chip__action--primary{font:inherit}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip__action--secondary{position:relative;overflow:visible}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-trailing-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip__text-label{-webkit-user-select:none;user-select:none;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__text-label{font-family:var(--mat-chip-label-text-font, var(--mat-sys-label-large-font));line-height:var(--mat-chip-label-text-line-height, var(--mat-sys-label-large-line-height));font-size:var(--mat-chip-label-text-size, var(--mat-sys-label-large-size));font-weight:var(--mat-chip-label-text-weight, var(--mat-sys-label-large-weight));letter-spacing:var(--mat-chip-label-text-tracking, var(--mat-sys-label-large-tracking))}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-label-text-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label,.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label{color:var(--mat-chip-disabled-label-text-color, color-mix(in srgb, var(--mat-sys-on-surface) 38%, transparent))}.mdc-evolution-chip__graphic{align-items:center;display:inline-flex;justify-content:center;overflow:hidden;pointer-events:none;position:relative;flex:1 0 auto}.mat-mdc-standard-chip .mdc-evolution-chip__graphic{width:var(--mat-chip-with-avatar-avatar-size, 24px);height:var(--mat-chip-with-avatar-avatar-size, 24px);font-size:var(--mat-chip-with-avatar-avatar-size, 24px)}.mdc-evolution-chip--selecting .mdc-evolution-chip__graphic{transition:width 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selectable:not(.mdc-evolution-chip--selected):not(.mdc-evolution-chip--with-primary-icon) .mdc-evolution-chip__graphic{width:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__graphic{padding-left:0}.mdc-evolution-chip__checkmark{position:absolute;opacity:0;top:50%;left:50%;height:20px;width:20px}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark{transition:transform 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1);transform:translate(-75%, -50%)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{transform:translate(-50%, -50%);opacity:1}.mdc-evolution-chip__checkmark-svg{display:block}.mdc-evolution-chip__checkmark-path{stroke-width:2px;stroke-dasharray:29.7833385;stroke-dashoffset:29.7833385;stroke:currentColor}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark-path{transition:stroke-dashoffset 150ms 45ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark-path{stroke-dashoffset:0}@media(forced-colors: active){.mdc-evolution-chip__checkmark-path{stroke:CanvasText !important}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--trailing{height:18px;width:18px;font-size:18px}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove{opacity:calc(var(--mat-chip-trailing-action-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove:focus{opacity:calc(var(--mat-chip-trailing-action-focus-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mat-mdc-standard-chip{border-radius:var(--mat-chip-container-shape-radius, 8px);height:var(--mat-chip-container-height, 32px)}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-container-color, transparent)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{background-color:var(--mat-chip-elevated-disabled-container-color)}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled{background-color:var(--mat-chip-flat-disabled-selected-container-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}@media(forced-colors: active){.mat-mdc-standard-chip{outline:solid 1px}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--primary{border-radius:var(--mat-chip-with-avatar-avatar-shape-radius, 24px);width:var(--mat-chip-with-icon-icon-size, 18px);height:var(--mat-chip-with-icon-icon-size, 18px);font-size:var(--mat-chip-with-icon-icon-size, 18px)}.mdc-evolution-chip--selected .mdc-evolution-chip__icon--primary{opacity:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-highlighted{--mat-chip-with-icon-icon-color: var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container));--mat-chip-elevated-container-color: var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container));--mat-chip-label-text-color: var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container));--mat-chip-outline-width: var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-selected .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-chip:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-hover-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-focus-overlay .mat-mdc-chip-selected:hover,.mat-mdc-chip-highlighted:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-hover-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected.cdk-focused .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mdc-evolution-chip--disabled:not(.mdc-evolution-chip--selected) .mat-mdc-chip-avatar{opacity:var(--mat-chip-with-avatar-disabled-avatar-opacity, 0.38)}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{opacity:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38)}.mdc-evolution-chip--disabled.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{opacity:var(--mat-chip-with-icon-disabled-icon-opacity, 0.38)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{opacity:var(--mat-chip-disabled-container-opacity, 1)}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-trailing-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-edit,.mat-mdc-chip-remove{opacity:var(--mat-chip-trailing-action-opacity, 1)}.mat-mdc-chip-edit:focus,.mat-mdc-chip-remove:focus{opacity:var(--mat-chip-trailing-action-focus-opacity, 1)}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{background-color:var(--mat-chip-trailing-action-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-edit:hover::after,.mat-mdc-chip-remove:hover::after{opacity:var(--mat-chip-trailing-action-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-edit:focus::after,.mat-mdc-chip-remove:focus::after{opacity:var(--mat-chip-trailing-action-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected .mat-mdc-chip-remove::after,.mat-mdc-chip-highlighted .mat-mdc-chip-remove::after{background-color:var(--mat-chip-selected-trailing-action-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip{-webkit-tap-highlight-color:rgba(0,0,0,0)}.mat-mdc-standard-chip .mdc-evolution-chip__cell--primary,.mat-mdc-standard-chip .mdc-evolution-chip__action--primary,.mat-mdc-standard-chip .mat-mdc-chip-action-label{overflow:visible}.mat-mdc-standard-chip .mat-mdc-chip-graphic,.mat-mdc-standard-chip .mat-mdc-chip-trailing-icon{box-sizing:content-box}.mat-mdc-standard-chip._mat-animation-noopable,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__graphic,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark-path{transition-duration:1ms;animation-duration:1ms}.mat-mdc-chip-focus-overlay{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;opacity:0;border-radius:inherit;transition:opacity 150ms linear}._mat-animation-noopable .mat-mdc-chip-focus-overlay{transition:none}.mat-mdc-basic-chip .mat-mdc-chip-focus-overlay{display:none}.mat-mdc-chip .mat-ripple.mat-mdc-chip-ripple{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;border-radius:inherit}.mat-mdc-chip-avatar{text-align:center;line-height:1;color:var(--mat-chip-with-icon-icon-color, currentColor)}.mat-mdc-chip{position:relative;z-index:0}.mat-mdc-chip-action-label{text-align:left;z-index:1}[dir=rtl] .mat-mdc-chip-action-label{text-align:right}.mat-mdc-chip.mdc-evolution-chip--with-trailing-action .mat-mdc-chip-action-label{position:relative}.mat-mdc-chip-action-label .mat-mdc-chip-primary-focus-indicator{position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none}.mat-mdc-chip-action-label .mat-focus-indicator::before{margin:calc(calc(var(--mat-focus-indicator-border-width, 3px) + 2px)*-1)}.mat-mdc-chip-edit::before,.mat-mdc-chip-remove::before{margin:calc(var(--mat-focus-indicator-border-width, 3px)*-1);left:8px;right:8px}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{content:"";display:block;opacity:0;position:absolute;top:-3px;bottom:-3px;left:5px;right:5px;border-radius:50%;box-sizing:border-box;padding:12px;margin:-12px;background-clip:content-box}.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{width:18px;height:18px;font-size:18px;box-sizing:content-box}.mat-chip-edit-input{cursor:text;display:inline-block;color:inherit;outline:0}@media(forced-colors: active){.mat-mdc-chip-selected:not(.mat-mdc-chip-multiple){outline-width:3px}}.mat-mdc-chip-action:focus .mat-focus-indicator::before{content:""}.mdc-evolution-chip__icon,.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{min-height:fit-content}img.mdc-evolution-chip__icon{min-height:0}\n']
    }]
  }], null, {
    selectable: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    selected: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    selectionChange: [{
      type: Output
    }]
  });
})();
var MatChipEditInput = class _MatChipEditInput {
  _elementRef = inject(ElementRef);
  _document = inject(DOCUMENT);
  constructor() {
  }
  initialize(initialValue) {
    this.getNativeElement().focus();
    this.setValue(initialValue);
  }
  getNativeElement() {
    return this._elementRef.nativeElement;
  }
  setValue(value) {
    this.getNativeElement().textContent = value;
    this._moveCursorToEndOfInput();
  }
  getValue() {
    return this.getNativeElement().textContent || "";
  }
  _moveCursorToEndOfInput() {
    const range = this._document.createRange();
    range.selectNodeContents(this.getNativeElement());
    range.collapse(false);
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
  }
  static \u0275fac = function MatChipEditInput_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatChipEditInput)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatChipEditInput,
    selectors: [["span", "matChipEditInput", ""]],
    hostAttrs: ["role", "textbox", "tabindex", "-1", "contenteditable", "true", 1, "mat-chip-edit-input"]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipEditInput, [{
    type: Directive,
    args: [{
      selector: "span[matChipEditInput]",
      host: {
        "class": "mat-chip-edit-input",
        "role": "textbox",
        "tabindex": "-1",
        "contenteditable": "true"
      }
    }]
  }], () => [], null);
})();
var MatChipRow = class _MatChipRow extends MatChip {
  basicChipAttrName = "mat-basic-chip-row";
  /**
   * The editing action has to be triggered in a timeout. While we're waiting on it, a blur
   * event might occur which will interrupt the editing. This flag is used to avoid interruptions
   * while the editing action is being initialized.
   */
  _editStartPending = false;
  editable = false;
  /** Emitted when the chip is edited. */
  edited = new EventEmitter();
  /** The default chip edit input that is used if none is projected into this chip row. */
  defaultEditInput;
  /** The projected chip edit input. */
  contentEditInput;
  /**
   * Set on a mousedown when the chip is already focused via mouse or keyboard.
   *
   * This allows us to ensure chip is already focused when deciding whether to enter the
   * edit mode on a subsequent click. Otherwise, the chip appears focused when handling the
   * first click event.
   */
  _alreadyFocused = false;
  _isEditing = false;
  constructor() {
    super();
    this.role = "row";
    this._onBlur.pipe(takeUntil(this.destroyed)).subscribe(() => {
      if (this._isEditing && !this._editStartPending) {
        this._onEditFinish();
      }
      this._alreadyFocused = false;
    });
  }
  ngAfterViewInit() {
    super.ngAfterViewInit();
    this._ngZone.runOutsideAngular(() => {
      this._elementRef.nativeElement.addEventListener("mousedown", () => this._alreadyFocused = this._hasFocus());
    });
  }
  _hasLeadingActionIcon() {
    return !this._isEditing && !!this.editIcon;
  }
  _hasTrailingIcon() {
    return !this._isEditing && super._hasTrailingIcon();
  }
  /** Sends focus to the first gridcell when the user clicks anywhere inside the chip. */
  _handleFocus() {
    if (!this._isEditing && !this.disabled) {
      this.focus();
    }
  }
  _handleKeydown(event) {
    if (event.keyCode === ENTER && !this.disabled) {
      if (this._isEditing) {
        event.preventDefault();
        this._onEditFinish();
      } else if (this.editable) {
        this._startEditing(event);
      }
    } else if (this._isEditing) {
      event.stopPropagation();
    } else {
      super._handleKeydown(event);
    }
  }
  _handleClick(event) {
    if (!this.disabled && this.editable && !this._isEditing && this._alreadyFocused) {
      event.preventDefault();
      event.stopPropagation();
      this._startEditing(event);
    }
  }
  _handleDoubleclick(event) {
    if (!this.disabled && this.editable) {
      this._startEditing(event);
    }
  }
  _edit() {
    this._changeDetectorRef.markForCheck();
    this._startEditing();
  }
  _startEditing(event) {
    if (!this.primaryAction || this.removeIcon && !!event && this._getSourceAction(event.target) === this.removeIcon) {
      return;
    }
    const value = this.value;
    this._isEditing = this._editStartPending = true;
    afterNextRender(() => {
      this._getEditInput().initialize(value);
      setTimeout(() => this._ngZone.run(() => this._editStartPending = false));
    }, {
      injector: this._injector
    });
  }
  _onEditFinish() {
    this._isEditing = this._editStartPending = false;
    this.edited.emit({
      chip: this,
      value: this._getEditInput().getValue()
    });
    if (this._document.activeElement === this._getEditInput().getNativeElement() || this._document.activeElement === this._document.body) {
      this.primaryAction.focus();
    }
  }
  _isRippleDisabled() {
    return super._isRippleDisabled() || this._isEditing;
  }
  /**
   * Gets the projected chip edit input, or the default input if none is projected in. One of these
   * two values is guaranteed to be defined.
   */
  _getEditInput() {
    return this.contentEditInput || this.defaultEditInput;
  }
  static \u0275fac = function MatChipRow_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatChipRow)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatChipRow,
    selectors: [["mat-chip-row"], ["", "mat-chip-row", ""], ["mat-basic-chip-row"], ["", "mat-basic-chip-row", ""]],
    contentQueries: function MatChipRow_ContentQueries(rf, ctx, dirIndex) {
      if (rf & 1) {
        \u0275\u0275contentQuery(dirIndex, MatChipEditInput, 5);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.contentEditInput = _t.first);
      }
    },
    viewQuery: function MatChipRow_Query(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275viewQuery(MatChipEditInput, 5);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx.defaultEditInput = _t.first);
      }
    },
    hostAttrs: [1, "mat-mdc-chip", "mat-mdc-chip-row", "mdc-evolution-chip"],
    hostVars: 29,
    hostBindings: function MatChipRow_HostBindings(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275listener("focus", function MatChipRow_focus_HostBindingHandler() {
          return ctx._handleFocus();
        })("click", function MatChipRow_click_HostBindingHandler($event) {
          return ctx._handleClick($event);
        })("dblclick", function MatChipRow_dblclick_HostBindingHandler($event) {
          return ctx._handleDoubleclick($event);
        });
      }
      if (rf & 2) {
        \u0275\u0275domProperty("id", ctx.id);
        \u0275\u0275attribute("tabindex", ctx.disabled ? null : -1)("aria-label", null)("aria-description", null)("role", ctx.role);
        \u0275\u0275classProp("mat-mdc-chip-with-avatar", ctx.leadingIcon)("mat-mdc-chip-disabled", ctx.disabled)("mat-mdc-chip-editing", ctx._isEditing)("mat-mdc-chip-editable", ctx.editable)("mdc-evolution-chip--disabled", ctx.disabled)("mdc-evolution-chip--with-leading-action", ctx._hasLeadingActionIcon())("mdc-evolution-chip--with-trailing-action", ctx._hasTrailingIcon())("mdc-evolution-chip--with-primary-graphic", ctx.leadingIcon)("mdc-evolution-chip--with-primary-icon", ctx.leadingIcon)("mdc-evolution-chip--with-avatar", ctx.leadingIcon)("mat-mdc-chip-highlighted", ctx.highlighted)("mat-mdc-chip-with-trailing-icon", ctx._hasTrailingIcon());
      }
    },
    inputs: {
      editable: "editable"
    },
    outputs: {
      edited: "edited"
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: MatChip,
      useExisting: _MatChipRow
    }, {
      provide: MAT_CHIP,
      useExisting: _MatChipRow
    }]), \u0275\u0275InheritDefinitionFeature],
    ngContentSelectors: _c4,
    decls: 9,
    vars: 8,
    consts: [[1, "mat-mdc-chip-focus-overlay"], ["role", "gridcell", 1, "mdc-evolution-chip__cell", "mdc-evolution-chip__cell--leading"], ["role", "gridcell", "matChipAction", "", 1, "mdc-evolution-chip__cell", "mdc-evolution-chip__cell--primary", 3, "disabled"], [1, "mdc-evolution-chip__graphic", "mat-mdc-chip-graphic"], [1, "mdc-evolution-chip__text-label", "mat-mdc-chip-action-label"], ["aria-hidden", "true", 1, "mat-mdc-chip-primary-focus-indicator", "mat-focus-indicator"], ["role", "gridcell", 1, "mdc-evolution-chip__cell", "mdc-evolution-chip__cell--trailing"], ["matChipEditInput", ""]],
    template: function MatChipRow_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275projectionDef(_c3);
        \u0275\u0275conditionalCreate(0, MatChipRow_Conditional_0_Template, 1, 0, "span", 0);
        \u0275\u0275conditionalCreate(1, MatChipRow_Conditional_1_Template, 2, 0, "span", 1);
        \u0275\u0275elementStart(2, "span", 2);
        \u0275\u0275conditionalCreate(3, MatChipRow_Conditional_3_Template, 2, 0, "span", 3);
        \u0275\u0275elementStart(4, "span", 4);
        \u0275\u0275conditionalCreate(5, MatChipRow_Conditional_5_Template, 2, 1)(6, MatChipRow_Conditional_6_Template, 1, 0);
        \u0275\u0275element(7, "span", 5);
        \u0275\u0275elementEnd()();
        \u0275\u0275conditionalCreate(8, MatChipRow_Conditional_8_Template, 2, 0, "span", 6);
      }
      if (rf & 2) {
        \u0275\u0275conditional(!ctx._isEditing ? 0 : -1);
        \u0275\u0275advance();
        \u0275\u0275conditional(ctx._hasLeadingActionIcon() ? 1 : -1);
        \u0275\u0275advance();
        \u0275\u0275property("disabled", ctx.disabled);
        \u0275\u0275attribute("aria-description", ctx.ariaDescription)("aria-label", ctx.ariaLabel);
        \u0275\u0275advance();
        \u0275\u0275conditional(ctx.leadingIcon ? 3 : -1);
        \u0275\u0275advance(2);
        \u0275\u0275conditional(ctx._isEditing ? 5 : 6);
        \u0275\u0275advance(3);
        \u0275\u0275conditional(ctx._hasTrailingIcon() ? 8 : -1);
      }
    },
    dependencies: [MatChipAction, MatChipEditInput],
    styles: [_c2],
    encapsulation: 2,
    changeDetection: 0
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipRow, [{
    type: Component,
    args: [{
      selector: "mat-chip-row, [mat-chip-row], mat-basic-chip-row, [mat-basic-chip-row]",
      host: {
        "class": "mat-mdc-chip mat-mdc-chip-row mdc-evolution-chip",
        "[class.mat-mdc-chip-with-avatar]": "leadingIcon",
        "[class.mat-mdc-chip-disabled]": "disabled",
        "[class.mat-mdc-chip-editing]": "_isEditing",
        "[class.mat-mdc-chip-editable]": "editable",
        "[class.mdc-evolution-chip--disabled]": "disabled",
        "[class.mdc-evolution-chip--with-leading-action]": "_hasLeadingActionIcon()",
        "[class.mdc-evolution-chip--with-trailing-action]": "_hasTrailingIcon()",
        "[class.mdc-evolution-chip--with-primary-graphic]": "leadingIcon",
        "[class.mdc-evolution-chip--with-primary-icon]": "leadingIcon",
        "[class.mdc-evolution-chip--with-avatar]": "leadingIcon",
        "[class.mat-mdc-chip-highlighted]": "highlighted",
        "[class.mat-mdc-chip-with-trailing-icon]": "_hasTrailingIcon()",
        "[id]": "id",
        // Has to have a negative tabindex in order to capture
        // focus and redirect it to the primary action.
        "[attr.tabindex]": "disabled ? null : -1",
        "[attr.aria-label]": "null",
        "[attr.aria-description]": "null",
        "[attr.role]": "role",
        "(focus)": "_handleFocus()",
        "(click)": "_handleClick($event)",
        "(dblclick)": "_handleDoubleclick($event)"
      },
      providers: [{
        provide: MatChip,
        useExisting: MatChipRow
      }, {
        provide: MAT_CHIP,
        useExisting: MatChipRow
      }],
      encapsulation: ViewEncapsulation.None,
      changeDetection: ChangeDetectionStrategy.OnPush,
      imports: [MatChipAction, MatChipEditInput],
      template: '@if (!_isEditing) {\n  <span class="mat-mdc-chip-focus-overlay"></span>\n}\n\n@if (_hasLeadingActionIcon()) {\n  <span class="mdc-evolution-chip__cell mdc-evolution-chip__cell--leading" role="gridcell">\n    <ng-content select="[matChipEdit]"></ng-content>\n  </span>\n}\n<span class="mdc-evolution-chip__cell mdc-evolution-chip__cell--primary" role="gridcell"\n    matChipAction\n    [disabled]="disabled"\n    [attr.aria-description]="ariaDescription"\n    [attr.aria-label]="ariaLabel">\n  @if (leadingIcon) {\n    <span class="mdc-evolution-chip__graphic mat-mdc-chip-graphic">\n      <ng-content select="mat-chip-avatar, [matChipAvatar]"></ng-content>\n    </span>\n  }\n\n  <span class="mdc-evolution-chip__text-label mat-mdc-chip-action-label">\n    @if (_isEditing) {\n      @if (contentEditInput) {\n        <ng-content select="[matChipEditInput]"></ng-content>\n      } @else {\n        <span matChipEditInput></span>\n      }\n    } @else {\n      <ng-content></ng-content>\n    }\n\n    <span class="mat-mdc-chip-primary-focus-indicator mat-focus-indicator" aria-hidden="true"></span>\n  </span>\n</span>\n\n@if (_hasTrailingIcon()) {\n  <span\n    class="mdc-evolution-chip__cell mdc-evolution-chip__cell--trailing"\n    role="gridcell">\n    <ng-content select="mat-chip-trailing-icon,[matChipRemove],[matChipTrailingIcon]"></ng-content>\n  </span>\n}\n',
      styles: ['.mdc-evolution-chip,.mdc-evolution-chip__cell,.mdc-evolution-chip__action{display:inline-flex;align-items:center}.mdc-evolution-chip{position:relative;max-width:100%}.mdc-evolution-chip__cell,.mdc-evolution-chip__action{height:100%}.mdc-evolution-chip__cell--primary{flex-basis:100%;overflow-x:hidden}.mdc-evolution-chip__cell--trailing{flex:1 0 auto}.mdc-evolution-chip__action{align-items:center;background:none;border:none;box-sizing:content-box;cursor:pointer;display:inline-flex;justify-content:center;outline:none;padding:0;text-decoration:none;color:inherit}.mdc-evolution-chip__action--presentational{cursor:auto}.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{pointer-events:none}@media(forced-colors: active){.mdc-evolution-chip--disabled,.mdc-evolution-chip__action:disabled{forced-color-adjust:none}}.mdc-evolution-chip__action--primary{font:inherit;letter-spacing:inherit;white-space:inherit;overflow-x:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-outline-width, 1px);border-radius:var(--mat-chip-container-shape-radius, 8px);box-sizing:border-box;content:"";height:100%;left:0;position:absolute;pointer-events:none;top:0;width:100%;z-index:1;border-style:solid}.mat-mdc-standard-chip .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-outline-color, var(--mat-sys-outline))}.mdc-evolution-chip__action--primary:not(.mdc-evolution-chip__action--presentational):not(.mdc-ripple-upgraded):focus::before{border-color:var(--mat-chip-focus-outline-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--primary::before{border-color:var(--mat-chip-disabled-outline-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__action--primary::before{border-width:var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-basic-chip .mdc-evolution-chip__action--primary{font:inherit}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}.mat-mdc-standard-chip.mdc-evolution-chip--with-leading-action.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:0;padding-right:12px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__action--primary{padding-left:12px;padding-right:0}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--primary{padding-left:0;padding-right:0}.mdc-evolution-chip__action--secondary{position:relative;overflow:visible}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-trailing-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__action--secondary{color:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-standard-chip.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__action--secondary{padding-left:8px;padding-right:8px}.mdc-evolution-chip__text-label{-webkit-user-select:none;user-select:none;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}.mat-mdc-standard-chip .mdc-evolution-chip__text-label{font-family:var(--mat-chip-label-text-font, var(--mat-sys-label-large-font));line-height:var(--mat-chip-label-text-line-height, var(--mat-sys-label-large-line-height));font-size:var(--mat-chip-label-text-size, var(--mat-sys-label-large-size));font-weight:var(--mat-chip-label-text-weight, var(--mat-sys-label-large-weight));letter-spacing:var(--mat-chip-label-text-tracking, var(--mat-sys-label-large-tracking))}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-label-text-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__text-label{color:var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label,.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__text-label{color:var(--mat-chip-disabled-label-text-color, color-mix(in srgb, var(--mat-sys-on-surface) 38%, transparent))}.mdc-evolution-chip__graphic{align-items:center;display:inline-flex;justify-content:center;overflow:hidden;pointer-events:none;position:relative;flex:1 0 auto}.mat-mdc-standard-chip .mdc-evolution-chip__graphic{width:var(--mat-chip-with-avatar-avatar-size, 24px);height:var(--mat-chip-with-avatar-avatar-size, 24px);font-size:var(--mat-chip-with-avatar-avatar-size, 24px)}.mdc-evolution-chip--selecting .mdc-evolution-chip__graphic{transition:width 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selectable:not(.mdc-evolution-chip--selected):not(.mdc-evolution-chip--with-primary-icon) .mdc-evolution-chip__graphic{width:0}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mat-mdc-standard-chip.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:6px;padding-right:6px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:4px;padding-right:8px}[dir=rtl] .mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-trailing-action .mdc-evolution-chip__graphic{padding-left:8px;padding-right:4px}.mdc-evolution-chip--with-avatar.mdc-evolution-chip--with-primary-graphic.mdc-evolution-chip--with-leading-action .mdc-evolution-chip__graphic{padding-left:0}.mdc-evolution-chip__checkmark{position:absolute;opacity:0;top:50%;left:50%;height:20px;width:20px}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__checkmark{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark{transition:transform 150ms 0ms cubic-bezier(0.4, 0, 0.2, 1);transform:translate(-75%, -50%)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{transform:translate(-50%, -50%);opacity:1}.mdc-evolution-chip__checkmark-svg{display:block}.mdc-evolution-chip__checkmark-path{stroke-width:2px;stroke-dasharray:29.7833385;stroke-dashoffset:29.7833385;stroke:currentColor}.mdc-evolution-chip--selecting .mdc-evolution-chip__checkmark-path{transition:stroke-dashoffset 150ms 45ms cubic-bezier(0.4, 0, 0.2, 1)}.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark-path{stroke-dashoffset:0}@media(forced-colors: active){.mdc-evolution-chip__checkmark-path{stroke:CanvasText !important}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--trailing{height:18px;width:18px;font-size:18px}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove{opacity:calc(var(--mat-chip-trailing-action-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing.mat-mdc-chip-remove:focus{opacity:calc(var(--mat-chip-trailing-action-focus-opacity, 1)*var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38))}.mat-mdc-standard-chip{border-radius:var(--mat-chip-container-shape-radius, 8px);height:var(--mat-chip-container-height, 32px)}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-container-color, transparent)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{background-color:var(--mat-chip-elevated-disabled-container-color)}.mat-mdc-standard-chip.mdc-evolution-chip--selected:not(.mdc-evolution-chip--disabled){background-color:var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled{background-color:var(--mat-chip-flat-disabled-selected-container-color, color-mix(in srgb, var(--mat-sys-on-surface) 12%, transparent))}@media(forced-colors: active){.mat-mdc-standard-chip{outline:solid 1px}}.mat-mdc-standard-chip .mdc-evolution-chip__icon--primary{border-radius:var(--mat-chip-with-avatar-avatar-shape-radius, 24px);width:var(--mat-chip-with-icon-icon-size, 18px);height:var(--mat-chip-with-icon-icon-size, 18px);font-size:var(--mat-chip-with-icon-icon-size, 18px)}.mdc-evolution-chip--selected .mdc-evolution-chip__icon--primary{opacity:0}.mat-mdc-standard-chip:not(.mdc-evolution-chip--disabled) .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-icon-color, var(--mat-sys-on-surface-variant))}.mat-mdc-standard-chip.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--primary{color:var(--mat-chip-with-icon-disabled-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-highlighted{--mat-chip-with-icon-icon-color: var(--mat-chip-with-icon-selected-icon-color, var(--mat-sys-on-secondary-container));--mat-chip-elevated-container-color: var(--mat-chip-elevated-selected-container-color, var(--mat-sys-secondary-container));--mat-chip-label-text-color: var(--mat-chip-selected-label-text-color, var(--mat-sys-on-secondary-container));--mat-chip-outline-width: var(--mat-chip-flat-selected-outline-width, 0)}.mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-selected .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-chip:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-hover-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-focus-overlay .mat-mdc-chip-selected:hover,.mat-mdc-chip-highlighted:hover .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-hover-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-focus-state-layer-color, var(--mat-sys-on-surface-variant));opacity:var(--mat-chip-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected.cdk-focused .mat-mdc-chip-focus-overlay,.mat-mdc-chip-highlighted.cdk-focused .mat-mdc-chip-focus-overlay{background:var(--mat-chip-selected-focus-state-layer-color, var(--mat-sys-on-secondary-container));opacity:var(--mat-chip-selected-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mdc-evolution-chip--disabled:not(.mdc-evolution-chip--selected) .mat-mdc-chip-avatar{opacity:var(--mat-chip-with-avatar-disabled-avatar-opacity, 0.38)}.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{opacity:var(--mat-chip-with-trailing-icon-disabled-trailing-icon-opacity, 0.38)}.mdc-evolution-chip--disabled.mdc-evolution-chip--selected .mdc-evolution-chip__checkmark{opacity:var(--mat-chip-with-icon-disabled-icon-opacity, 0.38)}.mat-mdc-standard-chip.mdc-evolution-chip--disabled{opacity:var(--mat-chip-disabled-container-opacity, 1)}.mat-mdc-standard-chip.mdc-evolution-chip--selected .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-trailing-icon-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip.mdc-evolution-chip--selected.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing,.mat-mdc-standard-chip.mat-mdc-chip-highlighted.mdc-evolution-chip--disabled .mdc-evolution-chip__icon--trailing{color:var(--mat-chip-selected-disabled-trailing-icon-color, var(--mat-sys-on-surface))}.mat-mdc-chip-edit,.mat-mdc-chip-remove{opacity:var(--mat-chip-trailing-action-opacity, 1)}.mat-mdc-chip-edit:focus,.mat-mdc-chip-remove:focus{opacity:var(--mat-chip-trailing-action-focus-opacity, 1)}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{background-color:var(--mat-chip-trailing-action-state-layer-color, var(--mat-sys-on-surface-variant))}.mat-mdc-chip-edit:hover::after,.mat-mdc-chip-remove:hover::after{opacity:var(--mat-chip-trailing-action-hover-state-layer-opacity, var(--mat-sys-hover-state-layer-opacity))}.mat-mdc-chip-edit:focus::after,.mat-mdc-chip-remove:focus::after{opacity:var(--mat-chip-trailing-action-focus-state-layer-opacity, var(--mat-sys-focus-state-layer-opacity))}.mat-mdc-chip-selected .mat-mdc-chip-remove::after,.mat-mdc-chip-highlighted .mat-mdc-chip-remove::after{background-color:var(--mat-chip-selected-trailing-action-state-layer-color, var(--mat-sys-on-secondary-container))}.mat-mdc-standard-chip{-webkit-tap-highlight-color:rgba(0,0,0,0)}.mat-mdc-standard-chip .mdc-evolution-chip__cell--primary,.mat-mdc-standard-chip .mdc-evolution-chip__action--primary,.mat-mdc-standard-chip .mat-mdc-chip-action-label{overflow:visible}.mat-mdc-standard-chip .mat-mdc-chip-graphic,.mat-mdc-standard-chip .mat-mdc-chip-trailing-icon{box-sizing:content-box}.mat-mdc-standard-chip._mat-animation-noopable,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__graphic,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark,.mat-mdc-standard-chip._mat-animation-noopable .mdc-evolution-chip__checkmark-path{transition-duration:1ms;animation-duration:1ms}.mat-mdc-chip-focus-overlay{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;opacity:0;border-radius:inherit;transition:opacity 150ms linear}._mat-animation-noopable .mat-mdc-chip-focus-overlay{transition:none}.mat-mdc-basic-chip .mat-mdc-chip-focus-overlay{display:none}.mat-mdc-chip .mat-ripple.mat-mdc-chip-ripple{top:0;left:0;right:0;bottom:0;position:absolute;pointer-events:none;border-radius:inherit}.mat-mdc-chip-avatar{text-align:center;line-height:1;color:var(--mat-chip-with-icon-icon-color, currentColor)}.mat-mdc-chip{position:relative;z-index:0}.mat-mdc-chip-action-label{text-align:left;z-index:1}[dir=rtl] .mat-mdc-chip-action-label{text-align:right}.mat-mdc-chip.mdc-evolution-chip--with-trailing-action .mat-mdc-chip-action-label{position:relative}.mat-mdc-chip-action-label .mat-mdc-chip-primary-focus-indicator{position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none}.mat-mdc-chip-action-label .mat-focus-indicator::before{margin:calc(calc(var(--mat-focus-indicator-border-width, 3px) + 2px)*-1)}.mat-mdc-chip-edit::before,.mat-mdc-chip-remove::before{margin:calc(var(--mat-focus-indicator-border-width, 3px)*-1);left:8px;right:8px}.mat-mdc-chip-edit::after,.mat-mdc-chip-remove::after{content:"";display:block;opacity:0;position:absolute;top:-3px;bottom:-3px;left:5px;right:5px;border-radius:50%;box-sizing:border-box;padding:12px;margin:-12px;background-clip:content-box}.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{width:18px;height:18px;font-size:18px;box-sizing:content-box}.mat-chip-edit-input{cursor:text;display:inline-block;color:inherit;outline:0}@media(forced-colors: active){.mat-mdc-chip-selected:not(.mat-mdc-chip-multiple){outline-width:3px}}.mat-mdc-chip-action:focus .mat-focus-indicator::before{content:""}.mdc-evolution-chip__icon,.mat-mdc-chip-edit .mat-icon,.mat-mdc-chip-remove .mat-icon{min-height:fit-content}img.mdc-evolution-chip__icon{min-height:0}\n']
    }]
  }], () => [], {
    editable: [{
      type: Input
    }],
    edited: [{
      type: Output
    }],
    defaultEditInput: [{
      type: ViewChild,
      args: [MatChipEditInput]
    }],
    contentEditInput: [{
      type: ContentChild,
      args: [MatChipEditInput]
    }]
  });
})();
var MatChipSet = class _MatChipSet {
  _elementRef = inject(ElementRef);
  _changeDetectorRef = inject(ChangeDetectorRef);
  _dir = inject(Directionality, {
    optional: true
  });
  /** Index of the last destroyed chip that had focus. */
  _lastDestroyedFocusedChipIndex = null;
  /** Used to manage focus within the chip list. */
  _keyManager;
  /** Subject that emits when the component has been destroyed. */
  _destroyed = new Subject();
  /** Role to use if it hasn't been overwritten by the user. */
  _defaultRole = "presentation";
  /** Combined stream of all of the child chips' focus events. */
  get chipFocusChanges() {
    return this._getChipStream((chip) => chip._onFocus);
  }
  /** Combined stream of all of the child chips' destroy events. */
  get chipDestroyedChanges() {
    return this._getChipStream((chip) => chip.destroyed);
  }
  /** Combined stream of all of the child chips' remove events. */
  get chipRemovedChanges() {
    return this._getChipStream((chip) => chip.removed);
  }
  /** Whether the chip set is disabled. */
  get disabled() {
    return this._disabled;
  }
  set disabled(value) {
    this._disabled = value;
    this._syncChipsState();
  }
  _disabled = false;
  /** Whether the chip list contains chips or not. */
  get empty() {
    return !this._chips || this._chips.length === 0;
  }
  /** The ARIA role applied to the chip set. */
  get role() {
    if (this._explicitRole) {
      return this._explicitRole;
    }
    return this.empty ? null : this._defaultRole;
  }
  /** Tabindex of the chip set. */
  tabIndex = 0;
  set role(value) {
    this._explicitRole = value;
  }
  _explicitRole = null;
  /** Whether any of the chips inside of this chip-set has focus. */
  get focused() {
    return this._hasFocusedChip();
  }
  /** The chips that are part of this chip set. */
  _chips;
  /** Flat list of all the actions contained within the chips. */
  _chipActions = new QueryList();
  constructor() {
  }
  ngAfterViewInit() {
    this._setUpFocusManagement();
    this._trackChipSetChanges();
    this._trackDestroyedFocusedChip();
  }
  ngOnDestroy() {
    this._keyManager?.destroy();
    this._chipActions.destroy();
    this._destroyed.next();
    this._destroyed.complete();
  }
  /** Checks whether any of the chips is focused. */
  _hasFocusedChip() {
    return this._chips && this._chips.some((chip) => chip._hasFocus());
  }
  /** Syncs the chip-set's state with the individual chips. */
  _syncChipsState() {
    this._chips?.forEach((chip) => {
      chip._chipListDisabled = this._disabled;
      chip._changeDetectorRef.markForCheck();
    });
  }
  /** Dummy method for subclasses to override. Base chip set cannot be focused. */
  focus() {
  }
  /** Handles keyboard events on the chip set. */
  _handleKeydown(event) {
    if (this._originatesFromChip(event)) {
      this._keyManager.onKeydown(event);
    }
  }
  /**
   * Utility to ensure all indexes are valid.
   *
   * @param index The index to be checked.
   * @returns True if the index is valid for our list of chips.
   */
  _isValidIndex(index) {
    return index >= 0 && index < this._chips.length;
  }
  /**
   * Removes the `tabindex` from the chip set and resets it back afterwards, allowing the
   * user to tab out of it. This prevents the set from capturing focus and redirecting
   * it back to the first chip, creating a focus trap, if it user tries to tab away.
   */
  _allowFocusEscape() {
    const previous = this._elementRef.nativeElement.tabIndex;
    if (previous !== -1) {
      this._elementRef.nativeElement.tabIndex = -1;
      setTimeout(() => this._elementRef.nativeElement.tabIndex = previous);
    }
  }
  /**
   * Gets a stream of events from all the chips within the set.
   * The stream will automatically incorporate any newly-added chips.
   */
  _getChipStream(mappingFunction) {
    return this._chips.changes.pipe(startWith(null), switchMap(() => merge(...this._chips.map(mappingFunction))));
  }
  /** Checks whether an event comes from inside a chip element. */
  _originatesFromChip(event) {
    let currentElement = event.target;
    while (currentElement && currentElement !== this._elementRef.nativeElement) {
      if (currentElement.classList.contains("mat-mdc-chip")) {
        return true;
      }
      currentElement = currentElement.parentElement;
    }
    return false;
  }
  /** Sets up the chip set's focus management logic. */
  _setUpFocusManagement() {
    this._chips.changes.pipe(startWith(this._chips)).subscribe((chips) => {
      const actions = [];
      chips.forEach((chip) => chip._getActions().forEach((action) => actions.push(action)));
      this._chipActions.reset(actions);
      this._chipActions.notifyOnChanges();
    });
    this._keyManager = new FocusKeyManager(this._chipActions).withVerticalOrientation().withHorizontalOrientation(this._dir ? this._dir.value : "ltr").withHomeAndEnd().skipPredicate((action) => this._skipPredicate(action));
    this.chipFocusChanges.pipe(takeUntil(this._destroyed)).subscribe(({
      chip
    }) => {
      const action = chip._getSourceAction(document.activeElement);
      if (action) {
        this._keyManager.updateActiveItem(action);
      }
    });
    this._dir?.change.pipe(takeUntil(this._destroyed)).subscribe((direction) => this._keyManager.withHorizontalOrientation(direction));
  }
  /**
   * Determines if key manager should avoid putting a given chip action in the tab index. Skip
   * non-interactive and disabled actions since the user can't do anything with them.
   */
  _skipPredicate(action) {
    return !action.isInteractive || action.disabled;
  }
  /** Listens to changes in the chip set and syncs up the state of the individual chips. */
  _trackChipSetChanges() {
    this._chips.changes.pipe(startWith(null), takeUntil(this._destroyed)).subscribe(() => {
      if (this.disabled) {
        Promise.resolve().then(() => this._syncChipsState());
      }
      this._redirectDestroyedChipFocus();
    });
  }
  /** Starts tracking the destroyed chips in order to capture the focused one. */
  _trackDestroyedFocusedChip() {
    this.chipDestroyedChanges.pipe(takeUntil(this._destroyed)).subscribe((event) => {
      const chipArray = this._chips.toArray();
      const chipIndex = chipArray.indexOf(event.chip);
      const hasFocus = event.chip._hasFocus();
      const wasLastFocused = event.chip._hadFocusOnRemove && this._keyManager.activeItem && event.chip._getActions().includes(this._keyManager.activeItem);
      const shouldMoveFocus = hasFocus || wasLastFocused;
      if (this._isValidIndex(chipIndex) && shouldMoveFocus) {
        this._lastDestroyedFocusedChipIndex = chipIndex;
      }
    });
  }
  /**
   * Finds the next appropriate chip to move focus to,
   * if the currently-focused chip is destroyed.
   */
  _redirectDestroyedChipFocus() {
    if (this._lastDestroyedFocusedChipIndex == null) {
      return;
    }
    if (this._chips.length) {
      const newIndex = Math.min(this._lastDestroyedFocusedChipIndex, this._chips.length - 1);
      const chipToFocus = this._chips.toArray()[newIndex];
      if (chipToFocus.disabled) {
        if (this._chips.length === 1) {
          this.focus();
        } else {
          this._keyManager.setPreviousItemActive();
        }
      } else {
        chipToFocus.focus();
      }
    } else {
      this.focus();
    }
    this._lastDestroyedFocusedChipIndex = null;
  }
  static \u0275fac = function MatChipSet_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatChipSet)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatChipSet,
    selectors: [["mat-chip-set"]],
    contentQueries: function MatChipSet_ContentQueries(rf, ctx, dirIndex) {
      if (rf & 1) {
        \u0275\u0275contentQuery(dirIndex, MatChip, 5);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._chips = _t);
      }
    },
    hostAttrs: [1, "mat-mdc-chip-set", "mdc-evolution-chip-set"],
    hostVars: 1,
    hostBindings: function MatChipSet_HostBindings(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275listener("keydown", function MatChipSet_keydown_HostBindingHandler($event) {
          return ctx._handleKeydown($event);
        });
      }
      if (rf & 2) {
        \u0275\u0275attribute("role", ctx.role);
      }
    },
    inputs: {
      disabled: [2, "disabled", "disabled", booleanAttribute],
      role: "role",
      tabIndex: [2, "tabIndex", "tabIndex", (value) => value == null ? 0 : numberAttribute(value)]
    },
    ngContentSelectors: _c5,
    decls: 2,
    vars: 0,
    consts: [["role", "presentation", 1, "mdc-evolution-chip-set__chips"]],
    template: function MatChipSet_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275projectionDef();
        \u0275\u0275domElementStart(0, "div", 0);
        \u0275\u0275projection(1);
        \u0275\u0275domElementEnd();
      }
    },
    styles: [".mat-mdc-chip-set{display:flex}.mat-mdc-chip-set:focus{outline:none}.mat-mdc-chip-set .mdc-evolution-chip-set__chips{min-width:100%;margin-left:-8px;margin-right:0}.mat-mdc-chip-set .mdc-evolution-chip{margin:4px 0 4px 8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip-set__chips{margin-left:0;margin-right:-8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip{margin-left:0;margin-right:8px}.mdc-evolution-chip-set__chips{display:flex;flex-flow:wrap;min-width:0}.mat-mdc-chip-set-stacked{flex-direction:column;align-items:flex-start}.mat-mdc-chip-set-stacked .mat-mdc-chip{width:100%}.mat-mdc-chip-set-stacked .mdc-evolution-chip__graphic{flex-grow:0}.mat-mdc-chip-set-stacked .mdc-evolution-chip__action--primary{flex-basis:100%;justify-content:start}input.mat-mdc-chip-input{flex:1 0 150px;margin-left:8px}[dir=rtl] input.mat-mdc-chip-input{margin-left:0;margin-right:8px}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-moz-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-webkit-input-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input:-ms-input-placeholder{opacity:1}.mat-mdc-chip-set+input.mat-mdc-chip-input{margin-left:0;margin-right:0}\n"],
    encapsulation: 2,
    changeDetection: 0
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipSet, [{
    type: Component,
    args: [{
      selector: "mat-chip-set",
      template: `
    <div class="mdc-evolution-chip-set__chips" role="presentation">
      <ng-content></ng-content>
    </div>
  `,
      host: {
        "class": "mat-mdc-chip-set mdc-evolution-chip-set",
        "(keydown)": "_handleKeydown($event)",
        "[attr.role]": "role"
      },
      encapsulation: ViewEncapsulation.None,
      changeDetection: ChangeDetectionStrategy.OnPush,
      styles: [".mat-mdc-chip-set{display:flex}.mat-mdc-chip-set:focus{outline:none}.mat-mdc-chip-set .mdc-evolution-chip-set__chips{min-width:100%;margin-left:-8px;margin-right:0}.mat-mdc-chip-set .mdc-evolution-chip{margin:4px 0 4px 8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip-set__chips{margin-left:0;margin-right:-8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip{margin-left:0;margin-right:8px}.mdc-evolution-chip-set__chips{display:flex;flex-flow:wrap;min-width:0}.mat-mdc-chip-set-stacked{flex-direction:column;align-items:flex-start}.mat-mdc-chip-set-stacked .mat-mdc-chip{width:100%}.mat-mdc-chip-set-stacked .mdc-evolution-chip__graphic{flex-grow:0}.mat-mdc-chip-set-stacked .mdc-evolution-chip__action--primary{flex-basis:100%;justify-content:start}input.mat-mdc-chip-input{flex:1 0 150px;margin-left:8px}[dir=rtl] input.mat-mdc-chip-input{margin-left:0;margin-right:8px}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-moz-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-webkit-input-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input:-ms-input-placeholder{opacity:1}.mat-mdc-chip-set+input.mat-mdc-chip-input{margin-left:0;margin-right:0}\n"]
    }]
  }], () => [], {
    disabled: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    role: [{
      type: Input
    }],
    tabIndex: [{
      type: Input,
      args: [{
        transform: (value) => value == null ? 0 : numberAttribute(value)
      }]
    }],
    _chips: [{
      type: ContentChildren,
      args: [MatChip, {
        // We need to use `descendants: true`, because Ivy will no longer match
        // indirect descendants if it's left as false.
        descendants: true
      }]
    }]
  });
})();
var MatChipListboxChange = class {
  source;
  value;
  constructor(source, value) {
    this.source = source;
    this.value = value;
  }
};
var MAT_CHIP_LISTBOX_CONTROL_VALUE_ACCESSOR = {
  provide: NG_VALUE_ACCESSOR,
  useExisting: forwardRef(() => MatChipListbox),
  multi: true
};
var MatChipListbox = class _MatChipListbox extends MatChipSet {
  /**
   * Function when touched. Set as part of ControlValueAccessor implementation.
   * @docs-private
   */
  _onTouched = () => {
  };
  /**
   * Function when changed. Set as part of ControlValueAccessor implementation.
   * @docs-private
   */
  _onChange = () => {
  };
  // TODO: MDC uses `grid` here
  _defaultRole = "listbox";
  /** Default chip options. */
  _defaultOptions = inject(MAT_CHIPS_DEFAULT_OPTIONS, {
    optional: true
  });
  /** Whether the user should be allowed to select multiple chips. */
  get multiple() {
    return this._multiple;
  }
  set multiple(value) {
    this._multiple = value;
    this._syncListboxProperties();
  }
  _multiple = false;
  /** The array of selected chips inside the chip listbox. */
  get selected() {
    const selectedChips = this._chips.toArray().filter((chip) => chip.selected);
    return this.multiple ? selectedChips : selectedChips[0];
  }
  /** Orientation of the chip list. */
  ariaOrientation = "horizontal";
  /**
   * Whether or not this chip listbox is selectable.
   *
   * When a chip listbox is not selectable, the selected states for all
   * the chips inside the chip listbox are always ignored.
   */
  get selectable() {
    return this._selectable;
  }
  set selectable(value) {
    this._selectable = value;
    this._syncListboxProperties();
  }
  _selectable = true;
  /**
   * A function to compare the option values with the selected values. The first argument
   * is a value from an option. The second is a value from the selection. A boolean
   * should be returned.
   */
  compareWith = (o1, o2) => o1 === o2;
  /** Whether this chip listbox is required. */
  required = false;
  /** Whether checkmark indicator for single-selection options is hidden. */
  get hideSingleSelectionIndicator() {
    return this._hideSingleSelectionIndicator;
  }
  set hideSingleSelectionIndicator(value) {
    this._hideSingleSelectionIndicator = value;
    this._syncListboxProperties();
  }
  _hideSingleSelectionIndicator = this._defaultOptions?.hideSingleSelectionIndicator ?? false;
  /** Combined stream of all of the child chips' selection change events. */
  get chipSelectionChanges() {
    return this._getChipStream((chip) => chip.selectionChange);
  }
  /** Combined stream of all of the child chips' blur events. */
  get chipBlurChanges() {
    return this._getChipStream((chip) => chip._onBlur);
  }
  /** The value of the listbox, which is the combined value of the selected chips. */
  get value() {
    return this._value;
  }
  set value(value) {
    if (this._chips && this._chips.length) {
      this._setSelectionByValue(value, false);
    }
    this._value = value;
  }
  _value;
  /** Event emitted when the selected chip listbox value has been changed by the user. */
  change = new EventEmitter();
  _chips = void 0;
  ngAfterContentInit() {
    this._chips.changes.pipe(startWith(null), takeUntil(this._destroyed)).subscribe(() => {
      if (this.value !== void 0) {
        Promise.resolve().then(() => {
          this._setSelectionByValue(this.value, false);
        });
      }
      this._syncListboxProperties();
    });
    this.chipBlurChanges.pipe(takeUntil(this._destroyed)).subscribe(() => this._blur());
    this.chipSelectionChanges.pipe(takeUntil(this._destroyed)).subscribe((event) => {
      if (!this.multiple) {
        this._chips.forEach((chip) => {
          if (chip !== event.source) {
            chip._setSelectedState(false, false, false);
          }
        });
      }
      if (event.isUserInput) {
        this._propagateChanges();
      }
    });
  }
  /**
   * Focuses the first selected chip in this chip listbox, or the first non-disabled chip when there
   * are no selected chips.
   */
  focus() {
    if (this.disabled) {
      return;
    }
    const firstSelectedChip = this._getFirstSelectedChip();
    if (firstSelectedChip && !firstSelectedChip.disabled) {
      firstSelectedChip.focus();
    } else if (this._chips.length > 0) {
      this._keyManager.setFirstItemActive();
    } else {
      this._elementRef.nativeElement.focus();
    }
  }
  /**
   * Implemented as part of ControlValueAccessor.
   * @docs-private
   */
  writeValue(value) {
    if (value != null) {
      this.value = value;
    } else {
      this.value = void 0;
    }
  }
  /**
   * Implemented as part of ControlValueAccessor.
   * @docs-private
   */
  registerOnChange(fn) {
    this._onChange = fn;
  }
  /**
   * Implemented as part of ControlValueAccessor.
   * @docs-private
   */
  registerOnTouched(fn) {
    this._onTouched = fn;
  }
  /**
   * Implemented as part of ControlValueAccessor.
   * @docs-private
   */
  setDisabledState(isDisabled) {
    this.disabled = isDisabled;
  }
  /** Selects all chips with value. */
  _setSelectionByValue(value, isUserInput = true) {
    this._clearSelection();
    if (Array.isArray(value)) {
      value.forEach((currentValue) => this._selectValue(currentValue, isUserInput));
    } else {
      this._selectValue(value, isUserInput);
    }
  }
  /** When blurred, marks the field as touched when focus moved outside the chip listbox. */
  _blur() {
    if (!this.disabled) {
      setTimeout(() => {
        if (!this.focused) {
          this._markAsTouched();
        }
      });
    }
  }
  _keydown(event) {
    if (event.keyCode === TAB) {
      super._allowFocusEscape();
    }
  }
  /** Marks the field as touched */
  _markAsTouched() {
    this._onTouched();
    this._changeDetectorRef.markForCheck();
  }
  /** Emits change event to set the model value. */
  _propagateChanges() {
    let valueToEmit = null;
    if (Array.isArray(this.selected)) {
      valueToEmit = this.selected.map((chip) => chip.value);
    } else {
      valueToEmit = this.selected ? this.selected.value : void 0;
    }
    this._value = valueToEmit;
    this.change.emit(new MatChipListboxChange(this, valueToEmit));
    this._onChange(valueToEmit);
    this._changeDetectorRef.markForCheck();
  }
  /**
   * Deselects every chip in the listbox.
   * @param skip Chip that should not be deselected.
   */
  _clearSelection(skip) {
    this._chips.forEach((chip) => {
      if (chip !== skip) {
        chip.deselect();
      }
    });
  }
  /**
   * Finds and selects the chip based on its value.
   * @returns Chip that has the corresponding value.
   */
  _selectValue(value, isUserInput) {
    const correspondingChip = this._chips.find((chip) => {
      return chip.value != null && this.compareWith(chip.value, value);
    });
    if (correspondingChip) {
      isUserInput ? correspondingChip.selectViaInteraction() : correspondingChip.select();
    }
    return correspondingChip;
  }
  /** Syncs the chip-listbox selection state with the individual chips. */
  _syncListboxProperties() {
    if (this._chips) {
      Promise.resolve().then(() => {
        this._chips.forEach((chip) => {
          chip._chipListMultiple = this.multiple;
          chip.chipListSelectable = this._selectable;
          chip._chipListHideSingleSelectionIndicator = this.hideSingleSelectionIndicator;
          chip._changeDetectorRef.markForCheck();
        });
      });
    }
  }
  /** Returns the first selected chip in this listbox, or undefined if no chips are selected. */
  _getFirstSelectedChip() {
    if (Array.isArray(this.selected)) {
      return this.selected.length ? this.selected[0] : void 0;
    } else {
      return this.selected;
    }
  }
  /**
   * Determines if key manager should avoid putting a given chip action in the tab index. Skip
   * non-interactive actions since the user can't do anything with them.
   */
  _skipPredicate(action) {
    return !action.isInteractive;
  }
  static \u0275fac = /* @__PURE__ */ (() => {
    let \u0275MatChipListbox_BaseFactory;
    return function MatChipListbox_Factory(__ngFactoryType__) {
      return (\u0275MatChipListbox_BaseFactory || (\u0275MatChipListbox_BaseFactory = \u0275\u0275getInheritedFactory(_MatChipListbox)))(__ngFactoryType__ || _MatChipListbox);
    };
  })();
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatChipListbox,
    selectors: [["mat-chip-listbox"]],
    contentQueries: function MatChipListbox_ContentQueries(rf, ctx, dirIndex) {
      if (rf & 1) {
        \u0275\u0275contentQuery(dirIndex, MatChipOption, 5);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._chips = _t);
      }
    },
    hostAttrs: [1, "mdc-evolution-chip-set", "mat-mdc-chip-listbox"],
    hostVars: 10,
    hostBindings: function MatChipListbox_HostBindings(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275listener("focus", function MatChipListbox_focus_HostBindingHandler() {
          return ctx.focus();
        })("blur", function MatChipListbox_blur_HostBindingHandler() {
          return ctx._blur();
        })("keydown", function MatChipListbox_keydown_HostBindingHandler($event) {
          return ctx._keydown($event);
        });
      }
      if (rf & 2) {
        \u0275\u0275domProperty("tabIndex", ctx.disabled || ctx.empty ? -1 : ctx.tabIndex);
        \u0275\u0275attribute("role", ctx.role)("aria-required", ctx.role ? ctx.required : null)("aria-disabled", ctx.disabled.toString())("aria-multiselectable", ctx.multiple)("aria-orientation", ctx.ariaOrientation);
        \u0275\u0275classProp("mat-mdc-chip-list-disabled", ctx.disabled)("mat-mdc-chip-list-required", ctx.required);
      }
    },
    inputs: {
      multiple: [2, "multiple", "multiple", booleanAttribute],
      ariaOrientation: [0, "aria-orientation", "ariaOrientation"],
      selectable: [2, "selectable", "selectable", booleanAttribute],
      compareWith: "compareWith",
      required: [2, "required", "required", booleanAttribute],
      hideSingleSelectionIndicator: [2, "hideSingleSelectionIndicator", "hideSingleSelectionIndicator", booleanAttribute],
      value: "value"
    },
    outputs: {
      change: "change"
    },
    features: [\u0275\u0275ProvidersFeature([MAT_CHIP_LISTBOX_CONTROL_VALUE_ACCESSOR]), \u0275\u0275InheritDefinitionFeature],
    ngContentSelectors: _c5,
    decls: 2,
    vars: 0,
    consts: [["role", "presentation", 1, "mdc-evolution-chip-set__chips"]],
    template: function MatChipListbox_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275projectionDef();
        \u0275\u0275domElementStart(0, "div", 0);
        \u0275\u0275projection(1);
        \u0275\u0275domElementEnd();
      }
    },
    styles: [_c6],
    encapsulation: 2,
    changeDetection: 0
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipListbox, [{
    type: Component,
    args: [{
      selector: "mat-chip-listbox",
      template: `
    <div class="mdc-evolution-chip-set__chips" role="presentation">
      <ng-content></ng-content>
    </div>
  `,
      host: {
        "class": "mdc-evolution-chip-set mat-mdc-chip-listbox",
        "[attr.role]": "role",
        "[tabIndex]": "(disabled || empty) ? -1 : tabIndex",
        "[attr.aria-required]": "role ? required : null",
        "[attr.aria-disabled]": "disabled.toString()",
        "[attr.aria-multiselectable]": "multiple",
        "[attr.aria-orientation]": "ariaOrientation",
        "[class.mat-mdc-chip-list-disabled]": "disabled",
        "[class.mat-mdc-chip-list-required]": "required",
        "(focus)": "focus()",
        "(blur)": "_blur()",
        "(keydown)": "_keydown($event)"
      },
      providers: [MAT_CHIP_LISTBOX_CONTROL_VALUE_ACCESSOR],
      encapsulation: ViewEncapsulation.None,
      changeDetection: ChangeDetectionStrategy.OnPush,
      styles: [".mat-mdc-chip-set{display:flex}.mat-mdc-chip-set:focus{outline:none}.mat-mdc-chip-set .mdc-evolution-chip-set__chips{min-width:100%;margin-left:-8px;margin-right:0}.mat-mdc-chip-set .mdc-evolution-chip{margin:4px 0 4px 8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip-set__chips{margin-left:0;margin-right:-8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip{margin-left:0;margin-right:8px}.mdc-evolution-chip-set__chips{display:flex;flex-flow:wrap;min-width:0}.mat-mdc-chip-set-stacked{flex-direction:column;align-items:flex-start}.mat-mdc-chip-set-stacked .mat-mdc-chip{width:100%}.mat-mdc-chip-set-stacked .mdc-evolution-chip__graphic{flex-grow:0}.mat-mdc-chip-set-stacked .mdc-evolution-chip__action--primary{flex-basis:100%;justify-content:start}input.mat-mdc-chip-input{flex:1 0 150px;margin-left:8px}[dir=rtl] input.mat-mdc-chip-input{margin-left:0;margin-right:8px}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-moz-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-webkit-input-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input:-ms-input-placeholder{opacity:1}.mat-mdc-chip-set+input.mat-mdc-chip-input{margin-left:0;margin-right:0}\n"]
    }]
  }], null, {
    multiple: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    ariaOrientation: [{
      type: Input,
      args: ["aria-orientation"]
    }],
    selectable: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    compareWith: [{
      type: Input
    }],
    required: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    hideSingleSelectionIndicator: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    value: [{
      type: Input
    }],
    change: [{
      type: Output
    }],
    _chips: [{
      type: ContentChildren,
      args: [MatChipOption, {
        // We need to use `descendants: true`, because Ivy will no longer match
        // indirect descendants if it's left as false.
        descendants: true
      }]
    }]
  });
})();
var MatChipGridChange = class {
  source;
  value;
  constructor(source, value) {
    this.source = source;
    this.value = value;
  }
};
var MatChipGrid = class _MatChipGrid extends MatChipSet {
  ngControl = inject(NgControl, {
    optional: true,
    self: true
  });
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  controlType = "mat-chip-grid";
  /** The chip input to add more chips */
  _chipInput;
  _defaultRole = "grid";
  _errorStateTracker;
  /**
   * List of element ids to propagate to the chipInput's aria-describedby attribute.
   */
  _ariaDescribedbyIds = [];
  /**
   * Function when touched. Set as part of ControlValueAccessor implementation.
   * @docs-private
   */
  _onTouched = () => {
  };
  /**
   * Function when changed. Set as part of ControlValueAccessor implementation.
   * @docs-private
   */
  _onChange = () => {
  };
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  get disabled() {
    return this.ngControl ? !!this.ngControl.disabled : this._disabled;
  }
  set disabled(value) {
    this._disabled = value;
    this._syncChipsState();
    this.stateChanges.next();
  }
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  get id() {
    return this._chipInput.id;
  }
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  get empty() {
    return (!this._chipInput || this._chipInput.empty) && (!this._chips || this._chips.length === 0);
  }
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  get placeholder() {
    return this._chipInput ? this._chipInput.placeholder : this._placeholder;
  }
  set placeholder(value) {
    this._placeholder = value;
    this.stateChanges.next();
  }
  _placeholder;
  /** Whether any chips or the matChipInput inside of this chip-grid has focus. */
  get focused() {
    return this._chipInput.focused || this._hasFocusedChip();
  }
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  get required() {
    return this._required ?? this.ngControl?.control?.hasValidator(Validators.required) ?? false;
  }
  set required(value) {
    this._required = value;
    this.stateChanges.next();
  }
  _required;
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  get shouldLabelFloat() {
    return !this.empty || this.focused;
  }
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  get value() {
    return this._value;
  }
  set value(value) {
    this._value = value;
  }
  _value = [];
  /** An object used to control when error messages are shown. */
  get errorStateMatcher() {
    return this._errorStateTracker.matcher;
  }
  set errorStateMatcher(value) {
    this._errorStateTracker.matcher = value;
  }
  /** Combined stream of all of the child chips' blur events. */
  get chipBlurChanges() {
    return this._getChipStream((chip) => chip._onBlur);
  }
  /** Emits when the chip grid value has been changed by the user. */
  change = new EventEmitter();
  /**
   * Emits whenever the raw value of the chip-grid changes. This is here primarily
   * to facilitate the two-way binding for the `value` input.
   * @docs-private
   */
  valueChange = new EventEmitter();
  _chips = void 0;
  /**
   * Emits whenever the component state changes and should cause the parent
   * form-field to update. Implemented as part of `MatFormFieldControl`.
   * @docs-private
   */
  stateChanges = new Subject();
  /** Whether the chip grid is in an error state. */
  get errorState() {
    return this._errorStateTracker.errorState;
  }
  set errorState(value) {
    this._errorStateTracker.errorState = value;
  }
  constructor() {
    super();
    const parentForm = inject(NgForm, {
      optional: true
    });
    const parentFormGroup = inject(FormGroupDirective, {
      optional: true
    });
    const defaultErrorStateMatcher = inject(ErrorStateMatcher);
    if (this.ngControl) {
      this.ngControl.valueAccessor = this;
    }
    this._errorStateTracker = new _ErrorStateTracker(defaultErrorStateMatcher, this.ngControl, parentFormGroup, parentForm, this.stateChanges);
  }
  ngAfterContentInit() {
    this.chipBlurChanges.pipe(takeUntil(this._destroyed)).subscribe(() => {
      this._blur();
      this.stateChanges.next();
    });
    merge(this.chipFocusChanges, this._chips.changes).pipe(takeUntil(this._destroyed)).subscribe(() => this.stateChanges.next());
  }
  ngAfterViewInit() {
    super.ngAfterViewInit();
    if (!this._chipInput && (typeof ngDevMode === "undefined" || ngDevMode)) {
      throw Error("mat-chip-grid must be used in combination with matChipInputFor.");
    }
  }
  ngDoCheck() {
    if (this.ngControl) {
      this.updateErrorState();
    }
  }
  ngOnDestroy() {
    super.ngOnDestroy();
    this.stateChanges.complete();
  }
  /** Associates an HTML input element with this chip grid. */
  registerInput(inputElement) {
    this._chipInput = inputElement;
    this._chipInput.setDescribedByIds(this._ariaDescribedbyIds);
  }
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  onContainerClick(event) {
    if (!this.disabled && !this._originatesFromChip(event)) {
      this.focus();
    }
  }
  /**
   * Focuses the first chip in this chip grid, or the associated input when there
   * are no eligible chips.
   */
  focus() {
    if (this.disabled || this._chipInput.focused) {
      return;
    }
    if (!this._chips.length || this._chips.first.disabled) {
      Promise.resolve().then(() => this._chipInput.focus());
    } else {
      const activeItem = this._keyManager.activeItem;
      if (activeItem) {
        activeItem.focus();
      } else {
        this._keyManager.setFirstItemActive();
      }
    }
    this.stateChanges.next();
  }
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  get describedByIds() {
    return this._chipInput?.describedByIds || [];
  }
  /**
   * Implemented as part of MatFormFieldControl.
   * @docs-private
   */
  setDescribedByIds(ids) {
    this._ariaDescribedbyIds = ids;
    this._chipInput?.setDescribedByIds(ids);
  }
  /**
   * Implemented as part of ControlValueAccessor.
   * @docs-private
   */
  writeValue(value) {
    this._value = value;
  }
  /**
   * Implemented as part of ControlValueAccessor.
   * @docs-private
   */
  registerOnChange(fn) {
    this._onChange = fn;
  }
  /**
   * Implemented as part of ControlValueAccessor.
   * @docs-private
   */
  registerOnTouched(fn) {
    this._onTouched = fn;
  }
  /**
   * Implemented as part of ControlValueAccessor.
   * @docs-private
   */
  setDisabledState(isDisabled) {
    this.disabled = isDisabled;
    this.stateChanges.next();
  }
  /** Refreshes the error state of the chip grid. */
  updateErrorState() {
    this._errorStateTracker.updateErrorState();
  }
  /** When blurred, mark the field as touched when focus moved outside the chip grid. */
  _blur() {
    if (!this.disabled) {
      setTimeout(() => {
        if (!this.focused) {
          this._propagateChanges();
          this._markAsTouched();
        }
      });
    }
  }
  /**
   * Removes the `tabindex` from the chip grid and resets it back afterwards, allowing the
   * user to tab out of it. This prevents the grid from capturing focus and redirecting
   * it back to the first chip, creating a focus trap, if it user tries to tab away.
   */
  _allowFocusEscape() {
    if (!this._chipInput.focused) {
      super._allowFocusEscape();
    }
  }
  /** Handles custom keyboard events. */
  _handleKeydown(event) {
    const keyCode = event.keyCode;
    const activeItem = this._keyManager.activeItem;
    if (keyCode === TAB) {
      if (this._chipInput.focused && hasModifierKey(event, "shiftKey") && this._chips.length && !this._chips.last.disabled) {
        event.preventDefault();
        if (activeItem) {
          this._keyManager.setActiveItem(activeItem);
        } else {
          this._focusLastChip();
        }
      } else {
        super._allowFocusEscape();
      }
    } else if (!this._chipInput.focused) {
      if ((keyCode === UP_ARROW || keyCode === DOWN_ARROW) && activeItem) {
        const eligibleActions = this._chipActions.filter((action) => action._isPrimary === activeItem._isPrimary && !this._skipPredicate(action));
        const currentIndex = eligibleActions.indexOf(activeItem);
        const delta = event.keyCode === UP_ARROW ? -1 : 1;
        event.preventDefault();
        if (currentIndex > -1 && this._isValidIndex(currentIndex + delta)) {
          this._keyManager.setActiveItem(eligibleActions[currentIndex + delta]);
        }
      } else {
        super._handleKeydown(event);
      }
    }
    this.stateChanges.next();
  }
  _focusLastChip() {
    if (this._chips.length) {
      this._chips.last.focus();
    }
  }
  /** Emits change event to set the model value. */
  _propagateChanges() {
    const valueToEmit = this._chips.length ? this._chips.toArray().map((chip) => chip.value) : [];
    this._value = valueToEmit;
    this.change.emit(new MatChipGridChange(this, valueToEmit));
    this.valueChange.emit(valueToEmit);
    this._onChange(valueToEmit);
    this._changeDetectorRef.markForCheck();
  }
  /** Mark the field as touched */
  _markAsTouched() {
    this._onTouched();
    this._changeDetectorRef.markForCheck();
    this.stateChanges.next();
  }
  static \u0275fac = function MatChipGrid_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatChipGrid)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({
    type: _MatChipGrid,
    selectors: [["mat-chip-grid"]],
    contentQueries: function MatChipGrid_ContentQueries(rf, ctx, dirIndex) {
      if (rf & 1) {
        \u0275\u0275contentQuery(dirIndex, MatChipRow, 5);
      }
      if (rf & 2) {
        let _t;
        \u0275\u0275queryRefresh(_t = \u0275\u0275loadQuery()) && (ctx._chips = _t);
      }
    },
    hostAttrs: [1, "mat-mdc-chip-set", "mat-mdc-chip-grid", "mdc-evolution-chip-set"],
    hostVars: 10,
    hostBindings: function MatChipGrid_HostBindings(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275listener("focus", function MatChipGrid_focus_HostBindingHandler() {
          return ctx.focus();
        })("blur", function MatChipGrid_blur_HostBindingHandler() {
          return ctx._blur();
        });
      }
      if (rf & 2) {
        \u0275\u0275attribute("role", ctx.role)("tabindex", ctx.disabled || ctx._chips && ctx._chips.length === 0 ? -1 : ctx.tabIndex)("aria-disabled", ctx.disabled.toString())("aria-invalid", ctx.errorState);
        \u0275\u0275classProp("mat-mdc-chip-list-disabled", ctx.disabled)("mat-mdc-chip-list-invalid", ctx.errorState)("mat-mdc-chip-list-required", ctx.required);
      }
    },
    inputs: {
      disabled: [2, "disabled", "disabled", booleanAttribute],
      placeholder: "placeholder",
      required: [2, "required", "required", booleanAttribute],
      value: "value",
      errorStateMatcher: "errorStateMatcher"
    },
    outputs: {
      change: "change",
      valueChange: "valueChange"
    },
    features: [\u0275\u0275ProvidersFeature([{
      provide: MatFormFieldControl,
      useExisting: _MatChipGrid
    }]), \u0275\u0275InheritDefinitionFeature],
    ngContentSelectors: _c5,
    decls: 2,
    vars: 0,
    consts: [["role", "presentation", 1, "mdc-evolution-chip-set__chips"]],
    template: function MatChipGrid_Template(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275projectionDef();
        \u0275\u0275domElementStart(0, "div", 0);
        \u0275\u0275projection(1);
        \u0275\u0275domElementEnd();
      }
    },
    styles: [_c6],
    encapsulation: 2,
    changeDetection: 0
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipGrid, [{
    type: Component,
    args: [{
      selector: "mat-chip-grid",
      template: `
    <div class="mdc-evolution-chip-set__chips" role="presentation">
      <ng-content></ng-content>
    </div>
  `,
      host: {
        "class": "mat-mdc-chip-set mat-mdc-chip-grid mdc-evolution-chip-set",
        "[attr.role]": "role",
        "[attr.tabindex]": "(disabled || (_chips && _chips.length === 0)) ? -1 : tabIndex",
        "[attr.aria-disabled]": "disabled.toString()",
        "[attr.aria-invalid]": "errorState",
        "[class.mat-mdc-chip-list-disabled]": "disabled",
        "[class.mat-mdc-chip-list-invalid]": "errorState",
        "[class.mat-mdc-chip-list-required]": "required",
        "(focus)": "focus()",
        "(blur)": "_blur()"
      },
      providers: [{
        provide: MatFormFieldControl,
        useExisting: MatChipGrid
      }],
      encapsulation: ViewEncapsulation.None,
      changeDetection: ChangeDetectionStrategy.OnPush,
      styles: [".mat-mdc-chip-set{display:flex}.mat-mdc-chip-set:focus{outline:none}.mat-mdc-chip-set .mdc-evolution-chip-set__chips{min-width:100%;margin-left:-8px;margin-right:0}.mat-mdc-chip-set .mdc-evolution-chip{margin:4px 0 4px 8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip-set__chips{margin-left:0;margin-right:-8px}[dir=rtl] .mat-mdc-chip-set .mdc-evolution-chip{margin-left:0;margin-right:8px}.mdc-evolution-chip-set__chips{display:flex;flex-flow:wrap;min-width:0}.mat-mdc-chip-set-stacked{flex-direction:column;align-items:flex-start}.mat-mdc-chip-set-stacked .mat-mdc-chip{width:100%}.mat-mdc-chip-set-stacked .mdc-evolution-chip__graphic{flex-grow:0}.mat-mdc-chip-set-stacked .mdc-evolution-chip__action--primary{flex-basis:100%;justify-content:start}input.mat-mdc-chip-input{flex:1 0 150px;margin-left:8px}[dir=rtl] input.mat-mdc-chip-input{margin-left:0;margin-right:8px}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-moz-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input::-webkit-input-placeholder{opacity:1}.mat-mdc-form-field:not(.mat-form-field-hide-placeholder) input.mat-mdc-chip-input:-ms-input-placeholder{opacity:1}.mat-mdc-chip-set+input.mat-mdc-chip-input{margin-left:0;margin-right:0}\n"]
    }]
  }], () => [], {
    disabled: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    placeholder: [{
      type: Input
    }],
    required: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    value: [{
      type: Input
    }],
    errorStateMatcher: [{
      type: Input
    }],
    change: [{
      type: Output
    }],
    valueChange: [{
      type: Output
    }],
    _chips: [{
      type: ContentChildren,
      args: [MatChipRow, {
        // We need to use `descendants: true`, because Ivy will no longer match
        // indirect descendants if it's left as false.
        descendants: true
      }]
    }]
  });
})();
var MatChipInput = class _MatChipInput {
  _elementRef = inject(ElementRef);
  /** Whether the control is focused. */
  focused = false;
  /** Register input for chip list */
  get chipGrid() {
    return this._chipGrid;
  }
  set chipGrid(value) {
    if (value) {
      this._chipGrid = value;
      this._chipGrid.registerInput(this);
    }
  }
  _chipGrid;
  /**
   * Whether or not the chipEnd event will be emitted when the input is blurred.
   */
  addOnBlur = false;
  /**
   * The list of key codes that will trigger a chipEnd event.
   *
   * Defaults to `[ENTER]`.
   */
  separatorKeyCodes;
  /** Emitted when a chip is to be added. */
  chipEnd = new EventEmitter();
  /** The input's placeholder text. */
  placeholder = "";
  /** Unique id for the input. */
  id = inject(_IdGenerator).getId("mat-mdc-chip-list-input-");
  /** Whether the input is disabled. */
  get disabled() {
    return this._disabled || this._chipGrid && this._chipGrid.disabled;
  }
  set disabled(value) {
    this._disabled = value;
  }
  _disabled = false;
  /** Whether the input is readonly. */
  readonly = false;
  /** Whether the input should remain interactive when it is disabled. */
  disabledInteractive;
  /** Whether the input is empty. */
  get empty() {
    return !this.inputElement.value;
  }
  /** The native input element to which this directive is attached. */
  inputElement;
  constructor() {
    const defaultOptions = inject(MAT_CHIPS_DEFAULT_OPTIONS);
    const formField = inject(MAT_FORM_FIELD, {
      optional: true
    });
    this.inputElement = this._elementRef.nativeElement;
    this.separatorKeyCodes = defaultOptions.separatorKeyCodes;
    this.disabledInteractive = defaultOptions.inputDisabledInteractive ?? false;
    if (formField) {
      this.inputElement.classList.add("mat-mdc-form-field-input-control");
    }
  }
  ngOnChanges() {
    this._chipGrid.stateChanges.next();
  }
  ngOnDestroy() {
    this.chipEnd.complete();
  }
  /** Utility method to make host definition/tests more clear. */
  _keydown(event) {
    if (this.empty && event.keyCode === BACKSPACE) {
      if (!event.repeat) {
        this._chipGrid._focusLastChip();
      }
      event.preventDefault();
    } else {
      this._emitChipEnd(event);
    }
  }
  /** Checks to see if the blur should emit the (chipEnd) event. */
  _blur() {
    if (this.addOnBlur) {
      this._emitChipEnd();
    }
    this.focused = false;
    if (!this._chipGrid.focused) {
      this._chipGrid._blur();
    }
    this._chipGrid.stateChanges.next();
  }
  _focus() {
    this.focused = true;
    this._chipGrid.stateChanges.next();
  }
  /** Checks to see if the (chipEnd) event needs to be emitted. */
  _emitChipEnd(event) {
    if (!event || this._isSeparatorKey(event) && !event.repeat) {
      this.chipEnd.emit({
        input: this.inputElement,
        value: this.inputElement.value,
        chipInput: this
      });
      event?.preventDefault();
    }
  }
  _onInput() {
    this._chipGrid.stateChanges.next();
  }
  /** Focuses the input. */
  focus() {
    this.inputElement.focus();
  }
  /** Clears the input */
  clear() {
    this.inputElement.value = "";
  }
  /**
   * Implemented as part of MatChipTextControl.
   * @docs-private
   */
  get describedByIds() {
    const element = this._elementRef.nativeElement;
    const existingDescribedBy = element.getAttribute("aria-describedby");
    return existingDescribedBy?.split(" ") || [];
  }
  setDescribedByIds(ids) {
    const element = this._elementRef.nativeElement;
    if (ids.length) {
      element.setAttribute("aria-describedby", ids.join(" "));
    } else {
      element.removeAttribute("aria-describedby");
    }
  }
  /** Checks whether a keycode is one of the configured separators. */
  _isSeparatorKey(event) {
    return !hasModifierKey(event) && new Set(this.separatorKeyCodes).has(event.keyCode);
  }
  /** Gets the value to set on the `readonly` attribute. */
  _getReadonlyAttribute() {
    return this.readonly || this.disabled && this.disabledInteractive ? "true" : null;
  }
  static \u0275fac = function MatChipInput_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatChipInput)();
  };
  static \u0275dir = /* @__PURE__ */ \u0275\u0275defineDirective({
    type: _MatChipInput,
    selectors: [["input", "matChipInputFor", ""]],
    hostAttrs: [1, "mat-mdc-chip-input", "mat-mdc-input-element", "mdc-text-field__input", "mat-input-element"],
    hostVars: 8,
    hostBindings: function MatChipInput_HostBindings(rf, ctx) {
      if (rf & 1) {
        \u0275\u0275listener("keydown", function MatChipInput_keydown_HostBindingHandler($event) {
          return ctx._keydown($event);
        })("blur", function MatChipInput_blur_HostBindingHandler() {
          return ctx._blur();
        })("focus", function MatChipInput_focus_HostBindingHandler() {
          return ctx._focus();
        })("input", function MatChipInput_input_HostBindingHandler() {
          return ctx._onInput();
        });
      }
      if (rf & 2) {
        \u0275\u0275domProperty("id", ctx.id);
        \u0275\u0275attribute("disabled", ctx.disabled && !ctx.disabledInteractive ? "" : null)("placeholder", ctx.placeholder || null)("aria-invalid", ctx._chipGrid && ctx._chipGrid.ngControl ? ctx._chipGrid.ngControl.invalid : null)("aria-required", ctx._chipGrid && ctx._chipGrid.required || null)("aria-disabled", ctx.disabled && ctx.disabledInteractive ? "true" : null)("readonly", ctx._getReadonlyAttribute())("required", ctx._chipGrid && ctx._chipGrid.required || null);
      }
    },
    inputs: {
      chipGrid: [0, "matChipInputFor", "chipGrid"],
      addOnBlur: [2, "matChipInputAddOnBlur", "addOnBlur", booleanAttribute],
      separatorKeyCodes: [0, "matChipInputSeparatorKeyCodes", "separatorKeyCodes"],
      placeholder: "placeholder",
      id: "id",
      disabled: [2, "disabled", "disabled", booleanAttribute],
      readonly: [2, "readonly", "readonly", booleanAttribute],
      disabledInteractive: [2, "matChipInputDisabledInteractive", "disabledInteractive", booleanAttribute]
    },
    outputs: {
      chipEnd: "matChipInputTokenEnd"
    },
    exportAs: ["matChipInput", "matChipInputFor"],
    features: [\u0275\u0275NgOnChangesFeature]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipInput, [{
    type: Directive,
    args: [{
      selector: "input[matChipInputFor]",
      exportAs: "matChipInput, matChipInputFor",
      host: {
        // TODO: eventually we should remove `mat-input-element` from here since it comes from the
        // non-MDC version of the input. It's currently being kept for backwards compatibility, because
        // the MDC chips were landed initially with it.
        "class": "mat-mdc-chip-input mat-mdc-input-element mdc-text-field__input mat-input-element",
        "(keydown)": "_keydown($event)",
        "(blur)": "_blur()",
        "(focus)": "_focus()",
        "(input)": "_onInput()",
        "[id]": "id",
        "[attr.disabled]": 'disabled && !disabledInteractive ? "" : null',
        "[attr.placeholder]": "placeholder || null",
        "[attr.aria-invalid]": "_chipGrid && _chipGrid.ngControl ? _chipGrid.ngControl.invalid : null",
        "[attr.aria-required]": "_chipGrid && _chipGrid.required || null",
        "[attr.aria-disabled]": 'disabled && disabledInteractive ? "true" : null',
        "[attr.readonly]": "_getReadonlyAttribute()",
        "[attr.required]": "_chipGrid && _chipGrid.required || null"
      }
    }]
  }], () => [], {
    chipGrid: [{
      type: Input,
      args: ["matChipInputFor"]
    }],
    addOnBlur: [{
      type: Input,
      args: [{
        alias: "matChipInputAddOnBlur",
        transform: booleanAttribute
      }]
    }],
    separatorKeyCodes: [{
      type: Input,
      args: ["matChipInputSeparatorKeyCodes"]
    }],
    chipEnd: [{
      type: Output,
      args: ["matChipInputTokenEnd"]
    }],
    placeholder: [{
      type: Input
    }],
    id: [{
      type: Input
    }],
    disabled: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    readonly: [{
      type: Input,
      args: [{
        transform: booleanAttribute
      }]
    }],
    disabledInteractive: [{
      type: Input,
      args: [{
        alias: "matChipInputDisabledInteractive",
        transform: booleanAttribute
      }]
    }]
  });
})();
var CHIP_DECLARATIONS = [MatChip, MatChipAvatar, MatChipEdit, MatChipEditInput, MatChipGrid, MatChipInput, MatChipListbox, MatChipOption, MatChipRemove, MatChipRow, MatChipSet, MatChipTrailingIcon];
var MatChipsModule = class _MatChipsModule {
  static \u0275fac = function MatChipsModule_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatChipsModule)();
  };
  static \u0275mod = /* @__PURE__ */ \u0275\u0275defineNgModule({
    type: _MatChipsModule,
    imports: [MatCommonModule, MatRippleModule, MatChipAction, MatChip, MatChipAvatar, MatChipEdit, MatChipEditInput, MatChipGrid, MatChipInput, MatChipListbox, MatChipOption, MatChipRemove, MatChipRow, MatChipSet, MatChipTrailingIcon],
    exports: [MatCommonModule, MatChip, MatChipAvatar, MatChipEdit, MatChipEditInput, MatChipGrid, MatChipInput, MatChipListbox, MatChipOption, MatChipRemove, MatChipRow, MatChipSet, MatChipTrailingIcon]
  });
  static \u0275inj = /* @__PURE__ */ \u0275\u0275defineInjector({
    providers: [ErrorStateMatcher, {
      provide: MAT_CHIPS_DEFAULT_OPTIONS,
      useValue: {
        separatorKeyCodes: [ENTER]
      }
    }],
    imports: [MatCommonModule, MatRippleModule, MatCommonModule]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatChipsModule, [{
    type: NgModule,
    args: [{
      imports: [MatCommonModule, MatRippleModule, MatChipAction, CHIP_DECLARATIONS],
      exports: [MatCommonModule, CHIP_DECLARATIONS],
      providers: [ErrorStateMatcher, {
        provide: MAT_CHIPS_DEFAULT_OPTIONS,
        useValue: {
          separatorKeyCodes: [ENTER]
        }
      }]
    }]
  }], null, null);
})();

// node_modules/@angular/material/fesm2022/tooltip-module.mjs
var MatTooltipModule = class _MatTooltipModule {
  static \u0275fac = function MatTooltipModule_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MatTooltipModule)();
  };
  static \u0275mod = /* @__PURE__ */ \u0275\u0275defineNgModule({
    type: _MatTooltipModule,
    imports: [A11yModule, OverlayModule, MatCommonModule, MatTooltip, TooltipComponent],
    exports: [MatTooltip, TooltipComponent, MatCommonModule, CdkScrollableModule]
  });
  static \u0275inj = /* @__PURE__ */ \u0275\u0275defineInjector({
    providers: [MAT_TOOLTIP_SCROLL_STRATEGY_FACTORY_PROVIDER],
    imports: [A11yModule, OverlayModule, MatCommonModule, MatCommonModule, CdkScrollableModule]
  });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MatTooltipModule, [{
    type: NgModule,
    args: [{
      imports: [A11yModule, OverlayModule, MatCommonModule, MatTooltip, TooltipComponent],
      exports: [MatTooltip, TooltipComponent, MatCommonModule, CdkScrollableModule],
      providers: [MAT_TOOLTIP_SCROLL_STRATEGY_FACTORY_PROVIDER]
    }]
  }], null, null);
})();

// src/app/features/miembros/miembros-list/miembros-list.component.ts
var _c04 = (a0) => [a0];
var _c14 = (a0) => [a0, "editar"];
function MiembrosListComponent_Conditional_14_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "div", 6);
    \u0275\u0275element(1, "mat-spinner", 7);
    \u0275\u0275elementEnd();
  }
}
function MiembrosListComponent_Conditional_15_th_3_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "th", 21);
    \u0275\u0275text(1, "Nombre");
    \u0275\u0275elementEnd();
  }
}
function MiembrosListComponent_Conditional_15_td_4_Conditional_3_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "div", 23);
    \u0275\u0275text(1);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const m_r1 = \u0275\u0275nextContext().$implicit;
    \u0275\u0275advance();
    \u0275\u0275textInterpolate(m_r1.email);
  }
}
function MiembrosListComponent_Conditional_15_td_4_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "td", 22)(1, "strong");
    \u0275\u0275text(2);
    \u0275\u0275elementEnd();
    \u0275\u0275conditionalCreate(3, MiembrosListComponent_Conditional_15_td_4_Conditional_3_Template, 2, 1, "div", 23);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const m_r1 = ctx.$implicit;
    \u0275\u0275advance(2);
    \u0275\u0275textInterpolate2("", m_r1.nombre, " ", m_r1.apellido);
    \u0275\u0275advance();
    \u0275\u0275conditional(m_r1.email ? 3 : -1);
  }
}
function MiembrosListComponent_Conditional_15_th_6_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "th", 21);
    \u0275\u0275text(1, "Estado");
    \u0275\u0275elementEnd();
  }
}
function MiembrosListComponent_Conditional_15_td_7_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "td", 22)(1, "span", 24);
    \u0275\u0275text(2);
    \u0275\u0275elementEnd()();
  }
  if (rf & 2) {
    const m_r2 = ctx.$implicit;
    const ctx_r2 = \u0275\u0275nextContext(2);
    \u0275\u0275advance();
    \u0275\u0275classMap(ctx_r2.estadoClass(m_r2.estado));
    \u0275\u0275advance();
    \u0275\u0275textInterpolate(m_r2.estado);
  }
}
function MiembrosListComponent_Conditional_15_th_9_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "th", 21);
    \u0275\u0275text(1, "Tel\xE9fono");
    \u0275\u0275elementEnd();
  }
}
function MiembrosListComponent_Conditional_15_td_10_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "td", 22);
    \u0275\u0275text(1);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const m_r4 = ctx.$implicit;
    \u0275\u0275advance();
    \u0275\u0275textInterpolate(m_r4.telefono ?? "\u2014");
  }
}
function MiembrosListComponent_Conditional_15_th_12_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "th", 21);
    \u0275\u0275text(1, "L\xEDder c\xE9lula");
    \u0275\u0275elementEnd();
  }
}
function MiembrosListComponent_Conditional_15_td_13_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "td", 22);
    \u0275\u0275text(1);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const m_r5 = ctx.$implicit;
    \u0275\u0275advance();
    \u0275\u0275textInterpolate(m_r5.liderNombre ?? "\u2014");
  }
}
function MiembrosListComponent_Conditional_15_th_15_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "th", 21);
    \u0275\u0275text(1, "Etapa actual");
    \u0275\u0275elementEnd();
  }
}
function MiembrosListComponent_Conditional_15_td_16_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "td", 22);
    \u0275\u0275text(1);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const m_r6 = ctx.$implicit;
    \u0275\u0275advance();
    \u0275\u0275textInterpolate(m_r6.etapaActual ?? "\u2014");
  }
}
function MiembrosListComponent_Conditional_15_th_18_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275element(0, "th", 21);
  }
}
function MiembrosListComponent_Conditional_15_td_19_Template(rf, ctx) {
  if (rf & 1) {
    const _r7 = \u0275\u0275getCurrentView();
    \u0275\u0275elementStart(0, "td", 25)(1, "a", 26)(2, "mat-icon");
    \u0275\u0275text(3, "visibility");
    \u0275\u0275elementEnd()();
    \u0275\u0275elementStart(4, "a", 27)(5, "mat-icon");
    \u0275\u0275text(6, "edit");
    \u0275\u0275elementEnd()();
    \u0275\u0275elementStart(7, "button", 28);
    \u0275\u0275listener("click", function MiembrosListComponent_Conditional_15_td_19_Template_button_click_7_listener() {
      const m_r8 = \u0275\u0275restoreView(_r7).$implicit;
      const ctx_r2 = \u0275\u0275nextContext(2);
      return \u0275\u0275resetView(ctx_r2.eliminar(m_r8.miembroId));
    });
    \u0275\u0275elementStart(8, "mat-icon");
    \u0275\u0275text(9, "delete");
    \u0275\u0275elementEnd()()();
  }
  if (rf & 2) {
    const m_r8 = ctx.$implicit;
    \u0275\u0275advance();
    \u0275\u0275property("routerLink", \u0275\u0275pureFunction1(2, _c04, m_r8.miembroId));
    \u0275\u0275advance(3);
    \u0275\u0275property("routerLink", \u0275\u0275pureFunction1(4, _c14, m_r8.miembroId));
  }
}
function MiembrosListComponent_Conditional_15_tr_20_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275element(0, "tr", 29);
  }
}
function MiembrosListComponent_Conditional_15_tr_21_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275element(0, "tr", 30);
  }
}
function MiembrosListComponent_Conditional_15_Conditional_22_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "div", 20)(1, "mat-icon");
    \u0275\u0275text(2, "people_outline");
    \u0275\u0275elementEnd();
    \u0275\u0275elementStart(3, "p");
    \u0275\u0275text(4, "No hay miembros registrados");
    \u0275\u0275elementEnd()();
  }
}
function MiembrosListComponent_Conditional_15_Template(rf, ctx) {
  if (rf & 1) {
    \u0275\u0275elementStart(0, "mat-card")(1, "table", 8);
    \u0275\u0275elementContainerStart(2, 9);
    \u0275\u0275template(3, MiembrosListComponent_Conditional_15_th_3_Template, 2, 0, "th", 10)(4, MiembrosListComponent_Conditional_15_td_4_Template, 4, 3, "td", 11);
    \u0275\u0275elementContainerEnd();
    \u0275\u0275elementContainerStart(5, 12);
    \u0275\u0275template(6, MiembrosListComponent_Conditional_15_th_6_Template, 2, 0, "th", 10)(7, MiembrosListComponent_Conditional_15_td_7_Template, 3, 3, "td", 11);
    \u0275\u0275elementContainerEnd();
    \u0275\u0275elementContainerStart(8, 13);
    \u0275\u0275template(9, MiembrosListComponent_Conditional_15_th_9_Template, 2, 0, "th", 10)(10, MiembrosListComponent_Conditional_15_td_10_Template, 2, 1, "td", 11);
    \u0275\u0275elementContainerEnd();
    \u0275\u0275elementContainerStart(11, 14);
    \u0275\u0275template(12, MiembrosListComponent_Conditional_15_th_12_Template, 2, 0, "th", 10)(13, MiembrosListComponent_Conditional_15_td_13_Template, 2, 1, "td", 11);
    \u0275\u0275elementContainerEnd();
    \u0275\u0275elementContainerStart(14, 15);
    \u0275\u0275template(15, MiembrosListComponent_Conditional_15_th_15_Template, 2, 0, "th", 10)(16, MiembrosListComponent_Conditional_15_td_16_Template, 2, 1, "td", 11);
    \u0275\u0275elementContainerEnd();
    \u0275\u0275elementContainerStart(17, 16);
    \u0275\u0275template(18, MiembrosListComponent_Conditional_15_th_18_Template, 1, 0, "th", 10)(19, MiembrosListComponent_Conditional_15_td_19_Template, 10, 6, "td", 17);
    \u0275\u0275elementContainerEnd();
    \u0275\u0275template(20, MiembrosListComponent_Conditional_15_tr_20_Template, 1, 0, "tr", 18)(21, MiembrosListComponent_Conditional_15_tr_21_Template, 1, 0, "tr", 19);
    \u0275\u0275elementEnd();
    \u0275\u0275conditionalCreate(22, MiembrosListComponent_Conditional_15_Conditional_22_Template, 5, 0, "div", 20);
    \u0275\u0275elementEnd();
  }
  if (rf & 2) {
    const ctx_r2 = \u0275\u0275nextContext();
    \u0275\u0275advance();
    \u0275\u0275property("dataSource", ctx_r2.miembros());
    \u0275\u0275advance(19);
    \u0275\u0275property("matHeaderRowDef", ctx_r2.columns);
    \u0275\u0275advance();
    \u0275\u0275property("matRowDefColumns", ctx_r2.columns);
    \u0275\u0275advance();
    \u0275\u0275conditional(ctx_r2.miembros().length === 0 ? 22 : -1);
  }
}
var MiembrosListComponent = class _MiembrosListComponent {
  svc = inject(MiembrosService);
  auth = inject(AuthService);
  miembros = signal([], ...ngDevMode ? [{ debugName: "miembros" }] : []);
  loading = signal(false, ...ngDevMode ? [{ debugName: "loading" }] : []);
  busqueda = signal("", ...ngDevMode ? [{ debugName: "busqueda" }] : []);
  columns = ["nombre", "estado", "telefono", "lider", "etapa", "acciones"];
  ngOnInit() {
    this.cargar();
  }
  cargar() {
    this.loading.set(true);
    const sedeId = this.auth.currentUser().sedeId;
    this.svc.getAll(sedeId, this.busqueda() || void 0).subscribe({
      next: (data) => {
        this.miembros.set(data);
        this.loading.set(false);
      },
      error: () => this.loading.set(false)
    });
  }
  eliminar(id) {
    if (!confirm("\xBFEliminar este miembro?"))
      return;
    this.svc.delete(id).subscribe(() => this.cargar());
  }
  estadoClass(estado) {
    return `chip-${estado.toLowerCase()}`;
  }
  static \u0275fac = function MiembrosListComponent_Factory(__ngFactoryType__) {
    return new (__ngFactoryType__ || _MiembrosListComponent)();
  };
  static \u0275cmp = /* @__PURE__ */ \u0275\u0275defineComponent({ type: _MiembrosListComponent, selectors: [["app-miembros-list"]], decls: 16, vars: 2, consts: [[1, "list-header"], [1, "list-actions"], ["appearance", "outline", 1, "search-field"], ["matInput", "", "placeholder", "Nombre, apellido o c\xE9dula", 3, "ngModelChange", "keyup.enter", "ngModel"], ["matSuffix", ""], ["mat-raised-button", "", "color", "primary", "routerLink", "nuevo"], [1, "loading-center"], ["diameter", "40"], ["mat-table", "", 1, "full-width", 3, "dataSource"], ["matColumnDef", "nombre"], ["mat-header-cell", "", 4, "matHeaderCellDef"], ["mat-cell", "", 4, "matCellDef"], ["matColumnDef", "estado"], ["matColumnDef", "telefono"], ["matColumnDef", "lider"], ["matColumnDef", "etapa"], ["matColumnDef", "acciones"], ["mat-cell", "", "class", "actions-cell", 4, "matCellDef"], ["mat-header-row", "", 4, "matHeaderRowDef"], ["mat-row", "", 4, "matRowDef", "matRowDefColumns"], [1, "empty-state"], ["mat-header-cell", ""], ["mat-cell", ""], [1, "sub-text"], [1, "estado-chip"], ["mat-cell", "", 1, "actions-cell"], ["mat-icon-button", "", "matTooltip", "Ver detalle", 3, "routerLink"], ["mat-icon-button", "", "matTooltip", "Editar", 3, "routerLink"], ["mat-icon-button", "", "color", "warn", "matTooltip", "Eliminar", 3, "click"], ["mat-header-row", ""], ["mat-row", ""]], template: function MiembrosListComponent_Template(rf, ctx) {
    if (rf & 1) {
      \u0275\u0275elementStart(0, "div", 0)(1, "h2");
      \u0275\u0275text(2, "Miembros");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(3, "div", 1)(4, "mat-form-field", 2)(5, "mat-label");
      \u0275\u0275text(6, "Buscar");
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(7, "input", 3);
      \u0275\u0275twoWayListener("ngModelChange", function MiembrosListComponent_Template_input_ngModelChange_7_listener($event) {
        \u0275\u0275twoWayBindingSet(ctx.busqueda, $event) || (ctx.busqueda = $event);
        return $event;
      });
      \u0275\u0275listener("keyup.enter", function MiembrosListComponent_Template_input_keyup_enter_7_listener() {
        return ctx.cargar();
      });
      \u0275\u0275elementEnd();
      \u0275\u0275elementStart(8, "mat-icon", 4);
      \u0275\u0275text(9, "search");
      \u0275\u0275elementEnd()();
      \u0275\u0275elementStart(10, "button", 5)(11, "mat-icon");
      \u0275\u0275text(12, "person_add");
      \u0275\u0275elementEnd();
      \u0275\u0275text(13, " Nuevo miembro ");
      \u0275\u0275elementEnd()()();
      \u0275\u0275conditionalCreate(14, MiembrosListComponent_Conditional_14_Template, 2, 0, "div", 6)(15, MiembrosListComponent_Conditional_15_Template, 23, 4, "mat-card");
    }
    if (rf & 2) {
      \u0275\u0275advance(7);
      \u0275\u0275twoWayProperty("ngModel", ctx.busqueda);
      \u0275\u0275advance(7);
      \u0275\u0275conditional(ctx.loading() ? 14 : 15);
    }
  }, dependencies: [
    RouterLink,
    FormsModule,
    DefaultValueAccessor,
    NgControlStatus,
    NgModel,
    MatTableModule,
    MatTable,
    MatHeaderCellDef,
    MatHeaderRowDef,
    MatColumnDef,
    MatCellDef,
    MatRowDef,
    MatHeaderCell,
    MatCell,
    MatHeaderRow,
    MatRow,
    MatButtonModule,
    MatButton,
    MatIconButton,
    MatIconModule,
    MatIcon,
    MatFormFieldModule,
    MatFormField,
    MatLabel,
    MatSuffix,
    MatInputModule,
    MatInput,
    MatChipsModule,
    MatProgressSpinnerModule,
    MatProgressSpinner,
    MatCardModule,
    MatCard,
    MatTooltipModule,
    MatTooltip
  ], styles: ["\n\n.list-header[_ngcontent-%COMP%] {\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n  margin-bottom: 16px;\n  flex-wrap: wrap;\n  gap: 12px;\n}\n.list-header[_ngcontent-%COMP%]   h2[_ngcontent-%COMP%] {\n  margin: 0;\n  font-size: 22px;\n  font-weight: 500;\n}\n.list-actions[_ngcontent-%COMP%] {\n  display: flex;\n  align-items: center;\n  gap: 12px;\n}\n.search-field[_ngcontent-%COMP%] {\n  width: 280px;\n}\n.full-width[_ngcontent-%COMP%] {\n  width: 100%;\n}\n.sub-text[_ngcontent-%COMP%] {\n  font-size: 12px;\n  color: #666;\n}\n.estado-chip[_ngcontent-%COMP%] {\n  padding: 4px 10px;\n  border-radius: 12px;\n  font-size: 12px;\n  font-weight: 500;\n}\n.actions-cell[_ngcontent-%COMP%] {\n  white-space: nowrap;\n}\n.loading-center[_ngcontent-%COMP%] {\n  display: flex;\n  justify-content: center;\n  padding: 48px;\n}\n.empty-state[_ngcontent-%COMP%] {\n  text-align: center;\n  padding: 48px;\n  color: #999;\n}\n.empty-state[_ngcontent-%COMP%]   mat-icon[_ngcontent-%COMP%] {\n  font-size: 48px;\n  width: 48px;\n  height: 48px;\n}\n/*# sourceMappingURL=miembros-list.component.css.map */"] });
};
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && setClassMetadata(MiembrosListComponent, [{
    type: Component,
    args: [{ selector: "app-miembros-list", standalone: true, imports: [
      RouterLink,
      FormsModule,
      MatTableModule,
      MatButtonModule,
      MatIconModule,
      MatFormFieldModule,
      MatInputModule,
      MatChipsModule,
      MatProgressSpinnerModule,
      MatCardModule,
      MatTooltipModule
    ], template: `<div class="list-header">
  <h2>Miembros</h2>

  <div class="list-actions">
    <mat-form-field appearance="outline" class="search-field">
      <mat-label>Buscar</mat-label>
      <input matInput [(ngModel)]="busqueda" (keyup.enter)="cargar()" placeholder="Nombre, apellido o c\xE9dula" />
      <mat-icon matSuffix>search</mat-icon>
    </mat-form-field>

    <button mat-raised-button color="primary" routerLink="nuevo">
      <mat-icon>person_add</mat-icon>
      Nuevo miembro
    </button>
  </div>
</div>

@if (loading()) {
  <div class="loading-center">
    <mat-spinner diameter="40" />
  </div>
} @else {
  <mat-card>
    <table mat-table [dataSource]="miembros()" class="full-width">
      <ng-container matColumnDef="nombre">
        <th mat-header-cell *matHeaderCellDef>Nombre</th>
        <td mat-cell *matCellDef="let m">
          <strong>{{ m.nombre }} {{ m.apellido }}</strong>
          @if (m.email) {
            <div class="sub-text">{{ m.email }}</div>
          }
        </td>
      </ng-container>

      <ng-container matColumnDef="estado">
        <th mat-header-cell *matHeaderCellDef>Estado</th>
        <td mat-cell *matCellDef="let m">
          <span class="estado-chip" [class]="estadoClass(m.estado)">{{ m.estado }}</span>
        </td>
      </ng-container>

      <ng-container matColumnDef="telefono">
        <th mat-header-cell *matHeaderCellDef>Tel\xE9fono</th>
        <td mat-cell *matCellDef="let m">{{ m.telefono ?? '\u2014' }}</td>
      </ng-container>

      <ng-container matColumnDef="lider">
        <th mat-header-cell *matHeaderCellDef>L\xEDder c\xE9lula</th>
        <td mat-cell *matCellDef="let m">{{ m.liderNombre ?? '\u2014' }}</td>
      </ng-container>

      <ng-container matColumnDef="etapa">
        <th mat-header-cell *matHeaderCellDef>Etapa actual</th>
        <td mat-cell *matCellDef="let m">{{ m.etapaActual ?? '\u2014' }}</td>
      </ng-container>

      <ng-container matColumnDef="acciones">
        <th mat-header-cell *matHeaderCellDef></th>
        <td mat-cell *matCellDef="let m" class="actions-cell">
          <a mat-icon-button [routerLink]="[m.miembroId]" matTooltip="Ver detalle">
            <mat-icon>visibility</mat-icon>
          </a>
          <a mat-icon-button [routerLink]="[m.miembroId, 'editar']" matTooltip="Editar">
            <mat-icon>edit</mat-icon>
          </a>
          <button mat-icon-button color="warn" (click)="eliminar(m.miembroId)" matTooltip="Eliminar">
            <mat-icon>delete</mat-icon>
          </button>
        </td>
      </ng-container>

      <tr mat-header-row *matHeaderRowDef="columns"></tr>
      <tr mat-row *matRowDef="let row; columns: columns;"></tr>
    </table>

    @if (miembros().length === 0) {
      <div class="empty-state">
        <mat-icon>people_outline</mat-icon>
        <p>No hay miembros registrados</p>
      </div>
    }
  </mat-card>
}
`, styles: ["/* src/app/features/miembros/miembros-list/miembros-list.component.css */\n.list-header {\n  display: flex;\n  align-items: center;\n  justify-content: space-between;\n  margin-bottom: 16px;\n  flex-wrap: wrap;\n  gap: 12px;\n}\n.list-header h2 {\n  margin: 0;\n  font-size: 22px;\n  font-weight: 500;\n}\n.list-actions {\n  display: flex;\n  align-items: center;\n  gap: 12px;\n}\n.search-field {\n  width: 280px;\n}\n.full-width {\n  width: 100%;\n}\n.sub-text {\n  font-size: 12px;\n  color: #666;\n}\n.estado-chip {\n  padding: 4px 10px;\n  border-radius: 12px;\n  font-size: 12px;\n  font-weight: 500;\n}\n.actions-cell {\n  white-space: nowrap;\n}\n.loading-center {\n  display: flex;\n  justify-content: center;\n  padding: 48px;\n}\n.empty-state {\n  text-align: center;\n  padding: 48px;\n  color: #999;\n}\n.empty-state mat-icon {\n  font-size: 48px;\n  width: 48px;\n  height: 48px;\n}\n/*# sourceMappingURL=miembros-list.component.css.map */\n"] }]
  }], null, null);
})();
(() => {
  (typeof ngDevMode === "undefined" || ngDevMode) && \u0275setClassDebugInfo(MiembrosListComponent, { className: "MiembrosListComponent", filePath: "src/app/features/miembros/miembros-list/miembros-list.component.ts", lineNumber: 29 });
})();
export {
  MiembrosListComponent
};
//# sourceMappingURL=chunk-UM2H5I3M.mjs.map
