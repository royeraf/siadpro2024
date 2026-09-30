<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import * as pdfjsLib from 'pdfjs-dist';
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.js?url';
import { renderAsync as renderDocxAsync } from 'docx-preview';
import * as XLSX from 'xlsx';
import { 
    Download, X, ChevronUp, ChevronDown, 
    ZoomIn, ZoomOut, AlertTriangle, FileQuestion, FileText 
} from 'lucide-vue-next';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    url: {
        type: String,
        default: '',
    },
    name: {
        type: String,
        default: '',
    },
    downloadUrl: {
        type: String,
        default: '',
    },
    pdfRoute: {
        type: String,
        default: '/visor/pdf',
    },
});

const emit = defineEmits(['close']);

// ── Extensiones conocidas ──
const IMG_EXTS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'];
const PDF_EXTS = ['pdf'];
const WORD_EXTS = ['doc', 'docx'];
const SHEET_EXTS = ['xlsx', 'xls', 'csv'];
const KNOWN_EXTS = [...PDF_EXTS, ...IMG_EXTS, ...WORD_EXTS, ...SHEET_EXTS, 'txt'];

const MAX_READING_WIDTH = 900;
const PAGE_SCROLL_GAP = 8;
const PAGE_NAV_TOLERANCE = 16;
// Duración del salto animado entre páginas. Constante para cualquier distancia:
// un salto de 40 páginas se siente igual de ágil que uno de una sola página.
const PAGE_SCROLL_MS = 400;

// ── Estado reactivo del visor ──
const contentRef = ref(null);
const bodyRef = ref(null);
const pageInputRef = ref(null);

const loading = ref(true);
const errorMsg = ref('');
const isUnsupported = ref(false);

const showZoom = ref(false);
const showPager = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const pageInputValue = ref('1');

// ── Estado interno de renderizado (no reactivo para alto rendimiento) ──
let currentSession = null;
let goToPageFn = null;
// rAF del salto animado en curso, o null. Mientras exista, el contador de página
// se congela en la página destino (ver onScroll de cada visor).
let pageScroll = null;
// Listeners de cancelación del salto, para poder retirarlos al cerrar.
let scrollCancel = null;

/* ── Salto animado entre páginas ──
   Se anima con rAF + easing en vez de `scroll-behavior: smooth` en el
   contenedor, porque así el salto se puede cancelar: si el usuario scrollea a
   mano, hace zoom o pide otra página a mitad de vuelo, el control vuelve a él
   de inmediato en vez de pelearse con el scroll del navegador. Además el
   re-anchor del zoom (que debe ser instantáneo) no se anima por accidente. */
