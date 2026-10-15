<script setup>
import { reactive, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { AgendasService } from '@/Services/agenda';
import Swal from 'sweetalert2';
import { CheckCircle, AlertCircle } from 'lucide-vue-next';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es.js';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import { useMediaQuery } from '@/Composables/useMediaQuery';
import AgendaFormModal from './Partials/AgendaFormModal.vue';
import AgendaDetailModal from './Partials/AgendaDetailModal.vue';

// En móvil el calendario ocupa el ancho completo: la altura fija de 550px
// deja filas muy comprimidas, así que se deja crecer según su contenido.
const isCompact = useMediaQuery('(max-width: 767px)');

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

const detalle = reactive({
    show: false,
    agenda: null,
});

const form = reactive({
    show: false,
    mode: 'create',
    event: null,
    volverDetalle: false,
});

// FullCalendar entrega objetos Date en hora local; se formatea a mano (nunca
// con toISOString(), que convertiría a UTC y desplazaría un día).
function toFechaISO(value) {
    const fecha = value instanceof Date ? value : new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(fecha.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${fecha.getFullYear()}-${pad(fecha.getMonth() + 1)}-${pad(fecha.getDate())}`;
}

function findEvent(id) {
    return (props.events || []).find((e) => String(e.id) === String(id)) || null;
}

/**
 * FullCalendar pinta UN evento de `start` a `end`; como el `start`/`end` de la
 * agenda es el rango agregado (primer día → último día), marcaba también los
 * días intermedios. Se expande a un evento por día programado, de modo que en
 * el calendario solo se marquen los días reales.
 */
function eventosCalendario(list) {
    return (list || []).flatMap((e) => {
        const base = {
            title: e.title,
            color: e.color,
            evento: e.evento,
            seccion: e.seccion,
            nomDocente: e.nomDocente || '',
            agendaId: e.id,
        };

        const dias = Array.isArray(e.dias) && e.dias.length ? e.dias : null;

        // Agendas sin días (datos históricos): se conserva el rango agregado.
        if (!dias) {
            return [{ ...base, id: String(e.id), start: e.start, end: e.end }];
        }

        return dias.map((d) => ({
            ...base,
            id: `${e.id}-${d.id}`,
            start: `${d.fecha}T${d.hora_inicio || '00:00'}`,
            end: `${d.fecha}T${d.hora_fin || '23:59'}`,
        }));
    });
}

/** Días de un rango de selección del calendario (end exclusivo, como FullCalendar). */
function fechasEnRango(start, end) {
    const desde = new Date(start.getFullYear(), start.getMonth(), start.getDate());
    let hasta = new Date(end.getFullYear(), end.getMonth(), end.getDate());
    hasta = new Date(hasta.getTime() - 86400000);
    if (hasta < desde) hasta = desde;

    const dias = [];
    for (let d = new Date(desde); d <= hasta; d.setDate(d.getDate() + 1)) {
        dias.push({ fecha: toFechaISO(d), hora_inicio: '', hora_fin: '' });
    }
    return dias;
}

/** Abre el formulario de creación con la lista de días indicada. */
function abrirCreacion(dias) {
    form.event = {
        id: null,
        title: '',
        evento: '',
        seccion: '',
        dias,
    };
    form.volverDetalle = false;
    form.mode = 'create';
    form.show = true;
}

/**
 * Clic/toque simple sobre un día vacío.
 *
 * En pantallas táctiles el evento `select` de FullCalendar solo se dispara
 * tras una pulsación larga (`selectLongPressDelay`, 1000 ms por defecto), así
 * que un toque normal no abría nada. `dateClick` sí responde al toque corto y
 * es el que crea la agenda de un único día.
 */
function handleDateClick(arg) {
    if (!props.can?.create) return;
    abrirCreacion([
        { fecha: toFechaISO(arg.date), hora_inicio: '', hora_fin: '' },
    ]);
}

function handleSelect(arg) {
    // El arrastre sobre varios días sigue abriendo el formulario con el rango.
    // El clic/toque de un solo día lo maneja handleDateClick (así no se
    // duplica la apertura, porque ambos eventos se disparan en el clic simple).
    const dias = fechasEnRango(arg.start, arg.end);
    if (dias.length <= 1) return;
    abrirCreacion(dias);
}

function handleEventClick(arg) {
    // El id del evento del calendario es `${agendaId}-${diaId}`; se resuelve la
    // agenda por `agendaId` para abrir el detalle completo.
    const agendaId = arg.event.extendedProps?.agendaId ?? arg.event.id;
    const original = findEvent(agendaId);
    detalle.agenda = original
        ? { ...original }
        : {
            id: agendaId,
            title: arg.event.title,
            evento: arg.event.extendedProps?.evento || '',
            seccion: arg.event.extendedProps?.seccion || '',
            nomDocente: arg.event.extendedProps?.nomDocente || '',
        };
    detalle.show = true;
}

/** Abre el formulario en edición con los días ya cargados por el detalle. */
function handleEdit(payload) {
    const base = detalle.agenda || {};
    form.event = {
        id: base.id,
        title: base.title,
        evento: base.evento,
        seccion: base.seccion,
        dias: payload?.dias || undefined,
    };
    form.mode = 'edit';
    form.volverDetalle = true;
    form.show = true;
    detalle.show = false;
}

function cerrarForm() {
    form.show = false;

    if (form.volverDetalle && detalle.agenda) {
        detalle.show = true;
    }
    form.volverDetalle = false;
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

        AgendasService.destroy(evento.id, {
            preserveScroll: true,
            onSuccess: () => {
                form.show = false;
                detalle.show = false;
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
    height: isCompact.value ? 'auto' : 550,
    // Defensa en profundidad: el backend ya exige agendas.create/agendas.edit,
    // pero aquí también se ocultan la selección de un día vacío y el
    // arrastrar/soltar si falta el permiso (igual que el Blade original).
    editable: !!props.can?.edit,
    selectable: !!props.can?.create,
    dayMaxEvents: true,
    events: eventosCalendario(props.events),
    select: handleSelect,
    dateClick: handleDateClick,
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

        <div class="agenda-calendar bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="p-3 sm:p-5 max-w-[760px] mx-auto">
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>

        <AgendaDetailModal
            :show="detalle.show"
            :agenda="detalle.agenda"
            :can-edit="!!can.edit"
            :can-evidencia="!!can.evidencia"
            @close="detalle.show = false"
            @edit="handleEdit"
        />

        <AgendaFormModal
            :show="form.show"
            :mode="form.mode"
            :event="form.event"
            :secciones="secciones"
            :can-edit="!!can.edit"
            :can-destroy="!!can.destroy"
            @close="cerrarForm"
            @cancel="cerrarForm"
            @delete="confirmDelete"
        />
    </AppLayout>
</template>
