// Select2 es un plugin de jQuery y necesita jQuery global (Bootstrap 5 y AdminLTE 4 no lo usan).
// Va en su propio módulo para que se evalúe ANTES que los formularios: los import se ejecutan
// en orden, mientras que el código suelto de app.js correría después de ellos.
import $ from 'jquery';
import select2 from 'select2/dist/js/select2.full.js';

window.$ = window.jQuery = $;
select2(window, $);
