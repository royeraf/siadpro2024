<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Los 7 permisos `sectores.*` están definidos en RoleSeeder.php pero nunca
 * llegaron a crearse en esta base de datos (el módulo Sector se agregó
 * después del seed inicial, ver comentarios "#nuevo sector" en
 * routes/web.php) — sin ellos, SectorController rechaza con 403 a
 * absolutamente todos los usuarios, incluido Admin.
 *
 * El reparto de .view/.ugel/.director NO replica el de RoleSeeder.php (que
 * daba esos 3 alcances a Admin+Docente+PC): se corrigió para seguir el mismo
 * patrón que ya usan Evidencia/Sensibilización/Difusión/Producción —
 * .view → Admin+EspecDRE, .ugel → Admin+EspecUGEL, .director → Admin+Director
 * — porque en la práctica Docente/PC viendo los reportes agregados de UGEL y
 * General no tenía sentido. Docente/PC conservan index/create/edit/destroy
 * (gestionan sus propios registros).
 */
class SectoresPermissionSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::where('name', 'Admin')->firstOrFail();
        $especDre = Role::where('name', 'EspecDRE')->firstOrFail();
        $especUgel = Role::where('name', 'EspecUGEL')->firstOrFail();
        $director = Role::where('name', 'Director')->firstOrFail();
        $docente = Role::where('name', 'Docente')->firstOrFail();
        $pc = Role::where('name', 'PC')->firstOrFail();

        DB::transaction(function () use ($admin, $especDre, $especUgel, $director, $docente, $pc) {
            Permission::firstOrCreate(['name' => 'sectores.index'])->syncRoles([$admin, $especDre, $especUgel, $director, $docente, $pc]);
            Permission::firstOrCreate(['name' => 'sectores.create'])->syncRoles([$admin, $docente, $pc]);
            Permission::firstOrCreate(['name' => 'sectores.edit'])->syncRoles([$admin, $docente, $pc]);
            Permission::firstOrCreate(['name' => 'sectores.destroy'])->syncRoles([$admin, $docente, $pc]);
            Permission::firstOrCreate(['name' => 'sectores.view'])->syncRoles([$admin, $especDre]);
            Permission::firstOrCreate(['name' => 'sectores.ugel'])->syncRoles([$admin, $especUgel]);
            Permission::firstOrCreate(['name' => 'sectores.director'])->syncRoles([$admin, $director]);
        });
    }
}
