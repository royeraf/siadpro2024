<?php

namespace App\Http\Controllers;

use App\Models\Evidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EvidenciaController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:evidencias.index')->only('index');
        $this->middleware('can:evidencias.create')->only('create', 'store');
        $this->middleware('can:evidencias.edit')->only('edit', 'update');
        $this->middleware('can:evidencias.destroy')->only('destroy');
        $this->middleware('can:evidencias.view')->only('general');
        $this->middleware('can:evidencias.ugel')->only('ugel');
        $this->middleware('can:evidencias.director')->only('director');
    }
    
    public function index()
    {
        $usuario = Auth::user()->id;
        $anio = request()->get('anio') ?: '2026'; // El valor predeterminado aquí es 2024 como está en el código original
        
        $evidencias = Evidencia::where('estado', '1')
            ->whereYear('pro_evidencias.fecha', $anio)
            ->where('idUser', $usuario)
            ->orderby('fecha', 'desc')
            ->paginate(10);
        
        return view('evidencia.index')->with('evidencias', $evidencias);
    }

    public function general()
    {
        // Obtener el año del request si está disponible
        $anio = request()->get('anio') ?: '2026';

        $evidencias = Evidencia::select("pro_evidencias.id","pro_evidencias.nombreEvidencia","pro_evidencias.descripcion","pro_evidencias.documento","pro_evidencias.color","pro_evidencias.descripcion","pro_evidencias.fecha","users.name","users.institucion","users.provincia","users.cargo","users.nivelinstitucion","users.distrito","users.ugel","users.dni")
                    ->join("users","users.id","=","pro_evidencias.idUser")
                    ->where('pro_evidencias.estado', '1')
                    ->whereYear('fecha', $anio)
                    ->orderby('pro_evidencias.fecha','desc')
                    ->paginate(10);
        return view('evidencia.view')->with('evidencias', $evidencias);
    }

    public function ugel()
    {
        $ugel = Auth::user()->ugel;
        $anio = request()->get('anio') ?: '2026';
        
        $evidencias = Evidencia::select("pro_evidencias.id","pro_evidencias.nombreEvidencia","pro_evidencias.documento","pro_evidencias.color","pro_evidencias.descripcion","pro_evidencias.fecha","users.name","users.institucion","users.provincia","users.distrito","users.cargo","users.nivelinstitucion","users.ugel")
            ->join("users","users.id","=","pro_evidencias.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_evidencias.estado', '1')
            ->whereYear('fecha', $anio)
            ->orderby('fecha','desc')
            ->paginate(10);
            return view("evidencia.ugel",compact('evidencias'));
    }

    public function director()
    {
        $institucion = Auth::user()->institucion;
        $anio = request()->get('anio') ?: '2026';
        
        $evidencias = Evidencia::select("pro_evidencias.id","pro_evidencias.nombreEvidencia","pro_evidencias.documento","pro_evidencias.color","pro_evidencias.descripcion","pro_evidencias.fecha","users.name","users.institucion","users.provincia","users.distrito","users.cargo","users.ugel")
            ->join("users","users.id","=","pro_evidencias.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_evidencias.estado', '1')
            ->whereYear('pro_evidencias.fecha', $anio)
            ->orderby('fecha','desc')
            ->paginate(10);
            return view("evidencia.director",compact('evidencias'));
    }

    public function profesorcoordinador()
    {
        $institucion = Auth::user()->institucion;
        $anio = request()->get('anio') ?: '2026';
        
        $evidencias = Evidencia::select("pro_evidencias.id","pro_evidencias.nombreEvidencia","pro_evidencias.documento","pro_evidencias.color","pro_evidencias.descripcion","pro_evidencias.fecha","users.name","users.institucion","users.provincia","users.distrito","users.ugel")
            ->join("users","users.id","=","pro_evidencias.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_evidencias.estado', '1')
            ->whereYear('pro_evidencias.fecha', $anio)
            ->orderby('fecha','desc')
            ->paginate(10);
            return view("evidencia.coordinador",compact('evidencias'));
    }

    public function download($id)
    {
        $evidencia = Evidencia::findOrFail($id);
        $pathToFile = storage_path('app/public/' . $evidencia->enlace);
        
        // Verificar si el archivo existe antes de descargarlo
        
        return response()->download($pathToFile);
    }

    public function buscar(Request $request)
    {
        $usuario = Auth::user()->id;
        $texto = trim($request->get('texto'));
        $fecha = trim($request->get('fecha'));
        $anio = trim($request->get('anio')); // Sin valor predeterminado para respetar la búsqueda actual
        
        $query = Evidencia::where("nombreEvidencia", "LIKE", "%" . $texto . "%")
            ->where("fecha", "LIKE", "%" . $fecha . "%")
            ->where('estado', '1')
            ->where('idUser', $usuario);
        
        if (!empty($anio)) {
            $query->whereYear('fecha', $anio);
        }
        
        $evidencias = $query->orderBy('fecha', 'desc')->paginate(10);
        
        return view('evidencia.index')->with('evidencias', $evidencias);
    }

    public function buscarGeneral(Request $request)
    {
        if (empty($request->get('ugels')) && empty($request->get('instituciones')) && 
            empty($request->get('docentes')) && empty($request->get('texto')) && 
            empty($request->get('nivel')) && empty($request->get('anio'))) {
            return redirect('/evidencia-general');
        } else {
            $dni = trim($request->get('texto'));
            $name = trim($request->get('docentes'));
            $ugel = trim($request->get('ugels'));
            $nominstitucion = trim($request->get('instituciones'));
            $nivel = trim($request->get('nivel'));
            $anio = trim($request->get('anio')) ?: '2026'; // Valor por defecto 2023 si no se proporciona
    
            $query = Evidencia::select(
                "pro_evidencias.id", "pro_evidencias.nombreEvidencia", "pro_evidencias.documento", 
                "pro_evidencias.color", "pro_evidencias.descripcion", "pro_evidencias.fecha", 
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion", 
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_evidencias.idUser")
            ->where('pro_evidencias.estado', '1');
            
            // Aplicar filtro de año
            if (!empty($anio)) {
                $query->whereYear('pro_evidencias.fecha', $anio);
            }
    
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
            
            // Aplicar filtro de nivel de institución si está presente
            if (!empty($nivel)) {
                $query->where("users.nivelinstitucion", "LIKE", "%$nivel%");
            }
    
            $evidencias = $query->orderBy('pro_evidencias.fecha', 'desc')->paginate(1000);
    
            return view('evidencia.view')->with('evidencias', $evidencias);
        }
    }

    public function buscarUgel(Request $request)
    {
        $ugel = Auth::user()->ugel;
        $dni = trim($request->get('texto'));
        $nivel = trim($request->get('nivel'));
        $nominstitucion = trim($request->get('nombinstitucion'));
        $anio = trim($request->get('anio')) ?: '2026'; // Valor por defecto 2023
        
        $evidencias = Evidencia::select("pro_evidencias.id", "pro_evidencias.nombreEvidencia", "pro_evidencias.documento", "pro_evidencias.color", "pro_evidencias.descripcion", "pro_evidencias.fecha", "users.name", "users.institucion", "users.provincia", "users.distrito", "users.nivelinstitucion", "users.cargo", "users.ugel")
            ->join("users", "users.id", "=", "pro_evidencias.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_evidencias.estado', '1')
            ->where("users.dni", "LIKE", "%" . $dni . "%")
            ->whereYear('fecha', $anio)
            ->where('users.nivelinstitucion', "LIKE", "%" . $nivel . "%")
            ->where("users.institucion", "LIKE", "%" . $nominstitucion . "%")
            ->orderBy('pro_evidencias.fecha', 'desc')
            ->paginate(10);
            
        return view('evidencia.ugel')->with('evidencias', $evidencias);
    }

    public function buscarDirector(Request $request)
    {
        $institucion = Auth::user()->institucion;
        $texto = trim($request->get('texto'));
        $fecha = trim($request->get('fecha'));
        $anio = trim($request->get('anio')) ?: '2026'; // Valor por defecto 2023
        
        $evidencias = Evidencia::select("pro_evidencias.id", "pro_evidencias.nombreEvidencia", "pro_evidencias.documento", "pro_evidencias.color", "pro_evidencias.tipoevidencia", "pro_evidencias.updated_at", "pro_evidencias.lugar", "users.name", "users.institucion", "users.provincia", "users.distrito", "users.cargo", "users.ugel")
            ->join("users", "users.id", "=", "pro_evidencias.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_evidencias.estado', '1')
            ->whereYear('fecha', $anio)
            ->where("pro_evidencias.nombreEvidencia", "LIKE", "%" . $texto . "%")
            ->where("pro_evidencias.fecha", "LIKE", "%" . $fecha . "%")
            ->orderBy('pro_evidencias.fecha', 'desc')
            ->paginate(10);
            
        return view('evidencia.director')->with('evidencias', $evidencias);
    }


    
    public function create()
    {
        return view('evidencia.create');
    }

    
    public function store(Request $request)
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
        $fileContent = $request->get('nombreEvidencia').' '.$dateTimeNow.'.'. $extension;
        $route = 'evidencia';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la función storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
        
        $evidencias = new Evidencia;
        $evidencias->enlace = $route . '/' . $fileContent;
        $evidencias->nombreEvidencia = $request->get('nombreEvidencia');
        switch($extension){
            case 'doc':
                $evidencias->documento = 'fas fa-file-word';
                $evidencias->color = 'blue';
                break;
            case 'docx':
                $evidencias->documento = 'fas fa-file-word';
                $evidencias->color = 'blue';
                break;
            case 'png':
                $evidencias->documento = 'fas fa-file-image';
                $evidencias->color = 'darkturquoise';
                break;
            case 'jpg':
                $evidencias->documento = 'fas fa-file-image';
                $evidencias->color = 'darkturquoise';
                break;
            case 'jpeg':
                $evidencias->documento = 'fas fa-file-image';
                $evidencias->color = 'darkturquoise';
                break;
            case 'pdf':
                $evidencias->documento = 'fas fa-file-pdf';
                $evidencias->color = 'red';
                break;
            case 'ppt':
                $evidencias->documento = 'fas fa-file-powerpoint';
                $evidencias->color = 'orange';
                break;
            case 'pptm':
                $evidencias->documento = 'fas fa-file-powerpoint';
                $evidencias->color = 'orange';
                break;
            case 'pptx':
                $evidencias->documento = 'fas fa-file-powerpoint';
                $evidencias->color = 'orange';
                break;
            case 'xlm':
                $evidencias->documento = 'fas fa-file-excel';
                $evidencias->color = 'green';
                break;
            case 'xls':
                $evidencias->documento = 'fas fa-file-excel';
                $evidencias->color = 'green';
                break;   
            case 'xlsm':
                $evidencias->documento = 'fas fa-file-excel';
                $evidencias->color = 'green';
                break;
            case 'xlsx':
                $evidencias->documento = 'fas fa-file-excel';
                $evidencias->color = 'green';
                break;
        }
        $evidencias->descripcion = $request->get('descripcion');
        $evidencias->fecha = $request->get('fecha');
        $evidencias->idUser = Auth::user()->id;
        $evidencias->estado = 1;
        $evidencias->save();
        
        return redirect('/evidencias')->with('success', '¡Registro guardado con éxito!');
    }

    public function show()
    {
        //
    }

    
    public function edit($id)
    {
        $evidencia = Evidencia::findOrFail($id);
        return view('evidencia.edit')->with('evidencia', $evidencia);
    }

    
    public function update(Request $request, Evidencia $evidencia)
    {
        $request->validate([
            'documento' => 'required|mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10048',
        ]);
        
        $file = $request->file('documento');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $dateTimeNow = now()->format('Ymd_His_u');
        $fileContent = $request->get('nombreEvidencia').' '.$dateTimeNow.'.'. $extension;
        $route = 'evidencia';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la función storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
         // Eliminar el archivo antiguo
        Storage::delete('public/'.$evidencia->enlace);

        $evidencia->enlace = $route . '/' . $fileContent;
        $evidencia->nombreEvidencia = $request->get('nombreEvidencia');
        switch($extension){
            case 'doc':
                $evidencia->documento = 'fas fa-file-word';
                $evidencia->color = 'blue';
                break;
            case 'docx':
                $evidencia->documento = 'fas fa-file-word';
                $evidencia->color = 'blue';
                break;
            case 'png':
                $evidencia->documento = 'fas fa-file-image';
                $evidencia->color = 'darkturquoise';
                break;
            case 'jpg':
                $evidencia->documento = 'fas fa-file-image';
                $evidencia->color = 'darkturquoise';
                break;
            case 'jpeg':
                $evidencia->documento = 'fas fa-file-image';
                $evidencia->color = 'darkturquoise';
                break;
            case 'pdf':
                $evidencia->documento = 'fas fa-file-pdf';
                $evidencia->color = 'red';
                break;
            case 'ppt':
                $evidencia->documento = 'fas fa-file-powerpoint';
                $evidencia->color = 'orange';
                break;
            case 'pptm':
                $evidencia->documento = 'fas fa-file-powerpoint';
                $evidencia->color = 'orange';
                break;
            case 'pptx':
                $evidencia->documento = 'fas fa-file-powerpoint';
                $evidencia->color = 'orange';
                break;
            case 'xlm':
                $evidencia->documento = 'fas fa-file-excel';
                $evidencia->color = 'green';
                break;
            case 'xls':
                $evidencia->documento = 'fas fa-file-excel';
                $evidencia->color = 'green';
                break;   
            case 'xlsm':
                $evidencia->documento = 'fas fa-file-excel';
                $evidencia->color = 'green';
                break;
            case 'xlsx':
                $evidencia->documento = 'fas fa-file-excel';
                $evidencia->color = 'green';
                break;
        }
        $evidencia->descripcion = $request->get('descripcion');
        $evidencia->fecha = $request->get('fecha');
        $evidencia->idUser = Auth::user()->id;
        $evidencia->estado = 1;
        $evidencia->save();
        
        return redirect('/evidencias');
    }

   
    public function destroy(Evidencia $evidencia)
    {
        Storage::delete('public/'.$evidencia->enlace);
        $evidencia->estado = 0;
        $evidencia->idUser = Auth::user()->id;
        $evidencia->save();
        session()->flash('success', '¡Registro eliminado!');
        return redirect('/evidencias');
    }

    public function buscador(Request $request)
    {
        //dd($request);
        /*
        $evidencia = Evidencia::where("nombre",'like',$request->texto."%")->take(10)->get;
        return view("evidencias.paginas", compact("evidencia"));*/
        
        try {
            $term = $request->input('term'); // Obtén el término de búsqueda del formulario

            // Realiza una consulta para buscar instituciones que coincidan con $term
            $instituciones = DB::table('institucions')
            ->where('nomInstitucion', 'like', '%' . $term . '%')
            ->pluck('nomInstitucion'); // Cambia 'nombre' al nombre de tu columna de instituciones

    
            return response()->json($instituciones);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function obtenerUgels(Request $request)
    {       
        $anio = $request->input('anio') ?: '2026'; // Valor por defecto 2023 si no se proporciona
        
        $ugels = DB::table('pro_evidencias')
            ->select('users.ugel', DB::raw('count(distinct pro_evidencias.idUser) as docentes_count'))
            ->join('users', 'pro_evidencias.idUser', '=', 'users.id')
            ->where('pro_evidencias.estado', '1')
            ->whereYear('fecha', $anio)
            ->whereRaw("LENGTH(users.ugel) > 0")
            ->groupBy('users.ugel')
            ->get();
    
        return response()->json($ugels);
    }

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
        $anio = $request->input('anio') ?: '2026'; // Valor por defecto 2023 si no se proporciona
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            
            ->leftJoin('pro_evidencias', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_evidencias.idUser')
                    ->where('pro_evidencias.estado', '=', '1')
                    ->whereYear('pro_evidencias.fecha', $anio);
            })
            ->where('institucions.ugel', '=',$ugelSeleccionada)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_evidencias.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_evidencias', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_evidencias.idUser')
                    ->where('pro_evidencias.estado', '=', '1')
                    ->whereYear('pro_evidencias.fecha', $anio);
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
        
        $anio = $request->input('anio') ?: '2026'; // Valor por defecto 2023
        $term = $request->input('term'); // Obtén el término de búsqueda del formulario

        // Realiza una consulta para buscar instituciones que coincidan con $term y tengan información sobre docentes y agendas
        $resultados = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_evidencias', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_evidencias.idUser')
                    ->where('pro_evidencias.estado', '=', '1')
                    ->whereYear('pro_evidencias.fecha', $anio);
            })
            ->where('institucions.ugel', '=', $ugel)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_evidencias.idUser) as agendas_count'))
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
        $cargo = Auth::user()->cargo;
        // Corrige el operador de asignación a comparación
        if ($cargo == "Especialista UGEL") {
            $ugelSeleccionada = Auth::user()->ugel;
        }
        else {         
            $ugelSeleccionada = $request->input('ugel');
        }

        $institucionSeleccionada = $request->input('docente');
        $anio = $request->input('anio') ?: '2026'; // Valor por defecto 2023
        
        $docentes = DB::table('users')
            ->leftJoin('pro_evidencias', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_evidencias.idUser')
                    ->where('pro_evidencias.estado', '=', '1')
                    ->whereYear('pro_evidencias.fecha', $anio);
            })
            ->where('users.institucion', '=', $institucionSeleccionada)
            ->where('users.ugel', 'like', '%' . $ugelSeleccionada . '%')
            ->select('users.name', DB::raw('count(pro_evidencias.id) as agendas_count'))
            ->groupBy('users.name')
            ->having('agendas_count', '>=', 0)
            ->get();

        return response()->json($docentes);
    }

    public function buscadordocente(Request $request)
    {
        $institucion = $request->input('institucion'); 
        $term = $request->input('term');
        $anio = $request->input('anio') ?: '2026'; // Valor por defecto 2023
        
        $docentes = DB::table('users')
            ->leftJoin('pro_evidencias', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_evidencias.idUser')
                    ->where('pro_evidencias.estado', '=', '1')
                    ->whereYear('pro_evidencias.fecha', $anio);
            })
            ->where('users.institucion', '=', $institucion)
            ->where('users.name', 'like', '%' . $term . '%')
            ->select('users.name', DB::raw('count(pro_evidencias.idUser) as agendas_count'))
            ->groupBy('users.name')
            ->having('agendas_count', '>=', 0) 
            ->get();
            
        return response()->json($docentes);
    }

    public function exportarTodos(Request $request)
    {
        // Replicamos la misma lógica de filtros que tenemos en buscarGeneral
        $dni = trim($request->get('texto', ''));
        $name = trim($request->get('docentes', ''));
        $ugel = trim($request->get('ugels', ''));
        $nominstitucion = trim($request->get('instituciones', ''));
        $nivel = trim($request->get('nivel', ''));
        $anio = trim($request->get('anio', '2023')); // Valor por defecto 2023

        // Construimos la misma consulta pero sin paginación
        $query = Evidencia::select(
            "pro_evidencias.nombreEvidencia", "pro_evidencias.descripcion", 
            "pro_evidencias.fecha", "users.name", "users.cargo", 
            "users.nivelinstitucion", "users.institucion", 
            "users.provincia", "users.distrito", "users.ugel"
        )
        ->join("users", "users.id", "=", "pro_evidencias.idUser")
        ->where('pro_evidencias.estado', '1');
        
        // Aplicar filtro de año
        if (!empty($anio)) {
            $query->whereYear('pro_evidencias.fecha', $anio);
        }

        // Aplicamos los mismos filtros
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
        
        if (!empty($nivel)) {
            $query->where("users.nivelinstitucion", "LIKE", "%$nivel%");
        }

        // Obtenemos TODOS los resultados (sin paginar)
        $evidencias = $query->orderBy('pro_evidencias.fecha', 'desc')->get();

        // Determinamos el formato de exportación
        $format = $request->get('format', 'excel');
        
        // Según el formato, generamos el archivo correspondiente
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($evidencias);
            case 'csv':
                return $this->exportToCsv($evidencias);
            case 'pdf':
                return $this->exportToPdf($evidencias);
            default:
                return $this->exportToExcel($evidencias);
        }
    }

    private function exportToExcel($evidencias)
    {
        // Configuramos las cabeceras para descargar un archivo Excel
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=evidencias.xls',
        ];

        // Generamos el contenido del archivo Excel como HTML
        $content = '<table border="1">';
        $content .= '<tr><th>Nombre de la Asistencia</th><th>Descripción</th><th>Fecha</th><th>Usuario</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE.</th><th>Provincia</th><th>Distrito</th><th>UGEL</th></tr>';
        
        foreach ($evidencias as $item) {
            $content .= '<tr>';
            $content .= '<td>' . $item->nombreEvidencia . '</td>';
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

    private function exportToCsv($evidencias)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=evidencias.csv',
        ];

        $callback = function() use ($evidencias) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM para Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeceras
            fputcsv($file, [
                'Nombre de la Asistencia', 
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
            foreach ($evidencias as $item) {
                fputcsv($file, [
                    $item->nombreEvidencia,
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

    private function exportToPdf($evidencias)
    {
        // Aquí podrías usar una biblioteca como TCPDF, Dompdf o mPDF para generar un PDF
        // Por simplicidad, solo devolvemos un mensaje
        return response("La exportación a PDF requiere una biblioteca adicional. Por favor, exporta a Excel o CSV.", 200);
    }
}
