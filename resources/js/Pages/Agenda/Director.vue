<script setup>
import { reactive, computed, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import esLocale from '@fullcalendar/core/locales/es.js';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import BaseModal from '@/Components/UI/BaseModal.vue';
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

// Bottom sheet (móvil) para el "+N más" del calendario; en escritorio se usa el
// popover nativo de FullCalendar.
const hoja = reactive({
    show: false,
    titulo: '',
    eventos: [],
});

function findEvent(id) {
    return (props.events || []).find((e) => String(e.id) === String(id)) || null;
}

/** "14:30" -> "02:30p" (12 h con sufijo a/p, como en la referencia). */
function formatearHora(hhmm) {
    if (!hhmm) return '';
    const [hStr, mStr] = String(hhmm).split(':');
    const h = Number(hStr);
    if (Number.isNaN(h)) return hhmm;
    const h12 = h % 12 === 0 ? 12 : h % 12;
    const sufijo = h < 12 ? 'a' : 'p';
    return `${String(h12).padStart(2, '0')}:${mStr}${sufijo}`;
}

/** Rango de hora del día, p. ej. "09:00a – 11:00a"; vacío si no hay horas. */
function rangoHora(horaInicio, horaFin) {
    const inicio = formatearHora(horaInicio);
    const fin = formatearHora(horaFin);
    if (inicio && fin) return `${inicio} – ${fin}`;
    return inicio || fin || '';
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

        return dias.map((d) => {
            const conHora = Boolean(d.hora_inicio && d.hora_fin);

            const evento = {
                ...base,
                id: `${e.id}-${d.id}`,
                // Con hora: se ubica en su franja (vistas de semana/día).
                // Sin hora: se muestra como evento de todo el día.
                allDay: !conHora,
                // Hora visible en la píldora (rango); vacía si el día no tiene horas.
                hora: rangoHora(d.hora_inicio, d.hora_fin),
            };

            if (conHora) {
                evento.start = `${d.fecha}T${d.hora_inicio}`;
                evento.end = `${d.fecha}T${d.hora_fin}`;
            } else {
                evento.start = d.fecha;
            }

            return evento;
        });
    });
}

/**
 * Cierra el popover de "+N más" del calendario.
 *
 * FullCalendar solo lo cierra con un clic FUERA de él (autoHide) o con su
 * propio botón; al pulsar un evento dentro, el clic es interno y el popover
 * queda abierto por encima del modal. Aquí se dispara su cierre.
 */
function cerrarPopoverCalendario() {
    const popover = document.querySelector('.fc-popover');
    if (!popover) return;

    const cerrar = popover.querySelector('.fc-popover-close');
    if (cerrar) {
        cerrar.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
    } else {
        // Respaldo: un mousedown fuera del popover activa su auto-cierre.
        document.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
    }
}

