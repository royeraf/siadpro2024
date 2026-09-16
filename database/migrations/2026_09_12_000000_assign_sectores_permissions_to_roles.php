<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Los 7 permisos `sectores.*` existen en la base de datos, pero el reparto de
 * roles definido en SectoresPermissionSeeder nunca se aplicó en producción:
 * deploy.sh ejecuta `migrate --force`, no `db:seed`. Como consecuencia solo
 * Admin tenía acceso completo y EspecUGEL `sectores.ugel`; Docente, PC, PEC,
 * Director y EspecDRE no veían "Sectores del Aula" en el menú ni podían entrar
 * a la ruta (403).
 *
 * Se replica aquí el mismo reparto que ya usan los módulos normalizados
 * (Informe/Evidencia/Plan/Producción):
 *   sectores.view     → Admin, EspecDRE
 *   sectores.ugel     → Admin, EspecUGEL
 *   sectores.director → Admin, Director
 *   sectores.index/create/edit/destroy → Admin, Docente, PC, PEC
 *
 * PEC se incluye porque, igual que en DifusionPermissionSeeder y en el resto de
 * módulos, opera como rol de registro propio (no aparece en el seeder original
 * de Sectores, que quedó desactualizado).
 */
return new class extends Migration
{
    /** @var array<string, string[]> */
    private array $assignments = [
        'sectores.index'    => ['Admin', 'Docente', 'PC', 'PEC'],
        'sectores.create'   => ['Admin', 'Docente', 'PC', 'PEC'],
        'sectores.edit'     => ['Admin', 'Docente', 'PC', 'PEC'],
        'sectores.destroy'  => ['Admin', 'Docente', 'PC', 'PEC'],
        'sectores.view'     => ['Admin', 'EspecDRE'],
        'sectores.ugel'     => ['Admin', 'EspecUGEL'],
        'sectores.director' => ['Admin', 'Director'],
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->assignments as $permissionName => $roleNames) {
            $permission = Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);

            foreach (Role::whereIn('name', $roleNames)->get() as $role) {
                $role->givePermissionTo($permission);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Solo se revoca lo que esta migración agregó. `sectores.ugel` no se
        // toca: EspecUGEL ya lo tenía antes.
        $revocations = [
            'sectores.index'    => ['Docente', 'PC', 'PEC'],
            'sectores.create'   => ['Docente', 'PC', 'PEC'],
            'sectores.edit'     => ['Docente', 'PC', 'PEC'],
            'sectores.destroy'  => ['Docente', 'PC', 'PEC'],
            'sectores.view'     => ['EspecDRE'],
            'sectores.director' => ['Director'],
        ];

        foreach ($revocations as $permissionName => $roleNames) {
            $permission = Permission::where('name', $permissionName)->first();

            if (! $permission) {
                continue;
            }

            foreach (Role::whereIn('name', $roleNames)->get() as $role) {
                $role->revokePermissionTo($permission);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
