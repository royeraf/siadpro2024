<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Permiso de escritura para las evidencias (archivos) de una agenda de lectura.
 *
 * `agendas.create/edit` habilitan crear/editar la agenda, pero subir archivos a
 * un día programado es una acción distinta; se separa en `agendas.evidencia`.
 * Mismo reparto de roles que `agendas.create/edit` (Admin, Docente, PC, PEC):
 * el Director sigue siendo solo-visor (agendas.view + agendas.director-scope).
 */
return new class extends Migration
{
    private array $roleNames = ['Admin', 'Docente', 'PC', 'PEC'];

    public function up()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(['name' => 'agendas.evidencia', 'guard_name' => 'web']);

        foreach (Role::whereIn('name', $this->roleNames)->get() as $role) {
            $role->givePermissionTo($permission);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::where('name', 'agendas.evidencia')->first()?->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
