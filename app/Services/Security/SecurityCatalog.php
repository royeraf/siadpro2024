<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Puente entre el catálogo canónico (`config/security.php`) y las tablas de
 * Spatie Permission.
 *
 * `sync()` deja la BD exactamente como el catálogo (idempotente): crea roles y
 * permisos faltantes y sincroniza el reparto rol→permiso. `audit()` calcula el
 * diff antes de aplicar, para poder revisar qué cambia.
 */
class SecurityCatalog
{
    public const ACTIONS = [
        'registro'  => ['label' => 'Registro (CRUD)',   'color' => 'blue'],
        'general'   => ['label' => 'General / DRE',     'color' => 'indigo'],
        'ugel'      => ['label' => 'UGEL',              'color' => 'emerald'],
        'director'  => ['label' => 'Institución',       'color' => 'amber'],
        'seguridad' => ['label' => 'Seguridad',         'color' => 'rose'],
        'especial'  => ['label' => 'Especial',          'color' => 'cyan'],
    ];

    public function guard(): string
    {
        return config('security.guard', 'web');
    }

    /** @return array<string, array{label: string, level: int, protected: bool}> */
    public function roleDefinitions(): array
    {
        return config('security.roles', []);
    }

    /** @return string[] */
    public function roleNames(): array
    {
        return array_keys($this->roleDefinitions());
    }

    /** @return string[] */
    public function protectedRoleNames(): array
    {
        return collect($this->roleDefinitions())
            ->filter(fn ($def) => $def['protected'] ?? false)
            ->keys()
            ->all();
    }

    public function isProtected(string $roleName): bool
    {
        return in_array($roleName, $this->protectedRoleNames(), true);
    }

    /** Nombre visible de un rol: su `label` editable, o el del catálogo, o su name. */
    public function labelFor(Role $role): string
    {
        if (! empty($role->label)) {
            return $role->label;
        }

        return $this->roleDefinitions()[$role->name]['label'] ?? $role->name;
    }

    /** Roles que un rol puede asignar a otros (jerarquía escalonada). */
    public function assignmentFor(string $roleName): array
    {
        return config("security.assignment.$roleName", []);
    }

    public function moduleLabel(string $key): string
    {
        return config("security.modules.$key.label", $key);
    }

