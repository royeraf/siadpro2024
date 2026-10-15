import {
    FileText, FileSpreadsheet, FileImage, Presentation, File,
} from 'lucide-vue-next';

/**
 * Extensiones que se consideran imagen (se previsualizan con su miniatura real
 * en vez de un icono).
 */
export const IMAGENES = ['png', 'jpg', 'jpeg'];

/**
 * Icono (componente lucide) y clase de color por extensión. Mapa compartido por
 * `EvidenciaStack.vue` y el navegador de archivos de `FileViewerModal.vue` para
 * que ambos muestren el mismo aspecto.
 */
const ICONOS = {
    pdf: { cmp: FileText, clase: 'text-rose-600 bg-rose-50 border-rose-200' },
    doc: { cmp: FileText, clase: 'text-blue-600 bg-blue-50 border-blue-200' },
    docx: { cmp: FileText, clase: 'text-blue-600 bg-blue-50 border-blue-200' },
    xls: { cmp: FileSpreadsheet, clase: 'text-emerald-600 bg-emerald-50 border-emerald-200' },
    xlsx: { cmp: FileSpreadsheet, clase: 'text-emerald-600 bg-emerald-50 border-emerald-200' },
    xlm: { cmp: FileSpreadsheet, clase: 'text-emerald-600 bg-emerald-50 border-emerald-200' },
    xlsm: { cmp: FileSpreadsheet, clase: 'text-emerald-600 bg-emerald-50 border-emerald-200' },
    ppt: { cmp: Presentation, clase: 'text-orange-600 bg-orange-50 border-orange-200' },
    pptx: { cmp: Presentation, clase: 'text-orange-600 bg-orange-50 border-orange-200' },
    pptm: { cmp: Presentation, clase: 'text-orange-600 bg-orange-50 border-orange-200' },
    png: { cmp: FileImage, clase: 'text-violet-600 bg-violet-50 border-violet-200' },
    jpg: { cmp: FileImage, clase: 'text-violet-600 bg-violet-50 border-violet-200' },
    jpeg: { cmp: FileImage, clase: 'text-violet-600 bg-violet-50 border-violet-200' },
};

const ICONO_DEFECTO = { cmp: File, clase: 'text-slate-600 bg-slate-50 border-slate-200' };

/** Extensión en minúsculas de un nombre o URL (sin query string). */
export function extensionDe(nombre) {
    const limpio = String(nombre || '').split(/[?#]/)[0];
    return (limpio.split('.').pop() || '').toLowerCase();
}

/** ¿El archivo es una imagen previsualizable como miniatura? */
export function esImagenArchivo(nombre) {
    return IMAGENES.includes(extensionDe(nombre));
}

/** Devuelve el componente de icono y su clase de color según la extensión. */
export function iconoArchivo(nombre) {
    return ICONOS[extensionDe(nombre)] || ICONO_DEFECTO;
}
