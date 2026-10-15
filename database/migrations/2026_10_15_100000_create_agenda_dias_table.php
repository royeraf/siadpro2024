<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Días programados de una agenda de lectura.
 *
 * Sustituyen al par `start`/`end` de `pro_agendas` como fuente de verdad: cada
 * día lleva su propio rango de hora (nullable, porque ver
 * 2026_10_15_100200_backfill_agenda_dias_from_pro_agendas.php). `start`/`end`
 * quedan como columnas derivadas solo para el calendario y los filtros por año.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('pro_agenda_dias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenda_id');
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->timestamps();

            $table->foreign('agenda_id')->references('id')->on('pro_agendas')->cascadeOnDelete();
            $table->unique(['agenda_id', 'fecha']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pro_agenda_dias');
    }
};
