<script setup>
import { computed } from 'vue';
import { CalendarCheck } from 'lucide-vue-next';
import BaseModal from '@/Components/UI/BaseModal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    event: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

// Formato heredado del modal legacy (moment: 'DD/MM/YYYY HH:mm').
function formatFecha(value) {
    if (!value) return '';
    const fecha = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(fecha.getTime())) return String(value);
    const pad = (n) => String(n).padStart(2, '0');
    return `${pad(fecha.getDate())}/${pad(fecha.getMonth() + 1)}/${fecha.getFullYear()} ${pad(fecha.getHours())}:${pad(fecha.getMinutes())}`;
}

const titulo = computed(() => props.event?.title || '');
const evento = computed(() => props.event?.evento || '');
const docente = computed(() => props.event?.nomDocente || '');
const fechaInicial = computed(() => formatFecha(props.event?.start));
const fechaFinal = computed(() => formatFecha(props.event?.end));
</script>

<template>
    <BaseModal
        :show="show"
        title="Visualización de Evento"
        subtitle="Detalle del evento seleccionado"
        icon="CalendarCheck"
        color="pink"
        max-width="xl"
        :is-form="false"
        @close="emit('close')"
    >
        <div class="space-y-4">
            <div>
                <label for="detail-title" class="block text-xs font-bold text-slate-700 mb-1">
                    Título
                </label>
                <input
                    id="detail-title"
                    type="text"
                    :value="titulo"
                    readonly
                    class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 cursor-default"
                />
            </div>

            <div>
                <label for="detail-evento" class="block text-xs font-bold text-slate-700 mb-1">
                    Evento
                </label>
                <textarea
                    id="detail-evento"
                    rows="4"
                    :value="evento"
                    readonly
                    class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 cursor-default resize-none"
                ></textarea>
            </div>

            <div>
                <label for="detail-docente" class="block text-xs font-bold text-slate-700 mb-1">
                    Docente
                </label>
                <input
                    id="detail-docente"
                    type="text"
                    :value="docente"
                    readonly
                    class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 cursor-default"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="detail-start" class="block text-xs font-bold text-slate-700 mb-1">
                        Fecha Inicial
                    </label>
                    <input
                        id="detail-start"
                        type="text"
                        :value="fechaInicial"
                        readonly
                        class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 cursor-default"
                    />
                </div>

                <div>
                    <label for="detail-end" class="block text-xs font-bold text-slate-700 mb-1">
                        Fecha Final
                    </label>
                    <input
                        id="detail-end"
                        type="text"
                        :value="fechaFinal"
                        readonly
                        class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 cursor-default"
                    />
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center gap-2.5 max-sm:w-full">
                <button
                    type="button"
                    @click="emit('close')"
                    class="max-sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-2 text-xs sm:text-sm font-bold text-white bg-slate-700 hover:bg-slate-800 active:bg-slate-900 border border-slate-800 rounded-xl transition-colors shadow-2xs cursor-pointer"
                >
                    Cerrar
                </button>
            </div>
        </template>
    </BaseModal>
</template>