    /** Módulos ordenados con sus permisos y roles ya resueltos. */
    public function modules(): array
    {
        $modules = config('security.modules', []);

        uasort($modules, fn ($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        $result = [];
        foreach ($modules as $key => $module) {
            $permissions = [];
            foreach ($module['permissions'] ?? [] as $name => $def) {
                $permissions[$name] = [
                    'name'   => $name,
                    'label'  => $def['label'] ?? $name,
                    'action' => $def['action'] ?? 'registro',
                    'roles'  => $this->rolesForPermission($def),
                ];
            }

            $result[$key] = [
                'key'         => $key,
                'label'       => $module['label'] ?? $key,
                'icon'        => $module['icon'] ?? null,
                'permissions' => $permissions,
            ];
        }

        return $result;
    }

    /** Permisos aplanados: [name => {label, action, module, module_label, roles}]. */
    public function permissions(): array
    {
        $flat = [];
        foreach ($this->modules() as $key => $module) {
            foreach ($module['permissions'] as $name => $perm) {
                $flat[$name] = array_merge($perm, [
                    'module'       => $key,
                    'module_label' => $module['label'],
                ]);
            }
        }

        return $flat;
    }

    /** @return string[] */
    public function permissionNames(): array
    {
        return array_keys($this->permissions());
    }

    public function actionLabel(string $action): string
    {
        return self::ACTIONS[$action]['label'] ?? $action;
    }

    /** Set de roles de un permiso (explícito o derivado del `action`). */
    public function rolesForPermission(array $def): array
    {
        if (! empty($def['roles'])) {
            return array_values($def['roles']);
        }

        $defaults = config('security.default_roles', []);

        return $defaults[$def['action'] ?? 'registro'] ?? $defaults['registro'] ?? [];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Reconciliación
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Aplica el catálogo canónico a la BD. Idempotente.
     *
     * @return array{roles_created: string[], permissions_created: string[]}
     */
    public function sync(): array
    {
        $guard = $this->guard();

        return DB::transaction(function () use ($guard) {
            $this->forgetCache();

            $rolesCreated = [];

            // 1. Roles (+ label visible por defecto, sin pisar ediciones de la UI).
            foreach ($this->roleDefinitions() as $name => $def) {
                $role = Role::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
                if ($role->wasRecentlyCreated) {
                    $rolesCreated[] = $name;
                }

                if (Schema::hasColumn('roles', 'label') && blank($role->label)) {
                    $role->label = $def['label'] ?? $name;
                    $role->save();
                }
            }

            $rolesByName = Role::where('guard_name', $guard)->get()->keyBy('name');

            // 2. Permisos + reparto rol→permiso exacto.
            $permissionsCreated = [];
            foreach ($this->permissions() as $name => $def) {
                $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
                if ($permission->wasRecentlyCreated) {
                    $permissionsCreated[] = $name;
                }

                $roleModels = collect($def['roles'])
                    ->map(fn ($roleName) => $rolesByName->get($roleName))
                    ->filter()
                    ->values();

                $permission->syncRoles($roleModels);
            }

            $this->forgetCache();

            return [
                'roles_created'       => $rolesCreated,
                'permissions_created' => $permissionsCreated,
            ];
        });
    }

    /**
     * Diff entre la BD actual y el catálogo, sin aplicar cambios.
     *
     * @return array{
     *     missing_roles: string[],
     *     missing_permissions: string[],
     *     permission_diffs: array<int, array{name: string, expected: string[], actual: string[], add: string[], remove: string[]}>
     * }
     */
    public function audit(): array
    {
        $guard = $this->guard();

        $existingRoles = Role::where('guard_name', $guard)->pluck('name')->all();
        $existingPermissions = Permission::where('guard_name', $guard)->get()->keyBy('name');

        $missingRoles = array_values(array_diff($this->roleNames(), $existingRoles));

        $missingPermissions = [];
        $permissionDiffs = [];

        foreach ($this->permissions() as $name => $def) {
            $permission = $existingPermissions->get($name);

            if (! $permission) {
                $missingPermissions[] = $name;
                continue;
            }

            $expected = $def['roles'];
            $actual = $permission->roles()->pluck('name')->all();

            sort($expected);
            sort($actual);

            if ($expected !== $actual) {
                $permissionDiffs[] = [
                    'name'     => $name,
                    'expected' => $expected,
                    'actual'   => $actual,
                    'add'      => array_values(array_diff($expected, $actual)),
                    'remove'   => array_values(array_diff($actual, $expected)),
                ];
            }
        }

        return [
            'missing_roles'       => $missingRoles,
            'missing_permissions' => $missingPermissions,
            'permission_diffs'    => $permissionDiffs,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // Acceso efectivo
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Acceso efectivo de un usuario, agrupado por módulo para la UI.
     *
     * @return array{
     *     roles: string[],
     *     direct: string[],
     *     effective: string[],
     *     modules: array<string, array{key: string, label: string, granted: string[], permissions: array<int, array{name: string, label: string, action: string, granted: bool}>}>
     * }
     */
    public function accessFor(User $user): array
    {
        $user->unsetRelation('roles')->unsetRelation('permissions');

        $roles = $user->getRoleNames()->all();
        $direct = $user->getDirectPermissions()->pluck('name')->all();
        $fromRoles = $user->getPermissionsViaRoles()->pluck('name')->all();
        $effective = $user->getAllPermissions()->pluck('name')->all();

        $effectiveSet = array_flip($effective);
        $fromRolesSet = array_flip($fromRoles);

        $modules = [];
        foreach ($this->modules() as $key => $module) {
            $granted = [];
            $permissions = [];

            foreach ($module['permissions'] as $name => $perm) {
                $isGranted = isset($effectiveSet[$name]);
                $permissions[] = [
                    'name'    => $name,
                    'label'   => $perm['label'],
                    'action'  => $perm['action'],
                    'granted' => $isGranted,
                    'direct'  => in_array($name, $direct, true),
                    'via_role' => isset($fromRolesSet[$name]),
                ];
                if ($isGranted) {
                    $granted[] = $name;
                }
            }

            $modules[$key] = [
                'key'         => $key,
                'label'       => $module['label'],
                'granted'     => $granted,
                'permissions' => $permissions,
            ];
        }

        return [
            'roles'     => $roles,
            'direct'    => $direct,
            'from_roles' => $fromRoles,
            'effective' => $effective,
            'modules'   => $modules,
        ];
    }

    /**
     * Todos los roles del guard ordenados por nivel descendente (Admin
     * primero). Los roles del catálogo van por su `level`; los personalizados
     * (creados desde la UI) van al final, alfabéticamente. Incluye el conteo
     * de usuarios para evitar N+1 en el listado.
     */
    public function orderedRoles(): Collection
    {
        $defs = $this->roleDefinitions();

        return Role::where('guard_name', $this->guard())
            ->withCount('users')
            ->get()
            ->sort(function (Role $a, Role $b) use ($defs) {
                $levelA = $defs[$a->name]['level'] ?? -1;
                $levelB = $defs[$b->name]['level'] ?? -1;

                if ($levelA !== $levelB) {
                    return $levelB <=> $levelA;
                }

                return strcasecmp($a->name, $b->name);
            })
            ->values();
    }

    public function forgetCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
