import * as pdfjsLib from 'pdfjs-dist';
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.js?url';
import { renderAsync as renderDocxAsync } from 'docx-preview';
import * as XLSX from 'xlsx';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

const IMG_EXTS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'];
const PDF_EXTS = ['pdf'];
const DOC_EXTS = ['docx'];
const SHEET_EXTS = ['xlsx', 'xls', 'csv'];
// Ancho máximo de "hoja" al ajustar al ancho disponible (PDF/Word). En pantallas
// angostas nunca se activa (el ancho disponible ya es menor); en desktop evita
// que la página se estire de borde a borde, dejando márgenes a los costados
// como en el visor nativo de PDF de Chrome.
const MAX_READING_WIDTH = 900;

let current = null;
let goToPage = null;

function el(id) {
    return document.getElementById(id);
}

const els = () => ({
    root: el('file-viewer'),
    backdrop: el('fv-backdrop'),
    panel: el('fv-panel'),
    title: el('fv-title'),
    vtools: el('fv-vtools'),
    pager: el('fv-pager'),
    prev: el('fv-prev'),
    next: el('fv-next'),
    pageInput: el('fv-page-input'),
    pageTotal: el('fv-page-total'),
    zoomOut: el('fv-zoom-out'),
    zoomIn: el('fv-zoom-in'),
    download: el('fv-download'),
    close: el('fv-close'),
    content: el('fv-content'),
    loading: el('fv-loading'),
    body: el('fv-body'),
});

function extOf(name = '') {
    const base = name.split(/[?#]/)[0];
    return (base.split('.').pop() || '').toLowerCase();
}

function isMobile() {
    return window.matchMedia('(max-width: 639px)').matches;
}

async function fetchArrayBuffer(url) {
    const res = await fetch(url, { credentials: 'same-origin' });
    if (!res.ok) throw new Error('No se pudo cargar el archivo (HTTP ' + res.status + ')');
    const buf = await res.arrayBuffer();
    return buf;
}

/* ------------------------------ PDF ------------------------------ */
// Visor continuo: todas las páginas se apilan verticalmente, ajustadas al
// ancho disponible (con margen), y se renderizan de forma perezosa a medida
// que entran en el viewport.
async function openPdf(url, name) {
    const buf = await fetchArrayBuffer(url);
    const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
    const numPages = pdf.numPages;
    const firstPage = await pdf.getPage(1);
    const baseViewport = firstPage.getViewport({ scale: 1 });

    const state = { zoom: 1, baseScale: 1, current: 1, pages: [] };
    const effScale = () => state.baseScale * state.zoom;

    const computeBaseScale = () => {
        const bodyEl = els().body;
        const cs = getComputedStyle(bodyEl);
        const padX = parseFloat(cs.paddingLeft) + parseFloat(cs.paddingRight);
        const avail = Math.max(100, Math.min(bodyEl.clientWidth - padX, MAX_READING_WIDTH));
        state.baseScale = Math.min(avail / baseViewport.width, 4);
    };
    computeBaseScale();

    const body = els().body;
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
    hideLoading();

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

    // Cada página lleva un token de "generación": si el zoom cambia mientras
    // una petición pdf.getPage()/render() está en vuelo, esa respuesta llega
    // desactualizada (stale) y se descarta en vez de pintar sobre el canvas
    // ya reutilizado por un render más reciente.
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
        const croot = els().content.getBoundingClientRect();
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
    }, { root: els().content, rootMargin: '400px 0px' });
    state.pages.forEach((p) => renderObserver.observe(p.wrap));
    renderVisiblePages();

    const setCurrentPage = (n, scrollTo) => {
        state.current = n;
        if (document.activeElement !== els().pageInput) {
            els().pageInput.value = n;
        }
        els().prev.disabled = n <= 1;
        els().next.disabled = n >= numPages;
        if (scrollTo) {
            const p = state.pages[n - 1];
            if (p) els().content.scrollTop = p.wrap.offsetTop - 8;
        }
    };

    // Contador de página en base a la posición de scroll (no a intersección):
    // con IntersectionObserver, cuando dos páginas cruzan su umbral casi al
    // mismo tiempo el "más visible" salta de una a otra varias veces durante
    // un mismo scroll. Aquí la página actual es simplemente la última cuyo
    // borde superior ya pasó la parte de arriba del área visible: un único
    // valor determinístico, sin parpadeo.
    let scrollRAF = null;
    const updateCurrentPageFromScroll = () => {
        scrollRAF = null;
        const top = els().content.scrollTop;
        let idx = 0;
        for (let i = 0; i < state.pages.length; i++) {
            if (state.pages[i].wrap.offsetTop <= top + 4) idx = i; else break;
        }
        setCurrentPage(idx + 1, false);
    };
    const onScroll = () => {
        if (scrollRAF) return;
        scrollRAF = requestAnimationFrame(updateCurrentPageFromScroll);
    };
    els().content.addEventListener('scroll', onScroll);

    els().pageTotal.textContent = numPages;

    els().prev.onclick = () => { if (state.current > 1) setCurrentPage(state.current - 1, true); };
    els().next.onclick = () => { if (state.current < numPages) setCurrentPage(state.current + 1, true); };

    const rezoom = (newZoom) => {
        state.zoom = newZoom;
        const anchor = state.pages[state.current - 1];
        state.pages.forEach((p) => {
            p.gen++;
            if (p.task && p.task.cancel) { try { p.task.cancel(); } catch (e) { /* noop */ } }
            p.rendered = false;
        });
        sizePages();
        if (anchor) els().content.scrollTop = anchor.wrap.offsetTop - 8;
        renderVisiblePages();
    };
    els().zoomOut.onclick = () => rezoom(Math.max(0.25, +(state.zoom - 0.25).toFixed(2)));
    els().zoomIn.onclick = () => rezoom(Math.min(5, +(state.zoom + 0.25).toFixed(2)));

    let resizeTimer = null;
    const onResize = () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            computeBaseScale();
            rezoom(state.zoom);
        }, 150);
    };
    window.addEventListener('resize', onResize);

    current = {
        ...current,
        type: 'pdf',
        pdf,
        cleanupExtra: () => {
            renderObserver.disconnect();
            els().content.removeEventListener('scroll', onScroll);
            if (scrollRAF) cancelAnimationFrame(scrollRAF);
            window.removeEventListener('resize', onResize);
            clearTimeout(resizeTimer);
            state.pages.forEach((p) => { if (p.task && p.task.cancel) { try { p.task.cancel(); } catch (e) { /* noop */ } } });
        },
    };

    showPager(true, numPages > 1, (n) => setCurrentPage(n, true));
    showZoom(true);
    setCurrentPage(1, false);
}

