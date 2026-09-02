<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasScopeTabs;
use App\Models\Produccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProduccionController extends Controller
{
    use HasScopeTabs;

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
    
    public function index(Request $request)
    {
        $usuario = Auth::user()->id;

        $query = Produccion::where('estado', '1')->where('idUser', $usuario);

        if ($request->filled('texto')) {
            $query->where('nombreProduccion', 'LIKE', '%' . $request->input('texto') . '%');
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
                $q->where('nombreProduccion', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $produccions = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('produccion._rows', ['produccions' => $produccions])->render(),
                'pagination' => (string) $produccions->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $produccions->total(),
                'totalFormatted' => number_format($produccions->total()),
                'from' => $produccions->firstItem() ?? 0,
                'to' => $produccions->lastItem() ?? 0,
            ]);
        }

        $listaAnios = $this->listaAniosProduccion();

        $tabs = $this->tabsProduccion('index');

        return view('produccion.index', compact('produccions', 'listaAnios', 'tabs'));
    }

    /**
     * Pestañas de "Producción de Textos Infantiles". Solo Mis registros +
     * General: este controlador no tiene métodos ugel()/director() (aunque el
     * constructor sí registra middleware muerto para esos permisos).
     */
    private function tabsProduccion(string $activo): array
    {
        return $this->scopeTabs([
            'index'   => ['permission' => 'produccions.index', 'label' => 'Mis registros', 'route' => 'produccions.index'],
            'general' => ['permission' => 'produccions.view', 'label' => 'General', 'route' => 'produccions.view'],
        ], $activo);
    }

    public function create()
    {
        return view('produccion.create');
    }

    /**
     * Años plausibles disponibles para el selector de filtro, descartando
     * fechas corruptas (p. ej. años como 23, 203 o 1978 por datos mal
     * digitados) — mismo criterio que los demás módulos migrados.
     */
    private function listaAniosProduccion(string $anioActual = null)
    {
        $listaAnios = Produccion::whereYear('fecha', '>=', 2010)
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

        $cargo = Auth::user()->cargo;
        $ugeluser = Auth::user()->ugel;

        $query = Produccion::select(
                "pro_produccions.id", "pro_produccions.nombreProduccion", "pro_produccions.descripcion",
                "pro_produccions.documento", "pro_produccions.color", "pro_produccions.fecha",
                "pro_produccions.lugar", "pro_produccions.enlace", "users.name", "users.cargo", "users.nivelinstitucion",
                "users.institucion", "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_produccions.idUser")
            ->where('pro_produccions.estado', '1')
            ->whereYear('pro_produccions.fecha', $anio);

        // Alcance por rol (mismo criterio que la implementación original)
        if ($cargo == 'Especialista DRE') {
            // Especialista DRE: todas las producciones del año
        } elseif ($cargo == 'Director' || $cargo == 'Docente' || $cargo == 'PC') {
            $query->where("users.institucion", Auth::user()->institucion);
        } elseif ($ugeluser != '') {
            $query->where("users.ugel", $ugeluser);
        } else {
            // Sin cargo/UGEL asignada: solo sus propias producciones
            $query->where('pro_produccions.idUser', Auth::user()->id);
        }

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
                $q->where('pro_produccions.nombreProduccion', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_produccions.descripcion', 'LIKE', "%{$buscar}%");
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

        $produccions = $query->orderBy('pro_produccions.fecha', 'desc')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('produccion._rows_general', ['produccions' => $produccions])->render(),
                'pagination' => (string) $produccions->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $produccions->total(),
                'totalFormatted' => number_format($produccions->total()),
                'from' => $produccions->firstItem() ?? 0,
                'to' => $produccions->lastItem() ?? 0,
            ]);
        }

        $listaUgels = \App\Models\User::whereNotNull('ugel')->where('ugel', '!=', '')->distinct()->orderBy('ugel')->pluck('ugel');
        $listaAnios = $this->listaAniosProduccion($anio);

        $tabs = $this->tabsProduccion('general');

        return view('produccion.view', compact('produccions', 'anio', 'listaUgels', 'listaAnios', 'tabs'));
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
        return $this->index($request);
    }

    public function buscarGeneral(Request $request)
    {
        return $this->general($request);
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
        $anio = $request->filled('year') ? $request->input('year') : date('Y');

        // Construir la misma consulta pero sin paginación
        $query = Produccion::select(
            "pro_produccions.nombreProduccion", "pro_produccions.descripcion",
            "pro_produccions.fecha", "users.name", "users.cargo",
            "users.nivelinstitucion", "users.institucion",
            "users.provincia", "users.distrito", "users.ugel"
        )
        ->join("users", "users.id", "=", "pro_produccions.idUser")
        ->where('pro_produccions.estado', '1')
        ->whereYear('pro_produccions.fecha', $anio);

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

        // Determinar formato
        $format = $request->get('format', 'excel');

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($produccions);
            default:
                return $this->exportToExcel($produccions);
        }
    }

    private function exportToExcel($produccions)
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=producciones.xls',
        ];

        $content = '<table border="1">';
        $content .= '<tr><th>Tipo de Producción</th><th>Descripción</th><th>Fecha</th><th>Usuario</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE.</th><th>Provincia</th><th>Distrito</th><th>UGEL</th></tr>';

        foreach ($produccions as $item) {
            $content .= '<tr>';
            $content .= '<td>' . $item->nombreProduccion . '</td>';
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

    private function exportToCsv($produccions)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=producciones.csv',
        ];

        $callback = function () use ($produccions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Tipo de Producción', 'Descripción', 'Fecha', 'Usuario', 'Cargo',
                'Institución', 'Tipo de II.EE.', 'Provincia', 'Distrito', 'UGEL'
            ]);

            foreach ($produccions as $item) {
                fputcsv($file, [
                    $item->nombreProduccion,
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


