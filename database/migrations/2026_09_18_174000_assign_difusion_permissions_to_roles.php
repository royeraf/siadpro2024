<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Los permisos `difusions.*` se crearon originalmente mediante DifusionPermissionSeeder,
 * pero en producción (VPS) el deploy solo ejecuta `php artisan migrate --force` y nunca
 * `db:seed`. Como consecuencia, los roles (Docente, PC, PEC, Director, EspecDRE, EspecUGEL)
 * no tienen asignados los permisos `difusions.*` en la base de datos de producción.
 * Esto provoca que el menú "ACCIÓN DE DIFUSIÓN" esté oculto para todos los usuarios no admin
 * y que al intentar acceder a la ruta se reciba un error 403.
 *
 * Esta migración crea (o encuentra) los permisos y los asigna a sus respectivos roles
 * siguiendo la convención de los demás módulos:
 *   difusions.index/create/edit/destroy → Admin, Docente, PC, PEC, Director
 *   difusions.view                     → Admin, EspecDRE
 *   difusions.ugel                     → Admin, EspecUGEL
 *   difusions.director                 → Admin, Director
 */
return new class extends Migration
{
    /** @var array<string, string[]> */
    private array $assignments = [
        'difusions.index'    => ['Admin', 'Docente', 'PC', 'PEC', 'Director'],
        'difusions.create'   => ['Admin', 'Docente', 'PC', 'PEC', 'Director'],
        'difusions.edit'     => ['Admin', 'Docente', 'PC', 'PEC', 'Director'],
        'difusions.destroy'  => ['Admin', 'Docente', 'PC', 'PEC', 'Director'],
        'difusions.view'     => ['Admin', 'EspecDRE'],
        'difusions.ugel'     => ['Admin', 'EspecUGEL'],
        'difusions.director' => ['Admin', 'Director'],
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->assignments as $permissionName => $roleNames) {
            $permission = Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);

            foreach (Role::whereIn('name', $roleNames)->get() as $role) {
                $role->givePermissionTo($permission);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $revocations = [
            'difusions.index'    => ['Docente', 'PC', 'PEC', 'Director'],
            'difusions.create'   => ['Docente', 'PC', 'PEC', 'Director'],
            'difusions.edit'     => ['Docente', 'PC', 'PEC', 'Director'],
            'difusions.destroy'  => ['Docente', 'PC', 'PEC', 'Director'],
            'difusions.view'     => ['EspecDRE'],
            'difusions.ugel'     => ['EspecUGEL'],
            'difusions.director' => ['Director'],
        ];

        foreach ($revocations as $permissionName => $roleNames) {
            $permission = Permission::where('name', $permissionName)->first();

            if (! $permission) {
                continue;
            }

            foreach (Role::whereIn('name', $roleNames)->get() as $role) {
                $role->revokePermissionTo($permission);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
