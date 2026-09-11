<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS prdwebregistros;
            CREATE PROCEDURE prdwebregistros()
            BEGIN
                SELECT 
                    (SELECT COUNT(*) FROM users WHERE estado = '1' AND cargo IN ('Director', 'Docente', 'Profesor Coordinador')) AS totaldocentes,
                    (SELECT COUNT(*) FROM institucions WHERE estado = '1') AS totalinstituciones,
                    (SELECT COUNT(*) FROM pro_accions WHERE estado = '1' AND tipo = 'sensibilizacion') AS totalacciones,
                    (SELECT COUNT(*) FROM pro_accions WHERE estado = '1' AND tipo = 'difusion') AS totaldifusiones,
                    (SELECT COUNT(*) FROM pro_evidencias WHERE estado = '1') AS totalevidencias,
                    (SELECT COUNT(*) FROM pro_informes WHERE estado = '1') AS totalinformes,
                    (SELECT COUNT(*) FROM pro_plans WHERE estado = '1') AS totalplans,
                    (SELECT COUNT(*) FROM pro_produccions WHERE estado = '1') AS totalproducciones,
                    (SELECT COUNT(*) FROM pro_agendas WHERE estado = '1') AS totalagendas;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS prdwebregistros;");
    }
};
