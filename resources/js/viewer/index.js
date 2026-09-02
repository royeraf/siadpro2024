import * as pdfjsLib from 'pdfjs-dist';
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.js?url';
import * as mammoth from 'mammoth';
import * as XLSX from 'xlsx';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

const IMG_EXTS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'];
const PDF_EXTS = ['pdf'];
const DOC_EXTS = ['docx'];
const SHEET_EXTS = ['xlsx', 'xls', 'csv'];

let current = null;

function el(id) {
    return document.getElementById(id);
}

const els = () => ({
    root: el('file-viewer'),
    backdrop: el('fv-backdrop'),
    panel: el('fv-panel'),
    title: el('fv-title'),
    pager: el('fv-pager'),
    prev: el('fv-prev'),
    next: el('fv-next'),
    pageLabel: el('fv-page-label'),
    zoomBox: el('fv-zoom'),
    zoomOut: el('fv-zoom-out'),
    zoomIn: el('fv-zoom-in'),
    zoomReset: el('fv-zoom-reset'),
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
function renderPdf(doc, page, scale) {
    const body = els().body;
    body.innerHTML = '';
    const wrap = document.createElement('div');
    wrap.className = 'shadow-xl bg-white p-2 sm:p-3 my-1';
    const canvas = document.createElement('canvas');
    wrap.appendChild(canvas);
    body.appendChild(wrap);
    hideLoading();

    return doc.getPage(page).then((pageData) => {
        const viewport = pageData.getViewport({ scale });
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        return pageData.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
    });
}

async function openPdf(url, name) {
    const buf = await fetchArrayBuffer(url);
    const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
    const state = { pdf, page: 1, numPages: pdf.numPages, scale: 1.5 };

    const setPage = () => {
        els().pageLabel.textContent = `${state.page}/${state.numPages}`;
        els().prev.disabled = state.page <= 1;
        els().next.disabled = state.page >= state.numPages;
        renderPdf(state.pdf, state.page, state.scale).catch(() => {
            els().body.innerHTML = '<div class="text-red-600 p-6 text-center">Error al renderizar la página.</div>';
        });
    };

    els().prev.onclick = () => { if (state.page > 1) { state.page--; setPage(); } };
    els().next.onclick = () => { if (state.page < state.numPages) { state.page++; setPage(); } };
    els().zoomOut.onclick = () => { state.scale = Math.max(0.2, +(state.scale - 0.25).toFixed(2)); syncZoom(state.scale); setPage(); };
    els().zoomIn.onclick = () => { state.scale = Math.min(5, +(state.scale + 0.25).toFixed(2)); syncZoom(state.scale); setPage(); };
    els().zoomReset.onclick = () => { state.scale = 1.5; syncZoom(state.scale); setPage(); };

    current.cancel = () => { try { pdf.destroy(); } catch (e) { /* noop */ } };
    current = { ...current, type: 'pdf', pdf, state };

    showPager(true, () => setPage(), state.numPages > 1);
    setPage();
}

/* ---------------------------- Imágenes ---------------------------- */
function openImage(url, name) {
    const body = els().body;
    body.innerHTML = '';
    const img = new Image();
    img.alt = name;
    img.src = url;
    img.className = 'max-w-full h-auto rounded shadow-lg bg-white transition-transform duration-150';
    body.appendChild(img);
    img.onload = () => { els().loading.classList.add('hidden'); };

    const state = { scale: 1 };
    const apply = () => { img.style.transform = `scale(${state.scale})`; syncZoom(state.scale); };
    els().zoomOut.onclick = () => { state.scale = Math.max(0.2, +(state.scale - 0.25).toFixed(2)); apply(); };
    els().zoomIn.onclick = () => { state.scale = Math.min(5, +(state.scale + 0.25).toFixed(2)); apply(); };
    els().zoomReset.onclick = () => { state.scale = 1; apply(); };

    current = { ...current, type: 'image', img, state };
    showPager(false, null, false);
    els().pageLabel.textContent = '1/1';
}

/* --------------------------- Word (docx) --------------------------- */
async function openDocx(url, name) {
    const buf = await fetchArrayBuffer(url);
    const result = await mammoth.convertToHtml({ arrayBuffer: buf });
    const body = els().body;
    body.innerHTML = '';
    const sheet = document.createElement('div');
    sheet.className = 'bg-white shadow-xl rounded p-4 sm:p-8 w-full max-w-4xl mx-auto text-sm leading-relaxed text-gray-800 origin-top transition-transform duration-150';
    sheet.innerHTML = result.value || '<p>(documento vacío)</p>';
    body.appendChild(sheet);
    hideLoading();

    const state = { scale: 1 };
    const apply = () => {
        const s = state.scale;
        sheet.style.transform = `scale(${s})`;
        sheet.style.marginBottom = `${(s - 1) * 400}px`;
        syncZoom(s);
    };
    els().zoomOut.onclick = () => { state.scale = Math.max(0.5, +(state.scale - 0.1).toFixed(2)); apply(); };
    els().zoomIn.onclick = () => { state.scale = Math.min(3, +(state.scale + 0.1).toFixed(2)); apply(); };
    els().zoomReset.onclick = () => { state.scale = 1; apply(); };

    current = { ...current, type: 'docx' };
    showPager(false, null, false);
    els().pageLabel.textContent = '1/1';
    apply();
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
        els().pageLabel.textContent = `${state.idx + 1}/${sheets.length}`;
    };

    const applyZoom = () => syncZoom(state.scale);

    els().prev.onclick = () => { if (state.idx > 0) { state.idx--; render(); } };
    els().next.onclick = () => { if (state.idx < sheets.length - 1) { state.idx++; render(); } };
    els().zoomOut.onclick = () => { state.scale = Math.max(0.4, +(state.scale - 0.15).toFixed(2)); render(); applyZoom(); };
    els().zoomIn.onclick = () => { state.scale = Math.min(4, +(state.scale + 0.15).toFixed(2)); render(); applyZoom(); };
    els().zoomReset.onclick = () => { state.scale = 1; render(); applyZoom(); };

    current = { ...current, type: 'sheet' };
    showPager(true, () => {}, sheets.length > 1);
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
        syncZoom(s);
    };
    els().zoomOut.onclick = () => { state.scale = Math.max(0.5, +(state.scale - 0.1).toFixed(2)); apply(); };
    els().zoomIn.onclick = () => { state.scale = Math.min(3, +(state.scale + 0.1).toFixed(2)); apply(); };
    els().zoomReset.onclick = () => { state.scale = 1; apply(); };

    current = { ...current, type: 'text' };
    showPager(false, null, false);
    els().pageLabel.textContent = '1/1';
    apply();
}

