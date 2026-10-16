<script setup>
import { reactive, computed, nextTick } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { AgendasService } from '@/Services/agenda';
import Swal from 'sweetalert2';
import { CheckCircle, AlertCircle, Plus } from 'lucide-vue-next';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es.js';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import CreateButton from '@/Components/UI/CreateButton.vue';
import BaseModal from '@/Components/UI/BaseModal.vue';
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

// Bottom sheet (móvil) para el "+N más" del calendario; en escritorio se usa el
// popover nativo de FullCalendar.
const hoja = reactive({
    show: false,
    fecha: '',
    titulo: '',
    eventos: [],
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
 * Botón "Agregar agenda de lectura": abre el formulario con la fecha de hoy
 * (el usuario puede cambiarla dentro del modal).
 */
function abrirCreacionHoy() {
    abrirCreacion([
        { fecha: toFechaISO(new Date()), hora_inicio: '', hora_fin: '' },
    ]);
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
    hoja.fecha = toFechaISO(fechaLocal);
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
    detalle.agenda = original
        ? { ...original }
        : {
            id: ev.agendaId,
            title: ev.title,
            evento: ev.evento,
            seccion: ev.seccion,
            nomDocente: ev.nomDocente,
        };
    // Se espera a que el sheet termine de cerrarse antes de abrir el detalle,
    // para que no se pisen al restaurar el scroll del body.
    await nextTick();
    detalle.show = true;
}

/** Crea una agenda para la fecha del bottom sheet. */
async function agregarDesdeHoja() {
    const fecha = hoja.fecha || toFechaISO(new Date());
    hoja.show = false;
    await nextTick();
    abrirCreacion([{ fecha, hora_inicio: '', hora_fin: '' }]);
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
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
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
    // La locale 'es' de FullCalendar v6 no fija el primer día de la semana;
    // el legacy (v3) mostraba lunes primero, así que se fija explícitamente.
    firstDay: 1,
    height: isCompact.value ? 'auto' : 550,
    // Defensa en profundidad: el backend ya exige agendas.create/agendas.edit,
    // pero aquí también se ocultan la selección de un día vacío y el
    // arrastrar/soltar si falta el permiso (igual que el Blade original).
    editable: !!props.can?.edit,
    selectable: !!props.can?.create,
    // En móvil la altura es 'auto' y la celda crece con el contenido, por lo que
    // 'true' nunca mostraría el "+N más". Se limita a 2 eventos por día (deja
    // espacio libre y hace aparecer antes el "+N", que abre el bottom sheet).
    dayMaxEvents: isCompact.value ? 2 : true,
    // En la vista mes los eventos se ven como píldoras (no puntos) y la hora
    // la pinta el slot #eventContent, no FullCalendar.
    eventDisplay: 'block',
    displayEventTime: false,
    events: eventosCalendario(props.events),
    select: handleSelect,
    dateClick: handleDateClick,
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
        <Head title="Agenda de Lectura" />

        <PageHeader
            title="Agenda de Lectura"
            icon="CalendarCheck"
            color="pink"
            subtitle="Lectura y actividades de lectura en la institución educativa"
        />

        <SectionTabs :tabs="tabs" color="pink" />

        <CreateButton v-if="can.create" @click="abrirCreacionHoy">
            Agregar agenda de lectura
        </CreateButton>

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

            <button
                type="button"
                @click="agregarDesdeHoja"
                class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl transition-colors cursor-pointer"
            >
                <Plus class="w-4 h-4 shrink-0" />
                Agregar agenda para este día
            </button>
        </BaseModal>

        <!-- FAB móvil: agregar agenda (siempre visible, incluso con celdas llenas) -->
        <button
            v-if="can.create"
            type="button"
            @click="abrirCreacionHoy"
            class="md:hidden fixed bottom-6 right-5 z-40 inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-lg shadow-emerald-600/30 transition-colors cursor-pointer"
            title="Agregar agenda de lectura"
            aria-label="Agregar agenda de lectura"
        >
            <Plus class="w-6 h-6" />
        </button>
    </AppLayout>
</template>