/* ---------------------------- Imágenes ---------------------------- */
function openImage(url, name) {
    const body = els().body;
    body.innerHTML = '';
    const img = new Image();
    img.alt = name;
    img.src = url;
    img.className = 'object-contain rounded shadow-lg bg-white transition-transform duration-150';
    body.appendChild(img);

    // Ajusta la imagen al espacio disponible (con margen) en vez de a su
    // tamaño de píxeles nativo. Se recalcula en cada resize/rotación.
    const fit = () => {
        const c = els().content;
        const cs = getComputedStyle(els().body);
        const padX = parseFloat(cs.paddingLeft) + parseFloat(cs.paddingRight);
        const padY = parseFloat(cs.paddingTop) + parseFloat(cs.paddingBottom);
        img.style.maxWidth = Math.max(100, c.clientWidth - padX) + 'px';
        img.style.maxHeight = Math.max(100, c.clientHeight - padY) + 'px';
    };
    img.onload = () => { els().loading.classList.add('hidden'); fit(); };
    fit();

    let resizeTimer = null;
    const onResize = () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(fit, 150); };
    window.addEventListener('resize', onResize);

    const state = { scale: 1 };
    const apply = () => { img.style.transform = `scale(${state.scale})`; };
    els().zoomOut.onclick = () => { state.scale = Math.max(0.2, +(state.scale - 0.25).toFixed(2)); apply(); };
    els().zoomIn.onclick = () => { state.scale = Math.min(5, +(state.scale + 0.25).toFixed(2)); apply(); };

    current = {
        ...current,
        type: 'image',
        img,
        state,
        cleanupExtra: () => { window.removeEventListener('resize', onResize); clearTimeout(resizeTimer); },
    };
    showPager(false, false, null);
    showZoom(true);
    apply();
}

