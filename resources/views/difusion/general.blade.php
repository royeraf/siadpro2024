@extends('adminlte::page')

@section('title', 'Acción de Difusión (General)')

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@vite(['resources/css/app.css'])
@endsection

@section('content_header')
    <x-section-heading icon="radio" color="blue">Acción de Difusión</x-section-heading>
@stop

@section('content')

<x-section-tabs :tabs="$tabs" color="blue" />

<!-- Tabla Base Reutilizable con Tailwind CSS y Alpine.js -->
<x-table-base id="tabla-difusiones-general"
              :perPage="request('per_page', 10)"
              :exportable="true"
              :searchable="true"
              exportFilename="acciones_difusion_general"
              :exportUrl="route('exportDifusionGeneral', request()->all())"
              :serverPaginated="true"
              :totalServerRecords="$accions->total()"
              :fromServer="$accions->firstItem() ?? 0"
              :toServer="$accions->lastItem() ?? 0"
              :filterAction="route('difusions.view')">
    <x-slot name="filters">
        <x-table-filter name="anio" label="Año" icon="calendar" :options="$listaAnios" :value="$anio" placeholder="Año actual" />
        <x-table-filter name="texto" label="DNI del Docente" icon="id-card" placeholder="Ingrese DNI" />

        @if($showFullFilters)
            {{-- UGEL → Institución → Docente: encadenados vía AJAX, igual que antes de la migración --}}
            <div x-data="{
                    ugel: @js((string) request('ugels', '')),
                    institucion: @js((string) request('instituciones', '')),
                    instQuery: @js((string) request('instituciones', '')),
                    instOpen: false,
                    instOptions: [],
                    docente: @js((string) request('docentes', '')),
                    docQuery: @js((string) request('docentes', '')),
                    docOpen: false,
                    docOptions: [],
                    async loadInstituciones() {
                        this.instOptions = [];
                        if (!this.ugel) return;
                        const anio = document.getElementById('anio')?.value || '';
                        const params = new URLSearchParams({ ugel: this.ugel, anio });
                        try {
                            const res = await fetch('{{ route('buscarInstitucionporUgel-dif') }}?' + params.toString(), {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            });
                            const data = await res.json();
                            this.instOptions = data.map(d => d.nomInstitucion);
                        } catch (e) {}
                    },
                    async loadDocentes() {
                        this.docOptions = [];
                        if (!this.institucion) return;
                        const anio = document.getElementById('anio')?.value || '';
                        const params = new URLSearchParams({ docente: this.institucion, ugel: this.ugel, anio });
                        try {
                            const res = await fetch('{{ route('buscarDocenteporInstitucion-dif') }}?' + params.toString(), {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            });
                            const data = await res.json();
                            this.docOptions = data.map(d => d.name);
                        } catch (e) {}
                    },
                    onUgelChange() {
                        this.institucion = ''; this.instQuery = ''; this.instOptions = [];
                        this.docente = ''; this.docQuery = ''; this.docOptions = [];
                        this.loadInstituciones();
                    },
                    selectInstitucion(name) {
                        this.institucion = name;
                        this.instQuery = name;
                        this.instOpen = false;
                        this.docente = ''; this.docQuery = ''; this.docOptions = [];
                        this.loadDocentes();
                    },
                    clearInstitucion() {
                        this.institucion = ''; this.instQuery = ''; this.instOpen = false;
                        this.docente = ''; this.docQuery = ''; this.docOptions = [];
                    },
                    selectDocente(name) {
                        this.docente = name;
                        this.docQuery = name;
                        this.docOpen = false;
                    },
                    clearDocente() {
                        this.docente = ''; this.docQuery = ''; this.docOpen = false;
                    },
                    get filteredInstituciones() {
                        const q = this.instQuery.toLowerCase();
                        return q ? this.instOptions.filter(o => o.toLowerCase().includes(q)) : this.instOptions;
                    },
                    get filteredDocentes() {
                        const q = this.docQuery.toLowerCase();
                        return q ? this.docOptions.filter(o => o.toLowerCase().includes(q)) : this.docOptions;
                    },
                 }"
                 x-init="if (ugel) loadInstituciones(); if (institucion) loadDocentes();"
                 class="contents">

                <div>
                    <label for="ugels" class="block text-xs font-semibold text-gray-600 mb-1">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 mr-1 inline-block align-text-bottom text-gray-400"></i>
                        UGEL
                    </label>
                    <select id="ugels" name="ugels" x-model="ugel" @change="onUgelChange()"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-2.5 bg-white">
                        <option value="">-- Todas las UGEL --</option>
                        @foreach ($listaUgels as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="instituciones" class="block text-xs font-semibold text-gray-600 mb-1">
                        <i data-lucide="school" class="w-3.5 h-3.5 mr-1 inline-block align-text-bottom text-gray-400"></i>
                        Institución
                    </label>
                    <div class="relative" @click.outside="instOpen = false">
                        <input type="hidden" name="instituciones" :value="institucion">
                        <input type="text" id="instituciones" x-model="instQuery" autocomplete="off"
                               :disabled="!ugel"
                               @focus="instOpen = true" @click="instOpen = true" @input="instOpen = true; if (instQuery === '') clearInstitucion()"
                               :placeholder="ugel ? 'Buscar institución...' : 'Selecciona una UGEL primero'"
                               class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 pl-2.5 pr-8 disabled:bg-gray-100 disabled:cursor-not-allowed">
                        <button type="button" x-show="instQuery.length > 0" @click="clearInstitucion()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                        <div x-show="instOpen"
                             class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-md shadow-lg text-sm">
                            <template x-if="filteredInstituciones.length === 0">
                                <div class="px-3 py-2 text-gray-400" x-text="ugel ? 'Sin resultados' : 'Selecciona una UGEL primero'"></div>
                            </template>
                            <template x-for="opt in filteredInstituciones.slice(0, 100)" :key="opt">
                                <div @click="selectInstitucion(opt)"
                                     class="px-3 py-2 cursor-pointer hover:bg-blue-50"
                                     :class="{ 'bg-blue-50 font-medium text-blue-700': opt === institucion }"
                                     x-text="opt"></div>
                            </template>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="docentes" class="block text-xs font-semibold text-gray-600 mb-1">
                        <i data-lucide="user" class="w-3.5 h-3.5 mr-1 inline-block align-text-bottom text-gray-400"></i>
                        Docente
                    </label>
                    <div class="relative" @click.outside="docOpen = false">
                        <input type="hidden" name="docentes" :value="docente">
                        <input type="text" id="docentes" x-model="docQuery" autocomplete="off"
                               :disabled="!institucion"
                               @focus="docOpen = true" @click="docOpen = true" @input="docOpen = true; if (docQuery === '') clearDocente()"
                               :placeholder="institucion ? 'Buscar docente...' : 'Selecciona una institución primero'"
                               class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 pl-2.5 pr-8 disabled:bg-gray-100 disabled:cursor-not-allowed">
                        <button type="button" x-show="docQuery.length > 0" @click="clearDocente()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                        <div x-show="docOpen"
                             class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-md shadow-lg text-sm">
                            <template x-if="filteredDocentes.length === 0">
                                <div class="px-3 py-2 text-gray-400" x-text="institucion ? 'Sin resultados' : 'Selecciona una institución primero'"></div>
                            </template>
                            <template x-for="opt in filteredDocentes.slice(0, 100)" :key="opt">
                                <div @click="selectDocente(opt)"
                                     class="px-3 py-2 cursor-pointer hover:bg-blue-50"
                                     :class="{ 'bg-blue-50 font-medium text-blue-700': opt === docente }"
                                     x-text="opt"></div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <x-table-filter name="docentes" label="Docente" icon="user" placeholder="Nombre del docente" />
        @endif
    </x-slot>
    <x-slot name="header">
        <tr>
            <th @click="sortBy(0)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Nombre de la Acción</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 0 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 0 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(1)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Descripción</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 1 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 1 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(2)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition" style="width: 120px;">
                <div class="flex items-center justify-between">
                    <span>Fecha</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 2 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 2 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th class="px-4 py-3 text-center no-export" style="width: 100px;">
                Documento
            </th>
            <th @click="sortBy(4)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Docente</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 4 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 4 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(5)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Cargo</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 5 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 5 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(6)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Institución</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 6 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 6 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(7)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Tipo de II.EE</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 7 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 7 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(8)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Provincia</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 8 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 8 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(9)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>Distrito</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 9 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 9 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
            <th @click="sortBy(10)" class="px-4 py-3 cursor-pointer hover:bg-blue-700 transition">
                <div class="flex items-center justify-between">
                    <span>UGEL</span>
                    <span class="flex items-center gap-1">
                        <span x-show="sortCol === 10 && sortAsc"><i data-lucide="arrow-up-narrow-wide" class="w-3.5 h-3.5"></i></span>
                        <span x-show="sortCol === 10 && !sortAsc"><i data-lucide="arrow-down-wide-narrow" class="w-3.5 h-3.5"></i></span>
                    </span>
                </div>
            </th>
        </tr>
    </x-slot>

    @include('difusion._rows_general')
</x-table-base>

<x-table-pagination id="tabla-difusiones-general" :paginator="$accions" />

@stop

@section('js')
@vite(['resources/js/app.js'])
@stop
