@extends('adminlte::page')

@section('title', 'Inicio')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@vite(['resources/css/app.css'])
<style>
/* ── ESTILOS DEL DASHBOARD INICIAL ── */

/* Hero Banner */
.hero-welcome-card {
    background: linear-gradient(135deg, #EEF4FF 0%, #F5F3FF 100%);
    border: 1px solid #E0E7FF;
    border-radius: 24px;
    padding: 1.75rem 2rem;
    position: relative;
    overflow: hidden;
    min-height: 165px;
}
.hero-welcome-content {
    position: relative;
    z-index: 2;
    max-width: 65%;
}
.hero-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1E1B4B;
    line-height: 1.2;
    letter-spacing: -0.3px;
}
.hero-subtitle {
    color: #475569;
    font-size: 0.92rem;
    line-height: 1.5;
}
.hero-welcome-img {
    position: absolute;
    right: 15px;
    bottom: 0;
    max-height: 155px;
    max-width: 38%;
    object-fit: contain;
    z-index: 1;
    pointer-events: none;
}

/* Participación por Módulo */
.participacion-card {
    background: #FFFFFF;
    border: 1px solid #F1F5F9;
    border-radius: 20px;
    padding: 1rem 1.4rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
}
.participacion-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    border-color: #E2E8F0;
}
.participacion-grid {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 2px;
}
.part-item {
    flex: 1;
    min-width: 100px;
    padding: 0 10px;
    border-right: 1px solid #F1F5F9;
}
.part-item:last-child {
    border-right: none;
}
.part-icon-badge {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.part-icon {
    width: 15px;
    height: 15px;
}
.part-name {
    font-size: 0.74rem;
    color: #64748B;
    line-height: 1.1;
}
.part-pct {
    font-size: 0.88rem;
    line-height: 1.2;
}
.part-progress-bar {
    height: 5px;
    background: #F1F5F9;
    border-radius: 9999px;
    overflow: hidden;
    width: 100%;
    margin-top: 4px;
}
.part-progress-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.6s ease;
}

/* Tarjetas de actividades */
.activity-card {
    border-radius: 22px;
    padding: 1.4rem 1.3rem 1.2rem 1.3rem;
    transition: border-color 0.2s ease;
    overflow: hidden;
}
.activity-card:hover {
    border-color: var(--card-border-hover, #4F46E5) !important;
}
.activity-icon-badge {
    width: 50px;
    height: 50px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
}
.activity-card-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0F172A;
    line-height: 1.3;
}
.activity-card-desc {
    font-size: 0.82rem;
    color: #475569;
    font-weight: 500;
    line-height: 1.45;
    min-height: 40px;
}
.btn-module-action {
    display: inline-flex;
    align-items: center;
    font-size: 0.8rem;
    font-weight: 800;
    border-radius: 9999px;
    padding: 7px 16px;
    transition: all 0.2s ease;
    text-decoration: none !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}
.btn-module-action:hover {
    filter: brightness(0.92);
}
.activity-card-img {
    max-height: 72px;
    max-width: 95px;
    object-fit: contain;
    margin-bottom: -8px;
    margin-right: -4px;
    pointer-events: none;
}

/* Banner motivacional final */
.motivational-banner {
    background: linear-gradient(135deg, #EEF2FF 0%, #F5F3FF 100%);
    border: 1px solid #E0E7FF;
    border-radius: 24px;
    overflow: hidden;
    min-height: 180px;
}
.motivational-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #1E1B4B;
    line-height: 1.25;
}
.motivational-img {
    max-height: 115px;
    object-fit: contain;
    pointer-events: none;
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
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2) !important;
}
#modalResumen .modal-header {
    background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%) !important;
}
#modal-cards .mod-card { 
    border-left: 4px solid; 
    border-radius: 14px;
    transition: transform .15s; 
}
#modal-cards .mod-card:hover { transform: translateY(-2px); }
</style>
@endsection

@section('content_header')
@stop

