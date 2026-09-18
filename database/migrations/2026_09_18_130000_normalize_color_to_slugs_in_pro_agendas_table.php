<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Maneja de forma segura tanto si la columna se llama 'seccion' (despliegue previo en producción)
     * como si aún se llama 'color', asegurando que quede como 'color' VARCHAR(50) con valores normalizados (slugs).
     */
    public function up()
    {
        $hexASlug = [
            '#FF0085' => 'lila',
            '#0071C5' => 'azul_oscuro',
            '#0071c5' => 'azul_oscuro',
            '#40E0D0' => 'turquesa',
            '#008000' => 'verde',
            '#FFD700' => 'amarillo',
            '#FF8C00' => 'naranja',
            '#FF0000' => 'rojo',
            '#9D00FF' => 'violeta',
            '#BA4A00' => 'marron',
            '#99A3A4' => 'gris',
            '#21618C' => 'acero',
            '#000000' => 'negro',
            '#000'    => 'negro',
            '#1E90FF' => 'azul_cielo',
            '#FF4500' => 'rojo_naranja',
            '#00FF7F' => 'verde_claro',
        ];

        // 1. Si la columna fue renombrada a 'seccion' en producción
        if (Schema::hasColumn('pro_agendas', 'seccion')) {
            foreach ($hexASlug as $hex => $slug) {
                DB::table('pro_agendas')
                    ->where('seccion', $hex)
                    ->update(['seccion' => $slug]);
            }

            DB::statement("ALTER TABLE pro_agendas CHANGE seccion color VARCHAR(50) NOT NULL DEFAULT 'lila'");
        } 
        // 2. Si la columna se llama 'color'
        elseif (Schema::hasColumn('pro_agendas', 'color')) {
            foreach ($hexASlug as $hex => $slug) {
                DB::table('pro_agendas')
                    ->where('color', $hex)
                    ->update(['color' => $slug]);
            }

            DB::statement("ALTER TABLE pro_agendas MODIFY color VARCHAR(50) NOT NULL DEFAULT 'lila'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $slugAHex = [
            'lila'         => '#FF0085',
            'azul_oscuro'  => '#0071c5',
            'turquesa'     => '#40E0D0',
            'verde'        => '#008000',
            'amarillo'     => '#FFD700',
            'naranja'      => '#FF8C00',
            'rojo'         => '#FF0000',
            'violeta'      => '#9D00FF',
            'marron'       => '#BA4A00',
            'gris'         => '#99A3A4',
            'acero'        => '#21618C',
            'negro'        => '#000000',
            'azul_cielo'   => '#1E90FF',
            'rojo_naranja' => '#FF4500',
            'verde_claro'  => '#00FF7F',
        ];

        if (Schema::hasColumn('pro_agendas', 'color')) {
            foreach ($slugAHex as $slug => $hex) {
                DB::table('pro_agendas')
                    ->where('color', $slug)
                    ->update(['color' => $hex]);
            }

            DB::statement("ALTER TABLE pro_agendas MODIFY color VARCHAR(191) NOT NULL");
        }
    }
};
