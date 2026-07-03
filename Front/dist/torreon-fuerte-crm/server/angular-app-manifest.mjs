
export default {
  bootstrap: () => import('./main.server.mjs').then(m => m.default),
  inlineCriticalCss: true,
  baseHref: '/',
  locale: undefined,
  routes: [
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/"
  },
  {
    "renderMode": 2,
    "preload": [
      "chunk-YMHF5UGV.js",
      "chunk-GZJ5WSQV.js",
      "chunk-NNDRDHYR.js",
      "chunk-L4OEQCKD.js",
      "chunk-HYVENQRU.js",
      "chunk-BWPHFXSB.js"
    ],
    "route": "/login"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/dashboard"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/miembros"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/miembros/nuevo"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/miembros/*"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/miembros/*/editar"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/lideres"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/etapas"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/eventos"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/ministerios"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/sedes"
  },
  {
    "renderMode": 1,
    "preload": [
      "chunk-UG4MDGZI.js",
      "chunk-XVGN2EXP.js",
      "chunk-WUWK25TA.js"
    ],
    "route": "/configuracion"
  },
  {
    "renderMode": 1,
    "redirectTo": "/",
    "route": "/**"
  }
],
  entryPointToBrowserMapping: undefined,
  assets: {
    'index.csr.html': {size: 4374, hash: 'c26e3d9121f74c9d7f61b05ac5c4cb46438966deeeb239e4061a21bdf739986a', text: () => import('./assets-chunks/index_csr_html.mjs').then(m => m.default)},
    'index.server.html': {size: 1464, hash: 'a6cfb36099b6ea1b38094ecd9c3974e14e0549b0086db8edf711b47df459dc69', text: () => import('./assets-chunks/index_server_html.mjs').then(m => m.default)},
    'login/index.html': {size: 86017, hash: '61ebe9192937d84550d18922460b28d6af922ff90101aef47c53156b5ef60756', text: () => import('./assets-chunks/login_index_html.mjs').then(m => m.default)},
    'styles-WLSP4NJC.css': {size: 34607, hash: '1lGQnmd7AvA', text: () => import('./assets-chunks/styles-WLSP4NJC_css.mjs').then(m => m.default)}
  },
};