function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function easeInOutCubic(t) {
    return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

function stopPageScroll() {
    if (!pageScroll) return;
    cancelAnimationFrame(pageScroll);
    pageScroll = null;
}

function scrollToSmooth(content, top, onDone) {
    stopPageScroll();

    // El navegador recorta solo al asignar scrollTop, pero se recorta aquí para
    // no animar un tramo que al final no exista.
    const to = Math.max(0, Math.min(top, content.scrollHeight - content.clientHeight));
    const from = content.scrollTop;

    if (Math.abs(to - from) < 1 || prefersReducedMotion()) {
        content.scrollTop = to;
        if (onDone) onDone();
        return;
    }

    const startedAt = performance.now();
    const step = (now) => {
        const t = Math.min(1, (now - startedAt) / PAGE_SCROLL_MS);
        content.scrollTop = from + (to - from) * easeInOutCubic(t);
        if (t < 1) {
            pageScroll = requestAnimationFrame(step);
            return;
        }
        pageScroll = null;
        if (onDone) onDone();
    };
    pageScroll = requestAnimationFrame(step);
}

// Si el usuario scrollea a mano, el salto animado cede el control en el acto
// (el scroll manual recalcula el contador de página).
function bindScrollCancel() {
    unbindScrollCancel();
    const c = contentRef.value;
    if (!c) return;
    const onUserScroll = () => stopPageScroll();
    c.addEventListener('wheel', onUserScroll, { passive: true });
    c.addEventListener('touchstart', onUserScroll, { passive: true });
    scrollCancel = { el: c, onUserScroll };
}

function unbindScrollCancel() {
    if (!scrollCancel) return;
    scrollCancel.el.removeEventListener('wheel', scrollCancel.onUserScroll);
    scrollCancel.el.removeEventListener('touchstart', scrollCancel.onUserScroll);
    scrollCancel = null;
}

function extOf(name = '') {
    const base = name.split(/[?#]/)[0];
    return (base.split('.').pop() || '').toLowerCase();
}

function guessExt(name, url) {
    const primary = extOf(name);
    if (KNOWN_EXTS.includes(primary)) return primary;
    try {
        const pathParam = new URL(url, window.location.origin).searchParams.get('path');
        if (pathParam) {
            const fromPath = extOf(pathParam);
            if (KNOWN_EXTS.includes(fromPath)) return fromPath;
        }
    } catch (e) { /* noop */ }
    const fromUrl = extOf(url);
    if (KNOWN_EXTS.includes(fromUrl)) return fromUrl;
    return primary;
}

function convertedPdfUrl(streamUrl) {
    if (!props.pdfRoute) return null;
    try {
        const pathParam = new URL(streamUrl, window.location.origin).searchParams.get('path');
        if (!pathParam) return null;
        return props.pdfRoute + (props.pdfRoute.includes('?') ? '&' : '?') + 'path=' + encodeURIComponent(pathParam);
    } catch (e) {
        return null;
    }
}

async function fetchArrayBuffer(url) {
    const res = await fetch(url, { credentials: 'same-origin' });
    if (!res.ok) throw new Error('No se pudo cargar el archivo (HTTP ' + res.status + ')');
    return await res.arrayBuffer();
}

function cleanup() {
    if (currentSession && currentSession.cleanupExtra) {
        try { currentSession.cleanupExtra(); } catch (e) { /* noop */ }
    }
    if (currentSession && currentSession.pdf) {
        try { currentSession.pdf.destroy(); } catch (e) { /* noop */ }
    }
    stopPageScroll();
    unbindScrollCancel();
    if (bodyRef.value) {
        bodyRef.value.innerHTML = '';
    }
    loading.value = true;
    errorMsg.value = '';
    isUnsupported.value = false;
    showZoom.value = false;
    showPager.value = false;
    goToPageFn = null;
    currentSession = null;
}

/* ──────────────────────── PDF ──────────────────────── */
async function openPdf(url) {
    const buf = await fetchArrayBuffer(url);
    const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
    const numPages = pdf.numPages;
    const firstPage = await pdf.getPage(1);
    const baseViewport = firstPage.getViewport({ scale: 1 });

    const state = { zoom: 1, baseScale: 1, current: 1, pages: [] };
    const effScale = () => state.baseScale * state.zoom;

    const computeBaseScale = () => {
        if (!bodyRef.value) return;
        const cs = getComputedStyle(bodyRef.value);
        const padX = parseFloat(cs.paddingLeft) + parseFloat(cs.paddingRight);
        const avail = Math.max(100, Math.min(bodyRef.value.clientWidth - padX, MAX_READING_WIDTH));
        state.baseScale = Math.min(avail / baseViewport.width, 4);
    };
    computeBaseScale();

    const body = bodyRef.value;
    body.innerHTML = '';
    state.pages = [];
    for (let i = 1; i <= numPages; i++) {
        const wrap = document.createElement('div');
        wrap.className = 'shadow-xl bg-white shrink-0';
        const canvas = document.createElement('canvas');
        canvas.className = 'block w-full h-full';
        wrap.appendChild(canvas);
        body.appendChild(wrap);
        state.pages.push({ index: i, wrap, canvas, rendered: false, task: null, gen: 0 });
    }
    loading.value = false;

    const sizePages = () => {
        const s = effScale();
        const w = Math.round(baseViewport.width * s);
        const h = Math.round(baseViewport.height * s);
        state.pages.forEach((p) => {
            p.wrap.style.width = w + 'px';
            p.wrap.style.height = h + 'px';
        });
    };
    sizePages();

    const renderPage = (p) => {
        if (p.rendered) return;
        p.rendered = true;
        const myGen = p.gen;
        pdf.getPage(p.index).then((pageData) => {
            if (p.gen !== myGen) return;
            const viewport = pageData.getViewport({ scale: effScale() });
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            const canvas = p.canvas;
            canvas.width = Math.floor(viewport.width * dpr);
            canvas.height = Math.floor(viewport.height * dpr);
            const ctx = canvas.getContext('2d');
            const task = pageData.render({
                canvasContext: ctx,
                viewport,
                transform: dpr !== 1 ? [dpr, 0, 0, dpr, 0, 0] : undefined,
            });
            p.task = task;
            return task.promise;
        }).catch((e) => {
            if (p.gen !== myGen) return;
            if (e && e.name === 'RenderingCancelledException') { p.rendered = false; return; }
            p.wrap.innerHTML = '<div class="text-red-600 text-xs p-4 text-center">Error al renderizar la página.</div>';
        });
    };

    const renderVisiblePages = () => {
        if (!contentRef.value) return;
        const croot = contentRef.value.getBoundingClientRect();
        state.pages.forEach((p) => {
            const rect = p.wrap.getBoundingClientRect();
            if (rect.bottom > croot.top - 400 && rect.top < croot.bottom + 400) renderPage(p);
        });
    };

    const renderObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const p = state.pages.find((pp) => pp.wrap === entry.target);
            if (p) renderPage(p);
        });
    }, { root: contentRef.value, rootMargin: '400px 0px' });
    state.pages.forEach((p) => renderObserver.observe(p.wrap));
    renderVisiblePages();

    const setCurrentPage = (n, scrollTo) => {
        state.current = n;
        currentPage.value = n;
        pageInputValue.value = String(n);
        if (scrollTo && contentRef.value) {
            const p = state.pages[n - 1];
            if (p) scrollToSmooth(contentRef.value, p.wrap.offsetTop - PAGE_SCROLL_GAP, updateCurrentPageFromScroll);
        }
    };

    let scrollRAF = null;
    const updateCurrentPageFromScroll = () => {
        scrollRAF = null;
        if (!contentRef.value) return;
        const top = contentRef.value.scrollTop;
        let idx = 0;
        for (let i = 0; i < state.pages.length; i++) {
            if (state.pages[i].wrap.offsetTop <= top + PAGE_NAV_TOLERANCE) idx = i; else break;
        }
        if (top + contentRef.value.clientHeight >= contentRef.value.scrollHeight - 2) {
            idx = state.pages.length - 1;
        }
        setCurrentPage(idx + 1, false);
    };

    const onScroll = () => {
        if (scrollRAF || pageScroll) return;
        scrollRAF = requestAnimationFrame(updateCurrentPageFromScroll);
    };
    contentRef.value.addEventListener('scroll', onScroll);

    totalPages.value = numPages;

    const rezoom = (newZoom) => {
        if (!contentRef.value) return;
        stopPageScroll();
        const anchor = state.pages[state.current - 1];
        const rel = anchor ? (contentRef.value.scrollTop - anchor.wrap.offsetTop) / (anchor.wrap.offsetHeight || 1) : 0;
        state.zoom = newZoom;
        state.pages.forEach((p) => {
            p.gen++;
            if (p.task && p.task.cancel) { try { p.task.cancel(); } catch (e) { /* noop */ } }
            p.rendered = false;
        });
        sizePages();
        if (anchor) contentRef.value.scrollTop = anchor.wrap.offsetTop + rel * anchor.wrap.offsetHeight;
        renderVisiblePages();
    };

    const onZoomIn = () => rezoom(Math.min(5, +(state.zoom + 0.25).toFixed(2)));
    const onZoomOut = () => rezoom(Math.max(0.25, +(state.zoom - 0.25).toFixed(2)));

    let resizeTimer = null;
    const onResize = () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            computeBaseScale();
            rezoom(state.zoom);
        }, 150);
    };
    window.addEventListener('resize', onResize);

    currentSession = {
        type: 'pdf',
        pdf,
        zoomIn: onZoomIn,
        zoomOut: onZoomOut,
        cleanupExtra: () => {
            renderObserver.disconnect();
            if (contentRef.value) contentRef.value.removeEventListener('scroll', onScroll);
            stopPageScroll();
            if (scrollRAF) cancelAnimationFrame(scrollRAF);
            window.removeEventListener('resize', onResize);
            clearTimeout(resizeTimer);
            state.pages.forEach((p) => { if (p.task && p.task.cancel) { try { p.task.cancel(); } catch (e) { /* noop */ } } });
        },
    };

    goToPageFn = (n) => setCurrentPage(n, true);
    showPager.value = numPages > 1;
    showZoom.value = true;
    setCurrentPage(1, false);
}

