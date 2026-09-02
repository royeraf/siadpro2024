<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * `accions.view` y `produccions.view` ("General" de Sensibilización y
 * Producción) tenían un reparto distinto al resto de módulos: además de
 * Admin+EspecDRE, incluían también a EspecUGEL y Director — quienes con esto
 * consultaban datos de TODA la región en vez de solo su UGEL/institución.
 * Esto coincidía con que esos dos controladores auto-restringían "General"
 * por el campo de texto libre `users.cargo` en vez de por permiso real; al
 * introducir pestañas UGEL/Director propias con permiso real (ver
 * AccionController/ProduccionController), ese parche por cargo se retira y el
 * alcance pasa a decidirlo el permiso.
 *
 * Este seeder normaliza ambos permisos a Admin+EspecDRE, igual que
 * `evidencias.view`, `informes.view`, `plans.view` y `sectores.view`. A
 * diferencia de los demás seeders de este directorio, este SÍ quita acceso
 * (EspecUGEL y Director dejan de ver "General" en estos dos módulos y pasan a
 * ver su propia pestaña UGEL/Director) — decisión explícita del usuario, se
 * deja aislado en su propio seeder para que sea reversible de un vistazo.
 */
class NormalizeScopePermissionsSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::where('name', 'Admin')->firstOrFail();
        $especDre = Role::where('name', 'EspecDRE')->firstOrFail();

        DB::transaction(function () use ($admin, $especDre) {
            $accionsView = Permission::where('name', 'accions.view')->first();
            if ($accionsView) {
                $accionsView->syncRoles([$admin, $especDre]);
            }

            $produccionsView = Permission::where('name', 'produccions.view')->first();
            if ($produccionsView) {
                $produccionsView->syncRoles([$admin, $especDre]);
            }
        });
    }
}
