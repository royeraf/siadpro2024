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
        $this->middleware('can:users.index')->only('index', 'exportUsers');
        $this->middleware('can:users.create')->only('create', 'store');
        $this->middleware('can:users.edit')->only('edit', 'update');
        $this->middleware('can:users.destroy')->only('destroy');
    }

    public function index(Request $request)
    {
        // Tab Activos/Inhabilitados. La columna es tinyint(1) NOT NULL y solo
        // contiene 0 y 1, así que cualquier valor que no sea '0' cae en activos.
        $estado = $request->get('estado') === '0' ? '0' : '1';

        $usersQuery = User::where('estado', $estado);

        if ($request->filled('texto')) {
            $usersQuery->where('dni', 'LIKE', '%' . $request->input('texto') . '%');
        }

        if ($request->filled('cargos')) {
            $usersQuery->where('cargo', 'LIKE', '%' . $request->input('cargos') . '%');
        }

        if ($request->filled('ugel')) {
            $usersQuery->where('ugel', 'LIKE', "%{$request->input('ugel')}%");
        }

        $perPage = $this->resolvePerPage($request);

        $users = $usersQuery->orderBy('id', 'desc')
                            ->paginate($perPage)
                            ->withQueryString();

        $listaUgels = $this->listaUgels($estado);

        // Conteos para los badges de ambos tabs en una sola consulta.
        $conteos = User::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('user.index', compact('users', 'listaUgels', 'estado', 'conteos'));
    }

    public function create()
    {
        return view('user.create');
    }

    private function resolvePerPage(Request $request): int
    {
        $perPageRaw = $request->get('per_page', 10);

        if ($perPageRaw === 'all') {
            return 100000;
        }

        $perPage = (int) $perPageRaw;

        return in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 10;
    }

    private function listaUgels(string $estado)
    {
        return User::where('estado', $estado)
            ->whereNotNull('ugel')
            ->where('ugel', '!=', '')
            ->distinct()
            ->orderBy('ugel')
            ->pluck('ugel');
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
        return redirect()->route('users.index', ['estado' => $user->estado])
            ->with('success', 'Rol actualizado correctamente.');
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

    return redirect()->route('users.index', ['estado' => $user->estado])
        ->with('success', 'Usuario actualizado correctamente.');
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

    public function exportUsers(Request $request)
    {
        $estado = $request->get('estado') === '0' ? '0' : '1';

        $query = User::where('estado', $estado);

        if ($request->filled('texto')) {
            $query->where('dni', 'LIKE', '%' . $request->input('texto') . '%');
        }

        if ($request->filled('cargos')) {
            $query->where('cargo', 'LIKE', '%' . $request->input('cargos') . '%');
        }

        if ($request->filled('ugel')) {
            $query->where('ugel', 'LIKE', "%{$request->input('ugel')}%");
        }

        $users = $query->orderBy('id', 'desc')->get();

        $filename = ($estado === '1' ? 'usuarios_activos_' : 'usuarios_inhabilitados_') . date('Y-m-d') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($users, $estado) {
            $file = fopen('php://output', 'w');
            // Escribir BOM UTF-8 para visualización correcta de tildes y caracteres especiales en Excel
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
            $html .= '<th>Estado</th><th>ID</th><th>DNI</th><th>Usuario</th><th>Correo</th><th>Cargo</th><th>Institución</th><th>UGEL</th><th>Tipo de II.EE</th><th>Provincia</th><th>Distrito</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($users as $user) {
                $estadoTexto = $user->estado == 1 ? 'Activo' : 'Inactivo';
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($estadoTexto, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)$user->id, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)$user->dni, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)$user->name, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)$user->email, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($user->cargo ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($user->institucion ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($user->ugel ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($user->nivelinstitucion ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($user->provincia ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars((string)($user->distrito ?? '-'), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table></body></html>';

            fwrite($file, $html);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}






