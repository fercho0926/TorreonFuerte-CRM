/**
 * Genera los iconos de la aplicación instalable a partir de resources/iconos/*.svg.
 * Uso: npm run iconos  →  public/iconos/*.png y public/favicon.ico
 */
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';
import sharp from 'sharp';

const raiz = resolve(import.meta.dirname, '..');
const salida = resolve(raiz, 'public', 'iconos');
mkdirSync(salida, { recursive: true });

const marca = readFileSync(resolve(raiz, 'resources', 'iconos', 'marca.svg'));
const maskable = readFileSync(resolve(raiz, 'resources', 'iconos', 'marca-maskable.svg'));

const png = (svg, lado) => sharp(svg, { density: 384 }).resize(lado, lado).png({ compressionLevel: 9 }).toBuffer();

const archivos = {
    'icono-192.png': await png(marca, 192),
    'icono-512.png': await png(marca, 512),
    'icono-maskable-512.png': await png(maskable, 512),
    'apple-touch-180.png': await png(maskable, 180), // iPhone redondea las esquinas: se entrega a sangre
    'favicon-32.png': await png(marca, 32),
};
for (const [nombre, datos] of Object.entries(archivos)) writeFileSync(resolve(salida, nombre), datos);

// favicon.ico con un PNG de 32 px incrustado (formato ICO con imágenes PNG).
const imagen = archivos['favicon-32.png'];
const cabecera = Buffer.alloc(22);
cabecera.writeUInt16LE(0, 0); // reservado
cabecera.writeUInt16LE(1, 2); // tipo: icono
cabecera.writeUInt16LE(1, 4); // cantidad de imágenes
cabecera.writeUInt8(32, 6); // ancho
cabecera.writeUInt8(32, 7); // alto
cabecera.writeUInt16LE(1, 12); // planos
cabecera.writeUInt16LE(32, 14); // bits por píxel
cabecera.writeUInt32LE(imagen.length, 14 + 2); // tamaño de la imagen
cabecera.writeUInt32LE(22, 18); // posición de la imagen
writeFileSync(resolve(raiz, 'public', 'favicon.ico'), Buffer.concat([cabecera, imagen]));

console.log(`Iconos generados en ${salida}`);
