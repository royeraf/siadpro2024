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
        $this->middleware('auth');
        
    }
    public function index(Request $request)
    {
        $institucions = Institucion::where('estado', '1')->orderby('id');

        if ($request->has('ugels') && $request->ugels != '') {
            $institucions = $institucions->where('ugel', $request->ugels);
        }

        // Get the total count before fetching the results
        $total = $institucions->count();
        
        $institucions = $institucions->get();

        return view('institucion.index', compact('institucions', 'total'));
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
}
