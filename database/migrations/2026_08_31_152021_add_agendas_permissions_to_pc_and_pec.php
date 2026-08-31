<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $roleNames = ['PC', 'PEC'];

    private array $permissionNames = [
        'agendas.index',
        'agendas.view',
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
        $roles = Role::whereIn('name', $this->roleNames)->get();

        foreach ($this->permissionNames as $name) {
            $permission = Permission::where('name', $name)->first();

            if (!$permission) {
                continue;
            }

            foreach ($roles as $role) {
                $role->revokePermissionTo($permission);
            }
        }
    }
};
