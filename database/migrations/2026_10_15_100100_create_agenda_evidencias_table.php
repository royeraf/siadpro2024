<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Archivos (evidencias) subidos a un día programado de una agenda de lectura.
 *
 * Un archivo = una fila: "uno o más archivos por día" son N filas con el mismo
 * `agenda_dia_id`. El archivo físico se guarda en
 * storage/app/public/agenda_evidencia/ (ver AgendaEvidenciaController::store).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('pro_agenda_evidencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenda_dia_id');
            $table->string('enlace');
            $table->string('nombre_original')->nullable();
            $table->unsignedBigInteger('idUser');
            $table->boolean('estado')->default(1);
            $table->timestamps();

            $table->foreign('agenda_dia_id')->references('id')->on('pro_agenda_dias')->cascadeOnDelete();
            $table->foreign('idUser')->references('id')->on('users');
            $table->index(['agenda_dia_id', 'estado']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pro_agenda_evidencias');
    }
};
