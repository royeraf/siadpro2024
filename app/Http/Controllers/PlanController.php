<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
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
    
    public function index()
    {
        $usuario = Auth::user()->id;
        $plans = Plan::where('estado', '1')->where('idUser',$usuario)->orderby('id','desc')->paginate(10);
        return view('plan.index')->with('plans',$plans);
    }

    public function general(Request $request)
    {
        // Obtener el año del request, o usar 2026 como predeterminado
        $year = $request->get('year', 2026);
        
        $plans = Plan::select("pro_plans.id","pro_plans.nombrePlan","pro_plans.descripcion","pro_plans.fecha","pro_plans.documento","pro_plans.color","pro_plans.descripcion","users.name","users.institucion","users.provincia","users.cargo","users.nivelinstitucion","users.distrito","users.ugel","users.dni")
                    ->join("users","users.id","=","pro_plans.idUser")
                    ->where('pro_plans.estado', '1')
                    ->whereYear('fecha', $year)
                    ->orderby('pro_plans.id','desc')
                    ->paginate(10);
        
        return view('plan.view')->with('plans', $plans)->with('selectedYear', $year);
    }

    public function ugel(Request $request)
    {
        $ugel = Auth::user()->ugel;
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        $plans = Plan::select("pro_plans.id","pro_plans.nombrePlan","pro_plans.documento","pro_plans.fecha","pro_plans.color","pro_plans.descripcion","users.name","users.institucion","users.provincia","users.distrito","users.nivelinstitucion","users.cargo","users.ugel")
            ->join("users","users.id","=","pro_plans.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_plans.estado', '1')
            ->whereYear('fecha', $year)
            ->orderby('pro_plans.id','desc')
            ->paginate(10);
            
        return view("plan.ugel", compact('plans'))->with('selectedYear', $year);
    }

    public function director(Request $request)
    {
        $institucion = Auth::user()->institucion;
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        $plans = Plan::select("pro_plans.id","pro_plans.nombrePlan","pro_plans.documento","pro_plans.fecha","pro_plans.color","pro_plans.descripcion","users.name","users.institucion","users.provincia","users.distrito","users.cargo","users.ugel")
            ->join("users","users.id","=","pro_plans.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_plans.estado', '1')
            ->whereYear('fecha', $year)
            ->orderby('pro_plans.id','desc')
            ->paginate(10);
            
        return view("plan.director", compact('plans'))->with('selectedYear', $year);
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
        $usuario = Auth::user()->id;
        $texto = trim($request->get('texto'));
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        $plans = Plan::where("nombrePlan", "LIKE", "%" . $texto . "%")
            ->where('estado', '1')
            ->whereYear('fecha', $year)
            ->where('idUser', $usuario)
            ->orderby('pro_plans.id', 'desc')
            ->paginate(10);
            
        return view('plan.index')->with('plans', $plans)->with('selectedYear', $year);
    }

    public function buscarGeneral(Request $request)
    {
        \Log::info('Params en buscarGeneral:', $request->all());
        
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        if (empty($request->get('ugels')) && empty($request->get('instituciones')) && empty($request->get('docentes')) && empty($request->get('texto'))) {
            return redirect('/plan-general?year=' . $year);
        } else {    
            $dni = trim($request->get('texto', ''));
            $name = trim($request->get('docentes', ''));
            $ugel = trim($request->get('ugels', ''));
            $nominstitucion = trim($request->get('instituciones', ''));

            $query = Plan::select(
                "pro_plans.id", "pro_plans.nombrePlan", "pro_plans.documento", 
                "pro_plans.fecha", "pro_plans.color", "pro_plans.descripcion", 
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion", 
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_plans.idUser")
            ->where('pro_plans.estado', '1')
            ->whereYear('pro_plans.fecha', $year); // Usa el año seleccionado

            // Aplicar cada filtro independientemente si está presente
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

            $plans = $query->orderBy('pro_plans.id', 'desc')->paginate(1000);
            
            \Log::info('Total de registros encontrados: ' . $plans->total());

            return view('plan.view')->with('plans', $plans)->with('selectedYear', $year);
        }
    }

    public function buscarUgel(Request $request)
    {
        $ugel = Auth::user()->ugel;
        $dni = trim($request->get('texto'));
        $nivel = trim($request->get('nivel'));
        $nominstitucion = trim($request->get('nominstitucion'));
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        $plans = Plan::select("pro_plans.id", "pro_plans.nombrePlan", "pro_plans.documento", "pro_plans.fecha", "pro_plans.color", "pro_plans.descripcion", "users.name", "users.institucion", "users.provincia", "users.distrito", "users.nivelinstitucion", "users.cargo", "users.ugel")
            ->join("users", "users.id", "=", "pro_plans.idUser")
            ->where("users.ugel", $ugel)
            ->whereYear('fecha', $year)
            ->where('pro_plans.estado', '1')
            ->where("users.dni", "LIKE", "%" . $dni . "%")
            ->where('users.nivelinstitucion', "LIKE", "%" . $nivel . "%")
            ->where("users.institucion", "LIKE", "%" . $nominstitucion . "%")
            ->orderby('pro_plans.id', 'desc')
            ->paginate(10);
            
        return view('plan.ugel')->with('plans', $plans)->with('selectedYear', $year);
    }

    public function buscarDirector(Request $request)
    {
        $institucion = Auth::user()->institucion;
        $texto = trim($request->get('texto'));
        $year = $request->get('year', 2026); // Año predeterminado 2026
        
        $plans = Plan::select("pro_plans.id", "pro_plans.nombrePlan", "pro_plans.documento", "pro_plans.fecha", "pro_plans.color", "pro_plans.descripcion", "users.name", "users.institucion", "users.provincia", "users.distrito", "users.ugel")
            ->join("users", "users.id", "=", "pro_plans.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_plans.estado', '1')
            ->whereYear('fecha', $year)
            ->where("pro_plans.nombrePlan", "LIKE", "%" . $texto . "%")
            ->orderby('pro_plans.id', 'desc')
            ->paginate(10);
            
        return view('plan.director')->with('plans', $plans)->with('selectedYear', $year);
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

