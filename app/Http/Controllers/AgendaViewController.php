<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Agenda;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AgendaViewController extends Controller
{
    public function __construct(){
        //$this->middleware('auth');
        //$this->middleware('can:agendas.view')->only('index');
    }
    public function index()
    {
        $institucion = Auth::user()->institucion;
        $events = Agenda::all()
        ->where("institucion", $institucion)
       
        ->where('estado', '1')
        ;
        
        return $events;
        
        
        return view('agenda.view',compact('events'));

    }

    public function ugel()
    {
        $ugel = Auth::user()->ugel;
        $year = request()->get('year', '2025'); // Por defecto, año 2025
        
        $agendas = Agenda::select("pro_agendas.id","pro_agendas.nomDocente","pro_agendas.title","pro_agendas.evento","pro_agendas.start","pro_agendas.end","pro_agendas.institucion","users.provincia","users.distrito","users.ugel")
            ->join("users","users.id","=","pro_agendas.idUser")
            ->where("users.ugel", $ugel)
            ->where('pro_agendas.estado', '1')
            ->whereYear('start', $year)
            ->whereYear('end', $year)
            ->orderby('pro_agendas.start','desc')
            ->paginate(10);
            
        return view("agenda.ugel")
            ->with('agendas', $agendas)
            ->with('selectedYear', $year);
    }

    public function buscarUgel(Request $request)
    {
        if (empty($request->get('instituciones')) && empty($request->get('docentes')) && empty($request->get('year'))) {
            return redirect('/agenda-ugel');
        } else {
            $ugel = Auth::user()->ugel;
            $instituciones = trim($request->get('instituciones'));
            $docentes = trim($request->get('docentes'));
            $year = trim($request->get('year', '2025')); // Por defecto, año 2025

            if (empty($request->get('docentes'))) {
                $agendas = Agenda::select("pro_agendas.id","pro_agendas.nomDocente","pro_agendas.title","pro_agendas.evento","pro_agendas.start","pro_agendas.end","pro_agendas.institucion","users.provincia","users.distrito","users.ugel")
                ->join("users","users.id","=","pro_agendas.idUser")
                ->where("pro_agendas.institucion", "=", $instituciones)
                ->where("users.ugel", "=", $ugel)
                ->whereYear('start', $year)
                ->whereYear('end', $year)
                ->where('pro_agendas.estado', '1')
                ->orderBy('pro_agendas.id', 'desc')
                ->paginate(10);
            } else {
                $agendas = Agenda::select("pro_agendas.id","pro_agendas.nomDocente","pro_agendas.title","pro_agendas.evento","pro_agendas.start","pro_agendas.end","pro_agendas.institucion","users.provincia","users.distrito","users.ugel")
                ->join("users","users.id","=","pro_agendas.idUser")
                ->where("pro_agendas.institucion", "=", $instituciones)
                ->where("users.name", "=", $docentes)
                ->whereYear('start', $year)
                ->whereYear('end', $year)
                ->where("users.ugel", "=", $ugel)
                ->where('pro_agendas.estado', '1')
                ->orderBy('pro_agendas.id', 'desc')
                ->paginate(10);
            }
            
            // Mantener los parámetros en la paginación
            $agendas->appends($request->all());
            
            return view('agenda.ugel')
                ->with('agendas', $agendas)
                ->with('selectedYear', $year)
                ->with('selectedInstitucion', $instituciones)
                ->with('selectedDocente', $docentes);
        }                
    }

    public function general()
    {
        // Por defecto, mostrar datos del año 2025
        $year = request()->get('year', '2025');
        
        $agendas = Agenda::select("pro_agendas.id","pro_agendas.nomDocente","pro_agendas.title","pro_agendas.evento","pro_agendas.start","pro_agendas.end","pro_agendas.institucion","users.provincia","users.distrito","users.ugel")
        ->join("users","users.id","=","pro_agendas.idUser")
        ->where('pro_agendas.estado', '1')
        ->whereYear('start', $year)
        ->whereYear('end', $year)
        ->orderBy('pro_agendas.start','desc')
        ->paginate(10);
        
        return view('agenda.general')->with('agendas', $agendas)->with('selectedYear', $year);
    }

    public function buscarGeneral(Request $request)
    {
        // Variables de los filtros
        $ugel = trim($request->get('ugels'));
        $instituciones = trim($request->get('instituciones'));
        $docentes = trim($request->get('docentes'));
        $nivel = trim($request->get('nivel')); 
        $year = trim($request->get('year', '2025')); // Año por defecto 2025

        // Si no se envía ningún filtro, redirige a la página principal
        if (empty($ugel) && empty($instituciones) && 
            empty($docentes) && empty($nivel) && 
            empty($year)) {
            return redirect('/agenda-general');
        }

        // Construcción dinámica de la consulta
        $query = Agenda::select(
            "pro_agendas.id", "pro_agendas.nomDocente", "pro_agendas.title", 
            "pro_agendas.evento", "pro_agendas.start", "pro_agendas.end", 
            "pro_agendas.institucion", "users.provincia", "users.distrito", 
            "users.ugel", "users.nivelinstitucion"
        )
        ->join("users", "users.id", "=", "pro_agendas.idUser")
        ->where('pro_agendas.estado', '1') // Solo agendas activas
        ->whereYear('pro_agendas.start', $year) // Filtrar por año seleccionado
        ->whereYear('pro_agendas.end', $year);  // Filtrar por año seleccionado

        // Aplicar filtros según los valores recibidos
        if (!empty($ugel)) {
            $query->where("users.ugel", $ugel);
        }

        if (!empty($instituciones)) {
            $query->where("pro_agendas.institucion", $instituciones);
        }

        if (!empty($docentes)) {
            $query->where("users.name", $docentes);
        }
        
        if (!empty($nivel)) {
            $query->where("users.nivelinstitucion", $nivel);
        }

        // Obtener resultados paginados
        $agendas = $query->orderBy('pro_agendas.id', 'desc')->paginate(1000);
        
        // Mantener los parámetros en la paginación
        $agendas->appends($request->all());

        // Retornar la vista con los resultados y todos los valores seleccionados
        return view('agenda.general')
            ->with('agendas', $agendas)
            ->with('selectedYear', $year)
            ->with('selectedUgel', $ugel)
            ->with('selectedInstitucion', $instituciones)
            ->with('selectedDocente', $docentes)
            ->with('selectedNivel', $nivel);
    }



    public function obtenerUgels(Request $request)
    {        
        $year = $request->get('year', '2025'); // Parámetro de año, defecto 2025
        
        $ugels = DB::table('institucions')
            ->select('institucions.ugel', DB::raw('count(distinct pro_agendas.idUser) as docentes_count'))
            ->join('users', 'institucions.nomInstitucion', '=', 'users.institucion')
            ->join('pro_agendas', 'users.id', '=', 'pro_agendas.idUser')
            ->where('pro_agendas.estado', '1')
            ->whereYear('start', $year)
            ->whereYear('end', $year)
            ->groupBy('institucions.ugel')
            ->get();

        return response()->json($ugels);
    }
    public function obtenerInstitucions(Request $request)
    {        
        $ugel = Auth::user()->ugel;
        $year = $request->get('year', '2025'); // Parámetro de año, defecto 2025
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', 'institucions.nomInstitucion', '=', 'users.institucion')
            ->leftJoin('pro_agendas', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_agendas.idUser')
                    ->whereYear('pro_agendas.start', $year)
                    ->whereYear('pro_agendas.end', $year)
                    ->where('pro_agendas.estado', '=', '1');
            })
            ->where('institucions.ugel', '=', $ugel)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_agendas.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        // Calcular la cantidad total de docentes por institución
        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', 'institucions.nomInstitucion', '=', 'users.institucion')
            ->where('institucions.ugel', '=', $ugel)
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

    public function buscarInstitucionporUgel(Request $request)
    {
        $ugelSeleccionada = $request->input('ugel');
        $year = $request->input('year', '2025'); // Parámetro de año, defecto 2025
        
        $resultados = DB::table('institucions')
            ->leftJoin('users', 'institucions.nomInstitucion', '=', 'users.institucion')
            ->leftJoin('pro_agendas', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_agendas.idUser')
                    ->whereYear('pro_agendas.start', $year)
                    ->whereYear('pro_agendas.end', $year);
            })
            ->where('institucions.ugel', '=', $ugelSeleccionada)
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_agendas.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', 'institucions.nomInstitucion', '=', 'users.institucion')
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
        
        $term = $request->input('term'); // Obtén el término de búsqueda del formulario
        $year = $request->input('year', '2025'); // Obtener año, defecto 2025

        // Realiza una consulta para buscar instituciones que coincidan con $term y tengan información sobre docentes y agendas
        $resultados = DB::table('institucions')
            ->leftJoin('users', 'institucions.nomInstitucion', '=', 'users.institucion')
            ->leftJoin('pro_agendas', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_agendas.idUser')
                    ->whereYear('pro_agendas.start', $year)
                    ->whereYear('pro_agendas.end', $year);
            })
            ->where('institucions.ugel', '=', $ugel)
            ->where('institucions.nomInstitucion', 'like', '%' . $term . '%')
            ->select('institucions.nomInstitucion', DB::raw('count(distinct pro_agendas.idUser) as agendas_count'))
            ->groupBy('institucions.nomInstitucion')
            ->get();

        $totalDocentes = DB::table('institucions')
            ->leftJoin('users', 'institucions.nomInstitucion', '=', 'users.institucion')
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
        \Log::info('Parámetros recibidos en buscarDocenteporInstitucion:', $request->all());
        
        $cargo = Auth::user()->cargo;
        // Determinar la UGEL seleccionada
        if ($cargo == "Especialista UGEL") {
            $ugelSeleccionada = Auth::user()->ugel;
        } else {
            $ugelSeleccionada = $request->input('ugel', $request->input('ugels', ''));
        }

        $institucionSeleccionada = $request->input('docente');
        $year = $request->input('year', '2025'); // Parámetro de año, defecto 2025
        
        \Log::info('Buscando docentes para institución y UGEL:', [
            'institucion' => $institucionSeleccionada, 
            'ugel' => $ugelSeleccionada,
            'year' => $year
        ]);

        try {
            // Consulta a la base de datos
            $query = DB::table('users')
                ->leftJoin('pro_agendas', function($join) use ($year) {
                    $join->on('users.id', '=', 'pro_agendas.idUser')
                        ->where('pro_agendas.estado', '=', '1')
                        ->whereYear('pro_agendas.start', $year)
                        ->whereYear('pro_agendas.end', $year);
                })
                ->where('users.institucion', '=', $institucionSeleccionada)
                ->where('users.estado', '=', '1');  // Solo usuarios activos
                
            // Aplicar filtro por UGEL si está presente
            if (!empty($ugelSeleccionada)) {
                $query->where('users.ugel', '=', $ugelSeleccionada);
            }
            
            // Agrupar por nombre de usuario y contar agendas
            $docentes = $query->select('users.name', DB::raw('count(pro_agendas.id) as agendas_count'))
                ->groupBy('users.name')
                ->get();
            
            \Log::info('Docentes encontrados:', ['count' => $docentes->count(), 'data' => $docentes]);
            
            return response()->json($docentes);
        } catch (\Exception $e) {
            \Log::error('Error al buscar docentes:', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function buscadordocente(Request $request)
    {
        $institucion = $request->input('institucion'); 
        $term = $request->input('term'); // Obtén el término de búsqueda del formulario
        $year = $request->input('year', '2025'); // Obtener año, defecto 2025

        $docentes = DB::table('users')
            ->leftJoin('pro_agendas', function($join) use ($year) {
                $join->on('users.id', '=', 'pro_agendas.idUser')
                    ->whereYear('pro_agendas.start', $year)
                    ->whereYear('pro_agendas.end', $year);
            })
            ->where('users.institucion', '=', $institucion)
            ->where('users.name', 'like', '%' . $term . '%')
            ->select('users.name', DB::raw('count(pro_agendas.idUser) as agendas_count'))
            ->groupBy('users.name')
            ->having('agendas_count', '>', 1) // Filtra solo docentes con al menos 1 agenda
            ->get();
        
        // Simplificado para la respuesta final
        $docentes = DB::table('users')
            ->where('name', 'like', '%' . $term . '%')
            ->where('institucion', 'like', '%' . $institucion . '%')
            ->pluck('name');

        return response()->json($docentes);
    }

    public function exportarTodos(Request $request)
    {
        // Obtener parámetros de filtro
        $ugel = trim($request->get('ugels', ''));
        $instituciones = trim($request->get('instituciones', ''));
        $docentes = trim($request->get('docentes', ''));
        $nivel = trim($request->get('nivel', ''));
        $year = trim($request->get('year', '2025')); // Añadir año, defecto 2025

        // Construir la consulta
        $query = Agenda::select(
            "pro_agendas.nomDocente", "pro_agendas.title", "pro_agendas.evento",
            "pro_agendas.start", "pro_agendas.end", "pro_agendas.institucion", 
            "users.provincia", "users.distrito", "users.ugel", "users.nivelinstitucion"
        )
        ->join("users", "users.id", "=", "pro_agendas.idUser")
        ->where('pro_agendas.estado', '1')
        ->whereYear('pro_agendas.start', $year)
        ->whereYear('pro_agendas.end', $year);

        // Aplicar los filtros
        if (!empty($ugel)) {
            $query->where("users.ugel", $ugel);
        }
        
        if (!empty($instituciones)) {
            $query->where("pro_agendas.institucion", $instituciones);
        }
        
        if (!empty($docentes)) {
            $query->where("users.name", $docentes);
        }
        
        // Aplicar filtro por nivel
        if (!empty($nivel)) {
            $query->where("users.nivelinstitucion", $nivel);
        }

        // Obtener TODOS los resultados (sin paginar)
        $agendas = $query->orderBy('pro_agendas.start', 'desc')->get();

        // Determinar formato
        $format = $request->get('format', 'excel');
        
        // Exportar en el formato correspondiente
        switch ($format) {
            case 'excel':
                return $this->exportToExcel($agendas);
            case 'csv':
                return $this->exportToCsv($agendas);
            default:
                return $this->exportToExcel($agendas);
        }
    }

    private function exportToExcel($agendas)
    {
        // Configurar cabeceras para Excel
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=agendas.xls',
        ];

        // Generar contenido HTML que Excel puede interpretar
        $content = '<table border="1">';
        $content .= '<tr><th>Docente</th><th>Nombre de la agenda</th><th>Descripción</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Institución</th><th>Nivel</th><th>Provincia</th><th>Distrito</th><th>UGEL</th></tr>';
        
        foreach ($agendas as $item) {
            $content .= '<tr>';
            $content .= '<td>' . $item->nomDocente . '</td>';
            $content .= '<td>' . $item->title . '</td>';
            $content .= '<td>' . $item->evento . '</td>';
            $content .= '<td>' . date('d-m-Y', strtotime($item->start)) . '</td>';
            $content .= '<td>' . date('d-m-Y', strtotime($item->end)) . '</td>';
            $content .= '<td>' . $item->institucion . '</td>';
            $content .= '<td>' . $item->nivelinstitucion . '</td>'; // Incluir nivel
            $content .= '<td>' . $item->provincia . '</td>';
            $content .= '<td>' . $item->distrito . '</td>';
            $content .= '<td>' . $item->ugel . '</td>';
            $content .= '</tr>';
        }
        
        $content .= '</table>';
        
        return response($content, 200, $headers);
    }

    private function exportToCsv($agendas)
    {
        // Configurar cabeceras para CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=agendas.csv',
        ];

        $callback = function() use ($agendas) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM para Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeceras
            fputcsv($file, [
                'Docente', 
                'Nombre de la agenda', 
                'Descripción', 
                'Fecha Inicio', 
                'Fecha Fin', 
                'Institución', 
                'Nivel',  // Incluir nivel
                'Provincia', 
                'Distrito', 
                'UGEL'
            ]);
            
            // Datos
            foreach ($agendas as $item) {
                fputcsv($file, [
                    $item->nomDocente,
                    $item->title,
                    $item->evento,
                    date('d-m-Y', strtotime($item->start)),
                    date('d-m-Y', strtotime($item->end)),
                    $item->institucion,
                    $item->nivelinstitucion, // Incluir nivel
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


