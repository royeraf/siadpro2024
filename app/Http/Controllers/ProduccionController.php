<?php

namespace App\Http\Controllers;

use App\Models\Produccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProduccionController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:produccions.index')->only('index');
        $this->middleware('can:produccions.create')->only('create', 'store');
        $this->middleware('can:produccions.edit')->only('edit', 'update');
        $this->middleware('can:produccions.destroy')->only('destroy');
        $this->middleware('can:produccions.view')->only('general');
        $this->middleware('can:produccions.ugel')->only('ugel');
        $this->middleware('can:produccions.director')->only('director');
        $this->middleware('can:produccions.dre')->only('dre');
    }
    
    public function index()
    {
        $usuario = Auth::user()->id;

        $produccions = Produccion::where('estado', '1')
            ->where('idUser', $usuario)
            ->orderby('id', 'desc')
            ->paginate(10);

        return view('produccion.index')
            ->with('produccions', $produccions);
    }

    public function create()
    {
        return view('produccion.create');
    }

    public function general()
    {
        // Log inicio del método para depuración
        \Log::info('Iniciando método general() en ProduccionController');
        
        $ugeluser = Auth::user()->ugel;
        $cargo = Auth::user()->cargo;
        // Obtener el año seleccionado o usar 2026 por defecto
        $selectedYear = request('year', '2026');
        
        \Log::info('Parámetros de filtro:', [
            'ugel_usuario' => $ugeluser,
            'cargo_usuario' => $cargo,
            'año_seleccionado' => $selectedYear
        ]);
        
        // Si el usuario es Especialista DRE, mostrar todas las producciones
        if ($cargo == 'Especialista DRE') {
            $produccions = Produccion::select(
                "pro_produccions.id", "pro_produccions.nombreProduccion", "pro_produccions.fecha", 
                "pro_produccions.descripcion", "pro_produccions.documento", "pro_produccions.color", 
                "pro_produccions.descripcion", "pro_produccions.lugar", "users.name", "users.institucion", 
                "users.provincia", "users.cargo", "users.nivelinstitucion", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_produccions.idUser")
            ->where('pro_produccions.estado', '1')
            ->whereYear('pro_produccions.fecha', $selectedYear)
            ->orderby('pro_produccions.id', 'desc')
            ->paginate(10);
            
            $rols = [];
            $buscars = ['1', '2'];  
            
            \Log::info('Total de registros encontrados para Especialista DRE: ' . $produccions->total());
            
            return view('produccion.dre')
                ->with('produccions', $produccions)
                ->with('rols', $rols)
                ->with('buscars', $buscars)
                ->with('selectedYear', $selectedYear);
        }
        // Resto de la lógica original para otros roles
        else if ($ugeluser != '') {
            // Lógica actual para usuarios con UGEL asignada
            // (Director, Docente, PC, Especialista UGEL)
            // [Mantén el código original aquí]
            
            if ($cargo == 'Director') {
                // Lógica para Director
                $institucion = Auth::user()->institucion;
                $produccions = Produccion::select(
                    "pro_produccions.id", "pro_produccions.nombreProduccion", "pro_produccions.fecha", 
                    "pro_produccions.documento", "pro_produccions.color", "pro_produccions.descripcion", 
                    "pro_produccions.lugar", "users.name", "users.institucion", "users.provincia", 
                    "users.cargo", "users.nivelinstitucion", "users.distrito", "users.ugel"
                )
                ->join("users", "users.id", "=", "pro_produccions.idUser")
                ->where("users.institucion", $institucion)
                ->where('pro_produccions.estado', '1')
                ->whereYear('pro_produccions.fecha', $selectedYear)
                ->orderby('pro_produccions.id', 'desc')
                ->paginate(10);
                $buscars = [];
            }
            else if ($cargo == 'Docente' || $cargo == 'PC') {
                // Lógica para Docente o PC
                $institucion = Auth::user()->institucion;
                $produccions = Produccion::select(
                    "pro_produccions.id", "pro_produccions.nombreProduccion", "pro_produccions.fecha", 
                    "pro_produccions.documento", "pro_produccions.color", "pro_produccions.descripcion", 
                    "pro_produccions.lugar", "users.name", "users.institucion", "users.provincia", 
                    "users.cargo", "users.nivelinstitucion", "users.distrito", "users.ugel"
                )
                ->join("users", "users.id", "=", "pro_produccions.idUser")
                ->where("users.institucion", $institucion)
                ->whereYear('pro_produccions.fecha', $selectedYear)
                ->where('pro_produccions.estado', '1')
                ->orderby('pro_produccions.id', 'desc')
                ->paginate(10);
                $buscars = [];
            } else {
                // Lógica para UGEL - especialistas u otros roles
                $produccions = Produccion::select(
                    "pro_produccions.id", "pro_produccions.nombreProduccion", "pro_produccions.fecha", 
                    "pro_produccions.documento", "pro_produccions.color", "pro_produccions.descripcion", 
                    "pro_produccions.lugar", "users.name", "users.institucion", "users.provincia", 
                    "users.cargo", "users.nivelinstitucion", "users.distrito", "users.ugel"
                )
                ->join("users", "users.id", "=", "pro_produccions.idUser")
                ->where("users.ugel", $ugeluser)
                ->whereYear('pro_produccions.fecha', $selectedYear)
                ->where('pro_produccions.estado', '1')
                ->orderby('pro_produccions.id', 'desc')
                ->paginate(10);   
                $buscars = ['1'];  
            }
            
            $rols = ['1', '5'];
            
            \Log::info('Total de registros encontrados para usuario con UGEL: ' . $produccions->total());
            
            return view("produccion.view", compact('produccions', 'rols', 'buscars', 'selectedYear'));
        }
        else {
            // Lógica para otros casos (usuario sin cargo específico o sin UGEL)
            // Mostrar solo sus propias producciones por seguridad
            $usuario = Auth::user()->id;
            $produccions = Produccion::select(
                "pro_produccions.id", "pro_produccions.nombreProduccion", "pro_produccions.fecha", 
                "pro_produccions.documento", "pro_produccions.color", "pro_produccions.descripcion", 
                "pro_produccions.lugar", "users.name", "users.institucion", "users.provincia", 
                "users.cargo", "users.nivelinstitucion", "users.distrito", "users.ugel"
            )
            ->join("users", "users.id", "=", "pro_produccions.idUser")
            ->where('pro_produccions.idUser', $usuario)
            ->where('pro_produccions.estado', '1')
            ->whereYear('pro_produccions.fecha', $selectedYear)
            ->orderby('pro_produccions.id', 'desc')
            ->paginate(10);
            
            $rols = [];
            $buscars = [];
            
            \Log::info('Total de registros encontrados para usuario sin roles específicos: ' . $produccions->total());
            
            return view("produccion.view", compact('produccions', 'rols', 'buscars', 'selectedYear'));
        }
    }

        
    
    public function download($id)
    {
        $produccion = Produccion::findOrFail($id);
        $pathToFile = storage_path('app/public/' . $produccion->enlace);
        
        // Verificar si el archivo existe antes de descargarlo
        
        return response()->download($pathToFile);
    }

    public function buscar(Request $request)
    {
        $usuario = Auth::user()->id;
        $texto = trim($request->get('texto'));

        $produccions = Produccion::where("nombreProduccion", "LIKE", "%" . $texto . "%")
            ->where('estado', '1')
            ->where('idUser', $usuario)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('produccion.index')
            ->with('produccions', $produccions);
    }

    public function buscarGeneral(Request $request)
    {
        \Log::info('Parámetros de búsqueda:', $request->all());
        
        $cargo = Auth::user()->cargo;
        // Obtener el año seleccionado o usar 2026 por defecto
        $selectedYear = $request->get('year', '2026');

        if ($cargo == 'Especialista DRE') {
            if (empty($request->get('ugels')) && empty($request->get('instituciones')) && 
                empty($request->get('docentes')) && empty($request->get('texto')) && 
                empty($request->get('nivel')) && $request->get('year', null) === null) {
                return redirect('/produccion-general');
            } else {
                $dni = trim($request->get('texto', ''));
                $name = trim($request->get('docentes', ''));
                $ugel = trim($request->get('ugels', ''));
                $nominstitucion = trim($request->get('instituciones', ''));
                $nivel = trim($request->get('nivel', ''));

                $query = Produccion::select(
                    "pro_produccions.id", "pro_produccions.nombreProduccion", "pro_produccions.fecha", 
                    "pro_produccions.documento", "pro_produccions.color", "pro_produccions.descripcion", 
                    "pro_produccions.lugar", "users.name", "users.cargo", "users.nivelinstitucion", 
                    "users.institucion", "users.provincia", "users.distrito", "users.ugel", "users.dni"
                )
                ->join("users", "users.id", "=", "pro_produccions.idUser")
                ->where('pro_produccions.estado', '1')
                ->whereYear('pro_produccions.fecha', $selectedYear);  // Filtrar por el año seleccionado

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
                
                if (!empty($nivel)) {
                    $query->where("users.nivelinstitucion", "=", $nivel);
                }

                $produccions = $query->orderBy('pro_produccions.id', 'desc')->paginate(1000);
                
                \Log::info('Total de registros encontrados: ' . $produccions->total());
                \Log::info('Año seleccionado: ' . $selectedYear);

                return view('produccion.dre')
                    ->with('produccions', $produccions)
                    ->with('selectedYear', $selectedYear);  // Pasar el año seleccionado a la vista
            }
        }
        // El resto del método para otros roles
        else {
            // Lógica para otros roles
            $produccions = Produccion::select(
                "pro_produccions.id", "pro_produccions.nombreProduccion", "pro_produccions.fecha", 
                "pro_produccions.documento", "pro_produccions.color", "pro_produccions.descripcion", 
                "pro_produccions.lugar", "users.name", "users.cargo", "users.nivelinstitucion", 
                "users.institucion", "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_produccions.idUser")
            ->where('pro_produccions.estado', '1')
            ->whereYear('pro_produccions.fecha', $selectedYear)  // Filtrar por el año seleccionado
            ->orderBy('pro_produccions.id', 'desc')
            ->paginate(1000);
            
            \Log::info('Total de registros encontrados (otros roles): ' . $produccions->total());
            
            return view('produccion.dre')
                ->with('produccions', $produccions)
                ->with('selectedYear', $selectedYear);  // Pasar el año seleccionado a la vista
        }
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
        $fileContent = $request->get('nombreProduccion').' '.$dateTimeNow.'.'. $extension;
        $route = 'produccion';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la funci贸n storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
        
        $produccions = new Produccion;
        $produccions->enlace = $route . '/' . $fileContent;
        $produccions->nombreProduccion = $request->get('nombreProduccion');
        switch($extension){
            case 'doc':
                $produccions->documento = 'fas fa-file-word';
                $produccions->color = 'blue';
                break;
            case 'docx':
                $produccions->documento = 'fas fa-file-word';
                $produccions->color = 'blue';
                break;
            case 'png':
                $produccions->documento = 'fas fa-file-image';
                $produccions->color = 'darkturquoise';
                break;
            case 'jpg':
                $produccions->documento = 'fas fa-file-image';
                $produccions->color = 'darkturquoise';
                break;
            case 'jpeg':
                $produccions->documento = 'fas fa-file-image';
                $produccions->color = 'darkturquoise';
                break;
            case 'pdf':
                $produccions->documento = 'fas fa-file-pdf';
                $produccions->color = 'red';
                break;
            case 'ppt':
                $produccions->documento = 'fas fa-file-powerpoint';
                $produccions->color = 'orange';
                break;
            case 'pptm':
                $produccions->documento = 'fas fa-file-powerpoint';
                $produccions->color = 'orange';
                break;
            case 'pptx':
                $produccions->documento = 'fas fa-file-powerpoint';
                $produccions->color = 'orange';
                break;
            case 'xlm':
                $produccions->documento = 'fas fa-file-excel';
                $produccions->color = 'green';
                break;
            case 'xls':
                $produccions->documento = 'fas fa-file-excel';
                $produccions->color = 'green';
                break;   
            case 'xlsm':
                $produccions->documento = 'fas fa-file-excel';
                $produccions->color = 'green';
                break;
            case 'xlsx':
                $produccions->documento = 'fas fa-file-excel';
                $produccions->color = 'green';
                break;
        }
        $produccions->fecha = $request->get('fecha');
        $produccions->descripcion = $request->get('descripcion');
        $produccions->idUser = Auth::user()->id;
        $produccions->estado = 1;
        $produccions->save();
        
        return redirect('/produccions')->with('success', '¡Registro guardado con éxito!');
    }

    public function show()
    {
        //
    }

    
    public function edit($id)
    {
        $produccion = Produccion::findOrFail($id);
        return view('produccion.edit')->with('produccion', $produccion);
    }

    
    public function update(Request $request, Produccion $produccion)
    {
        $request->validate([
            'documento' => 'required|mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10048',
        ]);
        
        $file = $request->file('documento');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $dateTimeNow = now()->format('Ymd_His_u');
        $fileContent = $request->get('nombreProduccion').' '.$dateTimeNow.'.'. $extension;
        $route = 'produccion';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la funci贸n storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
         // Eliminar el archivo antiguo
        Storage::delete('public/'.$produccion->enlace);

        $produccion->enlace = $route . '/' . $fileContent;
        $produccion->nombreProduccion = $request->get('nombreProduccion');
        switch($extension){
            case 'doc':
                $produccion->documento = 'fas fa-file-word';
                $produccion->color = 'blue';
                break;
            case 'docx':
                $produccion->documento = 'fas fa-file-word';
                $produccion->color = 'blue';
                break;
            case 'png':
                $produccion->documento = 'fas fa-file-image';
                $produccion->color = 'darkturquoise';
                break;
            case 'jpg':
                $produccion->documento = 'fas fa-file-image';
                $produccion->color = 'darkturquoise';
                break;
            case 'jpeg':
                $produccion->documento = 'fas fa-file-image';
                $produccion->color = 'darkturquoise';
                break;
            case 'pdf':
                $produccion->documento = 'fas fa-file-pdf';
                $produccion->color = 'red';
                break;
            case 'ppt':
                $produccion->documento = 'fas fa-file-powerpoint';
                $produccion->color = 'orange';
                break;
            case 'pptm':
                $produccion->documento = 'fas fa-file-powerpoint';
                $produccion->color = 'orange';
                break;
            case 'pptx':
                $produccion->documento = 'fas fa-file-powerpoint';
                $produccion->color = 'orange';
                break;
            case 'xlm':
                $produccion->documento = 'fas fa-file-excel';
                $produccion->color = 'green';
                break;
            case 'xls':
                $produccion->documento = 'fas fa-file-excel';
                $produccion->color = 'green';
                break;   
            case 'xlsm':
                $produccion->documento = 'fas fa-file-excel';
                $produccion->color = 'green';
                break;
            case 'xlsx':
                $produccion->documento = 'fas fa-file-excel';
                $produccion->color = 'green';
                break;
        }
        $produccion->fecha = $request->get('fecha');
        $produccion->descripcion = $request->get('descripcion');
        $produccion->idUser = Auth::user()->id;
        $produccion->estado = 1;
        $produccion->save();
        
        return redirect('/produccions');
    }

   
    public function destroy(Produccion $produccion)
    {
        Storage::delete('public/'.$produccion->enlace);
        $produccion->estado = 0;
        $produccion->idUser = Auth::user()->id;
        $produccion->save();
        session()->flash('success', 'Registro eliminado!');
        return redirect('/produccions');
    }

    public function obtenerUgels(Request $request)
    {       
        // Obtener el año seleccionado del request o usar 2026 como predeterminado
        $selectedYear = $request->get('year', '2026');
        
        $ugels = DB::table('pro_produccions')
            ->select('users.ugel', DB::raw('count(distinct pro_produccions.idUser) as docentes_count'))
            ->join('users', 'pro_produccions.idUser', '=', 'users.id')
            ->where('pro_produccions.estado', '1')
            ->whereYear('pro_produccions.fecha', $selectedYear) // Usar el año seleccionado
            ->whereRaw("LENGTH(users.ugel) > 0")
            ->groupBy('users.ugel')
            ->get();

        return response()->json($ugels);
    }

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
        $selectedYear = $request->input('year', '2026');
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_produccions', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_produccions.idUser')
                    ->where('pro_produccions.estado', '=', '1')
                    ->whereYear('pro_produccions.fecha', $selectedYear);
            })
            ->where('institucions.ugel', '=', $ugelSeleccionada)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_produccions.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_produccions', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_produccions.idUser')
                    ->where('pro_produccions.estado', '=', '1')
                    ->whereYear('pro_produccions.fecha', $selectedYear);
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
        $selectedYear = $request->input('year', '2026');
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_produccions', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_produccions.idUser')
                    ->where('pro_produccions.estado', '=', '1')
                    ->whereYear('pro_produccions.fecha', $selectedYear);
            })
            ->where('institucions.ugel', '=', $ugel)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_produccions.idUser) as agendas_count'))
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
    public function buscarDocenteporInstitucion(Request $request)
    {
        // Logs para depuración
        \Log::info('Parámetros recibidos en buscarDocenteporInstitucion:', $request->all());
        
        $cargo = Auth::user()->cargo;
        if ($cargo == "Especialista UGEL") {
            $ugelSeleccionada = Auth::user()->ugel;
        } else {         
            // Acepta ambos nombres de parámetros para mejor compatibilidad
            $ugelSeleccionada = $request->input('ugel', $request->input('ugels', ''));
        }

        $institucionSeleccionada = $request->input('docente');
        $selectedYear = $request->input('year', '2026');
        
        \Log::info('Buscando docentes para:', [
            'institucion' => $institucionSeleccionada, 
            'ugel' => $ugelSeleccionada,
            'year' => $selectedYear
        ]);

        $docentes = DB::table('users')
            ->leftJoin('pro_produccions', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_produccions.idUser')
                    ->where('pro_produccions.estado', '=', '1')
                    ->whereYear('pro_produccions.fecha', $selectedYear);
            })
            ->where('users.institucion', '=', $institucionSeleccionada)
            // Aplicar el filtro por UGEL solo si está presente
            ->when($ugelSeleccionada, function($query) use ($ugelSeleccionada) {
                return $query->where('users.ugel', 'like', '%' . $ugelSeleccionada . '%');
            })
            ->select('users.name', DB::raw('count(pro_produccions.id) as agendas_count'))
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
        $selectedYear = $request->input('year', '2026');
        
        $docentes = DB::table('users')
            ->leftJoin('pro_produccions', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_produccions.idUser')
                    ->where('pro_produccions.estado', '=', '1')
                    ->whereYear('pro_produccions.fecha', $selectedYear);
            })
            ->where('users.institucion', '=', $institucion)
            ->where('users.name', 'like', '%' . $term . '%')
            ->select('users.name', DB::raw('count(pro_produccions.idUser) as agendas_count'))
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
        $nivel = trim($request->get('nivel', ''));
        $selectedYear = $request->get('year', '2026');  // Obtener el año seleccionado o usar 2026 por defecto
    
        // Construir la misma consulta pero sin paginación
        $query = Produccion::select(
            "pro_produccions.nombreProduccion", "pro_produccions.descripcion", 
            "pro_produccions.fecha", "users.name", "users.cargo", 
            "users.nivelinstitucion", "users.institucion", 
            "users.provincia", "users.distrito", "users.ugel"
        )
        ->join("users", "users.id", "=", "pro_produccions.idUser")
        ->where('pro_produccions.estado', '1')
        ->whereYear('pro_produccions.fecha', $selectedYear);  // Filtrar por el año seleccionado
    
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
        
        if (!empty($nivel)) {
            $query->where("users.nivelinstitucion", "=", $nivel);
        }
    
        // Obtener TODOS los resultados (sin paginar)
        $produccions = $query->orderBy('pro_produccions.fecha', 'desc')->get();
    
        // El resto del método sigue igual...
        // Aquí iría la lógica para exportar a Excel, CSV, etc.
    }
}


