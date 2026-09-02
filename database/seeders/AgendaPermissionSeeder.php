<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Los enlaces de menú "LISTA DE AGENDAS DE LECTURA (GENERAL)" y "(UGEL)" estaban
 * protegidos con permisos prestados de otros módulos (`evidencias.view` y
 * `plans.ugel` respectivamente) — copy-paste, no permisos propios de Agenda.
 * Este seeder crea `agendas.general` y `agendas.ugel` con el mismo reparto de
 * roles que esos permisos prestados ya tenían en la práctica, así que esto no
 * cambia quién tiene acceso hoy — solo corrige el wiring.
 */
class AgendaPermissionSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::where('name', 'Admin')->firstOrFail();
        $especDre = Role::where('name', 'EspecDRE')->firstOrFail();
        $especUgel = Role::where('name', 'EspecUGEL')->firstOrFail();

        DB::transaction(function () use ($admin, $especDre, $especUgel) {
            Permission::firstOrCreate(['name' => 'agendas.general'])->syncRoles([$admin, $especDre]);
            Permission::firstOrCreate(['name' => 'agendas.ugel'])->syncRoles([$admin, $especUgel]);
        });
    }
}
