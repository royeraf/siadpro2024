<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    
    public function run()
    {
       $role1 = Role::create(['name' => 'Admin']);
       $role2 = Role::create(['name' => 'EspecDRE']);
       $role3 = Role::create(['name' => 'EspecUGEL']);
       $role4 = Role::create(['name' => 'Director']);
       $role5 = Role::create(['name' => 'Docente']);
       $role6 = Role::create(['name' => 'PC']);

       
        Permission::create(['name' => 'users.index'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'users.create'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'users.edit'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'users.destroy'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);

        Permission::create(['name' => 'institucions.index'])->syncRoles([$role1]);
        Permission::create(['name' => 'institucions.create'])->syncRoles([$role1]);
        Permission::create(['name' => 'institucions.edit'])->syncRoles([$role1]);
        Permission::create(['name' => 'institucions.destroy'])->syncRoles([$role1]);
        
        Permission::create(['name' => 'dashboard.index'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'dashboard.ugel'])->syncRoles([$role3]);
        Permission::create(['name' => 'dashboard.director'])->syncRoles([$role4]);
        Permission::create(['name' => 'dashboard.pc'])->syncRoles([$role6]);

        
        Permission::create(['name' => 'accions.index'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'accions.create'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'accions.edit'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'accions.destroy'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'accions.view'])->syncRoles([$role1, $role2, $role3, $role4,$role2]);
        Permission::create(['name' => 'accions.dre'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'accions.ugel'])->syncRoles([$role1, $role3, $role2]);
        Permission::create(['name' => 'accions.director'])->syncRoles([$role1, $role4, $role2]);

        Permission::create(['name' => 'evidencias.index'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'evidencias.create'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'evidencias.edit'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'evidencias.destroy'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'evidencias.view'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'evidencias.ugel'])->syncRoles([$role1, $role3]);
        Permission::create(['name' => 'evidencias.director'])->syncRoles([$role1, $role4]);

        Permission::create(['name' => 'plans.index'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'plans.create'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'plans.edit'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'plans.destroy'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'plans.view'])->syncRoles([$role1, $role2, $role5]);
        Permission::create(['name' => 'plans.ugel'])->syncRoles([$role1, $role3, $role5]);
        Permission::create(['name' => 'plans.director'])->syncRoles([$role1, $role4, $role5]);

        Permission::create(['name' => 'informes.index'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'informes.create'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'informes.edit'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'informes.destroy'])->syncRoles([$role1,$role5, $role6]);
        Permission::create(['name' => 'informes.view'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'informes.ugel'])->syncRoles([$role1, $role3]);
        Permission::create(['name' => 'informes.director'])->syncRoles([$role1, $role4]);

        Permission::create(['name' => 'produccions.index'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'produccions.create'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'produccions.edit'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'produccions.destroy'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'produccions.view'])->syncRoles([$role1, $role2, $role3, $role4]);
        Permission::create(['name' => 'produccions.ugel'])->syncRoles([$role1, $role3]);
        Permission::create(['name' => 'produccions.director'])->syncRoles([$role1, $role4]);

        Permission::create(['name' => 'agendas.index'])->syncRoles([$role1, $role4, $role5, $role6]);
        Permission::create(['name' => 'agendas.view'])->syncRoles([$role1, $role4, $role5, $role6]);
        
        Permission::create(['name' => 'sectores.index'])->syncRoles([$role1, $role2, $role3, $role4, $role5, $role6]);
        Permission::create(['name' => 'sectores.create'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'sectores.edit'])->syncRoles([$role1,$role5, $role6]);
        Permission::create(['name' => 'sectores.destroy'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'sectores.view'])->syncRoles([$role1, $role5, $role6]);
        Permission::create(['name' => 'sectores.ugel'])->syncRoles([$role1,$role5, $role6]);
        Permission::create(['name' => 'sectores.director'])->syncRoles([$role1,$role4, $role5, $role6]);

        // USUARIOS INHABILITADOS (usersi)
        Permission::create(['name' => 'usersi.index'])->syncRoles([$role1]);
        Permission::create(['name' => 'usersi.create'])->syncRoles([$role1]);
        Permission::create(['name' => 'usersi.edit'])->syncRoles([$role1]);
        Permission::create(['name' => 'usersi.destroy'])->syncRoles([$role1]);

    }
}
