@extends('adminlte::page')

@section('title', 'Inicio')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@vite(['resources/css/app.css'])
<style>
/* ── Tarjeta compacta responsive ── */
#resumen-card-compacta {
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
}
#resumen-card-compacta:hover {
    border-color: #1a6496 !important;
    box-shadow: 0 3px 10px rgba(0,0,0,.13);
}

/* Grid CSS responsivo para los 7 módulos */
#resumen-mini-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px 12px;
}

/* Celda de mini-módulo: título/% arriba, icono + barra alineados abajo */
.mini-item    { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.mini-head    { display: flex; justify-content: space-between; align-items: baseline; gap: 2px; }
.mini-label   { font-size: .68rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mini-pct     { font-size: .75rem; font-weight: 700; white-space: nowrap; flex-shrink: 0; }
.mini-bar-row { display: flex; align-items: center; gap: 5px; margin-top: 1px; }
.mini-icon    { width: 14px; height: 14px; stroke-width: 2.2; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; }
.mini-bar     { flex: 1; height: 4px; border-radius: 2px; background: #e9ecef; overflow: hidden; }
.mini-fill    { height: 100%; border-radius: 2px; transition: width .5s ease; }
.modal-card-icon { width: 20px; height: 20px; stroke-width: 2.2; }

/* Tablet: 4 columnas */
@media (max-width: 991px) {
    #resumen-mini-grid { grid-template-columns: repeat(4, 1fr); }
}
/* Móvil landscape: 3 columnas */
@media (max-width: 767px) {
    #resumen-mini-grid { grid-template-columns: repeat(3, 1fr); gap: 6px 10px; }
    .mini-label { font-size: .65rem; }
}
/* Móvil portrait: 2 columnas */
@media (max-width: 480px) {
    #resumen-mini-grid { grid-template-columns: repeat(2, 1fr); gap: 6px 8px; }
}

/* Modal */
#modalResumen {
    z-index: 1060 !important;
}
#modalResumen .modal-dialog {
    max-width: 960px;
    z-index: 1061 !important;
}
#modalResumen .modal-content {
    opacity: 1 !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
}
#modal-cards .mod-card { border-left: 4px solid; transition: transform .15s; }
#modal-cards .mod-card:hover { transform: translateY(-2px); }
#modalResumen .btn-secondary {
    background-color: #6c757d !important;
    border: 1px solid #6c757d !important;
    color: #ffffff !important;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}
#modalResumen .btn-secondary:hover {
    background-color: #5a6268 !important;
    border-color: #545b62 !important;
    color: #ffffff !important;
}
</style>
@endsection

@section('content_header')
    <x-page-header icon="house" title="Inicio" color="blue" />
@stop

@section('content')

<div class="mb-3">
    <p class="text-lg text-gray-800 mb-1">Bienvenido(a), {{ Auth::user()->name }}</p>
    <p class="text-sm text-gray-500 mt-1">Elige un módulo para continuar.</p>
</div>

{{-- ══ TARJETA COMPACTA DE RESUMEN ══ --}}
<div class="card card-primary card-outline mb-4" id="resumen-card-compacta"
     onclick="abrirModalResumen()"
     title="Haz clic para ver el detalle completo de participación">
    <div class="card-body py-2 px-3">

        {{-- Fila superior: título + botón --}}
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center">
                <span class="bg-primary rounded d-flex align-items-center justify-content-center mr-2"
                      style="width:32px;height:32px;flex-shrink:0;">
                    <i class="fas fa-chart-bar text-white" style="font-size:.85rem;"></i>
                </span>
                <div>
                    <div class="font-weight-bold text-dark" style="font-size:.88rem;line-height:1.2;">
                        Participación por Módulo
                    </div>
                    <div id="resumen-scope-label" class="text-muted" style="font-size:.72rem;">
                        <i class="fas fa-spinner fa-spin fa-xs"></i> Cargando...
                    </div>
                </div>
            </div>
            <span class="btn btn-sm btn-outline-primary flex-shrink-0 ml-2"
                  onclick="abrirModalResumen(); event.stopPropagation();"
                  style="font-size:.75rem;padding:3px 10px;">
                <i class="fas fa-expand-alt mr-1"></i>
                <span class="d-none d-sm-inline">Ver detalle</span>
                <span class="d-inline d-sm-none">Ver</span>
            </span>
        </div>

        {{-- Grid de mini-barras por módulo (generado por JS) --}}
        <div id="resumen-mini-grid"></div>

    </div>