/* ──────────────────────── Imágenes ──────────────────────── */
function openImage(url, name) {
    const body = bodyRef.value;
    body.innerHTML = '';
    const img = new Image();
    img.alt = name;
    img.src = url;
    img.className = 'object-contain rounded shadow-lg bg-white transition-transform duration-150';
    body.appendChild(img);

    const fit = () => {
        if (!contentRef.value || !bodyRef.value) return;
        const cs = getComputedStyle(bodyRef.value);
        const padX = parseFloat(cs.paddingLeft) + parseFloat(cs.paddingRight);
        const padY = parseFloat(cs.paddingTop) + parseFloat(cs.paddingBottom);
        img.style.maxWidth = Math.max(100, contentRef.value.clientWidth - padX) + 'px';
        img.style.maxHeight = Math.max(100, contentRef.value.clientHeight - padY) + 'px';
    };
    img.onload = () => { loading.value = false; fit(); };
    fit();

    let resizeTimer = null;
    const onResize = () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(fit, 150); };
    window.addEventListener('resize', onResize);

    const state = { scale: 1 };
    const apply = () => { img.style.transform = `scale(${state.scale})`; };
    const onZoomOut = () => { state.scale = Math.max(0.2, +(state.scale - 0.25).toFixed(2)); apply(); };
    const onZoomIn = () => { state.scale = Math.min(5, +(state.scale + 0.25).toFixed(2)); apply(); };

    currentSession = {
        type: 'image',
        zoomIn: onZoomIn,
        zoomOut: onZoomOut,
        cleanupExtra: () => { window.removeEventListener('resize', onResize); clearTimeout(resizeTimer); },
    };
    showPager.value = false;
    showZoom.value = true;
    apply();
}