/* --------------------------- No soportado --------------------------- */
function openUnsupported(name) {
    const body = els().body;
    body.innerHTML = `
        <div class="bg-white shadow-xl rounded-lg p-8 max-w-md mx-auto my-10 text-center">
            <i data-lucide="file-question" class="w-14 h-14 text-gray-400 mx-auto mb-4"></i>
            <p class="text-gray-600 mb-4">No es posible previsualizar este tipo de archivo (<strong>${extOf(name).toUpperCase()}</strong>) en el navegador.</p>
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
    showPager(false, null, false);
    els().pageLabel.textContent = '-';
    syncZoom(1);
    hideLoading();
    window.lucideRefresh && window.lucideRefresh();
}

/* ------------------------------ Utilidades ------------------------------ */
function syncZoom(scale) {
    const pct = Math.round(scale * 100);
    els().zoomReset.textContent = `${pct}%`;
}

function hideLoading() {
    els().loading.classList.add('hidden');
}

function showPager(show, onPageChange, enabled) {
    els().pager.classList.toggle('hidden', !show);
    els().pager.classList.toggle('flex', show);
    els().prev.disabled = !enabled;
    els().next.disabled = !enabled;
    if (onPageChange) {
        els().pageLabel.onclick = onPageChange;
    } else {
        els().pageLabel.onclick = null;
    }
}

async function cleanup() {
    if (current && current.pdf) {
        try { current.pdf.destroy(); } catch (e) { /* noop */ }
    }
    els().body.innerHTML = '';
    els().loading.classList.remove('hidden');
    current = null;
}

function close() {
    els().panel.classList.add('translate-y-full');
    setTimeout(() => {
        els().root.classList.add('hidden');
        cleanup();
        document.body.style.overflow = '';
    }, 280);
}

function open(payload) {
    if (!els().root) return;
    const { url, name, downloadUrl } = payload;
    const ext = extOf(name);

    cleanup();
    els().title.textContent = name;
    els().title.title = name;
    els().download.href = downloadUrl || url;
    els().download.setAttribute('download', downloadUrl ? '' : name);

    document.body.style.overflow = 'hidden';
    els().root.classList.remove('hidden');
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
