<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $permissionNames = [
        'users.index',
        'users.create',
        'users.edit',
        'users.destroy',
    ];

    public function up()
    {
        $roles = Role::all();

        foreach ($this->permissionNames as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $permission->syncRoles($roles);
        }
    }

    public function down()
    {
        $adminRole = Role::where('name', 'Admin')->first();

        if (!$adminRole) {
            return;
        }

        foreach ($this->permissionNames as $name) {
            $permission = Permission::where('name', $name)->first();
            $permission?->syncRoles([$adminRole]);
        }
    }
};
