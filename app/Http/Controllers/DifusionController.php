<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DifusionController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:accions.index')->only('index');
        $this->middleware('can:accions.create')->only('create', 'store');
        $this->middleware('can:accions.edit')->only('edit', 'update');
        $this->middleware('can:accions.destroy')->only('destroy');
        $this->middleware('can:accions.view')->only('general');
        $this->middleware('can:accions.dre')->only('dre');
    }
    
    public function index()
    {
        $usuario = Auth::user()->id;
        $accions = Accion::where('estado', '1')
                         ->where('idUser',$usuario)
                         ->where('tipo', 'difusion')
                         ->orderby('fecha','desc')
                         ->paginate(10);
        return view('difusion.index')->with('accions',$accions);
    }

    public function general()
    {
        $ugeluser = Auth::user()->ugel;
        $anioActual = request()->get('anio', '2025'); // Por defecto muestra 2025
        $cargo = Auth::user()->cargo;
        
        // Primero verificar si es Especialista DRE y enviarlo directamente a dre.blade.php
        if ($cargo == 'Especialista DRE') {
            $accions = Accion::select("pro_accions.id", "pro_accions.nombreAccion", "pro_accions.descripcion",
                                "pro_accions.documento", "pro_accions.color", "pro_accions.fecha", 
                                "pro_accions.lugar", "users.name", "users.institucion", "users.provincia", 
                                "users.cargo", "users.nivelinstitucion", "users.distrito", "users.ugel", "users.dni")
                ->join("users", "users.id", "=", "pro_accions.idUser")
                ->where('pro_accions.estado', '1')
                ->where("pro_accions.tipo", "difusion")
                ->whereYear('pro_accions.fecha', $anioActual)
                ->orderby('pro_accions.fecha', 'desc')
                ->paginate(10);
            
            $rols = [];
            $buscars = ['1', '2'];  
            
            return view('difusion.dre', compact('accions', 'rols', 'buscars', 'anioActual'));
        }
        // For users with assigned UGEL
        else if ($ugeluser != '') {
            if ($cargo == 'Director') { 
                // Director code...
                $institucion = Auth::user()->institucion;
                $accions = Accion::select(/* ... */)
                    // queries and filters...
                    ->paginate(10);
                $buscars = [];
            }      
            else if ($cargo == 'Docente' || $cargo == 'PC') {
                // Docente/PC code...
                $institucion = Auth::user()->institucion;
                $accions = Accion::select(/* ... */)
                    // queries and filters...
                    ->paginate(10);
                $buscars = [];
            } 
            else {
                // Especialista UGEL code...
                $accions = Accion::select(/* ... */)
                    // queries and filters...
                    ->paginate(10); 
                $buscars = ['1'];
            }
                                
            $rols = ['1', '5'];
            
            return view("difusion.view", compact('accions', 'rols', 'buscars', 'anioActual'));
        }
        // For any other users without UGEL assignment (fallback case)
        else {
            // This should rarely happen - users without UGEL who aren't Especialista DRE
            $accions = Accion::select("pro_accions.id", "pro_accions.nombreAccion", "pro_accions.descripcion",
                                "pro_accions.documento", "pro_accions.color", "pro_accions.fecha", 
                                "pro_accions.lugar", "users.name", "users.institucion", "users.provincia", 
                                "users.cargo", "users.nivelinstitucion", "users.distrito", "users.ugel", "users.dni")
                ->join("users", "users.id", "=", "pro_accions.idUser")
                ->where('pro_accions.estado', '1')
                ->where("pro_accions.tipo", "difusion")
                ->whereYear('pro_accions.fecha', $anioActual)
                ->orderby('pro_accions.fecha', 'desc')
                ->paginate(10);
            
            $rols = [];
            $buscars = ['1', '2'];  
            
            // You might want to consider a different view for non-DRE, non-UGEL users
            // Or keep this the same if you want them to see everything
            return view('difusion.dre', compact('accions', 'rols', 'buscars', 'anioActual'));
        }
    }


    public function ugel()
    {
        $ugel = Auth::user()->ugel;
        $anioActual = request()->get('anio', '2025'); // Por defecto 2025
        
        $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.fecha","pro_accions.documento","pro_accions.color","pro_accions.descripcion","pro_accions.updated_at","pro_accions.fecha","users.name","users.institucion","users.provincia","users.distrito","users.ugel")
            ->join("users","users.id","=","pro_accions.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_accions.estado', '1')
            ->where('pro_accions.tipo', 'difusion')
            ->whereYear('fecha', $anioActual)
            ->orderby('pro_accions.fecha','desc')
            ->paginate(10);
            return view("difusion.view",compact('accions'));
    }

    public function director()
    {
        $institucion = Auth::user()->institucion;
        $anioActual = request()->get('anio', '2025'); // Por defecto 2025
        
        $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento","pro_accions.color","pro_accions.descripcion","pro_accions.updated_at","pro_accions.fecha","users.name","users.institucion","users.provincia","users.distrito","users.ugel")
            ->join("users","users.id","=","pro_accions.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_accions.estado', '1')
            ->where('pro_accions.tipo', 'difusion')
            ->orderby('pro_accions.fecha','desc')
            ->whereYear('fecha', $anioActual)
            ->paginate(10);
            return view("difusion.view",compact('accions'));
    }
    public function profesorcoordinador()
    {
        $institucion = Auth::user()->institucion;
        $anioActual = request()->get('anio', '2025'); // Por defecto 2025
        
        $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento","pro_accions.color","pro_accions.descripcion","pro_accions.updated_at","pro_accions.fecha","users.name","users.institucion","users.provincia","users.distrito","users.ugel")
            ->join("users","users.id","=","pro_accions.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_accions.estado', '1')
            ->where('pro_accions.tipo', 'difusion')
            ->orderby('pro_accions.fecha','desc')
            ->whereYear('fecha', $anioActual)
            ->paginate(10);
            return view("difusion.view",compact('accions'));
    }

    public function buscar(Request $request){
        $usuario = Auth::user()->id;
        $texto = trim($request->get('texto'));
        $fecha = trim($request->get('fecha'));
        $anio = trim($request->get('anio', date('Y'))); // Por defecto el año actual
        
        $accions = Accion::where("nombreAccion","LIKE","%".$texto."%")
        ->where("fecha","LIKE","%".$fecha."%")
        ->where('estado', '1')
        ->whereYear('fecha', $anio)
        ->where('idUser', $usuario)
        ->where('tipo', 'difusion')
        ->orderby('fecha','desc')
        ->paginate(10);
        return view('difusion.index')->with('accions',$accions);
    }

    public function buscarGeneral(Request $request)
    {
        $cargo = Auth::user()->cargo;
        $anio = $request->get('anio', '2025'); // Por defecto 2025

        if ($cargo == 'Especialista DRE') {
            if (empty($request->get('ugels')) && empty($request->get('instituciones')) && empty($request->get('docentes')) && empty($request->get('texto')) && empty($request->get('nivel')) && empty($request->get('anio'))) {
                return redirect('/difusion-general');
            } else {
                $dni = trim($request->get('texto'));
                $docente = trim($request->get('docentes'));
                $ugel = trim($request->get('ugels'));
                $nominstitucion = trim($request->get('instituciones'));
                $nivel = trim($request->get('nivel')); // Obtener el valor del nivel

                $query = Accion::select(
                    "pro_accions.id", "pro_accions.nombreAccion", "pro_accions.documento", "pro_accions.color", 
                    "pro_accions.descripcion", "pro_accions.fecha", "pro_accions.lugar", "users.name", "users.cargo", 
                    "users.nivelinstitucion", "users.institucion", "users.provincia", "users.distrito", "users.ugel", 
                    "users.dni"
                )
                ->join("users", "users.id", "=", "pro_accions.idUser")
                ->where("pro_accions.tipo", "difusion") // Tipo de acción
                ->whereYear('pro_accions.fecha', $anio) // Filtro de año aplicado aquí
                ->where('pro_accions.estado', '1');    // Estado activo

                // Aplicar los filtros que se hayan especificado
                if (!empty($ugel)) {
                    $query->where("users.ugel", "LIKE", "%$ugel%");
                }
                
                if (!empty($dni)) {
                    $query->where("users.dni", "LIKE", "%$dni%");
                }
                
                if (!empty($docente)) {
                    $query->where("users.name", "LIKE", "%$docente%");
                }
                
                if (!empty($nominstitucion)) {
                    $query->where("users.institucion", "LIKE", "%$nominstitucion%");
                }
                
                // Aplicar el filtro de nivel de institución
                if (!empty($nivel)) {
                    $query->where("users.nivelinstitucion", "LIKE", "%$nivel%");
                }

                $accions = $query->orderBy('pro_accions.fecha', 'desc')->paginate(1000);

                return view('difusion.dre')->with('accions', $accions);
            }  
        }
        else {
            if (empty($request->get('nomdocente')) && empty($request->get('nominstitucion')) && empty($request->get('nivel')) && empty($request->get('texto')) && empty($request->get('anio'))) {
                return redirect('/difusion-general');
            }
            else {
                $cargo = Auth::user()->cargo;

                if ($cargo == 'Director') {
                    //$nivel = Auth::user()->nivelinstitucion;//Para filtrar por nivel (Escolarizado o Pronoei) segun quien esta ingresando
                    $dni = trim($request->get('texto'));
                    $nomdocente = trim($request->get('nomdocente'));
                    $ugel = trim($request->get('ugel'));
                    $nivel = trim($request->get('nivel')); // Agregar el nuevo filtro
                    
                    $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento","pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar","users.name","users.cargo","users.nivelinstitucion","users.institucion","users.provincia","users.distrito","users.ugel")
                    ->join("users","users.id","=","pro_accions.idUser")
                    ->where("users.dni","LIKE","%".$dni."%")
                    ->where("users.name","LIKE","%".$nomdocente."%")
                    ->where('pro_accions.estado', '1')
                    ->whereYear('fecha', $anio) // Filtro de año
                    ->where("pro_accions.tipo", "difusion");
                    
                    // Aplicar el filtro de nivel si existe
                    if (!empty($nivel)) {
                        $accions->where('users.nivelinstitucion', "LIKE", "%".$nivel."%");
                    }
                    
                    $accions = $accions->orderBy('pro_accions.fecha','desc')
                    ->paginate(10);
                    
                    $buscars = [];   
                    $rols =['1','5'];
                    return view('difusion.view')->with('accions',$accions)->with('rols',$rols)->with('buscars',$buscars);           
                }
                // Continuar con otros roles...
            }
        }
    }

    public function download($id)
    {
        $accion = Accion::findOrFail($id);
        $pathToFile = storage_path('app/public/' . $accion->enlace);
        
        // Verificar si el archivo existe antes de descargarlo
        
        return response()->download($pathToFile);
    }

    public function create()
    {
        return view('difusion.create');
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
        $fileContent = $request->get('nombreAccion').' '.$dateTimeNow.'.'. $extension;
        $route = 'accion';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la función storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
        
        $difusions = new Accion;
        $difusions->enlace = $route . '/' . $fileContent;
        $difusions->nombreAccion = $request->get('nombreAccion');
        switch($extension){
            case 'doc':
                $difusions->documento = 'fas fa-file-word';
                $difusions->color = 'blue';
                break;
            case 'docx':
                $difusions->documento = 'fas fa-file-word';
                $difusions->color = 'blue';
                break;
            case 'png':
                $difusions->documento = 'fas fa-file-image';
                $difusions->color = 'darkturquoise';
                break;
            case 'jpg':
                $difusions->documento = 'fas fa-file-image';
                $difusions->color = 'darkturquoise';
                break;
            case 'jpeg':
                $difusions->documento = 'fas fa-file-image';
                $difusions->color = 'darkturquoise';
                break;
            case 'pdf':
                $difusions->documento = 'fas fa-file-pdf';
                $difusions->color = 'red';
                break;
            case 'ppt':
                $difusions->documento = 'fas fa-file-powerpoint';
                $difusions->color = 'orange';
                break;
            case 'pptm':
                $difusions->documento = 'fas fa-file-powerpoint';
                $difusions->color = 'orange';
                break;
            case 'pptx':
                $difusions->documento = 'fas fa-file-powerpoint';
                $difusions->color = 'orange';
                break;
            case 'xlm':
                $difusions->documento = 'fas fa-file-excel';
                $difusions->color = 'green';
                break;
            case 'xls':
                $difusions->documento = 'fas fa-file-excel';
                $difusions->color = 'green';
                break;   
            case 'xlsm':
                $difusions->documento = 'fas fa-file-excel';
                $difusions->color = 'green';
                break;
            case 'xlsx':
                $difusions->documento = 'fas fa-file-excel';
                $difusions->color = 'green';
                break;
        }
        $difusions->descripcion = $request->get('descripcion');
        $difusions->fecha = $request->get('fecha');
        $difusions->idUser = Auth::user()->id;
        $difusions->tipo = 'difusion';
        $difusions->estado = 1;
        $difusions->save();
        
        return redirect('/difusions')->with('success', '¡Registro guardado con éxito!');
    }

    public function show()
    {
        //
    }

    
    public function edit($id)
    {
        $difusion = Accion::findOrFail($id);
        return view('difusion.edit')->with('difusion', $difusion);
    }

    
    public function update(Request $request, Accion $difusion)
    {
        $request->validate([
            'documento' => 'required|mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:2048',
        ], [
            'documento.max' => 'Archivo superior a 2MB', 
        ]);
        $file = $request->file('documento');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $dateTimeNow = now()->format('Ymd_His_u');
        $fileContent = $request->get('nombreAccion').' '.$dateTimeNow.'.'. $extension;
        $route = 'accion';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la función storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
         // Eliminar el archivo antiguo
        Storage::delete('public/'.$difusion->enlace);

        $difusion->enlace = $route . '/' . $fileContent;
        $difusion->nombreAccion = $request->get('nombreAccion');
        switch($extension){
            case 'doc':
                $difusion->documento = 'fas fa-file-word';
                $difusion->color = 'blue';
                break;
            case 'docx':
                $difusion->documento = 'fas fa-file-word';
                $difusion->color = 'blue';
                break;
            case 'png':
                $difusion->documento = 'fas fa-file-image';
                $difusion->color = 'darkturquoise';
                break;
            case 'jpg':
                $difusion->documento = 'fas fa-file-image';
                $difusion->color = 'darkturquoise';
                break;
            case 'jpeg':
                $difusion->documento = 'fas fa-file-image';
                $difusion->color = 'darkturquoise';
                break;
            case 'pdf':
                $difusion->documento = 'fas fa-file-pdf';
                $difusion->color = 'red';
                break;
            case 'ppt':
                $difusion->documento = 'fas fa-file-powerpoint';
                $difusion->color = 'orange';
                break;
            case 'pptm':
                $difusion->documento = 'fas fa-file-powerpoint';
                $difusion->color = 'orange';
                break;
            case 'pptx':
                $difusion->documento = 'fas fa-file-powerpoint';
                $difusion->color = 'orange';
                break;
            case 'xlm':
                $difusion->documento = 'fas fa-file-excel';
                $difusion->color = 'green';
                break;
            case 'xls':
                $difusion->documento = 'fas fa-file-excel';
                $difusion->color = 'green';
                break;   
            case 'xlsm':
                $difusion->documento = 'fas fa-file-excel';
                $difusion->color = 'green';
                break;
            case 'xlsx':
                $difusion->documento = 'fas fa-file-excel';
                $difusion->color = 'green';
                break;
        }
        $difusion->descripcion = $request->get('descripcion');
        $difusion->fecha = $request->get('fecha');
        $difusion->idUser = Auth::user()->id;
        $difusion->tipo = 'difusion';
        $difusion->estado = 1;
        $difusion->save();
        
        return redirect('/difusions');
    }

   
    public function destroy(Accion $difusion)
    {
        Storage::delete('public/'.$difusion->enlace);
        $difusion->estado = 0;
        $difusion->idUser = Auth::user()->id;
        $difusion->save();
        session()->flash('success', 'Registro eliminado!');
        return redirect('/difusions');
    }

    public function obtenerUgels(Request $request)
    {
        // Obtener el año seleccionado o usar el año actual como valor predeterminado
        $anio = $request->input('anio', date('Y'));
        
        \Log::info('Obteniendo UGELs para el año: ' . $anio);
        
        try {
            $ugels = DB::table('pro_accions')
                ->select('users.ugel', DB::raw('count(distinct pro_accions.idUser) as docentes_count'))
                ->join('users', 'pro_accions.idUser', '=', 'users.id')
                ->where('pro_accions.tipo', 'difusion')
                ->where('pro_accions.estado', '1')
                ->whereYear('pro_accions.fecha', $anio)
                ->whereRaw("LENGTH(users.ugel) > 0")
                ->groupBy('users.ugel')
                ->get();
            
            \Log::info('UGELs encontradas: ' . $ugels->count());
            
            return response()->json($ugels);
        } catch (\Exception $e) {
            \Log::error('Error al obtener UGELs: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
        $anio = $request->input('anio', date('Y')); // Por defecto el año actual si no se proporciona
        
        \Log::info('Buscando instituciones para UGEL: ' . $ugelSeleccionada . ' y año: ' . $anio);
        
        try {
            // Primera consulta: Contar docentes con acciones
            $resultados = DB::table('institucions')
                ->leftJoin('users', function($join) {
                    $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                        ->on('institucions.ugel', '=', 'users.ugel');
                })
                ->leftJoin('pro_accions', function($join) use ($anio) {
                    $join->on('users.id', '=', 'pro_accions.idUser')
                        ->where('pro_accions.estado', '=', '1')
                        ->where('pro_accions.tipo', '=', 'difusion')
                        ->whereYear('pro_accions.fecha', $anio);
                })
                ->where('institucions.ugel', '=', $ugelSeleccionada)
                ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_accions.idUser) as agendas_count'))
                ->groupBy('institucions.nomInstitucion')
                ->get();

            // Segunda consulta: Contar total de docentes
            $totalDocentes = DB::table('institucions')
                ->leftJoin('users', function($join) {
                    $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                        ->on('institucions.ugel', '=', 'users.ugel');
                })
                ->where('institucions.ugel', '=', $ugelSeleccionada)
                ->select('institucions.nomInstitucion', DB::raw('count(distinct users.id) as total_docentes'))
                ->groupBy('institucions.nomInstitucion')
                ->get();

            // Combinar resultados
            $resultados = $resultados->map(function ($item) use ($totalDocentes) {
                $total = $totalDocentes->firstWhere('nomInstitucion', $item->nomInstitucion);
                $item->total_docentes = $total ? $total->total_docentes : 0;
                return $item;
            });
            
            \Log::info('Instituciones encontradas: ' . $resultados->count());
            
            return response()->json($resultados);
        } catch (\Exception $e) {
            \Log::error('Error al buscar instituciones: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function buscadorinstitucion(Request $request)
    {   
        $cargo = Auth::user()->cargo;
        $anio = $request->input('anio', '2025'); // Por defecto 2025
        
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
            ->leftJoin('pro_accions', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_accions.idUser')
                    ->where('pro_accions.estado', '=', '1')
                    ->whereYear('pro_accions.fecha', '=', $anio);
            })
            ->where('institucions.ugel', '=', $ugel)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_accions.idUser) as agendas_count'))
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
        $anio = $request->input('anio', date('Y')); // Por defecto el año actual
        
        if ($cargo == "Especialista UGEL") {
            $ugelSeleccionada = Auth::user()->ugel;
        } else {
            $ugelSeleccionada = $request->input('ugel');
        }

        $institucionSeleccionada = $request->input('docente');
        
        \Log::info('Buscando docentes para institución: ' . $institucionSeleccionada . ', UGEL: ' . $ugelSeleccionada . ' y año: ' . $anio);
        
        try {
            $docentes = DB::table('users')
                ->leftJoin('pro_accions', function($join) use ($anio) {
                    $join->on('users.id', '=', 'pro_accions.idUser')
                        ->where('pro_accions.estado', '=', '1')
                        ->where('pro_accions.tipo', '=', 'difusion')
                        ->whereYear('pro_accions.fecha', $anio);
                })
                ->where('users.institucion', '=', $institucionSeleccionada)
                ->where('users.ugel', 'like', '%' . $ugelSeleccionada . '%')
                ->select('users.name', DB::raw('count(pro_accions.id) as agendas_count'))
                ->groupBy('users.name')
                ->having('agendas_count', '>=', 0)
                ->get();
            
            \Log::info('Docentes encontrados: ' . $docentes->count());
            
            return response()->json($docentes);
        } catch (\Exception $e) {
            \Log::error('Error al buscar docentes: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function buscadordocente(Request $request)
    {
        $institucion = $request->input('institucion'); 
        $term = $request->input('term');
        $anio = $request->input('anio', '2025'); // Por defecto 2025
        
        $docentes = DB::table('users')
        ->leftJoin('pro_accions', function($join) use ($anio) {
            $join->on('users.id', '=', 'pro_accions.idUser')
                ->where('pro_accions.estado', '=', '1')
                ->where('pro_accions.tipo', '=', 'difusion')
                ->whereYear('pro_accions.fecha', '=', $anio);
        })
        ->where('users.institucion', '=', $institucion)
        ->where('users.name', 'like', '%' . $term . '%')
        ->select('users.name', DB::raw('count(pro_accions.idUser) as agendas_count'))
        ->groupBy('users.name')
        ->having('agendas_count', '>=', 0) 
        ->get();
        
        return response()->json($docentes);
    }
    public function obtenerCantidadRegistros(Request $request)
    {
        $anio = $request->input('anio', '2025'); // Por defecto 2025
        
        $cantidadRegistros = Accion::whereYear('fecha', '=', $anio)
                                ->where('tipo', '=', 'difusion')
                                ->where('estado', '=', '1')
                                ->count();

        return $cantidadRegistros;
    }

}


