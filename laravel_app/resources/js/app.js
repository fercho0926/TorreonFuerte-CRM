import './bootstrap';
import '../css/app.css';
import 'admin-lte/dist/js/adminlte.esm.js';

// Recuerda si el usuario plegó el menú lateral.
import { iniciarMenuRecordado } from './layout/menu-recordado.js';

iniciarMenuRecordado();

// Formularios modernos: validación, selectores, máscaras, calendario, envío y diálogos.
import './forms/index.js';

// Grillas con DataTables (ordenar, filtrar por columna, paginar y exportar): se descargan solo si la pantalla tiene una.
if (document.querySelector('table[data-grilla]')) {
    import('./grilla/grilla.js').then(({ iniciarGrillas }) => iniciarGrillas());
}

// Gráficas del tablero (ApexCharts): se descargan solo si la pantalla tiene alguna.
if (document.querySelector('[data-grafica]')) {
    import('./tablero/graficas.js').then(({ iniciarGraficas }) => iniciarGraficas());
}

// Organigrama: botones «Expandir todo / Colapsar todo» (el árbol ya funciona sin JavaScript).
if (document.querySelector('[data-organigrama]')) {
    import('./organigrama/organigrama.js').then(({ iniciarOrganigrama }) => iniciarOrganigrama());
}

// Matriz de permisos de los roles: atajos «Todo en lectura / Todo sin acceso» (los radios ya funcionan sin JavaScript).
if (document.querySelector('[data-matriz-permisos]')) {
    import('./roles/matriz.js').then(({ iniciarMatriz }) => iniciarMatriz());
}

// Aplicación instalable: registra el service worker (solo HTTPS o localhost).
import { registrarServiceWorker } from './pwa/registrar.js';

registrarServiceWorker();
