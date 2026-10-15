<script setup>
import { reactive, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import esLocale from '@fullcalendar/core/locales/es.js';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import { useMediaQuery } from '@/Composables/useMediaQuery';
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
});

const detalle = reactive({
    show: false,
    event: null,
});

function findEvent(id) {
    return (props.events || []).find((e) => String(e.id) === String(id)) || null;
}

/**
 * FullCalendar pinta UN evento de `start` a `end`; como el `start`/`end` de la
 * agenda es el rango agregado (primer día → último día), marcaba también los
 * días intermedios. Se expande a un evento por día programado.
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

function handleEventClick(arg) {
    const agendaId = arg.event.extendedProps?.agendaId ?? arg.event.id;
    const original = findEvent(agendaId);
    detalle.event = original
        ? { ...original }
        : {
            id: agendaId,
            title: arg.event.title,
            evento: arg.event.extendedProps?.evento || '',
            nomDocente: arg.event.extendedProps?.nomDocente || '',
        };
    detalle.show = true;
}

// Vista de solo lectura: sin editable, sin selectable y sin handlers de
// arrastre (el Blade del Director no modificaba nada).
const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek,dayGridDay',
    },
    locale: esLocale,
    firstDay: 1,
    height: isCompact.value ? 'auto' : 550,
    dayMaxEvents: true,
    events: eventosCalendario(props.events),
    eventClick: handleEventClick,
}));
</script>

<template>
    <AppLayout>
        <Head title="Agenda de Lectura - Director" />

        <PageHeader
            title="Agenda de Lectura"
            icon="CalendarCheck"
            color="pink"
            subtitle="Lectura y actividades de lectura en la institución educativa"
        />

        <SectionTabs :tabs="tabs" color="pink" />

        <div class="agenda-calendar bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="p-3 sm:p-5 max-w-[860px] mx-auto">
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>

        <AgendaDetailModal
            :show="detalle.show"
            :agenda="detalle.event"
            :can-edit="false"
            :can-evidencia="false"
            @close="detalle.show = false"
        />
    </AppLayout>
</template>
