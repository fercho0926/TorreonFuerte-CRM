import './polyfills.server.mjs';
import{a as wt,b as Ct}from"./chunk-HRP6SAY5.mjs";import{f as dt,g as It}from"./chunk-7I3OASSX.mjs";import{Ba as Bt,Ja as Dt,Ka as Q,Na as M,Pa as N,Qa as k,Ra as s,S as rt,Sa as u,V as Tt,aa as _t,ba as xt,ca as j,ia as st,la as H,p as et,s as F,wa as lt,y as nt}from"./chunk-A3HDF5AK.mjs";import{Ac as gt,Cc as I,Dc as mt,Ec as tt,Fb as X,Gb as Y,Hb as ut,L as P,La as y,Lb as O,M as g,Mb as B,N as $,Nb as D,Ob as w,P as m,Pb as Z,Pc as A,Qb as V,Qc as yt,R as a,Rb as f,Sb as h,Tb as pt,Ub as bt,Vb as vt,W as q,X as W,Y as at,Yb as ft,Zb as l,bb as T,cb as G,da as U,fb as _,gb as x,gc as C,hb as R,ja as c,la as ct,pb as v,qb as S,rb as L,tc as ht,wb as p,xb as E,yb as z,zb as J,zc as d}from"./chunk-ERHZA7IG.mjs";var Mt=`
    .p-tabs {
        display: flex;
        flex-direction: column;
    }

    .p-tablist {
        display: flex;
        position: relative;
        overflow: hidden;
        background: dt('tabs.tablist.background');
    }

    .p-tablist-viewport {
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
        scrollbar-width: none;
        overscroll-behavior: contain auto;
    }

    .p-tablist-viewport::-webkit-scrollbar {
        display: none;
    }

    .p-tablist-tab-list {
        position: relative;
        display: flex;
        border-style: solid;
        border-color: dt('tabs.tablist.border.color');
        border-width: dt('tabs.tablist.border.width');
    }

    .p-tablist-content {
        flex-grow: 1;
    }

    .p-tablist-nav-button {
        all: unset;
        position: absolute !important;
        flex-shrink: 0;
        inset-block-start: 0;
        z-index: 2;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: dt('tabs.nav.button.background');
        color: dt('tabs.nav.button.color');
        width: dt('tabs.nav.button.width');
        transition:
            color dt('tabs.transition.duration'),
            outline-color dt('tabs.transition.duration'),
            box-shadow dt('tabs.transition.duration');
        box-shadow: dt('tabs.nav.button.shadow');
        outline-color: transparent;
        cursor: pointer;
    }

    .p-tablist-nav-button:focus-visible {
        z-index: 1;
        box-shadow: dt('tabs.nav.button.focus.ring.shadow');
        outline: dt('tabs.nav.button.focus.ring.width') dt('tabs.nav.button.focus.ring.style') dt('tabs.nav.button.focus.ring.color');
        outline-offset: dt('tabs.nav.button.focus.ring.offset');
    }

    .p-tablist-nav-button:hover {
        color: dt('tabs.nav.button.hover.color');
    }

    .p-tablist-prev-button {
        inset-inline-start: 0;
    }

    .p-tablist-next-button {
        inset-inline-end: 0;
    }

    .p-tablist-prev-button:dir(rtl),
    .p-tablist-next-button:dir(rtl) {
        transform: rotate(180deg);
    }

    .p-tab {
        flex-shrink: 0;
        cursor: pointer;
        user-select: none;
        position: relative;
        border-style: solid;
        white-space: nowrap;
        gap: dt('tabs.tab.gap');
        background: dt('tabs.tab.background');
        border-width: dt('tabs.tab.border.width');
        border-color: dt('tabs.tab.border.color');
        color: dt('tabs.tab.color');
        padding: dt('tabs.tab.padding');
        font-weight: dt('tabs.tab.font.weight');
        transition:
            background dt('tabs.transition.duration'),
            border-color dt('tabs.transition.duration'),
            color dt('tabs.transition.duration'),
            outline-color dt('tabs.transition.duration'),
            box-shadow dt('tabs.transition.duration');
        margin: dt('tabs.tab.margin');
        outline-color: transparent;
    }

    .p-tab:not(.p-disabled):focus-visible {
        z-index: 1;
        box-shadow: dt('tabs.tab.focus.ring.shadow');
        outline: dt('tabs.tab.focus.ring.width') dt('tabs.tab.focus.ring.style') dt('tabs.tab.focus.ring.color');
        outline-offset: dt('tabs.tab.focus.ring.offset');
    }

    .p-tab:not(.p-tab-active):not(.p-disabled):hover {
        background: dt('tabs.tab.hover.background');
        border-color: dt('tabs.tab.hover.border.color');
        color: dt('tabs.tab.hover.color');
    }

    .p-tab-active {
        background: dt('tabs.tab.active.background');
        border-color: dt('tabs.tab.active.border.color');
        color: dt('tabs.tab.active.color');
    }

    .p-tabpanels {
        background: dt('tabs.tabpanel.background');
        color: dt('tabs.tabpanel.color');
        padding: dt('tabs.tabpanel.padding');
        outline: 0 none;
    }

    .p-tabpanel:focus-visible {
        box-shadow: dt('tabs.tabpanel.focus.ring.shadow');
        outline: dt('tabs.tabpanel.focus.ring.width') dt('tabs.tabpanel.focus.ring.style') dt('tabs.tabpanel.focus.ring.color');
        outline-offset: dt('tabs.tabpanel.focus.ring.offset');
    }

    .p-tablist-active-bar {
        z-index: 1;
        display: block;
        position: absolute;
        inset-block-end: dt('tabs.active.bar.bottom');
        height: dt('tabs.active.bar.height');
        background: dt('tabs.active.bar.background');
        transition: 250ms cubic-bezier(0.35, 0, 0.25, 1);
    }
`;var qt=["previcon"],Wt=["nexticon"],Rt=["content"],Ut=["prevButton"],Gt=["nextButton"],Jt=["inkbar"],Xt=["tabs"],K=["*"];function Yt(t,b){t&1&&X(0)}function Zt(t,b){if(t&1&&R(0,Yt,1,0,"ng-container",11),t&2){let e=B(2);p("ngTemplateOutlet",e.prevIconTemplate||e._prevIconTemplate)}}function te(t,b){t&1&&(at(),J(0,"svg",10))}function ee(t,b){if(t&1){let e=Y();E(0,"button",9,3),O("click",function(){q(e);let n=B();return W(n.onPrevButtonClick())}),S(2,Zt,1,1,"ng-container")(3,te,1,0,":svg:svg",10),z()}if(t&2){let e=B();l(e.cx("prevButton")),p("pBind",e.ptm("prevButton")),v("aria-label",e.prevButtonAriaLabel)("tabindex",e.tabindex())("data-pc-group-section","navigator"),y(2),L(e.prevIconTemplate||e._prevIconTemplate?2:3)}}function ne(t,b){t&1&&X(0)}function ie(t,b){if(t&1&&R(0,ne,1,0,"ng-container",11),t&2){let e=B(2);p("ngTemplateOutlet",e.nextIconTemplate||e._nextIconTemplate)}}function oe(t,b){t&1&&(at(),J(0,"svg",12))}function ae(t,b){if(t&1){let e=Y();E(0,"button",9,4),O("click",function(){q(e);let n=B();return W(n.onNextButtonClick())}),S(2,ie,1,1,"ng-container")(3,oe,1,0,":svg:svg",12),z()}if(t&2){let e=B();l(e.cx("nextButton")),p("pBind",e.ptm("nextButton")),v("aria-label",e.nextButtonAriaLabel)("tabindex",e.tabindex())("data-pc-group-section","navigator"),y(2),L(e.nextIconTemplate||e._nextIconTemplate?2:3)}}function re(t,b){t&1&&w(0)}function se(t,b){t&1&&X(0)}function le(t,b){if(t&1&&R(0,se,1,0,"ng-container",1),t&2){let e=B(),i=vt(1);p("ngTemplateOutlet",e.content()?e.content():i)}}var de={root:({instance:t})=>["p-tabs p-component",{"p-tabs-scrollable":t.scrollable()}]},Nt=(()=>{class t extends M{name="tabs";style=Mt;classes=de;static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275prov=g({token:t,factory:t.\u0275fac})}return t})();var ce={root:({instance:t})=>["p-tab",{"p-tab-active":t.active(),"p-disabled":t.disabled()}]},kt=(()=>{class t extends M{name="tab";classes=ce;static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275prov=g({token:t,factory:t.\u0275fac})}return t})();var ue={root:"p-tablist",content:"p-tablist-content p-tablist-viewport",tabList:"p-tablist-tab-list",activeBar:"p-tablist-active-bar",prevButton:"p-tablist-prev-button p-tablist-nav-button",nextButton:"p-tablist-next-button p-tablist-nav-button"},Et=(()=>{class t extends M{name="tablist";classes=ue;static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275prov=g({token:t,factory:t.\u0275fac})}return t})();var At=new m("TABLIST_INSTANCE"),Ot=(()=>{class t extends k{$pcTabList=a(At,{optional:!0,skipSelf:!0})??void 0;bindDirectiveInstance=a(s,{self:!0});onAfterViewChecked(){this.bindDirectiveInstance.setAttrs(this.ptms(["host","root"]))}prevIconTemplate;nextIconTemplate;templates;content;prevButton;nextButton;inkbar;tabs;pcTabs=a(P(()=>it));isPrevButtonEnabled=U(!1);isNextButtonEnabled=U(!1);resizeObserver;showNavigators=d(()=>this.pcTabs.showNavigators());tabindex=d(()=>this.pcTabs.tabindex());scrollable=d(()=>this.pcTabs.scrollable());_componentStyle=a(Et);constructor(){super(),gt(()=>{this.pcTabs.value(),nt(this.platformId)&&setTimeout(()=>{this.updateInkBar()})})}get prevButtonAriaLabel(){return this.config?.translation?.aria?.previous}get nextButtonAriaLabel(){return this.config?.translation?.aria?.next}onAfterViewInit(){this.showNavigators()&&nt(this.platformId)&&(this.updateButtonState(),this.bindResizeObserver())}_prevIconTemplate;_nextIconTemplate;onAfterContentInit(){this.templates?.forEach(e=>{switch(e.getType()){case"previcon":this._prevIconTemplate=e.template;break;case"nexticon":this._nextIconTemplate=e.template;break}})}onDestroy(){this.unbindResizeObserver()}onScroll(e){this.showNavigators()&&this.updateButtonState(),e.preventDefault()}onPrevButtonClick(){let e=this.content.nativeElement,i=H(e),n=Math.abs(e.scrollLeft)-i,o=n<=0?0:n;e.scrollLeft=rt(e)?-1*o:o}onNextButtonClick(){let e=this.content.nativeElement,i=H(e)-this.getVisibleButtonWidths(),n=e.scrollLeft+i,o=e.scrollWidth-i,r=n>=o?o:n;e.scrollLeft=rt(e)?-1*r:r}updateButtonState(){let e=this.content?.nativeElement,i=this.el?.nativeElement,{scrollWidth:n,offsetWidth:o}=e,r=Math.abs(e.scrollLeft),ot=H(e);this.isPrevButtonEnabled.set(r!==0),this.isNextButtonEnabled.set(i.offsetWidth>=o&&Math.abs(r-n+ot)>1)}updateInkBar(){let e=this.content?.nativeElement,i=this.inkbar?.nativeElement,n=this.tabs?.nativeElement,o=_t(e,'[data-pc-name="tab"][data-p-active="true"]');i&&(i.style.width=Tt(o)+"px",i.style.left=st(o).left-st(n).left+"px")}getVisibleButtonWidths(){let e=this.prevButton?.nativeElement,i=this.nextButton?.nativeElement;return[e,i].reduce((n,o)=>o?n+H(o):n,0)}bindResizeObserver(){this.resizeObserver=new ResizeObserver(()=>this.updateButtonState()),this.resizeObserver.observe(this.el.nativeElement)}unbindResizeObserver(){this.resizeObserver&&(this.resizeObserver.unobserve(this.el.nativeElement),this.resizeObserver=null)}static \u0275fac=function(i){return new(i||t)};static \u0275cmp=T({type:t,selectors:[["p-tablist"]],contentQueries:function(i,n,o){if(i&1&&(Z(o,qt,4),Z(o,Wt,4),Z(o,Dt,4)),i&2){let r;f(r=h())&&(n.prevIconTemplate=r.first),f(r=h())&&(n.nextIconTemplate=r.first),f(r=h())&&(n.templates=r)}},viewQuery:function(i,n){if(i&1&&(V(Rt,5),V(Ut,5),V(Gt,5),V(Jt,5),V(Xt,5)),i&2){let o;f(o=h())&&(n.content=o.first),f(o=h())&&(n.prevButton=o.first),f(o=h())&&(n.nextButton=o.first),f(o=h())&&(n.inkbar=o.first),f(o=h())&&(n.tabs=o.first)}},hostVars:2,hostBindings:function(i,n){i&2&&l(n.cx("root"))},features:[C([Et,{provide:At,useExisting:t},{provide:N,useExisting:t}]),x([s]),_],ngContentSelectors:K,decls:9,vars:11,consts:[["content",""],["tabs",""],["inkbar",""],["prevButton",""],["nextButton",""],["type","button","pRipple","",3,"pBind","class"],[3,"scroll","pBind"],["role","tablist",3,"pBind"],["role","presentation",3,"pBind"],["type","button","pRipple","",3,"click","pBind"],["data-p-icon","chevron-left"],[4,"ngTemplateOutlet"],["data-p-icon","chevron-right"]],template:function(i,n){if(i&1){let o=Y();D(),S(0,ee,4,7,"button",5),E(1,"div",6,0),O("scroll",function(ot){return q(o),W(n.onScroll(ot))}),E(3,"div",7,1),w(5),J(6,"span",8,2),z()(),S(8,ae,4,7,"button",5)}i&2&&(L(n.showNavigators()&&n.isPrevButtonEnabled()?0:-1),y(),l(n.cx("content")),p("pBind",n.ptm("content")),y(2),l(n.cx("tabList")),p("pBind",n.ptm("tabList")),y(3),l(n.cx("activeBar")),p("pBind",n.ptm("activeBar")),y(2),L(n.showNavigators()&&n.isNextButtonEnabled()?8:-1))},dependencies:[F,et,wt,Ct,It,dt,Q,u,s],encapsulation:2,changeDetection:0})}return t})(),Ft=new m("TAB_INSTANCE"),pe=(()=>{class t extends k{$pcTab=a(Ft,{optional:!0,skipSelf:!0})??void 0;bindDirectiveInstance=a(s,{self:!0});onAfterViewChecked(){this.bindDirectiveInstance.setAttrs(this.ptms(["host","root"]))}value=tt();disabled=I(!1,{transform:A});pcTabs=a(P(()=>it));pcTabList=a(P(()=>Ot));el=a(ct);_componentStyle=a(kt);ripple=d(()=>this.config.ripple());id=d(()=>`${this.pcTabs.id()}_tab_${this.value()}`);ariaControls=d(()=>`${this.pcTabs.id()}_tabpanel_${this.value()}`);active=d(()=>lt(this.pcTabs.value(),this.value()));tabindex=d(()=>this.disabled()?-1:this.active()?this.pcTabs.tabindex():-1);mutationObserver;onFocus(e){this.disabled()||this.pcTabs.selectOnFocus()&&this.changeActiveValue()}onClick(e){this.disabled()||this.changeActiveValue()}onKeyDown(e){switch(e.code){case"ArrowRight":this.onArrowRightKey(e);break;case"ArrowLeft":this.onArrowLeftKey(e);break;case"Home":this.onHomeKey(e);break;case"End":this.onEndKey(e);break;case"PageDown":this.onPageDownKey(e);break;case"PageUp":this.onPageUpKey(e);break;case"Enter":case"NumpadEnter":case"Space":this.onEnterKey(e);break;default:break}e.stopPropagation()}onAfterViewInit(){this.bindMutationObserver()}onArrowRightKey(e){let i=this.findNextTab(e.currentTarget);i?this.changeFocusedTab(e,i):this.onHomeKey(e),e.preventDefault()}onArrowLeftKey(e){let i=this.findPrevTab(e.currentTarget);i?this.changeFocusedTab(e,i):this.onEndKey(e),e.preventDefault()}onHomeKey(e){let i=this.findFirstTab();this.changeFocusedTab(e,i),e.preventDefault()}onEndKey(e){let i=this.findLastTab();this.changeFocusedTab(e,i),e.preventDefault()}onPageDownKey(e){this.scrollInView(this.findLastTab()),e.preventDefault()}onPageUpKey(e){this.scrollInView(this.findFirstTab()),e.preventDefault()}onEnterKey(e){this.disabled()||this.changeActiveValue(),e.preventDefault()}findNextTab(e,i=!1){let n=i?e:e.nextElementSibling;return n?j(n,"data-p-disabled")||j(n,"data-pc-section")==="activebar"?this.findNextTab(n):n:null}findPrevTab(e,i=!1){let n=i?e:e.previousElementSibling;return n?j(n,"data-p-disabled")||j(n,"data-pc-section")==="activebar"?this.findPrevTab(n):n:null}findFirstTab(){return this.findNextTab(this.pcTabList?.tabs?.nativeElement?.firstElementChild,!0)}findLastTab(){return this.findPrevTab(this.pcTabList?.tabs?.nativeElement?.lastElementChild,!0)}changeActiveValue(){this.pcTabs.updateValue(this.value())}changeFocusedTab(e,i){xt(i),this.scrollInView(i)}scrollInView(e){e?.scrollIntoView?.({block:"nearest"})}bindMutationObserver(){nt(this.platformId)&&(this.mutationObserver=new MutationObserver(e=>{e.forEach(()=>{this.active()&&this.pcTabList?.updateInkBar()})}),this.mutationObserver.observe(this.el.nativeElement,{childList:!0,characterData:!0,subtree:!0}))}unbindMutationObserver(){this.mutationObserver?.disconnect()}onDestroy(){this.mutationObserver&&this.unbindMutationObserver()}static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275cmp=T({type:t,selectors:[["p-tab"]],hostVars:10,hostBindings:function(i,n){i&1&&O("focus",function(r){return n.onFocus(r)})("click",function(r){return n.onClick(r)})("keydown",function(r){return n.onKeyDown(r)}),i&2&&(v("id",n.id())("aria-controls",n.ariaControls())("role","tab")("aria-selected",n.active())("aria-disabled",n.disabled())("data-p-disabled",n.disabled())("data-p-active",n.active())("tabindex",n.tabindex()),l(n.cx("root")))},inputs:{value:[1,"value"],disabled:[1,"disabled"]},outputs:{value:"valueChange"},features:[C([kt,{provide:Ft,useExisting:t},{provide:N,useExisting:t}]),x([dt,s]),_],ngContentSelectors:K,decls:1,vars:0,template:function(i,n){i&1&&(D(),w(0))},dependencies:[F,Q,u],encapsulation:2,changeDetection:0})}return t})(),be={root:({instance:t})=>["p-tabpanel",{"p-tabpanel-active":t.active()}]},St=(()=>{class t extends M{name="tabpanel";classes=be;static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275prov=g({token:t,factory:t.\u0275fac})}return t})();var Lt=new m("TABPANEL_INSTANCE"),ve=(()=>{class t extends k{$pcTabPanel=a(Lt,{optional:!0,skipSelf:!0})??void 0;bindDirectiveInstance=a(s,{self:!0});pcTabs=a(P(()=>it));onAfterViewChecked(){this.bindDirectiveInstance.setAttrs(this.ptms(["host","root"]))}lazy=I(!1,{transform:A});value=tt(void 0);content=mt("content");id=d(()=>`${this.pcTabs.id()}_tabpanel_${this.value()}`);ariaLabelledby=d(()=>`${this.pcTabs.id()}_tab_${this.value()}`);active=d(()=>lt(this.pcTabs.value(),this.value()));isLazyEnabled=d(()=>this.pcTabs.lazy()||this.lazy());hasBeenRendered=!1;shouldRender=d(()=>!this.isLazyEnabled()||this.hasBeenRendered?!0:this.active()?(this.hasBeenRendered=!0,!0):!1);_componentStyle=a(St);static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275cmp=T({type:t,selectors:[["p-tabpanel"]],contentQueries:function(i,n,o){i&1&&pt(o,n.content,Rt,5),i&2&&bt()},hostVars:7,hostBindings:function(i,n){i&2&&(ut("hidden",!n.active()),v("id",n.id())("role","tabpanel")("aria-labelledby",n.ariaLabelledby())("data-p-active",n.active()),l(n.cx("root")))},inputs:{lazy:[1,"lazy"],value:[1,"value"]},outputs:{value:"valueChange"},features:[C([St,{provide:Lt,useExisting:t},{provide:N,useExisting:t}]),x([s]),_],ngContentSelectors:K,decls:3,vars:1,consts:[["defaultContent",""],[4,"ngTemplateOutlet"]],template:function(i,n){i&1&&(D(),R(0,re,1,0,"ng-template",null,0,ht),S(2,le,1,1,"ng-container")),i&2&&(y(2),L(n.shouldRender()?2:-1))},dependencies:[et,u],encapsulation:2,changeDetection:0})}return t})(),fe={root:"p-tabpanels"},zt=(()=>{class t extends M{name="tabpanels";classes=fe;static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275prov=g({token:t,factory:t.\u0275fac})}return t})();var Vt=new m("TABPANELS_INSTANCE"),he=(()=>{class t extends k{$pcTabPanels=a(Vt,{optional:!0,skipSelf:!0})??void 0;bindDirectiveInstance=a(s,{self:!0});_componentStyle=a(zt);onAfterViewChecked(){this.bindDirectiveInstance.setAttrs(this.ptms(["host","root"]))}static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275cmp=T({type:t,selectors:[["p-tabpanels"]],hostVars:3,hostBindings:function(i,n){i&2&&(v("role","presentation"),l(n.cx("root")))},features:[C([zt,{provide:Vt,useExisting:t},{provide:N,useExisting:t}]),x([s]),_],ngContentSelectors:K,decls:1,vars:0,template:function(i,n){i&1&&(D(),w(0))},dependencies:[F,u],encapsulation:2,changeDetection:0})}return t})(),Pt=new m("TABS_INSTANCE"),it=(()=>{class t extends k{$pcTabs=a(Pt,{optional:!0,skipSelf:!0})??void 0;bindDirectiveInstance=a(s,{self:!0});onAfterViewChecked(){this.bindDirectiveInstance.setAttrs(this.ptms(["host","root"]))}value=tt(void 0);scrollable=I(!1,{transform:A});lazy=I(!1,{transform:A});selectOnFocus=I(!1,{transform:A});showNavigators=I(!0,{transform:A});tabindex=I(0,{transform:yt});id=U(Bt("pn_id_"));_componentStyle=a(Nt);updateValue(e){this.value.update(()=>e)}static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275cmp=T({type:t,selectors:[["p-tabs"]],hostVars:3,hostBindings:function(i,n){i&2&&(v("id",n.id()),l(n.cx("root")))},inputs:{value:[1,"value"],scrollable:[1,"scrollable"],lazy:[1,"lazy"],selectOnFocus:[1,"selectOnFocus"],showNavigators:[1,"showNavigators"],tabindex:[1,"tabindex"]},outputs:{value:"valueChange"},features:[C([Nt,{provide:Pt,useExisting:t},{provide:N,useExisting:t}]),x([s]),_],ngContentSelectors:K,decls:1,vars:0,template:function(i,n){i&1&&(D(),w(0))},dependencies:[F,u],encapsulation:2,changeDetection:0})}return t})(),Ke=(()=>{class t{static \u0275fac=function(i){return new(i||t)};static \u0275mod=G({type:t});static \u0275inj=$({imports:[it,he,ve,Ot,pe,u,u]})}return t})();var jt=`
    .p-divider-horizontal {
        display: flex;
        width: 100%;
        position: relative;
        align-items: center;
        margin: dt('divider.horizontal.margin');
        padding: dt('divider.horizontal.padding');
    }

    .p-divider-horizontal:before {
        position: absolute;
        display: block;
        inset-block-start: 50%;
        inset-inline-start: 0;
        width: 100%;
        content: '';
        border-block-start: 1px solid dt('divider.border.color');
    }

    .p-divider-horizontal .p-divider-content {
        padding: dt('divider.horizontal.content.padding');
    }

    .p-divider-vertical {
        min-height: 100%;
        display: flex;
        position: relative;
        justify-content: center;
        margin: dt('divider.vertical.margin');
        padding: dt('divider.vertical.padding');
    }

    .p-divider-vertical:before {
        position: absolute;
        display: block;
        inset-block-start: 0;
        inset-inline-start: 50%;
        height: 100%;
        content: '';
        border-inline-start: 1px solid dt('divider.border.color');
    }

    .p-divider.p-divider-vertical .p-divider-content {
        padding: dt('divider.vertical.content.padding');
    }

    .p-divider-content {
        z-index: 1;
        background: dt('divider.content.background');
        color: dt('divider.content.color');
    }

    .p-divider-solid.p-divider-horizontal:before {
        border-block-start-style: solid;
    }

    .p-divider-solid.p-divider-vertical:before {
        border-inline-start-style: solid;
    }

    .p-divider-dashed.p-divider-horizontal:before {
        border-block-start-style: dashed;
    }

    .p-divider-dashed.p-divider-vertical:before {
        border-inline-start-style: dashed;
    }

    .p-divider-dotted.p-divider-horizontal:before {
        border-block-start-style: dotted;
    }

    .p-divider-dotted.p-divider-vertical:before {
        border-inline-start-style: dotted;
    }

    .p-divider-left:dir(rtl),
    .p-divider-right:dir(rtl) {
        flex-direction: row-reverse;
    }
`;var ge=["*"],me={root:({instance:t})=>({justifyContent:t.layout==="horizontal"?t.align==="center"||t.align==null?"center":t.align==="left"?"flex-start":t.align==="right"?"flex-end":null:null,alignItems:t.layout==="vertical"?t.align==="center"||t.align==null?"center":t.align==="top"?"flex-start":t.align==="bottom"?"flex-end":null:null})},ye={root:({instance:t})=>["p-divider p-component","p-divider-"+t.layout,"p-divider-"+t.type,{"p-divider-left":t.layout==="horizontal"&&(!t.align||t.align==="left")},{"p-divider-center":t.layout==="horizontal"&&t.align==="center"},{"p-divider-right":t.layout==="horizontal"&&t.align==="right"},{"p-divider-top":t.layout==="vertical"&&t.align==="top"},{"p-divider-center":t.layout==="vertical"&&(!t.align||t.align==="center")},{"p-divider-bottom":t.layout==="vertical"&&t.align==="bottom"}],content:"p-divider-content"},Ht=(()=>{class t extends M{name="divider";style=jt;classes=ye;inlineStyles=me;static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275prov=g({token:t,factory:t.\u0275fac})}return t})();var Qt=new m("DIVIDER_INSTANCE"),Te=(()=>{class t extends k{$pcDivider=a(Qt,{optional:!0,skipSelf:!0})??void 0;bindDirectiveInstance=a(s,{self:!0});onAfterViewChecked(){this.bindDirectiveInstance.setAttrs(this.ptms(["host","root"]))}styleClass;layout="horizontal";type="solid";align;_componentStyle=a(Ht);static \u0275fac=(()=>{let e;return function(n){return(e||(e=c(t)))(n||t)}})();static \u0275cmp=T({type:t,selectors:[["p-divider"]],hostAttrs:["role","separator"],hostVars:5,hostBindings:function(i,n){i&2&&(v("aria-orientation",n.layout),ft(n.sx("root")),l(n.cn(n.cx("root"),n.styleClass)))},inputs:{styleClass:"styleClass",layout:"layout",type:"type",align:"align"},features:[C([Ht,{provide:Qt,useExisting:t},{provide:N,useExisting:t}]),x([s]),_],ngContentSelectors:ge,decls:2,vars:3,consts:[[3,"pBind"]],template:function(i,n){i&1&&(D(),E(0,"div",0),w(1),z()),i&2&&(l(n.cx("content")),p("pBind",n.ptm("content")))},dependencies:[F,Q,u,s],encapsulation:2,changeDetection:0})}return t})(),sn=(()=>{class t{static \u0275fac=function(i){return new(i||t)};static \u0275mod=G({type:t});static \u0275inj=$({imports:[Te,u,u]})}return t})();export{Ot as a,pe as b,ve as c,he as d,it as e,Ke as f,Te as g,sn as h};
