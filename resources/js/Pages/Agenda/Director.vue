<script setup>
import { reactive, computed, nextTick, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import esLocale from '@fullcalendar/core/locales/es.js';
import { MoreHorizontal, CalendarDays, Loader2, AlertCircle } from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import SectionTabs from '@/Components/UI/SectionTabs.vue';
import BaseModal from '@/Components/UI/BaseModal.vue';
import { useMediaQuery } from '@/Composables/useMediaQuery';
import { AgendasService } from '@/Services/agenda';
import AgendaDetailModal from './Partials/AgendaDetailModal.vue';

// En móvil cada día tiene muy poco ancho: se oculta la hora de las píldoras
// del mes para priorizar el título. La altura del calendario es automática.
const isCompact = useMediaQuery('(max-width: 767px)');

defineProps({
    tabs: {
        type: Array,
        default: () => [],
    },
});

// Agendas del rango que FullCalendar tiene visible en este momento. Se piden
// aparte (carga perezosa) para no traer toda la institución en el primer render.
const agendasCargadas = ref([]);
const cargandoEventos = ref(false);
const errorEventos = ref('');
// Rango visible que pide FullCalendar. Se usa para no expandir rangos
// históricos inválidos fuera de la vista.
const rangoVisible = ref({ start: '', end: '' });

const detalle = reactive({
    show: false,
    event: null,
    // El detalle se abrió desde el listado del día: al cerrarlo se vuelve a
    // mostrar el listado en lugar de cerrar todas las capas.
    volverHoja: false,
});

// Modal con la lista completa de eventos del día. Se abre desde la celda
// agrupada del mes (tanto en móvil como en escritorio).
const hoja = reactive({
    show: false,
    fecha: '',
    titulo: '',
    eventos: [],
});

function findEvent(id) {
    return (agendasCargadas.value || []).find((e) => String(e.id) === String(id)) || null;
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
            // Agendas históricas sin días: se acota al rango visible para no
            // arrastrar rangos inválidos (p. ej. años con ceros de más).
            const rango = fechasDeRango(e.start, e.end, rangoVisible.value);
            if (!rango.length) return [];

            const finDia = parseFechaLocal(rango[rango.length - 1]);
            finDia.setDate(finDia.getDate() + 1);

            return [{
                ...base,
                id: String(e.id),
                start: rango[0],
                end: toFechaISO(finDia),
                allDay: true,
            }];
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

/** Formatea un Date a YYYY-MM-DD con componentes locales (sin UTC). */
function toFechaISO(value) {
    const fecha = value instanceof Date ? value : parseFechaLocal(value);
    if (!fecha) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${fecha.getFullYear()}-${pad(fecha.getMonth() + 1)}-${pad(fecha.getDate())}`;
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
function fechasDeRango(startValue, endValue, bounds = null) {
    let inicio = parseFechaLocal(startValue);
    if (!inicio) return [];

    let fin = parseFechaLocal(endValue);
    if (!fin || fin < inicio) {
        fin = new Date(inicio);
    } else {
        const textoFin = String(endValue ?? '').replace(' ', 'T');
        if (!/T\d{2}:\d{2}/.test(textoFin)) {
            fin.setDate(fin.getDate() - 1);
        }
        if (fin < inicio) fin.setTime(inicio.getTime());
    }

    // Solo interesan las fechas dentro del rango visible: evita expandir
    // rangos con fechas inválidas (p. ej. años con ceros de más) que antes
    // generaban cientos de miles de entradas y congelaban la vista.
    if (bounds?.start && bounds?.end) {
        const bInicio = parseFechaLocal(bounds.start);
        const bFin = parseFechaLocal(bounds.end);
        if (bInicio && inicio < bInicio) {
            inicio = new Date(bInicio);
        }
        if (bFin) {
            // FullCalendar entrega `end` como exclusivo.
            const ultimo = new Date(bFin);
            ultimo.setDate(ultimo.getDate() - 1);
            if (fin > ultimo) fin = ultimo;
        }
        if (fin < inicio) return [];
    }

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

    (agendasCargadas.value || []).forEach((e) => {
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

        fechasDeRango(e.start, e.end, rangoVisible.value).forEach((fecha) => {
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

    if (!total) return `${base}. Sin eventos`;
    return `${base}. ${total} evento${total === 1 ? '' : 's'}. Ver todos`;
}

const subtituloHoja = computed(() => {
    const total = hoja.eventos.length;
    if (!total) return 'Sin eventos programados';
    return `${total} evento${total === 1 ? '' : 's'} programado${total === 1 ? '' : 's'}`;
});

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

/** En la vista mensual, cualquier clic sobre el día abre la lista. */
function handleDateClick(arg) {
    if (!arg.view?.type?.startsWith('dayGrid')) return;
    abrirListadoDia(toFechaISO(arg.date));
}

/** Abre el detalle del evento elegido en el bottom sheet. */
async function abrirDesdeHoja(ev) {
    hoja.show = false;
    // Marca que el detalle viene del listado: al cerrarlo se reabre la lista.
    detalle.volverHoja = true;
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
        // Refresca la lista por si el evento se actualizó mientras estaba abierto.
        hoja.eventos = eventosDelDia(hoja.fecha);
        hoja.show = true;
    }
}

/**
 * Fuente de eventos de FullCalendar: pide solo el rango visible.
 * FullCalendar la llama al montar y cada vez que se navega a otro mes/semana/día.
 */
async function cargarEventos(info, successCallback, failureCallback) {
    cargandoEventos.value = true;
    errorEventos.value = '';
    rangoVisible.value = { start: info.startStr, end: info.endStr };
    // Evita mostrar por un instante los eventos del rango anterior al navegar.
    agendasCargadas.value = [];

    try {
        const data = await AgendasService.eventosRango({
            start: info.startStr,
            end: info.endStr,
        });

        agendasCargadas.value = Array.isArray(data) ? data : [];
        successCallback(eventosCalendario(agendasCargadas.value));
    } catch (e) {
        agendasCargadas.value = [];
        errorEventos.value = 'No se pudieron cargar los eventos. Intenta de nuevo.';
        failureCallback(e);
    } finally {
        cargandoEventos.value = false;
    }
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
    // El mes crece según el contenido: cada celda muestra hasta 5 eventos y no
    // debe recortarlos. Semana/día conservan su altura fija.
    height: 'auto',
    // En el mes no se pintan las píldoras nativas: la celda usa un botón
    // agrupado con los primeros 5 eventos y el resto se ve en el modal.
    dayMaxEvents: 0,
    // En semana/día los eventos se ven como píldoras (no puntos) y la hora
    // la pinta el slot #eventContent, no FullCalendar.
    eventDisplay: 'block',
    displayEventTime: false,
    events: cargarEventos,
    dateClick: handleDateClick,
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

        <div class="agenda-calendar relative bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
            <div
                v-if="cargandoEventos"
                role="status"
                aria-live="polite"
                class="pointer-events-none absolute inset-x-0 top-16 z-20 flex justify-center px-4"
            >
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white/95 px-3 py-1.5 text-[11px] font-semibold text-slate-500 shadow-sm">
                    <Loader2 class="w-3.5 h-3.5 animate-spin" />
                    Cargando eventos…
                </span>
            </div>
            <div
                v-else-if="errorEventos"
                role="alert"
                class="absolute inset-x-0 top-16 z-20 flex justify-center px-4"
            >
                <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-[11px] font-semibold text-rose-700 shadow-sm">
                    <AlertCircle class="w-3.5 h-3.5" />
                    {{ errorEventos }}
                </span>
            </div>
            <div class="p-3 sm:p-5 max-w-[860px] mx-auto">
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
            :agenda="detalle.event"
            :can-edit="false"
            :can-evidencia="false"
            @close="cerrarDetalle"
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
            </div>
        </BaseModal>
    </AppLayout>
</template>
