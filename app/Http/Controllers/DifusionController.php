<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasScopeTabs;
use Illuminate\Http\Request;
use App\Models\Accion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DifusionController extends Controller
{
    use HasScopeTabs;

    public function __construct(){
        $this->middleware('auth');
        // Difusión tenía permisos prestados de Sensibilización (accions.*). Ahora
        // tiene su propio set difusions.* (ver DifusionPermissionSeeder), con el
        // mismo reparto de roles que tenían de facto los prestados.
        $this->middleware('can:difusions.index')->only('index');
        $this->middleware('can:difusions.create')->only('create', 'store');
        $this->middleware('can:difusions.edit')->only('edit', 'update');
        $this->middleware('can:difusions.destroy')->only('destroy');
        $this->middleware('can:difusions.view')->only('general', 'exportDifusionGeneral');
        $this->middleware('can:difusions.ugel')->only('ugel', 'exportDifusionUgel');
        $this->middleware('can:difusions.director')->only('director', 'exportDifusionDirector');
        $this->middleware('can:accions.dre')->only('dre');
        // Los endpoints legacy de abajo (buscarGeneral/exportarTodos) ya no se usan desde
        // la vista migrada, pero seguían alcanzables por URL directa sin control de acceso
        // propio: exportarTodos en particular no aplicaba NINGÚN alcance por cargo (cualquier
        // usuario autenticado podía descargar todas las acciones de difusión de todo el
        // sistema). Se cierran con el mismo permiso que protege la vista general.
        $this->middleware('can:difusions.view')->only('buscarGeneral', 'exportarTodos');
    }

    public function index(Request $request)
    {
        $usuario = Auth::user()->id;

        $accionsQuery = Accion::where('estado', '1')
            ->where('idUser', $usuario)
            ->where('tipo', 'difusion');

        if ($request->filled('texto')) {
            $accionsQuery->where('nombreAccion', 'LIKE', '%' . $request->input('texto') . '%');
        }

        if ($request->filled('fecha')) {
            $accionsQuery->where('fecha', 'LIKE', '%' . $request->input('fecha') . '%');
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $accionsQuery->where(function ($q) use ($buscar) {
                $q->where('nombreAccion', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $accions = $accionsQuery->orderBy('fecha', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('difusion._rows', ['accions' => $accions])->render(),
                'pagination' => (string) $accions->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $accions->total(),
                'totalFormatted' => number_format($accions->total()),
                'from' => $accions->firstItem() ?? 0,
                'to' => $accions->lastItem() ?? 0,
            ]);
        }

        $tabs = $this->tabsDifusion('index');

        return view('difusion.index', compact('accions', 'tabs'));
    }

    /**
     * Pestañas de "Acción de Difusión", una por alcance al que el usuario
     * autenticado tenga permiso.
     */
    private function tabsDifusion(string $activo): array
    {
        return $this->scopeTabs([
            'index'    => ['permission' => 'difusions.index', 'label' => 'Mis registros', 'route' => 'difusions.index'],
            'ugel'     => ['permission' => 'difusions.ugel', 'label' => 'UGEL', 'route' => 'difusions.ugel'],
            'general'  => ['permission' => 'difusions.view', 'label' => 'General', 'route' => 'difusions.view'],
            'director' => ['permission' => 'difusions.director', 'label' => 'Director', 'route' => 'difusions.director'],
        ], $activo);
    }

    /**
     * Consulta base compartida por los tres alcances agregados, igual criterio
     * que AccionController::accionsGeneralQuery(): sin parámetros devuelve todo
     * (General); $forceUgel/$forceInstitucion acotan el resultado (UGEL/Director).
     * El alcance lo decide el permiso que habilitó la ruta, no el cargo del
     * usuario.
     *
     * La versión anterior de general() para estas mismas ramas llamaba literalmente a
     * Accion::select(/* ... *\/)->paginate(10) sin join, sin where de tipo/estado y sin
     * ningún alcance: un Director o Docente veía TODAS las acciones del sistema (de
     * cualquier institución, sensibilización incluida). Se corrigió al migrar a esta
     * consulta compartida.
     */
    private function difusionGeneralQuery(Request $request, ?string $forceUgel = null, ?string $forceInstitucion = null): array
    {
        $anio = $request->filled('anio') ? $request->input('anio') : date('Y');

        $query = Accion::select(
                'pro_accions.id', 'pro_accions.nombreAccion', 'pro_accions.descripcion',
                'pro_accions.documento', 'pro_accions.color', 'pro_accions.fecha',
                'pro_accions.enlace',
                'users.name', 'users.institucion', 'users.provincia', 'users.cargo',
                'users.nivelinstitucion', 'users.distrito', 'users.ugel', 'users.dni'
            )
            ->join('users', 'users.id', '=', 'pro_accions.idUser')
            ->where('pro_accions.estado', '1')
            ->where('pro_accions.tipo', 'difusion')
            ->whereYear('pro_accions.fecha', $anio);

        $showFullFilters = $forceUgel === null && $forceInstitucion === null;

        if ($forceInstitucion !== null) {
            $query->where('users.institucion', $forceInstitucion);
        } elseif ($forceUgel !== null) {
            $query->where('users.ugel', $forceUgel);
        } elseif ($showFullFilters) {
            if ($request->filled('ugels')) {
                $query->where('users.ugel', $request->input('ugels'));
            }
            if ($request->filled('instituciones')) {
                $query->where('users.institucion', 'LIKE', '%' . $request->input('instituciones') . '%');
            }
        }

        if ($request->filled('texto')) {
            $query->where('users.dni', 'LIKE', '%' . $request->input('texto') . '%');
        }

        if ($request->filled('docentes')) {
            $query->where('users.name', 'LIKE', '%' . $request->input('docentes') . '%');
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_accions.nombreAccion', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_accions.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        return [$query, $anio, $showFullFilters];
    }

    private function paginateDifusion(Request $request, $query)
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

        return $query->orderBy('pro_accions.fecha', 'desc')->paginate($perPage)->withQueryString();
    }

    private function listaAniosDifusion(string $anio): \Illuminate\Support\Collection
    {
        // Mismo saneo que en AccionController: hay registros con la fecha mal digitada
        // (p. ej. "0024-07-12" en vez de "2024-07-12") que ensuciarían el selector de año.
        $listaAnios = Accion::where('tipo', 'difusion')
            ->whereYear('fecha', '>=', 2010)
            ->selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');
        if (!$listaAnios->contains($anio)) {
            $listaAnios->prepend($anio);
        }

        return $listaAnios;
    }

    private function ajaxDifusionResponse(Request $request, $accions)
    {
        return response()->json([
            'rows' => view('difusion._rows_general', ['accions' => $accions])->render(),
            'pagination' => (string) $accions->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
            'total' => $accions->total(),
            'totalFormatted' => number_format($accions->total()),
            'from' => $accions->firstItem() ?? 0,
            'to' => $accions->lastItem() ?? 0,
        ]);
    }

    public function general(Request $request)
    {
        [$query, $anio, $showFullFilters] = $this->difusionGeneralQuery($request);
        $accions = $this->paginateDifusion($request, $query);

        if ($request->ajax()) {
            return $this->ajaxDifusionResponse($request, $accions);
        }

        return view('difusion.general', [
            'accions' => $accions,
            'anio' => $anio,
            'showFullFilters' => $showFullFilters,
            'listaUgels' => User::whereNotNull('ugel')->where('ugel', '!=', '')->distinct()->orderBy('ugel')->pluck('ugel'),
            'listaAnios' => $this->listaAniosDifusion($anio),
            'filterActionRoute' => 'difusions.view',
            'exportRoute' => 'exportDifusionGeneral',
            'tableId' => 'tabla-difusiones-general',
            'tabs' => $this->tabsDifusion('general'),
        ]);
    }

    public function ugel(Request $request)
    {
        [$query, $anio, $showFullFilters] = $this->difusionGeneralQuery($request, Auth::user()->ugel);
        $accions = $this->paginateDifusion($request, $query);

        if ($request->ajax()) {
            return $this->ajaxDifusionResponse($request, $accions);
        }

        return view('difusion.general', [
            'accions' => $accions,
            'anio' => $anio,
            'showFullFilters' => $showFullFilters,
            'listaUgels' => collect(),
            'listaAnios' => $this->listaAniosDifusion($anio),
            'filterActionRoute' => 'difusions.ugel',
            'exportRoute' => 'exportDifusionUgel',
            'tableId' => 'tabla-difusiones-ugel',
            'tabs' => $this->tabsDifusion('ugel'),
        ]);
    }

    public function director(Request $request)
    {
        [$query, $anio, $showFullFilters] = $this->difusionGeneralQuery($request, null, Auth::user()->institucion);
        $accions = $this->paginateDifusion($request, $query);

        if ($request->ajax()) {
            return $this->ajaxDifusionResponse($request, $accions);
        }

        return view('difusion.general', [
            'accions' => $accions,
            'anio' => $anio,
            'showFullFilters' => $showFullFilters,
            'listaUgels' => collect(),
            'listaAnios' => $this->listaAniosDifusion($anio),
            'filterActionRoute' => 'difusions.director',
            'exportRoute' => 'exportDifusionDirector',
            'tableId' => 'tabla-difusiones-director',
            'tabs' => $this->tabsDifusion('director'),
        ]);
    }

    private function streamDifusionExport($query, string $filenamePrefix)
    {
        $accions = $query->orderBy('pro_accions.fecha', 'desc')->get();

        $filename = $filenamePrefix . '_' . date('Y-m-d') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($accions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            $html .= '<head><meta charset="utf-8">';
            $html .= '<style>
                table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
                th { background-color: #1E40AF; color: #FFFFFF; font-weight: bold; border: 1px solid #D1D5DB; padding: 8px; text-align: left; }
                td { border: 1px solid #E5E7EB; padding: 6px; }
                tr:nth-child(even) td { background-color: #F9FAFB; }
            </style></head><body>';
            $html .= '<table><thead><tr>';
            $html .= '<th>Nombre de la Acción</th><th>Descripción</th><th>Fecha</th><th>Docente</th><th>DNI</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE</th><th>Provincia</th><th>Distrito</th><th>UGEL</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($accions as $accion) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars((string) $accion->nombreAccion, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->descripcion ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars(date('d-m-Y', strtotime($accion->fecha)), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->name ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->dni ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->cargo ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->institucion ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->nivelinstitucion ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->provincia ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->distrito ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->ugel ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table></body></html>';

            fwrite($file, $html);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportDifusionGeneral(Request $request)
    {
        [$query] = $this->difusionGeneralQuery($request);
        return $this->streamDifusionExport($query, 'acciones_difusion_general');
    }

    public function exportDifusionUgel(Request $request)
    {
        [$query] = $this->difusionGeneralQuery($request, Auth::user()->ugel);
        return $this->streamDifusionExport($query, 'acciones_difusion_ugel');
    }

    public function exportDifusionDirector(Request $request)
    {
        [$query] = $this->difusionGeneralQuery($request, null, Auth::user()->institucion);
        return $this->streamDifusionExport($query, 'acciones_difusion_director');
    }

    public function profesorcoordinador()
    {
        $institucion = Auth::user()->institucion;
        $anioActual = request()->get('anio', '2026'); // Por defecto 2025
        
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
        $anio = $request->get('anio', '2026'); // Por defecto 2025

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
                    "pro_accions.descripcion", "pro_accions.fecha", "pro_accions.lugar", "pro_accions.enlace",
                    "users.name", "users.cargo",
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
                    
                    $accions = Accion::select("pro_accions.id","pro_accions.nombreAccion","pro_accions.documento","pro_accions.color","pro_accions.descripcion","pro_accions.fecha","pro_accions.lugar","pro_accions.enlace","users.name","users.cargo","users.nivelinstitucion","users.institucion","users.provincia","users.distrito","users.ugel")
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
        $anio = $request->input('anio', '2026'); // Por defecto 2025
        
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
        $anio = $request->input('anio', '2026'); // Por defecto 2025
        
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
        $anio = $request->input('anio', '2026'); // Por defecto 2025
        
        $cantidadRegistros = Accion::whereYear('fecha', '=', $anio)
                                ->where('tipo', '=', 'difusion')
                                ->where('estado', '=', '1')
                                ->count();

        return $cantidadRegistros;
    }
    
    public function exportarTodos(Request $request)
{
    $dni = trim($request->get('texto', ''));
    $name = trim($request->get('docentes', ''));
    $ugel = trim($request->get('ugels', ''));
    $nominstitucion = trim($request->get('instituciones', ''));
    $anio = $request->get('anio', 2026);

    $query = Accion::select(
        "pro_accions.nombreAccion", "pro_accions.descripcion", 
        "pro_accions.fecha", "users.name", "users.cargo", 
        "users.nivelinstitucion", "users.institucion", 
        "users.provincia", "users.distrito", "users.ugel"
    )
    ->join("users", "users.id", "=", "pro_accions.idUser")
    ->where('pro_accions.estado', '1')
    ->where('pro_accions.tipo', 'difusion')
    ->whereYear('pro_accions.fecha', $anio);

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

    $accions = $query->orderBy('pro_accions.fecha', 'desc')->get();

    // Exportar como Excel (HTML interpretado por Excel)
    $headers = [
        'Content-Type' => 'application/vnd.ms-excel',
        'Content-Disposition' => 'attachment; filename=difusion.xls',
    ];

    $content = '<table border="1">';
    $content .= '<tr><th>Nombre de la Acción</th><th>Descripción</th><th>Fecha</th><th>Usuario</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE.</th><th>Provincia</th><th>Distrito</th><th>UGEL</th></tr>';
    foreach ($accions as $item) {
        $content .= '<tr>';
        $content .= '<td>' . $item->nombreAccion . '</td>';
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


