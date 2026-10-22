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

// Aplicación instalable: registra el service worker (solo HTTPS o localhost).
import { registrarServiceWorker } from './pwa/registrar.js';

registrarServiceWorker();