/* ──────────────────────── Word (docx) ──────────────────────── */
async function openDocx(url) {
    const buf = await fetchArrayBuffer(url);
    const body = bodyRef.value;
    body.innerHTML = '';

    const host = document.createElement('div');
    const styleEl = document.createElement('style');
    body.appendChild(styleEl);
    body.appendChild(host);

    await renderDocxAsync(buf, host, styleEl, {
        className: 'docx',
        inWrapper: true,
        ignoreWidth: false,
        ignoreHeight: false,
        breakPages: true,
        renderHeaders: true,
        renderFooters: true,
        renderFootnotes: true,
        renderEndnotes: true,
        useBase64URL: true,
        experimental: true,
    });
    loading.value = false;

    const wrapperEl = host.querySelector('.docx-wrapper');
    if (!wrapperEl) {
        body.innerHTML = '<div class="text-red-600 p-6 text-center">No se pudo renderizar el documento.</div>';
        currentSession = { type: 'docx' };
        showPager.value = false;
        showZoom.value = false;
        return;
    }
    wrapperEl.style.background = 'transparent';
    wrapperEl.style.padding = '0';

    const pages = [...wrapperEl.querySelectorAll(':scope > section.docx')];
    const numPages = pages.length || 1;

    const wrapRect = wrapperEl.getBoundingClientRect();
    const naturalWidth = wrapRect.width;
    const naturalHeight = wrapRect.height;
    const pageTops = pages.map((p) => p.getBoundingClientRect().top - wrapRect.top);
    wrapperEl.style.width = naturalWidth + 'px';
    wrapperEl.style.height = naturalHeight + 'px';

    const state = { zoom: 1, baseScale: 1, current: 1 };

    const computeBaseScale = () => {
        if (!bodyRef.value) return;
        const cs = getComputedStyle(bodyRef.value);
        const padX = parseFloat(cs.paddingLeft) + parseFloat(cs.paddingRight);
        const avail = Math.max(100, Math.min(bodyRef.value.clientWidth - padX, MAX_READING_WIDTH));
        state.baseScale = Math.min(avail / naturalWidth, 2);
    };
    computeBaseScale();

    const applyScale = () => {
        const s = state.baseScale * state.zoom;
        host.style.width = Math.round(naturalWidth * s) + 'px';
        host.style.height = Math.round(naturalHeight * s) + 'px';
        wrapperEl.style.transformOrigin = 'top left';
        wrapperEl.style.transform = `scale(${s})`;
    };
    applyScale();

    // smooth = true solo en navegación explícita de página (botones, flechas,
    // input). El re-anchor tras un resize debe ser instantáneo: si no, el
    // documento se mueve solo mientras el usuario solo cambió el tamaño de la
    // ventana.
    const scrollToPage = (n, smooth) => {
        if (!contentRef.value) return;
        const s = state.baseScale * state.zoom;
        const top = host.offsetTop + pageTops[n - 1] * s - PAGE_SCROLL_GAP;
        if (!smooth) {
            stopPageScroll();
            contentRef.value.scrollTop = top;
            return;
        }
        scrollToSmooth(contentRef.value, top, updateCurrentPageFromScroll);
    };

    const setCurrentPage = (n, scrollTo) => {
        state.current = n;
        currentPage.value = n;
        pageInputValue.value = String(n);
        if (scrollTo) scrollToPage(n, true);
    };

    let scrollRAF = null;
    const updateCurrentPageFromScroll = () => {
        scrollRAF = null;
        if (!contentRef.value) return;
        const s = state.baseScale * state.zoom;
        const top = contentRef.value.scrollTop;
        let idx = 0;
        for (let i = 0; i < pages.length; i++) {
            if (host.offsetTop + pageTops[i] * s <= top + PAGE_NAV_TOLERANCE) idx = i; else break;
        }
        if (top + contentRef.value.clientHeight >= contentRef.value.scrollHeight - 2) {
            idx = pages.length - 1;
        }
        setCurrentPage(idx + 1, false);
    };
    const onScroll = () => {
        if (scrollRAF || pageScroll) return;
        scrollRAF = requestAnimationFrame(updateCurrentPageFromScroll);
    };
    contentRef.value.addEventListener('scroll', onScroll);

    totalPages.value = numPages;

    const zoomTo = (newZoom) => {
        if (!contentRef.value) return;
        const sOld = state.baseScale * state.zoom;
        stopPageScroll();
        const rel = (contentRef.value.scrollTop - host.offsetTop) / (naturalHeight * sOld || 1);
        state.zoom = newZoom;
        applyScale();
        contentRef.value.scrollTop = host.offsetTop + rel * naturalHeight * state.baseScale * state.zoom;
    };
    const onZoomOut = () => zoomTo(Math.max(0.5, +(state.zoom - 0.25).toFixed(2)));
    const onZoomIn = () => zoomTo(Math.min(3, +(state.zoom + 0.25).toFixed(2)));

    let resizeTimer = null;
    const onResize = () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            computeBaseScale();
            applyScale();
            scrollToPage(state.current);
        }, 150);
    };
    window.addEventListener('resize', onResize);

    currentSession = {
        type: 'docx',
        zoomIn: onZoomIn,
        zoomOut: onZoomOut,
        cleanupExtra: () => {
            if (contentRef.value) contentRef.value.removeEventListener('scroll', onScroll);
            stopPageScroll();
            if (scrollRAF) cancelAnimationFrame(scrollRAF);
            window.removeEventListener('resize', onResize);
            clearTimeout(resizeTimer);
        },
    };

    goToPageFn = (n) => setCurrentPage(n, true);
    showPager.value = numPages > 1;
    showZoom.value = true;
    setCurrentPage(1, false);
}

