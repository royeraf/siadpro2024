/**
 * Iconos de documentos/archivos según su extensión.
 *
 * Los SVG viven en `public/img/archivos/` y se sirven desde la raíz del sitio
 * (`/img/archivos/…`), igual que el resto de imágenes estáticas del proyecto.
 *
 * Estilo "Microsoft Office" actual (Word/Excel/PowerPoint 2025) más un icono de
 * PDF, uno de imagen y un tipo **genérico** para cualquier otra extensión.
 *
 * Origen: la colección *Microsoft Office* de Icons8
 * (https://iconos8.es/icons/set/microsoft-office), que sirvió de referencia de
 * estilo. Como Icons8 bloquea la descarga de SVG sin cuenta, los SVG se toman de
 * fuentes libres:
 *   - Word / Excel / PowerPoint: "Microsoft Office … (2025–present)" (Wikimedia Commons).
 *   - PDF: "Adobe Acrobat DC logo 2020" (Wikimedia Commons).
 *   - Imagen: set "Paomedia small-n-flat".
 *   - Genérico: propio, en el mismo estilo que el resto.
 */

/**
 * Extensiones que se consideran imagen (se previsualizan con su miniatura real
 * en vez de un icono).
 */
export const IMAGENES = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'];

const BASE = '/img/archivos';

/**
 * Ruta del SVG por extensión. Varias extensiones comparten el mismo icono (por
 * ejemplo `doc` y `docx` son ambos de Word).
 */
const ICONOS = {
    // PDF
    pdf: `${BASE}/pdf.svg`,

    // Word
    doc: `${BASE}/word.svg`,
    docx: `${BASE}/word.svg`,
    rtf: `${BASE}/word.svg`,

    // Excel
    xls: `${BASE}/excel.svg`,
    xlsx: `${BASE}/excel.svg`,
    xlm: `${BASE}/excel.svg`,
    xlsm: `${BASE}/excel.svg`,
    xlsb: `${BASE}/excel.svg`,
    csv: `${BASE}/excel.svg`,

    // PowerPoint
    ppt: `${BASE}/powerpoint.svg`,
    pptx: `${BASE}/powerpoint.svg`,
    pptm: `${BASE}/powerpoint.svg`,

    // Imagen
    png: `${BASE}/image.svg`,
    jpg: `${BASE}/image.svg`,
    jpeg: `${BASE}/image.svg`,
    gif: `${BASE}/image.svg`,
    webp: `${BASE}/image.svg`,
    bmp: `${BASE}/image.svg`,
    svg: `${BASE}/image.svg`,
};

/**
 * Tipo **genérico**: icono de "archivo" para cualquier extensión que no esté
 * contemplada en `ICONOS` (por ejemplo `txt`, `zip`, `odt`, `ods`, `mp3`, `mp4`,
 * `rar`… o archivos sin extensión). Evita que un archivo desconocido se muestre
 * sin icono.
 */
export const ICONO_GENERICO = `${BASE}/file.svg`;

/** Extensión en minúsculas de un nombre o URL (sin query string). */
export function extensionDe(nombre) {
    const limpio = String(nombre || '').split(/[?#]/)[0];
    return (limpio.split('.').pop() || '').toLowerCase();
}

/** ¿El archivo es una imagen previsualizable como miniatura? */
export function esImagenArchivo(nombre) {
    return IMAGENES.includes(extensionDe(nombre));
}

/**
 * Devuelve la ruta del icono (SVG) que corresponde a la extensión del archivo.
 * Si la extensión no está contemplada, devuelve el tipo genérico.
 */
export function iconoArchivo(nombre) {
    return ICONOS[extensionDe(nombre)] || ICONO_GENERICO;
}

/** ¿La extensión del archivo está contemplada por un icono específico? */
export function tieneIconoPropio(nombre) {
    return extensionDe(nombre) in ICONOS;
}
