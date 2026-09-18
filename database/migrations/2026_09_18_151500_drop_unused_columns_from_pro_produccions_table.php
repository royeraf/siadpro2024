<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Eliminar columnas huérfanas/inutilizadas (enlacedrive, lugar) que quedaron
     * de la plantilla inicial de creación y tienen el 100% de valores NULL (0 registros con datos).
     */
    public function up(): void
    {
        Schema::table('pro_produccions', function (Blueprint $table) {
            if (Schema::hasColumn('pro_produccions', 'enlacedrive')) {
                $table->dropColumn('enlacedrive');
            }
            if (Schema::hasColumn('pro_produccions', 'lugar')) {
                $table->dropColumn('lugar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pro_produccions', function (Blueprint $table) {
            if (!Schema::hasColumn('pro_produccions', 'enlacedrive')) {
                $table->string('enlacedrive')->nullable()->after('descripcion');
            }
            if (!Schema::hasColumn('pro_produccions', 'lugar')) {
                $table->string('lugar')->nullable()->after('descripcion');
            }
        });
    }
};
