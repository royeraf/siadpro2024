<?php

namespace App\Http\Controllers;

use App\Models\Evidencia;
use App\Models\User;
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
    
    public function index(Request $request)
    {
        $usuario = Auth::user()->id;

        $evidenciasQuery = Evidencia::where('estado', '1')->where('idUser', $usuario);

        if ($request->filled('texto')) {
            $evidenciasQuery->where('nombreEvidencia', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('fecha')) {
            $evidenciasQuery->where('fecha', 'LIKE', '%' . $request->input('fecha') . '%');
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $evidenciasQuery->where(function ($q) use ($buscar) {
                $q->where('nombreEvidencia', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $evidencias = $evidenciasQuery->orderBy('fecha', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('evidencia._rows', ['evidencias' => $evidencias])->render(),
                'pagination' => (string) $evidencias->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $evidencias->total(),
                'totalFormatted' => number_format($evidencias->total()),
                'from' => $evidencias->firstItem() ?? 0,
                'to' => $evidencias->lastItem() ?? 0,
            ]);
        }

        $tabs = $this->tabsEvidencia('index');

        return view('evidencia.index', compact('evidencias', 'tabs'));
    }

    /**
     * Pestañas de la sección "Asistencia Técnica", una por alcance al que el
     * usuario autenticado tenga permiso (director queda fuera a propósito:
     * ese alcance no tiene enlace de menú todavía).
     */
    private function tabsEvidencia(string $activo): array
    {
        $user = Auth::user();
        $tabs = [];

        if ($user->can('evidencias.index')) {
            $tabs[] = ['label' => 'Mis registros', 'url' => route('evidencias.index'), 'active' => $activo === 'index'];
        }
        if ($user->can('evidencias.ugel')) {
            $tabs[] = ['label' => 'UGEL', 'url' => route('evidencias.ugel'), 'active' => $activo === 'ugel'];
        }
        if ($user->can('evidencias.view')) {
            $tabs[] = ['label' => 'General', 'url' => route('evidencias.view'), 'active' => $activo === 'general'];
        }

        return $tabs;
    }

    /**
     * Años plausibles disponibles para el selector de filtro, descartando
     * fechas corruptas (p. ej. años como 23, 203 o 1978 por datos mal
     * digitados) — mismo criterio que AccionController::general().
     */
    private function listaAniosEvidencia(string $anioActual)
    {
        $listaAnios = Evidencia::whereYear('fecha', '>=', 2010)
            ->selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');

        if (!$listaAnios->contains($anioActual)) {
            $listaAnios->prepend($anioActual);
        }

        return $listaAnios;
    }

    public function general(Request $request)
    {
        $anio = $request->filled('anio') ? $request->input('anio') : date('Y');

        $query = Evidencia::select(
                "pro_evidencias.id", "pro_evidencias.nombreEvidencia", "pro_evidencias.descripcion",
                "pro_evidencias.documento", "pro_evidencias.color", "pro_evidencias.fecha",
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion",
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_evidencias.idUser")
            ->where('pro_evidencias.estado', '1')
            ->whereYear('pro_evidencias.fecha', $anio);

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
                $q->where('pro_evidencias.nombreEvidencia', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_evidencias.descripcion', 'LIKE', "%{$buscar}%");
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

        $evidencias = $query->orderBy('pro_evidencias.fecha', 'desc')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('evidencia._rows_general', ['evidencias' => $evidencias])->render(),
                'pagination' => (string) $evidencias->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $evidencias->total(),
                'totalFormatted' => number_format($evidencias->total()),
                'from' => $evidencias->firstItem() ?? 0,
                'to' => $evidencias->lastItem() ?? 0,
            ]);
        }

        $listaUgels = User::whereNotNull('ugel')->where('ugel', '!=', '')->distinct()->orderBy('ugel')->pluck('ugel');
        $listaAnios = $this->listaAniosEvidencia($anio);
        $tabs = $this->tabsEvidencia('general');

        return view('evidencia.view', compact('evidencias', 'anio', 'listaUgels', 'listaAnios', 'tabs'));
    }

    public function ugel(Request $request)
    {
        $ugel = Auth::user()->ugel;
        $anio = $request->filled('anio') ? $request->input('anio') : date('Y');

        $query = Evidencia::select(
                "pro_evidencias.id", "pro_evidencias.nombreEvidencia", "pro_evidencias.descripcion",
                "pro_evidencias.documento", "pro_evidencias.color", "pro_evidencias.fecha",
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion",
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_evidencias.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_evidencias.estado', '1')
            ->whereYear('pro_evidencias.fecha', $anio);

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
                $q->where('pro_evidencias.nombreEvidencia', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_evidencias.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $evidencias = $query->orderBy('pro_evidencias.fecha', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('evidencia._rows_general', ['evidencias' => $evidencias])->render(),
                'pagination' => (string) $evidencias->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $evidencias->total(),
                'totalFormatted' => number_format($evidencias->total()),
                'from' => $evidencias->firstItem() ?? 0,
                'to' => $evidencias->lastItem() ?? 0,
            ]);
        }

        $listaInstituciones = User::where('ugel', $ugel)->whereNotNull('institucion')->where('institucion', '!=', '')->distinct()->orderBy('institucion')->pluck('institucion');
        $listaAnios = $this->listaAniosEvidencia($anio);
        $tabs = $this->tabsEvidencia('ugel');

        return view('evidencia.ugel', compact('evidencias', 'anio', 'listaInstituciones', 'listaAnios', 'tabs'));
    }

    public function director(Request $request)
    {
        $institucion = Auth::user()->institucion;
        $anio = $request->filled('anio') ? $request->input('anio') : date('Y');

        $query = Evidencia::select(
                "pro_evidencias.id", "pro_evidencias.nombreEvidencia", "pro_evidencias.descripcion",
                "pro_evidencias.documento", "pro_evidencias.color", "pro_evidencias.fecha",
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion",
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_evidencias.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_evidencias.estado', '1')
            ->whereYear('pro_evidencias.fecha', $anio);

        if ($request->filled('texto')) {
            $query->where('pro_evidencias.nombreEvidencia', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('fecha')) {
            $query->where('pro_evidencias.fecha', 'LIKE', '%' . $request->input('fecha') . '%');
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_evidencias.nombreEvidencia', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_evidencias.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $evidencias = $query->orderBy('pro_evidencias.fecha', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('evidencia._rows_general', ['evidencias' => $evidencias])->render(),
                'pagination' => (string) $evidencias->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $evidencias->total(),
                'totalFormatted' => number_format($evidencias->total()),
                'from' => $evidencias->firstItem() ?? 0,
                'to' => $evidencias->lastItem() ?? 0,
            ]);
        }

        $listaAnios = $this->listaAniosEvidencia($anio);

        return view('evidencia.director', compact('evidencias', 'anio', 'listaAnios'));
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
        // Replicamos la misma lógica de filtros que general()
        $anio = $request->filled('anio') ? $request->input('anio') : date('Y');

        $query = Evidencia::select(
            "pro_evidencias.nombreEvidencia", "pro_evidencias.descripcion",
            "pro_evidencias.fecha", "users.name", "users.cargo",
            "users.nivelinstitucion", "users.institucion",
            "users.provincia", "users.distrito", "users.ugel"
        )
        ->join("users", "users.id", "=", "pro_evidencias.idUser")
        ->where('pro_evidencias.estado', '1')
        ->whereYear('pro_evidencias.fecha', $anio);

        if ($request->filled('ugels')) {
            $query->where('users.ugel', $request->input('ugels'));
        }
        if ($request->filled('texto')) {
            $query->where('users.dni', 'LIKE', '%' . $request->input('texto') . '%');
        }
        if ($request->filled('docentes')) {
            $query->where('users.name', 'LIKE', '%' . $request->input('docentes') . '%');
        }
        if ($request->filled('instituciones')) {
            $query->where('users.institucion', $request->input('instituciones'));
        }
        if ($request->filled('nivel')) {
            $query->where('users.nivelinstitucion', $request->input('nivel'));
        }

        $evidencias = $query->orderBy('pro_evidencias.fecha', 'desc')->get();

        return $this->exportToExcel($evidencias);
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

}
