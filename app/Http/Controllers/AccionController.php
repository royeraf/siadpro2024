<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AccionController extends Controller
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
        $accions = Accion::where('estado', '1')->where('idUser',$usuario)->where('tipo','sensibilizacion')->orderby('fecha','desc')->paginate(10);
        return view('accion.index')->with('accions',$accions);
    }

    public function general()
        {
            $ugeluser = Auth::user()->ugel;
            $cargo = Auth::user()->cargo;
            // Obtiene el año del request, si no hay, usa 2026 como default
            $anio = request()->get('anio', '2026');
            
            // Si es un Especialista DRE, mostrar todas las acciones sin filtros
            if ($cargo == 'Especialista DRE') {
                $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento",
                        "pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar",
                        "users.name","users.institucion","users.provincia","users.cargo",
                        "users.nivelinstitucion","users.distrito","users.ugel","users.dni")
                ->join("users","users.id","=","pro_accions.idUser")
                ->where('pro_accions.estado', '1')
                ->where("pro_accions.tipo", "sensibilizacion")
                ->whereYear('fecha', $anio)
                ->orderby('pro_accions.fecha','desc')
                ->paginate(10);
                
                $rols = [];
                $buscars = ['1','2'];
                
                return view('accion.dre', compact('accions', 'rols', 'buscars', 'anio'));
            }
            // Para usuarios con UGEL asignada (pero que no son Especialista DRE)
            else if ($ugeluser != '') {
                if ($cargo == 'Director') {
                    $institucion = Auth::user()->institucion;
                    $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento",
                            "pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar",
                            "users.name","users.institucion","users.provincia","users.cargo",
                            "users.nivelinstitucion","users.distrito","users.ugel","users.dni")
                    ->join("users","users.id","=","pro_accions.idUser")
                    ->where("users.institucion", $institucion)
                    ->where('pro_accions.estado', '1')
                    ->where("pro_accions.tipo", "sensibilizacion")
                    ->whereYear('fecha', $anio)
                    ->orderby('pro_accions.fecha','desc')
                    ->paginate(10);
                    $buscars = [];
                }
                else if ($cargo == 'Docente' || $cargo == 'PC') {
                    $institucion = Auth::user()->institucion;
                    $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento",
                            "pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar",
                            "users.name","users.institucion","users.provincia","users.cargo",
                            "users.nivelinstitucion","users.distrito","users.ugel","users.dni")
                    ->join("users","users.id","=","pro_accions.idUser")
                    ->where("users.institucion", $institucion)
                    ->where('pro_accions.estado', '1')
                    ->where("pro_accions.tipo", "sensibilizacion")
                    ->whereYear('fecha', $anio)
                    ->orderby('pro_accions.fecha','desc')
                    ->paginate(10);
                    $buscars = [];
                } 
                else {
                    // Esto es Por Ugeles (Especialista UGEL)
                    $ugeluser = Auth::user()->ugel;
                    $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento",
                            "pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar",
                            "users.name","users.institucion","users.provincia","users.cargo",
                            "users.nivelinstitucion","users.distrito","users.ugel","users.dni")
                    ->join("users","users.id","=","pro_accions.idUser")
                    ->where("users.ugel", $ugeluser)
                    ->where('pro_accions.estado', '1')
                    ->where("pro_accions.tipo", "sensibilizacion")
                    ->whereYear('fecha', $anio)
                    ->orderby('pro_accions.fecha','desc')
                    ->paginate(10);
                    $buscars = ['1'];
                }
                
                $rols = ['1','5'];
                return view("accion.view", compact('accions', 'rols', 'buscars', 'anio'));
            }
            // Para otros casos (puede ser otro administrador o usuarios sin UGEL)
            else {
                // Esto es general por Dre o Admin sin UGEL asignada
                $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento",
                        "pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar",
                        "users.name","users.institucion","users.provincia","users.cargo",
                        "users.nivelinstitucion","users.distrito","users.ugel","users.dni")
                ->join("users","users.id","=","pro_accions.idUser")
                ->where('pro_accions.estado', '1')
                ->where("pro_accions.tipo", "sensibilizacion")
                ->whereYear('fecha', $anio)
                ->orderby('pro_accions.fecha','desc')
                ->paginate(10);
                
                $rols = [];
                $buscars = ['1','2'];
                
                return view('accion.view', compact('accions', 'rols', 'buscars', 'anio'));
            }
        }

    public function ugel()
    {
        $ugel = Auth::user()->ugel;
        $anio = request()->get('anio', '2026');
        $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento","pro_accions.color","pro_accions.fecha","pro_accions.lugar","pro_users.name","users.institucion","users.provincia","users.distrito","users.ugel","users.dni")
            ->join("users","users.id","=","pro_accions.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_accions.estado', '1')
            ->where('pro_accions.tipo', 'sensibilizacion')
            ->whereYear('fecha', $anio)
            ->orderby('pro_accions.fecha','desc')
            ->paginate(10);
            return view("accion.view",compact('accions'));
    }

    public function director()
    {
        $institucion = Auth::user()->institucion;
        $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento","pro_accions.color","pro_accions.fecha","pro_accions.lugar","users.name","users.institucion","users.provincia","users.distrito","users.ugel","users.dni")
            ->join("users","users.id","=","pro_accions.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_accions.estado', '1')
            
            ->where('pro_accions.tipo', 'sensibilizacion')
            ->orderby('pro_accions.fecha','desc')
            ->paginate(10);
            return view("accion.view",compact('accions'));
    }
    public function profesorcoordinador()
    {
        $institucion = Auth::user()->institucion;
        $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento","pro_accions.color","pro_accions.fecha","pro_accions.lugar","users.name","users.institucion","users.provincia","users.distrito","users.ugel","users.dni")
            ->join("users","users.id","=","pro_accions.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_accions.estado', '1')
            ->where('pro_accions.tipo', 'sensibilizacion')
            ->orderby('pro_accions.fecha','desc')
            ->paginate(10);
            return view("accion.view",compact('accions'));
    }

    public function buscar(Request $request){
        $usuario = Auth::user()->id;
        $texto=trim($request->get('texto'));
        $fecha=trim($request->get('fecha'));
        $accions = Accion::where("nombreAccion","LIKE","%".$texto."%")
        ->where("fecha","LIKE","%".$fecha."%")
        ->where('estado', '1')
        ->where('idUser', $usuario)
        ->where("tipo", "sensibilizacion")
        ->orderby('fecha','desc')
        ->paginate(10);
        return view('accion.index')->with('accions',$accions);
    }

    public function buscarGeneral(Request $request){
        $cargo = Auth::user()->cargo;
        $anio = trim($request->get('anio')) ?: '2026'; // Capturar el año para todos los roles
        
        if ($cargo == 'Especialista DRE') {
            if (empty($request->get('ugels')) && empty($request->get('instituciones')) && 
                empty($request->get('docentes')) && empty($request->get('texto')) && 
                empty($request->get('anio'))) {
                return redirect('/accion-general');
            }
            else {    
                $dni = trim($request->get('texto'));
                $docente = trim($request->get('docentes'));
                $ugel = trim($request->get('ugels'));
                $nominstitucion = trim($request->get('instituciones'));
    
                $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento",
                           "pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar",
                           "users.name","users.cargo","users.nivelinstitucion","users.institucion",
                           "users.provincia","users.distrito","users.ugel","users.dni")
                ->join("users","users.id","=","pro_accions.idUser")
                ->where("users.dni","LIKE","%".$dni."%")
                ->where('users.name',"LIKE","%".$docente."%")
                ->where("users.ugel","LIKE","%".$ugel."%")
                ->where("users.institucion","LIKE","%".$nominstitucion."%")
                ->where("pro_accions.tipo", "sensibilizacion")
                ->whereYear('fecha', $anio)
                ->where('pro_accions.estado', '1');
                
                // Aplicar filtro por año si está seleccionado
                if (!empty($anio)) {
                    $accions = $accions->whereYear('fecha', $anio);
                }
                
                $accions = $accions->orderBy('pro_accions.fecha','desc')
                ->paginate(10);
                
                return view('accion.dre')->with('accions',$accions); 
            }  
        } 
        else {
            if (empty($request->get('nomdocente')) && empty($request->get('nominstitucion')) && 
                empty($request->get('nivel')) && empty($request->get('texto')) && 
                empty($request->get('anio'))) {
                return redirect('/accion-general');
            }
            else {
                $cargo = Auth::user()->cargo;
    
                if ($cargo == 'Director') {
                    //$nivel = Auth::user()->nivelinstitucion;
                    $dni = trim($request->get('texto'));
                    $nomdocente = trim($request->get('nomdocente'));
                    $ugel = trim($request->get('ugel'));
                    
                    $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento",
                               "pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar",
                               "users.name","users.cargo","users.nivelinstitucion","users.institucion",
                               "users.provincia","users.distrito","users.ugel","users.dni")
                    ->join("users","users.id","=","pro_accions.idUser")
                    ->where("users.dni","LIKE","%".$dni."%")
                    ->where("users.name","LIKE","%".$nomdocente."%")
                    ->where('pro_accions.estado', '1')
                    ->where("pro_accions.tipo", "sensibilizacion");
                    
                    // Aplicar filtro por año si está seleccionado
                    if (!empty($anio)) {
                        $accions = $accions->whereYear('fecha', $anio);
                    }
                    
                    $accions = $accions->orderBy('pro_accions.fecha','desc')
                    ->paginate(10);
                    
                    $buscars = [];   
                    $rols = ['1','5'];
                    return view('accion.view')->with('accions',$accions)->with('rols',$rols)->with('buscars',$buscars);
                }     
                
                if ($cargo == 'Especialista UGEL') {
                    $dni = trim($request->get('texto'));
                    $nomdocente = trim($request->get('nomdocente'));
                    $nominstitucion = trim($request->get('nominstitucion'));
                    $ugeluser = Auth::user()->ugel;
                    $nivel = trim($request->get('nivel'));
                    
                    $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento",
                               "pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar",
                               "users.name","users.cargo","users.nivelinstitucion","users.institucion",
                               "users.provincia","users.distrito","users.ugel","users.dni")
                    ->join("users","users.id","=","pro_accions.idUser")
                    ->where("users.dni","LIKE","%".$dni."%")
                    ->where("users.name","LIKE","%".$nomdocente."%")
                    ->where("users.institucion","LIKE","%".$nominstitucion."%")
                    ->where("users.ugel", $ugeluser)
                    ->where('pro_accions.estado', '1')
                    ->where("pro_accions.tipo", "sensibilizacion")
                    ->where('users.nivelinstitucion',"LIKE","%".$nivel."%");
                    
                    // Aplicar filtro por año si está seleccionado
                    if (!empty($anio)) {
                        $accions = $accions->whereYear('fecha', $anio);
                    }
                    
                    $accions = $accions->orderBy('pro_accions.fecha','desc')
                    ->paginate(10);
                    
                    $rols = ['1','5'];
                    $buscars = ['1'];
                    return view('accion.view')->with('accions',$accions)->with('rols',$rols)->with('buscars',$buscars);               
                }
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
        return view('accion.create');
    }

    
    public function store(Request $request)
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
        
        $accions = new Accion;
        $accions->enlace = $route . '/' . $fileContent;
        $accions->nombreAccion = $request->get('nombreAccion');
        switch($extension){
            case 'doc':
                $accions->documento = 'fas fa-file-word';
                $accions->color = 'blue';
                break;
            case 'docx':
                $accions->documento = 'fas fa-file-word';
                $accions->color = 'blue';
                break;
            case 'png':
                $accions->documento = 'fas fa-file-image';
                $accions->color = 'darkturquoise';
                break;
            case 'jpg':
                $accions->documento = 'fas fa-file-image';
                $accions->color = 'darkturquoise';
                break;
            case 'jpeg':
                $accions->documento = 'fas fa-file-image';
                $accions->color = 'darkturquoise';
                break;
            case 'pdf':
                $accions->documento = 'fas fa-file-pdf';
                $accions->color = 'red';
                break;
            case 'ppt':
                $accions->documento = 'fas fa-file-powerpoint';
                $accions->color = 'orange';
                break;
            case 'pptm':
                $accions->documento = 'fas fa-file-powerpoint';
                $accions->color = 'orange';
                break;
            case 'pptx':
                $accions->documento = 'fas fa-file-powerpoint';
                $accions->color = 'orange';
                break;
            case 'xlm':
                $accions->documento = 'fas fa-file-excel';
                $accions->color = 'green';
                break;
            case 'xls':
                $accions->documento = 'fas fa-file-excel';
                $accions->color = 'green';
                break;   
            case 'xlsm':
                $accions->documento = 'fas fa-file-excel';
                $accions->color = 'green';
                break;
            case 'xlsx':
                $accions->documento = 'fas fa-file-excel';
                $accions->color = 'green';
                break;
        }
        $accions->lugar = $request->get('lugar');
        $accions->fecha = $request->get('fecha');
        $accions->idUser = Auth::user()->id;
        $accions->tipo = 'sensibilizacion';
        $accions->estado = 1;
        $accions->save();
        
        return redirect('/accions')->with('success', '¡Registro guardado con éxito!');
    }

    public function show()
    {
        //
    }

    
    public function edit($id)
    {
        $accion = Accion::findOrFail($id);
        return view('accion.edit')->with('accion', $accion);
    }

    
    public function update(Request $request, Accion $accion)
    {
        $request->validate([
            'documento' => 'required|mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:22048',
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
        Storage::delete('public/'.$accion->enlace);

        $accion->enlace = $route . '/' . $fileContent;
        $accion->nombreAccion = $request->get('nombreAccion');
        switch($extension){
            case 'doc':
                $accion->documento = 'fas fa-file-word';
                $accion->color = 'blue';
                break;
            case 'docx':
                $accion->documento = 'fas fa-file-word';
                $accion->color = 'blue';
                break;
            case 'png':
                $accion->documento = 'fas fa-file-image';
                $accion->color = 'darkturquoise';
                break;
            case 'jpg':
                $accion->documento = 'fas fa-file-image';
                $accion->color = 'darkturquoise';
                break;
            case 'jpeg':
                $accion->documento = 'fas fa-file-image';
                $accion->color = 'darkturquoise';
                break;
            case 'pdf':
                $accion->documento = 'fas fa-file-pdf';
                $accion->color = 'red';
                break;
            case 'ppt':
                $accion->documento = 'fas fa-file-powerpoint';
                $accion->color = 'orange';
                break;
            case 'pptm':
                $accion->documento = 'fas fa-file-powerpoint';
                $accion->color = 'orange';
                break;
            case 'pptx':
                $accion->documento = 'fas fa-file-powerpoint';
                $accion->color = 'orange';
                break;
            case 'xlm':
                $accion->documento = 'fas fa-file-excel';
                $accion->color = 'green';
                break;
            case 'xls':
                $accion->documento = 'fas fa-file-excel';
                $accion->color = 'green';
                break;   
            case 'xlsm':
                $accion->documento = 'fas fa-file-excel';
                $accion->color = 'green';
                break;
            case 'xlsx':
                $accion->documento = 'fas fa-file-excel';
                $accion->color = 'green';
                break;
        }
       
        $accion->lugar = $request->get('lugar');
        $accion->fecha = $request->get('fecha');
        $accion->idUser = Auth::user()->id;
        $accion->tipo = 'sensibilizacion';
        $accion->estado = 1;
        $accion->save();
        
        return redirect('/accions');
    }

   
    public function destroy(Accion $accion)
    {
        Storage::delete('public/'.$accion->enlace);
        $accion->estado = 0;
        $accion->idUser = Auth::user()->id;
        $accion->save();
        session()->flash('success', '¡Registro eliminado!');
        return redirect('/accions');
    }

    public function obtenerUgels(Request $request)
    {
        // Obtener el año seleccionado o mostrar todos si no se especifica
        $anio = $request->input('anio');
        
        $query = DB::table('pro_accions')
            ->select('users.ugel', DB::raw('count(distinct pro_accions.idUser) as docentes_count'))
            ->join('users', 'pro_accions.idUser', '=', 'users.id')
            ->where('pro_accions.tipo', 'sensibilizacion')
            ->where('pro_accions.estado', '1')
            ->whereRaw("LENGTH(users.ugel) > 0");
        
        // Solo aplicar filtro por año si se proporciona uno
        if ($anio) {
            $query->whereYear('fecha', $anio);
        }
        
        $ugels = $query->groupBy('users.ugel')->get();

        return response()->json($ugels);
    }



    public function buscarInstitucionporUgel(Request $request)
    {
        $ingreso = Auth::user()->cargo;
        $anio = $request->input('anio', '2026'); // Obtener el año seleccionado
        
        switch ($ingreso) {
            case 'Especialista DRE':
                $ugelSeleccionada = $request->input('ugel');
                break;
            case 'Especialista UGEL':
                $ugelSeleccionada = Auth::user()->ugel;
                break;
            default:
                break;
        }
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_accions', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_accions.idUser')
                    ->where('pro_accions.estado', '=', '1')
                    ->where('pro_accions.tipo', '=', 'sensibilizacion')
                    ->whereYear('pro_accions.fecha', '=', $anio); // Agregar filtro por año
            })
            ->where('institucions.ugel', '=', $ugelSeleccionada)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_accions.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_accions', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_accions.idUser')
                    ->where('pro_accions.estado', '=', '1')
                    ->where('pro_accions.tipo', '=', 'sensibilizacion')
                    ->whereYear('pro_accions.fecha', '=', $anio); // Agregar filtro por año
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

    public function buscadorinstitucion(Request $request)
        {   
            $cargo = Auth::user()->cargo;
            $anio = $request->input('anio', '2026'); // Obtener el año seleccionado
            
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
                ->leftJoin('pro_accions', function($join) use ($anio) {
                    $join->on('users.id', '=', 'pro_accions.idUser')
                        ->where('pro_accions.estado', '=', '1')
                        ->whereYear('pro_accions.fecha', '=', $anio); // Agregar filtro por año
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

            // Combina los resultados
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
            $anio = $request->input('anio', '2026'); // Obtener el año seleccionado
            
            if ($cargo == "Especialista UGEL") {
                $ugelSeleccionada = Auth::user()->ugel;
            } else {         
                $ugelSeleccionada = $request->input('ugel');
            }

            $institucionSeleccionada = $request->input('docente');
                
            $docentes = DB::table('users')
                ->leftJoin('pro_accions', function($join) use ($anio) {
                    $join->on('users.id', '=', 'pro_accions.idUser')
                        ->where('pro_accions.estado', '=', '1')
                        ->where('pro_accions.tipo', '=', 'sensibilizacion')
                        ->whereYear('pro_accions.fecha', '=', $anio); // Agregar filtro por año
                })
                ->where('users.institucion', '=', $institucionSeleccionada)
                ->where('users.ugel', 'like', '%' . $ugelSeleccionada . '%')
                ->select('users.name', DB::raw('count(pro_accions.id) as agendas_count'))
                ->groupBy('users.name')
                ->having('agendas_count', '>=', 0)
                ->get();

            return response()->json($docentes);
        }
    public function buscadordocente(Request $request)
            {
                $institucion = $request->input('institucion'); 
                $term = $request->input('term');
                $anio = $request->input('anio', '2026'); // Obtener el año seleccionado
                
                $docentes = DB::table('users')
                    ->leftJoin('pro_accions', function($join) use ($anio) {
                        $join->on('users.id', '=', 'pro_accions.idUser')
                            ->where('pro_accions.estado', '=', '1')
                            ->where('pro_accions.tipo', '=', 'sensibilizacion')
                            ->whereYear('pro_accions.fecha', '=', $anio); // Agregar filtro por año
                    })
                    ->where('users.institucion', '=', $institucion)
                    ->where('users.name', 'like', '%' . $term . '%')
                    ->select('users.name', DB::raw('count(pro_accions.idUser) as agendas_count'))
                    ->groupBy('users.name')
                    ->having('agendas_count', '>=', 0) 
                    ->get();
                    
                return response()->json($docentes);
            }
    public function obtenerCantidadRegistros()
    {
        $cantidadRegistros = Accion::count(); // Reemplaza 'TuModelo' con el nombre de tu modelo

        return $cantidadRegistros;
    }
    public function exportarFiltradoTotal(Request $request)
    {
        $cargo = Auth::user()->cargo;
        $anio = trim($request->get('anio')) ?: '2026';
        $dni = trim($request->get('texto', ''));
        $docente = trim($request->get('docentes', ''));
        $ugel = trim($request->get('ugels', ''));
        $institucion = trim($request->get('instituciones', ''));
        
        // Registrar los valores de los parámetros para depuración
        \Log::info('Filtros de exportación recibidos:', [
            'dni' => $dni,
            'anio' => $anio,
            'ugel' => $ugel,
            'institucion' => $institucion,
            'docente' => $docente
        ]);
        
        // Construir la consulta base
        $query = Accion::select(
            "pro_accions.id", "pro_accions.nombreAccion", "pro_accions.documento",
            "pro_accions.color", "pro_accions.descripcion", "pro_accions.fecha", 
            "pro_accions.lugar", "users.name", "users.institucion", "users.provincia", 
            "users.cargo", "users.nivelinstitucion", "users.distrito", "users.ugel", "users.dni"
        )
        ->join("users", "users.id", "=", "pro_accions.idUser")
        ->where('pro_accions.estado', '1')
        ->where("pro_accions.tipo", "sensibilizacion");
        
        // Aplicar filtros según las condiciones proporcionadas
        if (!empty($anio)) {
            $query->whereYear('pro_accions.fecha', $anio);
        }
        
        if (!empty($dni)) {
            $query->where("users.dni", "LIKE", "%{$dni}%");
        }
        
        if (!empty($ugel)) {
            // En lugar de LIKE con comodines, usar una comparación más estricta
            // Extraer el valor exacto de la UGEL sin "UGEL+"
            $ugel = str_replace('UGEL+', '', $ugel);
            $ugelParts = explode('+', $ugel);
            
            if (count($ugelParts) > 0) {
                $query->where(function($q) use ($ugelParts) {
                    foreach ($ugelParts as $part) {
                        if (!empty($part)) {
                            $q->where("users.ugel", "LIKE", "%{$part}%");
                        }
                    }
                });
            }
        }
        
        if (!empty($institucion)) {
            $query->where("users.institucion", "LIKE", "%{$institucion}%");
        }
        
        if (!empty($docente)) {
            $query->where("users.name", "LIKE", "%{$docente}%");
        }
        
        // Si es especialista UGEL, filtrar por su UGEL
        if ($cargo == 'Especialista UGEL') {
            $ugeluser = Auth::user()->ugel;
            $query->where("users.ugel", $ugeluser);
        }
        
        // Si es director, filtrar por su institución
        if ($cargo == 'Director' || $cargo == 'Docente' || $cargo == 'PC') {
            $institucionUser = Auth::user()->institucion;
            $query->where("users.institucion", $institucionUser);
        }
        
        // Antes de ejecutar la consulta, obtener la consulta SQL para depuración
        $sqlQuery = $query->toSql();
        $sqlBindings = $query->getBindings();
        \Log::info('SQL Query:', ['query' => $sqlQuery, 'bindings' => $sqlBindings]);
        
        // Ordenar y obtener todos los resultados sin paginación
        $accions = $query->orderBy('pro_accions.fecha', 'desc')->get();
        
        // Registrar la cantidad de resultados
        \Log::info('Total registros para exportación completa: ' . $accions->count());
        
        return response()->json($accions);
    }

}