async function openWord(url) {
    const pdfUrl = convertedPdfUrl(url);
    if (pdfUrl) {
        try {
            return await openPdf(pdfUrl);
        } catch (e) {
            cleanup();
        }
    }
    return await openDocx(url);
}

/* ──────────────────────── Excel / CSV ──────────────────────── */
function sheetToTable(ws) {
    const html = XLSX.utils.sheet_to_html(ws, { header: '', footer: '' });
    const wrap = document.createElement('div');
    wrap.innerHTML = html;
    const table = wrap.querySelector('table');
    if (table) {
        table.classList.add('fv-xlsx-table', 'bg-white', 'shadow-xl', 'rounded', 'p-2', 'text-xs', 'sm:text-sm', 'mx-auto');
        table.style.borderCollapse = 'collapse';
        table.style.width = 'auto';
    }
    return table || wrap;
}

async function openSheet(url) {
    const buf = await fetchArrayBuffer(url);
    const wb = XLSX.read(buf, { type: 'array' });
    const sheets = wb.SheetNames;
    const state = { idx: 0, scale: 1 };

    const render = () => {
        const body = bodyRef.value;
        body.innerHTML = '';
        const ws = wb.Sheets[sheets[state.idx]];
        const tbl = sheetToTable(ws);
        const holder = document.createElement('div');
        holder.className = 'origin-top transition-transform duration-150';
        holder.style.transform = `scale(${state.scale})`;
        holder.appendChild(tbl);
        body.appendChild(holder);
        loading.value = false;
        currentPage.value = state.idx + 1;
        pageInputValue.value = String(state.idx + 1);
        totalPages.value = sheets.length;
    };

    const zoomTo = (newScale) => {
        if (!contentRef.value) return;
        const rel = contentRef.value.scrollHeight ? contentRef.value.scrollTop / contentRef.value.scrollHeight : 0;
        state.scale = newScale;
        render();
        contentRef.value.scrollTop = rel * contentRef.value.scrollHeight;
    };
    const onZoomOut = () => zoomTo(Math.max(0.4, +(state.scale - 0.15).toFixed(2)));
    const onZoomIn = () => zoomTo(Math.min(4, +(state.scale + 0.15).toFixed(2)));

    currentSession = {
        type: 'sheet',
        zoomIn: onZoomIn,
        zoomOut: onZoomOut,
    };
    goToPageFn = (n) => { state.idx = n - 1; render(); };
    showPager.value = sheets.length > 1;
    showZoom.value = true;
    render();
}