function handleEventClick(arg) {
    cerrarPopoverCalendario();

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

/** Título legible de una fecha (p. ej. "Lunes, 16 de octubre"). */
function tituloFecha(fecha) {
    const texto = fecha.toLocaleDateString('es-PE', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

/** Abre el bottom sheet con todos los eventos del día (solo móvil). */
function abrirHoja(arg) {
    // `arg.date` de FullCalendar es un marker en UTC; se convierte a fecha local
    // para evitar el desfase de un día (p. ej. en Perú, UTC-5).
    const fechaLocal = new Date(
        arg.date.getUTCFullYear(),
        arg.date.getUTCMonth(),
        arg.date.getUTCDate()
    );
    hoja.titulo = tituloFecha(fechaLocal);
    hoja.eventos = (arg.allSegs || []).map((seg) => {
        const ev = seg.event;
        return {
            id: ev.id,
            agendaId: ev.extendedProps?.agendaId ?? ev.id,
            title: ev.title,
            hora: ev.extendedProps?.hora || '',
            color: ev.backgroundColor || '',
            evento: ev.extendedProps?.evento || '',
            seccion: ev.extendedProps?.seccion || '',
            nomDocente: ev.extendedProps?.nomDocente || '',
        };
    });
    hoja.show = true;
}

/** Abre el detalle del evento elegido en el bottom sheet. */
async function abrirDesdeHoja(ev) {
    hoja.show = false;
    const original = findEvent(ev.agendaId);
    detalle.event = original
        ? { ...original }
        : {
            id: ev.agendaId,
            title: ev.title,
            evento: ev.evento,
            nomDocente: ev.nomDocente,
        };
    // Se espera a que el sheet termine de cerrarse antes de abrir el detalle,
    // para que no se pisen al restaurar el scroll del body.
    await nextTick();
    detalle.show = true;
}

// Vista de solo lectura: sin editable, sin selectable y sin handlers de
// arrastre (el Blade del Director no modificaba nada).
const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    // Semana y día como franja horaria (eje de horas a la izquierda) con una
    // altura fija para que el scroll quede dentro del calendario.
    views: {
        timeGridWeek: { height: 520 },
        timeGridDay: { height: 520 },
    },
    // Eje de horas en formato HH:mm (por defecto v6 muestra solo la hora: "6").
    slotLabelFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    },
    locale: esLocale,
    firstDay: 1,
    height: isCompact.value ? 'auto' : 550,
    // En móvil la altura es 'auto' y la celda crece con el contenido, por lo que
    // 'true' nunca mostraría el "+N más". Se limita a 2 eventos por día (deja
    // espacio libre y hace aparecer antes el "+N", que abre el bottom sheet).
    dayMaxEvents: isCompact.value ? 2 : true,
    // En la vista mes los eventos se ven como píldoras (no puntos) y la hora
    // la pinta el slot #eventContent, no FullCalendar.
    eventDisplay: 'block',
    displayEventTime: false,
    events: eventosCalendario(props.events),
    eventClick: handleEventClick,
    // En móvil el "+N más" abre un bottom sheet en vez del popover nativo.
    // Devolver `true` evita que FullCalendar muestre su popover.
    moreLinkClick: (arg) => {
        if (!isCompact.value) return 'popover';
        abrirHoja(arg);
        return true;
    },
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
                <FullCalendar :options="calendarOptions">
                    <template #eventContent="{ event, view }">
                        <div class="agenda-ev">
                            <span class="fc-event-title">{{ event.title }}</span><span
                                v-if="view.type.startsWith('dayGrid') && event.extendedProps.hora"
                                class="fc-event-time"
                            >, {{ event.extendedProps.hora }}</span>
                        </div>
                    </template>
                </FullCalendar>
            </div>
        </div>

        <AgendaDetailModal
            :show="detalle.show"
            :agenda="detalle.event"
            :can-edit="false"
            :can-evidencia="false"
            @close="detalle.show = false"
        />

        <!-- Bottom sheet (móvil) con los eventos del día al pulsar "+N más" -->
        <BaseModal
            :show="hoja.show"
            :is-form="false"
            :has-footer="false"
            :title="hoja.titulo"
            subtitle="Eventos programados"
            icon="CalendarCheck"
            color="pink"
            max-width="md"
            @close="hoja.show = false"
        >
            <div class="space-y-2">
                <button
                    v-for="ev in hoja.eventos"
                    :key="ev.id"
                    type="button"
                    @click="abrirDesdeHoja(ev)"
                    class="w-full flex items-stretch gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors text-left cursor-pointer"
                >
                    <span
                        class="w-1.5 rounded-full shrink-0"
                        :style="{ backgroundColor: ev.color || '#94a3b8' }"
                    ></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-xs font-bold text-slate-800 truncate">{{ ev.title }}</span>
                        <span v-if="ev.hora" class="block text-[11px] font-semibold text-slate-500">{{ ev.hora }}</span>
                        <span v-if="ev.evento" class="block text-[11px] text-slate-400 truncate">{{ ev.evento }}</span>
                    </span>
                </button>
            </div>
        </BaseModal>
    </AppLayout>
</template>
