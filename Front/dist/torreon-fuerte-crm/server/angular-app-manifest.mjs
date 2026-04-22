
export default {
  bootstrap: () => import('./main.server.mjs').then(m => m.default),
  inlineCriticalCss: false,
  baseHref: '/',
  locale: undefined,
  routes: [
  {
    "renderMode": 2,
    "preload": [
      "chunk-BXZNKSDQ.js",
      "chunk-BYOUWXMH.js",
      "chunk-TKMUIM6Y.js",
      "chunk-QEJW36V7.js",
      "chunk-6T6PUUJ5.js",
      "chunk-ATYHHWXS.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/"
  },
  {
    "renderMode": 2,
    "preload": [
      "chunk-NI2AETAG.js",
      "chunk-ECFSJ26P.js",
      "chunk-QEJW36V7.js",
      "chunk-XI5MGTD6.js",
      "chunk-ATYHHWXS.js",
      "chunk-IR6HV7K5.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/login"
  },
  {
    "renderMode": 2,
    "preload": [
      "chunk-BXZNKSDQ.js",
      "chunk-BYOUWXMH.js",
      "chunk-TKMUIM6Y.js",
      "chunk-QEJW36V7.js",
      "chunk-6T6PUUJ5.js",
      "chunk-ATYHHWXS.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/dashboard"
  },
  {
    "renderMode": 2,
    "preload": [
      "chunk-BXZNKSDQ.js",
      "chunk-BYOUWXMH.js",
      "chunk-TKMUIM6Y.js",
      "chunk-QEJW36V7.js",
      "chunk-6T6PUUJ5.js",
      "chunk-ATYHHWXS.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/miembros"
  },
  {
    "renderMode": 0,
    "preload": [
      "chunk-BXZNKSDQ.js",
      "chunk-BYOUWXMH.js",
      "chunk-TKMUIM6Y.js",
      "chunk-QEJW36V7.js",
      "chunk-6T6PUUJ5.js",
      "chunk-ATYHHWXS.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/miembros/nuevo"
  },
  {
    "renderMode": 0,
    "preload": [
      "chunk-BXZNKSDQ.js",
      "chunk-BYOUWXMH.js",
      "chunk-TKMUIM6Y.js",
      "chunk-QEJW36V7.js",
      "chunk-6T6PUUJ5.js",
      "chunk-ATYHHWXS.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/miembros/*/editar"
  },
  {
    "renderMode": 2,
    "preload": [
      "chunk-BXZNKSDQ.js",
      "chunk-BYOUWXMH.js",
      "chunk-TKMUIM6Y.js",
      "chunk-QEJW36V7.js",
      "chunk-6T6PUUJ5.js",
      "chunk-ATYHHWXS.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/lideres"
  },
  {
    "renderMode": 2,
    "preload": [
      "chunk-BXZNKSDQ.js",
      "chunk-BYOUWXMH.js",
      "chunk-TKMUIM6Y.js",
      "chunk-QEJW36V7.js",
      "chunk-6T6PUUJ5.js",
      "chunk-ATYHHWXS.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/etapas"
  },
  {
    "renderMode": 2,
    "preload": [
      "chunk-BXZNKSDQ.js",
      "chunk-BYOUWXMH.js",
      "chunk-TKMUIM6Y.js",
      "chunk-QEJW36V7.js",
      "chunk-6T6PUUJ5.js",
      "chunk-ATYHHWXS.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/eventos"
  },
  {
    "renderMode": 2,
    "preload": [
      "chunk-BXZNKSDQ.js",
      "chunk-BYOUWXMH.js",
      "chunk-TKMUIM6Y.js",
      "chunk-QEJW36V7.js",
      "chunk-6T6PUUJ5.js",
      "chunk-ATYHHWXS.js",
      "chunk-46FKC62F.js"
    ],
    "route": "/sedes"
  },
  {
    "renderMode": 2,
    "redirectTo": "/",
    "route": "/**"
  }
],
  entryPointToBrowserMapping: undefined,
  assets: {
    'index.csr.html': {size: 1019, hash: 'ac7ce267a3531f2b6b8e93c9510858037cb9222b9b205a99dd1305504ea56a03', text: () => import('./assets-chunks/index_csr_html.mjs').then(m => m.default)},
    'index.server.html': {size: 1559, hash: 'f5308aca7c0f09647dead5c1c7f7045b2397835dccf664174de6cee887c3330d', text: () => import('./assets-chunks/index_server_html.mjs').then(m => m.default)},
    'index.html': {size: 240, hash: 'db096474d521163c4f5fb7d700305222bcea1012b38583442ad232da75e59192', text: () => import('./assets-chunks/index_html.mjs').then(m => m.default)},
    'eventos/index.html': {size: 240, hash: 'db096474d521163c4f5fb7d700305222bcea1012b38583442ad232da75e59192', text: () => import('./assets-chunks/eventos_index_html.mjs').then(m => m.default)},
    'login/index.html': {size: 84108, hash: '93af61b7d64db59832590dc962f3a46fef6a1d09973d71efcd48e57089f7c687', text: () => import('./assets-chunks/login_index_html.mjs').then(m => m.default)},
    'lideres/index.html': {size: 240, hash: 'db096474d521163c4f5fb7d700305222bcea1012b38583442ad232da75e59192', text: () => import('./assets-chunks/lideres_index_html.mjs').then(m => m.default)},
    'dashboard/index.html': {size: 240, hash: 'db096474d521163c4f5fb7d700305222bcea1012b38583442ad232da75e59192', text: () => import('./assets-chunks/dashboard_index_html.mjs').then(m => m.default)},
    'sedes/index.html': {size: 240, hash: 'db096474d521163c4f5fb7d700305222bcea1012b38583442ad232da75e59192', text: () => import('./assets-chunks/sedes_index_html.mjs').then(m => m.default)},
    'miembros/index.html': {size: 240, hash: 'db096474d521163c4f5fb7d700305222bcea1012b38583442ad232da75e59192', text: () => import('./assets-chunks/miembros_index_html.mjs').then(m => m.default)},
    'etapas/index.html': {size: 240, hash: 'db096474d521163c4f5fb7d700305222bcea1012b38583442ad232da75e59192', text: () => import('./assets-chunks/etapas_index_html.mjs').then(m => m.default)}
  },
};
