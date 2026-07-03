import './polyfills.server.mjs';
import{a as Qe}from"./chunk-7PQ2TRGN.mjs";import{a as We}from"./chunk-II3K3JB3.mjs";import{c as ie,d as se,f as H,g as Ne,h as G,j as Ze,l as Re}from"./chunk-NEW6NZRZ.mjs";import{a as Xe,b as Je}from"./chunk-QKPHSUUB.mjs";import{c as Q}from"./chunk-DF5IST3K.mjs";import{e as F}from"./chunk-IXYWE4MO.mjs";import{a as Ye}from"./chunk-RHRGATOU.mjs";import{i as Fe,k as Le,m as Ae,n as ze,o as je}from"./chunk-YEHZTER3.mjs";import{Ba as P,Fa as qe,Ja as $e,Ka as j,Na as He,Pa as Ge,Qa as re,Ra as N,k as $,m as Pe,n as De,p as Be,ra as ae,s as oe,sa as Ue,y as Ve}from"./chunk-A3HDF5AK.mjs";import{$ as J,$b as S,Ab as I,Bb as M,Cb as k,Db as we,Eb as Te,Fb as ee,Gb as O,Hb as V,J as X,Ka as he,La as s,Lb as w,M as me,Mb as a,N as ue,P as fe,Pb as Z,Pc as q,Qa as K,Qc as z,R as b,Ra as be,Rb as R,Sb as U,W as u,Wa as Ce,X as f,Xb as Ie,Y as g,Yb as Me,Zb as v,_b as x,ac as ke,bb as y,bc as Oe,cb as _e,da as B,fb as T,gb as ve,gc as te,hb as L,ic as ne,ja as E,jc as Se,lc as Ee,n as de,pa as ge,pb as m,qb as C,rb as _,u as pe,ub as xe,vb as ye,wb as d,xb as r,yb as l,zb as p,zc as A}from"./chunk-ERHZA7IG.mjs";import"./chunk-T55IDOPT.mjs";function Ke(t,i){let n=!i?.manualCleanup?i?.injector?.get(J)??b(J):null,o=rt(i?.equal),c;i?.requireSync?c=B({kind:0},{equal:o}):c=B({kind:1,value:i?.initialValue},{equal:o});let h,ce=t.subscribe({next:D=>c.set({kind:1,value:D}),error:D=>{c.set({kind:2,error:D}),h?.()},complete:()=>{h?.()}});if(i?.requireSync&&c().kind===0)throw new X(601,!1);return h=n?.onDestroy(ce.unsubscribe.bind(ce)),A(()=>{let D=c();switch(D.kind){case 1:return D.value;case 2:throw D.error;case 0:throw new X(601,!1)}},{equal:i?.equal})}function rt(t=Object.is){return(i,e)=>i.kind===1&&e.kind===1&&t(i.value,e.value)}var lt=["data-p-icon","exclamation-triangle"],et=(()=>{class t extends F{pathId;onInit(){this.pathId="url(#"+P()+")"}static \u0275fac=(()=>{let e;return function(o){return(e||(e=E(t)))(o||t)}})();static \u0275cmp=y({type:t,selectors:[["","data-p-icon","exclamation-triangle"]],features:[T],attrs:lt,decls:7,vars:2,consts:[["d","M13.4018 13.1893H0.598161C0.49329 13.189 0.390283 13.1615 0.299143 13.1097C0.208003 13.0578 0.131826 12.9832 0.0780112 12.8932C0.0268539 12.8015 0 12.6982 0 12.5931C0 12.4881 0.0268539 12.3848 0.0780112 12.293L6.47985 1.08982C6.53679 1.00399 6.61408 0.933574 6.70484 0.884867C6.7956 0.836159 6.897 0.810669 7 0.810669C7.103 0.810669 7.2044 0.836159 7.29516 0.884867C7.38592 0.933574 7.46321 1.00399 7.52015 1.08982L13.922 12.293C13.9731 12.3848 14 12.4881 14 12.5931C14 12.6982 13.9731 12.8015 13.922 12.8932C13.8682 12.9832 13.792 13.0578 13.7009 13.1097C13.6097 13.1615 13.5067 13.189 13.4018 13.1893ZM1.63046 11.989H12.3695L7 2.59425L1.63046 11.989Z","fill","currentColor"],["d","M6.99996 8.78801C6.84143 8.78594 6.68997 8.72204 6.57787 8.60993C6.46576 8.49782 6.40186 8.34637 6.39979 8.18784V5.38703C6.39979 5.22786 6.46302 5.0752 6.57557 4.96265C6.68813 4.85009 6.84078 4.78686 6.99996 4.78686C7.15914 4.78686 7.31179 4.85009 7.42435 4.96265C7.5369 5.0752 7.60013 5.22786 7.60013 5.38703V8.18784C7.59806 8.34637 7.53416 8.49782 7.42205 8.60993C7.30995 8.72204 7.15849 8.78594 6.99996 8.78801Z","fill","currentColor"],["d","M6.99996 11.1887C6.84143 11.1866 6.68997 11.1227 6.57787 11.0106C6.46576 10.8985 6.40186 10.7471 6.39979 10.5885V10.1884C6.39979 10.0292 6.46302 9.87658 6.57557 9.76403C6.68813 9.65147 6.84078 9.58824 6.99996 9.58824C7.15914 9.58824 7.31179 9.65147 7.42435 9.76403C7.5369 9.87658 7.60013 10.0292 7.60013 10.1884V10.5885C7.59806 10.7471 7.53416 10.8985 7.42205 11.0106C7.30995 11.1227 7.15849 11.1866 6.99996 11.1887Z","fill","currentColor"],[3,"id"],["width","14","height","14","fill","white"]],template:function(n,o){n&1&&(g(),I(0,"g"),k(1,"path",0)(2,"path",1)(3,"path",2),M(),I(4,"defs")(5,"clipPath",3),k(6,"rect",4),M()()),n&2&&(m("clip-path",o.pathId),s(5),V("id",o.pathId))},encapsulation:2})}return t})();var ct=["data-p-icon","info-circle"],tt=(()=>{class t extends F{pathId;onInit(){this.pathId="url(#"+P()+")"}static \u0275fac=(()=>{let e;return function(o){return(e||(e=E(t)))(o||t)}})();static \u0275cmp=y({type:t,selectors:[["","data-p-icon","info-circle"]],features:[T],attrs:ct,decls:5,vars:2,consts:[["fill-rule","evenodd","clip-rule","evenodd","d","M3.11101 12.8203C4.26215 13.5895 5.61553 14 7 14C8.85652 14 10.637 13.2625 11.9497 11.9497C13.2625 10.637 14 8.85652 14 7C14 5.61553 13.5895 4.26215 12.8203 3.11101C12.0511 1.95987 10.9579 1.06266 9.67879 0.532846C8.3997 0.00303296 6.99224 -0.13559 5.63437 0.134506C4.2765 0.404603 3.02922 1.07129 2.05026 2.05026C1.07129 3.02922 0.404603 4.2765 0.134506 5.63437C-0.13559 6.99224 0.00303296 8.3997 0.532846 9.67879C1.06266 10.9579 1.95987 12.0511 3.11101 12.8203ZM3.75918 2.14976C4.71846 1.50879 5.84628 1.16667 7 1.16667C8.5471 1.16667 10.0308 1.78125 11.1248 2.87521C12.2188 3.96918 12.8333 5.45291 12.8333 7C12.8333 8.15373 12.4912 9.28154 11.8502 10.2408C11.2093 11.2001 10.2982 11.9478 9.23232 12.3893C8.16642 12.8308 6.99353 12.9463 5.86198 12.7212C4.73042 12.4962 3.69102 11.9406 2.87521 11.1248C2.05941 10.309 1.50384 9.26958 1.27876 8.13803C1.05367 7.00647 1.16919 5.83358 1.61071 4.76768C2.05222 3.70178 2.79989 2.79074 3.75918 2.14976ZM7.00002 4.8611C6.84594 4.85908 6.69873 4.79698 6.58977 4.68801C6.48081 4.57905 6.4187 4.43185 6.41669 4.27776V3.88888C6.41669 3.73417 6.47815 3.58579 6.58754 3.4764C6.69694 3.367 6.84531 3.30554 7.00002 3.30554C7.15473 3.30554 7.3031 3.367 7.4125 3.4764C7.52189 3.58579 7.58335 3.73417 7.58335 3.88888V4.27776C7.58134 4.43185 7.51923 4.57905 7.41027 4.68801C7.30131 4.79698 7.1541 4.85908 7.00002 4.8611ZM7.00002 10.6945C6.84594 10.6925 6.69873 10.6304 6.58977 10.5214C6.48081 10.4124 6.4187 10.2652 6.41669 10.1111V6.22225C6.41669 6.06754 6.47815 5.91917 6.58754 5.80977C6.69694 5.70037 6.84531 5.63892 7.00002 5.63892C7.15473 5.63892 7.3031 5.70037 7.4125 5.80977C7.52189 5.91917 7.58335 6.06754 7.58335 6.22225V10.1111C7.58134 10.2652 7.51923 10.4124 7.41027 10.5214C7.30131 10.6304 7.1541 10.6925 7.00002 10.6945Z","fill","currentColor"],[3,"id"],["width","14","height","14","fill","white"]],template:function(n,o){n&1&&(g(),I(0,"g"),k(1,"path",0),M(),I(2,"defs")(3,"clipPath",1),k(4,"rect",2),M()()),n&2&&(m("clip-path",o.pathId),s(3),V("id",o.pathId))},encapsulation:2})}return t})();var dt=["data-p-icon","times-circle"],nt=(()=>{class t extends F{pathId;onInit(){this.pathId="url(#"+P()+")"}static \u0275fac=(()=>{let e;return function(o){return(e||(e=E(t)))(o||t)}})();static \u0275cmp=y({type:t,selectors:[["","data-p-icon","times-circle"]],features:[T],attrs:dt,decls:5,vars:2,consts:[["fill-rule","evenodd","clip-rule","evenodd","d","M7 14C5.61553 14 4.26215 13.5895 3.11101 12.8203C1.95987 12.0511 1.06266 10.9579 0.532846 9.67879C0.00303296 8.3997 -0.13559 6.99224 0.134506 5.63437C0.404603 4.2765 1.07129 3.02922 2.05026 2.05026C3.02922 1.07129 4.2765 0.404603 5.63437 0.134506C6.99224 -0.13559 8.3997 0.00303296 9.67879 0.532846C10.9579 1.06266 12.0511 1.95987 12.8203 3.11101C13.5895 4.26215 14 5.61553 14 7C14 8.85652 13.2625 10.637 11.9497 11.9497C10.637 13.2625 8.85652 14 7 14ZM7 1.16667C5.84628 1.16667 4.71846 1.50879 3.75918 2.14976C2.79989 2.79074 2.05222 3.70178 1.61071 4.76768C1.16919 5.83358 1.05367 7.00647 1.27876 8.13803C1.50384 9.26958 2.05941 10.309 2.87521 11.1248C3.69102 11.9406 4.73042 12.4962 5.86198 12.7212C6.99353 12.9463 8.16642 12.8308 9.23232 12.3893C10.2982 11.9478 11.2093 11.2001 11.8502 10.2408C12.4912 9.28154 12.8333 8.15373 12.8333 7C12.8333 5.45291 12.2188 3.96918 11.1248 2.87521C10.0308 1.78125 8.5471 1.16667 7 1.16667ZM4.66662 9.91668C4.58998 9.91704 4.51404 9.90209 4.44325 9.87271C4.37246 9.84333 4.30826 9.8001 4.2544 9.74557C4.14516 9.6362 4.0838 9.48793 4.0838 9.33335C4.0838 9.17876 4.14516 9.0305 4.2544 8.92113L6.17553 7L4.25443 5.07891C4.15139 4.96832 4.09529 4.82207 4.09796 4.67094C4.10063 4.51982 4.16185 4.37563 4.26872 4.26876C4.3756 4.16188 4.51979 4.10066 4.67091 4.09799C4.82204 4.09532 4.96829 4.15142 5.07887 4.25446L6.99997 6.17556L8.92106 4.25446C9.03164 4.15142 9.1779 4.09532 9.32903 4.09799C9.48015 4.10066 9.62434 4.16188 9.73121 4.26876C9.83809 4.37563 9.89931 4.51982 9.90198 4.67094C9.90464 4.82207 9.84855 4.96832 9.74551 5.07891L7.82441 7L9.74554 8.92113C9.85478 9.0305 9.91614 9.17876 9.91614 9.33335C9.91614 9.48793 9.85478 9.6362 9.74554 9.74557C9.69168 9.8001 9.62748 9.84333 9.55669 9.87271C9.4859 9.90209 9.40996 9.91704 9.33332 9.91668C9.25668 9.91704 9.18073 9.90209 9.10995 9.87271C9.03916 9.84333 8.97495 9.8001 8.9211 9.74557L6.99997 7.82444L5.07884 9.74557C5.02499 9.8001 4.96078 9.84333 4.88999 9.87271C4.81921 9.90209 4.74326 9.91704 4.66662 9.91668Z","fill","currentColor"],[3,"id"],["width","14","height","14","fill","white"]],template:function(n,o){n&1&&(g(),I(0,"g"),k(1,"path",0),M(),I(2,"defs")(3,"clipPath",1),k(4,"rect",2),M()()),n&2&&(m("clip-path",o.pathId),s(3),V("id",o.pathId))},encapsulation:2})}return t})();var ot=`
    .p-toast {
        width: dt('toast.width');
        white-space: pre-line;
        word-break: break-word;
    }

    .p-toast-message {
        margin: 0 0 1rem 0;
    }

    .p-toast-message-icon {
        flex-shrink: 0;
        font-size: dt('toast.icon.size');
        width: dt('toast.icon.size');
        height: dt('toast.icon.size');
    }

    .p-toast-message-content {
        display: flex;
        align-items: flex-start;
        padding: dt('toast.content.padding');
        gap: dt('toast.content.gap');
    }

    .p-toast-message-text {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        gap: dt('toast.text.gap');
    }

    .p-toast-summary {
        font-weight: dt('toast.summary.font.weight');
        font-size: dt('toast.summary.font.size');
    }

    .p-toast-detail {
        font-weight: dt('toast.detail.font.weight');
        font-size: dt('toast.detail.font.size');
    }

    .p-toast-close-button {
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        background: transparent;
        transition:
            background dt('toast.transition.duration'),
            color dt('toast.transition.duration'),
            outline-color dt('toast.transition.duration'),
            box-shadow dt('toast.transition.duration');
        outline-color: transparent;
        color: inherit;
        width: dt('toast.close.button.width');
        height: dt('toast.close.button.height');
        border-radius: dt('toast.close.button.border.radius');
        margin: -25% 0 0 0;
        right: -25%;
        padding: 0;
        border: none;
        user-select: none;
    }

    .p-toast-close-button:dir(rtl) {
        margin: -25% 0 0 auto;
        left: -25%;
        right: auto;
    }

    .p-toast-message-info,
    .p-toast-message-success,
    .p-toast-message-warn,
    .p-toast-message-error,
    .p-toast-message-secondary,
    .p-toast-message-contrast {
        border-width: dt('toast.border.width');
        border-style: solid;
        backdrop-filter: blur(dt('toast.blur'));
        border-radius: dt('toast.border.radius');
    }

    .p-toast-close-icon {
        font-size: dt('toast.close.icon.size');
        width: dt('toast.close.icon.size');
        height: dt('toast.close.icon.size');
    }

    .p-toast-close-button:focus-visible {
        outline-width: dt('focus.ring.width');
        outline-style: dt('focus.ring.style');
        outline-offset: dt('focus.ring.offset');
    }

    .p-toast-message-info {
        background: dt('toast.info.background');
        border-color: dt('toast.info.border.color');
        color: dt('toast.info.color');
        box-shadow: dt('toast.info.shadow');
    }

    .p-toast-message-info .p-toast-detail {
        color: dt('toast.info.detail.color');
    }

    .p-toast-message-info .p-toast-close-button:focus-visible {
        outline-color: dt('toast.info.close.button.focus.ring.color');
        box-shadow: dt('toast.info.close.button.focus.ring.shadow');
    }

    .p-toast-message-info .p-toast-close-button:hover {
        background: dt('toast.info.close.button.hover.background');
    }

    .p-toast-message-success {
        background: dt('toast.success.background');
        border-color: dt('toast.success.border.color');
        color: dt('toast.success.color');
        box-shadow: dt('toast.success.shadow');
    }

    .p-toast-message-success .p-toast-detail {
        color: dt('toast.success.detail.color');
    }

    .p-toast-message-success .p-toast-close-button:focus-visible {
        outline-color: dt('toast.success.close.button.focus.ring.color');
        box-shadow: dt('toast.success.close.button.focus.ring.shadow');
    }

    .p-toast-message-success .p-toast-close-button:hover {
        background: dt('toast.success.close.button.hover.background');
    }

    .p-toast-message-warn {
        background: dt('toast.warn.background');
        border-color: dt('toast.warn.border.color');
        color: dt('toast.warn.color');
        box-shadow: dt('toast.warn.shadow');
    }

    .p-toast-message-warn .p-toast-detail {
        color: dt('toast.warn.detail.color');
    }

    .p-toast-message-warn .p-toast-close-button:focus-visible {
        outline-color: dt('toast.warn.close.button.focus.ring.color');
        box-shadow: dt('toast.warn.close.button.focus.ring.shadow');
    }

    .p-toast-message-warn .p-toast-close-button:hover {
        background: dt('toast.warn.close.button.hover.background');
    }

    .p-toast-message-error {
        background: dt('toast.error.background');
        border-color: dt('toast.error.border.color');
        color: dt('toast.error.color');
        box-shadow: dt('toast.error.shadow');
    }

    .p-toast-message-error .p-toast-detail {
        color: dt('toast.error.detail.color');
    }

    .p-toast-message-error .p-toast-close-button:focus-visible {
        outline-color: dt('toast.error.close.button.focus.ring.color');
        box-shadow: dt('toast.error.close.button.focus.ring.shadow');
    }

    .p-toast-message-error .p-toast-close-button:hover {
        background: dt('toast.error.close.button.hover.background');
    }

    .p-toast-message-secondary {
        background: dt('toast.secondary.background');
        border-color: dt('toast.secondary.border.color');
        color: dt('toast.secondary.color');
        box-shadow: dt('toast.secondary.shadow');
    }

    .p-toast-message-secondary .p-toast-detail {
        color: dt('toast.secondary.detail.color');
    }

    .p-toast-message-secondary .p-toast-close-button:focus-visible {
        outline-color: dt('toast.secondary.close.button.focus.ring.color');
        box-shadow: dt('toast.secondary.close.button.focus.ring.shadow');
    }

    .p-toast-message-secondary .p-toast-close-button:hover {
        background: dt('toast.secondary.close.button.hover.background');
    }

    .p-toast-message-contrast {
        background: dt('toast.contrast.background');
        border-color: dt('toast.contrast.border.color');
        color: dt('toast.contrast.color');
        box-shadow: dt('toast.contrast.shadow');
    }

    .p-toast-message-contrast .p-toast-detail {
        color: dt('toast.contrast.detail.color');
    }

    .p-toast-message-contrast .p-toast-close-button:focus-visible {
        outline-color: dt('toast.contrast.close.button.focus.ring.color');
        box-shadow: dt('toast.contrast.close.button.focus.ring.shadow');
    }

    .p-toast-message-contrast .p-toast-close-button:hover {
        background: dt('toast.contrast.close.button.hover.background');
    }

    .p-toast-top-center {
        transform: translateX(-50%);
    }

    .p-toast-bottom-center {
        transform: translateX(-50%);
    }

    .p-toast-center {
        min-width: 20vw;
        transform: translate(-50%, -50%);
    }

    .p-toast-message-enter-from {
        opacity: 0;
        transform: translateY(50%);
    }

    .p-toast-message-leave-from {
        max-height: 1000px;
    }

    .p-toast .p-toast-message.p-toast-message-leave-to {
        max-height: 0;
        opacity: 0;
        margin-bottom: 0;
        overflow: hidden;
    }

    .p-toast-message-enter-active {
        transition:
            transform 0.3s,
            opacity 0.3s;
    }

    .p-toast-message-leave-active {
        transition:
            max-height 0.45s cubic-bezier(0, 1, 0, 1),
            opacity 0.3s,
            margin-bottom 0.3s;
    }
`;var pt=(t,i,e,n)=>({showTransformParams:t,hideTransformParams:i,showTransitionParams:e,hideTransitionParams:n}),mt=t=>({value:"visible",params:t}),ut=(t,i)=>({$implicit:t,closeFn:i}),ft=t=>({$implicit:t});function gt(t,i){t&1&&ee(0)}function ht(t,i){if(t&1&&L(0,gt,1,0,"ng-container",3),t&2){let e=a();d("ngTemplateOutlet",e.headlessTemplate)("ngTemplateOutletContext",Se(2,ut,e.message,e.onCloseIconClick))}}function bt(t,i){if(t&1&&p(0,"span",4),t&2){let e=a(3);v(e.cn(e.cx("messageIcon"),e.message==null?null:e.message.icon)),d("pBind",e.ptm("messageIcon"))}}function Ct(t,i){if(t&1&&(g(),p(0,"svg",11)),t&2){let e=a(4);v(e.cx("messageIcon")),d("pBind",e.ptm("messageIcon")),m("aria-hidden",!0)}}function _t(t,i){if(t&1&&(g(),p(0,"svg",12)),t&2){let e=a(4);v(e.cx("messageIcon")),d("pBind",e.ptm("messageIcon")),m("aria-hidden",!0)}}function vt(t,i){if(t&1&&(g(),p(0,"svg",13)),t&2){let e=a(4);v(e.cx("messageIcon")),d("pBind",e.ptm("messageIcon")),m("aria-hidden",!0)}}function xt(t,i){if(t&1&&(g(),p(0,"svg",14)),t&2){let e=a(4);v(e.cx("messageIcon")),d("pBind",e.ptm("messageIcon")),m("aria-hidden",!0)}}function yt(t,i){if(t&1&&(g(),p(0,"svg",12)),t&2){let e=a(4);v(e.cx("messageIcon")),d("pBind",e.ptm("messageIcon")),m("aria-hidden",!0)}}function wt(t,i){if(t&1&&C(0,Ct,1,4,":svg:svg",7)(1,_t,1,4,":svg:svg",8)(2,vt,1,4,":svg:svg",9)(3,xt,1,4,":svg:svg",10)(4,yt,1,4,":svg:svg",8),t&2){let e,n=a(3);_((e=n.message.severity)==="success"?0:e==="info"?1:e==="error"?2:e==="warn"?3:4)}}function Tt(t,i){if(t&1&&(we(0),C(1,bt,1,3,"span",2)(2,wt,5,1),r(3,"div",6)(4,"div",6),x(5),l(),r(6,"div",6),x(7),l()(),Te()),t&2){let e=a(2);s(),_(e.message.icon?1:2),s(2),d("pBind",e.ptm("messageText"))("ngClass",e.cx("messageText")),s(),d("pBind",e.ptm("summary"))("ngClass",e.cx("summary")),s(),ke(" ",e.message.summary," "),s(),d("pBind",e.ptm("detail"))("ngClass",e.cx("detail")),s(),S(e.message.detail)}}function It(t,i){t&1&&ee(0)}function Mt(t,i){if(t&1&&p(0,"span",4),t&2){let e=a(4);v(e.cn(e.cx("closeIcon"),e.message==null?null:e.message.closeIcon)),d("pBind",e.ptm("closeIcon"))}}function kt(t,i){if(t&1&&L(0,Mt,1,3,"span",17),t&2){let e=a(3);d("ngIf",e.message.closeIcon)}}function Ot(t,i){if(t&1&&(g(),p(0,"svg",18)),t&2){let e=a(3);v(e.cx("closeIcon")),d("pBind",e.ptm("closeIcon")),m("aria-hidden",!0)}}function St(t,i){if(t&1){let e=O();r(0,"div")(1,"button",15),w("click",function(o){u(e);let c=a(2);return f(c.onCloseIconClick(o))})("keydown.enter",function(o){u(e);let c=a(2);return f(c.onCloseIconClick(o))}),C(2,kt,1,1,"span",2)(3,Ot,1,4,":svg:svg",16),l()()}if(t&2){let e=a(2);s(),d("pBind",e.ptm("closeButton")),m("class",e.cx("closeButton"))("aria-label",e.closeAriaLabel),s(),_(e.message.closeIcon?2:3)}}function Et(t,i){if(t&1&&(r(0,"div",4),L(1,Tt,8,9,"ng-container",5)(2,It,1,0,"ng-container",3),C(3,St,4,4,"div"),l()),t&2){let e=a();v(e.cn(e.cx("messageContent"),e.message==null?null:e.message.contentStyleClass)),d("pBind",e.ptm("messageContent")),s(),d("ngIf",!e.template),s(),d("ngTemplateOutlet",e.template)("ngTemplateOutletContext",ne(7,ft,e.message)),s(),_((e.message==null?null:e.message.closable)!==!1?3:-1)}}var Pt=["message"],Dt=["headless"];function Bt(t,i){if(t&1){let e=O();r(0,"p-toastItem",1),w("onClose",function(o){u(e);let c=a();return f(c.onMessageClose(o))})("@toastAnimation.start",function(o){u(e);let c=a();return f(c.onAnimationStart(o))})("@toastAnimation.done",function(o){u(e);let c=a();return f(c.onAnimationEnd(o))}),l()}if(t&2){let e=i.$implicit,n=i.index,o=a();d("message",e)("index",n)("life",o.life)("template",o.template||o._template)("headlessTemplate",o.headlessTemplate||o._headlessTemplate)("@toastAnimation",void 0)("showTransformOptions",o.showTransformOptions)("hideTransformOptions",o.hideTransformOptions)("showTransitionOptions",o.showTransitionOptions)("hideTransitionOptions",o.hideTransitionOptions)("pt",o.pt)}}var Vt={root:({instance:t})=>{let{_position:i}=t;return{position:"fixed",top:i==="top-right"||i==="top-left"||i==="top-center"?"20px":i==="center"?"50%":null,right:(i==="top-right"||i==="bottom-right")&&"20px",bottom:(i==="bottom-left"||i==="bottom-right"||i==="bottom-center")&&"20px",left:i==="top-left"||i==="bottom-left"?"20px":i==="center"||i==="top-center"||i==="bottom-center"?"50%":null}}},Ft={root:({instance:t})=>["p-toast p-component",`p-toast-${t._position}`],message:({instance:t})=>({"p-toast-message":!0,"p-toast-message-info":t.message.severity==="info"||t.message.severity===void 0,"p-toast-message-warn":t.message.severity==="warn","p-toast-message-error":t.message.severity==="error","p-toast-message-success":t.message.severity==="success","p-toast-message-secondary":t.message.severity==="secondary","p-toast-message-contrast":t.message.severity==="contrast"}),messageContent:"p-toast-message-content",messageIcon:({instance:t})=>({"p-toast-message-icon":!0,[`pi ${t.message.icon}`]:!!t.message.icon}),messageText:"p-toast-message-text",summary:"p-toast-summary",detail:"p-toast-detail",closeButton:"p-toast-close-button",closeIcon:({instance:t})=>({"p-toast-close-icon":!0,[`pi ${t.message.closeIcon}`]:!!t.message.closeIcon})},Y=(()=>{class t extends He{name="toast";style=ot;classes=Ft;inlineStyles=Vt;static \u0275fac=(()=>{let e;return function(o){return(e||(e=E(t)))(o||t)}})();static \u0275prov=me({token:t,factory:t.\u0275fac})}return t})();var it=new fe("TOAST_INSTANCE"),Lt=(()=>{class t extends re{zone;message;index;life;template;headlessTemplate;showTransformOptions;hideTransformOptions;showTransitionOptions;hideTransitionOptions;onClose=new K;_componentStyle=b(Y);timeout;constructor(e){super(),this.zone=e}onAfterViewInit(){this.initTimeout()}initTimeout(){this.message?.sticky||(this.clearTimeout(),this.zone.runOutsideAngular(()=>{this.timeout=setTimeout(()=>{this.onClose.emit({index:this.index,message:this.message})},this.message?.life||this.life||3e3)}))}clearTimeout(){this.timeout&&(clearTimeout(this.timeout),this.timeout=null)}onMouseEnter(){this.clearTimeout()}onMouseLeave(){this.initTimeout()}onCloseIconClick=e=>{this.clearTimeout(),this.onClose.emit({index:this.index,message:this.message}),e.preventDefault()};get closeAriaLabel(){return this.config.translation.aria?this.config.translation.aria.close:void 0}onDestroy(){this.clearTimeout()}static \u0275fac=function(n){return new(n||t)(Ce(be))};static \u0275cmp=y({type:t,selectors:[["p-toastItem"]],inputs:{message:"message",index:[2,"index","index",z],life:[2,"life","life",z],template:"template",headlessTemplate:"headlessTemplate",showTransformOptions:"showTransformOptions",hideTransformOptions:"hideTransformOptions",showTransitionOptions:"showTransitionOptions",hideTransitionOptions:"hideTransitionOptions"},outputs:{onClose:"onClose"},features:[te([Y]),T],decls:4,vars:13,consts:[["container",""],["role","alert","aria-live","assertive","aria-atomic","true",3,"mouseenter","mouseleave","pBind"],[3,"pBind","class"],[4,"ngTemplateOutlet","ngTemplateOutletContext"],[3,"pBind"],[4,"ngIf"],[3,"pBind","ngClass"],["data-p-icon","check",3,"pBind","class"],["data-p-icon","info-circle",3,"pBind","class"],["data-p-icon","times-circle",3,"pBind","class"],["data-p-icon","exclamation-triangle",3,"pBind","class"],["data-p-icon","check",3,"pBind"],["data-p-icon","info-circle",3,"pBind"],["data-p-icon","times-circle",3,"pBind"],["data-p-icon","exclamation-triangle",3,"pBind"],["type","button","autofocus","",3,"click","keydown.enter","pBind"],["data-p-icon","times",3,"pBind","class"],[3,"pBind","class",4,"ngIf"],["data-p-icon","times",3,"pBind"]],template:function(n,o){if(n&1){let c=O();r(0,"div",1,0),w("mouseenter",function(){return u(c),f(o.onMouseEnter())})("mouseleave",function(){return u(c),f(o.onMouseLeave())}),C(2,ht,1,5,"ng-container")(3,Et,4,9,"div",2),l()}n&2&&(v(o.cn(o.cx("message"),o.message==null?null:o.message.styleClass)),d("pBind",o.ptm("message"))("@messageState",ne(11,mt,Ee(6,pt,o.showTransformOptions,o.hideTransformOptions,o.showTransitionOptions,o.hideTransitionOptions))),m("id",o.message==null?null:o.message.id),s(2),_(o.headlessTemplate?2:3))},dependencies:[oe,$,De,Be,Qe,et,tt,We,nt,j,N],encapsulation:2,data:{animation:[ie("messageState",[Ne("visible",H({transform:"translateY(0)",opacity:1})),G("void => *",[H({transform:"{{showTransformParams}}",opacity:0}),se("{{showTransitionParams}}")]),G("* => void",[se("{{hideTransitionParams}}",H({height:0,opacity:0,transform:"{{hideTransformParams}}"}))])])]},changeDetection:0})}return t})(),le=(()=>{class t extends re{$pcToast=b(it,{optional:!0,skipSelf:!0})??void 0;bindDirectiveInstance=b(N,{self:!0});onAfterViewChecked(){this.bindDirectiveInstance.setAttrs(this.ptms(["host","root"]))}key;autoZIndex=!0;baseZIndex=0;life=3e3;styleClass;get position(){return this._position}set position(e){this._position=e,this.cd.markForCheck()}preventOpenDuplicates=!1;preventDuplicates=!1;showTransformOptions="translateY(100%)";hideTransformOptions="translateY(-100%)";showTransitionOptions="300ms ease-out";hideTransitionOptions="250ms ease-in";breakpoints;onClose=new K;template;headlessTemplate;messageSubscription;clearSubscription;messages;messagesArchieve;_position="top-right";messageService=b(qe);_componentStyle=b(Y);styleElement;id=P("pn_id_");templates;constructor(){super()}onInit(){this.messageSubscription=this.messageService.messageObserver.subscribe(e=>{if(e)if(Array.isArray(e)){let n=e.filter(o=>this.canAdd(o));this.add(n)}else this.canAdd(e)&&this.add([e])}),this.clearSubscription=this.messageService.clearObserver.subscribe(e=>{e?this.key===e&&(this.messages=null):this.messages=null,this.cd.markForCheck()})}_template;_headlessTemplate;onAfterContentInit(){this.templates?.forEach(e=>{switch(e.getType()){case"message":this._template=e.template;break;case"headless":this._headlessTemplate=e.template;break;default:this._template=e.template;break}})}onAfterViewInit(){this.breakpoints&&this.createStyle()}add(e){this.messages=this.messages?[...this.messages,...e]:[...e],this.preventDuplicates&&(this.messagesArchieve=this.messagesArchieve?[...this.messagesArchieve,...e]:[...e]),this.cd.markForCheck()}canAdd(e){let n=this.key===e.key;return n&&this.preventOpenDuplicates&&(n=!this.containsMessage(this.messages,e)),n&&this.preventDuplicates&&(n=!this.containsMessage(this.messagesArchieve,e)),n}containsMessage(e,n){return e?e.find(o=>o.summary===n.summary&&o.detail==n.detail&&o.severity===n.severity)!=null:!1}onMessageClose(e){this.messages?.splice(e.index,1),this.onClose.emit({message:e.message}),this.cd.detectChanges()}onAnimationStart(e){e.fromState==="void"&&(this.renderer.setAttribute(this.el?.nativeElement,this.id,""),this.autoZIndex&&this.el?.nativeElement.style.zIndex===""&&Q.set("modal",this.el?.nativeElement,this.baseZIndex||this.config.zIndex.modal))}onAnimationEnd(e){e.toState==="void"&&this.autoZIndex&&Ue(this.messages)&&Q.clear(this.el?.nativeElement)}createStyle(){if(!this.styleElement){this.styleElement=this.renderer.createElement("style"),this.styleElement.type="text/css",ae(this.styleElement,"nonce",this.config?.csp()?.nonce),this.renderer.appendChild(this.document.head,this.styleElement);let e="";for(let n in this.breakpoints){let o="";for(let c in this.breakpoints[n])o+=c+":"+this.breakpoints[n][c]+" !important;";e+=`
                    @media screen and (max-width: ${n}) {
                        .p-toast[${this.id}] {
                           ${o}
                        }
                    }
                `}this.renderer.setProperty(this.styleElement,"innerHTML",e),ae(this.styleElement,"nonce",this.config?.csp()?.nonce)}}destroyStyle(){this.styleElement&&(this.renderer.removeChild(this.document.head,this.styleElement),this.styleElement=null)}onDestroy(){this.messageSubscription&&this.messageSubscription.unsubscribe(),this.el&&this.autoZIndex&&Q.clear(this.el.nativeElement),this.clearSubscription&&this.clearSubscription.unsubscribe(),this.destroyStyle()}static \u0275fac=function(n){return new(n||t)};static \u0275cmp=y({type:t,selectors:[["p-toast"]],contentQueries:function(n,o,c){if(n&1&&(Z(c,Pt,5),Z(c,Dt,5),Z(c,$e,4)),n&2){let h;R(h=U())&&(o.template=h.first),R(h=U())&&(o.headlessTemplate=h.first),R(h=U())&&(o.templates=h)}},hostVars:4,hostBindings:function(n,o){n&2&&(Me(o.sx("root")),v(o.cn(o.cx("root"),o.styleClass)))},inputs:{key:"key",autoZIndex:[2,"autoZIndex","autoZIndex",q],baseZIndex:[2,"baseZIndex","baseZIndex",z],life:[2,"life","life",z],styleClass:"styleClass",position:"position",preventOpenDuplicates:[2,"preventOpenDuplicates","preventOpenDuplicates",q],preventDuplicates:[2,"preventDuplicates","preventDuplicates",q],showTransformOptions:"showTransformOptions",hideTransformOptions:"hideTransformOptions",showTransitionOptions:"showTransitionOptions",hideTransitionOptions:"hideTransitionOptions",breakpoints:"breakpoints"},outputs:{onClose:"onClose"},features:[te([Y,{provide:it,useExisting:t},{provide:Ge,useExisting:t}]),ve([N]),T],decls:1,vars:1,consts:[[3,"message","index","life","template","headlessTemplate","showTransformOptions","hideTransformOptions","showTransitionOptions","hideTransitionOptions","pt","onClose",4,"ngFor","ngForOf"],[3,"onClose","message","index","life","template","headlessTemplate","showTransformOptions","hideTransformOptions","showTransitionOptions","hideTransitionOptions","pt"]],template:function(n,o){n&1&&L(0,Bt,1,11,"p-toastItem",0),n&2&&d("ngForOf",o.messages)},dependencies:[oe,Pe,Lt,j],encapsulation:2,data:{animation:[ie("toastAnimation",[G(":enter, :leave",[Re("@*",Ze())])])]},changeDetection:0})}return t})(),st=(()=>{class t{static \u0275fac=function(n){return new(n||t)};static \u0275mod=_e({type:t});static \u0275inj=ue({imports:[le,j,j]})}return t})();var zt=(t,i)=>i.route;function jt(t,i){if(t&1){let e=O();r(0,"div",21),w("click",function(){u(e);let o=a();return f(o.sidenavOpen.set(!1))}),l()}}function Nt(t,i){if(t&1&&(r(0,"div",6)(1,"span",22),x(2,"Torre\xF3n Fuerte"),l(),r(3,"span",23),x(4),l()()),t&2){let e,n=a();s(4),S((e=n.auth.currentUser())==null?null:e.sedeNombre)}}function Zt(t,i){if(t&1&&(r(0,"span",26),x(1),l()),t&2){let e=a().$implicit;s(),S(e.label)}}function Rt(t,i){if(t&1){let e=O();r(0,"a",24),w("click",function(){u(e);let o=a();return f(o.closeOnMobile())}),p(1,"i",25),C(2,Zt,2,1,"span",26),l()}if(t&2){let e=i.$implicit,n=a();d("routerLink",e.route)("pTooltip",n.sidenavCollapsed()&&!n.isMobile()?e.label:""),s(),d("ngClass",e.icon),s(),_(!n.sidenavCollapsed()||n.isMobile()?2:-1)}}function Ut(t,i){if(t&1){let e=O();r(0,"div",10)(1,"div",27),x(2),l(),r(3,"div",28)(4,"span",29),x(5),l(),r(6,"span",30),x(7),l()(),r(8,"button",31),w("click",function(){u(e);let o=a();return f(o.auth.logout())}),p(9,"i",32),l()()}if(t&2){let e,n,o=a();s(2),S(o.userInitials),s(3),Oe("",(e=o.auth.currentUser())==null?null:e.nombre," ",(e=o.auth.currentUser())==null?null:e.apellido),s(2),S((n=o.auth.currentUser())==null?null:n.rol)}}function qt(t,i){if(t&1){let e=O();r(0,"button",33),w("click",function(){u(e);let o=a();return f(o.auth.logout())}),p(1,"i",32),l()}}function $t(t,i){if(t&1&&(r(0,"div",19),x(1),l()),t&2){let e=a();s(),S(e.userInitials)}}var at=class t{auth=b(Ye);router=b(Ae);platformId=b(ge);navItems=[{label:"Dashboard",icon:"pi-th-large",route:"/dashboard"},{label:"Miembros",icon:"pi-users",route:"/miembros"},{label:"Liderazgo",icon:"pi-sitemap",route:"/lideres"},{label:"Preparaci\xF3n",icon:"pi-sort-alt",route:"/etapas"},{label:"Eventos",icon:"pi-calendar",route:"/eventos"},{label:"Ministerios",icon:"pi-heart",route:"/ministerios"},{label:"Sedes",icon:"pi-building",route:"/sedes",roles:["SuperAdmin"]},{label:"Configuraci\xF3n",icon:"pi-cog",route:"/configuracion"}];isMobile=B(!1);sidenavOpen=B(!0);sidenavCollapsed=B(!1);currentUrl=Ke(this.router.events.pipe(pe(i=>i instanceof Fe),de(i=>i.urlAfterRedirects)),{initialValue:this.router.url});currentPageTitle=A(()=>{let i=this.currentUrl()??"";return this.navItems.find(e=>i.startsWith(e.route))?.label??"Torre\xF3n Fuerte"});currentPageIcon=A(()=>{let i=this.currentUrl()??"";return this.navItems.find(e=>i.startsWith(e.route))?.icon??"pi-shield"});get visibleItems(){let i=this.auth.currentUser()?.rol;return this.navItems.filter(e=>!e.roles||e.roles.includes(i??""))}get userInitials(){let i=this.auth.currentUser();return((i?.nombre?.[0]??"")+(i?.apellido?.[0]??"")).toUpperCase()}ngOnInit(){Ve(this.platformId)&&this.checkBreakpoint()}onResize(){this.checkBreakpoint()}checkBreakpoint(){let i=window.innerWidth<768;this.isMobile.set(i),i?(this.sidenavOpen.set(!1),this.sidenavCollapsed.set(!1)):this.sidenavOpen.set(!0)}toggleSidenav(){this.isMobile()?this.sidenavOpen.set(!this.sidenavOpen()):this.sidenavCollapsed.set(!this.sidenavCollapsed())}closeOnMobile(){this.isMobile()&&this.sidenavOpen.set(!1)}static \u0275fac=function(e){return new(e||t)};static \u0275cmp=y({type:t,selectors:[["app-layout"]],hostBindings:function(e,n){e&1&&w("resize",function(){return n.onResize()},he)},decls:26,vars:14,consts:[[1,"sidenav-container"],[1,"sidenav-backdrop"],[1,"sidenav"],[1,"brand"],[1,"brand-logo"],[1,"pi","pi-shield"],[1,"brand-text"],[1,"nav"],["routerLinkActive","nav-active","tooltipPosition","right",1,"nav-item",3,"routerLink","pTooltip"],[1,"user-section"],[1,"user-row"],["pTooltip","Cerrar sesi\xF3n","tooltipPosition","right",1,"icon-btn","icon-btn-center"],[1,"main-content"],[1,"top-bar"],[1,"icon-btn","menu-btn",3,"click"],[1,"pi",3,"ngClass"],[1,"top-bar-title"],[1,"pi","title-icon",3,"ngClass"],[1,"spacer"],[1,"user-avatar-sm"],[1,"content"],[1,"sidenav-backdrop",3,"click"],[1,"brand-title"],[1,"brand-sub"],["routerLinkActive","nav-active","tooltipPosition","right",1,"nav-item",3,"click","routerLink","pTooltip"],[1,"pi","nav-icon",3,"ngClass"],[1,"nav-label"],[1,"user-avatar"],[1,"user-info"],[1,"user-name"],[1,"user-rol"],["pTooltip","Cerrar sesi\xF3n","tooltipPosition","above",1,"icon-btn",3,"click"],[1,"pi","pi-sign-out"],["pTooltip","Cerrar sesi\xF3n","tooltipPosition","right",1,"icon-btn","icon-btn-center",3,"click"]],template:function(e,n){e&1&&(r(0,"div",0),C(1,jt,1,0,"div",1),r(2,"aside",2)(3,"div",3)(4,"div",4),p(5,"i",5),l(),C(6,Nt,5,1,"div",6),l(),r(7,"nav",7),xe(8,Rt,3,4,"a",8,zt),l(),r(10,"div",9),C(11,Ut,10,4,"div",10)(12,qt,2,0,"button",11),l()(),r(13,"div",12)(14,"header",13)(15,"button",14),w("click",function(){return n.toggleSidenav()}),p(16,"i",15),l(),r(17,"div",16),p(18,"i",17),r(19,"span"),x(20),l()(),p(21,"span",18),C(22,$t,2,1,"div",19),l(),r(23,"div",20),p(24,"router-outlet"),l()()(),p(25,"p-toast")),e&2&&(s(),_(n.isMobile()&&n.sidenavOpen()?1:-1),s(),Ie("collapsed",n.sidenavCollapsed()&&!n.isMobile())("mobile",n.isMobile())("mobile-open",n.isMobile()&&n.sidenavOpen()),s(4),_(!n.sidenavCollapsed()||n.isMobile()?6:-1),s(2),ye(n.visibleItems),s(3),_(!n.sidenavCollapsed()||n.isMobile()?11:12),s(4),m("aria-label","Men\xFA"),s(),d("ngClass",n.isMobile()?"pi-bars":n.sidenavCollapsed()?"pi-angle-double-right":"pi-angle-double-left"),s(2),d("ngClass",n.currentPageIcon()),s(2),S(n.currentPageTitle()),s(2),_(n.isMobile()?22:-1))},dependencies:[Le,ze,je,$,Je,Xe,st,le],styles:['.sidenav-container[_ngcontent-%COMP%]{display:flex;min-height:100vh;background:#f1f5f9}.sidenav-backdrop[_ngcontent-%COMP%]{position:fixed;inset:0;background:#0f172a73;z-index:199}.sidenav[_ngcontent-%COMP%]{width:240px;background:linear-gradient(175deg,#0a7a9e,#0e93bb 55%,#1199c5);display:flex;flex-direction:column;flex-shrink:0;transition:width .25s ease;overflow:hidden}.sidenav.collapsed[_ngcontent-%COMP%]{width:64px}.sidenav.mobile[_ngcontent-%COMP%]{position:fixed;inset:0 auto 0 0;width:260px;z-index:200;transform:translate(-100%);transition:transform .25s ease}.sidenav.mobile.mobile-open[_ngcontent-%COMP%]{transform:translate(0)}.brand[_ngcontent-%COMP%]{display:flex;align-items:center;gap:12px;padding:18px 16px;border-bottom:1px solid rgba(255,255,255,.07);min-height:64px;flex-shrink:0}.brand-logo[_ngcontent-%COMP%]{width:36px;height:36px;border-radius:10px;background:#ffffff2e;display:flex;align-items:center;justify-content:center;flex-shrink:0}.brand-logo[_ngcontent-%COMP%]   i[_ngcontent-%COMP%]{font-size:20px;width:20px;height:20px;color:#fff}.brand-text[_ngcontent-%COMP%]{display:flex;flex-direction:column;overflow:hidden}.brand-title[_ngcontent-%COMP%]{font-size:.9rem;font-weight:700;color:#f8fafc;white-space:nowrap}.brand-sub[_ngcontent-%COMP%]{font-size:.7rem;color:#ffffff73;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.nav[_ngcontent-%COMP%]{flex:1;padding:10px 8px;display:flex;flex-direction:column;gap:2px;overflow-y:auto;overflow-x:hidden}.nav[_ngcontent-%COMP%]::-webkit-scrollbar{width:4px}.nav[_ngcontent-%COMP%]::-webkit-scrollbar-track{background:transparent}.nav[_ngcontent-%COMP%]::-webkit-scrollbar-thumb{background:#ffffff1a;border-radius:4px}.nav-item[_ngcontent-%COMP%]{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;color:#fff9;text-decoration:none;font-size:.86rem;font-weight:500;transition:background .15s,color .15s;white-space:nowrap;cursor:pointer;position:relative;overflow:hidden}.nav-item[_ngcontent-%COMP%]:hover{background:#ffffff12;color:#f8fafc}.nav-active[_ngcontent-%COMP%]{background:#fff3!important;color:#fff!important}.nav-active[_ngcontent-%COMP%]   .nav-icon[_ngcontent-%COMP%]{color:#fff}.nav-active[_ngcontent-%COMP%]:before{content:"";position:absolute;left:0;top:20%;bottom:20%;width:3px;background:#fff;border-radius:0 3px 3px 0}.nav-icon[_ngcontent-%COMP%]{font-size:20px;width:20px;height:20px;flex-shrink:0;color:#ffffff80;transition:color .15s}.nav-item[_ngcontent-%COMP%]:hover   .nav-icon[_ngcontent-%COMP%]{color:#f8fafc}.nav-label[_ngcontent-%COMP%]{overflow:hidden;text-overflow:ellipsis}.collapsed[_ngcontent-%COMP%]   .nav-item[_ngcontent-%COMP%]{justify-content:center;padding:10px;gap:0}.collapsed[_ngcontent-%COMP%]   .brand[_ngcontent-%COMP%]{justify-content:center;padding:14px 0}.user-section[_ngcontent-%COMP%]{padding:12px 8px;border-top:1px solid rgba(255,255,255,.07);flex-shrink:0}.user-row[_ngcontent-%COMP%]{display:flex;align-items:center;gap:10px;padding:8px;border-radius:8px;background:#ffffff0d}.user-avatar[_ngcontent-%COMP%]{width:34px;height:34px;border-radius:50%;background:#ffffff40;color:#fff;font-size:.72rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0}.user-info[_ngcontent-%COMP%]{flex:1;min-width:0;display:flex;flex-direction:column;gap:1px}.user-name[_ngcontent-%COMP%]{font-size:.78rem;font-weight:600;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.user-rol[_ngcontent-%COMP%]{font-size:.65rem;color:#fff6}.icon-btn[_ngcontent-%COMP%]{width:32px;height:32px;border-radius:8px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#ffffff80;transition:background .15s,color .15s;flex-shrink:0}.icon-btn[_ngcontent-%COMP%]:hover{background:#ffffff1a;color:#f8fafc}.icon-btn[_ngcontent-%COMP%]   i[_ngcontent-%COMP%]{font-size:18px;width:18px;height:18px}.icon-btn-center[_ngcontent-%COMP%]{margin:0 auto;display:flex}.top-bar[_ngcontent-%COMP%]{display:flex;align-items:center;gap:12px;height:60px;padding:0 16px 0 8px;background:#fff;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:100;flex-shrink:0}.menu-btn[_ngcontent-%COMP%]{color:#64748b!important;width:38px!important;height:38px!important;border-radius:8px}.menu-btn[_ngcontent-%COMP%]:hover{background:#f1f5f9!important;color:#1e293b!important}.menu-btn[_ngcontent-%COMP%]   i[_ngcontent-%COMP%]{font-size:22px!important;width:22px!important;height:22px!important}.top-bar-title[_ngcontent-%COMP%]{display:flex;align-items:center;gap:8px;font-size:1rem;font-weight:700;color:#1e293b}.title-icon[_ngcontent-%COMP%]{font-size:20px;width:20px;height:20px;color:#1199c5}.spacer[_ngcontent-%COMP%]{flex:1}.user-avatar-sm[_ngcontent-%COMP%]{width:34px;height:34px;border-radius:50%;background:#ffffff40;color:#fff;font-size:.72rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0}.main-content[_ngcontent-%COMP%]{display:flex;flex-direction:column;min-height:100vh;background:#f1f5f9}.content[_ngcontent-%COMP%]{flex:1;padding:24px;max-width:1200px;width:100%;margin:0 auto;box-sizing:border-box}@media(max-width:767px){.sidenav[_ngcontent-%COMP%]{width:260px!important}.content[_ngcontent-%COMP%]{padding:16px}.top-bar[_ngcontent-%COMP%]{height:56px}}@media(max-width:480px){.content[_ngcontent-%COMP%]{padding:12px}}']})};export{at as LayoutComponent};
