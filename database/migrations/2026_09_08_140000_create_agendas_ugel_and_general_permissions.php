<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Los permisos `agendas.ugel` y `agendas.general` fueron definidos en
 * AgendaPermissionSeeder pero el seeder nunca se ejecutó, por lo que esos
 * permisos no existen en la base de datos. Como consecuencia, EspecDRE y
 * EspecUGEL no ven las pestañas "General" y "UGEL" en Agenda de Lectura.
 *
 * Reparto de roles:
 *   agendas.general → Admin, EspecDRE
 *   agendas.ugel    → Admin, EspecUGEL
 */
return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin     = Role::where('name', 'Admin')->first();
        $especDre  = Role::where('name', 'EspecDRE')->first();
        $especUgel = Role::where('name', 'EspecUGEL')->first();

        $general = Permission::firstOrCreate(['name' => 'agendas.general', 'guard_name' => 'web']);
        $ugel    = Permission::firstOrCreate(['name' => 'agendas.ugel',    'guard_name' => 'web']);

        // Asignar a los roles correspondientes (givePermissionTo es idempotente)
        foreach (array_filter([$admin, $especDre]) as $role) {
            $role->givePermissionTo($general);
        }
        foreach (array_filter([$admin, $especUgel]) as $role) {
            $role->givePermissionTo($ugel);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['agendas.general', 'agendas.ugel'] as $name) {
            Permission::where('name', $name)->first()?->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