@section('content')

@php
    // Paleta de temas con colores más vivos, cálidos y definidos
    $themeMap = [
        'accion' => [
            'cardBg'      => 'linear-gradient(150deg, #FEF08A 0%, #FEF9C3 55%, #FFFDF0 100%)',
            'border'      => '#FACC15',
            'borderHover' => '#CA8A04',
            'badgeBg'     => 'linear-gradient(135deg, #F59E0B 0%, #D97706 100%)',
            'badgeText'   => '#FFFFFF',
            'btnBg'       => '#FDE68A',
            'btnText'     => '#78350F',
            'btnBorder'   => '#FACC15',
            'shadow'      => '0 6px 18px rgba(245, 158, 11, 0.14)',
            'title'       => 'Acción de Sensibilización',
            'icon'        => 'megaphone',
            'img'         => 'img/dashboard/sensibilizacion_girl.png',
        ],
        'difusion' => [
            'cardBg'      => 'linear-gradient(150deg, #E0E7FF 0%, #EEF2FF 55%, #F5F3FF 100%)',
            'border'      => '#A5B4FC',
            'borderHover' => '#4F46E5',
            'badgeBg'     => 'linear-gradient(135deg, #6366F1 0%, #4F46E5 100%)',
            'badgeText'   => '#FFFFFF',
            'btnBg'       => '#C7D2FE',
            'btnText'     => '#312E81',
            'btnBorder'   => '#A5B4FC',
            'shadow'      => '0 6px 18px rgba(99, 102, 241, 0.16)',
            'title'       => 'Acción de Difusión',
            'icon'        => 'radio',
            'img'         => null,
        ],
        'sector' => [
            'cardBg'      => 'linear-gradient(150deg, #DBEAFE 0%, #EFF6FF 55%, #F8FAFC 100%)',
            'border'      => '#93C5FD',
            'borderHover' => '#2563EB',
            'badgeBg'     => 'linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%)',
            'badgeText'   => '#FFFFFF',
            'btnBg'       => '#BFDBFE',
            'btnText'     => '#1E3A8A',
            'btnBorder'   => '#93C5FD',
            'shadow'      => '0 6px 18px rgba(59, 130, 246, 0.16)',
            'title'       => 'Sectores del Aula',
            'icon'        => 'layout-grid',
            'img'         => 'img/dashboard/sectores_blocks.png',
        ],
        'evidencia' => [
            'cardBg'      => 'linear-gradient(150deg, #FFE4E6 0%, #FFF1F2 55%, #FFF5F5 100%)',
            'border'      => '#FDA4AF',
            'borderHover' => '#E11D48',
            'badgeBg'     => 'linear-gradient(135deg, #F43F5E 0%, #BE123C 100%)',
            'badgeText'   => '#FFFFFF',
            'btnBg'       => '#FECDD3',
            'btnText'     => '#881337',
            'btnBorder'   => '#FDA4AF',
            'shadow'      => '0 6px 18px rgba(244, 63, 94, 0.16)',
            'title'       => 'Asistencia Técnica',
            'icon'        => 'file-text',
            'img'         => 'img/dashboard/asistencia_clipboard.png',
        ],
        'informe' => [
            'cardBg'      => 'linear-gradient(150deg, #FFEDD5 0%, #FFF7ED 55%, #FFFAF0 100%)',
            'border'      => '#FDBA74',
            'borderHover' => '#EA580C',
            'badgeBg'     => 'linear-gradient(135deg, #F97316 0%, #C2410C 100%)',
            'badgeText'   => '#FFFFFF',
            'btnBg'       => '#FED7AA',
            'btnText'     => '#7C2D12',
            'btnBorder'   => '#FDBA74',
            'shadow'      => '0 6px 18px rgba(249, 115, 22, 0.16)',
            'title'       => 'Biblioteca del Aula',
            'icon'        => 'book-open',
            'img'         => 'img/dashboard/biblioteca_books.png',
        ],
        'plan' => [
            'cardBg'      => 'linear-gradient(150deg, #CFFAFE 0%, #ECFEFF 55%, #F0FDFA 100%)',
            'border'      => '#67E8F9',
            'borderHover' => '#0891B2',
            'badgeBg'     => 'linear-gradient(135deg, #06B6D4 0%, #0E7490 100%)',
            'badgeText'   => '#FFFFFF',
            'btnBg'       => '#A5F3FC',
            'btnText'     => '#164E63',
            'btnBorder'   => '#67E8F9',
            'shadow'      => '0 6px 18px rgba(6, 182, 212, 0.16)',
            'title'       => 'Espacio de Lectura en el Hogar',
            'icon'        => 'book-heart',
            'img'         => 'img/dashboard/hogar_house.png',
        ],
        'produccion' => [
            'cardBg'      => 'linear-gradient(150deg, #DCFCE7 0%, #F0FDF4 55%, #F5FBF7 100%)',
            'border'      => '#86EFAC',
            'borderHover' => '#16A34A',
            'badgeBg'     => 'linear-gradient(135deg, #10B981 0%, #047857 100%)',
            'badgeText'   => '#FFFFFF',
            'btnBg'       => '#BBF7D0',
            'btnText'     => '#14532D',
            'btnBorder'   => '#86EFAC',
            'shadow'      => '0 6px 18px rgba(16, 185, 129, 0.16)',
            'title'       => 'Producción de Textos Infantiles',
            'icon'        => 'notebook-pen',
            'img'         => 'img/dashboard/produccion_book.png',
        ],
        'agenda' => [
            'cardBg'      => 'linear-gradient(150deg, #F3E8FF 0%, #FAF5FF 55%, #FCF8FF 100%)',
            'border'      => '#D8B4FE',
            'borderHover' => '#7C3AED',
            'badgeBg'     => 'linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%)',
            'badgeText'   => '#FFFFFF',
            'btnBg'       => '#DDD6FE',
            'btnText'     => '#4C1D95',
            'btnBorder'   => '#D8B4FE',
            'shadow'      => '0 6px 18px rgba(139, 92, 246, 0.16)',
            'title'       => 'Agenda de Lectura',
            'icon'        => 'calendar-check',
            'img'         => 'img/dashboard/agenda_calendar.png',
        ],
    ];

    function resolveTheme($modulo, $themeMap) {
        $href = strtolower($modulo['href'] ?? '');
        $text = strtolower($modulo['text'] ?? '');
        foreach ($themeMap as $key => $theme) {
            if (str_contains($href, $key) || str_contains($text, $key)) {
                return $theme;
            }
        }
        // Fallback por defecto
        return [
            'cardBg'      => 'linear-gradient(150deg, #E2E8F0 0%, #F1F5F9 100%)',
            'border'      => '#CBD5E1',
            'borderHover' => '#64748B',
            'badgeBg'     => 'linear-gradient(135deg, #64748B 0%, #475569 100%)',
            'badgeText'   => '#FFFFFF',
            'btnBg'       => '#E2E8F0',
            'btnText'     => '#1E293B',
            'btnBorder'   => '#CBD5E1',
            'shadow'      => '0 6px 18px rgba(0, 0, 0, 0.05)',
            'title'       => ucwords(mb_strtolower($modulo['text'])),
            'icon'        => $modulo['icon'] ?? 'folder',
            'img'         => null,
        ];
    }
