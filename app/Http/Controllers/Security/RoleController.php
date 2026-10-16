<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\SecurityCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

/**
 * CRUD de roles + edición de la matriz rol→permiso desde la SPA.
 *
 * El rol Admin es protegido (ver config/security.php): no se renombra, no se
 * elimina y su matriz de permisos es inmutable desde la UI (la gobierna el
 * catálogo canónico). Así no se puede dejar al sistema sin administrador.
 */
class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:roles.index')->only('index');
        $this->middleware('can:roles.create')->only('store');
        $this->middleware('can:roles.edit')->only('update');
        $this->middleware('can:roles.destroy')->only('destroy');
    }

    public function index(SecurityCatalog $catalog)
    {
        $definitions = $catalog->roleDefinitions();

        $ordered = $catalog->orderedRoles()->load('permissions');

        $roles = $ordered->map(function (Role $role) use ($definitions, $catalog) {
            $def = $definitions[$role->name] ?? [];

            return [
                'id'            => $role->id,
                'name'          => $role->name,
                'label'         => $catalog->labelFor($role),
                'level'         => $def['level'] ?? 0,
                'protected'     => (bool) ($def['protected'] ?? false),
                'in_catalog'    => isset($definitions[$role->name]),
                'users_count'   => (int) ($role->users_count ?? $role->users()->count()),
                'permissions'   => $role->permissions->pluck('name')->all(),
            ];
        })->values();

        // Reparto real en BD (permiso → roles que lo tienen), para la pestaña
        // "Catálogo de Permisos".
        $assignments = [];
        foreach ($ordered as $role) {
            foreach ($role->permissions as $permission) {
                $assignments[$permission->name][] = $role->name;
            }
        }

        $modules = collect($catalog->modules())->map(function ($module) use ($assignments) {
            $module['permissions'] = collect($module['permissions'])->map(function ($perm) use ($assignments) {
                $perm['roles_actual'] = $assignments[$perm['name']] ?? [];

                return $perm;
            })->values();

            return $module;
        })->values();

        return Inertia::render('Security/Roles/Index', [
            'roles'       => $roles,
            'modules'     => $modules,
            'actionTypes' => SecurityCatalog::ACTIONS,
            'can'         => [
                'create'  => Auth::user()->can('roles.create'),
                'edit'    => Auth::user()->can('roles.edit'),
                'destroy' => Auth::user()->can('roles.destroy'),
            ],
        ]);
    }

    public function store(Request $request, SecurityCatalog $catalog)
    {
        $data = $request->validate([
            'label'         => ['required', 'string', 'max:191', 'regex:/^[A-Za-z0-9 _\-\.]+$/'],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => [Rule::in($catalog->permissionNames())],
        ], [
            'label.required' => 'El nombre del rol es obligatorio.',
            'label.regex'    => 'El nombre solo puede contener letras, números, espacios, guiones y puntos.',
        ]);

        $label = trim($data['label']);

        // En los roles personalizados el identificador técnico es el mismo
        // nombre visible, así que debe ser único.
        if (Role::where('name', $label)->where('guard_name', $catalog->guard())->exists()) {
            throw ValidationException::withMessages([
                'label' => 'Ya existe un rol con ese nombre.',
            ]);
        }

        $role = Role::create([
            'name'       => $label,
            'label'      => $label,
            'guard_name' => $catalog->guard(),
        ]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        $catalog->forgetCache();

        return redirect()->route('security.roles.index')
            ->with('success', "Rol «{$label}» creado correctamente.");
    }

    public function update(Request $request, Role $role, SecurityCatalog $catalog)
    {
        if ($catalog->isProtected($role->name)) {
            return redirect()->route('security.roles.index')
                ->with('error', 'El rol Admin es protegido y no se puede editar.');
        }

        $data = $request->validate([
            'label'         => ['required', 'string', 'max:191', 'regex:/^[A-Za-z0-9 _\-\.]+$/'],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => [Rule::in($catalog->permissionNames())],
        ], [
            'label.required' => 'El nombre del rol es obligatorio.',
            'label.regex'    => 'El nombre solo puede contener letras, números, espacios, guiones y puntos.',
        ]);

        $label = trim($data['label']);
        $inCatalog = array_key_exists($role->name, $catalog->roleDefinitions());

        // Los roles del catálogo conservan su `name` técnico (p. ej. EspecDRE)
        // porque el catálogo, la jerarquía y el código dependen de él: aquí solo
        // cambia el nombre VISIBLE. Los personalizados sí se renombran completos.
        if (! $inCatalog) {
            $conflict = Role::where('name', $label)
                ->where('guard_name', $catalog->guard())
                ->where('id', '!=', $role->id)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'label' => 'Ya existe un rol con ese nombre.',
                ]);
            }

            $role->name = $label;
        }

        $role->label = $label;
        $role->save();

        // El Admin no puede perder los permisos de Seguridad desde la UI.
        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        $catalog->forgetCache();

        return redirect()->route('security.roles.index')
            ->with('success', "Rol «{$label}» actualizado correctamente.");
    }

    public function destroy(Role $role, SecurityCatalog $catalog)
    {
        if ($catalog->isProtected($role->name)) {
            return redirect()->route('security.roles.index')
                ->with('error', 'El rol Admin no puede eliminarse.');
        }

        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return redirect()->route('security.roles.index')
                ->with('error', "No se puede eliminar «{$role->name}»: tiene {$usersCount} usuario(s) asignado(s). Reasígnalos primero.");
        }

        $name = $role->name;
        $role->delete();
        $catalog->forgetCache();

        return redirect()->route('security.roles.index')
            ->with('success', "Rol «{$name}» eliminado correctamente.");
    }
}
