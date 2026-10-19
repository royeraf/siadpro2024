<script setup>
import { reactive, computed, nextTick } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { AgendasService } from '@/Services/agenda';
import Swal from 'sweetalert2';
import { CheckCircle, AlertCircle, MoreHorizontal, CalendarDays } from 'lucide-vue-next';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es.js';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import AddFab from '@/Components/UI/AddFab.vue';
import BaseModal from '@/Components/UI/BaseModal.vue';
import { useMediaQuery } from '@/Composables/useMediaQuery';
import AgendaFormModal from './Partials/AgendaFormModal.vue';
import AgendaDetailModal from './Partials/AgendaDetailModal.vue';

// En móvil cada día tiene muy poco ancho: se oculta la hora de las píldoras
// del mes para priorizar el título. La altura del calendario es automática.
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
    // El detalle se abrió desde el listado del día: al cerrarlo se vuelve a
    // mostrar el listado en lugar de cerrar todas las capas.
    volverHoja: false,
});

const form = reactive({
    show: false,
    mode: 'create',
    event: null,
    volverDetalle: false,
});

// Modal con la lista completa de eventos del día. Se abre desde la celda
// agrupada del mes (tanto en móvil como en escritorio).
const hoja = reactive({
    show: false,
    fecha: '',
    titulo: '',
    eventos: [],
});

// FullCalendar entrega objetos Date en hora local; se formatea a mano (nunca
// con toISOString(), que convertiría a UTC y desplazaría un día).
function toFechaISO(value) {
    const fecha = value instanceof Date ? value : parseFechaLocal(value);
    if (!fecha) return '';
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

/** Convierte un valor de fecha a un Date local (sin desfase de zona horaria). */
function parseFechaLocal(value) {
    if (value instanceof Date) {
        return new Date(value.getFullYear(), value.getMonth(), value.getDate());
    }

    const texto = String(value ?? '');
    const iso = texto.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (iso) {
        return new Date(Number(iso[1]), Number(iso[2]) - 1, Number(iso[3]));
    }

    const fecha = new Date(texto.replace(' ', 'T'));
    return Number.isNaN(fecha.getTime())
        ? null
        : new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate());
}

/** Fechas (YYYY-MM-DD) que abarca un rango; el fin es inclusivo. */
function fechasDeRango(startValue, endValue) {
    const inicio = parseFechaLocal(startValue);
    if (!inicio) return [];

    const fin = parseFechaLocal(endValue);
    if (!fin || fin < inicio) return [toFechaISO(inicio)];

    // Si el fin viene como fecha sin hora, FullCalendar lo trata como
    // exclusivo (el día siguiente al rango); se ajusta para no pintar un día
    // de más en los datos históricos.
    const textoFin = String(endValue ?? '').replace(' ', 'T');
    if (!/T\d{2}:\d{2}/.test(textoFin)) {
        fin.setDate(fin.getDate() - 1);
    }
    if (fin < inicio) fin.setTime(inicio.getTime());

    const fechas = [];
    for (let d = new Date(inicio); d <= fin; d.setDate(d.getDate() + 1)) {
        fechas.push(toFechaISO(d));
    }
    return fechas;
}

