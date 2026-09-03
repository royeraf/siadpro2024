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

class AccionController extends Controller
{
    use HasScopeTabs;

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:accions.index')->only('index');
        $this->middleware('can:accions.create')->only('create', 'store');
        $this->middleware('can:accions.edit')->only('edit', 'update');
        $this->middleware('can:accions.destroy')->only('destroy');
        $this->middleware('can:accions.view')->only('general', 'exportAccionsGeneral');
        $this->middleware('can:accions.ugel')->only('ugel', 'exportAccionsUgel');
        $this->middleware('can:accions.director')->only('director', 'exportAccionsDirector');
        $this->middleware('can:accions.dre')->only('dre');
        // Igual que en DifusionController: buscarGeneral/exportarFiltradoTotal ya no los usa
        // la vista migrada, pero seguían alcanzables por URL directa sin control de acceso
        // propio más allá del "auth" genérico.
        $this->middleware('can:accions.view')->only('buscarGeneral', 'exportarFiltradoTotal');
        // buscar() (ruta /buscar-accion) tampoco tenía permiso propio.
        $this->middleware('can:accions.index')->only('buscar');
    }
    
    public function index(Request $request)
    {
        $usuario = Auth::user()->id;

        $accionsQuery = Accion::where('estado', '1')
            ->where('idUser', $usuario)
            ->where('tipo', 'sensibilizacion');

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
                  ->orWhere('lugar', 'LIKE', "%{$buscar}%");
            });
        }

        $accions = $accionsQuery->orderBy('fecha', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('accion._rows', ['accions' => $accions])->render(),
                'pagination' => (string) $accions->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $accions->total(),
                'totalFormatted' => number_format($accions->total()),
                'from' => $accions->firstItem() ?? 0,
                'to' => $accions->lastItem() ?? 0,
            ]);
        }

        $tabs = $this->tabsAccion('index');

        return view('accion.index', compact('accions', 'tabs'));
    }

    /**
     * Pestañas de la sección "Acción de Sensibilización", una por alcance al
     * que el usuario autenticado tenga permiso.
     */
    private function tabsAccion(string $activo): array
    {
        return $this->scopeTabs([
            'index'    => ['permission' => 'accions.index', 'label' => 'Mis registros', 'route' => 'accions.index'],
            'ugel'     => ['permission' => 'accions.ugel', 'label' => 'UGEL', 'route' => 'accions.ugel'],
            'general'  => ['permission' => 'accions.view', 'label' => 'General', 'route' => 'accions.view'],
            'director' => ['permission' => 'accions.director', 'label' => 'Director', 'route' => 'accions.director'],
        ], $activo);
    }

    /**
     * Punto de entrada del menú. accions.index (Mis registros) no incluye a
     * EspecDRE/EspecUGEL/Director — solo tienen .view/.ugel/.director
     * respectivamente — así que la entrada de menú no puede apuntar fijo a
     * /accions o esos roles se quedan sin poder llegar a nada. Redirige a la
     * primera pestaña a la que el usuario realmente tenga acceso.
     */
    public function landing()
    {
        $tabs = $this->tabsAccion('index');
        abort_if(empty($tabs), 403);

        return redirect($tabs[0]['url']);
    }

    /**
     * Consulta base compartida por los tres alcances agregados. Sin parámetros
     * devuelve todo (alcance General); $forceUgel/$forceInstitucion acotan el
     * resultado a una UGEL o institución concreta (alcances UGEL/Director). El
     * alcance real lo decide el permiso que habilitó la ruta, no el cargo del
     * usuario — antes este método leía `users.cargo` para autolimitarse, lo que
     * podía divergir del rol real de Spatie.
     */
    private function accionsGeneralQuery(Request $request, ?string $forceUgel = null, ?string $forceInstitucion = null): array
    {
        $anio = $request->filled('anio') ? $request->input('anio') : date('Y');

        $query = Accion::select(
                'pro_accions.id', 'pro_accions.nombreAccion', 'pro_accions.descripcion',
                'pro_accions.documento', 'pro_accions.color', 'pro_accions.fecha', 'pro_accions.lugar',
                'pro_accions.enlace',
                'users.name', 'users.institucion', 'users.provincia', 'users.cargo',
                'users.nivelinstitucion', 'users.distrito', 'users.ugel', 'users.dni'
            )
            ->join('users', 'users.id', '=', 'pro_accions.idUser')
            ->where('pro_accions.estado', '1')
            ->where('pro_accions.tipo', 'sensibilizacion')
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
                  ->orWhere('pro_accions.lugar', 'LIKE', "%{$buscar}%");
            });
        }

        return [$query, $anio, $showFullFilters];
    }

    private function paginateAccions(Request $request, $query)
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

    private function listaAniosAccion(string $anio): \Illuminate\Support\Collection
    {
        // Se descartan años fuera de un rango plausible: hay registros antiguos con
        // la fecha mal digitada (p. ej. "0023-08-14" en vez de "2023-08-14") que
        // ensuciarían el selector con años como 23, 203 o 1978.
        $listaAnios = Accion::where('tipo', 'sensibilizacion')
            ->whereYear('fecha', '>=', 2010)
            ->selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');
        if (!$listaAnios->contains($anio)) {
            $listaAnios->prepend($anio);
        }

        return $listaAnios;
    }

    private function ajaxAccionsResponse(Request $request, $accions)
    {
        return response()->json([
            'rows' => view('accion._rows_general', ['accions' => $accions])->render(),
            'pagination' => (string) $accions->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
            'total' => $accions->total(),
            'totalFormatted' => number_format($accions->total()),
            'from' => $accions->firstItem() ?? 0,
            'to' => $accions->lastItem() ?? 0,
        ]);
    }

    public function general(Request $request)
    {
        [$query, $anio, $showFullFilters] = $this->accionsGeneralQuery($request);
        $accions = $this->paginateAccions($request, $query);

        if ($request->ajax()) {
            return $this->ajaxAccionsResponse($request, $accions);
        }

        return view('accion.general', [
            'accions' => $accions,
            'anio' => $anio,
            'showFullFilters' => $showFullFilters,
            'listaUgels' => User::whereNotNull('ugel')->where('ugel', '!=', '')->distinct()->orderBy('ugel')->pluck('ugel'),
            'listaAnios' => $this->listaAniosAccion($anio),
            'filterActionRoute' => 'accions.view',
            'exportRoute' => 'exportAccionsGeneral',
            'tableId' => 'tabla-acciones-general',
            'tabs' => $this->tabsAccion('general'),
        ]);
    }

    public function ugel(Request $request)
    {
        [$query, $anio, $showFullFilters] = $this->accionsGeneralQuery($request, Auth::user()->ugel);
        $accions = $this->paginateAccions($request, $query);

        if ($request->ajax()) {
            return $this->ajaxAccionsResponse($request, $accions);
        }

        return view('accion.general', [
            'accions' => $accions,
            'anio' => $anio,
            'showFullFilters' => $showFullFilters,
            'listaUgels' => collect(),
            'listaAnios' => $this->listaAniosAccion($anio),
            'filterActionRoute' => 'accions.ugel',
            'exportRoute' => 'exportAccionsUgel',
            'tableId' => 'tabla-acciones-ugel',
            'tabs' => $this->tabsAccion('ugel'),
        ]);
    }

    public function director(Request $request)
    {
        [$query, $anio, $showFullFilters] = $this->accionsGeneralQuery($request, null, Auth::user()->institucion);
        $accions = $this->paginateAccions($request, $query);

        if ($request->ajax()) {
            return $this->ajaxAccionsResponse($request, $accions);
        }

        return view('accion.general', [
            'accions' => $accions,
            'anio' => $anio,
            'showFullFilters' => $showFullFilters,
            'listaUgels' => collect(),
            'listaAnios' => $this->listaAniosAccion($anio),
            'filterActionRoute' => 'accions.director',
            'exportRoute' => 'exportAccionsDirector',
            'tableId' => 'tabla-acciones-director',
            'tabs' => $this->tabsAccion('director'),
        ]);
    }

    private function streamAccionesExport($query, string $filenamePrefix)
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
            $html .= '<th>Nombre de la Acción</th><th>Descripción</th><th>Lugar</th><th>Fecha</th><th>Docente</th><th>DNI</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE</th><th>Provincia</th><th>Distrito</th><th>UGEL</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($accions as $accion) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars((string) $accion->nombreAccion, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->descripcion ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) $accion->lugar, ENT_QUOTES, 'UTF-8') . '</td>';
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

    public function exportAccionsGeneral(Request $request)
    {
        [$query] = $this->accionsGeneralQuery($request);
        return $this->streamAccionesExport($query, 'acciones_sensibilizacion_general');
    }

    public function exportAccionsUgel(Request $request)
    {
        [$query] = $this->accionsGeneralQuery($request, Auth::user()->ugel);
        return $this->streamAccionesExport($query, 'acciones_sensibilizacion_ugel');
    }

    public function exportAccionsDirector(Request $request)
    {
        [$query] = $this->accionsGeneralQuery($request, null, Auth::user()->institucion);
        return $this->streamAccionesExport($query, 'acciones_sensibilizacion_director');
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
                           "pro_accions.enlace",
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
                               "pro_accions.enlace",
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
                               "pro_accions.enlace",
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