/* ──────────────────────── Texto ──────────────────────── */
async function openText(url) {
    const res = await fetch(url, { credentials: 'same-origin' });
    const text = await res.text();
    const body = bodyRef.value;
    body.innerHTML = '';
    const pre = document.createElement('pre');
    pre.className = 'bg-white shadow-xl rounded p-4 sm:p-6 w-full max-w-4xl mx-auto text-xs sm:text-sm whitespace-pre-wrap break-words text-gray-800 overflow-auto';
    pre.textContent = text;
    body.appendChild(pre);
    loading.value = false;

    const state = { scale: 1 };
    const apply = () => { pre.style.fontSize = `${state.scale * 100}%`; };
    const onZoomOut = () => { state.scale = Math.max(0.5, +(state.scale - 0.1).toFixed(2)); apply(); };
    const onZoomIn = () => { state.scale = Math.min(3, +(state.scale + 0.1).toFixed(2)); apply(); };

    currentSession = {
        type: 'text',
        zoomIn: onZoomIn,
        zoomOut: onZoomOut,
    };
    showPager.value = false;
    showZoom.value = true;
    apply();
}

/* ──────────────────────── Control maestro de apertura ──────────────────────── */
async function loadFile() {
    if (!props.show || !props.url) return;

    cleanup();
    loading.value = true;
    errorMsg.value = '';

    await nextTick();
    if (!bodyRef.value) return;
    bindScrollCancel();

    const ext = guessExt(props.name, props.url);

    try {
        if (PDF_EXTS.includes(ext)) {
            await openPdf(props.url);
        } else if (IMG_EXTS.includes(ext)) {
            openImage(props.url, props.name);
        } else if (WORD_EXTS.includes(ext)) {
            await openWord(props.url);
        } else if (SHEET_EXTS.includes(ext)) {
            await openSheet(props.url);
        } else if (ext === 'txt') {
            await openText(props.url);
        } else {
            loading.value = false;
            isUnsupported.value = true;
        }
    } catch (err) {
        loading.value = false;
        errorMsg.value = (err && err.message) || 'Error al abrir el archivo.';
    }
}

watch(() => props.show, (val) => {
    if (val) {
        document.body.style.overflow = 'hidden';
        loadFile();
    } else {
        document.body.style.overflow = '';
        cleanup();
    }
});

watch(() => props.url, () => {
    if (props.show) {
        loadFile();
    }
});

// ── Paginación y Zoom ──
function handlePrev() {
    if (currentPage.value > 1 && goToPageFn) {
        goToPageFn(currentPage.value - 1);
    }
}

function handleNext() {
    if (currentPage.value < totalPages.value && goToPageFn) {
        goToPageFn(currentPage.value + 1);
    }
}

