<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Agenda de Lectura no tenía permisos propios de escritura: el constructor de
 * AgendaController tiene el middleware `can:agendas.index` comentado, así que
 * cualquier usuario autenticado (Director incluido) podía crear/editar/borrar
 * cualquier agenda. Se crean agendas.create/edit/destroy con el mismo reparto
 * de roles que agendas.index (ver 2026_08_31_152021_add_agendas_permissions_to_pc_and_pec.php
 * y RoleSeeder.php): Admin, Docente, PC, PEC. Deliberadamente sin Director,
 * que debe quedar como solo-visor (agendas.view).
 */
return new class extends Migration
{
    private array $roleNames = ['Admin', 'Docente', 'PC', 'PEC'];

    private array $permissionNames = [
        'agendas.create',
        'agendas.edit',
        'agendas.destroy',
    ];

    public function up()
    {
        $roles = Role::whereIn('name', $this->roleNames)->get();

        foreach ($this->permissionNames as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

            foreach ($roles as $role) {
                $role->givePermissionTo($permission);
            }
        }
    }

    public function down()
    {
        foreach ($this->permissionNames as $name) {
            $permission = Permission::where('name', $name)->first();
            $permission?->delete();
        }
    }
};
