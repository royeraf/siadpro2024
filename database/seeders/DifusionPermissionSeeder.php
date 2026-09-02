<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * "Acción de Difusión" no tenía permisos propios: usaba prestados los de
 * "Acción de Sensibilización" (`accions.*`) — copy-paste, mismo controlador
 * base (Accion), otro `tipo`. Este seeder crea el set `difusions.*` con el
 * mismo reparto de roles que tenían de facto los prestados (salvo
 * `difusions.view`, que nace ya normalizado a Admin+EspecDRE — ver
 * NormalizeScopePermissionsSeeder para el porqué), para que Difusión quede
 * independiente de Sensibilización sin cambiar el acceso real de nadie.
 */
class DifusionPermissionSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::where('name', 'Admin')->firstOrFail();
        $especDre = Role::where('name', 'EspecDRE')->firstOrFail();
        $especUgel = Role::where('name', 'EspecUGEL')->firstOrFail();
        $director = Role::where('name', 'Director')->firstOrFail();
        $docente = Role::where('name', 'Docente')->firstOrFail();
        $pc = Role::where('name', 'PC')->firstOrFail();
        $pec = Role::where('name', 'PEC')->firstOrFail();

        DB::transaction(function () use ($admin, $especDre, $especUgel, $director, $docente, $pc, $pec) {
            $todos = [$admin, $especDre, $especUgel, $director, $docente, $pc, $pec];

            Permission::firstOrCreate(['name' => 'difusions.index'])->syncRoles($todos);
            Permission::firstOrCreate(['name' => 'difusions.create'])->syncRoles($todos);
            Permission::firstOrCreate(['name' => 'difusions.edit'])->syncRoles($todos);
            Permission::firstOrCreate(['name' => 'difusions.destroy'])->syncRoles($todos);
            Permission::firstOrCreate(['name' => 'difusions.view'])->syncRoles([$admin, $especDre]);
            Permission::firstOrCreate(['name' => 'difusions.ugel'])->syncRoles([$admin, $especUgel]);
            Permission::firstOrCreate(['name' => 'difusions.director'])->syncRoles([$admin, $director]);
        });
    }
}