function handlePageInputEnter() {
    const n = parseInt(pageInputValue.value, 10);
    if (goToPageFn && !isNaN(n) && n >= 1 && n <= totalPages.value) {
        goToPageFn(n);
    } else {
        pageInputValue.value = String(currentPage.value);
    }
    if (pageInputRef.value) pageInputRef.value.blur();
}

function handleZoomIn() {
    if (currentSession && currentSession.zoomIn) currentSession.zoomIn();
}

function handleZoomOut() {
    if (currentSession && currentSession.zoomOut) currentSession.zoomOut();
}

// ── Atajos de teclado ──
function handleKeydown(e) {
    if (!props.show) return;

    if (e.key === 'Escape') {
        emit('close');
        return;
    }

    const tag = (e.target && e.target.tagName) || '';
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || (e.target && e.target.isContentEditable)) return;

    if (!goToPageFn) return;

    if (e.key === 'ArrowDown' || e.key === 'PageDown') {
        e.preventDefault();
        handleNext();
    } else if (e.key === 'ArrowUp' || e.key === 'PageUp') {
        e.preventDefault();
        handlePrev();
    } else if (e.key === 'Home') {
        e.preventDefault();
        goToPageFn(1);
    } else if (e.key === 'End') {
        e.preventDefault();
        goToPageFn(totalPages.value);
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    if (props.show) {
        document.body.style.overflow = 'hidden';
        loadFile();
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
    cleanup();
    unbindScrollCancel();
});
</script>

