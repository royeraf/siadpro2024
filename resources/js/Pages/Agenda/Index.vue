<script setup>
import { reactive, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { CheckCircle, AlertCircle } from 'lucide-vue-next';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es.js';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import AgendaFormModal from './Partials/AgendaFormModal.vue';

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
    tabs: {
        type: Array,
        default: () => [],
    },
    secciones: {
        type: Object,
        default: () => ({}),
    },
    can: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const modal = reactive({
    show: false,
    mode: 'create',
    event: null,
});

// <input type="datetime-local"> trabaja en hora local; FullCalendar entrega
// objetos Date ya en hora local, así que se formatea a mano (nunca con
// toISOString(), que convertiría a UTC y desplazaría un día).
function toInputValue(value) {
    if (!value) return '';
    const fecha = value instanceof Date ? value : new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(fecha.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${fecha.getFullYear()}-${pad(fecha.getMonth() + 1)}-${pad(fecha.getDate())}T${pad(fecha.getHours())}:${pad(fecha.getMinutes())}`;
}

function findEvent(id) {
    return (props.events || []).find((e) => String(e.id) === String(id)) || null;
}

function handleSelect(arg) {
    modal.event = {
        id: null,
        title: '',
        evento: '',
        seccion: '',
        start: toInputValue(arg.start),
        end: toInputValue(arg.end),
    };
    modal.mode = 'create';
    modal.show = true;
}

function handleEventClick(arg) {
    const original = findEvent(arg.event.id);
    modal.event = original
        ? { ...original }
        : {
            id: arg.event.id,
            title: arg.event.title,
            evento: arg.event.extendedProps?.evento || '',
            seccion: arg.event.extendedProps?.seccion || '',
            start: toInputValue(arg.event.start),
            end: toInputValue(arg.event.end),
        };
    modal.mode = 'view';
    modal.show = true;
}

function handleEdit() {
    modal.mode = 'edit';
}

function handleCancel() {
    if (modal.mode === 'edit') {
        // Vuelve a lectura descartando los cambios (el modal repuebla los
        // campos desde el evento original al cambiar de modo).
        modal.mode = 'view';
    } else {
        modal.show = false;
    }
}

function handleClose() {
    modal.show = false;
}

function confirmDelete(evento) {
    Swal.fire({
        title: '¿Está seguro de eliminar este evento?',
        text: `Se eliminará "${evento.title}".`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: 'rounded-2xl shadow-2xl border border-slate-100 p-6',
            title: 'text-base sm:text-lg font-bold text-slate-800',
            htmlContainer: 'text-xs sm:text-sm text-slate-600 mt-1',
            actions: 'flex items-center justify-center gap-3 mt-5',
            confirmButton: 'px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold hover:bg-rose-700 active:bg-rose-800 shadow-md shadow-rose-600/30 transition-all text-xs sm:text-sm cursor-pointer border-0 outline-none',
            cancelButton: 'px-5 py-2.5 rounded-xl bg-slate-700 text-white font-semibold hover:bg-slate-600 active:bg-slate-500 border border-slate-600 shadow-xs transition-all text-xs sm:text-sm cursor-pointer outline-none',
        },
        buttonsStyling: false,
    }).then((result) => {
        if (!result.isConfirmed) return;

        // AgendaController::update() decide el borrado con $request->get('delete')
        // == 'on' e identifica el registro por $request->id, así que hace falta
        // un FormData multipart (el body JSON no se ve en $request->get()).
        const formData = new FormData();
        formData.append('id', String(evento.id));
        formData.append('delete', 'on');

        router.post('/agendas/update', formData, {
            preserveScroll: true,
            onSuccess: () => {
                modal.show = false;
                Swal.fire({
                    title: '¡Eliminado!',
                    text: 'El evento ha sido eliminado con éxito.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl border border-slate-100 p-6',
                        title: 'text-base sm:text-lg font-bold text-slate-800',
                        htmlContainer: 'text-xs sm:text-sm text-slate-600',
                    },
                });
            },
            onError: (errors) => {
                const mensaje = Object.values(errors || {}).join(' ')
                    || 'No se pudo eliminar el evento.';
                Swal.fire({
                    title: 'No se pudo eliminar',
                    text: mensaje,
                    icon: 'error',
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl border border-slate-100 p-6',
                        title: 'text-base sm:text-lg font-bold text-slate-800',
                        htmlContainer: 'text-xs sm:text-sm text-slate-600',
                    },
                    buttonsStyling: false,
                });
            },
        });
    });
}

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek,dayGridDay',
    },
    locale: esLocale,
    // La locale 'es' de FullCalendar v6 no fija el primer día de la semana;
    // el legacy (v3) mostraba lunes primero, así que se fija explícitamente.
    firstDay: 1,
    height: 550,
    // Defensa en profundidad: el backend ya exige agendas.create/agendas.edit,
    // pero aquí también se ocultan la selección de un día vacío y el
    // arrastrar/soltar si falta el permiso (igual que el Blade original).
    editable: !!props.can?.edit,
    selectable: !!props.can?.create,
    dayMaxEvents: true,
    events: (props.events || []).map((e) => ({
        id: String(e.id),
        title: e.title,
        start: e.start,
        end: e.end,
        color: e.color,
        evento: e.evento,
        seccion: e.seccion,
        nomDocente: e.nomDocente || '',
    })),
    select: handleSelect,
    eventClick: handleEventClick,
}));
</script>

<template>
    <AppLayout>
        <Head title="Agenda de Lectura" />

        <PageHeader
            title="Agenda de Lectura"
            icon="CalendarCheck"
            color="pink"
            subtitle="Lectura y actividades de lectura en la institución educativa"
        />

        <SectionTabs :tabs="tabs" color="pink" />

        <div
            v-if="page.props.flash?.success"
            class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between"
        >
            <div class="flex items-center">
                <CheckCircle class="w-4 h-4 mr-2 text-emerald-600 shrink-0" />
                <span>{{ page.props.flash.success }}</span>
            </div>
            <button
                type="button"
                @click="page.props.flash.success = null"
                class="text-emerald-500 hover:text-emerald-700 p-1"
            >
                &times;
            </button>
        </div>

        <div
            v-if="page.props.flash?.error"
            class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center justify-between"
        >
            <div class="flex items-center">
                <AlertCircle class="w-4 h-4 mr-2 text-rose-600 shrink-0" />
                <span>{{ page.props.flash.error }}</span>
            </div>
            <button
                type="button"
                @click="page.props.flash.error = null"
                class="text-rose-500 hover:text-rose-700 p-1"
            >
                &times;
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="p-3 sm:p-5 max-w-[760px] mx-auto">
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>

        <AgendaFormModal
            :show="modal.show"
            :mode="modal.mode"
            :event="modal.event"
            :secciones="secciones"
            :can-edit="!!can.edit"
            :can-destroy="!!can.destroy"
            @close="handleClose"
            @edit="handleEdit"
            @cancel="handleCancel"
            @delete="confirmDelete"
        />
    </AppLayout>
</template>