/* --------------------------- Word (docx) --------------------------- */
// Render fiel al tamaño de papel real (carta/A4, márgenes, saltos de página)
// vía docx-preview, en vez de reflow de HTML plano. Reutiliza el mismo patrón
// de "ajustar al ancho + contador de página por scroll" que openPdf().
async function openDocx(url, name) {
    const buf = await fetchArrayBuffer(url);
    const body = els().body;
    body.innerHTML = '';

    const host = document.createElement('div'); // tamaño fijo, reserva el espacio; no se transforma
    const styleEl = document.createElement('style'); // styleContainer que exige renderAsync
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
    hideLoading();

    const wrapperEl = host.querySelector('.docx-wrapper');
    if (!wrapperEl) {
        body.innerHTML = '<div class="text-red-600 p-6 text-center">No se pudo renderizar el documento.</div>';
        current = { ...current, type: 'docx' };
        showPager(false, false, null);
        showZoom(false);
        return;
    }
    // El wrapper de docx-preview ya trae fondo/padding propios; los quitamos
    // porque #fv-content/#fv-body ya dan ese marco visual.
    wrapperEl.style.background = 'transparent';
    wrapperEl.style.padding = '0';

    const pages = [...wrapperEl.querySelectorAll(':scope > section.docx')];
    const numPages = pages.length || 1;

    // Métricas naturales (scale=1, antes de transformar nada).
    const wrapRect = wrapperEl.getBoundingClientRect();
    const naturalWidth = wrapRect.width;
    const naturalHeight = wrapRect.height;
    const pageTops = pages.map((p) => p.getBoundingClientRect().top - wrapRect.top);
    // Fija el tamaño natural del wrapper: si no, al ponerle un ancho explícito
    // a `host` (abajo) el wrapper (bloque normal, width:auto) se estira para
    // llenarlo y el transform:scale() escala ESE ancho ya estirado (doble
    // escalado → la página se ve más ancha que la pantalla).
    wrapperEl.style.width = naturalWidth + 'px';
    wrapperEl.style.height = naturalHeight + 'px';

    const state = { zoom: 1, baseScale: 1, current: 1 };

    const computeBaseScale = () => {
        const cs = getComputedStyle(body);
        const padX = parseFloat(cs.paddingLeft) + parseFloat(cs.paddingRight);
        const avail = Math.max(100, Math.min(body.clientWidth - padX, MAX_READING_WIDTH));
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

    const scrollToPage = (n) => {
        const s = state.baseScale * state.zoom;
        els().content.scrollTop = host.offsetTop + pageTops[n - 1] * s - 8;
    };

    const setCurrentPage = (n, scrollTo) => {
        state.current = n;
        if (document.activeElement !== els().pageInput) {
            els().pageInput.value = n;
        }
        els().prev.disabled = n <= 1;
        els().next.disabled = n >= numPages;
        if (scrollTo) scrollToPage(n);
    };

    // Igual que en openPdf(): contador basado en la posición de scroll, no en
    // IntersectionObserver, para que no salte entre páginas mientras se hace
    // scroll (dos páginas cruzando su umbral casi a la vez causaban parpadeo).
    let scrollRAF = null;
    const updateCurrentPageFromScroll = () => {
        scrollRAF = null;
        const s = state.baseScale * state.zoom;
        const top = els().content.scrollTop;
        let idx = 0;
        for (let i = 0; i < pages.length; i++) {
            if (host.offsetTop + pageTops[i] * s <= top + 4) idx = i; else break;
        }
        setCurrentPage(idx + 1, false);
    };
    const onScroll = () => {
        if (scrollRAF) return;
        scrollRAF = requestAnimationFrame(updateCurrentPageFromScroll);
    };
    els().content.addEventListener('scroll', onScroll);

    els().pageTotal.textContent = numPages;
    els().prev.onclick = () => { if (state.current > 1) setCurrentPage(state.current - 1, true); };
    els().next.onclick = () => { if (state.current < numPages) setCurrentPage(state.current + 1, true); };
    els().zoomOut.onclick = () => { state.zoom = Math.max(0.5, +(state.zoom - 0.25).toFixed(2)); applyScale(); scrollToPage(state.current); };
    els().zoomIn.onclick = () => { state.zoom = Math.min(3, +(state.zoom + 0.25).toFixed(2)); applyScale(); scrollToPage(state.current); };

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

    current = {
        ...current,
        type: 'docx',
        cleanupExtra: () => {
            els().content.removeEventListener('scroll', onScroll);
            if (scrollRAF) cancelAnimationFrame(scrollRAF);
            window.removeEventListener('resize', onResize);
            clearTimeout(resizeTimer);
        },
    };

    showPager(true, numPages > 1, (n) => setCurrentPage(n, true));
    showZoom(true);
    setCurrentPage(1, false);
}

/* --------------------------- Excel / CSV --------------------------- */
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

async function openSheet(url, name) {
    const buf = await fetchArrayBuffer(url);
    const wb = XLSX.read(buf, { type: 'array' });
    const sheets = wb.SheetNames;
    const state = { idx: 0, scale: 1 };

    const render = () => {
        const body = els().body;
        body.innerHTML = '';
        const ws = wb.Sheets[sheets[state.idx]];
        const tbl = sheetToTable(ws);
        const holder = document.createElement('div');
        holder.className = 'origin-top transition-transform duration-150';
        holder.style.transform = `scale(${state.scale})`;
        holder.appendChild(tbl);
        body.appendChild(holder);
        hideLoading();
        els().pageInput.value = state.idx + 1;
        els().pageTotal.textContent = sheets.length;
        els().prev.disabled = state.idx <= 0;
        els().next.disabled = state.idx >= sheets.length - 1;
    };

    els().prev.onclick = () => { if (state.idx > 0) { state.idx--; render(); } };
    els().next.onclick = () => { if (state.idx < sheets.length - 1) { state.idx++; render(); } };
    els().zoomOut.onclick = () => { state.scale = Math.max(0.4, +(state.scale - 0.15).toFixed(2)); render(); };
    els().zoomIn.onclick = () => { state.scale = Math.min(4, +(state.scale + 0.15).toFixed(2)); render(); };

    current = { ...current, type: 'sheet' };
    showPager(true, sheets.length > 1, (n) => { state.idx = n - 1; render(); });
    showZoom(true);
    render();
}

/* ------------------------------ Texto ------------------------------ */
async function openText(url, name) {
    const res = await fetch(url, { credentials: 'same-origin' });
    const text = await res.text();
    const body = els().body;
    body.innerHTML = '';
    const pre = document.createElement('pre');
    pre.className = 'bg-white shadow-xl rounded p-4 sm:p-6 w-full max-w-4xl mx-auto text-xs sm:text-sm whitespace-pre-wrap break-words text-gray-800 overflow-auto';
    pre.textContent = text;
    body.appendChild(pre);
    hideLoading();

    const state = { scale: 1 };
    const apply = () => {
        const s = state.scale;
        pre.style.fontSize = `${s * 100}%`;
    };
    els().zoomOut.onclick = () => { state.scale = Math.max(0.5, +(state.scale - 0.1).toFixed(2)); apply(); };
    els().zoomIn.onclick = () => { state.scale = Math.min(3, +(state.scale + 0.1).toFixed(2)); apply(); };

    current = { ...current, type: 'text' };
    showPager(false, false, null);
    showZoom(true);
    apply();
}

/* --------------------------- No soportado --------------------------- */
function openUnsupported(name) {
    const body = els().body;
    const ext = extOf(name);
    const message = ext
        ? `No es posible previsualizar este tipo de archivo (<strong>${ext.toUpperCase()}</strong>) en el navegador.`
        : 'No se encontró un archivo válido para este registro.';
    body.innerHTML = `
        <div class="bg-white shadow-xl rounded-lg p-8 max-w-md mx-auto my-10 text-center">
            <i data-lucide="file-question" class="w-14 h-14 text-gray-400 mx-auto mb-4"></i>
            <p class="text-gray-600 mb-4">${message}</p>
            <p class="text-sm text-gray-500 mb-6">Puedes descargarlo para verlo con la aplicación correspondiente.</p>
            <a href="#" id="fv-unsupported-download" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium text-sm">
                <i data-lucide="download" class="w-4 h-4"></i> Descargar
            </a>
        </div>`;
    const d = el('fv-unsupported-download');
    if (d && current && current.downloadUrl) {
        d.href = current.downloadUrl;
        d.setAttribute('download', current.name);
    }
    showPager(false, false, null);
    showZoom(false);
    hideLoading();
    window.lucideRefresh && window.lucideRefresh();
}

/* ------------------------------ Utilidades ------------------------------ */
function hideLoading() {
    els().loading.classList.add('hidden');
}

function showPager(show, enabled, onGoTo) {
    els().pager.classList.toggle('hidden', !show);
    els().pager.classList.toggle('flex', show);
    els().prev.disabled = !enabled;
    els().next.disabled = !enabled;
    goToPage = onGoTo || null;
}

function showZoom(show) {
    els().vtools.classList.toggle('hidden', !show);
    els().vtools.classList.toggle('flex', show);
}

async function cleanup() {
    if (current && current.cleanupExtra) {
        try { current.cleanupExtra(); } catch (e) { /* noop */ }
    }
    if (current && current.pdf) {
        try { current.pdf.destroy(); } catch (e) { /* noop */ }
    }
    els().body.innerHTML = '';
    els().loading.classList.remove('hidden');
    showZoom(false);
    goToPage = null;
    current = null;
}

function close() {
    const panel = els().panel;
    panel.classList.add('translate-y-full');

    let done = false;
    const finish = () => {
        if (done) return;
        done = true;
        panel.removeEventListener('transitionend', onTransitionEnd);
        clearTimeout(fallback);
        els().root.classList.add('hidden');
        els().root.style.display = 'none';
        cleanup();
        document.body.style.overflow = '';
    };
    const onTransitionEnd = (e) => { if (e.target === panel && e.propertyName === 'transform') finish(); };

    panel.addEventListener('transitionend', onTransitionEnd);
    // Salvaguarda por si transitionend no llega a disparar (p. ej. panel ya oculto)
    const fallback = setTimeout(finish, 220);
}

const KNOWN_EXTS = [...PDF_EXTS, ...IMG_EXTS, ...DOC_EXTS, ...SHEET_EXTS, 'txt'];

// El nombre visible (basename del registro en la base de datos) a veces llega
// vacío o mal formado para ciertos registros, aunque la URL del archivo sea
// válida. Antes de rendirnos, intentamos deducir la extensión de la URL: del
// parámetro ?path= (rutas tipo visor.stream) o, si no, de la URL misma
// (rutas que sirven el archivo directamente, p. ej. /archivos_pdf/algo.pdf).
function guessExt(name, url) {
    const primary = extOf(name);
    if (KNOWN_EXTS.includes(primary)) return primary;
    try {
        const pathParam = new URL(url, window.location.origin).searchParams.get('path');
        if (pathParam) {
            const fromPath = extOf(pathParam);
            if (KNOWN_EXTS.includes(fromPath)) return fromPath;
        }
    } catch (e) { /* URL inválida, seguir con el siguiente intento */ }
    const fromUrl = extOf(url);
    if (KNOWN_EXTS.includes(fromUrl)) return fromUrl;
    return primary;
}

function open(payload) {
    if (!els().root) return;
    const { url, name, downloadUrl } = payload;
    const ext = guessExt(name, url);

    cleanup();
    els().title.textContent = name;
    els().title.title = name;
    els().download.href = downloadUrl || url;
    els().download.setAttribute('download', downloadUrl ? '' : name);

    document.body.style.overflow = 'hidden';
    els().root.classList.remove('hidden');
    els().root.style.display = '';
    requestAnimationFrame(() => els().panel.classList.remove('translate-y-full'));

    current = { url, name, downloadUrl, type: ext };

    const run = async () => {
        try {
            if (PDF_EXTS.includes(ext)) return await openPdf(url, name);
            if (IMG_EXTS.includes(ext)) return openImage(url, name);
            if (DOC_EXTS.includes(ext)) return await openDocx(url, name);
            if (SHEET_EXTS.includes(ext)) return await openSheet(url, name);
            if (ext === 'txt') return await openText(url, name);
            openUnsupported(name);
        } catch (err) {
            els().loading.classList.add('hidden');
            els().body.innerHTML = `
                <div class="bg-white shadow-xl rounded-lg p-8 max-w-md mx-auto my-10 text-center">
                    <i data-lucide="alert-triangle" class="w-14 h-14 text-red-500 mx-auto mb-4"></i>
                    <p class="text-gray-600">${(err && err.message) || 'Error al abrir el archivo.'}</p>
                    <a href="${downloadUrl || url}" class="inline-flex items-center gap-2 px-4 py-2 mt-4 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium text-sm">
                        <i data-lucide="download" class="w-4 h-4"></i> Descargar
                    </a>
                </div>`;
            window.lucideRefresh && window.lucideRefresh();
        }
    };
    run();
}

export function initFileViewer() {
    if (!el('file-viewer')) return;

    els().close.onclick = close;
    els().backdrop.onclick = close;
    els().root.addEventListener('click', (e) => {
        if (e.target === els().root) close();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !els().root.classList.contains('hidden')) close();
    });

    els().pageInput.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const input = els().pageInput;
        const n = parseInt(input.value, 10);
        if (goToPage && !isNaN(n)) {
            const max = parseInt(els().pageTotal.textContent, 10) || 1;
            goToPage(Math.min(Math.max(n, 1), max));
        }
        input.blur();
    });

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-file-viewer]');
        if (!trigger) return;
        e.preventDefault();
        open({
            url: trigger.dataset.src,
            name: trigger.dataset.name,
            downloadUrl: trigger.dataset.download || null,
        });
    });
}
