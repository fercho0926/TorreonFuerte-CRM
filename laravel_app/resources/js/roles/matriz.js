/**
 * Matriz de permisos de un rol (spec 013): los radios por módulo funcionan sin JavaScript; aquí solo se
 * activan los atajos «Todo en lectura» y «Todo sin acceso», que están ocultos en el HTML.
 */
export function iniciarMatriz(raiz = document) {
    raiz.querySelectorAll('[data-matriz-permisos]').forEach((matriz) => {
        const atajos = matriz.querySelector('[data-matriz-atajos]');
        if (atajos) atajos.hidden = false;

        matriz.addEventListener('click', (evento) => {
            const boton = evento.target.closest('[data-matriz-todo]');
            if (!boton) return;

            const nivel = boton.dataset.matrizTodo;
            const nombres = new Set([...matriz.querySelectorAll('input[type="radio"][name^="permisos["]')].map((r) => r.name));

            nombres.forEach((nombre) => {
                const elegido = matriz.querySelector(`input[type="radio"][name="${nombre}"][value="${nivel}"]`);
                if (elegido) {
                    elegido.checked = true;
                    elegido.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    });
}
