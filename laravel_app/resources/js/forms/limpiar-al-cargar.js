/**
 * Campos que SIEMPRE deben empezar en blanco (`data-limpiar-al-cargar`), como el monto de un abono
 * nuevo: algunos navegadores restauran el último valor escrito en un campo con el mismo nombre al
 * volver a cargar la página (ignoran `autocomplete="off"` para esa restauración), aunque el servidor
 * ya mande el campo vacío. `pageshow` cubre tanto la carga normal como la restauración desde la
 * memoria de ida y vuelta (bfcache), donde `DOMContentLoaded` no se dispara.
 */
export function iniciarLimpiarAlCargar() {
    const limpiar = () => {
        document.querySelectorAll('[data-limpiar-al-cargar]').forEach((campo) => {
            if (!campo.classList.contains('is-invalid')) campo.value = '';
        });
    };

    limpiar();
    window.addEventListener('pageshow', limpiar);
}
