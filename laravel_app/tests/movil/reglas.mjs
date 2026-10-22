/**
 * Medición de las reglas de adaptación a celular dentro de la página (specs/008-experiencia-movil/contracts/prueba-movil.md).
 * `medirPagina` se ejecuta con `page.evaluate`; devuelve las infracciones por regla, cada una con un selector legible.
 */
export function medirPagina() {
    const MIN_TACTIL = 44;
    const MIN_LETRA = 16;
    const QUIEBRE = 768;
    const raiz = document.documentElement;
    const ancho = raiz.clientWidth;

    const nombre = (el) => {
        const partes = [];
        let n = el;
        for (let i = 0; n && n.nodeType === 1 && i < 4; i++, n = n.parentElement) {
            let t = n.tagName.toLowerCase();
            if (n.id) t += `#${n.id}`;
            else if (n.classList.length) t += `.${[...n.classList].slice(0, 2).join('.')}`;
            partes.unshift(t);
            if (n.id) break;
        }
        return partes.join(' > ');
    };

    const rectDe = (el) => el.getBoundingClientRect();
    const visible = (el) => {
        const r = rectDe(el);
        if (r.width === 0 || r.height === 0) return false;
        const e = getComputedStyle(el);
        if (e.visibility === 'hidden' || e.display === 'none' || Number(e.opacity) === 0) return false;
        // Fuera de la pantalla (menú lateral plegado, elementos accesibles ocultos).
        if (r.right <= 0 || r.left >= ancho || r.bottom <= 0) return false;
        if (el.closest('.visually-hidden, .sr-only, .select2-hidden-accessible, [aria-hidden="true"][tabindex="-1"]')) return false;
        if (el.closest('.app-sidebar') && document.body.classList.contains('sidebar-collapse')) return false;
        return true;
    };
    const dentroDeMenuCerrado = (el) => {
        const lateral = el.closest('.app-sidebar');
        return !!lateral && rectDe(lateral).right <= 0;
    };

    // 1. Desplazamiento horizontal de la página.
    const desborde = { hay: raiz.scrollWidth > ancho + 1, ancho: raiz.scrollWidth, culpables: [] };
    if (desborde.hay) {
        document.querySelectorAll('body *').forEach((el) => {
            if (desborde.culpables.length >= 5) return;
            const r = rectDe(el);
            if (r.width > 0 && r.right > ancho + 1 && !el.closest('.table-responsive, .select2-dropdown, .app-sidebar, .dt-container .dt-scroll')) {
                if (getComputedStyle(el).position !== 'fixed') desborde.culpables.push(`${nombre(el)} (${Math.round(r.right)})`);
            }
        });
    }

    // 2. Controles tocables de al menos 44 px.
    const controles = [];
    const candidatos = document.querySelectorAll(
        'button, input:not([type=hidden]), select, textarea, [role=button], [role=switch], .page-link, .dropdown-item, .nav-link, a.btn, a[href], .select2-selection'
    );
    candidatos.forEach((el) => {
        if (!visible(el) || dentroDeMenuCerrado(el)) return;
        if (el.matches('a[href]:not(.btn):not(.nav-link):not(.page-link):not(.dropdown-item)') && getComputedStyle(el).display === 'inline') return; // enlace dentro de un texto
        let r = rectDe(el);
        if (el.matches('input[type=checkbox], input[type=radio]')) {
            const etiqueta = el.closest('label') || (el.id ? document.querySelector(`label[for="${CSS.escape(el.id)}"]`) : null);
            const re = etiqueta ? rectDe(etiqueta) : r;
            r = { width: Math.max(r.width, re.width), height: Math.max(r.height, re.height) };
        }
        if (r.width < MIN_TACTIL - 0.5 || r.height < MIN_TACTIL - 0.5) {
            controles.push(`${nombre(el)} ${Math.round(r.width)}x${Math.round(r.height)}`);
        }
    });

    // 3. Letra de campos de al menos 16 px.
    const letras = [];
    document.querySelectorAll('input:not([type=hidden]):not([type=checkbox]):not([type=radio]):not([type=range]):not([type=file]), select, textarea').forEach((el) => {
        if (!visible(el)) return;
        const px = parseFloat(getComputedStyle(el).fontSize);
        if (px < MIN_LETRA - 0.01) letras.push(`${nombre(el)} ${px}px`);
    });
    document.querySelectorAll('.select2-selection__rendered').forEach((el) => {
        if (!visible(el)) return;
        const px = parseFloat(getComputedStyle(el).fontSize);
        if (px < MIN_LETRA - 0.01) letras.push(`${nombre(el)} ${px}px`);
    });

    // 4. Tablas sin formato adaptado por debajo de 768 px.
    const tablas = [];
    if (ancho < QUIEBRE) {
        document.querySelectorAll('table').forEach((t) => {
            if (!visible(t)) return;
            const apilable = t.classList.contains('tabla-apilable') || t.hasAttribute('data-grilla');
            const fila = t.querySelector('tbody tr:not(.dtr-hidden)');
            const apilada = fila ? getComputedStyle(fila).display !== 'table-row' : true;
            if (!apilable || !apilada) tablas.push(`${nombre(t)} ${apilable ? '(sin apilar)' : '(sin clase tabla-apilable)'}`);
        });
    }

    // 5. Texto cortado sin puntos suspensivos.
    const cortados = [];
    document.querySelectorAll('body *').forEach((el) => {
        if (cortados.length >= 5 || !el.childNodes.length) return;
        const e = getComputedStyle(el);
        if (!['hidden', 'clip'].includes(e.overflowX)) return;
        if (e.textOverflow === 'ellipsis' || !visible(el)) return;
        if (el.closest('.table-responsive, .select2-container, .app-sidebar, .dt-container, .sidebar-wrapper, .dropdown-menu, .fm-avisos, .swal2-container')) return;
        const tieneTexto = [...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim());
        if (tieneTexto && el.scrollWidth > el.clientWidth + 2) cortados.push(nombre(el));
    });

    return { desborde, controles, letras, tablas, cortados };
}
