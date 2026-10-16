<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega `label` a la tabla `roles`: el nombre VISIBLE del rol ("Especialista
 * DRE"), editable desde la UI. El `name` sigue siendo el identificador técnico
 * ("EspecDRE") que usan el catálogo canónico, la jerarquía de asignación y el
 * código, por lo que NO se toca desde la interfaz.
 *
 * Backfill: el label por defecto de cada rol del catálogo es el definido en
 * `config/security.php`. Los roles personalizados quedan con label = name.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'label')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('label')->nullable()->after('name');
            });
        }

        foreach (config('security.roles', []) as $name => $def) {
            DB::table('roles')
                ->where('name', $name)
                ->whereNull('label')
                ->update(['label' => $def['label'] ?? $name]);
        }

        // Los roles personalizados sin label toman su propio nombre.
        DB::table('roles')
            ->whereNull('label')
            ->update(['label' => DB::raw('name')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('roles', 'label')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('label');
            });
        }
    }
};
