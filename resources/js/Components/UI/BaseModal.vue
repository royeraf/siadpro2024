<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { 
    X, Loader2, Folder, Megaphone, Radio, 
    LayoutGrid, FileText, BookOpen, BookHeart, 
    NotebookPen, CalendarCheck, Landmark, Users, House 
} from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    subtitle: {
        type: String,
        default: '',
    },
    icon: {
        type: [String, Object],
        default: null,
    },
    color: {
        type: String,
        default: 'yellow',
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    isForm: {
        type: Boolean,
        default: true,
    },
    submitting: {
        type: Boolean,
        default: false,
    },
    submitText: {
        type: String,
        default: 'Guardar',
    },
    cancelText: {
        type: String,
        default: 'Cancelar',
    },
    submitDisabled: {
        type: Boolean,
        default: false,
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    hasFooter: {
        type: Boolean,
        default: true,
    },
    hasHeader: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close', 'submit']);

const icons = {
    Megaphone,
    Radio,
    LayoutGrid,
    FileText,
    BookOpen,
    BookHeart,
    NotebookPen,
    CalendarCheck,
    Folder,
    Landmark,
    Users,
    House,
};

function getIconComponent() {
    if (!props.icon) return null;
    if (typeof props.icon === 'object') return props.icon;
    const name = props.icon.charAt(0).toUpperCase() + props.icon.slice(1);
    return icons[name] || Folder;
}

const palette = {
    yellow: {
        badge: 'bg-amber-100 text-amber-600 border border-amber-200',
        submit: 'bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white shadow-xs focus:ring-amber-500',
    },
    blue: {
        badge: 'bg-blue-100 text-blue-600 border border-blue-200',
        submit: 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white shadow-xs focus:ring-blue-500',
    },
    emerald: {
        badge: 'bg-emerald-100 text-emerald-600 border border-emerald-200',
        submit: 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-xs focus:ring-emerald-500',
    },
    indigo: {
        badge: 'bg-indigo-100 text-indigo-600 border border-indigo-200',
        submit: 'bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white shadow-xs focus:ring-indigo-500',
    },
    rose: {
        badge: 'bg-rose-100 text-rose-600 border border-rose-200',
        submit: 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-xs focus:ring-rose-500',
    },
    red: {
        badge: 'bg-rose-100 text-rose-600 border border-rose-200',
        submit: 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-xs focus:ring-rose-500',
    },
    orange: {
        badge: 'bg-orange-100 text-orange-600 border border-orange-200',
        submit: 'bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white shadow-xs focus:ring-orange-500',
    },
    gray: {
        badge: 'bg-slate-100 text-slate-700 border border-slate-200',
        submit: 'bg-slate-800 hover:bg-slate-900 active:bg-slate-950 text-white shadow-xs focus:ring-slate-500',
    },
};

const currentPalette = computed(() => palette[props.color] || palette.yellow);

const maxWidthClass = computed(() => {
    const map = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl',
        '3xl': 'max-w-3xl',
        '4xl': 'max-w-4xl',
        full: 'max-w-full',
    };
    return map[props.maxWidth] || 'max-w-2xl';
});

function handleClose() {
    if (props.closeable && !props.submitting) {
        emit('close');
    }
}

function handleKeyDown(e) {
    if (e.key === 'Escape' && props.show) {
        handleClose();
    }
}

watch(() => props.show, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    document.body.style.overflow = '';
});
</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 z-[60] overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-6 transition-all duration-300"
        @click.self="handleClose"
    >
        <div 
            class="bg-white rounded-t-[28px] rounded-b-none sm:rounded-3xl shadow-2xl w-full border-t border-x sm:border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] sm:max-h-[90vh] animate-in fade-in slide-in-from-bottom-8 sm:slide-in-from-bottom-0 sm:zoom-in-95 duration-250 ease-out"
            :class="maxWidthClass"
            @click.stop
        >
            <component 
                :is="isForm ? 'form' : 'div'" 
                class="flex flex-col h-full overflow-hidden m-0"
                @submit.prevent="emit('submit')"
            >
                <!-- ══ TIRADOR / DRAG HANDLE (MÓVIL BOTTOM SHEET) ══ -->
                <div class="sm:hidden pt-3 pb-1 flex justify-center shrink-0 bg-white">
                    <div class="w-12 h-1.5 bg-slate-300 rounded-full"></div>
                </div>

                <!-- ══ CABECERA DEL MODAL ══ -->
                <div v-if="hasHeader" class="px-5 py-3.5 sm:px-6 sm:py-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
                    <slot name="header">
                        <div class="flex items-center gap-3 min-w-0">
                            <div 
                                v-if="icon" 
                                class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                :class="currentPalette.badge"
                            >
                                <component :is="getIconComponent()" class="w-5 h-5" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base sm:text-lg font-black text-slate-800 tracking-tight truncate m-0">
                                    {{ title }}
                                </h3>
                                <p v-if="subtitle" class="text-xs text-slate-500 font-medium m-0 truncate">
                                    {{ subtitle }}
                                </p>
                            </div>
                        </div>
                    </slot>

                    <button 
                        v-if="closeable"
                        type="button" 
                        @click="handleClose"
                        :disabled="submitting"
                        class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 p-2 rounded-xl transition-colors shrink-0 disabled:opacity-50"
                        title="Cerrar modal"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- ══ CUERPO SCROLLABLE DEL MODAL ══ -->
                <div class="px-5 py-4 sm:px-6 sm:py-6 overflow-y-auto flex-1 space-y-4">
                    <slot />
                </div>

                <!-- ══ PIE DE PÁGINA (ACCIONES) ══ -->
                <div v-if="hasFooter" class="px-5 py-4 pb-6 sm:pb-4 sm:px-6 sm:py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between shrink-0 gap-3">
                    <div class="max-sm:hidden">
                        <slot name="footer-actions" />
                    </div>

                    <slot name="footer">
                        <div class="flex items-center gap-2.5 max-sm:w-full">
                            <button 
                                type="button" 
                                @click="handleClose"
                                :disabled="submitting"
                                class="max-sm:flex-1 inline-flex items-center justify-center px-4 py-2.5 sm:py-2 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-100 active:bg-slate-200 border border-slate-300 rounded-xl transition-colors shadow-2xs disabled:opacity-50 cursor-pointer"
                            >
                                {{ cancelText }}
                            </button>

                            <button 
                                type="submit" 
                                :disabled="submitting || submitDisabled"
                                class="max-sm:flex-1 inline-flex items-center justify-center px-5 py-2.5 sm:py-2 text-xs sm:text-sm font-bold rounded-xl transition-colors shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                :class="currentPalette.submit"
                            >
                                <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin shrink-0" />
                                <span>{{ submitting ? 'Guardando...' : submitText }}</span>
                            </button>
                        </div>
                    </slot>
                </div>
            </component>
        </div>
    </div>
</template>
