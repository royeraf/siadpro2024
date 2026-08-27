<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:users.index')->only('index');
        $this->middleware('can:users.create')->only('create', 'store');
        $this->middleware('can:users.edit')->only('edit', 'update');
        $this->middleware('can:users.destroy')->only('destroy');
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

        // Obtener todos los usuarios filtrados para la exportaci��n
        $allUsersQuery = clone $usersQuery;
        $allUsers = $allUsersQuery->where('estado', '1')
                         ->orderBy('id', 'desc')
                         ->get();

        // Para la vista normal, paginar los resultados
        $users = $usersQuery->where('estado', '1')
                            ->orderBy('id', 'desc')
                            ->paginate(10);

        // Asegurarnos de incluir el par��metro ugel en la paginaci��n
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
        
        // Obtener todos los usuarios para la exportaci��n
        $allUsers = $usersQuery->where('estado', '1')
                            ->orderBy('id', 'desc')
                            ->get();
        
        // Para la vista normal, paginar los resultados
        $users = $usersQuery->where('estado', '1')
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
            return response()->json(['message' => 'Usuario creado con ��xito'], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'El DNI o correo ya est�� en uso.'], 400);
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
    // El formulario de asignación de roles (user.edit) solo envía "roles[]"
    if ($request->has('roles') && !$request->has('name')) {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'integer|exists:roles,id',
        ]);
        $user->roles()->sync($validated['roles'] ?? []);
        return redirect('/users')->with('success', 'Rol actualizado correctamente.');
    }

    // Validaci��n personalizada con mensajes en espa�0�9ol
    $validator = Validator::make($request->all(), [
        'dni' => 'required|unique:users,dni,' . $user->id,
        'email' => 'required|email|unique:users,email,' . $user->id,
        'name' => 'required|string|max:255',
        'ugel' => 'required',
        'institucion' => 'required|string|max:30',
        'nivelinstitucion' => 'required',
        'provincia' => 'required|string|max:30',
        'distrito' => 'required|string|max:30',
        'cargo' => 'required',
        'estado' => 'required',
    ], [
        'dni.unique' => 'Este DNI ya est�� registrado en otro usuario.',
        'email.unique' => 'Este correo electr��nico ya est�� registrado en otro usuario.',
        'email.email' => 'El formato del correo electr��nico no es v��lido.',
        'name.required' => 'El nombre es obligatorio.',
        'ugel.required' => 'La UGEL es obligatoria.',
        'institucion.required' => 'La instituci��n es obligatoria.',
        'nivelinstitucion.required' => 'El tipo de II.EE. es obligatorio.',
        'provincia.required' => 'La provincia es obligatoria.',
        'distrito.required' => 'El distrito es obligatorio.',
        'cargo.required' => 'El cargo es obligatorio.',
        'estado.required' => 'El estado es obligatorio.',
    ]);

    // Si la validaci��n falla, redirigir con errores
    if ($validator->fails()) {
        return redirect()->back()
                         ->withErrors($validator)
                         ->withInput();
    }

    // Actualizar los datos del usuario
    $user->dni = $request->get('dni');
    $user->name = Str::upper($request->get('name'));
    $user->email = $request->get('email');
    $user->ugel = $request->get('ugel');
    $user->institucion = Str::upper($request->get('institucion'));
    $user->nivelinstitucion = $request->get('nivelinstitucion');
    $user->cargo = $request->get('cargo');
    $user->distrito = Str::upper($request->get('distrito'));
    $user->provincia = Str::upper($request->get('provincia'));
    $user->estado = $request->get('estado');
    
    // Solo actualizar la contrase�0�9a si se proporciona una nueva
    if ($request->filled('password')) {
        $user->password = bcrypt($request->get('password'));
    }

    // Sincronizar roles (mantienes tu l��gica original)
    if ($request->has('roles')) {
        $user->roles()->sync($request->roles);
    }

    $user->save();
    
    return redirect('/users')->with('success', 'Usuario actualizado correctamente.');
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






