// Select2 es un plugin de jQuery y necesita jQuery global (Bootstrap 5 y AdminLTE 4 no lo usan).
// Va en su propio módulo para que se evalúe ANTES que los formularios: los import se ejecutan
// en orden, mientras que el código suelto de app.js correría después de ellos.
// Estilos de Select2: viajan con este módulo, que se descarga solo en pantallas con selectores con buscador.
import 'select2/dist/css/select2.min.css';
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';
import $ from 'jquery';
import select2 from 'select2/dist/js/select2.full.js';

window.$ = window.jQuery = $;
select2(window, $);