@endphp

{{-- ══ SECCIÓN HERO (TARJETA DE BIENVENIDA) ══ --}}
<div class="mb-4 pt-2">
    <div class="hero-welcome-card d-flex flex-column justify-content-between">
        <div class="hero-welcome-content">
            <div class="d-flex align-items-center mb-2">
                <h2 class="hero-title mb-0">Bienvenido(a)</h2>
                <span class="ml-2" style="font-size: 1.5rem;">✨</span>
            </div>
            <p class="hero-subtitle mb-0">
                Hola, <strong>{{ Auth::user()->name }}</strong>. Aquí encontrarás recursos y herramientas para fortalecer las experiencias de aprendizaje de tus niños y niñas.
            </p>
        </div>
        @if(file_exists(public_path('img/dashboard/1553253269.svg')))
            <img src="{{ asset('img/dashboard/1553253269.svg') }}" alt="Bienvenida" class="hero-welcome-img d-none d-sm-block">
        @endif
    </div>
</div>

{{-- ══ PARTICIPACIÓN POR MÓDULO (BARRA HORIZONTAL) ══ --}}
<div class="participacion-card mb-4" onclick="abrirModalResumen()" title="Haz clic para ver el detalle de participación">
    <div class="row align-items-center">
        <div class="col-12 col-xl-3 col-lg-3 mb-3 mb-lg-0">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">Participación por Módulo</h6>
                    <small class="text-muted d-block" id="resumen-scope-label" style="font-size: 0.74rem;">
                        <i class="fas fa-spinner fa-spin fa-xs"></i> Cargando resumen...
                    </small>
                </div>
                <span class="btn btn-xs btn-outline-primary d-inline-block d-lg-none" onclick="abrirModalResumen(); event.stopPropagation();">
                    Ver detalle
                </span>
            </div>
        </div>
        <div class="col-12 col-xl-9 col-lg-9">
            <div id="participacion-items-container" class="participacion-grid">
                {{-- Generado dinámicamente por JS con los 7 módulos --}}
            </div>
        </div>
    </div>
