<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Eliminar la columna huérfana/inutilizada 'enlacedrive' de pro_sectores
     * (100% de valores NULL en los 2,548 registros).
     */
    public function up(): void
    {
        Schema::table('pro_sectores', function (Blueprint $table) {
            if (Schema::hasColumn('pro_sectores', 'enlacedrive')) {
                $table->dropColumn('enlacedrive');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pro_sectores', function (Blueprint $table) {
            if (!Schema::hasColumn('pro_sectores', 'enlacedrive')) {
                $table->string('enlacedrive')->nullable()->after('enlace');
            }
        });
    }
};
