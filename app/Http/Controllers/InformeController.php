<?php

namespace App\Http\Controllers;
use App\Models\Informe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class InformeController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:informes.index')->only('index');
        $this->middleware('can:informes.create')->only('create', 'store');
        $this->middleware('can:informes.edit')->only('edit', 'update');
        $this->middleware('can:informes.destroy')->only('destroy');
        $this->middleware('can:informes.view')->only('general');
        $this->middleware('can:informes.ugel')->only('ugel');
        $this->middleware('can:informes.director')->only('director');
    }
    
    public function index()
    {
        $usuario = Auth::user()->id;
        $informes = Informe::where('estado', '1')
        ->where('idUser',$usuario)
        ->whereYear('fecha',2025)
        ->orderby('id','desc')->paginate(10);
        return view('informe.index')->with('informes',$informes);
    }
    public function general()
    {
        // Obtener el año del filtro, con 2025 como valor predeterminado
        $year = request()->get('year', '2025');
        
        $informes = Informe::select("pro_informes.id","pro_informes.nombreInforme","pro_informes.descripcion","pro_informes.documento","pro_informes.fecha","pro_informes.color","pro_informes.descripcion","users.name","users.institucion","users.provincia","users.cargo","users.nivelinstitucion","users.distrito","users.ugel","users.dni")
                    ->join("users","users.id","=","pro_informes.idUser")
                    ->where('pro_informes.estado', '1')
                    ->whereYear('fecha', $year)
                    ->orderby('pro_informes.id','desc')
                    ->paginate(10);
        return view('informe.view')->with('informes', $informes);
    }

    public function ugel()
    {
        $ugel = Auth::user()->ugel;
        $informes = Informe::select("pro_informes.id","pro_informes.nombreInforme","pro_informes.documento","pro_informes.color","pro_informes.fecha","pro_informes.descripcion","users.name","users.institucion","users.provincia","users.distrito","users.nivelinstitucion","users.cargo","users.ugel")
            ->join("users","users.id","=","pro_informes.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_informes.estado', '1')
            ->whereYear('pro_informes.fecha',2024)
            ->orderby('pro_informes.id','desc')
            ->paginate(10);
            return view("informe.ugel",compact('informes'));
    }

    public function director()
    {
        $institucion = Auth::user()->institucion;
        $informes = Informe::select("pro_informes.id","pro_informes.nombreInforme","pro_informes.documento","pro_informes.fecha","pro_informes.color","pro_informes.descripcion","users.name","users.institucion","users.provincia","users.distrito","users.cargo","users.ugel")
            ->join("users","users.id","=","pro_informes.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_informes.estado', '1')
            ->whereYear('pro_informes.fecha',2024)
            
            ->orderby('pro_informes.id','desc')
            ->paginate(10);
            return view("informe.director",compact('informes'));
    }

    public function profesorcoordinador()
    {
        $institucion = Auth::user()->institucion;
        $informes = Informe::select("pro_informes.id","pro_informes.nombreInforme","pro_informes.documento","pro_informes.fecha","pro_informes.color","pro_informes.descripcion","users.name","users.institucion","users.provincia","users.distrito","users.nivelinstitucion","users.cargo","users.ugel")
            ->join("users","users.id","=","pro_informes.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_informes.estado', '1')
            ->whereYear('pro_informes.fecha',2024)
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
    public function buscar(Request $request){
        $usuario = Auth::user()->id;
        $texto = trim($request->get('texto'));
        $year = trim($request->get('year', '2025')); // Valor predeterminado: 2025
        
        $informes = Informe::where("nombreInforme","LIKE","%".$texto."%")
        ->where('estado', '1')
        ->whereYear('pro_informes.fecha', $year)
        ->where('idUser', $usuario)
        ->orderby('pro_informes.id','desc')
        ->paginate(10);
        return view('informe.index')->with('informes',$informes);
    }

    public function buscarGeneral(Request $request)
    {
        \Log::info('Parámetros de búsqueda:', $request->all());
        
        if (empty($request->get('ugels')) && empty($request->get('instituciones')) && empty($request->get('docentes')) && empty($request->get('texto')) && empty($request->get('year'))) {
            return redirect('/informe-general');
        } else {    
            $dni = trim($request->get('texto', ''));
            $name = trim($request->get('docentes', ''));
            $ugel = trim($request->get('ugels', ''));
            $nominstitucion = trim($request->get('instituciones', ''));
            $year = trim($request->get('year', '2025')); // Valor predeterminado: 2025

            $query = Informe::select(
                "pro_informes.id", "pro_informes.nombreInforme", "pro_informes.documento", 
                "pro_informes.fecha", "pro_informes.color", "pro_informes.descripcion", 
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion", 
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_informes.idUser")
            ->where('pro_informes.estado', '1')
            ->whereYear('pro_informes.fecha', $year);

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

            $informes = $query->orderBy('pro_informes.id', 'desc')->paginate(1000);
            
            \Log::info('Total de registros encontrados: ' . $informes->total());

            return view('informe.view')->with('informes', $informes);
        }
    }

    public function buscarUgel(Request $request){
        $ugel = Auth::user()->ugel;
        $dni = trim($request->get('texto'));
        $nivel = trim($request->get('nivel'));
        $nominstitucion = trim($request->get('nominstitucion'));
        $year = trim($request->get('year', '2025')); // Valor predeterminado: 2025
        
        $informes = Informe::select("pro_informes.id","pro_informes.descripcion","pro_informes.nombreInforme","pro_informes.fecha","pro_informes.documento","pro_informes.color","pro_informes.updated_at","users.name","users.institucion","users.provincia","users.distrito","users.nivelinstitucion","users.cargo","users.ugel")
        ->join("users","users.id","=","pro_informes.idUser")
        ->where("users.ugel", $ugel)
        ->where('pro_informes.estado', '1')
        ->where("users.dni","LIKE","%".$dni."%")
        ->where('users.nivelinstitucion',"LIKE","%".$nivel."%")
        ->whereYear('fecha', $year)
        ->where("users.institucion","LIKE","%".$nominstitucion."%")
        ->orderby('pro_informes.id','desc')
        ->paginate(10);
        return view('informe.ugel')->with('informes',$informes);
    }

    public function buscarDirector(Request $request){
        $institucion = Auth::user()->institucion;
        $texto = trim($request->get('texto'));
        $year = trim($request->get('year', '2025')); // Valor predeterminado: 2025
        
        $informes = Informe::select("pro_informes.id","pro_informes.nombreInforme","pro_informes.documento","pro_informes.color","pro_informes.descripcion","users.name","users.institucion","users.provincia","users.distrito","users.cargo","users.ugel")
        ->join("users","users.id","=","pro_informes.idUser")
        ->where("users.institucion", $institucion)
        ->where('pro_informes.estado', '1')
        ->whereYear('fecha', $year)
        ->where("pro_informes.nombreInforme","LIKE","%".$texto."%")
        ->paginate(10);
        return view('informe.director')->with('informes',$informes);
    }

    
    public function create()
    {
        return view('informe.create');
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
        switch($extension){
            case 'doc':
                $informes->documento = 'fas fa-file-word';
                $informes->color = 'blue';
                break;
            case 'docx':
                $informes->documento = 'fas fa-file-word';
                $informes->color = 'blue';
                break;
            case 'png':
                $informes->documento = 'fas fa-file-image';
                $informes->color = 'darkturquoise';
                break;
            case 'jpg':
                $informes->documento = 'fas fa-file-image';
                $informes->color = 'darkturquoise';
                break;
            case 'jpeg':
                $informes->documento = 'fas fa-file-image';
                $informes->color = 'darkturquoise';
                break;
            case 'pdf':
                $informes->documento = 'fas fa-file-pdf';
                $informes->color = 'red';
                break;
            case 'ppt':
                $informes->documento = 'fas fa-file-powerpoint';
                $informes->color = 'orange';
                break;
            case 'pptm':
                $informes->documento = 'fas fa-file-powerpoint';
                $informes->color = 'orange';
                break;
            case 'pptx':
                $informes->documento = 'fas fa-file-powerpoint';
                $informes->color = 'orange';
                break;
            case 'xlm':
                $informes->documento = 'fas fa-file-excel';
                $informes->color = 'green';
                break;
            case 'xls':
                $informes->documento = 'fas fa-file-excel';
                $informes->color = 'green';
                break;   
            case 'xlsm':
                $informes->documento = 'fas fa-file-excel';
                $informes->color = 'green';
                break;
            case 'xlsx':
                $informes->documento = 'fas fa-file-excel';
                $informes->color = 'green';
                break;
        }
        $informes->fecha = $request->get('fecha');
        $informes->descripcion = $request->get('descripcion');
        $informes->idUser = Auth::user()->id;
        $informes->estado = 1;
        $informes->save();
        
        return redirect('/informes')->with('success', '¡Registro guardado con éxito!');;
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
            'documento' => 'required|mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10048',
        ]);
        
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
         // Eliminar el archivo antiguo
        Storage::delete('public/'.$informe->enlace);

        $informe->enlace = $route . '/' . $fileContent;
        $informe->nombreInforme = $request->get('nombreInforme');
        switch($extension){
            case 'doc':
                $informe->documento = 'fas fa-file-word';
                $informe->color = 'blue';
                break;
            case 'docx':
                $informe->documento = 'fas fa-file-word';
                $informe->color = 'blue';
                break;
            case 'png':
                $informe->documento = 'fas fa-file-image';
                $informe->color = 'darkturquoise';
                break;
            case 'jpg':
                $informe->documento = 'fas fa-file-image';
                $informe->color = 'darkturquoise';
                break;
            case 'jpeg':
                $informe->documento = 'fas fa-file-image';
                $informe->color = 'darkturquoise';
                break;
            case 'pdf':
                $informe->documento = 'fas fa-file-pdf';
                $informe->color = 'red';
                break;
            case 'ppt':
                $informe->documento = 'fas fa-file-powerpoint';
                $informe->color = 'orange';
                break;
            case 'pptm':
                $informe->documento = 'fas fa-file-powerpoint';
                $informe->color = 'orange';
                break;
            case 'pptx':
                $informe->documento = 'fas fa-file-powerpoint';
                $informe->color = 'orange';
                break;
            case 'xlm':
                $informe->documento = 'fas fa-file-excel';
                $informe->color = 'green';
                break;
            case 'xls':
                $informe->documento = 'fas fa-file-excel';
                $informe->color = 'green';
                break;   
            case 'xlsm':
                $informe->documento = 'fas fa-file-excel';
                $informe->color = 'green';
                break;
            case 'xlsx':
                $informe->documento = 'fas fa-file-excel';
                $informe->color = 'green';
                break;
        }
        $informe->fecha = $request->get('fecha');
        $informe->descripcion = $request->get('descripcion');
        $informe->idUser = Auth::user()->id;
        $informe->estado = 1;
        $informe->save();
        
        return redirect('/informes');
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
        $year = request()->get('year', '2025'); // Valor predeterminado: 2025
        
        $ugels = DB::table('pro_informes')
            ->select('users.ugel', DB::raw('count(distinct pro_informes.idUser) as docentes_count'))
            ->join('users', 'pro_informes.idUser', '=', 'users.id')
            ->where('pro_informes.estado', '1')
            ->whereYear('fecha', $year)
            ->whereRaw("LENGTH(users.ugel) > 0")
            ->groupBy('users.ugel')
            ->get();

        return response()->json($ugels);
    }

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
            $resultados = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                     ->on('institucions.ugel', '=', 'users.ugel');
            })
            
            ->leftJoin('pro_informes', function($join) {
                $join->on('users.id', '=', 'pro_informes.idUser')
                     ->where('pro_informes.estado', '=', '1');
            })
            ->where('institucions.ugel', '=',$ugelSeleccionada)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_informes.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

            $totalDocentes = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                     ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_informes', function($join) {
                $join->on('users.id', '=', 'pro_informes.idUser')
                     ->where('pro_informes.estado', '=', '1');
            })
            ->where('institucions.ugel', '=',$ugelSeleccionada)
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
            $term = $request->input('term'); // Obtén el término de búsqueda del formulario
    
            // Realiza una consulta para buscar instituciones que coincidan con $term y tengan información sobre docentes y agendas
                $resultados = DB::table('institucions')
                ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                     ->on('institucions.ugel', '=', 'users.ugel');
                })
                ->leftJoin('pro_informes', function($join) {
                    $join->on('users.id', '=', 'pro_informes.idUser')
                         ->where('pro_informes.estado', '=', '1');
                })
                ->where('institucions.ugel', '=', $ugel)
                ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
                ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_informes.idUser) as agendas_count'))
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
        
        \Log::info('Buscando docentes para:', [
            'institucion' => $institucionSeleccionada, 
            'ugel' => $ugelSeleccionada
        ]);

        $docentes = DB::table('users')
            ->leftJoin('pro_informes', function($join) {
                $join->on('users.id', '=', 'pro_informes.idUser')
                    ->where('pro_informes.estado', '=', '1');
            })
            ->where('users.institucion', '=', $institucionSeleccionada)
            ->when($ugelSeleccionada, function($query) use ($ugelSeleccionada) {
                return $query->where('users.ugel', 'like', '%' . $ugelSeleccionada . '%');
            })
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
        $docentes = DB::table('users')
        ->leftJoin('pro_informes', function($join) {
            $join->on('users.id', '=', 'pro_informes.idUser')
                 ->where('pro_informes.estado', '=', '1');
        })
        ->where('users.institucion', '=',$institucion)
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
        $year = trim($request->get('year', '2025')); // Valor predeterminado: 2025

        // Construir la misma consulta pero sin paginación
        $query = Informe::select(
            "pro_informes.nombreInforme", "pro_informes.descripcion", 
            "pro_informes.fecha", "users.name", "users.cargo", 
            "users.nivelinstitucion", "users.institucion", 
            "users.provincia", "users.distrito", "users.ugel"
        )
        ->join("users", "users.id", "=", "pro_informes.idUser")
        ->where('pro_informes.estado', '1')
        ->whereYear('pro_informes.fecha', $year);

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