</div>

{{-- ══ SECCIÓN MÓDULOS DE APRENDIZAJE ══ --}}
<div class="mb-3">
    <h4 class="font-weight-bold text-dark mb-1" style="font-size: 1.25rem;">Módulos de Aprendizaje</h4>
    <p class="text-muted small mb-3">Selecciona una actividad para comenzar a trabajar</p>
</div>

{{-- Grid de tarjetas pastel con ilustración y botón --}}
<div class="row">
    @forelse ($modulos as $modulo)
        @php
            $theme = resolveTheme($modulo, $themeMap);
        @endphp
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-4">
            <div class="activity-card h-100 d-flex flex-column justify-content-between position-relative"
                 style="--card-border-hover: {{ $theme['borderHover'] }}; background: {{ $theme['cardBg'] }}; border: 2px solid {{ $theme['border'] }}; box-shadow: {{ $theme['shadow'] }};">
                <div>
                    {{-- Icono circular con color sólido --}}
                    <div class="activity-icon-badge mb-3" style="background: {{ $theme['badgeBg'] }}; color: {{ $theme['badgeText'] }};">
                        <i data-lucide="{{ $theme['icon'] }}" class="w-6 h-6"></i>
                    </div>

                    {{-- Título --}}
                    <h5 class="activity-card-title mb-1">
                        {{ $theme['title'] }}
                    </h5>

                    {{-- Descripción --}}
                    <p class="activity-card-desc mb-3">
                        {{ $modulo['description'] }}
                    </p>
                </div>

                {{-- Botón de acción + ilustración opcional --}}
                <div class="d-flex align-items-end justify-content-between mt-auto">
                    <a href="{{ $modulo['href'] }}" 
                       class="btn-module-action"
                       style="background-color: {{ $theme['btnBg'] }}; color: {{ $theme['btnText'] }}; border: 1px solid {{ $theme['btnBorder'] }};">
                        <span>Ir al módulo</span>
                        <i class="fas fa-arrow-right ml-1" style="font-size: 0.72rem;"></i>
                    </a>

                    @if(!empty($theme['img']) && file_exists(public_path($theme['img'])))
                        <img src="{{ asset($theme['img']) }}" alt="" class="activity-card-img d-none d-sm-block">
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">
                No tienes módulos asignados todavía. Comunícate con el administrador del sistema si crees que esto es un error.
            </div>
        </div>
    @endforelse

    {{-- Tarjeta motivacional final decorativa --}}
    <div class="col-12 col-md-6 col-xl-6 mb-4">
        <div class="motivational-banner h-100 p-4 d-flex align-items-center justify-content-between"
             style="background: linear-gradient(135deg, #EEF2FF 0%, #EDE9FE 100%); border: 1.5px solid #C7D2FE; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.12);">
            <div class="pr-3">
                <h4 class="motivational-title mb-2">Pequeños pasos,<br>grandes futuros ♡</h4>
                <p class="text-muted small mb-0" style="color: #4338CA !important; font-weight: 500;">
                    Cada actividad que registras fortalece el camino hacia una educación de calidad.
                </p>
            </div>
            @if(file_exists(public_path('img/dashboard/pencils_bottom.png')))
                <img src="{{ asset('img/dashboard/pencils_bottom.png') }}" alt="" class="motivational-img d-none d-sm-block">
            @endif
        </div>
    </div>
