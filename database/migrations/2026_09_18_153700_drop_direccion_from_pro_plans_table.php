<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Eliminar la columna huérfana/inutilizada 'direccion' de pro_plans
     * (100% de valores NULL en los 3,770 registros).
     */
    public function up(): void
    {
        Schema::table('pro_plans', function (Blueprint $table) {
            if (Schema::hasColumn('pro_plans', 'direccion')) {
                $table->dropColumn('direccion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pro_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('pro_plans', 'direccion')) {
                $table->string('direccion')->nullable()->after('color');
            }
        });
    }
};
