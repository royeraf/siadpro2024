<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normaliza institucions.nivel: elimina los espacios sobrantes de los
     * descriptores MINEDU ("A2 - Inicial - Jardín              " → "A2 - Inicial - Jardín").
     *
     * No se mapea a un catálogo propio: es una decisión de dominio que se
     * documenta aparte (los valores son: A1, A2, A3, A5, E0, E1 y "Inicial-Jardin").
     */
    public function up(): void
    {
        DB::statement("UPDATE institucions SET nivel = TRIM(nivel) WHERE nivel IS NOT NULL");
    }

    public function down(): void
    {
        // Operación de limpieza sin reversión significativa.
    }
};