<template>
    <div 
        v-if="show"
        class="fixed inset-0 z-[9999] overflow-hidden" 
        role="dialog" 
        aria-modal="true" 
        :aria-label="name || 'Visualizador de archivos'"
    >
        <!-- Backdrop -->
        <div 
            class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity duration-200"
            @click="emit('close')"
        ></div>

        <!-- Panel principal: bottom-sheet en móvil, pantalla completa en desktop -->
        <div
            class="absolute inset-x-0 bottom-0 top-0 sm:inset-0 bg-gray-100 flex flex-col overflow-hidden animate-in fade-in duration-200"
            @click.stop
        >
            <!-- Barra de herramientas superior -->
            <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-5 py-3 sm:py-4 bg-gray-900 text-white shrink-0 flex-wrap z-20 shadow-md">
                <div class="flex items-center gap-2 min-w-0 max-w-[60vw] sm:max-w-[70vw]">
                    <FileText class="w-4 h-4 text-indigo-400 shrink-0" />
                    <span class="text-sm sm:text-base font-semibold truncate text-slate-100" :title="name">
                        {{ name || 'Visualizador de Documento' }}
                    </span>
                </div>

                <div class="ml-auto flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <!-- Botón Descargar -->
                    <a 
                        v-if="downloadUrl || url"
                        :href="downloadUrl || url" 
                        :download="name"
                        title="Descargar archivo"
                        class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-white transition-colors inline-flex items-center justify-center text-decoration-none"
                    >
                        <Download class="w-5 h-5" />
                    </a>

                    <!-- Botón Cerrar -->
                    <button 
                        type="button" 
                        title="Cerrar (Esc)"
                        class="p-2 rounded-lg bg-gray-700 hover:bg-red-600 text-white transition-colors inline-flex items-center justify-center cursor-pointer border-0 outline-none"
                        @click="emit('close')"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Área de contenido con scroll -->
            <div ref="contentRef" class="flex-1 min-h-0 overflow-auto bg-gray-200 relative select-none">
                <!-- Spinner de carga -->
                <div 
                    v-if="loading"
                    class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-gray-500 z-10 bg-gray-200/80 backdrop-blur-xs"
                >
                    <svg class="animate-spin w-8 h-8 text-blue-600" width="32" height="32" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="text-sm font-medium text-slate-600">Cargando archivo…</span>
                </div>

                <!-- Contenedor del documento renderizado -->
                <div 
                    ref="bodyRef" 
                    class="min-h-full flex flex-col items-center gap-4 p-2 sm:p-4"
                ></div>

                <!-- Mensaje de error al abrir -->
                <div 
                    v-if="errorMsg && !loading"
                    class="bg-white shadow-xl rounded-2xl p-8 max-w-md mx-auto my-12 text-center"
                >
                    <AlertTriangle class="w-14 h-14 text-rose-500 mx-auto mb-4" />
                    <p class="text-slate-700 font-semibold mb-2">Error al abrir el archivo</p>
                    <p class="text-xs text-slate-500 mb-5 leading-relaxed">{{ errorMsg }}</p>
                    <a 
                        v-if="downloadUrl || url"
                        :href="downloadUrl || url" 
                        :download="name"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-sm transition-all"
                    >
                        <Download class="w-4 h-4" /> Descargar archivo
                    </a>
                </div>

                <!-- Formato no soportado -->
                <div 
                    v-if="isUnsupported && !loading"
                    class="bg-white shadow-xl rounded-2xl p-8 max-w-md mx-auto my-12 text-center"
                >
                    <FileQuestion class="w-14 h-14 text-slate-400 mx-auto mb-4" />
                    <h5 class="text-slate-800 font-bold text-sm mb-2">Previsualización no disponible</h5>
                    <p class="text-xs text-slate-500 mb-5 leading-relaxed">
                        Este formato no se puede previsualizar directamente en el navegador. Puedes descargarlo para abrirlo con la aplicación correspondiente.
                    </p>
                    <a 
                        v-if="downloadUrl || url"
                        :href="downloadUrl || url" 
                        :download="name"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-sm transition-all"
                    >
                        <Download class="w-4 h-4" /> Descargar
                    </a>
                </div>
            </div>

            <!-- Panel flotante de controles: Paginación y Zoom (fijo sobre el contenido) -->
            <div 
                v-if="showZoom || showPager"
                class="flex flex-col items-center gap-1 absolute bottom-4 right-4 z-30 bg-gray-900/90 backdrop-blur-md border border-gray-600/80 rounded-xl p-1.5 text-white shadow-2xl"
            >
                <!-- Paginador -->
                <div v-if="showPager" class="flex flex-col items-center gap-1">
                    <input 
                        ref="pageInputRef"
                        type="text" 
                        inputmode="numeric" 
                        v-model="pageInputValue"
                        @keydown.enter="handlePageInputEnter"
                        title="Ir a la página (Enter)"
                        class="w-9 h-8 text-center text-xs font-bold bg-gray-800 border border-gray-500 rounded text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <span class="text-[11px] text-gray-300 font-semibold tabular-nums">
                        {{ totalPages }}
                    </span>
                    <div class="w-6 border-t border-gray-600 my-1"></div>
                    <button 
                        type="button" 
                        title="Página anterior (Flecha Arriba)"
                        :disabled="currentPage <= 1"
                        @click="handlePrev"
                        class="p-1.5 rounded hover:bg-gray-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer border-0 text-white"
                    >
                        <ChevronUp class="w-4 h-4" />
                    </button>
                    <button 
                        type="button" 
                        title="Página siguiente (Flecha Abajo)"
                        :disabled="currentPage >= totalPages"
                        @click="handleNext"
                        class="p-1.5 rounded hover:bg-gray-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors cursor-pointer border-0 text-white"
                    >
                        <ChevronDown class="w-4 h-4" />
                    </button>
                    <div class="w-6 border-t border-gray-600 my-1"></div>
                </div>

                <!-- Botones de Zoom -->
                <div v-if="showZoom" class="flex flex-col items-center gap-1">
                    <button 
                        type="button" 
                        title="Acercar (+)"
                        @click="handleZoomIn"
                        class="p-1.5 rounded hover:bg-gray-700 transition-colors cursor-pointer border-0 text-white"
                    >
                        <ZoomIn class="w-4 h-4" />
                    </button>
                    <button 
                        type="button" 
                        title="Alejar (-)"
                        @click="handleZoomOut"
                        class="p-1.5 rounded hover:bg-gray-700 transition-colors cursor-pointer border-0 text-white"
                    >
                        <ZoomOut class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.fv-xlsx-table) {
    background-color: white;
    border-radius: 8px;
    border-collapse: collapse;
}

:deep(.fv-xlsx-table td),
:deep(.fv-xlsx-table th) {
    border: 1px solid #cbd5e1;
    padding: 6px 12px;
    white-space: nowrap;
    font-size: 12px;
    color: #334155;
}

:deep(.fv-xlsx-table th) {
    background-color: #f1f5f9;
    font-weight: 700;
}

:deep(.docx-wrapper) {
    background: transparent !important;
    padding: 0 !important;
}

:deep(.docx) {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    background: white;
    margin-bottom: 16px;
}
</style>
