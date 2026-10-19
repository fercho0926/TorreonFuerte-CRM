import './bootstrap';
import '../css/app.css';
import 'admin-lte/dist/js/adminlte.esm.js';

import $ from 'jquery';
import select2 from 'select2/dist/js/select2.full.js';

// Select2 es un plugin de jQuery y necesita jQuery global (Bootstrap 5 y AdminLTE 4 no lo usan).
window.$ = window.jQuery = $;
select2(window, $);

// Recuerda si el usuario plegó el menú lateral.
import { iniciarMenuRecordado } from './layout/menu-recordado.js';

iniciarMenuRecordado();

// Formularios modernos: validación, selectores, máscaras, calendario, envío y diálogos.
import './forms/index.js';
