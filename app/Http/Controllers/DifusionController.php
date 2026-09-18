<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasScopeTabs;
use Illuminate\Http\Request;
use App\Models\Difusion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DifusionController extends Controller
{
    use HasScopeTabs;

    public function __construct(){
        $this->middleware('auth');
        // Difusión tiene su propio set difusions.* (ver DifusionPermissionSeeder)
        $this->middleware('can:difusions.index')->only('index');
        $this->middleware('can:difusions.create')->only('create', 'store');
        $this->middleware('can:difusions.edit')->only('edit', 'update');
        $this->middleware('can:difusions.destroy')->only('destroy');
        $this->middleware('can:difusions.view')->only('general', 'exportDifusionGeneral');
        $this->middleware('can:difusions.ugel')->only('ugel', 'exportDifusionUgel');
        $this->middleware('can:difusions.director')->only('director', 'exportDifusionDirector');
        $this->middleware('can:accions.dre')->only('dre');
        $this->middleware('can:difusions.view')->only('buscarGeneral', 'exportarTodos');
        $this->middleware('can:difusions.index')->only('buscar');
    }

    public function index(Request $request)
    {
        $usuario = Auth::user()->id;

        $accionsQuery = Difusion::with('getUser')
            ->where('estado', '1')
            ->where('idUser', $usuario);

        if ($request->filled('texto')) {
            $accionsQuery->where('nombreAccion', 'LIKE', '%' . $request->input('texto') . '%');
        }

        if ($request->filled('lugar')) {
            $accionsQuery->where('lugar', 'LIKE', '%' . $request->input('lugar') . '%');
        }

        if ($request->filled('fecha')) {
            $accionsQuery->where('fecha', 'LIKE', '%' . $request->input('fecha') . '%');
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $accionsQuery->where(function ($q) use ($buscar) {
                $q->where('nombreAccion', 'LIKE', "%{$buscar}%")
                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                  ->orWhere('lugar', 'LIKE', "%{$buscar}%");
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
     * Punto de entrada del menú. Redirige a la primera pestaña autorizada.
     */
    public function landing()
    {
        $tabs = $this->tabsDifusion('index');
        abort_if(empty($tabs), 403);

        return redirect($tabs[0]['url']);
    }

    /**
     * Consulta base compartida por los tres alcances agregados.
     */
    private function difusionGeneralQuery(Request $request, ?string $forceUgel = null, ?string $forceInstitucion = null): array
    {
        $anio = $request->filled('anio') ? $request->input('anio') : date('Y');

        $query = Difusion::select(
                'pro_difusions.id', 'pro_difusions.nombreAccion', 'pro_difusions.lugar',
                'pro_difusions.descripcion',
                'pro_difusions.fecha', 'pro_difusions.enlace',
                'users.name', 'users.institucion', 'users.provincia', 'users.cargo',
                'users.nivelinstitucion', 'users.distrito', 'users.ugel', 'users.dni'
            )
            ->join('users', 'users.id', '=', 'pro_difusions.idUser')
            ->where('pro_difusions.estado', '1')
            ->whereYear('pro_difusions.fecha', $anio);

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

        if ($request->filled('lugar')) {
            $query->where('pro_difusions.lugar', 'LIKE', '%' . $request->input('lugar') . '%');
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('pro_difusions.nombreAccion', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_difusions.descripcion', 'LIKE', "%{$buscar}%")
                  ->orWhere('pro_difusions.lugar', 'LIKE', "%{$buscar}%");
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

        return $query->orderBy('pro_difusions.fecha', 'desc')->paginate($perPage)->withQueryString();
    }

    private function listaAniosDifusion(string $anio): \Illuminate\Support\Collection
    {
        $listaAnios = Difusion::whereYear('fecha', '>=', 2010)
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
        $accions = $query->orderBy('pro_difusions.fecha', 'desc')->get();

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
            $html .= '<th>Nombre de la Acción</th><th>Lugar</th><th>Descripción</th><th>Fecha</th><th>Docente</th><th>DNI</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE</th><th>Provincia</th><th>Distrito</th><th>UGEL</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($accions as $accion) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars((string) $accion->nombreAccion, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string) ($accion->lugar ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
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
            echo $html;
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
        $anioActual = request()->get('anio', date('Y'));
        
        $accions = Difusion::select("pro_difusions.id","pro_difusions.nombreAccion","pro_difusions.lugar","pro_difusions.enlace","pro_difusions.descripcion","pro_difusions.updated_at","pro_difusions.fecha","users.name","users.institucion","users.provincia","users.distrito","users.ugel")
            ->join("users","users.id","=","pro_difusions.idUser")
            ->where("users.institucion", $institucion)
            ->where('pro_difusions.estado', '1')
            ->orderby('pro_difusions.fecha','desc')
            ->whereYear('fecha', $anioActual)
            ->paginate(10);
            
        return view("difusion.view", compact('accions'));
    }

    public function buscar(Request $request)
    {
        return $this->index($request);
    }

    public function buscarGeneral(Request $request)
    {
        return $this->general($request);
    }

    public function download($id)
    {
        $difusion = Difusion::findOrFail($id);
        $pathToFile = storage_path('app/public/' . $difusion->enlace);

        if (!file_exists($pathToFile)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download($pathToFile);
    }

    public function create()
    {
        return view('difusion.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombreAccion' => 'required|string|max:191',
            'lugar'        => 'required|string|max:191',
            'descripcion'  => 'nullable|string',
            'fecha'        => 'required|date',
            'documento'    => 'required|file|mimes:pdf,doc,docx,xls,xlsx,xlm,xlsm,ppt,pptx,pptm,png,jpg,jpeg|max:10240',
        ], [
            'documento.required' => 'Debe adjuntar un archivo para el registro.',
            'documento.max'      => 'El archivo no debe ser superior a 10MB.',
            'documento.mimes'    => 'El tipo de archivo no es compatible.',
        ]);

        $file = $request->file('documento');
        $extension = $file->getClientOriginalExtension();
        $dateTimeNow = now()->format('Ymd_His_u');
        $fileContent = $request->get('nombreAccion') . ' ' . $dateTimeNow . '.' . $extension;
        $route = 'difusion';

        Storage::makeDirectory('public/' . $route);
        Storage::disk('public')->setVisibility($route, 'public');
        Storage::putFileAs('public/' . $route, $file, $fileContent);

        $difusion = new Difusion;
        $difusion->enlace = $route . '/' . $fileContent;
        $difusion->nombreAccion = $request->get('nombreAccion');
        $difusion->lugar = $request->get('lugar');
        $difusion->descripcion = $request->get('descripcion');
        $difusion->fecha = $request->get('fecha');
        $difusion->idUser = Auth::user()->id;
        $difusion->estado = 1;
        $difusion->save();

        return redirect('/difusions')->with('success', '¡Registro guardado con éxito!');
    }

    public function show()
    {
        return redirect('/difusions');
    }

    public function edit($id)
    {
        $difusion = Difusion::findOrFail($id);
        return view('difusion.edit')->with('difusion', $difusion);
    }

    public function update(Request $request, Difusion $difusion)
    {
        $request->validate([
            'nombreAccion' => 'required|string|max:191',
            'lugar'        => 'required|string|max:191',
            'descripcion'  => 'nullable|string',
            'fecha'        => 'required|date',
            'documento'    => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,xlm,xlsm,ppt,pptx,pptm,png,jpg,jpeg|max:10240',
        ], [
            'documento.max'   => 'El archivo no debe ser superior a 10MB.',
            'documento.mimes' => 'El tipo de archivo no es compatible.',
        ]);

        $difusion->nombreAccion = $request->get('nombreAccion');
        $difusion->lugar = $request->get('lugar');
        $difusion->descripcion = $request->get('descripcion');
        $difusion->fecha = $request->get('fecha');

        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            $extension = $file->getClientOriginalExtension();
            $dateTimeNow = now()->format('Ymd_His_u');
            $fileContent = $request->get('nombreAccion') . ' ' . $dateTimeNow . '.' . $extension;
            $route = 'difusion';

            Storage::makeDirectory('public/' . $route);
            Storage::disk('public')->setVisibility($route, 'public');
            Storage::putFileAs('public/' . $route, $file, $fileContent);

            if ($difusion->enlace && Storage::exists('public/' . $difusion->enlace)) {
                Storage::delete('public/' . $difusion->enlace);
            }

            $difusion->enlace = $route . '/' . $fileContent;
        }

        $difusion->save();

        return redirect('/difusions')->with('success', '¡Registro actualizado con éxito!');
    }

    public function destroy(Difusion $difusion)
    {
        $difusion->estado = 0;
        $difusion->save();

        return redirect('/difusions')->with('success', '¡Registro eliminado con éxito!');
    }

    public function obtenerUgels(Request $request)
    {
        $anio = $request->input('anio', date('Y'));
        
        try {
            $ugels = DB::table('pro_difusions')
                ->select('users.ugel', DB::raw('count(distinct pro_difusions.idUser) as docentes_count'))
                ->join('users', 'pro_difusions.idUser', '=', 'users.id')
                ->where('pro_difusions.estado', '1')
                ->whereYear('pro_difusions.fecha', $anio)
                ->whereRaw("LENGTH(users.ugel) > 0")
                ->groupBy('users.ugel')
                ->get();
            
            return response()->json($ugels);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
        $anio = $request->input('anio', date('Y'));
        
        try {
            $resultados = DB::table('institucions')
                ->leftJoin('users', function($join) {
                    $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                        ->on('institucions.ugel', '=', 'users.ugel');
                })
                ->leftJoin('pro_difusions', function($join) use ($anio) {
                    $join->on('users.id', '=', 'pro_difusions.idUser')
                        ->where('pro_difusions.estado', '=', '1')
                        ->whereYear('pro_difusions.fecha', $anio);
                })
                ->where('institucions.ugel', '=', $ugelSeleccionada)
                ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_difusions.idUser) as agendas_count'))
                ->groupBy('institucions.nomInstitucion')
                ->get();

            $totalDocentes = DB::table('institucions')
                ->leftJoin('users', function($join) {
                    $join->on('institucions.nomInstitucion', '=', 'users.institucion')
                        ->on('institucions.ugel', '=', 'users.ugel');
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
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function buscadorinstitucion(Request $request)
    {   
        $cargo = Auth::user()->cargo;
        $anio = $request->input('anio', date('Y'));
        
        switch ($cargo) {
            case 'Especialista UGEL':
                $ugel = Auth::user()->ugel;
                break;
            case 'Especialista DRE':
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
            ->leftJoin('pro_difusions', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_difusions.idUser')
                    ->where('pro_difusions.estado', '=', '1')
                    ->whereYear('pro_difusions.fecha', '=', $anio);
            })
            ->where('institucions.ugel', '=', $ugel)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_difusions.idUser) as agendas_count'))
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
        $cargo = Auth::user()->cargo;
        $anio = $request->input('anio', date('Y'));
        
        if ($cargo == "Especialista UGEL") {
            $ugelSeleccionada = Auth::user()->ugel;
        } else {
            $ugelSeleccionada = $request->input('ugel');
        }

        $institucionSeleccionada = $request->input('docente');
        
        try {
            $docentes = DB::table('users')
                ->leftJoin('pro_difusions', function($join) use ($anio) {
                    $join->on('users.id', '=', 'pro_difusions.idUser')
                        ->where('pro_difusions.estado', '=', '1')
                        ->whereYear('pro_difusions.fecha', $anio);
                })
                ->where('users.institucion', '=', $institucionSeleccionada)
                ->where('users.ugel', 'like', '%' . $ugelSeleccionada . '%')
                ->select('users.name', DB::raw('count(pro_difusions.id) as agendas_count'))
                ->groupBy('users.name')
                ->having('agendas_count', '>=', 0)
                ->get();
            
            return response()->json($docentes);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function buscadordocente(Request $request)
    {
        $institucion = $request->input('institucion'); 
        $term = $request->input('term');
        $anio = $request->input('anio', date('Y'));
        
        $docentes = DB::table('users')
            ->leftJoin('pro_difusions', function($join) use ($anio) {
                $join->on('users.id', '=', 'pro_difusions.idUser')
                    ->where('pro_difusions.estado', '=', '1')
                    ->whereYear('pro_difusions.fecha', '=', $anio);
            })
            ->where('users.institucion', '=', $institucion)
            ->where('users.name', 'like', '%' . $term . '%')
            ->select('users.name', DB::raw('count(pro_difusions.idUser) as agendas_count'))
            ->groupBy('users.name')
            ->having('agendas_count', '>=', 0) 
            ->get();
        
        return response()->json($docentes);
    }

    public function obtenerCantidadRegistros(Request $request)
    {
        $anio = $request->input('anio', date('Y'));
        
        return Difusion::whereYear('fecha', '=', $anio)
            ->where('estado', '=', '1')
            ->count();
    }
    
    public function exportarTodos(Request $request)
    {
        $dni = trim($request->get('texto', ''));
        $name = trim($request->get('docentes', ''));
        $ugel = trim($request->get('ugels', ''));
        $nominstitucion = trim($request->get('instituciones', ''));
        $anio = $request->get('anio', date('Y'));

        $query = Difusion::select(
            "pro_difusions.nombreAccion", "pro_difusions.lugar", "pro_difusions.descripcion", 
            "pro_difusions.fecha", "users.name", "users.cargo", 
            "users.nivelinstitucion", "users.institucion", 
            "users.provincia", "users.distrito", "users.ugel"
        )
        ->join("users", "users.id", "=", "pro_difusions.idUser")
        ->where('pro_difusions.estado', '1')
        ->whereYear('pro_difusions.fecha', $anio);

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

        $accions = $query->orderBy('pro_difusions.fecha', 'desc')->get();

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=difusion.xls',
        ];

        $content = '<table border="1">';
        $content .= '<tr><th>Nombre de la Acción</th><th>Lugar</th><th>Descripción</th><th>Fecha</th><th>Usuario</th><th>Cargo</th><th>Institución</th><th>Tipo de II.EE.</th><th>Provincia</th><th>Distrito</th><th>UGEL</th></tr>';
        foreach ($accions as $item) {
            $content .= '<tr>';
            $content .= '<td>' . htmlspecialchars((string) $item->nombreAccion, ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '<td>' . htmlspecialchars((string) ($item->lugar ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '<td>' . htmlspecialchars((string) $item->descripcion, ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '<td>' . date('d-m-Y', strtotime($item->fecha)) . '</td>';
            $content .= '<td>' . htmlspecialchars((string) $item->name, ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '<td>' . htmlspecialchars((string) $item->cargo, ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '<td>' . htmlspecialchars((string) $item->institucion, ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '<td>' . htmlspecialchars((string) $item->nivelinstitucion, ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '<td>' . htmlspecialchars((string) $item->provincia, ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '<td>' . htmlspecialchars((string) $item->distrito, ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '<td>' . htmlspecialchars((string) $item->ugel, ENT_QUOTES, 'UTF-8') . '</td>';
            $content .= '</tr>';
        }
        $content .= '</table>';

        return response($content, 200, $headers);
    }
}
