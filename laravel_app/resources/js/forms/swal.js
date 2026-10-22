/**
 * Carga diferida de SweetAlert2 (spec 008, ligereza): solo se descarga cuando hace falta mostrar un
 * aviso o un diálogo, no en cada pantalla.
 */
let promesa = null;

export function swal() {
    promesa ??= Promise.all([import('sweetalert2'), import('sweetalert2/dist/sweetalert2.min.css')]).then(([modulo]) => modulo.default);

    return promesa;
}
