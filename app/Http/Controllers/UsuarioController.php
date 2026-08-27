<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }
    
    public function index(){

        $user = User::findOrFail(Auth::id());
        return view('usuario.index', compact('user'));
    }

    public function store(Request $request){

        $user = User::find(Auth::id());
        $user->password = bcrypt($request->get('password'));
        $user->save();
        return redirect('/usuarios');
    }
   
    public function edit($id)
    {
        $user = User::find($id);
        return view('user.update')->with('user',$user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        $user->name = Str::upper($request->get('name'));
        $user->email = $request->get('email');
        $user->ugel = Str::upper($request->get('ugel'));
        $user->institucion = Str::upper($request->get('institucion'));
        $user->dni = $request->get('dni');
        $user->nivelinstitucion = $request->get('nivelinstitucion');
        $user->cargo = $request->get('cargo');
        $user->distrito = Str::upper($request->get('distrito'));
        $user->provincia = Str::upper($request->get('provincia'));
        $user->estado = $request->get('estado');
        $user->password = bcrypt($request->get('password'));

        $user->save();
        return redirect()->route('users.index', ['estado' => $user->estado]);
    }

   
}
?>