<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * El rol EspecDRE debe ser exclusivamente visor del alcance General/DRE
 * (permisos `*.view` + `agendas.general`) en los 8 módulos, igual que se dejó
 * a Director limitado a `*.director` (2026_09_03_150000) y a EspecUGEL
 * limitado a `*.ugel` (2026_09_03_153000).
 *
 * Mismo diagnóstico que EspecUGEL: el rol tenía de más, desde el mismo
 * seeder viejo nunca depurado, el CRUD completo (create/edit/destroy/index)
 * en accions, difusions, evidencias y produccions, más sectores.index — la
 * lista de permisos a revocar es idéntica a la de EspecUGEL.
 *
 * Solo 9 usuarios tienen el rol Spatie EspecDRE. De ellos, 5 además tienen
 * Admin (que ya trae esos mismos permisos por su cuenta) y no se ven
 * afectados. Un sexto usuario (Docente+EspecDRE+PC, sin Admin) queda fuera
 * del alcance de esta migración a propósito: su CRUD viene de sus roles
 * Docente/PC, no de EspecDRE, y tocar eso es una decisión de asignación de
 * roles por usuario, no de la definición del rol.
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
        $role = Role::where('name', 'EspecDRE')->first();

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
        $role = Role::where('name', 'EspecDRE')->first();

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
