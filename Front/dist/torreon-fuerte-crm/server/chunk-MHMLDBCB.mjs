import './polyfills.server.mjs';
import{b as Y}from"./chunk-WD2XXYJR.mjs";import{a as X}from"./chunk-YUOYBXYO.mjs";import{a as K}from"./chunk-7I3OASSX.mjs";import{Ja as P,Ka as u,Na as W,Pa as U,Ra as d,Sa as J,p as $,s as q}from"./chunk-A3HDF5AK.mjs";import{Cc as G,Fb as N,Gb as H,L as T,La as c,Lb as y,M as _,Mb as O,N as S,P as V,Pb as v,Pc as C,Qa as E,Qb as Q,Qc as R,R as a,Rb as g,Sb as h,W as p,X as w,Yb as z,Zb as s,bb as B,cb as M,fb as I,gb as F,gc as x,hb as A,ic as j,ja as b,pb as f,qb as L,rb as D,wb as r,xb as m,yb as k}from"./chunk-ERHZA7IG.mjs";var Z=`
    .p-toggleswitch {
        display: inline-block;
        width: dt('toggleswitch.width');
        height: dt('toggleswitch.height');
    }

    .p-toggleswitch-input {
        cursor: pointer;
        appearance: none;
        position: absolute;
        top: 0;
        inset-inline-start: 0;
        width: 100%;
        height: 100%;
        padding: 0;
        margin: 0;
        opacity: 0;
        z-index: 1;
        outline: 0 none;
        border-radius: dt('toggleswitch.border.radius');
    }

    .p-toggleswitch-slider {
        cursor: pointer;
        width: 100%;
        height: 100%;
        border-width: dt('toggleswitch.border.width');
        border-style: solid;
        border-color: dt('toggleswitch.border.color');
        background: dt('toggleswitch.background');
        transition:
            background dt('toggleswitch.transition.duration'),
            color dt('toggleswitch.transition.duration'),
            border-color dt('toggleswitch.transition.duration'),
            outline-color dt('toggleswitch.transition.duration'),
            box-shadow dt('toggleswitch.transition.duration');
        border-radius: dt('toggleswitch.border.radius');
        outline-color: transparent;
        box-shadow: dt('toggleswitch.shadow');
    }

    .p-toggleswitch-handle {
        position: absolute;
        top: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: dt('toggleswitch.handle.background');
        color: dt('toggleswitch.handle.color');
        width: dt('toggleswitch.handle.size');
        height: dt('toggleswitch.handle.size');
        inset-inline-start: dt('toggleswitch.gap');
        margin-block-start: calc(-1 * calc(dt('toggleswitch.handle.size') / 2));
        border-radius: dt('toggleswitch.handle.border.radius');
        transition:
            background dt('toggleswitch.transition.duration'),
            color dt('toggleswitch.transition.duration'),
            inset-inline-start dt('toggleswitch.slide.duration'),
            box-shadow dt('toggleswitch.slide.duration');
    }

    .p-toggleswitch.p-toggleswitch-checked .p-toggleswitch-slider {
        background: dt('toggleswitch.checked.background');
        border-color: dt('toggleswitch.checked.border.color');
    }

    .p-toggleswitch.p-toggleswitch-checked .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.checked.background');
        color: dt('toggleswitch.handle.checked.color');
        inset-inline-start: calc(dt('toggleswitch.width') - calc(dt('toggleswitch.handle.size') + dt('toggleswitch.gap')));
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover) .p-toggleswitch-slider {
        background: dt('toggleswitch.hover.background');
        border-color: dt('toggleswitch.hover.border.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover) .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.hover.background');
        color: dt('toggleswitch.handle.hover.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover).p-toggleswitch-checked .p-toggleswitch-slider {
        background: dt('toggleswitch.checked.hover.background');
        border-color: dt('toggleswitch.checked.hover.border.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover).p-toggleswitch-checked .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.checked.hover.background');
        color: dt('toggleswitch.handle.checked.hover.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:focus-visible) .p-toggleswitch-slider {
        box-shadow: dt('toggleswitch.focus.ring.shadow');
        outline: dt('toggleswitch.focus.ring.width') dt('toggleswitch.focus.ring.style') dt('toggleswitch.focus.ring.color');
        outline-offset: dt('toggleswitch.focus.ring.offset');
    }

    .p-toggleswitch.p-invalid > .p-toggleswitch-slider {
        border-color: dt('toggleswitch.invalid.border.color');
    }

    .p-toggleswitch.p-disabled {
        opacity: 1;
    }

    .p-toggleswitch.p-disabled .p-toggleswitch-slider {
        background: dt('toggleswitch.disabled.background');
    }

    .p-toggleswitch.p-disabled .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.disabled.background');
    }
`;var oe=["handle"],le=["input"],re=t=>({checked:t});function se(t,ne){t&1&&N(0)}function de(t,ne){if(t&1&&A(0,se,1,0,"ng-container",3),t&2){let i=O();r("ngTemplateOutlet",i.handleTemplate||i._handleTemplate)("ngTemplateOutletContext",j(2,re,i.checked()))}}var ae=`
    ${Z}

    p-toggleswitch.ng-invalid.ng-dirty > .p-toggleswitch-slider {
        border-color: dt('toggleswitch.invalid.border.color');
    }
`,ce={root:{position:"relative"}},ge={root:({instance:t})=>["p-toggleswitch p-component",{"p-toggleswitch p-component":!0,"p-toggleswitch-checked":t.checked(),"p-disabled":t.$disabled(),"p-invalid":t.invalid()}],input:"p-toggleswitch-input",slider:"p-toggleswitch-slider",handle:"p-toggleswitch-handle"},ee=(()=>{class t extends W{name="toggleswitch";style=ae;classes=ge;inlineStyles=ce;static \u0275fac=(()=>{let i;return function(e){return(i||(i=b(t)))(e||t)}})();static \u0275prov=_({token:t,factory:t.\u0275fac})}return t})();var te=new V("TOGGLESWITCH_INSTANCE"),he={provide:X,useExisting:T(()=>ie),multi:!0},ie=(()=>{class t extends Y{$pcToggleSwitch=a(te,{optional:!0,skipSelf:!0})??void 0;bindDirectiveInstance=a(d,{self:!0});onAfterViewChecked(){this.bindDirectiveInstance.setAttrs(this.ptms(["host","root"]))}styleClass;tabindex;inputId;readonly;trueValue=!0;falseValue=!1;ariaLabel;size=G();ariaLabelledBy;autofocus;onChange=new E;input;handleTemplate;_handleTemplate;focused=!1;_componentStyle=a(ee);templates;onHostClick(i){this.onClick(i)}onAfterContentInit(){this.templates.forEach(i=>{i.getType()==="handle"?this._handleTemplate=i.template:this._handleTemplate=i.template})}onClick(i){!this.$disabled()&&!this.readonly&&(this.writeModelValue(this.checked()?this.falseValue:this.trueValue),this.onModelChange(this.modelValue()),this.onChange.emit({originalEvent:i,checked:this.modelValue()}),this.input.nativeElement.focus())}onFocus(){this.focused=!0}onBlur(){this.focused=!1,this.onModelTouched()}checked(){return this.modelValue()===this.trueValue}writeControlValue(i,n){n(i),this.cd.markForCheck()}static \u0275fac=(()=>{let i;return function(e){return(i||(i=b(t)))(e||t)}})();static \u0275cmp=B({type:t,selectors:[["p-toggleswitch"],["p-toggleSwitch"],["p-toggle-switch"]],contentQueries:function(n,e,o){if(n&1&&(v(o,oe,4),v(o,P,4)),n&2){let l;g(l=h())&&(e.handleTemplate=l.first),g(l=h())&&(e.templates=l)}},viewQuery:function(n,e){if(n&1&&Q(le,5),n&2){let o;g(o=h())&&(e.input=o.first)}},hostVars:5,hostBindings:function(n,e){n&1&&y("click",function(l){return e.onHostClick(l)}),n&2&&(f("data-pc-name","toggleswitch"),z(e.sx("root")),s(e.cn(e.cx("root"),e.styleClass)))},inputs:{styleClass:"styleClass",tabindex:[2,"tabindex","tabindex",R],inputId:"inputId",readonly:[2,"readonly","readonly",C],trueValue:"trueValue",falseValue:"falseValue",ariaLabel:"ariaLabel",size:[1,"size"],ariaLabelledBy:"ariaLabelledBy",autofocus:[2,"autofocus","autofocus",C]},outputs:{onChange:"onChange"},features:[x([he,ee,{provide:te,useExisting:t},{provide:U,useExisting:t}]),F([d]),I],decls:5,vars:20,consts:[["input",""],["type","checkbox","role","switch",3,"focus","blur","checked","pAutoFocus","pBind"],[3,"pBind"],[4,"ngTemplateOutlet","ngTemplateOutletContext"]],template:function(n,e){if(n&1){let o=H();m(0,"input",1,0),y("focus",function(){return p(o),w(e.onFocus())})("blur",function(){return p(o),w(e.onBlur())}),k(),m(2,"div",2)(3,"div",2),L(4,de,1,4,"ng-container"),k()()}n&2&&(s(e.cx("input")),r("checked",e.checked())("pAutoFocus",e.autofocus)("pBind",e.ptm("input")),f("id",e.inputId)("required",e.required()?"":void 0)("disabled",e.$disabled()?"":void 0)("aria-checked",e.checked())("aria-labelledby",e.ariaLabelledBy)("aria-label",e.ariaLabel)("name",e.name())("tabindex",e.tabindex),c(2),s(e.cx("slider")),r("pBind",e.ptm("slider")),c(),s(e.cx("handle")),r("pBind",e.ptm("handle")),c(),D(e.handleTemplate||e._handleTemplate?4:-1))},dependencies:[q,$,K,u,J,d],encapsulation:2,changeDetection:0})}return t})(),Qe=(()=>{class t{static \u0275fac=function(n){return new(n||t)};static \u0275mod=M({type:t});static \u0275inj=S({imports:[ie,u,u]})}return t})();export{ie as a,Qe as b};
