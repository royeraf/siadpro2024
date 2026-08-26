<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pro_agendas', function (Blueprint $table) {
            $table->id();
            $table->string('institucion');
            $table->string('title');
            $table->string('evento');
            $table->string('color');
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->unsignedBigInteger('idUser');
            $table->boolean('estado');
            $table->foreign('idUser')->references('id')->on('users');
            $table->string('ugel');
            $table->string('nomDocente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agendas');
    }
};
