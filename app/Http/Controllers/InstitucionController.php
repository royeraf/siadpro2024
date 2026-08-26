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

        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
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
}
