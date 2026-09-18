<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Eliminar las columnas redundantes 'documento' y 'color' de pro_difusions y pro_accions.
     * Ambas propiedades son generadas dinámicamente por los modelos Eloquent (Difusion y Accion)
     * mediante accessors que infieren el icono FontAwesome y color a partir de la extensión
     * del archivo almacenado en 'enlace'.
     */
    public function up(): void
    {
        Schema::table('pro_difusions', function (Blueprint $table) {
            if (Schema::hasColumn('pro_difusions', 'documento')) {
                $table->dropColumn('documento');
            }
            if (Schema::hasColumn('pro_difusions', 'color')) {
                $table->dropColumn('color');
            }
        });

        Schema::table('pro_accions', function (Blueprint $table) {
            if (Schema::hasColumn('pro_accions', 'documento')) {
                $table->dropColumn('documento');
            }
            if (Schema::hasColumn('pro_accions', 'color')) {
                $table->dropColumn('color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pro_difusions', function (Blueprint $table) {
            if (!Schema::hasColumn('pro_difusions', 'documento')) {
                $table->string('documento')->nullable()->after('nombreAccion');
            }
            if (!Schema::hasColumn('pro_difusions', 'color')) {
                $table->string('color')->nullable()->after('enlace');
            }
        });

        Schema::table('pro_accions', function (Blueprint $table) {
            if (!Schema::hasColumn('pro_accions', 'documento')) {
                $table->string('documento')->nullable()->after('nombreAccion');
            }
            if (!Schema::hasColumn('pro_accions', 'color')) {
                $table->string('color')->nullable()->after('enlace');
            }
        });
    }
};