</div>
{{-- ══ FIN TARJETA COMPACTA ══ --}}

{{-- Tarjetas de módulos del sistema --}}
@forelse ($modulos as $modulo)
    @if($loop->first)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
    @endif

    <x-module-card
        :icon="$modulo['icon']"
        :title="$modulo['text']"
        :href="$modulo['href']"
        :color="$modulo['color']"
        :description="$modulo['description']"
    />

    @if($loop->last)
        </div>
    @endif
@empty
    <div class="alert alert-info">
        No tienes módulos asignados todavía. Comunícate con el administrador del sistema si crees que esto es un error.
    </div>
@endforelse

{{-- ══ MODAL DE DETALLE ══ --}}
<div class="modal fade" id="modalResumen" tabindex="-1" role="dialog" aria-labelledby="modalResumenLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title mb-0" id="modalResumenLabel">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Participación por Módulo
                    <span id="modal-scope-badge" class="badge badge-light ml-2" style="font-size:.8rem;"></span>
                    <small id="modal-total-label" class="ml-1 font-weight-normal" style="font-size:.78rem;opacity:.85;"></small>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalResumen()" style="padding: 0.25rem 0.5rem; margin: 0; opacity: 0.9; cursor: pointer; background: transparent; border: 0; outline: none;">
                    <span aria-hidden="true" style="font-size: 1.5rem; line-height: 1; display: inline-block;">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div id="modal-loading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
                    <p class="text-muted mt-3">Cargando estadísticas...</p>
                </div>
                <div id="modal-contenido" style="display:none;">
                    <div class="row" id="modal-cards"></div>
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-lg-7 col-12 mb-3">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-users mr-1"></i>Docentes que registraron por módulo
                            </h6>
                            <canvas id="chartResumenModal" style="max-height:300px;"></canvas>
                        </div>
                        <div class="col-lg-5 col-12 mb-3">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-table mr-1"></i>Detalle por módulo
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Módulo</th>
                                            <th class="text-center">Registros</th>
                                            <th class="text-center">Docentes</th>
                                            <th class="text-center">%</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-tabla-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal" onclick="cerrarModalResumen()" style="background-color: #6c757d !important; border: 1px solid #6c757d !important; color: #ffffff !important; cursor: pointer;">
                    <i class="fas fa-times mr-1" style="color: #ffffff !important;"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
{{-- ══ FIN MODAL ══ --}}

@stop

