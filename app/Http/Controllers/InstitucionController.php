<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Institucion;
use App\Models\User;

class InstitucionController extends Controller
{
    public function __construct(){
        $this->middleware('auth')->except(['buscarPorCodModular']);
        
    }
    public function index(Request $request)
    {
        $query = Institucion::where('estado', '1');

        if ($request->filled('buscar')) {
            $buscar = trim($request->get('buscar'));
            $query->where(function($q) use ($buscar) {
                $q->where('nomInstitucion', 'LIKE', "%{$buscar}%")
                  ->orWhere('codModular', 'LIKE', "%{$buscar}%")
                  ->orWhere('id', 'LIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('institucion')) {
            $nom = trim($request->get('institucion'));
            $query->where('nomInstitucion', 'LIKE', "%{$nom}%");
        }

        if ($request->filled('codModular')) {
            $cod = trim($request->get('codModular'));
            $query->where('codModular', 'LIKE', "%{$cod}%");
        }

        if ($request->filled('ugels')) {
            $query->where('ugel', $request->get('ugels'));
        }

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->get('nivel'));
        }

        $total = $query->count();

        $perPageRaw = $request->get('per_page', 15);
        if ($perPageRaw === 'all') {
            $perPage = 100000;
        } else {
            $perPage = (int) $perPageRaw;
            if (!in_array($perPage, [10, 15, 25, 50, 100])) {
                $perPage = 15;
            }
        }

        $institucions = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        $listaUgels = Institucion::where('estado', '1')
            ->whereNotNull('ugel')
            ->where('ugel', '!=', '')
            ->distinct()
            ->orderBy('ugel')
            ->pluck('ugel');

        $listaNiveles = Institucion::where('estado', '1')
            ->whereNotNull('nivel')
            ->where('nivel', '!=', '')
            ->distinct()
            ->orderBy('nivel')
            ->pluck('nivel');

        return view('institucion.index', compact('institucions', 'total', 'listaUgels', 'listaNiveles'));
    }
    
    public function create()
    {

        return view('institucion.create');
        
    }

    public function store(Request $request)
    {
        $institucions = new Institucion();
        $institucions->nomInstitucion = Str::upper($request->get('nomInstitucion'));
        $institucions->codModular = $request->get('codModular');
        $institucions->nivel = $request->get('nivel');
        $institucions->centropoblado = Str::upper($request->get('centropoblado'));
        $institucions->estado = 1;
        $institucions->distrito = Str::upper($request->get('distrito'));
        $institucions->provincia = Str::upper($request->get('provincia'));
        $institucions->ugel = Str::upper($request->get('ugel'));
        $institucions->save();
        
        return redirect('/institucions');

    }

    public function edit($id)
    {
        
        $institucion = Institucion::find($id);
        return view('institucion.edit')->with('institucion', $institucion);
       
    }


    public function update(Request $request, $id)
    {
        $institucion = Institucion::find($id);
        $institucion->nomInstitucion = Str::upper($request->get('nomInstitucion'));
        $institucion->codModular = $request->get('codModular');
        $institucion->nivel = $request->get('nivel');
        $institucion->centropoblado = Str::upper($request->get('centropoblado'));
        $institucion->estado = 1;
        $institucion->distrito = Str::upper($request->get('distrito'));
        $institucion->provincia = Str::upper($request->get('provincia'));
        $institucion->ugel = Str::upper($request->get('ugel'));
        $institucion->save();
        
        return redirect('/institucions');
    }

    public function destroy($id)
    {
        $institucion = Institucion::find($id);
        $institucion->estado = '0';
        $institucion->save();
        return redirect('/institucions');
    }

    /**
     * Buscar institución por código modular (AJAX - sin auth)
     */
    public static function buscarPorCodModular($codModular)
    {
        $codModular = trim($codModular);

        $results = Institucion::where(function($q) use ($codModular) {
            $q->where('codModular', $codModular)
              ->orWhere('codModular', str_pad($codModular, 7, '0', STR_PAD_LEFT))
              ->orWhere('codModular', ltrim($codModular, '0'))
              ->orWhere('codModular', 'LIKE', '%' . $codModular . '%');
        })->get();

        return response()->json($results);
    }

    public function exportInstituciones(Request $request)
    {
        $query = Institucion::where('estado', '1');

        if ($request->filled('buscar')) {
            $buscar = trim($request->get('buscar'));
            $query->where(function($q) use ($buscar) {
                $q->where('nomInstitucion', 'LIKE', "%{$buscar}%")
                  ->orWhere('codModular', 'LIKE', "%{$buscar}%")
                  ->orWhere('id', 'LIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('institucion')) {
            $nom = trim($request->get('institucion'));
            $query->where('nomInstitucion', 'LIKE', "%{$nom}%");
        }

        if ($request->filled('codModular')) {
            $cod = trim($request->get('codModular'));
            $query->where('codModular', 'LIKE', "%{$cod}%");
        }

        if ($request->filled('ugels')) {
            $query->where('ugel', $request->get('ugels'));
        }

        if ($request->filled('nivel')) {
            $query->where('nivel', $request->get('nivel'));
        }

        $instituciones = $query->orderBy('id', 'asc')->get();

        $filename = 'instituciones_' . date('Y-m-d') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($instituciones) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 para visualización correcta de tildes y caracteres especiales
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            $html .= '<head><meta charset="utf-8">';
            $html .= '<style>
                table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; }
                th { background-color: #1E40AF; color: #FFFFFF; font-weight: bold; border: 1px solid #D1D5DB; padding: 8px; text-align: left; }
                td { border: 1px solid #E5E7EB; padding: 6px; }
                tr:nth-child(even) td { background-color: #F9FAFB; }
            </style></head><body>';
            $html .= '<table><thead><tr>';
            $html .= '<th>ID</th><th>Institución</th><th>Cód. Modular</th><th>Nivel</th><th>Provincia</th><th>Distrito</th><th>Centro Poblado</th><th>UGEL</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($instituciones as $item) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars((string)$item->id, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)$item->nomInstitucion, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)$item->codModular, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($item->nivel ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($item->provincia ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($item->distrito ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($item->centropoblado ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($item->ugel ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table></body></html>';

            fwrite($file, $html);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