</div>

{{-- ══ MODAL DE DETALLE (INTERACTIVO) ══ --}}
<div class="modal fade" id="modalResumen" tabindex="-1" role="dialog" aria-labelledby="modalResumenLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header py-3 text-white">
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

@stop

@section('js')
<script src="vendor/chart.js/Chart.min.js"></script>
<script>
(function () {
    /* ── Configuración de colores e iconos exactos a la maqueta por módulo ── */
    var CFG = {
        agenda:     { short: 'Agenda',     lucide: 'calendar-check', circleBg: '#FCE7F3', iconColor: '#EC4899', pctColor: '#DB2777', barColor: '#EC4899', full: 'Agenda de Lectura' },
        accion:     { short: 'Acciones',   lucide: 'megaphone',      circleBg: '#FEF3C7', iconColor: '#F59E0B', pctColor: '#D97706', barColor: '#F59E0B', full: 'Acciones de Sensibilización' },
        difusion:   { short: 'Difusión',   lucide: 'radio',          circleBg: '#EDE9FE', iconColor: '#8B5CF6', pctColor: '#7C3AED', barColor: '#8B5CF6', full: 'Acciones de Difusión' },
        evidencia:  { short: 'Evidencias', lucide: 'file-text',      circleBg: '#FFE4E6', iconColor: '#F43F5E', pctColor: '#E11D48', barColor: '#F43F5E', full: 'Evidencias de Asistencia Técnica' },
        plan:       { short: 'Planes',     lucide: 'book-heart',     circleBg: '#CFFAFE', iconColor: '#06B6D4', pctColor: '#0891B2', barColor: '#06B6D4', full: 'Espacio de Lectura en el Hogar' },
        produccion: { short: 'Producción', lucide: 'notebook-pen',   circleBg: '#DCFCE7', iconColor: '#10B981', pctColor: '#16A34A', barColor: '#10B981', full: 'Producción de Textos Infantiles' },
        informe:    { short: 'Informes',   lucide: 'book-open',      circleBg: '#FFEDD5', iconColor: '#F97316', pctColor: '#EA580C', barColor: '#F97316', full: 'Biblioteca del Aula (Informes)' },
    };

    function badge(pct) {
        if (pct >= 75) return 'success';
        if (pct >= 50) return 'primary';
        if (pct >= 25) return 'warning';
        return 'danger';
    }

    var cachedData = null;
    var chartModal = null;

    /* ══════════════════════════════════════
       Barra Horizontal de Participación
    ══════════════════════════════════════ */
    function renderParticipacionBar(data) {
        var labelEl = document.getElementById('resumen-scope-label');
        if (labelEl) {
            labelEl.textContent = data.scope + ' · ' + data.totalDocentes.toLocaleString() + ' docentes';
        }

        var container = document.getElementById('participacion-items-container');
        if (!container) return;

        var html = '';
        data.modulos.forEach(function (m) {
            var cfg = CFG[m.key] || { 
                short: m.nombre, 
                lucide: 'folder', 
                circleBg: '#F1F5F9', 
                iconColor: '#64748B', 
                pctColor: '#334155', 
                barColor: '#64748B' 
            };

            html +=
                '<div class="part-item">' +
                    '<div class="d-flex align-items-center mb-1">' +
                        '<div class="part-icon-badge mr-2" style="background:' + cfg.circleBg + ';color:' + cfg.iconColor + ';">' +
                            '<i data-lucide="' + cfg.lucide + '" class="part-icon"></i>' +
                        '</div>' +
                        '<div style="min-width:0;">' +
                            '<div class="part-name text-truncate">' + cfg.short + '</div>' +
                            '<div class="part-pct font-weight-bold" style="color:' + cfg.pctColor + ';">' + m.porcentaje + '%</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="part-progress-bar">' +
                        '<div class="part-progress-fill" style="width:' + Math.min(m.porcentaje, 100) + '%;background:' + cfg.barColor + ';"></div>' +
                    '</div>' +
                '</div>';
        });

        container.innerHTML = html;
        if (window.lucideRefresh) { window.lucideRefresh(); }
    }

    /* ══════════════════════════════════════
       Modal — Tarjetas + Gráfico + Tabla
    ══════════════════════════════════════ */
    function renderModal(data) {
        document.getElementById('modal-scope-badge').textContent = data.scope;
        document.getElementById('modal-total-label').textContent =
            '— ' + data.totalDocentes.toLocaleString() + ' docentes en el universo';

        /* Tarjetas */
        var cardsHtml = '';
        data.modulos.forEach(function (m) {
            var cfg = CFG[m.key] || { circleBg: '#e2e3e5', iconColor: '#6c757d', pctColor: '#495057', lucide: 'folder' };
            var nombreCompleto = cfg.full ? cfg.full : m.nombre;
            cardsHtml +=
                '<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">' +
                    '<div class="card mod-card shadow-sm h-100 mb-0" style="border-left:4px solid ' + cfg.pctColor + ';">' +
                        '<div class="card-body py-3">' +
                            '<div class="d-flex align-items-center mb-2">' +
                                '<span class="rounded-circle d-flex align-items-center justify-content-center mr-2" ' +
                                      'style="width:36px;height:36px;background:' + cfg.circleBg + ';flex-shrink:0;">' +
                                    '<i data-lucide="' + cfg.lucide + '" style="width:20px;height:20px;color:' + cfg.iconColor + ';"></i>' +
                                '</span>' +
                                '<div class="font-weight-bold text-dark" style="font-size:0.83rem;line-height:1.25;" title="' + nombreCompleto + '">' + nombreCompleto + '</div>' +
                            '</div>' +
                            '<div class="d-flex justify-content-between align-items-end mb-1">' +
                                '<div>' +
                                    '<span class="h4 mb-0 font-weight-bold" style="color:' + cfg.pctColor + ';">' + m.porcentaje + '%</span>' +
                                    '<small class="text-muted ml-1">participación</small>' +
                                '</div>' +
                                '<div class="text-right">' +
                                    '<div class="small text-muted">' + m.docentes_con_registro.toLocaleString() + ' docentes</div>' +
                                    '<div class="small text-muted">' + m.registros.toLocaleString() + ' registros</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="progress" style="height:6px;border-radius:10px;">' +
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
            var nombreCompleto = cfg.full ? cfg.full : m.nombre;
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
        var bgColors = data.modulos.map(function (m) { return (CFG[m.key] || { pctColor:'#6c757d' }).pctColor; });
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
            var labelEl = document.getElementById('resumen-scope-label');
            if (labelEl) {
                labelEl.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i>Error al cargar</span>';
            }
            console.error('resumenModulos:', err);
        });
    }

    /* ══════════════════════════════════════
       Funciones globales para modal
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

    /* Cargar datos al iniciar */
    setTimeout(function () { cargarDatos(renderParticipacionBar); }, 50);
})();
</script>
@stop
