import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { formatBytes, formatBytesCompact } from '@/Utils/formatBytes';

const DEFAULT_MAX_KB = 5120;

/**
 * Variante multi-archivo de `useDocumentUpload`: en vez de un único `file`,
 * mantiene una lista de candidatos con su validación individual. Se usa en la
 * subida de evidencias por día (uno o varios archivos por vez).
 *
 * Reutiliza los mismos topes compartidos por Inertia (`uploadMaxKb` /
 * `uploadMaxBytes`) y el mismo mapa de compresores de PDF.
 *
 * @param {object}   options
 * @param {string}   [options.accept]      Valor del atributo `accept`.
 * @param {string[]} [options.extensions]  Extensiones admitidas.
 * @param {string}   [options.hint]        Texto de ayuda.
 * @param {number}   [options.max]         Máximo de archivos por subida.
 */
export function useMultiDocumentUpload(options = {}) {
    const page = usePage();

    const accept = options.accept ?? '';
    const extensions = options.extensions ?? null;
    const max = Number(options.max ?? 10);

    /** Lista de candidatos: { file, error, oversized }. */
    const items = ref([]);
    const globalError = ref('');
    const uploading = ref(false);
    const progress = ref(0);

    const configuredBytes = computed(() => {
        const kb = Number(page.props.uploadMaxKb);
        return Number.isFinite(kb) && kb > 0 ? kb * 1024 : DEFAULT_MAX_KB * 1024;
    });

    const maxBytes = computed(() => {
        const shared = Number(page.props.uploadMaxBytes);
        return Number.isFinite(shared) && shared > 0 ? shared : configuredBytes.value;
    });

    const maxLabel = computed(() => formatBytesCompact(maxBytes.value));

    const limitIsServerSide = computed(() => maxBytes.value < configuredBytes.value);

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

    /** Solo se sugieren compresores si algún rechazo por peso fue de un PDF. */
    const showCompressors = computed(() => items.value.some((it) => it.oversized && it.ext === 'pdf'));

    function extensionOf(name) {
        return (name?.split('.').pop() || '').toLowerCase();
    }

    function validate(candidate) {
        const ext = extensionOf(candidate.name);
        const item = { file: candidate, ext, error: '', oversized: false };

        if (extensions?.length && !extensions.includes(ext)) {
            item.error = `Formato no permitido (.${ext}). Admitidos: ${extensions.join(', ')}.`;
            return item;
        }

        if (candidate.size > maxBytes.value) {
            item.oversized = true;
            item.error = limitIsServerSide.value
                ? `Pesa ${formatBytes(candidate.size)} y supera el límite del servidor (${maxLabel.value}).`
                : `Pesa ${formatBytes(candidate.size)} y el máximo permitido es ${maxLabel.value}.`;
        }

        return item;
    }

    function addFiles(list) {
        globalError.value = '';

        for (const candidate of Array.from(list || [])) {
            if (items.value.length >= max) {
                globalError.value = `Máximo ${max} archivos por vez.`;
                break;
            }
            items.value.push(validate(candidate));
        }
    }

    function removeAt(index) {
        items.value.splice(index, 1);
        globalError.value = '';
    }

    function clear() {
        items.value = [];
        globalError.value = '';
        uploading.value = false;
        progress.value = 0;
    }

    const validFiles = computed(() => items.value.filter((it) => !it.error).map((it) => it.file));
    const hasErrors = computed(() => items.value.some((it) => it.error));
    const canSubmit = computed(() => validFiles.value.length > 0 && !hasErrors.value && !uploading.value);

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
        max,
        maxBytes,
        maxLabel,
        compressors,
        showCompressors,
        items,
        globalError,
        uploading,
        progress,
        addFiles,
        removeAt,
        clear,
        validFiles,
        hasErrors,
        canSubmit,
        startUpload,
        trackProgress,
        finishUpload,
    };
}
