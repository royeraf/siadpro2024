<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasScopeTabs;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SectorController extends Controller
{
    use HasScopeTabs;

    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:sectores.index')->only('index');
        $this->middleware('can:sectores.create')->only('create', 'store');
        $this->middleware('can:sectores.edit')->only('edit', 'update');
        $this->middleware('can:sectores.destroy')->only('destroy');
        $this->middleware('can:sectores.view')->only('general', 'exportSectoresGeneral', 'buscarGeneral');
        $this->middleware('can:sectores.ugel')->only('ugel', 'exportSectoresUgel', 'buscarUgel');
        $this->middleware('can:sectores.director')->only('director', 'buscarDirector', 'exportSectoresDirector');
        // exportarTodos ya no lo usa la vista migrada, pero seguía alcanzable por URL
        // directa sin exigir ningún permiso propio (igual que en Accion/Difusion).
        $this->middleware('can:sectores.view')->only('exportarTodos');
    }

    public function index(Request $request)
    {
        $usuario = Auth::user()->id;

        $sectoresQuery = Sector::where('estado', '1')->where('idUser', $usuario);

        if ($request->filled('texto')) {
            $sectoresQuery->where('nombreSector', 'LIKE', '%' . $request->input('texto') . '%');
        }

        if ($request->filled('fecha')) {
            $sectoresQuery->where('fecha', 'LIKE', '%' . $request->input('fecha') . '%');
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $sectoresQuery->where(function ($q) use ($buscar) {
                $q->where('nombreSector', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        $sectores = $sectoresQuery->orderBy('fecha', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('sector._rows', ['sectores' => $sectores])->render(),
                'pagination' => (string) $sectores->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $sectores->total(),
                'totalFormatted' => number_format($sectores->total()),
                'from' => $sectores->firstItem() ?? 0,
                'to' => $sectores->lastItem() ?? 0,
            ]);
        }

        $tabs = $this->tabsSector('index');

        return view('sector.index', compact('sectores', 'tabs'));
    }

    /**
     * Pestañas de "Sectores del Aula", una por alcance al que el usuario
     * autenticado tenga permiso.
     */
    private function tabsSector(string $activo): array
    {
        return $this->scopeTabs([
            'index'    => ['permission' => 'sectores.index', 'label' => 'Mis registros', 'route' => 'sector.index'],
            'ugel'     => ['permission' => 'sectores.ugel', 'label' => 'UGEL', 'route' => 'sectores.ugel'],
            'general'  => ['permission' => 'sectores.view', 'label' => 'General', 'route' => 'sectores.view'],
            'director' => ['permission' => 'sectores.director', 'label' => 'Director', 'route' => 'sectores.director'],
        ], $activo);
    }

    /**
     * Consulta base compartida por la vista General (sin restricción, para
     * Especialista DRE / administración) y la vista UGEL (siempre acotada a la
     * UGEL del usuario autenticado, vía $forceUgel). Ambas comparten los mismos
     * filtros de año/DNI/docente, y General añade UGEL/Institución libres.
     */
    private function sectoresGeneralQuery(Request $request, ?string $forceUgel = null, ?string $forceInstitucion = null): array
    {
        $anio = $request->filled('year') ? $request->input('year') : date('Y');

        $query = Sector::select(
                'pro_sectores.id', 'pro_sectores.nombreSector', 'pro_sectores.descripcion',
                'pro_sectores.documento', 'pro_sectores.color', 'pro_sectores.fecha',
                'pro_sectores.enlace',
                'users.name', 'users.institucion', 'users.provincia', 'users.cargo',
                'users.nivelinstitucion', 'users.distrito', 'users.ugel', 'users.dni'
            )
            ->join('users', 'users.id', '=', 'pro_sectores.idUser')
            ->where('pro_sectores.estado', '1')
            ->whereYear('pro_sectores.fecha', $anio);

        $showFullFilters = $forceUgel === null && $forceInstitucion === null;

        if ($forceInstitucion !== null) {
            $query->where('users.institucion', $forceInstitucion);
        } elseif ($forceUgel !== null) {
            $query->where('users.ugel', $forceUgel);
        } elseif ($request->filled('ugels')) {
            $query->where('users.ugel', $request->input('ugels'));
        }

        if ($request->filled('instituciones')) {
            $query->where('users.institucion', $request->input('instituciones'));
        }

        if ($request->filled('texto')) {
            $query->where('users.dni', 'LIKE', '%' . $request->input('texto') . '%');
        }

        if ($request->filled('docentes')) {
            $query->where('users.name', 'LIKE', '%' . $request->input('docentes') . '%');
        }

        if ($request->filled('nivel')) {
            $query->where('users.nivelinstitucion', 'LIKE', '%' . $request->input('nivel') . '%');
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_sectores.nombreSector', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_sectores.descripcion', 'LIKE', "%{$buscar}%");
            });
        }

        return [$query, $anio, $showFullFilters];
    }

    private function paginateSectores(Request $request, $query)
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

        return $query->orderBy('pro_sectores.fecha', 'desc')->paginate($perPage)->withQueryString();
    }

    private function listaAniosSectores($anio)
    {
        // Se descarta cualquier año fuera de un rango plausible por la misma razón
        // que en AccionController/DifusionController: fechas mal digitadas ensucian
        // el selector (aquí además hay un año "2222", no solo años truncados).
        $listaAnios = Sector::whereYear('fecha', '>=', 2010)
            ->whereYear('fecha', '<=', (int) date('Y') + 1)
            ->selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');
        if (!$listaAnios->contains($anio)) {
            $listaAnios->prepend($anio);
        }

        return $listaAnios;
    }

    private function ajaxSectoresResponse(Request $request, $sectores)
    {
        return response()->json([
            'rows' => view('sector._rows_general', ['sectores' => $sectores])->render(),
            'pagination' => (string) $sectores->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
            'total' => $sectores->total(),
            'totalFormatted' => number_format($sectores->total()),
            'from' => $sectores->firstItem() ?? 0,
            'to' => $sectores->lastItem() ?? 0,
        ]);
    }

    public function general(Request $request)
    {
        [$query, $anio, $showFullFilters] = $this->sectoresGeneralQuery($request);
        $sectores = $this->paginateSectores($request, $query);

        if ($request->ajax()) {
            return $this->ajaxSectoresResponse($request, $sectores);
        }

        return view('sector.general', [
            'sectores' => $sectores,
            'anio' => $anio,
            'showFullFilters' => $showFullFilters,
            'listaUgels' => User::whereNotNull('ugel')->where('ugel', '!=', '')->distinct()->orderBy('ugel')->pluck('ugel'),
            'listaAnios' => $this->listaAniosSectores($anio),
            'filterActionRoute' => 'sectores.view',
            'exportRoute' => 'exportSectoresGeneral',
            'pageTitle' => 'Sectores del Aula',
            'tableId' => 'tabla-sectores-general',
            'tabs' => $this->tabsSector('general'),
        ]);
    }

    public function ugel(Request $request)
    {
        [$query, $anio, $showFullFilters] = $this->sectoresGeneralQuery($request, Auth::user()->ugel);
        $sectores = $this->paginateSectores($request, $query);

        if ($request->ajax()) {
            return $this->ajaxSectoresResponse($request, $sectores);
        }

        return view('sector.general', [
            'sectores' => $sectores,
            'anio' => $anio,
            'showFullFilters' => $showFullFilters,
            'listaUgels' => collect(),
            'listaInstituciones' => User::where('ugel', Auth::user()->ugel)->whereNotNull('institucion')->where('institucion', '!=', '')->distinct()->orderBy('institucion')->pluck('institucion'),
            'listaAnios' => $this->listaAniosSectores($anio),
            'filterActionRoute' => 'sectores.ugel',
            'exportRoute' => 'exportSectoresUgel',
            'pageTitle' => 'Sectores del Aula',
            'tableId' => 'tabla-sectores-ugel',
            'tabs' => $this->tabsSector('ugel'),
        ]);
    }

    private function streamSectoresExport($query, string $filenamePrefix)
    {
        $sectores = $query->orderBy('pro_sectores.fecha', 'desc')->get();

        $filename = $filenamePrefix . '_' . date('Y-m-d') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($sectores) {
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
            $html .= '<th>Nombre del Sector</th><th>Descripción</th><th>Fecha</th><th>Docente</th><th>DNI</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE</th><th>Provincia</th><th>Distrito</th><th>UGEL</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($sectores as $sector) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars((string) $sector->nombreSector, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($sector->descripcion ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars(date('d-m-Y', strtotime($sector->fecha)), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($sector->name ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($sector->dni ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($sector->cargo ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($sector->institucion ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($sector->nivelinstitucion ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($sector->provincia ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($sector->distrito ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($sector->ugel ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table></body></html>';

            fwrite($file, $html);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportSectoresGeneral(Request $request)
    {
        [$query] = $this->sectoresGeneralQuery($request);
        return $this->streamSectoresExport($query, 'sectores_general');
    }

    public function exportSectoresUgel(Request $request)
    {
        [$query] = $this->sectoresGeneralQuery($request, Auth::user()->ugel);
        return $this->streamSectoresExport($query, 'sectores_ugel');
    }

    public function exportSectoresDirector(Request $request)
    {
        [$query] = $this->sectoresGeneralQuery($request, null, Auth::user()->institucion);
        return $this->streamSectoresExport($query, 'sectores_director');
    }

    public function director(Request $request)
    {
        [$query, $anio, $showFullFilters] = $this->sectoresGeneralQuery($request, null, Auth::user()->institucion);
        $sectores = $this->paginateSectores($request, $query);

        if ($request->ajax()) {
            return $this->ajaxSectoresResponse($request, $sectores);
        }

        return view('sector.general', [
            'sectores' => $sectores,
            'anio' => $anio,
            'showFullFilters' => $showFullFilters,
            'listaUgels' => collect(),
            'listaInstituciones' => collect(),
            'listaAnios' => $this->listaAniosSectores($anio),
            'filterActionRoute' => 'sectores.director',
            'exportRoute' => 'exportSectoresDirector',
            'pageTitle' => 'Sectores del Aula',
            'tableId' => 'tabla-sectores-director',
            'tabs' => $this->tabsSector('director'),
        ]);
    }

    public function profesorcoordinador(Request $request)
    {
        $institucion = Auth::user()->institucion;
        $selectedYear = $request->get('year', 2026);
        
        $sectores = Sector::select("pro_sectores.id","pro_sectores.nombreSector","pro_sectores.documento","pro_sectores.color","pro_sectores.descripcion","pro_sectores.fecha","users.name","users.institucion","users.provincia","users.distrito","users.ugel")
            ->join("users","users.id","=","pro_sectores.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_sectores.estado', '1')
            ->whereYear('pro_sectores.fecha', $selectedYear)
            ->orderby('fecha','desc')
            ->paginate(10);
            
        return view("sector.coordinador", compact('sectores', 'selectedYear'));
    }

    public function download($id)
    {
        $sector = Sector::findOrFail($id);
        $pathToFile = storage_path('app/public/' . $sector->enlace);
        
        // Verificar si el archivo existe antes de descargarlo
        
        return response()->download($pathToFile);
    }
    
    public function buscar(Request $request)
    {
        $usuario = Auth::user()->id;
        $texto = trim($request->get('texto'));
        $fecha = trim($request->get('fecha'));
        $selectedYear = $request->get('year', 2026);
        
        $sectores = Sector::where("nombreSector", "LIKE", "%" . $texto . "%")
            ->where("fecha", "LIKE", "%" . $fecha . "%")
            ->where('estado', '1')
            ->where('idUser', $usuario)
            ->whereYear('fecha', $selectedYear)
            ->orderBy('fecha', 'desc')
            ->paginate(10);
            
        return view('sector.index')->with(['sectores' => $sectores, 'selectedYear' => $selectedYear]);
    }

    public function buscarGeneral(Request $request)
    {
        if (empty($request->get('ugels')) && empty($request->get('instituciones')) && empty($request->get('docentes')) && empty($request->get('texto')) && empty($request->get('year'))) {
            return redirect('/sector-general');
        } else {
            $dni = trim($request->get('texto'));
            $name = trim($request->get('docentes'));
            $ugel = trim($request->get('ugels'));
            $nominstitucion = trim($request->get('instituciones'));
            $selectedYear = $request->get('year', 2026);

            $query = Sector::select(
                "pro_sectores.id", "pro_sectores.nombreSector", "pro_sectores.documento",
                "pro_sectores.color", "pro_sectores.descripcion", "pro_sectores.fecha",
                "pro_sectores.enlace",
                "users.name", "users.cargo", "users.nivelinstitucion", "users.institucion",
                "users.provincia", "users.distrito", "users.ugel", "users.dni"
            )
            ->join("users", "users.id", "=", "pro_sectores.idUser")
            ->where('pro_sectores.estado', '1')
            ->whereYear('pro_sectores.fecha', $selectedYear);

            // Aplicar cada filtro independientemente
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

            $sectores = $query->orderBy('pro_sectores.fecha', 'desc')->paginate(10);

            return view('sector.view')->with(['sectores' => $sectores, 'selectedYear' => $selectedYear]);
        }
    }

    public function buscarUgel(Request $request)
    {
        $ugel = Auth::user()->ugel;
        $dni = trim($request->get('texto'));
        $nivel = trim($request->get('nivel'));
        $nominstitucion = trim($request->get('nombinstitucion'));
        $selectedYear = $request->get('year', 2026);
        
        $sectores = Sector::select("pro_sectores.id", "pro_sectores.nombreSector", "pro_sectores.documento", "pro_sectores.color", "pro_sectores.descripcion", "pro_sectores.fecha", "pro_sectores.enlace", "users.name", "users.institucion", "users.provincia", "users.distrito", "users.nivelinstitucion", "users.cargo", "users.ugel")
            ->join("users", "users.id", "=", "pro_sectores.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_sectores.estado', '1')
            ->where("users.dni", "LIKE", "%" . $dni . "%")
            ->where('users.nivelinstitucion', "LIKE", "%" . $nivel . "%")
            ->where("users.institucion", "LIKE", "%" . $nominstitucion . "%")
            ->whereYear('pro_sectores.fecha', $selectedYear)
            ->orderBy('pro_sectores.fecha', 'desc')
            ->paginate(10);
            
        return view('sector.ugel')->with(['sectores' => $sectores, 'selectedYear' => $selectedYear]);
    }

    public function buscarDirector(Request $request)
    {
        $institucion = Auth::user()->institucion;
        $texto = trim($request->get('texto'));
        $fecha = trim($request->get('fecha'));
        $selectedYear = $request->get('year', 2026);
        
        $sectores = Sector::select("pro_sectores.id", "pro_sectores.nombreSector", "pro_sectores.documento", "pro_sectores.color", "pro_sectores.tiposector", "pro_sectores.updated_at", "pro_sectores.lugar", "pro_sectores.enlace", "users.name", "users.institucion", "users.provincia", "users.distrito", "users.cargo", "users.ugel")
            ->join("users", "users.id", "=", "pro_sectores.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_sectores.estado', '1')
            ->where("pro_sectores.nombreSector", "LIKE", "%" . $texto . "%")
            ->where("pro_sectores.fecha", "LIKE", "%" . $fecha . "%")
            ->whereYear('pro_sectores.fecha', $selectedYear)
            ->orderBy('pro_sectores.fecha', 'desc')
            ->paginate(10);
            
        return view('sector.director')->with(['sectores' => $sectores, 'selectedYear' => $selectedYear]);
    }


    
    public function create()
    {
        return view('sector.create');
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
        $fileContent = $request->get('nombreSector').' '.$dateTimeNow.'.'. $extension;
        $route = 'sector';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la función storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
        
        $sectores = new Sector;
        $sectores->enlace = $route . '/' . $fileContent;
        $sectores->nombreSector = $request->get('nombreSector');
        switch($extension){
            case 'doc':
                $sectores->documento = 'fas fa-file-word';
                $sectores->color = 'blue';
                break;
            case 'docx':
                $sectores->documento = 'fas fa-file-word';
                $sectores->color = 'blue';
                break;
            case 'png':
                $sectores->documento = 'fas fa-file-image';
                $sectores->color = 'darkturquoise';
                break;
            case 'jpg':
                $sectores->documento = 'fas fa-file-image';
                $sectores->color = 'darkturquoise';
                break;
            case 'jpeg':
                $sectores->documento = 'fas fa-file-image';
                $sectores->color = 'darkturquoise';
                break;
            case 'pdf':
                $sectores->documento = 'fas fa-file-pdf';
                $sectores->color = 'red';
                break;
            case 'ppt':
                $sectores->documento = 'fas fa-file-powerpoint';
                $sectores->color = 'orange';
                break;
            case 'pptm':
                $sectores->documento = 'fas fa-file-powerpoint';
                $sectores->color = 'orange';
                break;
            case 'pptx':
                $sectores->documento = 'fas fa-file-powerpoint';
                $sectores->color = 'orange';
                break;
            case 'xlm':
                $sectores->documento = 'fas fa-file-excel';
                $sectores->color = 'green';
                break;
            case 'xls':
                $sectores->documento = 'fas fa-file-excel';
                $sectores->color = 'green';
                break;   
            case 'xlsm':
                $sectores->documento = 'fas fa-file-excel';
                $sectores->color = 'green';
                break;
            case 'xlsx':
                $sectores->documento = 'fas fa-file-excel';
                $sectores->color = 'green';
                break;
        }
        $sectores->descripcion = $request->get('descripcion');
        $sectores->fecha = $request->get('fecha');
        $sectores->idUser = Auth::user()->id;
        $sectores->estado = 1;
        $sectores->save();
        
        return redirect('/sectores')->with('success', '¡Registro guardado con éxito!');
    }

    public function show()
    {
        //
    }

    
    public function edit($id)
    {
        $sector = Sector::findOrFail($id);
        return view('sector.edit')->with('sector', $sector);
    }

    
    public function update(Request $request, Sector $sector)
    {
        $request->validate([
            'documento' => 'required|mimetypes:application/pdf,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10048',
        ]);
        
        $file = $request->file('documento');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $dateTimeNow = now()->format('Ymd_His_u');
        $fileContent = $request->get('nombreSector').' '.$dateTimeNow.'.'. $extension;
        $route = 'sector';
        
        // Asegurarse de que la carpeta existe y tiene los permisos correctos
        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        
        // Almacenar el archivo con la función storeAs()
        Storage::putFileAs('public/' . $route, $file, $fileContent);
         // Eliminar el archivo antiguo
        Storage::delete('public/'.$sector->enlace);

        $sector->enlace = $route . '/' . $fileContent;
        $sector->nombreSector = $request->get('nombreSector');
        switch($extension){
            case 'doc':
                $sector->documento = 'fas fa-file-word';
                $sector->color = 'blue';
                break;
            case 'docx':
                $sector->documento = 'fas fa-file-word';
                $sector->color = 'blue';
                break;
            case 'png':
                $sector->documento = 'fas fa-file-image';
                $sector->color = 'darkturquoise';
                break;
            case 'jpg':
                $sector->documento = 'fas fa-file-image';
                $sector->color = 'darkturquoise';
                break;
            case 'jpeg':
                $sector->documento = 'fas fa-file-image';
                $sector->color = 'darkturquoise';
                break;
            case 'pdf':
                $sector->documento = 'fas fa-file-pdf';
                $sector->color = 'red';
                break;
            case 'ppt':
                $sector->documento = 'fas fa-file-powerpoint';
                $sector->color = 'orange';
                break;
            case 'pptm':
                $sector->documento = 'fas fa-file-powerpoint';
                $sector->color = 'orange';
                break;
            case 'pptx':
                $sector->documento = 'fas fa-file-powerpoint';
                $sector->color = 'orange';
                break;
            case 'xlm':
                $sector->documento = 'fas fa-file-excel';
                $sector->color = 'green';
                break;
            case 'xls':
                $sector->documento = 'fas fa-file-excel';
                $sector->color = 'green';
                break;   
            case 'xlsm':
                $sector->documento = 'fas fa-file-excel';
                $sector->color = 'green';
                break;
            case 'xlsx':
                $sector->documento = 'fas fa-file-excel';
                $sector->color = 'green';
                break;
        }
        $sector->descripcion = $request->get('descripcion');
        $sector->fecha = $request->get('fecha');
        $sector->idUser = Auth::user()->id;
        $sector->estado = 1;
        $sector->save();
        
        return redirect('/sectores');
    }

   
   /*
    public function destroy(Storage $sector)
    {
    
        Storage::delete('public/'.$sector->enlace);
        $sector->estado = 0;
        $sector->idUser = Auth::user()->id;
        $sector->save();
        return redirect('/sectores');
    }

*/

    public function destroy($id)
    {
        $sector = Sector::findOrFail($id);
        Storage::delete('public/' . $sector->enlace);
        $sector->estado = 0;
        $sector->idUser = Auth::user()->id;
        $sector->save();
        session()->flash('success', '¡Registro eliminado!');
        return redirect('/sector');
    }

    public function buscador(Request $request)
    {
        //dd($request);
        /*
        $sector = Sector::where("nombre",'like',$request->texto."%")->take(10)->get;
        return view("sectores.paginas", compact("sector"));*/
        
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
        $selectedYear = $request->get('year', 2026);
            
        $ugels = DB::table('pro_sectores')
            ->select('users.ugel', DB::raw('count(distinct pro_sectores.idUser) as docentes_count'))
            ->join('users', 'pro_sectores.idUser', '=', 'users.id')
            ->where('pro_sectores.estado', '1')
            ->whereRaw("LENGTH(users.ugel) > 0")
            ->whereYear('pro_sectores.fecha', $selectedYear)
            ->groupBy('users.ugel')
            ->get();

        return response()->json($ugels);
    }

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
        $selectedYear = $request->get('year', 2026);
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_sectores', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_sectores.idUser')
                    ->where('pro_sectores.estado', '=', '1')
                    ->whereYear('pro_sectores.fecha', $selectedYear);
            })
            ->where('institucions.ugel', '=', $ugelSeleccionada)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_sectores.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', function($join) {
                $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                    ->on('institucions.ugel', '=', 'users.ugel');
            })
            ->leftJoin('pro_sectores', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_sectores.idUser')
                    ->where('pro_sectores.estado', '=', '1')
                    ->whereYear('pro_sectores.fecha', $selectedYear);
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
        $selectedYear = $request->get('year', 2026);
        
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
            ->leftJoin('pro_sectores', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_sectores.idUser')
                    ->where('pro_sectores.estado', '=', '1')
                    ->whereYear('pro_sectores.fecha', $selectedYear);
            })
            ->where('institucions.ugel', '=', $ugel)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_sectores.idUser) as agendas_count'))
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
        // Agrega esto para depuración
        \Log::info('Parámetros recibidos en buscarDocenteporInstitucion:', $request->all());
        
        $selectedYear = $request->get('year', 2026);
        $cargo = Auth::user()->cargo;
        
        // Corrige el operador de asignación a comparación
        if ($cargo == "Especialista UGEL") {
            $ugelSeleccionada = Auth::user()->ugel;
        } else {         
            // Acepta ambos nombres de parámetros para mayor compatibilidad
            $ugelSeleccionada = $request->input('ugel', $request->input('ugels', ''));
        }

        // Este es el nombre de la institución seleccionada
        $institucionSeleccionada = $request->input('docente');
        
        \Log::info('Buscando docentes para: ', [
            'institucion' => $institucionSeleccionada, 
            'ugel' => $ugelSeleccionada,
            'year' => $selectedYear
        ]);

        // Realiza la consulta de docentes
        $docentes = DB::table('users')
            ->leftJoin('pro_sectores', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_sectores.idUser')
                    ->where('pro_sectores.estado', '=', '1')
                    ->whereYear('pro_sectores.fecha', $selectedYear);
            })
            ->where('users.institucion', '=', $institucionSeleccionada)
            // Condicional para aplicar el filtro por UGEL solo si está presente
            ->when($ugelSeleccionada, function($query) use ($ugelSeleccionada) {
                return $query->where('users.ugel', 'like', '%' . $ugelSeleccionada . '%');
            })
            ->select('users.name', DB::raw('count(pro_sectores.id) as agendas_count'))
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
        $selectedYear = $request->get('year', 2026);
        
        $docentes = DB::table('users')
            ->leftJoin('pro_sectores', function($join) use ($selectedYear) {
                $join->on('users.id', '=', 'pro_sectores.idUser')
                    ->where('pro_sectores.estado', '=', '1')
                    ->whereYear('pro_sectores.fecha', $selectedYear);
            })
            ->where('users.institucion', '=', $institucion)
            ->where('users.name', 'like', '%' . $term . '%')
            ->select('users.name', DB::raw('count(pro_sectores.idUser) as agendas_count'))
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
        $selectedYear = $request->get('year', 2026);

        // Construir la misma consulta pero sin paginación
        $query = Sector::select(
            "pro_sectores.nombreSector", "pro_sectores.descripcion", 
            "pro_sectores.fecha", "users.name", "users.cargo", 
            "users.nivelinstitucion", "users.institucion", 
            "users.provincia", "users.distrito", "users.ugel"
        )
        ->join("users", "users.id", "=", "pro_sectores.idUser")
        ->where('pro_sectores.estado', '1')
        ->whereYear('pro_sectores.fecha', $selectedYear);

        // Aplicar filtros
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
        $sectores = $query->orderBy('pro_sectores.fecha', 'desc')->get();

        // Determinar formato
        $format = $request->get('format', 'excel');
        
        // Exportar en el formato correspondiente
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($sectores);
            case 'csv':
                return $this->exportToCsv($sectores);
            default:
                return $this->exportToExcel($sectores);
        }
    }

    private function exportToExcel($sectores)
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=sectores.xls',
        ];

        $content = '<table border="1">';
        $content .= '<tr><th>Nombre del Sector</th><th>Descripción</th><th>Fecha</th><th>Usuario</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE.</th><th>Provincia</th><th>Distrito</th><th>UGEL</th></tr>';
        
        foreach ($sectores as $item) {
            $content .= '<tr>';
            $content .= '<td>' . $item->nombreSector . '</td>';
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

    private function exportToCsv($sectores)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=sectores.csv',
        ];

        $callback = function() use ($sectores) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM para Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeceras
            fputcsv($file, [
                'Nombre del Sector', 
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
            foreach ($sectores as $item) {
                fputcsv($file, [
                    $item->nombreSector,
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

