<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasScopeTabs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\AgendaEvidencia;
use App\Models\Ugel;
use App\Models\Institucion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AgendaViewController extends Controller
{
    use HasScopeTabs;

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:agendas.view')->only('index');
        $this->middleware('can:agendas.ugel')->only('ugel', 'buscarUgel');
        $this->middleware('can:agendas.general')->only('general', 'buscarGeneral', 'exportarTodos');
    }
    public function index()
    {
        $institucionId = Auth::user()->institucion_id;
        $events = Agenda::select('pro_agendas.*')
            ->join('users', 'users.id', '=', 'pro_agendas.idUser')
            ->where('users.institucion_id', $institucionId)
            ->where('pro_agendas.estado', '1')
            ->with(['user', 'dias'])
            ->withCount(['evidencias' => fn ($q) => $q->where('pro_agenda_evidencias.estado', 1)])
            ->get()
            ->map(fn ($e) => [
                'id'       => $e->id,
                'title'    => $e->title,
                'evento'   => $e->evento,
                'color'    => $e->color,
                'seccion'  => $e->seccion,
                'nomDocente' => $e->nomDocente ?? ($e->user->name ?? ''),
                'start'    => $this->isoDateTime($e->start),
                'end'      => $this->isoDateTime($e->end),
                'evidencias' => (int) ($e->evidencias_count ?? 0),
                'dias'     => $e->dias->map(fn ($d) => [
                    'id'          => $d->id,
                    'fecha'       => $d->fecha?->format('Y-m-d'),
                    'hora_inicio' => $d->hora_inicio ? substr($d->hora_inicio, 0, 5) : null,
                    'hora_fin'    => $d->hora_fin ? substr($d->hora_fin, 0, 5) : null,
                ])->values(),
            ]);

        $tabs = $this->tabsAgenda('view');

        return Inertia::render('Agenda/Director', [
            'events' => $events,
            'tabs'   => $tabs,
        ]);
    }

    /**
     * Igual que AgendaController::isoDateTime(): start/end son varchar en el
     * formato del input datetime-local y FullCalendar necesita ISO 8601.
     */
    private function isoDateTime(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $value = str_replace(' ', 'T', trim($value));

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
            return $value . ':00';
        }

        return $value;
    }

    /**
     * Expresión SQL que deriva "nivelinstitucion" (Escolarizado / No escolarizado -
     * PRONOEI) desde institucions.nivel (la columna legacy users.nivelinstitucion
     * ya no existe).
     */
    private function nivelInstitucionExpr(): string
    {
        return "CASE "
            . "WHEN institucions.nivel LIKE '%no escolariz%' OR institucions.nivel LIKE '%pronoei%' "
            . "THEN 'No escolarizado - PRONOEI' "
            . "WHEN institucions.nivel IS NULL OR TRIM(institucions.nivel) = '' THEN NULL "
            . "ELSE 'Escolarizado' END as nivelinstitucion";
    }

    /**
     * Aplica el filtro por "nivel" (Escolarizado / No escolarizado - PRONOEI)
     * usando institucions.nivel (requiere que la consulta ya haga join a institucions).
     */
    private function aplicarFiltroNivel($query, ?string $nivel): void
    {
        if ($nivel === null || $nivel === '') {
            return;
        }

        if ($nivel === 'No escolarizado - PRONOEI') {
            $query->where(function ($q) {
                $q->where('institucions.nivel', 'LIKE', '%No Escolariz%')
                  ->orWhere('institucions.nivel', 'LIKE', '%PRONOEI%');
            });
        } else {
            $query->where('institucions.nivel', 'NOT LIKE', '%No Escolariz%')
                  ->where('institucions.nivel', 'NOT LIKE', '%PRONOEI%');
        }
    }

    /**
     * Duplicado a propósito de AgendaController::tabsAgenda() — mismo criterio
     * que el resto de los módulos (cada controlador tiene su propia copia
     * pequeña en vez de forzar una abstracción compartida entre 2 clases).
     */
    private function tabsAgenda(string $activo): array
    {
        return $this->scopeTabs([
            'index'   => ['permission' => 'agendas.index', 'label' => 'Mis registros', 'route' => 'agendas.index'],
            'view'    => ['permission' => 'agendas.view', 'label' => 'Director', 'route' => 'agendas.view'],
            'ugel'    => ['permission' => 'agendas.ugel', 'label' => 'UGEL', 'route' => 'agenda.ugel'],
            'general' => ['permission' => 'agendas.general', 'label' => 'General', 'route' => 'agenda.general'],
        ], $activo);
    }

    public function ugel(Request $request)
    {
        $ugelId = Auth::user()->ugel_id;
        $anio = $request->filled('year') ? $request->input('year') : date('Y');

        $query = Agenda::select(
                "pro_agendas.id", "users.name as nomDocente", "pro_agendas.title",
                "pro_agendas.evento", "pro_agendas.color", "pro_agendas.start", "pro_agendas.end",
                "institucions.nomInstitucion as institucion",
                "institucions.provincia as provincia",
                "institucions.distrito as distrito",
                "ugeles.nombre as ugel"
            )
            ->addSelect(DB::raw($this->nivelInstitucionExpr()))
            ->join("users", "users.id", "=", "pro_agendas.idUser")
            ->leftJoin("institucions", "institucions.id", "=", "users.institucion_id")
            ->leftJoin("ugeles", "ugeles.id", "=", "users.ugel_id")
            ->where("users.ugel_id", $ugelId)
            ->where('pro_agendas.estado', '1')
            // Solo el año de inicio: filtrar también por `end` descartaba los
            // eventos que cruzan de año (11 en 2026), que no aparecían en
            // ningún año.
            ->whereYear('pro_agendas.start', $anio)
            ->withCount(['evidencias' => fn ($q) => $q->where('pro_agenda_evidencias.estado', 1)]);

        if ($request->filled('instituciones')) {
            $query->where('institucions.nomInstitucion', $request->input('instituciones'));
        }
        if ($request->filled('docentes')) {
            $query->where('users.name', $request->input('docentes'));
        }
        if ($request->filled('nivel')) {
            $this->aplicarFiltroNivel($query, $request->input('nivel'));
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_agendas.title', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_agendas.evento', 'LIKE', "%{$buscar}%")
                  ->orWhere('users.name', 'LIKE', "%{$buscar}%");
            });
        }

        $perPageRaw = $request->get('per_page', 10);
        if ($perPageRaw === 'all') {
            $perPage = 100000;
        } else {
            $perPage = (int) $perPageRaw;
            if (!in_array($perPage, [10, 15, 25, 50, 100])) {
                $perPage = 10;
            }
        }

        $agendas = $query->orderBy('pro_agendas.start', 'desc')->paginate($perPage)->withQueryString();

        $this->adjuntarEvidenciasPreview($agendas);

        if ($request->ajax() && !$request->header('X-Inertia')) {
            return response()->json([
                'rows' => view('agenda._rows_general', ['agendas' => $agendas])->render(),
                'pagination' => (string) $agendas->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $agendas->total(),
                'totalFormatted' => number_format($agendas->total()),
                'from' => $agendas->firstItem() ?? 0,
                'to' => $agendas->lastItem() ?? 0,
            ]);
        }

        $listaInstituciones = Institucion::where('ugel_id', $ugelId)->orderBy('nomInstitucion')->pluck('nomInstitucion');
        $listaDocentes = \App\Models\User::where('ugel_id', $ugelId)->whereNotNull('name')->where('name', '!=', '')->distinct()->orderBy('name')->pluck('name');
        $listaAnios = $this->listaAniosAgenda($anio);
        $tabs = $this->tabsAgenda('ugel');

        return Inertia::render('Agenda/Ugel', [
            'agendas'           => $agendas,
            'anio'              => (string) $anio,
            'listaInstituciones' => $listaInstituciones,
            'listaDocentes'     => $listaDocentes,
            'listaAnios'        => $listaAnios,
            'scope'             => 'ugel',
            'tabs'              => $tabs,
            'filters'           => $request->only(['year', 'instituciones', 'docentes', 'nivel', 'buscar', 'per_page']),
        ]);
    }

    public function buscarUgel(Request $request)
    {
        return $this->ugel($request);
    }

    public function general(Request $request)
    {
        $anio = $request->filled('year') ? $request->input('year') : date('Y');

        $query = Agenda::select(
                "pro_agendas.id", "users.name as nomDocente", "pro_agendas.title",
                "pro_agendas.evento", "pro_agendas.color", "pro_agendas.start", "pro_agendas.end",
                "institucions.nomInstitucion as institucion",
                "institucions.provincia as provincia",
                "institucions.distrito as distrito",
                "ugeles.nombre as ugel"
            )
            ->addSelect(DB::raw($this->nivelInstitucionExpr()))
            ->join("users", "users.id", "=", "pro_agendas.idUser")
            ->leftJoin("institucions", "institucions.id", "=", "users.institucion_id")
            ->leftJoin("ugeles", "ugeles.id", "=", "users.ugel_id")
            ->where('pro_agendas.estado', '1')
            // Ver comentario en ugel(): solo el año de inicio.
            ->whereYear('pro_agendas.start', $anio)
            ->withCount(['evidencias' => fn ($q) => $q->where('pro_agenda_evidencias.estado', 1)]);

        if ($request->filled('ugels')) {
            $query->where('ugeles.nombre', $request->input('ugels'));
        }
        if ($request->filled('instituciones')) {
            $query->where('institucions.nomInstitucion', $request->input('instituciones'));
        }
        if ($request->filled('docentes')) {
            $query->where('users.name', $request->input('docentes'));
        }
        if ($request->filled('nivel')) {
            $this->aplicarFiltroNivel($query, $request->input('nivel'));
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_agendas.title', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_agendas.evento', 'LIKE', "%{$buscar}%")
                  ->orWhere('users.name', 'LIKE', "%{$buscar}%");
            });
        }

        $perPageRaw = $request->get('per_page', 10);
        if ($perPageRaw === 'all') {
            $perPage = 100000;
        } else {
            $perPage = (int) $perPageRaw;
            if (!in_array($perPage, [10, 15, 25, 50, 100])) {
                $perPage = 10;
            }
        }

        $agendas = $query->orderBy('pro_agendas.start', 'desc')->paginate($perPage)->withQueryString();

        $this->adjuntarEvidenciasPreview($agendas);

        if ($request->ajax() && !$request->header('X-Inertia')) {
            return response()->json([
                'rows' => view('agenda._rows_general', ['agendas' => $agendas])->render(),
                'pagination' => (string) $agendas->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $agendas->total(),
                'totalFormatted' => number_format($agendas->total()),
                'from' => $agendas->firstItem() ?? 0,
                'to' => $agendas->lastItem() ?? 0,
            ]);
        }

        $listaUgels = Ugel::orderBy('nombre')->pluck('nombre');
        $listaAnios = $this->listaAniosAgenda($anio);
        $tabs = $this->tabsAgenda('general');

        return Inertia::render('Agenda/General', [
            'agendas'            => $agendas,
            'anio'               => (string) $anio,
            'listaUgels'         => $listaUgels,
            'listaAnios'         => $listaAnios,
            'filterActionRoute'  => 'agenda.general',
            'exportRoute'        => '/exportar-agendas',
            'scope'              => 'general',
            'tabs'               => $tabs,
            'filters'            => $request->only(['year', 'ugels', 'instituciones', 'docentes', 'nivel', 'buscar', 'per_page']),
        ]);
    }

    public function buscarGeneral(Request $request)
    {
        return $this->general($request);
    }

    /**
     * Adjunta a cada agenda de la página una vista previa (máx. `$limite`) de
     * sus archivos, para pintar el "stack" de iconos del listado sin cargar
     * todas las evidencias de todas las agendas. El total real ya viene en
     * `pro_agendas.evidencias_count` (withCount).
     */
    private function adjuntarEvidenciasPreview($agendas, int $limite = 3): void
    {
        $items = $agendas instanceof \Illuminate\Contracts\Pagination\Paginator
            ? $agendas->getCollection()
            : collect($agendas);

        // Salvaguarda para "per_page=all" (miles de agendas): no tiene sentido
        // traer la vista previa de todas; el listado muestra el contador.
        if ($items->isEmpty() || $items->count() > 200) {
            return;
        }

        $porAgenda = AgendaEvidencia::query()
            ->join('pro_agenda_dias', 'pro_agenda_dias.id', '=', 'pro_agenda_evidencias.agenda_dia_id')
            ->whereIn('pro_agenda_dias.agenda_id', $items->pluck('id')->all())
            ->where('pro_agenda_evidencias.estado', 1)
            ->orderBy('pro_agenda_evidencias.id')
            ->get([
                'pro_agenda_evidencias.id',
                'pro_agenda_evidencias.enlace',
                'pro_agenda_evidencias.nombre_original',
                'pro_agenda_dias.agenda_id',
            ])
            ->groupBy('agenda_id');

        foreach ($items as $agenda) {
            $preview = ($porAgenda[$agenda->id] ?? collect())
                ->take($limite)
                ->map(fn ($ev) => [
                    'id'              => $ev->id,
                    'enlace'          => $ev->enlace,
                    'nombre_original' => $ev->nombre_original,
                ])
                ->values()
                ->all();

            $agenda->setAttribute('evidencias_preview', $preview);
        }
    }

    /**
     * Años plausibles disponibles para el selector de filtro, descartando
     * fechas corruptas (p. ej. años como 23, 203 o 1978 por datos mal
     * digitados) — mismo criterio que los demás módulos migrados.
     */
    private function listaAniosAgenda(string $anioActual = null)
    {
        // Rango plausible 2010..año actual: sin el límite superior el selector
        // ofrecía años corruptos (hay eventos con start en 2027 cuyo end está
        // en 2024/2026 — error de tecla al digitar el año).
        $listaAnios = Agenda::whereYear('start', '>=', 2010)
            ->whereYear('start', '<=', (int) date('Y'))
            ->selectRaw('DISTINCT YEAR(start) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');

        $anioActual = $anioActual ?? date('Y');
        if (!$listaAnios->contains($anioActual)) {
            $listaAnios->prepend($anioActual);
        }

        return $listaAnios;
    }



    public function obtenerUgels(Request $request)
    {        
        $year = $request->get('year', date('Y')); // defecto: año en curso
        
        $ugels = DB::table('institucions')
            ->select('ugeles.nombre as ugel', DB::raw('count(distinct pro_agendas.idUser) as docentes_count'))
            ->join('users', 'users.institucion_id', '=', 'institucions.id')
            ->join('ugeles', 'ugeles.id', '=', 'institucions.ugel_id')
            ->join('pro_agendas', 'users.id', '=', 'pro_agendas.idUser')
            ->where('pro_agendas.estado', '1')
            ->whereYear('start', $year)
            ->whereYear('end', $year)
            ->groupBy('ugeles.nombre')
            ->get();

        return response()->json($ugels);
    }
    public function obtenerInstitucions(Request $request)
    {        
        $ugelId = Auth::user()->ugel_id;
        $year = $request->get('year', date('Y')); // defecto: año en curso
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', 'users.institucion_id', '=', 'institucions.id')
            ->leftJoin('pro_agendas', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_agendas.idUser')
                    ->whereYear('pro_agendas.start', $year)
                    ->whereYear('pro_agendas.end', $year)
                    ->where('pro_agendas.estado', '=', '1');
            })
            ->where('institucions.ugel_id', '=', $ugelId)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_agendas.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        // Calcular la cantidad total de docentes por institución
        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', 'users.institucion_id', '=', 'institucions.id')
            ->where('institucions.ugel_id', '=', $ugelId)
            ->select('institucions.nomInstitucion', DB::raw('count(users.id) as total_docentes'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $resultados = $resultados->map(function ($item) use ($totalDocentes) {
            $total = $totalDocentes->firstWhere('nomInstitucion', $item->nomInstitucion);
            $item->total_docentes = $total ? $total->total_docentes : 0;
            return $item;
        });
            
        return response()->json($resultados);
    }

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
        $ugelId = Ugel::resolverId($ugelSeleccionada);
        $year = $request->input('year', date('Y')); // defecto: año en curso
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', 'users.institucion_id', '=', 'institucions.id')
            ->leftJoin('pro_agendas', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_agendas.idUser')
                    ->whereYear('pro_agendas.start', $year);
            })
            ->where('institucions.ugel_id', '=', $ugelId)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_agendas.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', 'users.institucion_id', '=', 'institucions.id')
            ->where('institucions.ugel_id', '=', $ugelId)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct users.id) as total_docentes'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $resultados = $resultados->map(function ($item) use ($totalDocentes) {
            $total = $totalDocentes->firstWhere('nomInstitucion', $item->nomInstitucion);
            $item->total_docentes = $total ? $total->total_docentes : 0;
            return $item;
        });
            
        return response()->json($resultados);
    }
    public function buscadorinstitucion(Request $request)
    {   
        $cargo = Auth::user()->cargo;
        switch ($cargo) {
            case 'Especialista UGEL':
                $ugel = Auth::user()->ugel;
                break;
            case 'Especialista DRE':
                $ugel = $request->input('ugel'); 
                break;
            default:
                $ugel = $request->input('ugel'); 
                break;
        }
        
        $ugelId = Ugel::resolverId($ugel);
        $term = $request->input('term'); // Obtén el término de búsqueda del formulario
        $year = $request->input('year', date('Y')); // defecto: año en curso

        // Realiza una consulta para buscar instituciones que coincidan con $term y tengan información sobre docentes y agendas
        $resultados = DB::table('institucions')
            ->leftJoin('users', 'users.institucion_id', '=', 'institucions.id')
            ->leftJoin('pro_agendas', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_agendas.idUser')
                    ->whereYear('pro_agendas.start', $year)
                    ->whereYear('pro_agendas.end', $year);
            })
            ->where('institucions.ugel_id', '=', $ugelId)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_agendas.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', 'users.institucion_id', '=', 'institucions.id')
            ->where('institucions.ugel_id', '=', $ugelId)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
            ->select('institucions.nomInstitucion', DB::raw('count(users.id) as total_docentes'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        // Combina los resultados de agendas y docentes por institución
        $resultados = $resultados->map(function ($item) use ($totalDocentes) {
            $total = $totalDocentes->firstWhere('nomInstitucion', $item->nomInstitucion);
            $item->total_docentes = $total ? $total->total_docentes : 0;
            return $item;
        });

        return response()->json($resultados);
    }
    public function buscarDocenteporInstitucion(Request $request)
    {
        \Log::info('Parámetros recibidos en buscarDocenteporInstitucion:', $request->all());
        
        $cargo = Auth::user()->cargo;
        // Determinar la UGEL seleccionada
        if ($cargo == "Especialista UGEL") {
            $ugelSeleccionada = Auth::user()->ugel;
        } else {
            $ugelSeleccionada = $request->input('ugel', $request->input('ugels', ''));
        }

        $institucionSeleccionada = $request->input('docente');
        $institucionId = Institucion::where('nomInstitucion', $institucionSeleccionada)->value('id');
        $year = $request->input('year', date('Y')); // defecto: año en curso
        
        \Log::info('Buscando docentes para institución y UGEL:', [
            'institucion' => $institucionSeleccionada, 
            'ugel' => $ugelSeleccionada,
            'year' => $year
        ]);

        try {
            // Consulta a la base de datos
            $query = DB::table('users')
                ->leftJoin('pro_agendas', function($join) use ($year) {
                    $join->on('users.id', '=', 'pro_agendas.idUser')
                        ->where('pro_agendas.estado', '=', '1')
                        ->whereYear('pro_agendas.start', $year);
                })
                ->where('users.estado', '=', '1');  // Solo usuarios activos
                
            if ($institucionId) {
                $query->where('users.institucion_id', '=', $institucionId);
            }

            // Aplicar filtro por UGEL si está presente
            if (!empty($ugelSeleccionada)) {
                $ugelId = Ugel::resolverId($ugelSeleccionada);
                if ($ugelId) {
                    $query->where('users.ugel_id', '=', $ugelId);
                }
            }
            
            // Agrupar por nombre de usuario y contar agendas
            $docentes = $query->select('users.name', DB::raw('count(pro_agendas.id) as agendas_count'))
                ->groupBy('users.name')
                ->get();
            
            \Log::info('Docentes encontrados:', ['count' => $docentes->count(), 'data' => $docentes]);
            
            return response()->json($docentes);
        } catch (\Exception $e) {
            \Log::error('Error al buscar docentes:', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function buscadordocente(Request $request)
    {
        $institucion = $request->input('institucion'); 
        $institucionId = Institucion::where('nomInstitucion', $institucion)->value('id');
        $term = $request->input('term'); // Obtén el término de búsqueda del formulario
        $year = $request->input('year', date('Y')); // defecto: año en curso

        $docentes = DB::table('users')
            ->leftJoin('pro_agendas', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_agendas.idUser')
                    ->whereYear('pro_agendas.start', $year)
                    ->whereYear('pro_agendas.end', $year);
            })
            ->when($institucionId, fn ($q) => $q->where('users.institucion_id', '=', $institucionId))
            ->where('users.name', 'like', '%' . $term . '%')
            ->select('users.name', DB::raw('count(pro_agendas.idUser) as agendas_count'))
            ->groupBy('users.name')
            ->having('agendas_count', '>', 1) // Filtra solo docentes con al menos 1 agenda
            ->get();
        
        // Simplificado para la respuesta final
        $docentes = DB::table('users')
            ->where('name', 'like', '%' . $term . '%')
            ->when($institucionId, fn ($q) => $q->where('institucion_id', $institucionId))
            ->pluck('name');

        return response()->json($docentes);
    }

    public function exportarTodos(Request $request)
    {
        // Obtener parámetros de filtro
        $ugel = trim($request->get('ugels', ''));
        $instituciones = trim($request->get('instituciones', ''));
        $docentes = trim($request->get('docentes', ''));
        $nivel = trim($request->get('nivel', ''));
        $year = trim($request->get('year', date('Y'))); // defecto: año en curso

        // Construir la consulta
        $query = Agenda::select(
            "users.name as nomDocente", "pro_agendas.title", "pro_agendas.evento", "pro_agendas.color",
            "pro_agendas.start", "pro_agendas.end",
            "institucions.nomInstitucion as institucion",
            "institucions.provincia as provincia",
            "institucions.distrito as distrito",
            "ugeles.nombre as ugel"
        )
        ->addSelect(DB::raw($this->nivelInstitucionExpr()))
        ->join("users", "users.id", "=", "pro_agendas.idUser")
        ->leftJoin("institucions", "institucions.id", "=", "users.institucion_id")
        ->leftJoin("ugeles", "ugeles.id", "=", "users.ugel_id")
        ->where('pro_agendas.estado', '1')
        ->whereYear('pro_agendas.start', $year);

        // Aplicar los filtros
        if (!empty($ugel)) {
            $query->where("ugeles.nombre", $ugel);
        }
        
        if (!empty($instituciones)) {
            $query->where("institucions.nomInstitucion", $instituciones);
        }
        
        if (!empty($docentes)) {
            $query->where("users.name", $docentes);
        }
        
        // Aplicar filtro por nivel
        if (!empty($nivel)) {
            $this->aplicarFiltroNivel($query, $nivel);
        }

        // Búsqueda libre (misma que el listado general).
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_agendas.title', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_agendas.evento', 'LIKE', "%{$buscar}%")
                  ->orWhere('users.name', 'LIKE', "%{$buscar}%");
            });
        }

        // Obtener TODOS los resultados (sin paginar)
        $agendas = $query->orderBy('pro_agendas.start', 'desc')->get();

        // Determinar formato
        $format = $request->get('format', 'excel');
        
        // Exportar en el formato correspondiente
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($agendas);
            case 'csv':
                return $this->exportToCsv($agendas);
            default:
                return $this->exportToExcel($agendas);
        }
    }

    private function exportToExcel($agendas)
    {
        // Configurar cabeceras para Excel
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=agendas.xls',
        ];

        // Generar contenido HTML que Excel puede interpretar
        $content = '<table border="1">';
        $content .= '<tr><th>Docente</th><th>Nombre de la agenda</th><th>Descripción</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Institución</th><th>Nivel</th><th>Provincia</th><th>Distrito</th><th>UGEL</th></tr>';
        
        foreach ($agendas as $item) {
            $content .= '<tr>';
            $content .= '<td>' . $item->nomDocente . '</td>';
            $content .= '<td>' . $item->title . '</td>';
            $content .= '<td>' . $item->evento . '</td>';
            $content .= '<td>' . date('d-m-Y', strtotime($item->start)) . '</td>';
            $content .= '<td>' . date('d-m-Y', strtotime($item->end)) . '</td>';
            $content .= '<td>' . $item->institucion . '</td>';
            $content .= '<td>' . $item->nivelinstitucion . '</td>'; // Incluir nivel
            $content .= '<td>' . $item->provincia . '</td>';
            $content .= '<td>' . $item->distrito . '</td>';
            $content .= '<td>' . $item->ugel . '</td>';
            $content .= '</tr>';
        }
        
        $content .= '</table>';
        
        return response($content, 200, $headers);
    }

    private function exportToCsv($agendas)
    {
        // Configurar cabeceras para CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=agendas.csv',
        ];

        $callback = function() use ($agendas) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM para Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeceras
            fputcsv($file, [
                'Docente', 
                'Nombre de la agenda', 
                'Descripción', 
                'Fecha Inicio', 
                'Fecha Fin', 
                'Institución', 
                'Nivel',  // Incluir nivel
                'Provincia', 
                'Distrito', 
                'UGEL'
            ]);
            
            // Datos
            foreach ($agendas as $item) {
                fputcsv($file, [
                    $item->nomDocente,
                    $item->title,
                    $item->evento,
                    date('d-m-Y', strtotime($item->start)),
                    date('d-m-Y', strtotime($item->end)),
                    $item->institucion,
                    $item->nivelinstitucion, // Incluir nivel
                    $item->provincia,
                    $item->distrito,
                    $item->ugel
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}


