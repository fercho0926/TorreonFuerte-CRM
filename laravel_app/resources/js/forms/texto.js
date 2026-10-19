/**
 * Utilidades puras de texto para los formularios (sin acceso al DOM, fáciles de probar).
 */

/** Minúsculas y sin tildes: "Ángela Pérez" → "angela perez". */
export function normalizar(texto) {
    return String(texto ?? '')
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .trim();
}

/** Deja solo dígitos. */
export function soloDigitos(texto) {
    return String(texto ?? '').replace(/\D+/g, '');
}

/** Deja solo letras y dígitos (documentos como pasaportes). */
export function soloAlfanumerico(texto) {
    return String(texto ?? '').replace(/[^0-9A-Za-z]+/g, '').toUpperCase();
}

/** Agrupa un celular de 10 dígitos como "310 555 9999"; si es más corto agrupa lo que haya. */
export function formatearCelular(texto) {
    const d = soloDigitos(texto).slice(0, 15);
    if (d.length <= 3) return d;
    if (d.length <= 6) return `${d.slice(0, 3)} ${d.slice(3)}`;
    return `${d.slice(0, 3)} ${d.slice(3, 6)} ${d.slice(6)}`;
}

/**
 * Formato visible del monto mientras se escribe: el punto separa miles y la coma los decimales
 * ("50000,5" → "50.000,5"). Los puntos escritos se interpretan siempre como separador de miles.
 */
export function formatearMiles(valor) {
    const partes = String(valor ?? '').replace(/[^\d,]/g, '').split(',');
    let entero = partes[0].replace(/^0+(?=\d)/, '');
    const hayDecimales = partes.length > 1;

    if (entero === '' && !hayDecimales) return '';
    if (entero === '') entero = '0';

    const miles = entero.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return hayDecimales ? `${miles},${partes.slice(1).join('').slice(0, 2)}` : miles;
}

/** Convierte lo que ve el usuario ("50.000,50") en el número plano que se envía ("50000.50"). */
export function montoPlano(texto) {
    const t = String(texto ?? '').replace(/[^\d,.]/g, '');
    if (t === '') return '';
    const plano = t.replace(/\./g, '').replace(',', '.');

    return plano.startsWith('.') ? `0${plano}` : plano;
}

/** Convierte un valor plano que viene del servidor ("50000.00") al formato visible ("50.000"). */
export function visibleDesdePlano(plano) {
    const texto = String(plano ?? '').trim();
    if (!/^\d+(\.\d+)?$/.test(texto)) return texto;

    const [entero, decimales = ''] = texto.split('.');
    const util = decimales.replace(/0+$/, '');

    return formatearMiles(util ? `${entero},${util}` : entero);
}

function aFecha(fecha) {
    const y = fecha.getFullYear();
    const m = String(fecha.getMonth() + 1).padStart(2, '0');
    const d = String(fecha.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

/** Hoy en formato Y-m-d (hora local). */
export function hoy(base = new Date()) {
    return aFecha(base);
}

/** Ayer en formato Y-m-d (hora local). */
export function ayer(base = new Date()) {
    const d = new Date(base);
    d.setDate(d.getDate() - 1);
    return aFecha(d);
}

/** Escapa un identificador para usarlo en un selector CSS (con respaldo para entornos sin CSS.escape). */
export function escaparSelector(valor) {
    const texto = String(valor ?? '');
    if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') return CSS.escape(texto);

    return texto.replace(/[^\w-]/g, (c) => '\\' + c);
}
