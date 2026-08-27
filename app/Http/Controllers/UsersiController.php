<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UsersiController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:usersi.index')->only('index');
        $this->middleware('can:usersi.create')->only('create', 'store');
        $this->middleware('can:usersi.edit')->only('edit', 'update');
        $this->middleware('can:usersi.destroy')->only('destroy');
    }

    public function index(Request $request)
    {
        $usersQuery = User::query();

        if ($request->filled('texto')) {
            $usersQuery->where('dni', 'LIKE', '%' . $request->input('texto') . '%');
        }

        if ($request->filled('cargos')) {
            $usersQuery->where('cargo', 'LIKE', '%' . $request->input('cargos') . '%');
        }

        if ($request->filled('ugel')) {
            $usersQuery->where('ugel', 'LIKE', "%{$request->input('ugel')}%");
        }

        // Obtener todos los usuarios filtrados para la exportación
        $allUsersQuery = clone $usersQuery;
        $allUsers = $allUsersQuery->where('estado', '0')
                         ->orderBy('id', 'desc')
                         ->get();

        // Para la vista normal, paginar los resultados
        $users = $usersQuery->where('estado', '0')
                            ->orderBy('id', 'desc')
                            ->paginate(10);

        // Asegurarnos de incluir el parámetro ugel en la paginación
        if ($request->has('page')) {
            $users->appends(request()->only(['texto', 'cargos', 'ugel']));
        }

        return view('user.index', compact('users', 'allUsers'));
    }

    public function create()
    {
        return view('user.create');
    }
    
    public function buscar(Request $request){
        $texto=$request->get('texto', '');
        $cargos=trim($request->get('cargos'));
        $ugel = trim($request->get('ugel'));
        
        $usersQuery = User::query();
        
        if (!empty($texto)) {
            $usersQuery->where('dni', 'LIKE', "%{$texto}%");
        }
        
        if (!empty($cargos)) {
            $usersQuery->where('cargo', 'LIKE', "%{$cargos}%");
        }
        
        if (!empty($ugel)) {
            $usersQuery->where('ugel', 'LIKE', "%{$ugel}%");
        }
        
        // Obtener todos los usuarios para la exportación
        $allUsers = $usersQuery->where('estado', '0')
                            ->orderBy('id', 'desc')
                            ->get();
        
        // Para la vista normal, paginar los resultados
        $users = $usersQuery->where('estado', '0')
                            ->orderBy('id', 'desc')
                            ->paginate(10);
                            
        return view('user.index', compact('users', 'allUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dni' => 'required|unique:users,dni',
            'email' => 'required|unique:users,email',
        ]);
        $users = new User();
        $users->name = Str::upper($request->get('name'));
        $users->email = $request->get('email');
        $users->ugel = Str::upper($request->get('ugel'));
        $users->institucion = Str::upper($request->get('institucion'));
        $users->dni = $request->get('dni');
        $users->nivelinstitucion = $request->get('nivelinstitucion');
        $users->cargo = $request->get('cargo');
        $users->distrito = Str::upper($request->get('distrito'));
        $users->provincia = Str::upper($request->get('provincia'));
        $users->estado = $request->get('estado');
        $users->password = bcrypt($request->get('password'));

        try {
            // Crear usuario
            User::create($request->all());
            return response()->json(['message' => 'Usuario creado con éxito'], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'El DNI o correo ya está en uso.'], 400);
        }
        $users->save();
        return redirect('/users');
    }


    public function edit(User $user)
    {
        $roles =  Role::all();
        
        return view('user.edit',compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
       
        $user->roles()->sync($request->roles);
        

        return redirect('/users');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        $user->delete();
        return redirect('/users');
    }
    
    public function cambiarEstado($id)
    {
        $user = User::findOrFail($id);
        $user->estado = $user->estado == 1 ? 0 : 1;
        $user->save();

        return redirect()->back()->with('success', 'Estado de usuario actualizado correctamente.');
    }

    public function obtenerUgels()
    {
        $ugels = \DB::table('users')
            ->select('ugel')
            ->where('ugel', '!=', '')
            ->whereNotNull('ugel')
            ->distinct()
            ->orderBy('ugel')
            ->get();
        
        return response()->json($ugels);
    }

    public function buscarUser(Request $request)
    {
        return $this->buscar($request);
    }

}






