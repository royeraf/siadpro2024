<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * El rol EspecUGEL debe ser exclusivamente visor/editor del alcance UGEL
 * (permisos `*.ugel`) en los 8 módulos, igual que se dejó al rol Director
 * limitado a `*.director` (ver 2026_09_03_150000_remove_write_roles_from_director_users.php).
 *
 * A diferencia de Director, aquí el problema no está en combinaciones de
 * roles por usuario (solo 33 usuarios tienen el rol Spatie EspecUGEL y
 * ninguno lo combina con Docente/PC/PEC) sino en que el propio rol tenía de
 * más, desde un seeder viejo nunca depurado: CRUD completo (create/edit/
 * destroy/index) en accions, difusions, evidencias y produccions, más
 * sectores.index. Se revoca directamente del rol.
 *
 * Los 22 usuarios que además tienen el rol Admin no se ven afectados: Admin
 * ya trae esos mismos permisos por su cuenta (68 de 69 permisos totales),
 * independiente de EspecUGEL.
 */
return new class extends Migration
{
    private array $permissionNames = [
        'accions.create', 'accions.destroy', 'accions.edit', 'accions.index',
        'difusions.create', 'difusions.destroy', 'difusions.edit', 'difusions.index',
        'evidencias.create', 'evidencias.destroy', 'evidencias.edit', 'evidencias.index',
        'produccions.create', 'produccions.destroy', 'produccions.edit', 'produccions.index',
        'sectores.index',
    ];

    public function up()
    {
        $role = Role::where('name', 'EspecUGEL')->first();

        if (!$role) {
            return;
        }

        foreach ($this->permissionNames as $name) {
            $permission = Permission::where('name', $name)->first();
            if ($permission) {
                $role->revokePermissionTo($permission);
            }
        }
    }

    public function down()
    {
        $role = Role::where('name', 'EspecUGEL')->first();

        if (!$role) {
            return;
        }

        foreach ($this->permissionNames as $name) {
            $permission = Permission::where('name', $name)->first();
            if ($permission) {
                $role->givePermissionTo($permission);
            }
        }
    }
};
