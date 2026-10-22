/**
 * Avisos de éxito discretos que se cierran solos. Los mensajes de error permanecen visibles.
 * Sin JavaScript, los mensajes siguen apareciendo como alertas normales.
 */
import { swal } from './swal.js';

export const DURACION_AVISO_MS = 5000;

export function iniciarAvisos() {
    document.querySelectorAll('[data-avisos] [data-aviso="exito"]').forEach((aviso) => {
        const mensaje = aviso.textContent.trim();
        if (!mensaje) return;

        aviso.hidden = true;
        swal().then((Swal) => Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: mensaje,
            showConfirmButton: false,
            timer: DURACION_AVISO_MS,
            timerProgressBar: true,
            customClass: { popup: 'fm-aviso-exito' },
        }));
    });
}

/** Aviso breve (toast) con un texto cualquiera, por ejemplo «Mensaje copiado». */
export function avisoBreve(mensaje) {
    swal().then((Swal) => Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: mensaje,
        showConfirmButton: false,
        timer: 2500,
        customClass: { popup: 'fm-aviso-exito' },
    }));
}
