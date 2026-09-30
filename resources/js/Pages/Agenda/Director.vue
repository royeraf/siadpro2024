<script setup>
import { reactive, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import esLocale from '@fullcalendar/core/locales/es.js';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import AgendaDetailModal from './Partials/AgendaDetailModal.vue';

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

function handleEventClick(arg) {
    const original = findEvent(arg.event.id);
    detalle.event = original
        ? { ...original }
        : {
            id: arg.event.id,
            title: arg.event.title,
            evento: arg.event.extendedProps?.evento || '',
            nomDocente: arg.event.extendedProps?.nomDocente || '',
            start: arg.event.start ? arg.event.start.toISOString() : '',
            end: arg.event.end ? arg.event.end.toISOString() : '',
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
    height: 550,
    dayMaxEvents: true,
    events: (props.events || []).map((e) => ({
        id: String(e.id),
        title: e.title,
        start: e.start,
        end: e.end,
        color: e.color,
        evento: e.evento,
        nomDocente: e.nomDocente || '',
    })),
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

        <SectionTabs :tabs="tabs" section="agenda" color="pink" />

        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
            <div class="p-3 sm:p-5 max-w-[860px] mx-auto">
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>

        <AgendaDetailModal
            :show="detalle.show"
            :event="detalle.event"
            @close="detalle.show = false"
        />
    </AppLayout>
</template>
