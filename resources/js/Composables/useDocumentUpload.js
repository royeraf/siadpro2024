import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { formatBytes, formatBytesCompact } from '@/Utils/formatBytes';

const DEFAULT_MAX_KB = 5120;

/** Detecta si el mensaje del servidor habla de un archivo demasiado pesado. */
function isSizeError(message) {
    return /no debe pesar más de|supera el|límite del servidor|excede/i.test(message || '');
}

/**
 * Estado compartido por los modals que adjuntan un documento.
 *
 * - Rechaza en el cliente lo que el servidor ya no va a aceptar (peso, formato).
 * - Cuando el PDF pesa demasiado, ofrece los compresores online configurados en
 *   `config/siadpro.php`.
 * - Expone los hooks `onStart` / `onProgress` / `onFinish` de Inertia para
 *   alimentar el progreso circular mientras se sube el archivo.
 *
 * @param {object}  options
 * @param {string}  options.accept       Valor del atributo `accept` del input.
 * @param {string[]} [options.extensions] Extensiones admitidas; si se omite no
 *                                       se filtra el formato en el cliente.
 * @param {string}  [options.hint]       Texto de ayuda bajo el icono.
 */
export function useDocumentUpload(options = {}) {
    const page = usePage();

    const accept = options.accept ?? '';
    const extensions = options.extensions ?? null;

    /** Archivo aceptado y listo para enviarse en el FormData. */
    const file = ref(null);
    const fileError = ref('');
    const uploading = ref(false);
    const progress = ref(0);

    /** Extensión del último archivo evaluado, aunque haya sido rechazado. */
    const attemptedExt = ref('');
    const oversized = ref(false);

    const configuredBytes = computed(() => {
        const kb = Number(page.props.uploadMaxKb);
        return Number.isFinite(kb) && kb > 0 ? kb * 1024 : DEFAULT_MAX_KB * 1024;
    });

    const maxBytes = computed(() => {
        const shared = Number(page.props.uploadMaxBytes);
        return Number.isFinite(shared) && shared > 0 ? shared : configuredBytes.value;
    });

    /** "5MB", o el tope real si el PHP.ini del servidor es más restrictivo. */
    const maxLabel = computed(() => formatBytesCompact(maxBytes.value));

    const limitIsServerSide = computed(() => maxBytes.value < configuredBytes.value);

    /** Los formatos admitidos, siempre con el tamaño máximo vigente. */
    const hint = computed(() => {
        const limit = limitIsServerSide.value
            ? `Máx. ${maxLabel.value} (límite del servidor)`
            : `Máx. ${maxLabel.value}`;

        return options.hint ? `${options.hint} · ${limit}` : limit;
    });

    const compressors = computed(() => {
        const list = page.props.pdfCompressors;
        return Array.isArray(list) ? list : [];
    });

    /** Solo tiene sentido sugerir compresión cuando lo rechazado es un PDF. */
    const showCompressors = computed(() => oversized.value && attemptedExt.value === 'pdf');

    function extensionOf(name) {
        return (name?.split('.').pop() || '').toLowerCase();
    }

    function setFile(candidate) {
        file.value = null;
        fileError.value = '';
        oversized.value = false;

        if (!candidate) return;

        const ext = extensionOf(candidate.name);
        attemptedExt.value = ext;

        if (extensions?.length && !extensions.includes(ext)) {
            fileError.value = `Formato no permitido (.${ext}). Admitidos: ${extensions.join(', ')}.`;
            return;
        }

        if (candidate.size > maxBytes.value) {
            oversized.value = true;
            fileError.value = limitIsServerSide.value
                ? `El archivo pesa ${formatBytes(candidate.size)} y supera el límite del servidor (${maxLabel.value}).`
                : `El archivo pesa ${formatBytes(candidate.size)} y el máximo permitido es ${maxLabel.value}.`;
            return;
        }

        file.value = candidate;
    }

    function removeFile() {
        file.value = null;
        fileError.value = '';
        oversized.value = false;
        attemptedExt.value = '';
    }

    /** Vuelve a dejar el input en blanco para poder resubir el mismo archivo. */
    function reset() {
        removeFile();
        uploading.value = false;
        progress.value = 0;
    }

    /** Error devuelto por el backend: mantiene el aviso si habla de peso. */
    function setServerError(message) {
        fileError.value = message;
        oversized.value = isSizeError(message);

        if (!attemptedExt.value && file.value) {
            attemptedExt.value = extensionOf(file.value.name);
        }
    }

    function startUpload() {
        uploading.value = true;
        progress.value = 0;
    }

    function trackProgress(event) {
        if (!uploading.value) return;

        const percentage = Number(event?.percentage ?? 0);

        progress.value = Math.min(100, Math.max(0, percentage));
    }

    function finishUpload() {
        uploading.value = false;
        progress.value = 0;
    }

    return {
        accept,
        hint,
        extensions,
        compressors,
        showCompressors,
        file,
        fileError,
        maxBytes,
        maxLabel,
        uploading,
        progress,
        setFile,
        setServerError,
        removeFile,
        reset,
        startUpload,
        trackProgress,
        finishUpload,
    };
}
