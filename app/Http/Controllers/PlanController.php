<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Concerns\HasScopeTabs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    use HasScopeTabs;

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:plans.index')->only('index');
        $this->middleware('can:plans.create')->only('create', 'store');
        $this->middleware('can:plans.edit')->only('edit', 'update');
        $this->middleware('can:plans.destroy')->only('destroy');
        $this->middleware('can:plans.view')->only('general');
        $this->middleware('can:plans.ugel')->only('ugel');
        $this->middleware('can:plans.director')->only('director');
    }
    
    public function index(Request $request)
    {
        $usuario = Auth::user()->id;

        $query = Plan::where('estado', '1')->where('idUser', $usuario);

        if ($request->filled('texto')) {
            $query->where('nombrePlan', 'LIKE', '%' . $request->input('texto') . '%');
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
                $q->where('nombrePlan', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $plans = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('plan._rows', ['plans' => $plans])->render(),
                'pagination' => (string) $plans->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $plans->total(),
                'totalFormatted' => number_format($plans->total()),
                'from' => $plans->firstItem() ?? 0,
                'to' => $plans->lastItem() ?? 0,
            ]);
        }

        $listaAnios = $this->listaAniosPlan();
        $tabs = $this->tabsPlan('index');

        return view('plan.index', compact('plans', 'listaAnios', 'tabs'));
    }

    /**
     * Pestañas de "Espacio de Lectura en el Hogar" — Director tiene enlace de
     * menú (a diferencia de Evidencia/Sectores), así que va incluido.
     */
    private function tabsPlan(string $activo): array
    {
        return $this->scopeTabs([
            'index'    => ['permission' => 'plans.index', 'label' => 'Mis registros', 'route' => 'plans.index'],
            'ugel'     => ['permission' => 'plans.ugel', 'label' => 'UGEL', 'route' => 'plans.ugel'],
            'general'  => ['permission' => 'plans.view', 'label' => 'General', 'route' => 'plans.view'],
            'director' => ['permission' => 'plans.director', 'label' => 'Director', 'route' => 'plans.director'],
        ], $activo);
    }

    /**
     * Punto de entrada del menú. plans.index (Mis registros) NO incluye a
     * EspecDRE/EspecUGEL/Director — solo tienen .view/.ugel/.director
     * respectivamente — así que la entrada de menú no puede apuntar fijo a
     * /plans o esos roles se quedan sin poder llegar a nada. Redirige a la
     * primera pestaña a la que el usuario realmente tenga acceso.
     */
    public function landing()
    {
        $tabs = $this->tabsPlan('index');
        abort_if(empty($tabs), 403);

        return redirect($tabs[0]['url']);
    }

    /**
     * Años plausibles disponibles para el selector de filtro, descartando
     * fechas corruptas (p. ej. años como 23, 203 o 1978 por datos mal
     * digitados) — mismo criterio que EvidenciaController/InformeController.
     */
    private function listaAniosPlan(string $anioActual = null)
    {
        $listaAnios = Plan::whereYear('fecha', '>=', 2010)
            ->selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');

        $anioActual = $anioActual ?? date('Y');
        if (!$listaAnios->contains($anioActual)) {
            $listaAnios->prepend($anioActual);
        }

        return $listaAnios;
    }

    public function general(Request $request)
    {
        $anio = $request->filled('year') ? $request->input('year') : date('Y');

        $query = Plan::select(
                "pro_plans.id", "pro_plans.nombrePlan", "pro_plans.descripcion",
                "pro_plans.documento", "pro_plans.color", "pro_plans.fecha",
                "pro_plans.enlace",
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion",
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_plans.idUser")
            ->where('pro_plans.estado', '1')
            ->whereYear('pro_plans.fecha', $anio);

        if ($request->filled('texto')) {
            $query->where('users.dni', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('docentes')) {
            $query->where('users.name', 'LIKE', '%' . $request->input('docentes') . '%');
        }
        if ($request->filled('ugels')) {
            $query->where('users.ugel', $request->input('ugels'));
        }
        if ($request->filled('instituciones')) {
            $query->where('users.institucion', $request->input('instituciones'));
        }
        if ($request->filled('nivel')) {
            $query->where('users.nivelinstitucion', $request->input('nivel'));
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_plans.nombrePlan', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_plans.descripcion', 'LIKE', "%{$buscar}%");
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

        $plans = $query->orderBy('pro_plans.fecha', 'desc')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('plan._rows_general', ['plans' => $plans])->render(),
                'pagination' => (string) $plans->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $plans->total(),
                'totalFormatted' => number_format($plans->total()),
                'from' => $plans->firstItem() ?? 0,
                'to' => $plans->lastItem() ?? 0,
            ]);
        }

        $listaUgels = \App\Models\User::whereNotNull('ugel')->where('ugel', '!=', '')->distinct()->orderBy('ugel')->pluck('ugel');
        $listaAnios = $this->listaAniosPlan($anio);
        $tabs = $this->tabsPlan('general');

        return view('plan.view', compact('plans', 'anio', 'listaUgels', 'listaAnios', 'tabs'));
    }

    public function ugel(Request $request)
    {
        $ugel = Auth::user()->ugel;
        $anio = $request->filled('year') ? $request->input('year') : date('Y');

        $query = Plan::select(
                "pro_plans.id", "pro_plans.nombrePlan", "pro_plans.descripcion",
                "pro_plans.documento", "pro_plans.color", "pro_plans.fecha",
                "pro_plans.enlace",
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion",
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_plans.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_plans.estado', '1')
            ->whereYear('pro_plans.fecha', $anio);

        if ($request->filled('texto')) {
            $query->where('users.dni', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('instituciones')) {
            $query->where('users.institucion', $request->input('instituciones'));
        }
        if ($request->filled('nivel')) {
            $query->where('users.nivelinstitucion', $request->input('nivel'));
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_plans.nombrePlan', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_plans.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $plans = $query->orderBy('pro_plans.fecha', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('plan._rows_general', ['plans' => $plans])->render(),
                'pagination' => (string) $plans->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $plans->total(),
                'totalFormatted' => number_format($plans->total()),
                'from' => $plans->firstItem() ?? 0,
                'to' => $plans->lastItem() ?? 0,
            ]);
        }

        $listaInstituciones = \App\Models\User::where('ugel', $ugel)->whereNotNull('institucion')->where('institucion', '!=', '')->distinct()->orderBy('institucion')->pluck('institucion');
        $listaAnios = $this->listaAniosPlan($anio);
        $tabs = $this->tabsPlan('ugel');

        return view('plan.ugel', compact('plans', 'anio', 'listaInstituciones', 'listaAnios', 'tabs'));
    }

    public function director(Request $request)
    {
        $institucion = Auth::user()->institucion;
        $anio = $request->filled('year') ? $request->input('year') : date('Y');

        $query = Plan::select(
                "pro_plans.id", "pro_plans.nombrePlan", "pro_plans.descripcion",
                "pro_plans.documento", "pro_plans.color", "pro_plans.fecha",
                "pro_plans.enlace",
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion",
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_plans.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_plans.estado', '1')
            ->whereYear('pro_plans.fecha', $anio);

        if ($request->filled('texto')) {
            $query->where('pro_plans.nombrePlan', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('fecha')) {
            $query->where('pro_plans.fecha', 'LIKE', '%' . $request->input('fecha') . '%');
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_plans.nombrePlan', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_plans.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $plans = $query->orderBy('pro_plans.fecha', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('plan._rows_general', ['plans' => $plans])->render(),
                'pagination' => (string) $plans->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $plans->total(),
                'totalFormatted' => number_format($plans->total()),
                'from' => $plans->firstItem() ?? 0,
                'to' => $plans->lastItem() ?? 0,
            ]);
        }

        $listaAnios = $this->listaAniosPlan($anio);
        $tabs = $this->tabsPlan('director');

        return view('plan.director', compact('plans', 'anio', 'listaAnios', 'tabs'));
    }

    public function profesorcoordinador(Request $request)
    {
        $institucion = Auth::user()->institucion;
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        $plans = Plan::select("pro_plans.id","pro_plans.nombrePlan","pro_plans.documento","pro_plans.fecha","pro_plans.color","pro_plans.descripcion","users.name","users.institucion","users.provincia","users.distrito","users.ugel")
            ->join("users","users.id","=","pro_plans.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_plans.estado', '1')
            ->whereYear('fecha', $year)
            ->orderby('pro_plans.id','desc')
            ->paginate(10);
            
        return view("plan.coordinador", compact('plans'))->with('selectedYear', $year);
    }

    public function download($id)
    {
        $plan = Plan::findOrFail($id);
        $pathToFile = storage_path('app/public/' . $plan->enlace);
        
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
        return view('plan.create');
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'documento' => 'required|mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10048',
        ]);

        $file = $request->file('documento');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $dateTimeNow = now()->format('Ymd_His_u');
        $fileContent = $request->get('nombrePlan').' '.$dateTimeNow.'.'. $extension;
        $route = 'planA';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la función storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
        
        $plans = new Plan;
        $plans->enlace = $route . '/' . $fileContent;
        $plans->nombrePlan = $request->get('nombrePlan');
        switch($extension){
            case 'doc':
                $plans->documento = 'fas fa-file-word';
                $plans->color = 'blue';
                break;
            case 'docx':
                $plans->documento = 'fas fa-file-word';
                $plans->color = 'blue';
                break;
            case 'png':
                $plans->documento = 'fas fa-file-image';
                $plans->color = 'darkturquoise';
                break;
            case 'jpg':
                $plans->documento = 'fas fa-file-image';
                $plans->color = 'darkturquoise';
                break;
            case 'jpeg':
                $plans->documento = 'fas fa-file-image';
                $plans->color = 'darkturquoise';
                break;
            case 'pdf':
                $plans->documento = 'fas fa-file-pdf';
                $plans->color = 'red';
                break;
            case 'ppt':
                $plans->documento = 'fas fa-file-powerpoint';
                $plans->color = 'orange';
                break;
            case 'pptm':
                $plans->documento = 'fas fa-file-powerpoint';
                $plans->color = 'orange';
                break;
            case 'pptx':
                $plans->documento = 'fas fa-file-powerpoint';
                $plans->color = 'orange';
                break;
            case 'xlm':
                $plans->documento = 'fas fa-file-excel';
                $plans->color = 'green';
                break;
            case 'xls':
                $plans->documento = 'fas fa-file-excel';
                $plans->color = 'green';
                break;   
            case 'xlsm':
                $plans->documento = 'fas fa-file-excel';
                $plans->color = 'green';
                break;
            case 'xlsx':
                $plans->documento = 'fas fa-file-excel';
                $plans->color = 'green';
                break;
        }
        $plans->fecha = $request->get('fecha');
        $plans->descripcion = $request->get('descripcion');
        $plans->idUser = Auth::user()->id;
        $plans->estado = 1;
        $plans->save();
        
        return redirect('/plans')->with('success', '¡Registro guardado con éxito!');
    }

    public function show()
    {
        //
    }

    
    public function edit($id)
    {
        $plan = Plan::findOrFail($id);
        return view('plan.edit')->with('plan', $plan);
    }

    
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'documento' => 'required|mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10048',
        ], [
            'documento.max' => 'Archivo superior a 2MB', 
        ]);
        
        $file = $request->file('documento');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $dateTimeNow = now()->format('Ymd_His_u');
        $fileContent = $request->get('nombrePlan').' '.$dateTimeNow.'.'. $extension;
        $route = 'planA';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la función storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
         // Eliminar el archivo antiguo
        Storage::delete('public/'.$plan->enlace);

        $plan->enlace = $route . '/' . $fileContent;
        $plan->nombrePlan = $request->get('nombrePlan');
        switch($extension){
            case 'doc':
                $plan->documento = 'fas fa-file-word';
                $plan->color = 'blue';
                break;
            case 'docx':
                $plan->documento = 'fas fa-file-word';
                $plan->color = 'blue';
                break;
            case 'png':
                $plan->documento = 'fas fa-file-image';
                $plan->color = 'darkturquoise';
                break;
            case 'jpg':
                $plan->documento = 'fas fa-file-image';
                $plan->color = 'darkturquoise';
                break;
            case 'jpeg':
                $plan->documento = 'fas fa-file-image';
                $plan->color = 'darkturquoise';
                break;
            case 'pdf':
                $plan->documento = 'fas fa-file-pdf';
                $plan->color = 'red';
                break;
            case 'ppt':
                $plan->documento = 'fas fa-file-powerpoint';
                $plan->color = 'orange';
                break;
            case 'pptm':
                $plan->documento = 'fas fa-file-powerpoint';
                $plan->color = 'orange';
                break;
            case 'pptx':
                $plan->documento = 'fas fa-file-powerpoint';
                $plan->color = 'orange';
                break;
            case 'xlm':
                $plan->documento = 'fas fa-file-excel';
                $plan->color = 'green';
                break;
            case 'xls':
                $plan->documento = 'fas fa-file-excel';
                $plan->color = 'green';
                break;   
            case 'xlsm':
                $plan->documento = 'fas fa-file-excel';
                $plan->color = 'green';
                break;
            case 'xlsx':
                $plan->documento = 'fas fa-file-excel';
                $plan->color = 'green';
                break;
        }
        $plan->fecha = $request->get('fecha');
        $plan->descripcion = $request->get('descripcion');
        $plan->idUser = Auth::user()->id;
        $plan->estado = 1;
        $plan->save();
        
        return redirect('/plans');
    }

   
    public function destroy(Plan $plan)
    {
        Storage::delete('public/'.$plan->enlace);
        $plan->estado = 0;
        $plan->idUser = Auth::user()->id;
        $plan->save();
        session()->flash('success', '¡Registro eliminado!');
        return redirect('/plans');
    }

    public function obtenerUgels(Request $request)
    {
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        $ugels = DB::table('pro_plans')
            ->select('users.ugel', DB::raw('count(distinct pro_plans.idUser) as docentes_count'))
            ->join('users', 'pro_plans.idUser', '=', 'users.id')
            ->where('pro_plans.estado', '1')
            ->whereYear('fecha', $year)
            ->whereRaw("LENGTH(users.ugel) > 0")
            ->groupBy('users.ugel')
            ->get();

        return response()->json($ugels);
    }

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_plans', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_plans.idUser')
                    ->where('pro_plans.estado', '=', '1')
                    ->whereYear('pro_plans.fecha', $year);
            })
            ->where('institucions.ugel', '=', $ugelSeleccionada)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_plans.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_plans', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_plans.idUser')
                    ->where('pro_plans.estado', '=', '1')
                    ->whereYear('pro_plans.fecha', $year);
            })
            ->where('institucions.ugel', '=', $ugelSeleccionada)
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
    public function buscarDocenteporInstitucion(Request $request)
    {
        \Log::info('Params en buscarDocenteporInstitucion:', $request->all());
        
        $cargo = Auth::user()->cargo;
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        if ($cargo == "Especialista UGEL") {
            $ugelSeleccionada = Auth::user()->ugel;
        } else {
            $ugelSeleccionada = $request->input('ugel', $request->input('ugels', ''));
        }

        $institucionSeleccionada = $request->input('docente');
        
        \Log::info('Buscando docentes para:', [
            'institucion' => $institucionSeleccionada, 
            'ugel' => $ugelSeleccionada,
            'year' => $year
        ]);

        $docentes = DB::table('users')
            ->leftJoin('pro_plans', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_plans.idUser')
                    ->where('pro_plans.estado', '=', '1')
                    ->whereYear('pro_plans.fecha', $year);
            })
            ->where('users.institucion', '=', $institucionSeleccionada)
            ->when($ugelSeleccionada, function($query) use ($ugelSeleccionada) {
                return $query->where('users.ugel', 'like', '%' . $ugelSeleccionada . '%');
            })
            ->select('users.name', DB::raw('count(pro_plans.id) as agendas_count'))
            ->groupBy('users.name')
            ->having('agendas_count', '>=', 0)
            ->get();
        
        \Log::info('Docentes encontrados: ' . $docentes->count());

        return response()->json($docentes);
    }

    public function buscadorinstitucion(Request $request)
    {   
        $cargo = Auth::user()->cargo;
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
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
        
        $term = $request->input('term');

        $resultados = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_plans', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_plans.idUser')
                    ->where('pro_plans.estado', '=', '1')
                    ->whereYear('pro_plans.fecha', $year);
            })
            ->where('institucions.ugel', '=', $ugel)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_plans.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->where('institucions.ugel', '=', $ugel)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
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
    public function exportarTodos(Request $request)
    {
        // Obtener parámetros de filtro
        $dni = trim($request->get('texto', ''));
        $name = trim($request->get('docentes', ''));
        $ugel = trim($request->get('ugels', ''));
        $nominstitucion = trim($request->get('instituciones', ''));
        $year = $request->get('year', 2026); // Obtener el año del request, o usar 2026 como predeterminado

        // Construir la misma consulta pero sin paginación
        $query = Plan::select(
            "pro_plans.nombrePlan", "pro_plans.descripcion", 
            "pro_plans.fecha", "users.name", "users.cargo", 
            "users.nivelinstitucion", "users.institucion", 
            "users.provincia", "users.distrito", "users.ugel"
        )
        ->join("users", "users.id", "=", "pro_plans.idUser")
        ->where('pro_plans.estado', '1')
        ->whereYear('pro_plans.fecha', $year); // Usar el año seleccionado

        // Aplicar los mismos filtros
        if (!empty($ugel)) {
            $query->where("users.ugel", "LIKE", "%$ugel%");
        }
        
        if (!empty($dni)) {
            $query->where("users.dni", "LIKE", "%$dni%");
        }
        
        if (!empty($name)) {
            $query->where("users.name", "LIKE", "%$name%");
        }
        
        if (!empty($nominstitucion)) {
            $query->where("users.institucion", "LIKE", "%$nominstitucion%");
        }

        // Obtener TODOS los resultados (sin paginar)
        $plans = $query->orderBy('pro_plans.fecha', 'desc')->get();

        // Determinar formato
        $format = $request->get('format', 'excel');
        
        // Exportar en el formato correspondiente
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($plans);
            case 'csv':
                return $this->exportToCsv($plans);
            default:
                return $this->exportToExcel($plans);
        }
    }

    private function exportToExcel($plans)
    {
        // Configurar cabeceras para Excel
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=planes.xls',
        ];

        // Generar contenido HTML que Excel puede interpretar
        $content = '<table border="1">';
        $content .= '<tr><th>Nombre del Plan</th><th>Descripción</th><th>Fecha</th><th>Usuario</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE.</th><th>Provincia</th><th>Distrito</th><th>UGEL</th></tr>';
        
        foreach ($plans as $item) {
            $content .= '<tr>';
            $content .= '<td>' . $item->nombrePlan . '</td>';
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

    private function exportToCsv($plans)
    {
        // Configurar cabeceras para CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=planes.csv',
        ];

        $callback = function() use ($plans) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM para Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeceras
            fputcsv($file, [
                'Nombre del Plan', 
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
            foreach ($plans as $item) {
                fputcsv($file, [
                    $item->nombrePlan,
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

