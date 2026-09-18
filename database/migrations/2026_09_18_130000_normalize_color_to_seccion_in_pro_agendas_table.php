<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Convertir los valores hexadecimales existentes a identificadores semánticos de sección
        DB::statement("
            UPDATE pro_agendas SET color = CASE
                WHEN color = '#FF0085' THEN 'lila'
                WHEN color = '#0071c5' OR color = '#0071C5' THEN 'azul_oscuro'
                WHEN color = '#40E0D0' THEN 'turquesa'
                WHEN color = '#008000' THEN 'verde'
                WHEN color = '#FFD700' THEN 'amarillo'
                WHEN color = '#FF8C00' THEN 'naranja'
                WHEN color = '#FF0000' THEN 'rojo'
                WHEN color = '#9D00FF' THEN 'violeta'
                WHEN color = '#BA4A00' THEN 'marron'
                WHEN color = '#99A3A4' THEN 'gris'
                WHEN color = '#21618C' THEN 'acero'
                WHEN color = '#000000' OR color = '#000' THEN 'negro'
                WHEN color = '#1E90FF' THEN 'azul_cielo'
                WHEN color = '#FF4500' THEN 'rojo_naranja'
                WHEN color = '#00FF7F' THEN 'verde_claro'
                ELSE 'lila'
            END
        ");

        // 2. Renombrar la columna 'color' a 'seccion' (VARCHAR(50))
        DB::statement("ALTER TABLE pro_agendas CHANGE color seccion VARCHAR(50) NOT NULL DEFAULT 'lila'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 1. Revertir el nombre de la columna a 'color'
        DB::statement("ALTER TABLE pro_agendas CHANGE seccion color VARCHAR(191) NOT NULL");

        // 2. Reconvertir los identificadores semánticos a sus códigos hexadecimales
        DB::statement("
            UPDATE pro_agendas SET color = CASE
                WHEN color = 'lila' THEN '#FF0085'
                WHEN color = 'azul_oscuro' THEN '#0071c5'
                WHEN color = 'turquesa' THEN '#40E0D0'
                WHEN color = 'verde' THEN '#008000'
                WHEN color = 'amarillo' THEN '#FFD700'
                WHEN color = 'naranja' THEN '#FF8C00'
                WHEN color = 'rojo' THEN '#FF0000'
                WHEN color = 'violeta' THEN '#9D00FF'
                WHEN color = 'marron' THEN '#BA4A00'
                WHEN color = 'gris' THEN '#99A3A4'
                WHEN color = 'acero' THEN '#21618C'
                WHEN color = 'negro' THEN '#000000'
                WHEN color = 'azul_cielo' THEN '#1E90FF'
                WHEN color = 'rojo_naranja' THEN '#FF4500'
                WHEN color = 'verde_claro' THEN '#00FF7F'
                ELSE '#FF0085'
            END
        ");
    }
};
