<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Concerns\HasScopeTabs;
use App\Http\Controllers\Concerns\ValidaDocumento;
use App\Http\Controllers\Concerns\HasSorting;
use App\Models\Informe;
use App\Models\User;
use App\Models\Ugel;
use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InformeController extends Controller
{
    use HasScopeTabs;
    use ValidaDocumento;
    use HasSorting;

    /** Regla de tipo de archivo admitida por este recurso. */
    protected $documentoTypeRule = 'mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:informes.index')->only('index');
        $this->middleware('can:informes.create')->only('create', 'store');
        $this->middleware('can:informes.edit')->only('edit', 'update');
        $this->middleware('can:informes.destroy')->only('destroy');
        $this->middleware('can:informes.view')->only('general');
        $this->middleware('can:informes.ugel')->only('ugel');
        $this->middleware('can:informes.director')->only('director');
        // Los buscar*/exportarTodos son alcanzables por URL directa (rutas
        // /buscar-informe*, /exportar-biblioteca) y no quedaban cubiertos por
        // ningún permiso propio: un director podía leer el alcance General o
        // UGEL entero por ahí, sin tener informes.view/informes.ugel.
        $this->middleware('can:informes.index')->only('buscar');
        $this->middleware('can:informes.view')->only('buscarGeneral', 'exportarTodos');
        $this->middleware('can:informes.ugel')->only('buscarUgel');
        $this->middleware('can:informes.director')->only('buscarDirector');
    }
    
    public function index(Request $request)
    {
        $usuario = Auth::user()->id;

        $query = Informe::with('getUser')->where('estado', '1')->where('idUser', $usuario);

        if ($request->filled('texto')) {
            $query->where('nombreInforme', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('fecha')) {
            $query->where('fecha', 'LIKE', '%' . $request->input('fecha') . '%');
        }
        if ($request->filled('year')) {
            $query->whereYear('fecha', $request->input('year'));
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('nombreInforme', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = $this->applySort($request, $query, [
            'nombreInforme' => 'pro_informes.nombreInforme',
            'fecha' => 'pro_informes.fecha',
        ], 'pro_informes.id');

        $informes = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() && !$request->header('X-Inertia')) {
            return response()->json([
                'rows' => view('informe._rows', ['informes' => $informes])->render(),
                'pagination' => (string) $informes->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $informes->total(),
                'totalFormatted' => number_format($informes->total()),
                'from' => $informes->firstItem() ?? 0,
                'to' => $informes->lastItem() ?? 0,
            ]);
        }

        return Inertia::render('Informe/Index', [
            'informes' => $informes,
            'listaAnios' => $this->listaAniosInforme(),
            'filters' => $request->only(['year', 'texto', 'fecha', 'buscar', 'per_page', 'sort', 'direction']),
            'tabs' => $this->tabsInforme('index'),
            'can' => [
                'create'  => Auth::user()->can('informes.create'),
                'edit'    => Auth::user()->can('informes.edit'),
                'destroy' => Auth::user()->can('informes.destroy'),
                'view'    => Auth::user()->can('informes.view'),
            ],
        ]);
    }

    /**
     * Pestañas de "Biblioteca del Aula" — a diferencia de Evidencia/Sectores,
     * aquí Director SÍ tiene enlace de menú, así que va incluido.
     */
    private function tabsInforme(string $activo): array
    {
        return $this->scopeTabs([
            'index'    => ['permission' => 'informes.index', 'label' => 'Mis registros', 'route' => 'informes.index'],
            'ugel'     => ['permission' => 'informes.ugel', 'label' => 'UGEL', 'route' => 'informes.ugel'],
            'general'  => ['permission' => 'informes.view', 'label' => 'General', 'route' => 'informes.view'],
            'director' => ['permission' => 'informes.director', 'label' => 'Director', 'route' => 'informes.director'],
        ], $activo);
    }

    /**
     * Punto de entrada del menú. informes.index (Mis registros) NO incluye a
     * EspecDRE/EspecUGEL/Director — solo tienen .view/.ugel/.director
     * respectivamente — así que la entrada de menú no puede apuntar fijo a
     * /informes o esos roles se quedan sin poder llegar a nada. Redirige a la
     * primera pestaña a la que el usuario realmente tenga acceso.
     */
    public function landing()
    {
        $tabs = $this->tabsInforme('index');
        abort_if(empty($tabs), 403);

        return redirect($tabs[0]['url']);
    }

    /**
     * Años plausibles disponibles para el selector de filtro, descartando
     * fechas corruptas (p. ej. años como 23, 203 o 1978 por datos mal
     * digitados) — mismo criterio que EvidenciaController.
     */
    private function listaAniosInforme(string $anioActual = null)
    {
        $listaAnios = Informe::whereYear('fecha', '>=', 2010)
            ->selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');

        $anioActual = $anioActual ?? date('Y');
        if (!$listaAnios->contains($anioActual)) {
            $listaAnios->prepend($anioActual);
        }

        return $listaAnios;
    }

    /**
     * Base común de las tres vistas de alcance (General / UGEL / Director):
     * mismas columnas, mismo join con users y mismo filtro de año. Cada alcance
     * añade después su propia condición de ámbito y sus propios filtros.
     */
    private function informesScopeBase(Request $request): array
    {
        $anio = $request->filled('year') ? $request->input('year') : date('Y');

        $query = Informe::select(
                "pro_informes.id", "pro_informes.nombreInforme", "pro_informes.descripcion",
                "pro_informes.documento", "pro_informes.color", "pro_informes.fecha",
                "pro_informes.enlace",
                "users.name", "users.cargo", "users.dni",
                "institucions.nomInstitucion as institucion",
                "institucions.provincia as provincia",
                "institucions.distrito as distrito",
                "ugeles.nombre as ugel"
            )
            ->addSelect(DB::raw($this->nivelInstitucionExpr()))
            ->join("users", "users.id", "=", "pro_informes.idUser")
            ->leftJoin("institucions", "institucions.id", "=", "users.institucion_id")
            ->leftJoin("ugeles", "ugeles.id", "=", "users.ugel_id")
            ->where('pro_informes.estado', '1')
            ->whereYear('pro_informes.fecha', $anio);

        return [$query, $anio];
    }

    /**
     * Expresión SQL que deriva "nivelinstitucion" (Escolarizado / No escolarizado -
     * PRONOEI) desde institucions.nivel, para conservar la salida que antes venía
     * de la columna legacy users.nivelinstitucion.
     */
    private function nivelInstitucionExpr(): string
    {
        return "CASE "
            . "WHEN institucions.nivel LIKE '%no escolariz%' OR institucions.nivel LIKE '%pronoei%' "
            . "THEN 'No escolarizado - PRONOEI' "
            . "WHEN institucions.nivel IS NULL OR TRIM(institucions.nivel) = '' THEN NULL "
            . "ELSE 'Escolarizado' END as nivelinstitucion";
    }

    private function paginateInformes(Request $request, $query)
    {
        $perPageRaw = $request->get('per_page', 10);
        if ($perPageRaw === 'all') {
            $perPage = 100000;
        } else {
            $perPage = (int) $perPageRaw;
            if (!in_array($perPage, [10, 15, 25, 50, 100])) {
                $perPage = 10;
            }
        }

        $query = $this->applySort($request, $query, [
            'nombreInforme' => 'pro_informes.nombreInforme',
            'name' => 'users.name',
            'fecha' => 'pro_informes.fecha',
        ], 'pro_informes.fecha');

        return $query->paginate($perPage)->withQueryString();
    }

    private function ajaxInformesResponse(Request $request, $informes)
    {
        return response()->json([
            'rows' => view('informe._rows_general', ['informes' => $informes])->render(),
            'pagination' => (string) $informes->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
            'total' => $informes->total(),
            'totalFormatted' => number_format($informes->total()),
            'from' => $informes->firstItem() ?? 0,
            'to' => $informes->lastItem() ?? 0,
        ]);
    }

    public function general(Request $request)
    {
        [$query, $anio] = $this->informesScopeBase($request);

        if ($request->filled('texto')) {
            $query->where('users.dni', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('docentes')) {
            $query->where('users.name', 'LIKE', '%' . $request->input('docentes') . '%');
        }
        if ($request->filled('ugels')) {
            $query->where('ugeles.nombre', $request->input('ugels'));
        }
        if ($request->filled('instituciones')) {
            $query->where('institucions.nomInstitucion', 'LIKE', '%' . $request->input('instituciones') . '%');
        }
        if ($request->filled('nivel')) {
            $nivel = $request->input('nivel');
            if ($nivel === 'No escolarizado - PRONOEI') {
                $query->where(function ($q) {
                    $q->where('institucions.nivel', 'LIKE', '%No Escolariz%')
                      ->orWhere('institucions.nivel', 'LIKE', '%PRONOEI%');
                });
            } elseif ($nivel === 'Escolarizado') {
                $query->where('institucions.nivel', 'NOT LIKE', '%No Escolariz%')
                      ->where('institucions.nivel', 'NOT LIKE', '%PRONOEI%');
            }
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_informes.nombreInforme', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_informes.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $informes = $this->paginateInformes($request, $query);

        if ($request->ajax() && !$request->header('X-Inertia')) {
            return $this->ajaxInformesResponse($request, $informes);
        }

        return Inertia::render('Informe/General', [
            'informes' => $informes,
            'anio' => (string) $anio,
            'showFullFilters' => true,
            'listaUgels' => Ugel::orderBy('nombre')->pluck('nombre'),
            'listaInstituciones' => [],
            'listaAnios' => $this->listaAniosInforme($anio),
            'filterActionRoute' => 'informes.view',
            'exportRoute' => '/exportar-biblioteca',
            'scope' => 'general',
            'tabs' => $this->tabsInforme('general'),
            'filters' => $request->only(['year', 'texto', 'ugels', 'instituciones', 'docentes', 'nivel', 'buscar', 'per_page', 'sort', 'direction']),
        ]);
    }

    public function ugel(Request $request)
    {
        [$query, $anio] = $this->informesScopeBase($request);
        $query->where('users.ugel_id', Auth::user()->ugel_id);

        if ($request->filled('texto')) {
            $query->where('users.dni', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('instituciones')) {
            $query->where('institucions.nomInstitucion', 'LIKE', '%' . $request->input('instituciones') . '%');
        }
        if ($request->filled('nivel')) {
            $nivel = $request->input('nivel');
            if ($nivel === 'No escolarizado - PRONOEI') {
                $query->where(function ($q) {
                    $q->where('institucions.nivel', 'LIKE', '%No Escolariz%')
                      ->orWhere('institucions.nivel', 'LIKE', '%PRONOEI%');
                });
            } elseif ($nivel === 'Escolarizado') {
                $query->where('institucions.nivel', 'NOT LIKE', '%No Escolariz%')
                      ->where('institucions.nivel', 'NOT LIKE', '%PRONOEI%');
            }
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_informes.nombreInforme', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_informes.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $informes = $this->paginateInformes($request, $query);

        if ($request->ajax() && !$request->header('X-Inertia')) {
            return $this->ajaxInformesResponse($request, $informes);
        }

        $listaInstituciones = Institucion::where('ugel_id', Auth::user()->ugel_id)->orderBy('nomInstitucion')->pluck('nomInstitucion');

        return Inertia::render('Informe/General', [
            'informes' => $informes,
            'anio' => (string) $anio,
            'showFullFilters' => false,
            'listaUgels' => [],
            'listaInstituciones' => $listaInstituciones,
            'listaAnios' => $this->listaAniosInforme($anio),
            'filterActionRoute' => 'informes.ugel',
            'exportRoute' => '/exportar-biblioteca',
            'scope' => 'ugel',
            'tabs' => $this->tabsInforme('ugel'),
            'filters' => $request->only(['year', 'texto', 'instituciones', 'nivel', 'buscar', 'per_page', 'sort', 'direction']),
        ]);
    }

    public function director(Request $request)
    {
        [$query, $anio] = $this->informesScopeBase($request);
        $query->where('users.institucion_id', Auth::user()->institucion_id);

        if ($request->filled('texto')) {
            $query->where('pro_informes.nombreInforme', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('fecha')) {
            $query->where('pro_informes.fecha', 'LIKE', '%' . $request->input('fecha') . '%');
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_informes.nombreInforme', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_informes.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $informes = $this->paginateInformes($request, $query);

        if ($request->ajax() && !$request->header('X-Inertia')) {
            return $this->ajaxInformesResponse($request, $informes);
        }

        return Inertia::render('Informe/General', [
            'informes' => $informes,
            'anio' => (string) $anio,
            'showFullFilters' => false,
            'listaUgels' => [],
            'listaInstituciones' => [],
            'listaAnios' => $this->listaAniosInforme($anio),
            'filterActionRoute' => 'informes.director',
            'exportRoute' => '/exportar-biblioteca',
            'scope' => 'director',
            'tabs' => $this->tabsInforme('director'),
            'filters' => $request->only(['year', 'texto', 'fecha', 'buscar', 'per_page', 'sort', 'direction']),
        ]);
    }

    public function profesorcoordinador()
    {
        $institucionId = Auth::user()->institucion_id;
        $year = request()->get('year', '2026');
        $informes = Informe::select("pro_informes.id","pro_informes.nombreInforme","pro_informes.documento","pro_informes.fecha","pro_informes.color","pro_informes.descripcion","users.name","institucions.nomInstitucion as institucion","institucions.provincia as provincia","institucions.distrito as distrito","users.cargo","ugeles.nombre as ugel")
            ->addSelect(DB::raw($this->nivelInstitucionExpr()))
            ->join("users","users.id","=","pro_informes.idUser")
            ->leftJoin("institucions","institucions.id","=","users.institucion_id")
            ->leftJoin("ugeles","ugeles.id","=","users.ugel_id")
            ->where("users.institucion_id", $institucionId)
            ->where('pro_informes.estado', '1')
            ->whereYear('pro_informes.fecha', $year)
            ->orderby('pro_informes.id','desc')
            ->paginate(10);
            return view("informe.coordinador",compact('informes'));
    }

    public function download($id)
    {
        $informe = Informe::findOrFail($id);
        $pathToFile = storage_path('app/public/' . $informe->enlace);
        
        // Verificar si el archivo existe antes de descargarlo
        
        return response()->download($pathToFile);
    }
    public function buscar(Request $request)
    {
        return $this->index($request);
    }

    public function buscarGeneral(Request $request)
    {
        return $this->general($request);
    }

    public function buscarUgel(Request $request)
    {
        return $this->ugel($request);
    }

    public function buscarDirector(Request $request)
    {
        return $this->director($request);
    }

    
    public function create()
    {
        return view('informe.create');
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'documento' => $this->documentoRules(),
        ], $this->documentoMessages());

        $file = $request->file('documento');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $dateTimeNow = now()->format('Ymd_His_u');
        $fileContent = $request->get('nombreInforme').' '.$dateTimeNow.'.'. $extension;
        $route = 'informe';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la función storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
        
        $informes = new Informe;
        $informes->enlace = $route . '/' . $fileContent;
        $informes->nombreInforme = $request->get('nombreInforme');
        $informes->fecha = $request->get('fecha');
        $informes->descripcion = $request->get('descripcion');
        $informes->idUser = Auth::user()->id;
        $informes->estado = 1;
        $informes->save();
        
        return redirect('/informes')->with('success', '¡Registro guardado con éxito!');
    }

    public function show()
    {
        //
    }

    
    public function edit($id)
    {
        $informe = Informe::findOrFail($id);
        return view('informe.edit')->with('informe', $informe);
    }

    
    public function update(Request $request, Informe $informe)
    {
        $request->validate([
            'nombreInforme' => 'required|string|max:191',
            'descripcion' => 'required|string',
            'fecha' => 'required|date',
            'documento' => $this->documentoRules(false),
        ], $this->documentoMessages(false));
        
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            $extension = $file->getClientOriginalExtension();
            $dateTimeNow = now()->format('Ymd_His_u');
            $fileContent = $request->get('nombreInforme').' '.$dateTimeNow.'.'. $extension;
            $route = 'informe';
            
            // Asegurarse de que la carpeta existe y tiene los permisos correctos
            Storage::makeDirectory('public/' . $route);
            Storage::disk('public')->setVisibility($route, 'public');
            
            // Almacenar el archivo
            Storage::putFileAs('public/' . $route, $file, $fileContent);

            // Eliminar el archivo antiguo si existía
            if (!empty($informe->enlace)) {
                Storage::delete('public/'.$informe->enlace);
            }

            $informe->enlace = $route . '/' . $fileContent;
        }

        $informe->nombreInforme = $request->get('nombreInforme');
        $informe->fecha = $request->get('fecha');
        $informe->descripcion = $request->get('descripcion');
        $informe->idUser = Auth::user()->id;
        $informe->estado = 1;
        $informe->save();
        
        return redirect('/informes')->with('success', '¡Registro actualizado con éxito!');
    }

   
    public function destroy(Informe $informe)
    {
        Storage::delete('public/'.$informe->enlace);
        $informe->estado = 0;
        $informe->idUser = Auth::user()->id;
        $informe->save();
        session()->flash('success', '¡Registro eliminado!');
        return redirect('/informes');
    }

    public function obtenerUgels()
    {       
        $year = request()->get('year', '2026'); // Valor predeterminado: 2026
        
        $ugels = DB::table('pro_informes')
            ->select('ugeles.nombre as ugel', DB::raw('count(distinct pro_informes.idUser) as docentes_count'))
            ->join('users', 'pro_informes.idUser', '=', 'users.id')
            ->join('ugeles', 'ugeles.id', '=', 'users.ugel_id')
            ->where('pro_informes.estado', '1')
            ->whereYear('fecha', $year)
            ->groupBy('ugeles.nombre')
            ->get();

        return response()->json($ugels);
    }

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
        $ugelId = Ugel::resolverId($ugelSeleccionada);

            $resultados = DB::table('institucions')
            ->leftJoin('users', 'users.institucion_id', '=', 'institucions.id')
            ->leftJoin('pro_informes', function($join) {
                $join->on('users.id', '=', 'pro_informes.idUser')
                     ->where('pro_informes.estado', '=', '1');
            })
            ->where('institucions.ugel_id', '=', $ugelId)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_informes.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

            $totalDocentes = DB::table('institucions')
            ->leftJoin('users', 'users.institucion_id', '=', 'institucions.id')
            ->leftJoin('pro_informes', function($join) {
                $join->on('users.id', '=', 'pro_informes.idUser')
                     ->where('pro_informes.estado', '=', '1');
            })
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
    
            // Realiza una consulta para buscar instituciones que coincidan con $term y tengan información sobre docentes y agendas
                $resultados = DB::table('institucions')
                ->leftJoin('users', 'users.institucion_id', '=', 'institucions.id')
                ->leftJoin('pro_informes', function($join) {
                    $join->on('users.id', '=', 'pro_informes.idUser')
                         ->where('pro_informes.estado', '=', '1');
                })
                ->where('institucions.ugel_id', '=', $ugelId)
                ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
                ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_informes.idUser) as agendas_count'))
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
        // Agrega log para depuración
        \Log::info('Parámetros recibidos en buscarDocenteporInstitucion:', $request->all());
        
        $cargo = Auth::user()->cargo;
        // Corrige el operador de asignación a comparación
        if ($cargo == "Especialista UGEL") {
            $ugelSeleccionada = Auth::user()->ugel;
        } else {         
            // Acepta ambos nombres de parámetros para mayor compatibilidad
            $ugelSeleccionada = $request->input('ugel', $request->input('ugels', ''));
        }

        $institucionSeleccionada = $request->input('docente');
        $institucionId = Institucion::where('nomInstitucion', $institucionSeleccionada)->value('id');
        $ugelId = Ugel::resolverId($ugelSeleccionada);
        
        \Log::info('Buscando docentes para:', [
            'institucion' => $institucionSeleccionada, 
            'ugel' => $ugelSeleccionada
        ]);

        $query = DB::table('users')
            ->leftJoin('pro_informes', function($join) {
                $join->on('users.id', '=', 'pro_informes.idUser')
                    ->where('pro_informes.estado', '=', '1');
            });

        if ($institucionId) {
            $query->where('users.institucion_id', '=', $institucionId);
        }
        if ($ugelId) {
            $query->where('users.ugel_id', '=', $ugelId);
        }

        $docentes = $query
            ->select('users.name', DB::raw('count(pro_informes.id) as agendas_count'))
            ->groupBy('users.name')
            ->having('agendas_count', '>=', 0)
            ->get();

        \Log::info('Docentes encontrados: ' . $docentes->count());
        
        return response()->json($docentes);
    }
    public function buscadordocente(Request $request)
    {
        $institucion = $request->input('institucion'); 
        $term = $request->input('term');
        $institucionId = Institucion::where('nomInstitucion', $institucion)->value('id');

        $query = DB::table('users')
        ->leftJoin('pro_informes', function($join) {
            $join->on('users.id', '=', 'pro_informes.idUser')
                 ->where('pro_informes.estado', '=', '1');
        });

        if ($institucionId) {
            $query->where('users.institucion_id', '=', $institucionId);
        }

        $docentes = $query
        ->where('users.name', 'like', '%' . $term . '%')
        ->select('users.name', DB::raw('count(pro_informes.idUser) as agendas_count'))
        ->groupBy('users.name')
        ->having('agendas_count', '>=', 0) 
        ->get();
        return response()->json($docentes);
    }

    public function exportarTodos(Request $request)
    {
        // Obtener parámetros de filtro
        $dni = trim($request->get('texto', ''));
        $name = trim($request->get('docentes', ''));
        $ugel = trim($request->get('ugels', ''));
        $nominstitucion = trim($request->get('instituciones', ''));
        $year = trim($request->get('year', '2026')); // Valor predeterminado: 2026

        // Construir la misma consulta pero sin paginación
        $query = Informe::select(
            "pro_informes.nombreInforme", "pro_informes.descripcion", 
            "pro_informes.fecha", "users.name", "users.cargo", 
            "institucions.nomInstitucion as institucion",
            "institucions.provincia as provincia",
            "institucions.distrito as distrito",
            "ugeles.nombre as ugel"
        )
        ->addSelect(DB::raw($this->nivelInstitucionExpr()))
        ->join("users", "users.id", "=", "pro_informes.idUser")
        ->leftJoin("institucions", "institucions.id", "=", "users.institucion_id")
        ->leftJoin("ugeles", "ugeles.id", "=", "users.ugel_id")
        ->where('pro_informes.estado', '1')
        ->whereYear('pro_informes.fecha', $year);

        // Aplicar los mismos filtros
        if (!empty($ugel)) {
            $query->where("ugeles.nombre", "LIKE", "%$ugel%");
        }
        
        if (!empty($dni)) {
            $query->where("users.dni", "LIKE", "%$dni%");
        }
        
        if (!empty($name)) {
            $query->where("users.name", "LIKE", "%$name%");
        }
        
        if (!empty($nominstitucion)) {
            $query->where("institucions.nomInstitucion", "LIKE", "%$nominstitucion%");
        }

        // Búsqueda libre (misma que el listado general).
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_informes.nombreInforme', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_informes.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        // Obtener TODOS los resultados (sin paginar)
        $informes = $query->orderBy('pro_informes.fecha', 'desc')->get();

        // Determinar formato
        $format = $request->get('format', 'excel');
        
        // Exportar en el formato correspondiente
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($informes);
            case 'csv':
                return $this->exportToCsv($informes);
            default:
                return $this->exportToExcel($informes);
        }
    }

    private function exportToExcel($informes)
    {
        // Configurar cabeceras para Excel
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=biblioteca_aula.xls',
        ];

        // Generar contenido HTML que Excel puede interpretar
        $content = '<table border="1">';
        $content .= '<tr><th>Nombre de la Biblioteca</th><th>Descripción</th><th>Fecha</th><th>Usuario</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE.</th><th>Provincia</th><th>Distrito</th><th>UGEL</th></tr>';
        
        foreach ($informes as $item) {
            $content .= '<tr>';
            $content .= '<td>' . $item->nombreInforme . '</td>';
            $content .= '<td>' . $item->descripcion . '</td>';
            $content .= '<td>' . date('d-m-Y', strtotime($item->fecha)) . '</td>';
            $content .= '<td>' . $item->name . '</td>';
            $content .= '<td>' . $item->cargo . '</td>';
            $content .= '<td>' . $item->institucion . '</td>';
            $content .= '<td>' . $item->nivelinstitucion . '</td>';
            $content .= '<td>' . $item->provincia . '</td>';
            $content .= '<td>' . $item->distrito . '</td>';
            $content .= '<td>' . $item->ugel . '</td>';
            $content .= '</tr>';
        }
        
        $content .= '</table>';
        
        return response($content, 200, $headers);
    }

    private function exportToCsv($informes)
    {
        // Configurar cabeceras para CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=biblioteca_aula.csv',
        ];

        $callback = function() use ($informes) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM para Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeceras
            fputcsv($file, [
                'Nombre de la Biblioteca', 
                'Descripción', 
                'Fecha', 
                'Usuario', 
                'Cargo', 
                'Institución', 
                'Tipo de II.EE.', 
                'Provincia', 
                'Distrito', 
                'UGEL'
            ]);
            
            // Datos
            foreach ($informes as $item) {
                fputcsv($file, [
                    $item->nombreInforme,
                    $item->descripcion,
                    date('d-m-Y', strtotime($item->fecha)),
                    $item->name,
                    $item->cargo,
                    $item->institucion,
                    $item->nivelinstitucion,
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

