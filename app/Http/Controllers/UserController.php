<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use App\Services\ReniecService;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->middleware('can:users.index')->only('index', 'exportUsers');
        $this->middleware('can:users.create')->only('create', 'store');
        $this->middleware('can:users.edit')->only('edit', 'update');
        $this->middleware('can:users.destroy')->only('destroy');
    }

    private function isAdmin(): bool
    {
        return Auth::user()->hasRole('Admin');
    }

    /**
     * Jerarquía escalonada de asignación de roles: cada rol solo puede
     * asignar su propio nivel o niveles inferiores, nunca superiores.
     * Admin no aparece aquí porque isAdmin() ya deja pasar cualquier rol.
     */
    private const ROLE_HIERARCHY = [
        'EspecDRE'  => ['EspecDRE', 'EspecUGEL', 'Director', 'Docente', 'PC', 'PEC'],
        'EspecUGEL' => ['EspecUGEL', 'Director', 'Docente', 'PC', 'PEC'],
        'Director'  => ['Director', 'Docente', 'PC', 'PEC'],
        'Docente'   => ['Docente', 'PC', 'PEC'],
        'PC'        => ['Docente', 'PC', 'PEC'],
        'PEC'       => ['Docente', 'PC', 'PEC'],
    ];

    /**
     * Roles que el usuario autenticado puede asignar a otros, según la
     * jerarquía escalonada (nunca por encima de su propio nivel).
     */
    private function assignableRoles()
    {
        if ($this->isAdmin()) {
            return Role::all();
        }

        $assignableNames = collect(Auth::user()->roles->pluck('name'))
            ->flatMap(fn ($roleName) => self::ROLE_HIERARCHY[$roleName] ?? [])
            ->unique();

        return Role::whereIn('name', $assignableNames)->get();
    }

    /**
     * Restringe los roles que el usuario autenticado puede asignar a otros,
     * según la jerarquía escalonada: nunca puede otorgar un rol por encima
     * del suyo propio (evita escalada de privilegios).
     */
    private function filterAssignableRoles(array $roleIds): array
    {
        $assignableIds = $this->assignableRoles()->pluck('id')->all();

        return array_values(array_intersect($roleIds, $assignableIds));
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

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $usersQuery->where(function ($q) use ($buscar) {
                foreach (['dni', 'name', 'email', 'cargo', 'institucion', 'ugel', 'provincia', 'distrito'] as $col) {
                    $q->orWhere($col, 'LIKE', "%{$buscar}%");
                }
            });
        }

        $perPage = $this->resolvePerPage($request);

        $users = $usersQuery->orderBy('id', 'desc')
                            ->paginate($perPage)
                            ->withQueryString();

        $listaUgels = $this->listaUgels($estado);

        if ($request->ajax()) {
            return response()->json([
                'rows' => view('user._rows', ['users' => $users])->render(),
                'pagination' => (string) $users->appends($request->except('page'))->links('vendor.pagination.table-tailwind'),
                'total' => $users->total(),
                'totalFormatted' => number_format($users->total()),
                'from' => $users->firstItem() ?? 0,
                'to' => $users->lastItem() ?? 0,
            ]);
        }

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

    public function checkDni(Request $request, string $dni, ReniecService $reniecService)
    {
        $cleanDni = trim($dni);

        if (!preg_match('/^[0-9]{8}$/', $cleanDni)) {
            return response()->json([
                'valid' => false,
                'exists' => false,
                'message' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
            ], 422);
        }

        $excludeId = $request->query('exclude_id');

        $query = User::where('dni', $cleanDni);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $user = $query->first(['id', 'name', 'email', 'cargo', 'institucion', 'ugel', 'estado']);

        if ($user) {
            $estadoTexto = (int) $user->estado === 1 ? 'Activo' : 'Inactivo';
            return response()->json([
                'valid' => true,
                'exists' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'cargo' => $user->cargo ?? 'Sin cargo',
                    'institucion' => $user->institucion,
                    'ugel' => $user->ugel,
                    'estado' => $estadoTexto,
                ],
                'message' => "Este DNI ya está registrado para: {$user->name} ({$user->cargo}) - {$estadoTexto}.",
            ]);
        }

        // Si se solicita consultar en RENIEC (formulario de creación de usuario)
        $reniecData = null;
        if ($request->boolean('buscar_reniec', false)) {
            $reniecResult = $reniecService->consultarDni($cleanDni);
            if (!empty($reniecResult['success']) && !empty($reniecResult['data'])) {
                $reniecData = $reniecResult['data'];
            }
        }

        return response()->json([
            'valid' => true,
            'exists' => false,
            'reniec' => $reniecData !== null,
            'reniec_data' => $reniecData,
            'message' => $reniecData
                ? "DNI disponible y datos encontrados en RENIEC: {$reniecData['nombre_completo']}"
                : 'DNI disponible.',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|digits:8|unique:users,dni',
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email',
            'password' => 'required|string|min:6|max:50',
            'estado' => 'required|in:0,1',
            'cargo' => 'required|string|max:50',
            'ugel' => 'nullable|string|max:191',
            'institucion' => 'nullable|string|max:191',
            'nivelinstitucion' => 'nullable|string|max:191',
            'provincia' => 'nullable|string|max:80',
            'distrito' => 'nullable|string|max:80',
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado en otro usuario.',
            'name.required' => 'Los apellidos y nombres son obligatorios.',
            'name.max' => 'Los apellidos y nombres no deben exceder los 191 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado en otro usuario.',
            'email.max' => 'El correo electrónico no debe exceder los 191 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.max' => 'La contraseña no debe exceder los 50 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'cargo.required' => 'El cargo es obligatorio.',
            'cargo.max' => 'El cargo no debe exceder los 50 caracteres.',
            'institucion.max' => 'La institución no debe exceder los 191 caracteres.',
            'provincia.max' => 'La provincia no debe exceder los 80 caracteres.',
            'distrito.max' => 'El distrito no debe exceder los 80 caracteres.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = new User();
        $user->created_by = Auth::id();
        $user->name = Str::upper($request->get('name'));
        $user->email = trim($request->get('email'));
        $user->ugel = $request->filled('ugel') ? Str::upper($request->get('ugel')) : null;
        $user->institucion = $request->filled('institucion') ? Str::upper($request->get('institucion')) : null;
        $user->dni = trim($request->get('dni'));
        $user->nivelinstitucion = $request->get('nivelinstitucion');
        $user->cargo = $request->get('cargo');
        $user->distrito = $request->filled('distrito') ? Str::upper($request->get('distrito')) : null;
        $user->provincia = $request->filled('provincia') ? Str::upper($request->get('provincia')) : null;
        $user->estado = (int) $request->get('estado');
        $user->password = bcrypt($request->get('password'));
        $user->save();

        return redirect()->route('users.index', ['estado' => $user->estado])
            ->with('success', 'Usuario creado con éxito');
    }


    public function edit(User $user)
    {
        // abort_unless($this->isAdmin() || $user->created_by === Auth::id(), 403);

        $roles = $this->assignableRoles();

        return view('user.edit',compact('user','roles'));
    }

    
    public function update(Request $request, User $user)
    {
        // abort_unless($this->isAdmin() || $user->created_by === Auth::id(), 403);

        // El formulario de asignación de roles (user.edit) solo envía "roles[]"
        if ($request->has('roles') && !$request->has('name')) {
            $validated = $request->validate([
                'roles' => 'array',
                'roles.*' => 'integer|exists:roles,id',
            ]);
            $user->roles()->sync($this->filterAssignableRoles($validated['roles'] ?? []));
            return redirect()->route('users.index', ['estado' => $user->estado])
                ->with('success', 'Rol actualizado correctamente.');
        }

        // Validación personalizada con mensajes en español
        $validator = Validator::make($request->all(), [
            'dni' => 'required|digits:8|unique:users,dni,' . $user->id,
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|max:50',
            'estado' => 'required|in:0,1',
            'cargo' => 'required|string|max:50',
            'ugel' => 'nullable|string|max:191',
            'institucion' => 'nullable|string|max:191',
            'nivelinstitucion' => 'nullable|string|max:191',
            'provincia' => 'nullable|string|max:80',
            'distrito' => 'nullable|string|max:80',
        ], [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado en otro usuario.',
            'name.required' => 'Los apellidos y nombres son obligatorios.',
            'name.max' => 'Los apellidos y nombres no deben exceder los 191 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado en otro usuario.',
            'email.max' => 'El correo electrónico no debe exceder los 191 caracteres.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.max' => 'La nueva contraseña no debe exceder los 50 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'cargo.required' => 'El cargo es obligatorio.',
            'cargo.max' => 'El cargo no debe exceder los 50 caracteres.',
            'institucion.max' => 'La institución no debe exceder los 191 caracteres.',
            'provincia.max' => 'La provincia no debe exceder los 80 caracteres.',
            'distrito.max' => 'El distrito no debe exceder los 80 caracteres.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar los datos del usuario
        $user->dni = trim($request->get('dni'));
        $user->name = Str::upper($request->get('name'));
        $user->email = trim($request->get('email'));
        $user->ugel = $request->filled('ugel') ? Str::upper($request->get('ugel')) : null;
        $user->institucion = $request->filled('institucion') ? Str::upper($request->get('institucion')) : null;
        $user->nivelinstitucion = $request->get('nivelinstitucion');
        $user->cargo = $request->get('cargo');
        $user->distrito = $request->filled('distrito') ? Str::upper($request->get('distrito')) : null;
        $user->provincia = $request->filled('provincia') ? Str::upper($request->get('provincia')) : null;
        $user->estado = (int) $request->get('estado');

        // Solo actualizar la contraseña si se proporciona una nueva
        if ($request->filled('password')) {
            $user->password = bcrypt($request->get('password'));
        }

        // Sincronizar roles (mantienes tu lógica original)
        if ($request->has('roles')) {
            $user->roles()->sync($this->filterAssignableRoles($request->roles));
        }

        $user->save();

        return redirect()->route('users.index', ['estado' => $user->estado])
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // abort_unless($this->isAdmin() || $user->created_by === Auth::id(), 403);

        $user->delete();
        return redirect('/users');
    }

    public function cambiarEstado($id)
    {
        $user = User::findOrFail($id);
        // abort_unless($this->isAdmin() || $user->created_by === Auth::id(), 403);

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