/** Fondo tenue (rgba) a partir del hex de la sección. */
function softColor(hex, alpha = 0.16) {
    const valor = String(hex || '').replace('#', '');
    if (!/^[0-9a-fA-F]{6}$/.test(valor)) return 'rgba(148, 163, 184, 0.14)';

    const r = parseInt(valor.slice(0, 2), 16);
    const g = parseInt(valor.slice(2, 4), 16);
    const b = parseInt(valor.slice(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

/**
 * Eventos agrupados por fecha para la vista mensual. Se construye desde los
 * datos originales (no desde los eventos de FullCalendar) para conservar la
 * hora y el `agendaId` de cada día.
 */
const eventosPorFecha = computed(() => {
    const mapa = {};

    const agregar = (fecha, evento) => {
        if (!fecha) return;
        if (!mapa[fecha]) mapa[fecha] = [];
        mapa[fecha].push(evento);
    };

    (props.events || []).forEach((e) => {
        const base = {
            agendaId: e.id,
            title: e.title,
            color: e.color,
            evento: e.evento,
            seccion: e.seccion,
            nomDocente: e.nomDocente || '',
        };

        const dias = Array.isArray(e.dias) ? e.dias : [];

        if (dias.length) {
            dias.forEach((d) => {
                const conHora = Boolean(d.hora_inicio && d.hora_fin);
                agregar(d.fecha, {
                    ...base,
                    id: `${e.id}-${d.id}`,
                    hora: rangoHora(d.hora_inicio, d.hora_fin),
                    // En la celda solo cabe la hora inicial; el rango completo
                    // se muestra en el modal.
                    horaInicioTexto: formatearHora(d.hora_inicio),
                    horaInicio: d.hora_inicio || '',
                    horaFin: d.hora_fin || '',
                    allDay: !conHora,
                });
            });
            return;
        }

        // Datos históricos sin días: se replican en cada fecha del rango para
        // que el contador y el modal coincidan con lo que pinta el calendario.
        fechasDeRango(e.start, e.end).forEach((fecha) => {
            agregar(fecha, {
                ...base,
                id: `${e.id}-${fecha}`,
                hora: '',
                horaInicioTexto: '',
                horaInicio: '',
                horaFin: '',
                allDay: true,
            });
        });
    });

    // Todo el día primero; luego por hora de inicio.
    Object.values(mapa).forEach((lista) => {
        lista.sort((a, b) => {
            if (a.allDay !== b.allDay) return a.allDay ? -1 : 1;
            return (a.horaInicio || '').localeCompare(b.horaInicio || '');
        });
    });

    return mapa;
});

function eventosDelDia(fecha) {
    return eventosPorFecha.value[fecha] || [];
}

/** Primeros 5 eventos que se previsualizan en la celda del mes. */
function previewDia(fecha) {
    return eventosDelDia(fecha).slice(0, 5);
}

function totalDia(fecha) {
    return eventosDelDia(fecha).length;
}

function etiquetaDia(arg) {
    const fecha = toFechaISO(arg.date);
    const total = totalDia(fecha);
    const base = tituloFecha(fecha);

    if (!total) return `${base}. Sin eventos. Abrir opciones del día`;
    return `${base}. ${total} evento${total === 1 ? '' : 's'}. Ver todos`;
}

const subtituloHoja = computed(() => {
    const total = hoja.eventos.length;
    if (!total) return 'Sin eventos programados';
    return `${total} evento${total === 1 ? '' : 's'} programado${total === 1 ? '' : 's'}`;
});

// El FAB de la página se oculta mientras haya cualquier modal abierto: el
// listado del día, el detalle o el formulario. Así no se ve por detrás del
// fondo oscuro ni compite con el FAB propio del modal.
const hayModalActivo = computed(() => hoja.show || detalle.show || form.show);

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
 * Clic/toque simple sobre un día.
 *
 * En la vista mensual abre el modal con todos los eventos del día (también si
 * está vacío, para poder crear desde ahí). En semana/día se conserva el
 * comportamiento anterior: crear una agenda para la fecha pulsada.
 *
 * En pantallas táctiles el evento `select` de FullCalendar solo se dispara
 * tras una pulsación larga (`selectLongPressDelay`, 1000 ms por defecto), así
 * que un toque normal no abría nada. `dateClick` sí responde al toque corto.
 */
function handleDateClick(arg) {
    if (arg.view?.type?.startsWith('dayGrid')) {
        abrirListadoDia(toFechaISO(arg.date));
        return;
    }

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

    // Abierto directo desde el calendario: al cerrar no se reabre el listado.
    detalle.volverHoja = false;

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
function tituloFecha(value) {
    const fecha = value instanceof Date ? value : parseFechaLocal(value);
    if (!fecha) return '';

    const texto = fecha.toLocaleDateString('es-PE', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

/** Abre el modal con la lista completa de eventos de una fecha. */
function abrirListadoDia(fecha) {
    const normalizada = toFechaISO(parseFechaLocal(fecha) || fecha);

    hoja.fecha = normalizada;
    hoja.titulo = tituloFecha(normalizada);
    hoja.eventos = eventosDelDia(normalizada);
    hoja.show = true;
}

/** Abre el detalle del evento elegido en el bottom sheet. */
async function abrirDesdeHoja(ev) {
    hoja.show = false;
    // Marca que el detalle viene del listado: al cerrarlo se reabre la lista.
    detalle.volverHoja = true;
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

/**
 * Cierra el detalle. Si se abrió desde el listado del día, vuelve a mostrarlo
 * (en lugar de dejar cerrado todo). Se espera un tick para que el detalle
 * libere su bloqueo de scroll antes de que el listado lo vuelva a tomar.
 */
async function cerrarDetalle() {
    detalle.show = false;

    if (detalle.volverHoja) {
        detalle.volverHoja = false;
        await nextTick();
        // Refresca la lista por si el evento se editó mientras estaba abierto.
        hoja.eventos = eventosDelDia(hoja.fecha);
        hoja.show = true;
    }
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
                detalle.volverHoja = false;
                hoja.show = false;
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
    // El mes crece según el contenido: cada celda muestra hasta 5 eventos y no
    // debe recortarlos. Semana/día conservan su altura fija.
    height: 'auto',
    // Defensa en profundidad: el backend ya exige agendas.create/agendas.edit,
    // pero aquí también se ocultan la selección de un día vacío y el
    // arrastrar/soltar si falta el permiso (igual que el Blade original).
    editable: !!props.can?.edit,
    selectable: !!props.can?.create,
    // En el mes no se pintan las píldoras nativas: la celda usa un botón
    // agrupado con los primeros 5 eventos y el resto se ve en el modal.
    dayMaxEvents: 0,
    // En semana/día los eventos se ven como píldoras (no puntos) y la hora
    // la pinta el slot #eventContent, no FullCalendar.
    eventDisplay: 'block',
    displayEventTime: false,
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
                <FullCalendar :options="calendarOptions">
                    <!-- Vista mes: un solo botón por día con hasta 5 eventos. -->
                    <template #dayCellContent="arg">
                        <button
                            type="button"
                            class="agenda-day-cell"
                            :aria-label="etiquetaDia(arg)"
                            @click.stop="abrirListadoDia(toFechaISO(arg.date))"
                        >
                            <span
                                class="agenda-day-number"
                                :class="{ 'is-today': arg.isToday }"
                            >
                                {{ arg.dayNumberText }}
                            </span>

                            <span
                                v-if="totalDia(toFechaISO(arg.date))"
                                class="agenda-day-events"
                            >
                                <span
                                    v-for="ev in previewDia(toFechaISO(arg.date))"
                                    :key="ev.id"
                                    class="agenda-day-event"
                                    :style="{
                                        backgroundColor: softColor(ev.color),
                                        borderLeftColor: ev.color || '#94a3b8',
                                    }"
                                >
                                    <span
                                        class="agenda-day-event-dot"
                                        :style="{ backgroundColor: ev.color || '#94a3b8' }"
                                    ></span>
                                    <span class="agenda-day-event-title">{{ ev.title }}</span>
                                    <span
                                        v-if="!isCompact && ev.horaInicioTexto"
                                        class="agenda-day-event-time"
                                    >{{ ev.horaInicioTexto }}</span>
                                </span>

                                <span
                                    v-if="totalDia(toFechaISO(arg.date)) > 5"
                                    class="agenda-day-more"
                                    aria-hidden="true"
                                >
                                    <MoreHorizontal class="w-3.5 h-3.5" />
                                </span>
                            </span>
                        </button>
                    </template>

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
            @close="cerrarDetalle"
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

        <!-- Modal con la lista completa de eventos del día (vista mensual) -->
        <BaseModal
            :show="hoja.show"
            :is-form="false"
            :has-footer="false"
            :title="hoja.titulo"
            :subtitle="subtituloHoja"
            icon="CalendarCheck"
            color="pink"
            max-width="md"
            @close="hoja.show = false"
        >
            <div v-if="hoja.eventos.length" class="space-y-2">
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
                        <span class="mt-0.5 flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                            <span
                                v-if="ev.hora"
                                class="inline-flex items-center gap-1"
                            >{{ ev.hora }}</span>
                            <span
                                v-else
                                class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500"
                            >Todo el día</span>
                        </span>
                        <span v-if="ev.evento" class="mt-0.5 block text-[11px] text-slate-400 truncate">{{ ev.evento }}</span>
                    </span>
                </button>
            </div>

            <div
                v-else
                class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
            >
                <CalendarDays class="w-8 h-8 text-slate-300 mx-auto mb-2" />
                <p class="text-xs font-semibold text-slate-500 m-0">
                    No hay eventos programados para este día.
                </p>
                <p v-if="can.create" class="text-[11px] text-slate-400 m-0 mt-1">
                    Puedes crear una agenda para esta fecha.
                </p>
            </div>

            <!-- Espacio para que el último evento pueda desplazarse por encima
                 del FAB y no quede oculto. -->
            <div v-if="can.create" class="h-16" aria-hidden="true"></div>

            <!-- FAB universal (móvil y escritorio): crear agenda para este día. -->
            <template #fab>
                <AddFab
                    v-if="can.create"
                    label="Agregar"
                    aria-label="Agregar agenda para este día"
                    @click="agregarDesdeHoja"
                />
            </template>
        </BaseModal>

        <!-- FAB universal de la página: crear una agenda (abre el formulario). -->
        <AddFab
            v-if="can.create && !hayModalActivo"
            label="Agregar"
            aria-label="Agregar agenda de lectura"
            class="fixed right-5 bottom-6 z-40 md:right-8 md:bottom-8"
            @click="abrirCreacionHoy"
        />
    </AppLayout>
</template>
