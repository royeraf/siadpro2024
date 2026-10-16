<script setup>
import { computed, ref, onBeforeUnmount } from 'vue';
import { AgendasService } from '@/Services/agenda';
import { esImagenArchivo } from '@/Utils/archivoIconos';
import ArchivoIcono from '@/Components/UI/ArchivoIcono.vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
    /** Archivos a mostrar (puede ser un subconjunto cuando `total` es mayor). */
    evidencias: {
        type: Array,
        default: () => [],
    },
    /**
     * Total real de archivos. Si no se envía se usa la cantidad recibida. Se
     * usa cuando el backend solo manda las primeras fichas (listados).
     */
    total: {
        type: [Number, String],
        default: null,
    },
    /** Fichas visibles antes de colapsar en "+N". */
    limite: {
        type: Number,
        default: 3,
    },
    /** Muestra el botón para eliminar cada archivo (solo el dueño). */
    canDelete: {
        type: Boolean,
        default: false,
    },
    /** Permite desplegar el resto; si es false el "+N" emite `view`. */
    expandable: {
        type: Boolean,
        default: true,
    },
    /** Muestra el texto "N archivo(s)" junto al stack. */
    mostrarContador: {
        type: Boolean,
        default: true,
    },
    /** Tamaño de las fichas: 'sm' (tablas) o 'md' (modales). */
    size: {
        type: String,
        default: 'md',
    },
});

const emit = defineEmits(['view', 'delete']);

const expandido = ref(false);

// Long-press para eliminar en pantallas táctiles (donde no hay hover y el botón
// "X" no aparece). Mantiene pulsado el archivo y sale la confirmación.
const LONG_PRESS_MS = 550;
let pressTimer = null;
let longPressFired = false;

function onChipDown(ev, e) {
    if (!props.canDelete) return;
    // Solo botón primario (dedo / click izquierdo).
    if (typeof e.button === 'number' && e.button !== 0) return;

    longPressFired = false;
    clearTimeout(pressTimer);
    pressTimer = setTimeout(() => {
        longPressFired = true;
        emit('delete', ev);
    }, LONG_PRESS_MS);
}

function onChipUp() {
    clearTimeout(pressTimer);
    pressTimer = null;
}

onBeforeUnmount(onChipUp);

const SIZES = {
    sm: { box: 'w-7 h-7', texto: 'text-[10px]', overlap: '-space-x-2' },
    md: { box: 'w-9 h-9', texto: 'text-[11px]', overlap: '-space-x-3' },
};

const sz = computed(() => SIZES[props.size] || SIZES.md);

const totalCount = computed(() => {
    if (props.total === null || props.total === undefined || props.total === '') {
        return props.evidencias.length;
    }
    return Number(props.total) || 0;
});

const restantes = computed(() => Math.max(0, totalCount.value - props.limite));

const visibles = computed(() => (
    expandido.value ? props.evidencias : props.evidencias.slice(0, props.limite)
));

function thumbUrl(enlace) {
    return AgendasService.visorUrl(enlace);
}

function abrir(ev) {
    // Si el click es la secuela de un long-press, no se abre el visor.
    if (longPressFired) {
        longPressFired = false;
        return;
    }
    emit('view', ev);
}

function verMas() {
    if (props.expandable) {
        expandido.value = true;
        return;
    }
    // En listados no se despliegan: se delega al contenedor (abrir el detalle).
    emit('view', null);
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
        <!-- Fichas solapadas (máx. `limite` + indicador "+N") -->
        <div
            :class="expandido
                ? 'flex flex-wrap items-center gap-1.5'
                : `flex items-center ${sz.overlap}`"
        >
            <div
                v-for="ev in visibles"
                :key="ev.id"
                class="group/badge relative hover:z-10"
            >
                <button
                    type="button"
                    @click="abrir(ev)"
                    @pointerdown="onChipDown(ev, $event)"
                    @pointerup="onChipUp"
                    @pointerleave="onChipUp"
                    @pointercancel="onChipUp"
                    @contextmenu.prevent
                    :title="ev.nombre_original || ev.enlace"
                    class="ev-chip relative block rounded-full overflow-hidden ring-2 ring-white shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:ring-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-500 cursor-pointer select-none"
                    :class="sz.box"
                >
                    <ArchivoIcono
                        :nombre="ev.enlace"
                        :url="thumbUrl(ev.enlace)"
                        :alt="ev.nombre_original || 'archivo'"
                        class="w-full h-full"
                        :class="esImagenArchivo(ev.enlace) ? '' : 'bg-white p-1'"
                    />
                </button>

                <!-- En táctil no hay hover: el botón queda inerte y se elimina con long-press -->
                <button
                    v-if="canDelete"
                    type="button"
                    @click.stop="emit('delete', ev)"
                    title="Eliminar archivo"
                    class="absolute -top-1 -right-1 z-20 w-4 h-4 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow ring-1 ring-white opacity-0 pointer-events-none group-hover/badge:opacity-100 group-hover/badge:pointer-events-auto focus:opacity-100 focus:pointer-events-auto transition-opacity cursor-pointer"
                >
                    <X class="w-2.5 h-2.5" />
                </button>
            </div>

            <button
                v-if="restantes > 0 && !expandido"
                type="button"
                @click="verMas"
                :title="`Ver los ${totalCount} archivos`"
                class="relative z-10 rounded-full ring-2 ring-white shadow-sm bg-slate-800 hover:bg-slate-700 text-white font-bold flex items-center justify-center cursor-pointer"
                :class="[sz.box, sz.texto]"
            >
                +{{ restantes }}
            </button>
        </div>

        <span
            v-if="mostrarContador"
            class="font-semibold text-slate-400"
            :class="sz.texto"
        >
            {{ totalCount }} archivo{{ totalCount === 1 ? '' : 's' }}
        </span>

        <button
            v-if="expandable && expandido && restantes > 0"
            type="button"
            @click="expandido = false"
            class="font-bold text-slate-500 hover:text-slate-700 underline cursor-pointer"
            :class="sz.texto"
        >
            Mostrar menos
        </button>
    </div>
</template>

<style scoped>
/* Evita el menú contextual / selección al mantener pulsado en móvil. */
.ev-chip {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
</style>
