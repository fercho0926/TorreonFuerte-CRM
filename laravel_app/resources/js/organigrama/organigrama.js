/**
 * Organigrama (spec 011): el árbol usa <details> y funciona sin JavaScript; aquí solo se activan
 * los botones «Expandir todo» y «Colapsar todo», que están ocultos en el HTML.
 */
export function iniciarOrganigrama(raiz = document) {
    raiz.querySelectorAll('[data-organigrama]').forEach((organigrama) => {
        const acciones = organigrama.querySelector('.organigrama-acciones');
        if (acciones) acciones.hidden = false;

        organigrama.addEventListener('click', (evento) => {
            const boton = evento.target.closest('[data-organigrama-accion]');
            if (!boton) return;

            const abrir = boton.dataset.organigramaAccion === 'expandir';
            organigrama.querySelectorAll('details').forEach((d) => { d.open = abrir; });
        });
    });
}