@section('js')
<script src="vendor/chart.js/Chart.min.js"></script>
<script>
(function () {
    /* ── Config de colores, icono y nombres por módulo (exactos a las tarjetas de inicio) ── */
    var CFG = {
        agenda:     { bg: '#db2777', light: '#fce7f3', lucide: 'calendar-check', short: 'Agenda',     full: 'Agenda de Lectura' },
        accion:     { bg: '#ca8a04', light: '#fef9c3', lucide: 'megaphone',      short: 'Acciones',   full: 'Acciones de Sensibilización' },
        difusion:   { bg: '#2563eb', light: '#dbeafe', lucide: 'radio',          short: 'Difusión',   full: 'Acciones de Difusión' },
        evidencia:  { bg: '#dc2626', light: '#fee2e2', lucide: 'file-text',      short: 'Evidencias', full: 'Evidencias de Asistencia Técnica' },
        plan:       { bg: '#0891b2', light: '#cffafe', lucide: 'book-heart',     short: 'Planes',     full: 'Espacio de Lectura en el Hogar' },
        produccion: { bg: '#16a34a', light: '#dcfce7', lucide: 'notebook-pen',   short: 'Producción', full: 'Producción de Textos Infantiles' },
        informe:    { bg: '#ea580c', light: '#ffedd5', lucide: 'book-open',      short: 'Informes',   full: 'Biblioteca del Aula (Informes)' },
    };

    function badge(pct) {
        if (pct >= 75) return 'success';
        if (pct >= 50) return 'primary';
        if (pct >= 25) return 'warning';
        return 'danger';
    }
    function badgeColor(pct) {
        if (pct >= 75) return '#28a745';
        if (pct >= 50) return '#007bff';
        if (pct >= 25) return '#ffc107';
        return '#dc3545';
    }

    var cachedData = null;
    var chartModal = null;

    /* ══════════════════════════════════════
       Tarjeta compacta — mini-grid
    ══════════════════════════════════════ */
    function renderCompacta(data) {
        document.getElementById('resumen-scope-label').textContent =
            data.scope + ' · ' + data.totalDocentes.toLocaleString() + ' docentes';

        var html = '';
        data.modulos.forEach(function (m) {
            var cfg   = CFG[m.key] || { bg:'#6c757d', light:'#e2e3e5', lucide:'folder', short: m.nombre };
            var color = badgeColor(m.porcentaje);
            html +=
                '<div class="mini-item">' +
                    '<div class="mini-head">' +
                        '<span class="mini-label">' + cfg.short + '</span>' +
                        '<span class="mini-pct" style="color:' + cfg.bg + ';">' + m.porcentaje + '%</span>' +
                    '</div>' +
                    '<div class="mini-bar-row">' +
                        '<i data-lucide="' + cfg.lucide + '" class="mini-icon" style="color:' + cfg.bg + ';"></i>' +
                        '<div class="mini-bar">' +
                            '<div class="mini-fill" style="width:' + Math.min(m.porcentaje, 100) + '%;background:' + color + ';"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        });
        document.getElementById('resumen-mini-grid').innerHTML = html;
        if (window.lucideRefresh) { window.lucideRefresh(); }
    }

    /* ══════════════════════════════════════
       Modal — tarjetas + gráfico + tabla
    ══════════════════════════════════════ */
    function renderModal(data) {
        document.getElementById('modal-scope-badge').textContent = data.scope;
        document.getElementById('modal-total-label').textContent =
            '— ' + data.totalDocentes.toLocaleString() + ' docentes en el universo';

        /* Tarjetas */
        var cardsHtml = '';
        data.modulos.forEach(function (m) {
            var cfg = CFG[m.key] || { bg:'#6c757d', light:'#e2e3e5', lucide:'folder' };
            var nombreCompleto = (cfg.full) ? cfg.full : m.nombre;
            cardsHtml +=
                '<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">' +
                    '<div class="card mod-card shadow-sm h-100 mb-0" style="border-left:4px solid ' + cfg.bg + ';">' +
                        '<div class="card-body py-3">' +
                            '<div class="d-flex align-items-center mb-2">' +
                                '<span class="rounded-circle d-flex align-items-center justify-content-center mr-2" ' +
                                      'style="width:36px;height:36px;background:' + cfg.light + ';flex-shrink:0;">' +
                                    '<i data-lucide="' + cfg.lucide + '" class="modal-card-icon" style="color:' + cfg.bg + ';"></i>' +
                                '</span>' +
                                '<div class="font-weight-bold text-dark" style="font-size:0.83rem;line-height:1.25;" title="' + nombreCompleto + '">' + nombreCompleto + '</div>' +
                            '</div>' +
                            '<div class="d-flex justify-content-between align-items-end mb-1">' +
                                '<div>' +
                                    '<span class="h4 mb-0 font-weight-bold" style="color:' + cfg.bg + ';">' + m.porcentaje + '%</span>' +
                                    '<small class="text-muted ml-1">participación</small>' +
                                '</div>' +
                                '<div class="text-right">' +
                                    '<div class="small text-muted">' + m.docentes_con_registro.toLocaleString() + ' docentes</div>' +
                                    '<div class="small text-muted">' + m.registros.toLocaleString() + ' registros</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="progress" style="height:6px;">' +
                                '<div class="progress-bar bg-' + badge(m.porcentaje) + '" style="width:' + Math.min(m.porcentaje, 100) + '%;"></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        });
        document.getElementById('modal-cards').innerHTML = cardsHtml;

        /* Tabla */
        var tbodyHtml = '';
        data.modulos.forEach(function (m) {
            var cfg = CFG[m.key] || {};
            var nombreCompleto = (cfg.full) ? cfg.full : m.nombre;
            tbodyHtml +=
                '<tr>' +
                    '<td class="font-weight-normal">' + nombreCompleto + '</td>' +
                    '<td class="text-center">' + m.registros.toLocaleString() + '</td>' +
                    '<td class="text-center">' + m.docentes_con_registro.toLocaleString() + '</td>' +
                    '<td class="text-center"><span class="badge badge-' + badge(m.porcentaje) + '">' + m.porcentaje + '%</span></td>' +
                '</tr>';
        });
        document.getElementById('modal-tabla-body').innerHTML = tbodyHtml;

        /* Gráfico de barras horizontal */
        var labels   = data.modulos.map(function (m) { return (CFG[m.key] && CFG[m.key].full) ? CFG[m.key].full : m.nombre; });
        var valores  = data.modulos.map(function (m) { return m.docentes_con_registro; });
        var bgColors = data.modulos.map(function (m) { return (CFG[m.key] || { bg:'#6c757d' }).bg; });
        var pcts     = data.modulos.map(function (m) { return m.porcentaje; });

        if (chartModal) { chartModal.destroy(); }
        chartModal = new Chart(
            document.getElementById('chartResumenModal').getContext('2d'),
            {
                type: 'horizontalBar',
                data: { labels: labels, datasets: [{ label: 'Docentes', data: valores, backgroundColor: bgColors }] },
                options: {
                    maintainAspectRatio: false, responsive: true,
                    scales: {
                        xAxes: [{ ticks: { beginAtZero: true, precision: 0 } }],
                        yAxes: [{ ticks: { fontSize: 11 } }]
                    },
                    tooltips: {
                        callbacks: {
                            label: function (item) {
                                var i = item.index;
                                return ' ' + valores[i].toLocaleString() + ' docentes (' + pcts[i] + '% participación)';
                            }
                        }
                    },
                    legend: { display: false }
                }
            }
        );

        document.getElementById('modal-loading').style.display   = 'none';
        document.getElementById('modal-contenido').style.display = 'block';
        if (window.lucideRefresh) { window.lucideRefresh(); }
    }

    /* ══════════════════════════════════════
       Fetch con caché en memoria
    ══════════════════════════════════════ */
    function cargarDatos(cb) {
        if (cachedData) { cb(cachedData); return; }
        fetch('{{ route("dashboard.resumen-modulos") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function (data) { cachedData = data; cb(data); })
        .catch(function (err) {
            document.getElementById('resumen-scope-label').innerHTML =
                '<span class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i>Error al cargar</span>';
            document.getElementById('modal-loading').innerHTML =
                '<div class="alert alert-warning m-3"><i class="fas fa-exclamation-triangle mr-1"></i>' +
                'No se pudo cargar (' + err.message + ')</div>';
            console.error('resumenModulos:', err);
        });
    }

    /* ══════════════════════════════════════
       Funciones globales para abrir/cerrar modal
    ══════════════════════════════════════ */
    $(document).ready(function () {
        $('#modalResumen').appendTo('body');
    });

    window.cerrarModalResumen = function () {
        $('#modalResumen').modal('hide');
    };

    window.abrirModalResumen = function () {
        if ($('#modalResumen').parent().is(':not(body)')) {
            $('#modalResumen').appendTo('body');
        }
        $('#modalResumen').modal('show');

        if (cachedData) {
            renderModal(cachedData);
        } else {
            document.getElementById('modal-loading').innerHTML =
                '<i class="fas fa-spinner fa-spin fa-3x text-muted"></i>' +
                '<p class="text-muted mt-3">Cargando estadísticas...</p>';
            document.getElementById('modal-loading').style.display   = 'block';
            document.getElementById('modal-contenido').style.display = 'none';
            cargarDatos(renderModal);
        }
    };

    /* Cargar al iniciar y pintar la tarjeta compacta */
    setTimeout(function () { cargarDatos(renderCompacta); }, 50);
})();
</script>
@stop
