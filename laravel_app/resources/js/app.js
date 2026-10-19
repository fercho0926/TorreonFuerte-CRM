import './bootstrap';
import '../css/app.css';
import 'admin-lte/dist/js/adminlte.esm.js';

// jQuery y Select2 deben cargarse antes de los formularios.
import './jquery-global.js';

// Recuerda si el usuario plegó el menú lateral.
import { iniciarMenuRecordado } from './layout/menu-recordado.js';

iniciarMenuRecordado();

// Formularios modernos: validación, selectores, máscaras, calendario, envío y diálogos.
import './forms/index.js';

// Grillas con DataTables (ordenar, filtrar por columna, paginar y exportar).
import { iniciarGrillas } from './grilla/grilla.js';

iniciarGrillas();
