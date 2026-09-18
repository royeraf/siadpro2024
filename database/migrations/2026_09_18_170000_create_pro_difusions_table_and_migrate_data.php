<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear tabla pro_difusions con la misma estructura requerida para Difusión
        Schema::create('pro_difusions', function (Blueprint $table) {
            $table->id();
            $table->string('nombreAccion');
            $table->string('documento')->nullable();
            $table->string('enlace')->nullable();
            $table->string('color')->nullable();
            $table->date('fecha')->nullable();
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('idUser');
            $table->boolean('estado')->default(1);
            $table->foreign('idUser')->references('id')->on('users');
            $table->timestamps();
        });

        // 2. Migrar los 2,502 registros existentes de difusión preservando exactamente sus IDs
        DB::statement("
            INSERT INTO pro_difusions (id, nombreAccion, documento, enlace, color, fecha, descripcion, idUser, estado, created_at, updated_at)
            SELECT id, nombreAccion, documento, enlace, color, fecha, descripcion, idUser, estado, created_at, updated_at
            FROM pro_accions
            WHERE tipo = 'difusion'
        ");

        // 3. Eliminar de pro_accions las filas migradas a pro_difusions
        DB::statement("DELETE FROM pro_accions WHERE tipo = 'difusion'");

        // 4. Actualizar el stored procedure prdwebregistros para consultar la nueva tabla
        if (DB::getDriverName() !== 'sqlite') {
            DB::unprepared("
                DROP PROCEDURE IF EXISTS prdwebregistros;
                CREATE PROCEDURE prdwebregistros()
                BEGIN
                    SELECT 
                        (SELECT COUNT(*) FROM users WHERE estado = '1' AND cargo IN ('Director', 'Docente', 'Profesor Coordinador')) AS totaldocentes,
                        (SELECT COUNT(*) FROM institucions WHERE estado = '1') AS totalinstituciones,
                        (SELECT COUNT(*) FROM pro_accions WHERE estado = '1') AS totalacciones,
                        (SELECT COUNT(*) FROM pro_difusions WHERE estado = '1') AS totaldifusiones,
                        (SELECT COUNT(*) FROM pro_evidencias WHERE estado = '1') AS totalevidencias,
                        (SELECT COUNT(*) FROM pro_informes WHERE estado = '1') AS totalinformes,
                        (SELECT COUNT(*) FROM pro_plans WHERE estado = '1') AS totalplans,
                        (SELECT COUNT(*) FROM pro_produccions WHERE estado = '1') AS totalproducciones,
                        (SELECT COUNT(*) FROM pro_agendas WHERE estado = '1') AS totalagendas;
                END;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restaurar los registros de difusión en pro_accions con tipo = 'difusion'
        if (Schema::hasTable('pro_difusions') && Schema::hasTable('pro_accions')) {
            DB::statement("
                INSERT INTO pro_accions (id, nombreAccion, documento, enlace, color, fecha, tipo, descripcion, lugar, idUser, estado, created_at, updated_at)
                SELECT id, nombreAccion, documento, enlace, color, fecha, 'difusion', descripcion, NULL, idUser, estado, created_at, updated_at
                FROM pro_difusions
            ");
        }

        // 2. Eliminar la tabla pro_difusions
        Schema::dropIfExists('pro_difusions');

        // 3. Restaurar la definición previa del procedure prdwebregistros
        if (DB::getDriverName() !== 'sqlite') {
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
    }
};
